<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 1. Mengambil semua data klien + jumlah project & dokumen
    public function get_all_klien() {
        $this->db->select('
            c.*,
            (SELECT COUNT(*) FROM project   p WHERE p.klien_id  = c.id) AS total_project,
            (SELECT COUNT(*) FROM documents d WHERE d.client_id = c.id) AS total_dokumen
        ', FALSE);  // FALSE WAJIB agar subquery tidak di-escape CI
        $this->db->from('clients c');
        $this->db->order_by('c.id', 'DESC');
        return $this->db->get()->result();
    }

    // 2. Mengambil satu data klien berdasarkan ID
    public function get_klien_by_id($id) {
        return $this->db->get_where('clients', array('id' => (int) $id))->row();
    }

    // 3. Menyimpan data klien baru dan mengembalikan ID-nya
    public function insert_klien($data) {
        $this->db->insert('clients', $data);
        return $this->db->insert_id();
    }

    // 4. Menghapus data klien
    public function delete_klien($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('clients');
    }

    // 5. Mengupdate data klien
    public function update_klien($id, $data) {
        $this->db->where('id', (int) $id);
        return $this->db->update('clients', $data);
    }

    // 6. (Opsional) Ambil project milik klien — untuk halaman detail
    public function get_project_by_klien($klien_id) {
        return $this->db->where('klien_id', (int) $klien_id)
                        ->order_by('id', 'DESC')
                        ->get('project')->result();
    }

    // 7. (Opsional) Ambil dokumen milik klien — untuk halaman detail
    public function get_dokumen_by_klien($klien_id) {
        return $this->db->select('d.*, p.nama_project')
                        ->from('documents d')
                        ->join('project p', 'p.id = d.project_id', 'left')
                        ->where('d.client_id', (int) $klien_id)
                        ->order_by('d.id', 'DESC')
                        ->get()->result();
    }
}