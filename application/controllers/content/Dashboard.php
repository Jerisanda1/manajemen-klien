<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 1. UBAH MENJADI extends MY_Controller
class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct(); 
        // 2. Satpam manual (session & redirect) SUDAH DIHAPUS 
        // karena sudah otomatis ditangani oleh MY_Controller
    }

    public function index()
    {
        $data = [
            'title'   => 'Dashboard - Tridatu',
            'content' => 'content/dashboard'
        ];
        
        $this->load->view('main', $data);
    }
}