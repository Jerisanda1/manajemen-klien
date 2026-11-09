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

    // 2. Mengambil satu data klien berdasarkan ID
    public function get_klien_by_id($id) {
        return $this->db->get_where('clients', array('id' => $id))->row();
    }

    // 3. Menyimpan data klien baru dan mengembalikan ID-nya
    public function insert_klien($data) {
        $this->db->insert('clients', $data);
        return $this->db->insert_id(); // Mengembalikan ID dari data yang baru dibuat
    }

    // 4. Menghapus data klien
    public function delete_klien($id) {
        $this->db->where('id', $id);
        return $this->db->delete('clients');
    }

    // 5. Mengupdate data klien
    public function update_klien($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('clients', $data);
    }
}