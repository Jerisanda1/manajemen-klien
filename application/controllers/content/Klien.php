<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// 1. Ubah menjadi extends MY_Controller
class Klien extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // 2. Satpam manual (Session & Redirect) DIHAPUS.
        // Cukup sisakan pemanggilan model saja di sini.
        $this->load->model('Klien_model', 'klien_model');
    }

    public function index() {
        $data = [
            'title'      => 'Data Klien - Tridatu Solution',
            'content'    => 'content/klien/index',
            'data_klien' => $this->klien_model->get_all_klien() 
        ];
        
        $this->load->view('main', $data);
    }
}