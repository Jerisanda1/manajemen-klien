<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 1. Ubah menjadi extends MY_Controller
class Pembayaran extends MY_Controller {

    public function __construct() {
        parent::__construct();
        // Satpam otomatis berjalan dari MY_Controller
        
        // Di sini nanti Anda bisa memuat model, contoh: $this->load->model('Pembayaran_model');
    }

    // Fungsi untuk halaman utama (Pembayaran)
    public function index() {
        $data = [
            'title'   => 'Data Pembayaran - Tridatu Solution', // Judul diperbaiki
            'content' => 'content/pembayaran/index' 
        ];
        
        // Panggil layout utama
        $this->load->view('main', $data);
    }
}