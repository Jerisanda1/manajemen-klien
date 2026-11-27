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

    public function insert_penawaran($data, $items = NULL, $taxes = NULL)
    {
        $this->db->trans_begin();
        $this->db->insert('penawaran', $data);
        $id = $this->db->insert_id();
        if ($id) {
            $this->add_riwayat($id, $data['status']);
            if ($items !== NULL) {
                $this->simpan_rincian_penawaran($id, $items, $taxes);
            }
        }

        if (!$id || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return $id;
    }

    public function update_penawaran($id, $data, $items = NULL, $taxes = NULL)
    {
        $this->db->trans_begin();
        $lama = $this->db->select('status')->get_where('penawaran', ['id' => (int) $id])->row();
        $this->db->where('id', (int) $id)->update('penawaran', $data);
        if ($lama && isset($data['status']) && $lama->status !== $data['status']) {
            $this->add_riwayat($id, $data['status']);
        }
        if ($items !== NULL) {
            $this->replace_penawaran_details($id, $items, $taxes);
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        $this->db->trans_commit();
        return TRUE;
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

public function get_penawaran_disetujui()
{
    return $this->db->select('d.id, d.no_penawaran, d.perihal, d.isi_surat, d.klien_id, d.project_id, d.dibuat_lewat, d.subtotal, d.discount_amount, d.total_amount, d.notes, d.payment_method, d.payment_account, k.nama_klien, p.nama_project')
                    ->from('penawaran d')
                    ->join('clients k', 'k.id = d.klien_id', 'left')
                    ->join('project p', 'p.id = d.project_id', 'left')
                    ->where('d.status', 'Disetujui')
                    ->order_by('d.id', 'DESC')
                    ->get()->result();
}

public function get_penawaran_details($penawaran_id)
{
    return $this->db
        ->select('
            id,
            penawaran_id,
            nama_item,
            nama_item AS description,
            deskripsi,
            kuantitas,
            kuantitas AS quantity,
            satuan,
            satuan AS unit,
            harga_satuan,
            harga_satuan AS unit_price,
            subtotal,
            subtotal AS line_total,
            urutan
        ', FALSE)
        ->where('penawaran_id', (int) $penawaran_id)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('penawaran_details')
        ->result();
}

public function get_penawaran_taxes($penawaran_id)
{
    return $this->db
        ->select("
            id,
            penawaran_id,
            nama_pajak,
            nama_pajak AS tax_name,
            jenis,
            CASE WHEN persentase = 0 THEN 'fixed' ELSE 'percentage' END AS tax_type,
            persentase,
            CASE WHEN persentase = 0 THEN nilai ELSE persentase END AS tax_rate,
            nilai,
            nilai AS tax_amount,
            dasar_perhitungan,
            urutan
        ", FALSE)
        ->where('penawaran_id', (int) $penawaran_id)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('penawaran_taxes')
        ->result();
}

public function get_penawaran_details_by_ids($penawaran_ids)
{
    if (!$penawaran_ids) return [];
    $rows = $this->db
        ->select('penawaran_id, nama_item AS description, kuantitas AS quantity, satuan AS unit, harga_satuan AS unit_price, subtotal AS line_total')
        ->where_in('penawaran_id', $penawaran_ids)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('penawaran_details')
        ->result();
    $grouped = [];
    foreach ($rows as $row) $grouped[$row->penawaran_id][] = $row;
    return $grouped;
}

public function get_penawaran_taxes_by_ids($penawaran_ids)
{
    if (!$penawaran_ids) return [];
    $rows = $this->db
        ->select("
            penawaran_id,
            nama_pajak AS tax_name,
            CASE WHEN persentase = 0 THEN 'fixed' ELSE 'percentage' END AS tax_type,
            CASE WHEN persentase = 0 THEN nilai ELSE persentase END AS tax_rate
        ", FALSE)
        ->where_in('penawaran_id', $penawaran_ids)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('penawaran_taxes')
        ->result();
    $grouped = [];
    foreach ($rows as $row) $grouped[$row->penawaran_id][] = $row;
    return $grouped;
}

private function simpan_rincian_penawaran($penawaran_id, $items, $taxes)
{
    foreach ($items as $item) {
        $item['penawaran_id'] = (int) $penawaran_id;
        $this->db->insert('penawaran_details', $item);
    }

    foreach ($taxes as $tax) {
        $tax['penawaran_id'] = (int) $penawaran_id;
        $this->db->insert('penawaran_taxes', $tax);
    }
}

private function replace_penawaran_details($penawaran_id, $items, $taxes)
{
    $this->db->where('penawaran_id', (int) $penawaran_id)->delete('penawaran_details');
    $this->db->where('penawaran_id', (int) $penawaran_id)->delete('penawaran_taxes');
    $this->simpan_rincian_penawaran($penawaran_id, $items, $taxes);
}

public function get_invoices_by_penawaran($penawaran_id)
{
    return $this->db
        ->select('id, no_invoice, tanggal_invoice, status, total_amount')
        ->where('penawaran_id', (int) $penawaran_id)
        ->order_by('id', 'DESC')
        ->get('invoice')
        ->result();
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

public function add_pengiriman($id, $saluran, $tujuan, $berhasil, $keterangan = '', $otomatis = 0, $offset = NULL)
{
    return $this->db->insert('invoice_pengiriman', [
        'invoice_id'  => (int) $id,
        'saluran'     => $saluran,
        'tujuan'      => $tujuan,
        'berhasil'    => $berhasil ? 1 : 0,
        'keterangan'  => $keterangan !== '' ? mb_substr($keterangan, 0, 250) : NULL,
        'otomatis'    => $otomatis ? 1 : 0,
        'offset_hari' => $offset,
    ]);
}

// Percobaan terakhir per saluran: ['email' => row, 'whatsapp' => row]
public function get_pengiriman_terakhir($id)
{
    $rows = $this->db->where('invoice_id', (int) $id)->order_by('id', 'ASC')
                     ->get('invoice_pengiriman')->result();
    $out = [];
    foreach ($rows as $r) $out[$r->saluran] = $r;
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

    if ($tab === 'penawaran') {
        if ($this->db->where('penawaran_id', (int) $id)->count_all_results('invoice') > 0) {
            return FALSE;
        }
        $this->db->where('penawaran_id', (int) $id)->delete('penawaran_details');
        $this->db->where('penawaran_id', (int) $id)->delete('penawaran_taxes');
    }
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


/* ---------- PENGINGAT OTOMATIS ---------- */
public function get_invoice_belum_bayar()
{
    return $this->db->select('i.id, i.no_invoice, i.perihal, i.jatuh_tempo, k.nama_pic, k.email AS email_pic, k.no_hp AS hp_pic, k.reminder_days_before')
                    ->from('invoice i')
                    ->join('clients k', 'k.id = i.klien_id', 'left')
                    ->where('i.status', 'Belum dibayar')
                    ->where('i.jatuh_tempo IS NOT NULL', NULL, FALSE)
                    ->get()->result();
}

public function sudah_dikirim_otomatis($id, $saluran, $offset)
{
    return $this->db->where([
        'invoice_id'  => (int) $id,
        'saluran'     => $saluran,
        'otomatis'    => 1,
        'offset_hari' => (int) $offset,
        'berhasil'    => 1,
    ])->count_all_results('invoice_pengiriman') > 0;
}


/* ==========================================
   PROFORMA INVOICE DINAMIS
========================================== */

/**
 * Mengambil template aktif untuk klien.
 * Template global dan template khusus klien.
 */
public function get_template_invoice_options($client_id)
{
    return $this->db
        ->where('document_type', 'Proforma Invoice')
        ->where('is_active', 1)
        ->group_start()
            ->where('client_id IS NULL', NULL, FALSE)
            ->or_where('client_id', (int) $client_id)
        ->group_end()
        ->order_by('is_default', 'DESC')
        ->get('document_templates')
        ->result();
}

/**
 * Mengambil template default.
 * Template khusus klien diprioritaskan.
 */

public function get_default_template_invoice($client_id)
{
    // Prioritaskan template default khusus klien.
    $khusus = $this->db
        ->where('client_id', (int) $client_id)
        ->where('document_type', 'Proforma Invoice')
        ->where('is_default', 1)
        ->where('is_active', 1)
        ->get('document_templates')
        ->row();

    if ($khusus) {
        return $khusus;
    }

    // Jika tidak ada, gunakan template default global.
    return $this->db
        ->where('client_id IS NULL', NULL, FALSE)
        ->where('document_type', 'Proforma Invoice')
        ->where('is_default', 1)
        ->where('is_active', 1)
        ->get('document_templates')
        ->row();
}

/**
 * Mengambil rincian item invoice.
 */
public function get_invoice_details($invoice_id)
{
    return $this->db
        ->select('
            id,
            invoice_id,
            nama_item,
            nama_item AS description,
            deskripsi,
            kuantitas,
            kuantitas AS quantity,
            satuan,
            satuan AS unit,
            harga_satuan,
            harga_satuan AS unit_price,
            subtotal,
            subtotal AS line_total,
            urutan
        ', FALSE)
        ->where('invoice_id', (int) $invoice_id)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('invoice_details')
        ->result();
}

/**
 * Mengambil rincian pajak invoice.
 */
public function get_invoice_taxes($invoice_id)
{
    return $this->db
        ->select("
            id,
            invoice_id,
            nama_pajak,
            nama_pajak AS tax_name,
            jenis,
            CASE
                WHEN persentase = 0 THEN 'fixed'
                ELSE 'percentage'
            END AS tax_type,
            persentase,
            persentase AS tax_rate,
            nilai,
            nilai AS tax_amount,
            dasar_perhitungan,
            urutan
        ", FALSE)
        ->where('invoice_id', (int) $invoice_id)
        ->order_by('urutan', 'ASC')
        ->order_by('id', 'ASC')
        ->get('invoice_taxes')
        ->result();
}

/**
 * Menyimpan invoice beserta item dan pajak.
 * Semua disimpan dalam satu transaksi.
 */
public function insert_invoice_lengkap($header, $items, $taxes)
{
    $this->db->trans_begin();

    $this->db->insert('invoice', $header);
    $invoice_id = $this->db->insert_id();

    if (!$invoice_id) {
        $this->db->trans_rollback();
        return false;
    }

    foreach ($items as $item) {
        $item['invoice_id'] = $invoice_id;
        $this->db->insert('invoice_details', $item);
    }

    foreach ($taxes as $tax) {
        $tax['invoice_id'] = $invoice_id;
        $this->db->insert('invoice_taxes', $tax);
    }

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return false;
    }

    $this->db->trans_commit();

    return $invoice_id;
}

/**
 * Mengganti seluruh item dan pajak invoice.
 */
public function replace_invoice_details($invoice_id, $items, $taxes)
{
    $this->db->trans_begin();

    $this->db
        ->where('invoice_id', (int) $invoice_id)
        ->delete('invoice_details');

    $this->db
        ->where('invoice_id', (int) $invoice_id)
        ->delete('invoice_taxes');

    foreach ($items as $item) {
        $item['invoice_id'] = (int) $invoice_id;
        $this->db->insert('invoice_details', $item);
    }

    foreach ($taxes as $tax) {
        $tax['invoice_id'] = (int) $invoice_id;
        $this->db->insert('invoice_taxes', $tax);
    }

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return false;
    }

    $this->db->trans_commit();

    return true;
}

}