
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

    /* =========================================
     * 1. PENGATURAN PERUSAHAAN & ASET
     * ========================================= */

    public function get_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    public function update_settings($data) {
        return $this->db->where('id', 1)->update('company_settings', $data);
    }


    /* =========================================
     * 2. MASTER DATA JENIS DOKUMEN
     * ========================================= */

    public function get_all_types() {
        return $this->db->order_by('id', 'ASC')
                        ->get('document_types')
                        ->result();
    }

    public function insert_type($name) {
        return $this->db->insert('document_types', ['name' => $name]);
    }

    public function update_type($id, $name) {
        return $this->db->where('id', $id)
                        ->update('document_types', ['name' => $name]);
    }

    public function delete_type($id) {
        return $this->db->where('id', $id)
                        ->delete('document_types');
    }


    /* =========================================
     * 3. AKUN USER (ADMIN)
     * ========================================= */

    public function get_user($id) {
        return $this->db->get_where('users', ['id' => (int) $id])
                        ->row();
    }

    public function update_user($id, $data) {
        return $this->db->where('id', (int) $id)
                        ->update('users', $data);
    }


    /* =========================================
     * 4. REMINDER INVOICE
     * ========================================= */

    // Mengambil semua jadwal reminder
    public function get_all_reminder_rules() {
        return $this->db
            ->order_by('offset_hari', 'DESC')
            ->get('invoice_reminder_rules')
            ->result();
    }

    // Mengambil satu jadwal berdasarkan ID
    public function get_reminder_rule($id) {
        return $this->db
            ->get_where('invoice_reminder_rules', [
                'id' => (int) $id
            ])
            ->row();
    }

    // Menambahkan jadwal reminder
    public function insert_reminder_rule($data) {
        return $this->db->insert('invoice_reminder_rules', $data);
    }

    // Memperbarui jadwal reminder
    public function update_reminder_rule($id, $data) {
        return $this->db
            ->where('id', (int) $id)
            ->update('invoice_reminder_rules', $data);
    }

    // Menghapus jadwal reminder
    public function delete_reminder_rule($id) {
        return $this->db
            ->where('id', (int) $id)
            ->delete('invoice_reminder_rules');
    }

    // Mengaktifkan / menonaktifkan jadwal reminder
    public function toggle_reminder_rule($id, $status) {
        return $this->db
            ->where('id', (int) $id)
            ->update('invoice_reminder_rules', [
                'aktif' => (int) $status
            ]);
    }

    // Memeriksa apakah offset sudah digunakan
    public function reminder_offset_exists($offset_hari, $exclude_id = NULL) {

        $this->db->where('offset_hari', (int) $offset_hari);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db
            ->count_all_results('invoice_reminder_rules') > 0;
    }

}
