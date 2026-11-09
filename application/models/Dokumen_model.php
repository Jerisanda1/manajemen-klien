<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokumen_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_documents() {
        return $this->db
            ->select('d.*, c.nama_klien, p.nama_project')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->join('project p', 'p.id = d.project_id', 'left')
            ->order_by('d.id', 'DESC')
            ->get()->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('documents', ['id' => (int) $id])->row();
    }

    public function insert_document($data) {
        $this->db->insert('documents', $data);
        return $this->db->insert_id();
    }

    public function update_file($id, $data) {
        return $this->db->where('id', (int) $id)->update('documents', $data);
    }

    public function update_document($id, $data) {
        return $this->db->where('id', (int) $id)->update('documents', $data);
    }

    public function get_klien() {
        return $this->db->order_by('nama_klien', 'ASC')->get('clients')->result();
    }

        public function get_project_by_klien($client_id) {
        return $this->db->where('klien_id', (int) $client_id) 
                        ->order_by('nama_project', 'ASC')
                        ->get('project')->result();
    }

    public function delete_document($id) {
        return $this->db->delete('documents', ['id' => (int) $id]);
    }
        // Tambahkan fungsi ini untuk halaman detail
    public function get_detail($id) {
        return $this->db
            ->select('d.*, c.nama_klien, p.nama_project')
            ->from('documents d')
            ->join('clients c', 'c.id = d.client_id', 'left')
            ->join('project p', 'p.id = d.project_id', 'left')
            ->where('d.id', (int) $id)
            ->get()->row();
    }
}