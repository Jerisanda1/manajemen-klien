<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Autentikasi sudah otomatis ditangani oleh MY_Controller
        $this->load->model('Project_model');
        $this->load->model('Dashboard_model'); // Untuk Log Aktivitas
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
    }

    private function render($view, $data)
    {
        $data['content'] = 'content/project/' . $view;
        $this->load->view('main', $data);
    }

    public function index()
    {
        $q       = trim((string) $this->input->get('q', TRUE));
        $klien   = (string) $this->input->get('klien', TRUE);
        $status  = (string) $this->input->get('status', TRUE);
        $perPage = 7;
        $page    = max(1, (int) $this->input->get('page'));

        $total      = $this->Project_model->count_filtered($q, $klien, $status);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $this->render('index', [
            'title'       => 'Manajemen Project',
            'projects'    => $this->Project_model->get_list($q, $klien, $status, $perPage, ($page - 1) * $perPage),
            'klienList'   => $this->Project_model->get_klien_options(),
            'jmlProject'  => $this->Project_model->count_all(),
            'jmlBerjalan' => $this->Project_model->count_berjalan(),
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => $totalPages,
            'q'           => $q,
            'fKlien'      => $klien,
            'fStatus'     => $status,
        ]);
    }

    public function detail($id)
    {
        $project = $this->Project_model->get($id);
        if (!$project) show_404();

        $this->render('detail', [
            'title'   => 'Detail Project',
            'project' => $project,
        ]);
    }

    public function tambah()
    {
        $this->render('form', [
            'title'     => 'Tambah Project',
            'project'   => null,
            'klienList' => $this->Project_model->get_klien_options(),
            'from'      => 'list',
            'action'    => site_url('project/simpan'),
        ]);
    }

    public function simpan()
    {
        if ($this->input->method() !== 'post') redirect('project');

        if (!$this->validasi()) {
            return $this->tambah();
        }

        $input = $this->ambil_input();
        $this->Project_model->insert($input);
        
        // Log Aktivitas ke Dashboard
        $this->Dashboard_model->log_activity('project', 'Project baru dibuat: ' . $input['nama_project']);

        $this->session->set_flashdata('success', 'Project berhasil ditambahkan.');
        redirect('project');
    }

    public function edit($id)
    {
        $project = $this->Project_model->get($id);
        if (!$project) show_404();

        $from = $this->input->get('from') === 'detail' ? 'detail' : 'list';

        $this->render('form', [
            'title'     => 'Edit Project',
            'project'   => $project,
            'klienList' => $this->Project_model->get_klien_options(),
            'from'      => $from,
            'action'    => site_url('project/update/' . (int) $id) . ($from === 'detail' ? '?from=detail' : ''),
        ]);
    }

    public function update($id)
    {
        if ($this->input->method() !== 'post') redirect('project');
        if (!$this->Project_model->get($id)) show_404();

        if (!$this->validasi()) {
            return $this->edit($id);
        }

        $this->Project_model->update($id, $this->ambil_input());
        $this->session->set_flashdata('success', 'Project berhasil diperbarui.');

        if ($this->input->get('from') === 'detail') {
            redirect('project/detail/' . (int) $id);
        }
        redirect('project');
    }

    public function hapus()
    {
        if ($this->input->method() !== 'post') redirect('project');
        $this->Project_model->delete($this->input->post('id', TRUE));
        $this->session->set_flashdata('success', 'Project berhasil dihapus.');
        redirect('project');
    }

    private function validasi()
    {
        $this->form_validation->set_rules('nama_project', 'Nama project', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[Berjalan,Selesai,Ditunda]');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal mulai', 'trim|callback__cek_tanggal');
        $this->form_validation->set_rules('tanggal_selesai', 'Tanggal selesai', 'trim');
        return $this->form_validation->run();
    }

    public function _cek_tanggal($mulai)
    {
        $selesai = $this->input->post('tanggal_selesai');
        if ($mulai && $selesai && strtotime($selesai) < strtotime($mulai)) {
            $this->form_validation->set_message('_cek_tanggal', 'Tanggal selesai tidak boleh sebelum tanggal mulai.');
            return FALSE;
        }
        return TRUE;
    }

    private function ambil_input()
    {
        $mulai   = $this->input->post('tanggal_mulai', TRUE);
        $selesai = $this->input->post('tanggal_selesai', TRUE);

        return [
            'klien_id'        => (int) $this->input->post('klien_id'),
            'nama_project'    => $this->input->post('nama_project', TRUE),
            'deskripsi'       => $this->input->post('deskripsi', TRUE),
            'tanggal_mulai'   => $mulai ?: NULL,
            'tanggal_selesai' => $selesai ?: NULL,
            'status'          => $this->input->post('status', TRUE),
        ];
    }

    public function status($id)
    {
        if ($this->input->method() !== 'post') redirect('project/detail/' . (int) $id);
        if (!$this->Project_model->get($id)) show_404();

        $status = (string) $this->input->post('status', TRUE);
        if (in_array($status, ['Berjalan', 'Selesai', 'Ditunda'], TRUE)) {
            $this->Project_model->update_status($id, $status);
            $this->session->set_flashdata('success', 'Status project berhasil diperbarui.');
        }
        redirect('project/detail/' . (int) $id);
    }
}