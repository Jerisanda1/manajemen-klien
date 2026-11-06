<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // 1. Memuat Model
        $this->load->model('Klien_model', 'klien_model');
        
        // 2. Memuat Library Form Validation (INI YANG TADI HILANG)
        $this->load->library('form_validation');
    }

    public function index() {
        $data = [
            'title'      => 'Data Klien - Tridatu Solution',
            'content'    => 'content/klien/index',
            'data_klien' => $this->klien_model->get_all_klien() 
        ];
        $this->load->view('main', $data);
    }

    public function tambah() {
        $data = [
            'title'   => 'Tambah Klien - Tridatu Solution'
        ];
        $this->load->view('content/klien/tambah', $data);
    }

    public function simpan() {
        // 1. Aturan Validasi
        $this->form_validation->set_rules('nama_klien', 'Nama Klien', 'required');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('nama_pic', 'Nama PIC', 'required');
        $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');

        // 2. Jalankan Validasi
        if ($this->form_validation->run() == FALSE) {
            // Jika ada input yang kosong, kembalikan ke form tambah
            $this->session->set_flashdata('error', validation_errors());
            redirect('klien/tambah');
        } else {
            // 3. Jika validasi berhasil, siapkan data
            $data = [
                'nama_klien'    => $this->input->post('nama_klien', true),
                'tanggal_mulai' => $this->input->post('tanggal_mulai', true),
                'nama_pic'      => $this->input->post('nama_pic', true),
                'no_hp'         => $this->input->post('no_hp', true),
                'email'         => $this->input->post('email', true),
                'alamat'        => $this->input->post('alamat', true)
            ];

            // 4. Simpan ke Database melalui Model
            $this->klien_model->insert_klien($data);

            // 5. Buat pesan sukses dan arahkan kembali ke tabel
            $this->session->set_flashdata('success', 'Data klien berhasil ditambahkan!');
            redirect('klien');
        }
    }
}