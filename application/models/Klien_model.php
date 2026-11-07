<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 1. Mengambil semua data klien
    public function get_all_klien() {
        return $this->db->order_by('id', 'DESC')->get('clients')->result();
    }

    // 2. Mengambil satu data klien berdasarkan ID (INI YANG TADI HILANG)
    public function get_klien_by_id($id) {
        return $this->db->get_where('clients', array('id' => $id))->row();
    }

    // 3. Menyimpan data klien baru
    public function insert_klien($data) {
        return $this->db->insert('clients', $data);
    }

    // 4. Menghapus data klien
    public function delete_klien($id) {
        $this->db->where('id', $id);
        return $this->db->delete('clients');
    }
        // Fungsi untuk mengupdate data klien
    public function update_klien($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('clients', $data);
    }

}