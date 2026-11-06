<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function cek_login($username) {
        return $this->db->get_where('users', ['username' => $username])->row();
    }
}