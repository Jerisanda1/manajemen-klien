<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // --- Query Dasar & Filter (Server-Side Pagination) ---
    private function _base_query() {
        $this->db->select('
            c.*,
            (SELECT COUNT(*) FROM project   p WHERE p.klien_id  = c.id) AS total_project,
            (SELECT COUNT(*) FROM documents d WHERE d.client_id = c.id) AS total_dokumen
        ', FALSE);
        $this->db->from('clients c');
    }

    public function count_filtered($q = '') {
        $this->_base_query();
        if (!empty($q)) {
            $this->db->group_start();
            $this->db->like('c.nama_klien', $q);
            $this->db->or_like('c.nama_pic', $q);
            $this->db->group_end();
        }
        return (int) $this->db->count_all_results();
    }

    public function get_list($q = '', $limit = 5, $offset = 0) {
        $this->_base_query();
        if (!empty($q)) {
            $this->db->group_start();
            $this->db->like('c.nama_klien', $q);
            $this->db->or_like('c.nama_pic', $q);
            $this->db->group_end();
        }
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit((int) $limit, (int) $offset);
        return $this->db->get()->result();
    }

    // --- Method Lama (Tetap Dipertahankan) ---
    public function get_all_klien() {
        $this->_base_query();
        $this->db->order_by('c.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_klien_by_id($id) {
        return $this->db->get_where('clients', array('id' => (int) $id))->row();
    }

    public function insert_klien($data) {
        $this->db->insert('clients', $data);
        return $this->db->insert_id();
    }

    public function delete_klien($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('clients');
    }

    public function update_klien($id, $data) {
        $this->db->where('id', (int) $id);
        return $this->db->update('clients', $data);
    }

    public function get_project_by_klien($klien_id) {
        return $this->db->where('klien_id', (int) $klien_id)
                        ->order_by('id', 'DESC')
                        ->get('project')->result();
    }

    public function get_dokumen_by_klien($klien_id) {
        return $this->db->select('d.*, p.nama_project')
                        ->from('documents d')
                        ->join('project p', 'p.id = d.project_id', 'left')
                        ->where('d.client_id', (int) $klien_id)
                        ->order_by('d.id', 'DESC')
                        ->get()->result();
    }
}