<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran_model extends CI_Model
{
    // Nama tabel/kolom hanya dari daftar ini (aman dari injeksi)
    private $cfg = [
        'penawaran' => ['table' => 'penawaran', 'no' => 'no_penawaran', 'tgl' => 'tanggal_penawaran', 'akhir' => 'berlaku_sampai'],
        'invoice'   => ['table' => 'invoice',   'no' => 'no_invoice',   'tgl' => 'tanggal_invoice',   'akhir' => 'jatuh_tempo'],
    ];

    public function valid_tab($tab)
    {
        return isset($this->cfg[$tab]);
    }

    /* ---------- DAFTAR ---------- */
    private function filter($tab, $q, $klien, $sumber, $status)
    {
        $c = $this->cfg[$tab];
        $this->db->from($c['table'] . ' d');
        $this->db->join('clients k', 'k.id = d.klien_id', 'left');
        $this->db->join('project p', 'p.id = d.project_id', 'left');

        if ($q !== '') {
            $this->db->group_start()
                     ->like('d.' . $c['no'], $q)
                     ->or_like('k.nama_klien', $q)
                     ->or_like('p.nama_project', $q)
                     ->group_end();
        }
        if ($klien !== '')  $this->db->where('d.klien_id', (int) $klien);
        if ($status !== '') $this->db->where('d.status', $status);
        if ($sumber === 'Project') $this->db->where('d.project_id IS NOT NULL', NULL, FALSE);
        if ($sumber === 'Mandiri') $this->db->where('d.project_id IS NULL', NULL, FALSE);
    }

    public function get_list($tab, $q, $klien, $sumber, $status, $limit, $offset)
    {
        $c = $this->cfg[$tab];
        $this->filter($tab, $q, $klien, $sumber, $status);
        $this->db->select("d.id, d.{$c['no']} AS nomor, d.{$c['tgl']} AS tanggal, d.{$c['akhir']} AS akhir, d.status, k.nama_klien, p.nama_project", FALSE);
        $this->db->order_by('d.id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_filtered($tab, $q, $klien, $sumber, $status)
    {
        $this->filter($tab, $q, $klien, $sumber, $status);
        return $this->db->count_all_results();
    }

   public function get_klien_options()
{
    return $this->db->select('id, nama_klien, nama_pic')->order_by('nama_klien')->get('clients')->result();
}

public function get_klien($id)
{
    return $this->db->select('nama_klien, nama_pic, alamat')->get_where('clients', ['id' => (int) $id])->row();
}

public function get_project_nama($id)
{
    $r = $this->db->select('nama_project')->get_where('project', ['id' => (int) $id])->row();
    return $r ? $r->nama_project : NULL;
}

    public function get_project_options()
    {
        return $this->db->select('id, nama_project, klien_id')->order_by('nama_project')->get('project')->result();
    }

    /* ---------- PENAWARAN ---------- */
    public function get_penawaran($id)
    {
        return $this->db->select('d.*, k.nama_klien, k.nama_pic, k.no_hp, k.alamat, p.nama_project')
                        ->from('penawaran d')
                        ->join('clients k', 'k.id = d.klien_id', 'left')
                        ->join('project p', 'p.id = d.project_id', 'left')
                        ->where('d.id', (int) $id)
                        ->get()->row();
    }

    public function insert_penawaran($data)
    {
        $this->db->insert('penawaran', $data);
        $id = $this->db->insert_id();
        $this->add_riwayat($id, $data['status']);
        return $id;
    }

    public function update_penawaran($id, $data)
    {
        $lama = $this->db->select('status')->get_where('penawaran', ['id' => (int) $id])->row();
        $this->db->where('id', (int) $id)->update('penawaran', $data);
        if ($lama && isset($data['status']) && $lama->status !== $data['status']) {
            $this->add_riwayat($id, $data['status']);
        }
    }

    public function update_status_penawaran($id, $status)
    {
        $this->db->where('id', (int) $id)->update('penawaran', ['status' => $status]);
        $this->add_riwayat($id, $status);
    }

    public function next_nomor($tahun)
    {
        $prefix = 'PNW/' . (int) $tahun . '/';
        $row = $this->db->select('no_penawaran')
                        ->like('no_penawaran', $prefix, 'after')
                        ->order_by('no_penawaran', 'DESC')
                        ->limit(1)->get('penawaran')->row();
        $n = $row ? ((int) substr($row->no_penawaran, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad($n, 3, '0', STR_PAD_LEFT);
    }

    public function nomor_dipakai($no, $kecuali_id = 0)
    {
        $this->db->where('no_penawaran', $no);
        if ($kecuali_id) $this->db->where('id !=', (int) $kecuali_id);
        return $this->db->count_all_results('penawaran') > 0;
    }

    /* ---------- RIWAYAT STATUS ---------- */
    public function add_riwayat($id, $status)
    {
        return $this->db->insert('penawaran_riwayat', ['penawaran_id' => (int) $id, 'status' => $status]);
    }

    public function get_riwayat($id)
    {
        return $this->db->where('penawaran_id', (int) $id)->order_by('id', 'ASC')->get('penawaran_riwayat')->result();
    }

    /* ---------- HAPUS ---------- */
public function hapus()
{
    if ($this->input->method() !== 'post') redirect('pembayaran');

    $tab = (string) $this->input->post('tab', TRUE);
    if (!$this->Pembayaran_model->valid_tab($tab)) redirect('pembayaran');

    // Proses hapus data
    $this->Pembayaran_model->delete($tab, $this->input->post('id', TRUE));

    // Kirim pesan sukses
    $this->session->set_flashdata('success', ($tab === 'invoice' ? 'Invoice' : 'Penawaran') . ' berhasil dihapus.');

    // Kembali ke halaman pembayaran (tanpa halaman putih)
    redirect('pembayaran?tab=' . $tab);
}

public function get_penawaran_disetujui()
{
    return $this->db->select('d.id, d.no_penawaran, d.perihal, d.isi_surat, d.klien_id, d.project_id, d.dibuat_lewat, k.nama_klien, p.nama_project')
                    ->from('penawaran d')
                    ->join('clients k', 'k.id = d.klien_id', 'left')
                    ->join('project p', 'p.id = d.project_id', 'left')
                    ->where('d.status', 'Disetujui')
                    ->order_by('d.id', 'DESC')
                    ->get()->result();
}

public function next_nomor_invoice($tahun)
{
    $prefix = 'INV/' . (int) $tahun . '/';
    $row = $this->db->select('no_invoice')
                    ->like('no_invoice', $prefix, 'after')
                    ->order_by('no_invoice', 'DESC')
                    ->limit(1)->get('invoice')->row();
    $n = $row ? ((int) substr($row->no_invoice, strlen($prefix)) + 1) : 1;
    return $prefix . str_pad($n, 3, '0', STR_PAD_LEFT);
}

public function insert_invoice($data)
{
    return $this->db->insert('invoice', $data);
}

public function nomor_invoice_dipakai($no, $kecuali_id = 0)
{
    $this->db->where('no_invoice', $no);
    if ($kecuali_id) $this->db->where('id !=', (int) $kecuali_id);
    return $this->db->count_all_results('invoice') > 0;
}


public function get_invoice($id)
{
    return $this->db->select('
        i.*,
        pw.no_penawaran,
        pw.perihal AS perihal_penawaran,
        pw.status AS status_penawaran,
        k.nama_klien,
        k.nama_pic,
        k.email AS email_pic,
        k.no_hp AS hp_pic,
        k.alamat,
        p.nama_project
    ')
    ->from('invoice i')
    ->join('penawaran pw', 'pw.id = i.penawaran_id', 'left')
    ->join('clients k', 'k.id = i.klien_id', 'left')
    ->join('project p', 'p.id = i.project_id', 'left')
    ->where('i.id', (int) $id)
    ->get()
    ->row();
}


public function set_status_invoice($id, $status)
{
    return $this->db->where('id', (int) $id)->update('invoice', [
        'status'        => $status,
        'tanggal_bayar' => $status === 'Dibayar' ? date('Y-m-d') : NULL,
    ]);
}

public function add_pengiriman($id, $saluran)
{
    return $this->db->insert('invoice_pengiriman', ['invoice_id' => (int) $id, 'saluran' => $saluran]);
}

public function get_pengiriman_terakhir($id)
{
    $rows = $this->db->select('saluran, MAX(created_at) AS waktu', FALSE)
                     ->where('invoice_id', (int) $id)
                     ->group_by('saluran')
                     ->get('invoice_pengiriman')->result();
    $out = [];
    foreach ($rows as $r) $out[$r->saluran] = $r->waktu;
    return $out;
}

public function update_invoice($id, $data)
{
    return $this->db->where('id', (int) $id)->update('invoice', $data);
}

public function delete($tab, $id)
{
    // Tentukan nama tabel berdasarkan tab (invoice atau penawaran)
    // Sesuaikan nama tabel di bawah ini jika di database Anda berbeda
    $table = ($tab === 'invoice') ? 'invoice' : 'penawaran'; 

    $this->db->where('id', $id);
    return $this->db->delete($table);
}

public function get_by_id($tab, $id)
{
    // Tentukan tabel berdasarkan tab (invoice atau penawaran)
    $table = ($tab === 'invoice') ? 'invoice' : 'penawaran';

    // Sesuaikan query di bawah ini jika tabel Anda menggunakan JOIN dengan klien/project
    $this->db->where('id', $id);
    return $this->db->get($table)->row();
}

}