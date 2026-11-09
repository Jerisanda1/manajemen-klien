<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // 1. Memuat Model
        $this->load->model('Klien_model', 'klien_model');
        
        // 2. Memuat Library Form Validation
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

            // 5. Catat Log Aktivitas untuk Dashboard
            $this->load->model('Dashboard_model');
            $this->Dashboard_model->log_activity('klien', 'Klien baru ditambahkan: ' . $data['nama_klien']);

            // 6. Buat pesan sukses dan arahkan kembali ke tabel
            $this->session->set_flashdata('success', 'Data klien berhasil ditambahkan!');
            redirect('klien');
        }
    }

    public function detail($id) {
        $data['klien'] = $this->klien_model->get_klien_by_id($id);
        
        // Jika data tidak ditemukan, tampilkan 404
        if (!$data['klien']) {
            show_404();
        }

        $data['title'] = 'Detail Klien - ' . $data['klien']->nama_klien;
        
        // Panggil view detail
        $this->load->view('content/klien/detail', $data);
    }

    public function hapus($id) {
        // Cek apakah ID valid
        if ($id) {
            // Panggil model untuk menghapus data
            $this->klien_model->delete_klien($id);
            
            // Buat pesan sukses
            $this->session->set_flashdata('success', 'Data klien berhasil dihapus!');
        }
        
        // Arahkan kembali ke halaman tabel klien
        redirect('klien');
    }

    public function edit($id) {
        $data['klien'] = $this->klien_model->get_klien_by_id($id);
        
        if (!$data['klien']) {
            show_404();
        }

        $data['title'] = 'Edit Klien - ' . $data['klien']->nama_klien;
        
        // Panggil view edit
        $this->load->view('content/klien/edit', $data);
    }

    public function update() {
        // 1. Aturan Validasi
        $this->form_validation->set_rules('nama_klien', 'Nama Klien', 'required');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('nama_pic', 'Nama PIC', 'required');
        $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');

        $id = $this->input->post('id'); // Ambil ID dari hidden input

        if ($this->form_validation->run() == FALSE) {
            // Jika gagal, kembalikan ke form edit
            $this->session->set_flashdata('error', validation_errors());
            redirect('klien/edit/' . $id);
        } else {
            // 2. Siapkan data
            $data = [
                'nama_klien'    => $this->input->post('nama_klien', true),
                'tanggal_mulai' => $this->input->post('tanggal_mulai', true),
                'nama_pic'      => $this->input->post('nama_pic', true),
                'no_hp'         => $this->input->post('no_hp', true),
                'email'         => $this->input->post('email', true),
                'alamat'        => $this->input->post('alamat', true)
            ];

            // 3. Simpan perubahan ke database
            $this->klien_model->update_klien($id, $data);

            // 4. Pesan sukses & kembali ke tabel
            $this->session->set_flashdata('success', 'Data klien berhasil diperbarui!');
            redirect('klien');
        }
    }
}