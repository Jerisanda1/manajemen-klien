<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

    /* =========================================
     * 1. PENGATURAN PERUSAHAAN & ASET (LOGO, KOP, TTD)
     * ========================================= */
    
    // Mengambil semua data perusahaan (termasuk logo, kop surat, ttd)
    public function get_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    // Menyimpan perubahan data perusahaan / logo / aset
    public function update_settings($data) {
        return $this->db->where('id', 1)->update('company_settings', $data);
    }

    /* =========================================
     * 2. MASTER DATA JENIS DOKUMEN
     * ========================================= */
    
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

    /* =========================================
     * 3. AKUN USER (ADMIN)
     * ========================================= */
    
    // Mengambil detail user berdasarkan ID (untuk form edit profil)
    public function get_user($id) {
        return $this->db->get_where('users', ['id' => (int) $id])->row();
    }

    // Menyimpan perubahan username / password
    public function update_user($id, $data) {
        return $this->db->where('id', (int) $id)->update('users', $data);
    }
}