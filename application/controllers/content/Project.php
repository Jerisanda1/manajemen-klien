<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Project_model');
        $this->load->model('Dashboard_model');
        $this->load->library('form_validation');

        $this->form_validation->set_error_delimiters('', '');
    }

    private function renderWithLayout($view, $data)
    {
        $data['content'] = 'content/project/' . $view;
        $this->load->view('main', $data);
    }

    // =========================================
    // INDEX PROJECT
    // =========================================
    public function index()
    {
        $q      = trim((string) $this->input->get('q', TRUE));
        $klien  = (string) $this->input->get('klien', TRUE);
        $status = (string) $this->input->get('status', TRUE);

        // Menampilkan 5 data per halaman
        $perPage = 5;
        $page    = max(1, (int) $this->input->get('page'));

        $total      = $this->Project_model->count_filtered($q, $klien, $status);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $this->renderWithLayout('index', [
            'title'       => 'Manajemen Project',
            'projects'    => $this->Project_model->get_list(
                $q,
                $klien,
                $status,
                $perPage,
                ($page - 1) * $perPage
            ),
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

    // =========================================
    // DETAIL PROJECT + DOKUMEN + PENAWARAN + INVOICE
    // =========================================
    public function detail($id)
    {
        $project = $this->Project_model->get($id);

        if (!$project) {
            show_404();
        }

        // Mengambil dokumen berdasarkan project_id
        // Setiap baris memuat: id, nama, jenis, sampai, storage_type, device_id, file_name, file_hash
        $dokumen = $this->Project_model->get_dokumen_by_project($id);

        // Mengambil penawaran berdasarkan project_id
        $penawaran = $this->Project_model->get_penawaran_by_project($id);

        // Mengambil invoice berdasarkan project_id
        $invoice = $this->Project_model->get_invoice_by_project($id);

        // Memuat halaman detail project
        $this->load->view('content/project/detail', [
            'title'             => 'Detail Project',
            'project'           => $project,
            'dokumen'           => $dokumen,
            'penawaran'         => $penawaran,
            'invoice'           => $invoice,
            // Dipakai tabel Dokumen Kerja Sama (pratinjau lokal, sinkron ke server, hapus)
            'documentCsrfToken' => $this->_get_document_csrf_token(),
            'currentDeviceId'   => $this->_get_current_device_id(),
        ]);
    }

    // =========================================
    // TAMBAH PROJECT
    // =========================================
    public function tambah()
    {
        $this->load->view('content/project/form', [
            'title'     => 'Tambah Project',
            'project'   => null,
            'klienList' => $this->Project_model->get_klien_options(),
            'from'      => 'list',
            'action'    => site_url('project/simpan'),
        ]);
    }

    // =========================================
    // SIMPAN PROJECT
    // =========================================
    public function simpan()
    {
        if ($this->input->method() !== 'post') {
            redirect('project');
        }

        if (!$this->validasi()) {
            return $this->tambah();
        }

        $input = $this->ambil_input();

        $this->Project_model->insert($input);

        $this->Dashboard_model->log_activity(
            'project',
            'Project baru dibuat: ' . $input['nama_project']
        );

        $this->session->set_flashdata(
            'success',
            'Project berhasil ditambahkan.'
        );

        redirect('project');
    }

    // =========================================
    // EDIT PROJECT
    // =========================================
    public function edit($id)
    {
        $project = $this->Project_model->get($id);

        if (!$project) {
            show_404();
        }

        $from = $this->input->get('from') === 'detail'
            ? 'detail'
            : 'list';

        $this->load->view('content/project/form', [
            'title'     => 'Edit Project',
            'project'   => $project,
            'klienList' => $this->Project_model->get_klien_options(),
            'from'      => $from,
            'action'    => site_url('project/update/' . (int) $id)
                . ($from === 'detail' ? '?from=detail' : ''),
        ]);
    }

    // =========================================
    // UPDATE PROJECT
    // =========================================
    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('project');
        }

        if (!$this->Project_model->get($id)) {
            show_404();
        }

        if (!$this->validasi()) {
            return $this->edit($id);
        }

        $this->Project_model->update($id, $this->ambil_input());

        $this->session->set_flashdata(
            'success',
            'Project berhasil diperbarui.'
        );

        if ($this->input->get('from') === 'detail') {
            redirect('project/detail/' . (int) $id);
        }

        redirect('project');
    }

    // =========================================
    // HAPUS PROJECT
    // =========================================
    public function hapus()
    {
        if ($this->input->method() !== 'post') {
            redirect('project');
        }

        $this->Project_model->delete(
            $this->input->post('id', TRUE)
        );

        $this->session->set_flashdata(
            'success',
            'Project berhasil dihapus.'
        );

        redirect('project');
    }

    // =========================================
    // VALIDASI PROJECT
    // =========================================
    private function validasi()
    {
        $this->form_validation->set_rules(
            'nama_project',
            'Nama project',
            'required|trim|max_length[150]'
        );

        $this->form_validation->set_rules(
            'klien_id',
            'Klien',
            'required|integer'
        );

        $this->form_validation->set_rules(
            'status',
            'Status',
            'required|in_list[Draft,Berjalan,Selesai,Batal]'
        );

        $this->form_validation->set_rules(
            'tanggal_mulai',
            'Tanggal mulai',
            'trim|callback__cek_tanggal'
        );

        $this->form_validation->set_rules(
            'tanggal_selesai',
            'Tanggal selesai',
            'trim'
        );

        return $this->form_validation->run();
    }

    // =========================================
    // VALIDASI TANGGAL
    // =========================================
    public function _cek_tanggal($mulai)
    {
        $selesai = $this->input->post('tanggal_selesai');

        if (
            $mulai &&
            $selesai &&
            strtotime($selesai) < strtotime($mulai)
        ) {
            $this->form_validation->set_message(
                '_cek_tanggal',
                'Tanggal selesai tidak boleh sebelum tanggal mulai.'
            );

            return FALSE;
        }

        return TRUE;
    }

    // =========================================
    // MENGAMBIL INPUT FORM
    // =========================================
    private function ambil_input()
    {
        $mulai   = $this->input->post('tanggal_mulai', TRUE);
        $selesai = $this->input->post('tanggal_selesai', TRUE);

        return [
            'klien_id'        => (int) $this->input->post('klien_id'),
            'nama_project'     => $this->input->post('nama_project', TRUE),
            'deskripsi'        => $this->input->post('deskripsi', TRUE),
            'tanggal_mulai'    => $mulai ?: NULL,
            'tanggal_selesai'  => $selesai ?: NULL,
            'status'           => $this->input->post('status', TRUE),
        ];
    }

    // =========================================
    // UPDATE STATUS PROJECT
    // =========================================
    public function status($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('project/detail/' . (int) $id);
        }

        if (!$this->Project_model->get($id)) {
            show_404();
        }

        $status = (string) $this->input->post('status', TRUE);

        if (in_array($status, ['Draft', 'Berjalan', 'Selesai', 'Batal'], TRUE)) {
            $this->Project_model->update_status($id, $status);

            $this->session->set_flashdata(
                'success',
                'Status project berhasil diperbarui.'
            );
        }

        redirect('project/detail/' . (int) $id);
    }

    // =========================================
    // HELPER MODUL DOKUMEN (token CSRF & identitas perangkat)
    // Memakai kunci session yang sama dengan controller Dokumen,
    // sehingga token dan perangkat konsisten di kedua halaman.
    // =========================================
    private function _get_document_csrf_token()
    {
        $token = $this->session->userdata('dokumen_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata('dokumen_csrf_token', $token);
        }
        return $token;
    }

    private function _get_current_device_id()
    {
        $deviceId = $this->session->userdata('dokumen_device_id');
        if (!$deviceId) {
            $cookieDeviceId = (string) $this->input->cookie('tridatu_device_id', TRUE);
            if ($this->_is_valid_device_id($cookieDeviceId)) {
                $deviceId = strtolower($cookieDeviceId);
            } else {
                return '';
            }
            $this->session->set_userdata('dokumen_device_id', $deviceId);
        }
        return $deviceId;
    }

    private function _is_valid_device_id($deviceId)
    {
        return preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/i', (string) $deviceId) === 1;
    }
}