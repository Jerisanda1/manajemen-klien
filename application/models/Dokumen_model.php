<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokumen_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /* =========================================
     * 1. QUERY DASAR & FILTER (Server-Side)
     * ========================================= */
    
    private function _base_query() {
        $this->db->from('documents d');
        $this->db->join('clients c', 'c.id = d.client_id', 'left');
        $this->db->join('project p', 'p.id = d.project_id', 'left');
    }

    private function _apply_filters($q, $jenis, $klien = '', $project = '') {
        if (!empty($q)) {
            $this->db->like('d.nama_dokumen', $q);
        }
        if (!empty($jenis)) {
            $this->db->where('d.jenis_dokumen', $jenis);
        }
        if (!empty($klien)) {
            $this->db->where('c.nama_klien', $klien);
        }
        if (!empty($project)) {
            $this->db->where('p.nama_project', $project);
        }
    }

    // Menghitung total data setelah difilter (untuk pagination)
    public function count_filtered($q = '', $jenis = '', $klien = '', $project = '') {
        $this->_base_query();
        $this->_apply_filters($q, $jenis, $klien, $project);
        return (int) $this->db->count_all_results();
    }

    // Mengambil daftar dokumen dengan limit & offset (untuk pagination)
    public function get_list($q = '', $jenis = '', $klien = '', $project = '', $limit = 10, $offset = 0) {
        $this->db->select('d.*, c.nama_klien, p.nama_project');
        $this->_base_query();
        $this->_apply_filters($q, $jenis, $klien, $project);
        $this->db->order_by('d.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    /* =========================================
     * 1b. OPSI FILTER KLIEN & PROJECT
     * ========================================= */

    // Daftar nama klien yang memiliki dokumen (untuk dropdown filter)
    public function get_klien_filter_list() {
        $rows = $this->db
            ->select('c.nama_klien')
            ->distinct()
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id')
            ->where('c.nama_klien IS NOT NULL', null, false)
            ->where("c.nama_klien <> ''", null, false)
            ->order_by('c.nama_klien', 'ASC')
            ->get()->result_array();

        return array_column($rows, 'nama_klien');
    }

    // Daftar nama project yang memiliki dokumen.
    // Jika $klien diisi, hanya project milik klien tersebut.
    public function get_project_filter_list($klien = '') {
        $this->db
            ->select('p.nama_project')
            ->distinct()
            ->from('documents d')
            ->join('project p', 'p.id = d.project_id')
            ->join('clients c', 'c.id = d.client_id')
            ->where('p.nama_project IS NOT NULL', null, false)
            ->where("p.nama_project <> ''", null, false);

        if (!empty($klien)) {
            $this->db->where('c.nama_klien', $klien);
        }

        $rows = $this->db->order_by('p.nama_project', 'ASC')->get()->result_array();
        return array_column($rows, 'nama_project');
    }

    /* =========================================
     * 2. QUERY STANDAR (CRUD & Detail)
     * ========================================= */

    // Mengambil semua dokumen (jika sewaktu-waktu butuh tanpa pagination)
    public function get_all_documents() {
        return $this->db
            ->select('d.*, c.nama_klien, p.nama_project')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->join('project p', 'p.id = d.project_id', 'left')
            ->order_by('d.id', 'DESC')
            ->get()->result();
    }

    // Mengambil 1 dokumen berdasarkan ID (untuk Unduh, Preview, Hapus)
    public function get_by_id($id) {
        return $this->db->get_where('documents', ['id' => (int) $id])->row();
    }

    // Mengambil detail dokumen beserta nama Klien & Project (untuk Halaman Detail & Edit)
    public function get_detail($id) {
        return $this->db
            ->select('d.*, c.nama_klien, p.nama_project')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->join('project p', 'p.id = d.project_id', 'left')
            ->where('d.id', (int) $id)
            ->get()->row();
    }

    // Menyimpan dokumen baru
    public function insert_document($data) {
        $this->db->insert('documents', $data);
        return $this->db->insert_id();
    }

    // Mengupdate file (digunakan saat Sync ke Cloud)
    public function update_file($id, $data) {
        return $this->db->where('id', (int) $id)->update('documents', $data);
    }

    // Mengupdate seluruh data dokumen (digunakan saat Edit)
    public function update_document($id, $data) {
        return $this->db->where('id', (int) $id)->update('documents', $data);
    }

    // Menghapus dokumen
    public function delete_document($id) {
        return $this->db->delete('documents', ['id' => (int) $id]);
    }

    /* =========================================
     * 3. QUERY UNTUK DROPDOWN (Cascading)
     * ========================================= */

    // Mengambil list klien untuk dropdown
    public function get_klien() {
        return $this->db->order_by('nama_klien', 'ASC')->get('clients')->result();
    }

    // Mengambil list project berdasarkan Klien (Cascading Dropdown)
    // Catatan: kolom FK di tabel project adalah "klien_id", bukan "client_id"
    public function get_project_by_klien($client_id) {
        return $this->db->where('klien_id', (int) $client_id)
                        ->order_by('nama_project', 'ASC')
                        ->get('project')->result();
    }

    public function project_belongs_to_client($project_id, $client_id) {
        return $this->db
            ->where('id', (int) $project_id)
            ->where('klien_id', (int) $client_id)
            ->count_all_results('project') > 0;
    }

    // Mengambil list jenis dokumen dari Pengaturan (UNTUK DROPDOWN DINAMIS)
    public function get_jenis_dokumen() {
        return $this->db->order_by('name', 'ASC')->get('document_types')->result();
    }

    /* =========================================
     * 4. FITUR REMINDER EMAIL & WA (2 FASE)
     * =========================================
     *
     * Email uses the existing timestamps; WhatsApp uses its own timestamps.
     */

    // Mengambil data email dan no telp admin dari company_settings
    public function get_company_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    /**
     * Ambil dokumen yang sudah masuk rentang pengingat klien, sebelum hari berakhir.
     * Dokumen dikembalikan jika salah satu kanal yang aktif belum berhasil.
     */
    public function get_dokumen_akan_berakhir($email_enabled = TRUE, $wa_enabled = TRUE) {
        if (!$email_enabled && !$wa_enabled) return [];

        $this->db
            ->select('d.*, c.nama_klien, c.email as email_klien, c.nama_pic')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->where('DATEDIFF(d.tanggal_berakhir, CURDATE()) BETWEEN 1 AND c.reminder_days_before', null, false);
        $this->_where_pending_reminder_channels('last_reminder_sent_at', 'wa_reminder_sent_at', $email_enabled, $wa_enabled);
        return $this->db->order_by('d.tanggal_berakhir', 'ASC')->get()->result();
    }

    public function get_dokumen_berakhir_hari_ini($email_enabled = TRUE, $wa_enabled = TRUE) {
        if (!$email_enabled && !$wa_enabled) return [];

        $this->db
            ->select('d.*, c.nama_klien, c.email as email_klien, c.nama_pic')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->where('d.tanggal_berakhir', date('Y-m-d'));
        $this->_where_pending_reminder_channels('expiry_day_notified_at', 'wa_expiry_day_notified_at', $email_enabled, $wa_enabled);
        return $this->db->order_by('d.tanggal_berakhir', 'ASC')->get()->result();
    }

    /**
     * FASE 2: Ambil dokumen yang SUDAH expired & belum pernah dinotifikasi.
     * Dikirim 1x saja (setelah ditandai, tidak akan diambil lagi).
     */
    public function get_dokumen_sudah_expired($email_enabled = TRUE, $wa_enabled = TRUE) {
        if (!$email_enabled && !$wa_enabled) return [];

        $this->db
            ->select('d.*, c.nama_klien, c.email as email_klien, c.nama_pic')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->where('d.tanggal_berakhir <', date('Y-m-d'));
        $this->_where_pending_reminder_channels('expired_notified_at', 'wa_expired_notified_at', $email_enabled, $wa_enabled);
        return $this->db->order_by('d.tanggal_berakhir', 'DESC')->get()->result();
    }

    private function _where_pending_reminder_channels($email_column, $wa_column, $email_enabled, $wa_enabled) {
        $this->db->group_start();
        if ($email_enabled) {
            $this->db->where('d.' . $email_column . ' IS NULL', null, false);
        }
        if ($wa_enabled) {
            if ($email_enabled) {
                $this->db->or_where('d.' . $wa_column . ' IS NULL', null, false);
            } else {
                $this->db->where('d.' . $wa_column . ' IS NULL', null, false);
            }
        }
        $this->db->group_end();
    }

    // Kolom reminder lama tetap dipakai sebagai status email.
    public function mark_reminder_sent($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['last_reminder_sent_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_reminder_wa_sent($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['wa_reminder_sent_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_expiry_day_notified($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['expiry_day_notified_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_expiry_day_wa_notified($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['wa_expiry_day_notified_at' => date('Y-m-d H:i:s')]);
    }

    // Kolom expired lama tetap dipakai sebagai status email.
    public function mark_expired_notified($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['expired_notified_at' => date('Y-m-d H:i:s')]);
    }

    public function mark_expired_wa_notified($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['wa_expired_notified_at' => date('Y-m-d H:i:s')]);
    }
}