<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 1. Ubah menjadi extends MY_Controller
class Project extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // 2. Satpam manual (Session & Redirect) DIHAPUS.
        
        // Di sini nanti Anda bisa memuat model, contoh: $this->load->model('Project_model');
    }

    // Fungsi untuk halaman utama (Daftar Project)
    public function index() {
        $data = [
            'title'   => 'Data Project - Tridatu Solution',
            'content' => 'content/project/index' 
        ];
        
        $this->load->view('main', $data);
    }
}