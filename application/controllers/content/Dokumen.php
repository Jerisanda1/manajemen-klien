<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokumen extends MY_Controller {
    private $documentCsrfToken;
    private $currentDeviceId;

    public function __construct() {
        parent::__construct();
        $this->load->model('Dokumen_model', 'dokumen_model');
        $this->load->model('Dashboard_model');
        $this->load->library('form_validation');
        $this->load->helper(['form', 'url', 'string']);
        $this->documentCsrfToken = $this->_get_document_csrf_token();
        $this->currentDeviceId = $this->_get_current_device_id();
    }

    public function index() {
        $q       = trim((string) $this->input->get('q', TRUE));
        $jenis   = (string) $this->input->get('jenis', TRUE);
        
        $perPage = 5; 
        $page    = max(1, (int) $this->input->get('page'));

        $total      = $this->dokumen_model->count_filtered($q, $jenis);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $data = [
            'title'         => 'Dokumen Kerja Sama - Tridatu',
            'content'       => 'content/dokumen/index',
            'documents'     => $this->dokumen_model->get_list($q, $jenis, $perPage, ($page - 1) * $perPage),
            'jenis_dokumen' => $this->dokumen_model->get_jenis_dokumen(),
            'total'         => $total,
            'page'          => $page,
            'perPage'       => $perPage,
            'totalPages'    => $totalPages,
            'q'             => $q,
            'fJenis'        => $jenis,
            'documentCsrfToken' => $this->documentCsrfToken,
            'currentDeviceId' => $this->currentDeviceId,
        ];
        $this->load->view('main', $data);
    }

    public function tambah() {
        $data = [
            'title'         => 'Tambah Dokumen - Tridatu',
            'klien'         => $this->dokumen_model->get_klien(),
            'jenis_dokumen' => $this->dokumen_model->get_jenis_dokumen(),
            'documentCsrfToken' => $this->documentCsrfToken,
            'currentDeviceId' => $this->currentDeviceId,
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
        if ($this->input->method(TRUE) !== 'POST') show_404();
        if (!$this->_verify_document_csrf()) return $this->_csrf_error();

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
        $validationError = $this->_validate_project_and_dates(
            (int) $this->input->post('client_id'),
            (int) $this->input->post('project_id'),
            $this->input->post('tanggal_mulai'),
            $this->input->post('tanggal_berakhir')
        );
        if ($validationError !== '') {
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $validationError]);
            $this->session->set_flashdata('error', $validationError);
            redirect('dokumen/tambah');
        }

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
            $folder_bulan = date('Y/m'); 
            $upload_path  = FCPATH . 'uploads/dokumen/' . $folder_bulan . '/';

            $config = [
                'upload_path'   => $upload_path,
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
            $rel = 'uploads/dokumen/' . $folder_bulan . '/' . $up['file_name'];

            $file_data['file_path'] = $rel;
            $file_data['file_name'] = $up['orig_name'];
            $file_data['file_hash'] = hash_file('sha256', FCPATH . $rel);
            $file_data['file_size'] = $up['file_size'] * 1024;
            $file_data['mime_type'] = $up['file_type'];
        }

        if ($storage_type === 'local') {
            $postedDeviceId = strtolower((string) $this->input->post('device_id', TRUE));
            if ($this->currentDeviceId === '') {
                $this->currentDeviceId = $this->_is_valid_device_id($postedDeviceId)
                    ? $postedDeviceId
                    : $this->_generate_device_id();
                $this->session->set_userdata('dokumen_device_id', $this->currentDeviceId);
            }
            $file_data['file_name'] = $this->input->post('file_name_local', TRUE);
            $file_data['file_hash'] = $this->input->post('file_hash_local', TRUE);
            $file_data['file_size'] = (int) $this->input->post('file_size_local');
            $file_data['mime_type'] = $this->input->post('file_type_local', TRUE);
            $file_data['device_id'] = $this->currentDeviceId;
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

        $this->Dashboard_model->log_activity('dokumen', 'Dokumen baru diunggah: ' . $data['nama_dokumen']);
        $this->session->set_flashdata('success', 'Dokumen berhasil ditambahkan!');

        if ($this->input->is_ajax_request()) {
            return $this->_json([
                'status'   => true,
                'id'       => $id,
                'msg'      => 'Dokumen berhasil disimpan!',
                'redirect' => site_url('dokumen')
            ]);
        }

        redirect('dokumen');
    }

    public function edit($id) {
        $doc = $this->dokumen_model->get_detail($id);
        if (!$doc) show_404();

        $isLocal = ($doc->storage_type === 'local');
        $isOtherLocal = ($isLocal && $doc->device_id !== $this->currentDeviceId);

        if ($isOtherLocal) {
            $this->session->set_flashdata('error', 'Dokumen ini tersimpan di Perangkat Lain dan tidak dapat diedit.');
            redirect('dokumen');
        }

        $data = [
            'title'         => 'Edit Dokumen - Tridatu',
            'doc'           => $doc,
            'klien'         => $this->dokumen_model->get_klien(),
            'jenis_dokumen' => $this->dokumen_model->get_jenis_dokumen(),
            'documentCsrfToken' => $this->documentCsrfToken,
            'currentDeviceId' => $this->currentDeviceId
        ];
        $this->load->view('content/dokumen/edit', $data);
    }

    public function update() {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        if (!$this->_verify_document_csrf()) return $this->_csrf_error();

        $id = (int) $this->input->post('id');
        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc) show_404();
        if ($doc->storage_type === 'local' && $doc->device_id !== $this->currentDeviceId) {
            return $this->_document_access_denied('Dokumen ini tersimpan di Perangkat Lain dan tidak dapat diedit.');
        }

        $this->form_validation->set_rules('nama_dokumen', 'Nama Dokumen', 'required|max_length[200]');
        $this->form_validation->set_rules('client_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'required|integer');
        $this->form_validation->set_rules('jenis_dokumen', 'Jenis Dokumen', 'required|max_length[50]');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('tanggal_berakhir', 'Tanggal Berakhir', 'required');
        $this->form_validation->set_rules('storage_type', 'Tipe Penyimpanan', 'required|in_list[local,cloud]');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => strip_tags(validation_errors())]);
            $this->session->set_flashdata('error', validation_errors());
            redirect('dokumen/edit/'.$id);
        }

        $storage_type = $this->input->post('storage_type');
        $validationError = $this->_validate_project_and_dates(
            (int) $this->input->post('client_id'),
            (int) $this->input->post('project_id'),
            $this->input->post('tanggal_mulai'),
            $this->input->post('tanggal_berakhir')
        );
        if ($validationError !== '') {
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $validationError]);
            $this->session->set_flashdata('error', $validationError);
            redirect('dokumen/edit/'.$id);
        }

        $update_data = [
            'client_id'        => (int) $this->input->post('client_id'),
            'project_id'       => (int) $this->input->post('project_id'),
            'nama_dokumen'     => $this->input->post('nama_dokumen', TRUE),
            'jenis_dokumen'    => $this->input->post('jenis_dokumen', TRUE),
            'storage_type'     => $storage_type,
            'tanggal_mulai'    => $this->input->post('tanggal_mulai'),
            'tanggal_berakhir' => $this->input->post('tanggal_berakhir'),
        ];

        // ============================================================
        // BUG FIX: Reset flag reminder kalau tanggal berakhir berubah.
        // Supaya dokumen yang diperpanjang akan di-reminder ulang.
        // ============================================================
        if ($doc->tanggal_berakhir !== $this->input->post('tanggal_berakhir')) {
            $update_data['last_reminder_sent_at'] = NULL;
            $update_data['expired_notified_at']   = NULL;
            $update_data['wa_reminder_sent_at']   = NULL;
            $update_data['wa_expired_notified_at'] = NULL;
        }

        $is_new_cloud_file = ($storage_type === 'cloud' && !empty($_FILES['file_dokumen']['name']));
        $is_new_local_file = ($storage_type === 'local' && $this->input->post('file_name_local') && $this->input->post('file_name_local') !== $doc->file_name);

        $storage_changed = ($doc->storage_type !== $storage_type);
        $has_new_file    = ($is_new_cloud_file || $is_new_local_file);

        if ($storage_changed && !$has_new_file) {
            $msg = 'Tipe penyimpanan berubah. Silakan pilih file baru terlebih dahulu.';
            if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $msg]);
            $this->session->set_flashdata('error', $msg);
            redirect('dokumen/edit/'.$id);
        }

        if ($is_new_cloud_file) {
            $folder_bulan = date('Y/m'); 
            $upload_path  = FCPATH . 'uploads/dokumen/' . $folder_bulan . '/';

            $config = [
                'upload_path'   => $upload_path,
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
                $this->session->set_flashdata('error', $msg); redirect('dokumen/edit/'.$id);
            }

            $up = $this->upload->data();
            $rel = 'uploads/dokumen/' . $folder_bulan . '/' . $up['file_name'];

            if ($doc->storage_type === 'cloud' && $doc->file_path && is_file(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }

            $update_data['file_path'] = $rel;
            $update_data['file_name'] = $up['orig_name'];
            $update_data['file_hash'] = hash_file('sha256', FCPATH . $rel);
            $update_data['file_size'] = $up['file_size'] * 1024;
            $update_data['mime_type'] = $up['file_type'];
            $update_data['device_id'] = NULL;

        } elseif ($is_new_local_file) {
            if ((string) $this->input->post('device_id', TRUE) !== $this->currentDeviceId) {
                $msg = 'Identitas perangkat tidak valid. Muat ulang halaman lalu coba kembali.';
                if ($this->input->is_ajax_request()) return $this->_json(['status' => false, 'msg' => $msg]);
                $this->session->set_flashdata('error', $msg);
                redirect('dokumen/edit/'.$id);
            }

            $update_data['file_path'] = NULL;
            $update_data['file_name'] = $this->input->post('file_name_local', TRUE);
            $update_data['file_hash'] = $this->input->post('file_hash_local', TRUE);
            $update_data['file_size'] = (int) $this->input->post('file_size_local');
            $update_data['mime_type'] = $this->input->post('file_type_local', TRUE);
            $update_data['device_id'] = $this->currentDeviceId;

            if ($doc->storage_type === 'cloud' && $doc->file_path && is_file(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }
        }

        $this->dokumen_model->update_document($id, $update_data);

        $this->Dashboard_model->log_activity('dokumen', 'Dokumen diperbarui: ' . $update_data['nama_dokumen']);
        $this->session->set_flashdata('success', 'Dokumen berhasil diperbarui!');

        if ($this->input->is_ajax_request()) {
            return $this->_json([
                'status'   => true,
                'id'       => $id,
                'msg'      => 'Dokumen berhasil diperbarui!',
                'redirect' => site_url('dokumen')
            ]);
        }

        redirect('dokumen');
    }

    public function sync_to_cloud() {
        if ($this->input->method() !== 'post') show_404();
        if (!$this->_verify_document_csrf()) return $this->_csrf_error();

        $id        = (int) $this->input->post('id');
        $device_id = $this->input->post('device_id', TRUE);

        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc) return $this->_json(['status' => false, 'msg' => 'Dokumen tidak ditemukan']);
        if ($doc->storage_type === 'cloud') return $this->_json(['status' => false, 'msg' => 'Dokumen sudah di server sistem']);
        if ((string) $doc->device_id !== $this->currentDeviceId || (string) $device_id !== $this->currentDeviceId) {
            return $this->_json(['status' => false, 'msg' => 'Bukan perangkat asal dokumen ini']);
        }
        if (!preg_match('/\A[a-f0-9]{64}\z/i', (string) $doc->file_hash)) {
            return $this->_json(['status' => false, 'msg' => 'Hash file lokal tidak valid; sinkronisasi dibatalkan.']);
        }
        if (empty($_FILES['file']['name'])) return $this->_json(['status' => false, 'msg' => 'File tidak dikirim']);

        $folder_bulan = date('Y/m'); 
        $upload_path  = FCPATH . 'uploads/dokumen/' . $folder_bulan . '/';

        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx|jpg|jpeg|png',
            'max_size'      => 10240,
            'encrypt_name'  => TRUE,
        ];

        if (!is_dir($config['upload_path'])) {
            if (!@mkdir($config['upload_path'], 0755, TRUE) && !is_dir($config['upload_path'])) {
                return $this->_json(['status' => false, 'msg' => 'Gagal membuat folder upload. Periksa permission folder uploads/']);
            }
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            return $this->_json(['status' => false, 'msg' => strip_tags($this->upload->display_errors())]);
        }

        $up  = $this->upload->data();
        $rel = 'uploads/dokumen/' . $folder_bulan . '/' . $up['file_name'];

        $new_hash = hash_file('sha256', FCPATH . $rel);
        if (!hash_equals(strtolower($doc->file_hash), $new_hash)) {
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

    public function unduh($id) {
        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc || $doc->storage_type !== 'cloud' || !$doc->file_path) show_404();

        $path = FCPATH . $doc->file_path;
        if (!is_file($path)) show_404();

        $this->load->helper('download');
        force_download($doc->file_name ?: basename($path), file_get_contents($path));
    }

    public function preview($id) {
        $doc = $this->dokumen_model->get_by_id($id);
        if (!$doc || $doc->storage_type !== 'cloud' || !$doc->file_path) show_404();

        $path = FCPATH . $doc->file_path;
        if (!is_file($path)) show_404();

        if (ob_get_level() > 0) ob_end_clean();

        $mime = $doc->mime_type;
        if (!$mime || $mime === 'application/octet-stream') {
            $mime = mime_content_type($path);
        }
        if (!$mime) $mime = 'application/pdf';

        $allowed_inline = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mime, $allowed_inline)) {
            return $this->unduh($id);
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $doc->file_name . '"');
        header('Content-Length: ' . filesize($path));
        header('Accept-Ranges: bytes');

        readfile($path);
        exit;
    }

    public function hapus($id) {
        if ($this->input->method(TRUE) !== 'POST') show_404();
        if (!$this->_verify_document_csrf()) return $this->_csrf_error();

        $doc = $this->dokumen_model->get_by_id($id);
        if ($doc) {
            $localFileName = null;
            if (
                $doc->storage_type === 'local'
                && $this->currentDeviceId !== ''
                && (string) $doc->device_id === $this->currentDeviceId
            ) {
                $localFileName = $doc->file_name;
            }
            if ($doc->storage_type === 'cloud' && $doc->file_path && is_file(FCPATH . $doc->file_path)) {
                @unlink(FCPATH . $doc->file_path);
            }
            if (!$this->dokumen_model->delete_document($id)) {
                if ($this->input->is_ajax_request()) {
                    return $this->_json(['status' => false, 'msg' => 'Dokumen gagal dihapus.']);
                }
                $this->session->set_flashdata('error', 'Dokumen gagal dihapus.');
                redirect('dokumen');
            }
            $this->session->set_flashdata('success', 'Dokumen berhasil dihapus!');
            if ($this->input->is_ajax_request()) {
                return $this->_json([
                    'status' => true,
                    'redirect' => site_url('dokumen'),
                    'local_file_name' => $localFileName,
                ]);
            }
        }
        redirect('dokumen');
    }

    private function _json($arr) {
        $this->output->set_content_type('application/json')->set_output(json_encode($arr));
    }

    public function detail($id) {
        $doc = $this->dokumen_model->get_detail($id);
        if (!$doc) show_404();

        $isLocal = ($doc->storage_type === 'local');
        $isOtherLocal = ($isLocal && $doc->device_id !== $this->currentDeviceId);

        if ($isOtherLocal && $this->currentDeviceId !== '') {
            $this->session->set_flashdata('error', 'Dokumen ini tersimpan di Perangkat Lain dan tidak dapat diakses dari sini.');
            redirect('dokumen');
        }

        $data = [
            'title'           => 'Detail Dokumen - Tridatu',
            'doc'             => $doc,
            'currentDeviceId' => $this->currentDeviceId,
            'documentCsrfToken' => $this->documentCsrfToken
        ];
        $this->load->view('content/dokumen/detail', $data);
    }

    private function _get_document_csrf_token() {
        $token = $this->session->userdata('dokumen_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata('dokumen_csrf_token', $token);
        }
        return $token;
    }

    private function _get_current_device_id() {
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

    private function _is_valid_device_id($deviceId) {
        return preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/i', (string) $deviceId) === 1;
    }

    private function _generate_device_id() {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
    }

    private function _verify_document_csrf() {
        $submittedToken = (string) $this->input->post('dokumen_csrf_token', FALSE);
        return $submittedToken !== '' && hash_equals($this->documentCsrfToken, $submittedToken);
    }

    private function _csrf_error() {
        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403);
            return $this->_json(['status' => false, 'msg' => 'Sesi keamanan kedaluwarsa. Muat ulang halaman lalu coba kembali.']);
        }
        show_error('Permintaan tidak valid atau sesi keamanan telah kedaluwarsa.', 403);
    }

    private function _document_access_denied($message) {
        if ($this->input->is_ajax_request()) {
            $this->output->set_status_header(403);
            return $this->_json(['status' => false, 'msg' => $message]);
        }
        $this->session->set_flashdata('error', $message);
        redirect('dokumen');
    }

    private function _validate_project_and_dates($clientId, $projectId, $startDate, $endDate) {
        if (!$this->dokumen_model->project_belongs_to_client($projectId, $clientId)) {
            return 'Project yang dipilih tidak sesuai dengan klien.';
        }

        $start = DateTime::createFromFormat('!Y-m-d', (string) $startDate);
        $end = DateTime::createFromFormat('!Y-m-d', (string) $endDate);
        if (!$start || $start->format('Y-m-d') !== $startDate || !$end || $end->format('Y-m-d') !== $endDate) {
            return 'Tanggal dokumen harus menggunakan format yang valid (YYYY-MM-DD).';
        }
        if ($end < $start) {
            return 'Tanggal Berakhir tidak boleh lebih awal dari Tanggal Mulai.';
        }
        return '';
    }
}