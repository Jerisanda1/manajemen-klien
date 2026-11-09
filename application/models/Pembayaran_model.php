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
    public function delete($tab, $id)
{
    if ($tab === 'penawaran') {
        $r = $this->db->select('file_path')->get_where('penawaran', ['id' => (int) $id])->row();
        if ($r && $r->file_path) {
            $f = APPPATH . 'uploads/penawaran/' . basename($r->file_path);
            if (is_file($f)) @unlink($f);
        }
        $this->db->delete('penawaran_riwayat', ['penawaran_id' => (int) $id]);
    }
    return $this->db->delete($this->cfg[$tab]['table'], ['id' => (int) $id]);
}
}