<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct() {
        parent::__construct(); 
        // Memuat model Dashboard
        $this->load->model('Dashboard_model', 'dashboard_model');
    }

    public function index()
    {
        $data = [
            'title'      => 'Dashboard - Tridatu',
            'content'    => 'content/dashboard',
            'stats'      => $this->dashboard_model->get_stats(), // Mengambil data statistik
            'activities' => $this->dashboard_model->get_recent_activities(5) // Mengambil 5 aktivitas terbaru
        ];
        
        $this->load->view('main', $data);
    }
}