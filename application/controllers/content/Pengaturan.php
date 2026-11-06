<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 1. Ubah menjadi extends MY_Controller
class Pengaturan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Satpam otomatis berjalan dari MY_Controller
        
        // Di sini nanti Anda bisa memuat model, contoh: $this->load->model('Pengaturan_model');
    }

    // Fungsi untuk halaman utama (Pengaturan)
    public function index() {
        $data = [
            'title'   => 'Pengaturan - Tridatu Solution', // Judul diperbaiki
            'content' => 'content/pengaturan/index' 
        ];
        
        // Panggil layout utama
        $this->load->view('main', $data);
    }
}