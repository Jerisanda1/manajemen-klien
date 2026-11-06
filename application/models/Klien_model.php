<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Mengambil semua data klien dari database
    public function get_all_klien() {
        return $this->db->order_by('id', 'DESC')->get('clients')->result();
    }
}