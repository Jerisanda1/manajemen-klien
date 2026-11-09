<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

    // === Company Settings ===
    public function get_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    public function update_settings($data) {
        return $this->db->where('id', 1)->update('company_settings', $data);
    }

    // === Document Types ===
    public function get_all_types() {
        return $this->db->order_by('id', 'ASC')->get('document_types')->result();
    }

    public function insert_type($name) {
        return $this->db->insert('document_types', ['name' => $name]);
    }

    public function update_type($id, $name) {
        return $this->db->where('id', $id)->update('document_types', ['name' => $name]);
    }

    public function delete_type($id) {
        return $this->db->where('id', $id)->delete('document_types');
    }
}