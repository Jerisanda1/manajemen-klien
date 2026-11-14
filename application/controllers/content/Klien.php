<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Klien extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Klien_model', 'klien_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $q = trim((string) $this->input->get('q', TRUE));
        
        // Tampilkan 5 data per halaman
        $perPage = 5;
        $page    = max(1, (int) $this->input->get('page'));

        $total      = $this->klien_model->count_filtered($q);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $data = [
            'title'      => 'Data Klien - Tridatu Solution',
            'content'    => 'content/klien/index',
            'data_klien' => $this->klien_model->get_list($q, $perPage, ($page - 1) * $perPage),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => $totalPages,
            'q'          => $q
        ];
        $this->load->view('main', $data);
    }

    public function tambah() {
        $data = [
            'title' => 'Tambah Klien - Tridatu Solution'
        ];
        $this->load->view('content/klien/tambah', $data);
    }

    public function simpan() {
        $this->form_validation->set_rules('nama_klien', 'Nama Klien', 'required');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('nama_pic', 'Nama PIC', 'required');
        $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('klien/tambah');
        } else {
            $data = [
                'nama_klien'    => $this->input->post('nama_klien', true),
                'tanggal_mulai' => $this->input->post('tanggal_mulai', true),
                'nama_pic'      => $this->input->post('nama_pic', true),
                'no_hp'         => $this->input->post('no_hp', true),
                'email'         => $this->input->post('email', true),
                'alamat'        => $this->input->post('alamat', true)
            ];

            $this->klien_model->insert_klien($data);

            $this->load->model('Dashboard_model');
            $this->Dashboard_model->log_activity('klien', 'Klien baru ditambahkan: ' . $data['nama_klien']);

            $this->session->set_flashdata('success', 'Data klien berhasil ditambahkan!');
            redirect('klien');
        }
    }

    public function detail($id) {
        $data['klien'] = $this->klien_model->get_klien_by_id($id);
        if (!$data['klien']) {
            show_404();
        }

        $data['title']        = 'Detail Klien - ' . $data['klien']->nama_klien;
        $data['list_project'] = $this->klien_model->get_project_by_klien($id);
        $data['list_dokumen'] = $this->klien_model->get_dokumen_by_klien($id);

        $this->load->view('content/klien/detail', $data);
    }

    public function hapus($id) {
        if ($id) {
            $this->klien_model->delete_klien($id);
            $this->session->set_flashdata('success', 'Data klien berhasil dihapus!');
        }
        redirect('klien');
    }

    public function edit($id) {
        $data['klien'] = $this->klien_model->get_klien_by_id($id);
        if (!$data['klien']) {
            show_404();
        }
        $data['title'] = 'Edit Klien - ' . $data['klien']->nama_klien;
        $this->load->view('content/klien/edit', $data);
    }

    public function update() {
        $this->form_validation->set_rules('nama_klien', 'Nama Klien', 'required');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('nama_pic', 'Nama PIC', 'required');
        $this->form_validation->set_rules('no_hp', 'Nomor HP', 'required');

        $id = $this->input->post('id');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('klien/edit/' . $id);
        } else {
            $data = [
                'nama_klien'    => $this->input->post('nama_klien', true),
                'tanggal_mulai' => $this->input->post('tanggal_mulai', true),
                'nama_pic'      => $this->input->post('nama_pic', true),
                'no_hp'         => $this->input->post('no_hp', true),
                'email'         => $this->input->post('email', true),
                'alamat'        => $this->input->post('alamat', true)
            ];

            $this->klien_model->update_klien($id, $data);
            $this->session->set_flashdata('success', 'Data klien berhasil diperbarui!');
            redirect('klien');
        }
    }
}