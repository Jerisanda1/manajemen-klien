<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url']);
        $this->load->library('session');
    }

    public function index() {
        // Menampilkan halaman view dashboard
        $this->load->view('dashboard/index');
    }

    public function logout() {
        // Menghapus session dan kembali ke halaman login
        $this->session->sess_destroy();
        redirect('auth');
    }
}