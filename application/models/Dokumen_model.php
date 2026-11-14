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

    private function _apply_filters($q, $jenis) {
        if (!empty($q)) {
            $this->db->like('d.nama_dokumen', $q);
        }
        if (!empty($jenis)) {
            $this->db->where('d.jenis_dokumen', $jenis);
        }
    }

    // Menghitung total data setelah difilter (untuk pagination)
    public function count_filtered($q = '', $jenis = '') {
        $this->_base_query();
        $this->_apply_filters($q, $jenis);
        return (int) $this->db->count_all_results();
    }

    // Mengambil daftar dokumen dengan limit & offset (untuk pagination)
    public function get_list($q = '', $jenis = '', $limit = 10, $offset = 0) {
        $this->db->select('d.*, c.nama_klien, p.nama_project');
        $this->_base_query();
        $this->_apply_filters($q, $jenis);
        $this->db->order_by('d.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
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

    // Mengambil list jenis dokumen dari Pengaturan (UNTUK DROPDOWN DINAMIS)
    public function get_jenis_dokumen() {
        return $this->db->order_by('name', 'ASC')->get('document_types')->result();
    }

    /* =========================================
     * 4. FITUR REMINDER EMAIL
     * ========================================= */

    // Mengambil data email dan no telp admin dari company_settings
    public function get_company_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    // Mengambil dokumen yang akan berakhir dalam X hari ke depan
    public function get_dokumen_akan_berakhir($hari = 30) {
        $today    = date('Y-m-d');
        $deadline = date('Y-m-d', strtotime("+{$hari} days"));
        
        return $this->db
            ->select('d.*, c.nama_klien, c.email as email_klien, c.nama_pic')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->where('d.tanggal_berakhir >=', $today)
            ->where('d.tanggal_berakhir <=', $deadline)
            ->where('d.last_reminder_sent_at IS NULL', null, false)
            ->get()->result();
    }

    // Menandai bahwa reminder untuk dokumen ini sudah terkirim
    public function mark_reminder_sent($doc_id) {
        return $this->db
            ->where('id', (int) $doc_id)
            ->update('documents', ['last_reminder_sent_at' => date('Y-m-d H:i:s')]);
    }
}