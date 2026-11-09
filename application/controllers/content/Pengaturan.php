<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Pengaturan_model', 'pengaturan_model');
    }

    public function index() {
        $data = [
            'title'    => 'Pengaturan - Tridatu',
            'content'  => 'content/pengaturan/index',
            'settings' => $this->pengaturan_model->get_settings(),
            'types'    => $this->pengaturan_model->get_all_types()
        ];
        $this->load->view('main', $data);
    }

    // Proses Simpan Informasi Perusahaan
    public function update_info() {
        $update_data = [
            'nama_perusahaan' => $this->input->post('nama_perusahaan', TRUE),
            'alamat'          => $this->input->post('alamat', TRUE),
            'no_telp'         => $this->input->post('no_telp', TRUE),
            'email'           => $this->input->post('email', TRUE),
        ];

        // Handle upload logo di form informasi
        if (!empty($_FILES['logo']['name'])) {
            $config['upload_path']   = FCPATH . 'uploads/settings/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = 2048; // 2MB
            $config['encrypt_name']  = TRUE;
            
            if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, TRUE);
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('logo')) {
                $up = $this->upload->data();
                $update_data['logo'] = 'uploads/settings/' . $up['file_name'];
                
                // Hapus logo lama
                $old = $this->pengaturan_model->get_settings();
                if ($old->logo && file_exists(FCPATH . $old->logo)) @unlink(FCPATH . $old->logo);
            }
        }

        $this->pengaturan_model->update_settings($update_data);
        $this->session->set_flashdata('success', 'Informasi perusahaan berhasil disimpan!');
        redirect('pengaturan');
    }

    // Proses Simpan Kop Surat & Tanda Tangan
    public function update_assets() {
        $update_data = [];
        $files = ['kop_surat', 'ttd'];
        $old_data = $this->pengaturan_model->get_settings();

        foreach ($files as $file) {
            if (!empty($_FILES[$file]['name'])) {
                $config['upload_path']   = FCPATH . 'uploads/settings/';
                $config['allowed_types'] = 'jpg|jpeg|png'; // Sesuai instruksi gambar: PNG atau JPG
                $config['max_size']      = 2048; // Maksimal 2MB
                $config['encrypt_name']  = TRUE;
                
                if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, TRUE);
                $this->load->library('upload', $config);

                if ($this->upload->do_upload($file)) {
                    $up = $this->upload->data();
                    $update_data[$file] = 'uploads/settings/' . $up['file_name'];
                    
                    // Hapus file lama
                    if ($old_data->$file && file_exists(FCPATH . $old_data->$file)) {
                        @unlink(FCPATH . $old_data->$file);
                    }
                } else {
                    $this->session->set_flashdata('error', 'Gagal upload ' . $file . ': ' . strip_tags($this->upload->display_errors()));
                    redirect('pengaturan');
                }
            }
        }

        if (!empty($update_data)) {
            $this->pengaturan_model->update_settings($update_data);
            $this->session->set_flashdata('success', 'Aset dokumen berhasil diperbarui!');
        }
        redirect('pengaturan');
    }

    // Hapus Kop Surat / TTD
    public function hapus_asset($type) {
        if (in_array($type, ['kop_surat', 'ttd', 'logo'])) {
            $old_data = $this->pengaturan_model->get_settings();
            if ($old_data->$type && file_exists(FCPATH . $old_data->$type)) {
                @unlink(FCPATH . $old_data->$type);
            }
            $this->pengaturan_model->update_settings([$type => NULL]);
            $this->session->set_flashdata('success', 'File berhasil dihapus!');
        }
        redirect('pengaturan');
    }

    // === AJAX: Kelola Jenis Dokumen ===
    public function simpan_jenis() {
        $id = $this->input->post('id');
        $name = $this->input->post('name', TRUE);

        if ($id) {
            $this->pengaturan_model->update_type($id, $name);
            $msg = 'Jenis dokumen berhasil diperbarui!';
        } else {
            $this->pengaturan_model->insert_type($name);
            $msg = 'Jenis dokumen berhasil ditambahkan!';
        }
        echo json_encode(['status' => true, 'msg' => $msg]);
    }

    public function hapus_jenis($id) {
        $this->pengaturan_model->delete_type($id);
        $this->session->set_flashdata('success', 'Jenis dokumen berhasil dihapus!');
        redirect('pengaturan');
    }
}