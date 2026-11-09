<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokumen extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Dokumen_model', 'dokumen_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url', 'string']);
    }

    public function index() {
        $data = [
            'title'     => 'Dokumen Kerja Sama - Tridatu',
            'content'   => 'content/dokumen/index',
            'documents' => $this->dokumen_model->get_all_documents()
        ];
        $this->load->view('main', $data);
    }

    public function tambah() {
        $data = [
            'title'  => 'Tambah Dokumen - Tridatu',
            'klien'  => $this->dokumen_model->get_klien()
        ];
        $this->load->view('content/dokumen/tambah', $data);
    }

    public function get_projects() {
        $client_id = (int) $this->input->post('client_id');
        if (!$client_id) {
            return $this->_json(['status' => false, 'data' => []]);
        }
        $projects = $this->dokumen_model->get_project_by_klien($client_id);
        return $this->_json(['status' => true, 'data' => $projects]);
    }

    public function simpan() {
        $this->form_validation->set_rules('nama_dokumen', 'Nama Dokumen', 'required|max_length[200]');
        $this->form_validation->set_rules('client_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'required|integer'); 
        $this->form_validation->set_rules('jenis_dokumen', 'Jenis Dokumen', 'required|max_length[50]');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('tanggal_berakhir', 'Tanggal Berakhir', 'required');
        $this->form_validation->set_rules('storage_type', 'Tipe Penyimpanan', 'required|in_list[local,cloud]');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->is_ajax_request()) {
                return $this->_json(['status' => false, 'msg' => strip_tags(validation_errors())]);
            }
            $this->session->set_flashdata('error', validation_errors());
            redirect('dokumen/tambah');
        }

        $storage_type = $this->input->post('storage_type');

        if ($storage_type === 'cloud' && empty($_FILES['file_dokumen']['name'])) {
            $msg = 'File wajib diunggah untuk penyimpanan Server Sistem.';
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $msg]);
            $this->session->set_flashdata('error', $msg); redirect('dokumen/tambah');
        }
        if ($storage_type === 'local' && !$this->input->post('file_name_local')) {
            $msg = 'File lokal wajib dipilih.';
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $msg]);
            $this->session->set_flashdata('error', $msg); redirect('dokumen/tambah');
        }

        $file_data = [
            'file_path'  => null, 'file_name'  => null, 'file_hash'  => null,
            'file_size'  => null, 'mime_type'  => null, 'device_id'  => null,
        ];

        if ($storage_type === 'cloud') {
            $config = [
                'upload_path'   => FCPATH . 'writable/uploads/documents/cloud/',
                'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
                'max_size'      => 10240,
                'encrypt_name'  => TRUE,
            ];
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, TRUE);
            }
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('file_dokumen')) {
                $msg = strip_tags($this->upload->display_errors());
                if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $msg]);
                $this->session->set_flashdata('error', $msg); redirect('dokumen/tambah');
            }
            
            $up = $this->upload->data();
            $rel = 'writable/uploads/documents/cloud/' . $up['file_name'];

            $file_data['file_path'] = $rel;
            $file_data['file_name'] = $up['orig_name'];
            $file_data['file_hash'] = hash_file('sha256', FCPATH . $rel);
            $file_data['file_size'] = $up['file_size'] * 1024;
            $file_data['mime_type'] = $up['file_type'];
        }

        if ($storage_type === 'local') {
            $file_data['file_name'] = $this->input->post('file_name_local', TRUE);
            $file_data['file_hash'] = $this->input->post('file_hash_local', TRUE);
            $file_data['file_size'] = (int) $this->input->post('file_size_local');
            $file_data['mime_type'] = $this->input->post('file_type_local', TRUE);
            $file_data['device_id'] = $this->input->post('device_id', TRUE);
        }

        $data = array_merge($file_data, [
            'client_id'        => (int) $this->input->post('client_id'),
            'project_id'       => (int) $this->input->post('project_id'),
            'nama_dokumen'     => $this->input->post('nama_dokumen', TRUE),
            'jenis_dokumen'    => $this->input->post('jenis_dokumen', TRUE),
            'storage_type'     => $storage_type,
            'tanggal_mulai'    => $this->input->post('tanggal_mulai'),
            'tanggal_berakhir' => $this->input->post('tanggal_berakhir'),
        ]);

        $id = $this->dokumen_model->insert_document($data);

        if ($this->input->is_ajax_request()) {
            return $this->_json(['status' => true, 'id' => $id, 'msg' => 'Dokumen berhasil disimpan!']);
        }

        $this->session->set_flashdata('success', 'Dokumen berhasil ditambahkan!');
        redirect('dokumen');
    }

    public function sync_to_cloud() {
        if ($this->input->method() !== 'post') show_404();

        $id        = (int) $this->input->post('id');
        $device_id = $this->input->post('device_id', TRUE);
        $file_hash = $this->input->post('file_hash', TRUE);

        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc) return $this->_json(['status' => false, 'msg' => 'Dokumen tidak ditemukan']);

        if ($doc->storage_type === 'cloud') {
            return $this->_json(['status' => false, 'msg' => 'Dokumen sudah di server sistem']);
        }
        if ($doc->device_id !== $device_id) {
            return $this->_json(['status' => false, 'msg' => 'Bukan perangkat asal dokumen ini']);
        }
        if (empty($_FILES['file']['name'])) {
            return $this->_json(['status' => false, 'msg' => 'File tidak dikirim']);
        }

        $config = [
            'upload_path'   => FCPATH . 'writable/uploads/documents/cloud/',
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size'      => 10240,
            'encrypt_name'  => TRUE,
        ];
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            return $this->_json(['status' => false, 'msg' => strip_tags($this->upload->display_errors())]);
        }

        $up  = $this->upload->data();
        $rel = 'writable/uploads/documents/cloud/' . $up['file_name'];

        $new_hash = hash_file('sha256', FCPATH . $rel);
        if ($file_hash && !hash_equals($file_hash, $new_hash)) {
            @unlink(FCPATH . $rel);
            return $this->_json(['status' => false, 'msg' => 'File berbeda dari yang tersimpan di lokal!']);
        }

        $this->dokumen_model->update_file($id, [
            'storage_type'          => 'cloud',
            'file_path'             => $rel,
            'file_hash'             => $new_hash,
            'file_size'             => $up['file_size'] * 1024,
            'mime_type'             => $up['file_type'],
            'device_id'             => NULL,
            'uploaded_to_server_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->_json(['status' => true, 'msg' => 'Berhasil diunggah ke Server Sistem!']);
    }

    // Endpoint Download (Paksa Unduh)
    public function unduh($id) {
        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc || $doc->storage_type !== 'cloud' || !$doc->file_path) show_404();

        $path = FCPATH . $doc->file_path;
        if (!is_file($path)) show_404();

        $this->load->helper('download');
        force_download($doc->file_name ?: basename($path), file_get_contents($path));
    }

    // Endpoint Pratinjau (Inline) - SUDAH DIPERBAIKI
    public function preview($id) {
        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc || $doc->storage_type !== 'cloud' || !$doc->file_path) show_404();

        $path = FCPATH . $doc->file_path;
        if (!is_file($path)) show_404();

        // 1. Bersihkan output buffer CodeIgniter agar PDF tidak rusak
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 2. Tentukan MIME Type dengan aman
        $mime = $doc->mime_type;
        if (!$mime || $mime === 'application/octet-stream') {
            $mime = mime_content_type($path);
        }
        if (!$mime) {
            $mime = 'application/pdf'; // Fallback jika deteksi gagal
        }
        
        // 3. Set header untuk Pratinjau
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $doc->file_name . '"');
        header('Content-Length: ' . filesize($path));
        header('Accept-Ranges: bytes'); // Penting untuk PDF Viewer
        
        readfile($path);
        exit;
    }

    public function hapus($id) {
        $doc = $this->dokumen_model->get_by_id($id);
        if ($doc) {
            if ($doc->storage_type === 'cloud' && $doc->file_path && is_file(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }
            $this->dokumen_model->delete_document($id);
            $this->session->set_flashdata('success', 'Dokumen berhasil dihapus!');
        }
        redirect('dokumen');
    }

    private function _json($arr) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($arr));
    }

    public function detail($id) {
        $doc = $this->dokumen_model->get_detail($id);
        
        if (!$doc) {
            show_404();
        }

        $currentDeviceId = $_COOKIE['tridatu_device_id'] ?? '';
        $isLocal = ($doc->storage_type === 'local');
        $isOwnLocal = ($isLocal && $doc->device_id === $currentDeviceId);
        $isOtherLocal = ($isLocal && $doc->device_id !== $currentDeviceId);

        if ($isOtherLocal) {
            $this->session->set_flashdata('error', 'Dokumen ini tersimpan di Perangkat Lain dan tidak dapat diakses dari sini.');
            redirect('dokumen');
        }

        $data = [
            'title' => 'Detail Dokumen - Tridatu',
            'doc'   => $doc,
            'currentDeviceId' => $currentDeviceId
        ];
        $this->load->view('content/dokumen/detail', $data);
    }
}