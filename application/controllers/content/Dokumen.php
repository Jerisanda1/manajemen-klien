<?php
// PERHATIKAN: Ada tanda kutip satu di 'BASEPATH'
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokumen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Satpam otomatis berjalan dari MY_Controller
        
        // Di sini nanti Anda bisa memuat model, contoh: $this->load->model('Dokumen_model');
    }

    // Fungsi untuk halaman utama (Daftar Dokumen)
    public function index() {
        $data = [
            'title'   => 'Dokumen Kerja Sama - Tridatu Solution',
            'content' => 'content/dokumen/index' 
        ];
        
        $this->load->view('main', $data);
    }
}