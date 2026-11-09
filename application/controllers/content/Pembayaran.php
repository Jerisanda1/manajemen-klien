<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran extends CI_Controller
{
    private $statusList = [
        'penawaran' => ['Draft', 'Dikirim', 'Disetujui', 'Ditolak'],
        'invoice'   => ['Belum dibayar', 'Lunas', 'Jatuh tempo'],
    ];

    private $edit_id    = 0;
    private $tgl_dasar  = null;
    private $mode       = 'template';
    private $upload_err = '';

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('Pembayaran_model');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
    }

    /* ---------- DAFTAR ---------- */
    public function index()
    {
        $tab = (string) $this->input->get('tab', TRUE);
        if (!$this->Pembayaran_model->valid_tab($tab)) $tab = 'penawaran';

        $q       = trim((string) $this->input->get('q', TRUE));
        $klien   = (string) $this->input->get('klien', TRUE);
        $sumber  = (string) $this->input->get('sumber', TRUE);
        $status  = (string) $this->input->get('status', TRUE);
        if (!in_array($status, $this->statusList[$tab], TRUE)) $status = '';
        if (!in_array($sumber, ['Project', 'Mandiri'], TRUE))  $sumber = '';

        $perPage    = 6;
        $page       = max(1, (int) $this->input->get('page'));
        $total      = $this->Pembayaran_model->count_filtered($tab, $q, $klien, $sumber, $status);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $this->load->view('main', [
            'title'      => 'Pembayaran',
            'content'    => 'content/pembayaran/index',
            'tab'        => $tab,
            'rows'       => $this->Pembayaran_model->get_list($tab, $q, $klien, $sumber, $status, $perPage, ($page - 1) * $perPage),
            'klienList'  => $this->Pembayaran_model->get_klien_options(),
            'statusList' => $this->statusList[$tab],
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => $totalPages,
            'q'          => $q,
            'fKlien'     => $klien,
            'fSumber'    => $sumber,
            'fStatus'    => $status,
        ]);
    }

    /* ---------- HELPER ---------- */
    private function cek_tab($tab)
    {
        if ($tab !== 'penawaran') {
            $this->session->set_flashdata('success', 'Fitur invoice akan dibuat setelah penawaran selesai.');
            redirect('pembayaran?tab=invoice');
        }
    }

    private function render($view, $data)
    {
        $data['content'] = 'content/pembayaran/' . $view;
        $this->load->view('main', $data);
    }

    private function form_data($title, $penawaran, $action, $from)
    {
        $mode = $penawaran
            ? (($penawaran->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template')
            : $this->mode;

        return [
            'title'       => $title,
            'penawaran'   => $penawaran,
            'klienList'   => $this->Pembayaran_model->get_klien_options(),
            'projectList' => $this->Pembayaran_model->get_project_options(),
            'nomorBaru'   => $this->Pembayaran_model->next_nomor(date('Y')),
            'from'        => $from,
            'action'      => $action,
            'mode'        => $mode,
            'uploadErr'   => $this->upload_err,
        ];
    }

    /* ---------- TAMBAH ---------- */
    public function tambah($tab = 'penawaran')
    {
        $this->cek_tab($tab);
        $this->render('form', $this->form_data('Buat Penawaran', null, site_url('pembayaran/simpan/penawaran'), 'list'));
    }

    public function simpan($tab = 'penawaran')
    {
        $this->cek_tab($tab);
        if ($this->input->method() !== 'post') redirect('pembayaran');

        $this->mode = ($this->input->post('mode') === 'unggah') ? 'unggah' : 'template';

        $ok   = $this->validasi();
        $file = null;
        if ($this->mode === 'unggah') {
            $file = $this->proses_upload(TRUE);
            if ($file === false) $ok = FALSE;
        }
        if (!$ok) {
            return $this->tambah($tab);
        }

        $data = $this->ambil_input(TRUE);
        if (is_array($file)) $data = array_merge($data, $file);

        $this->Pembayaran_model->insert_penawaran($data);
        $this->session->set_flashdata('success', 'Penawaran berhasil dibuat.');
        redirect('pembayaran?tab=penawaran');
    }

    /* ---------- DETAIL ---------- */
    public function detail($tab, $id)
    {
        $this->cek_tab($tab);
        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        $this->render('detail', [
            'title'      => 'Detail Penawaran',
            'penawaran'  => $penawaran,
            'riwayat'    => $this->Pembayaran_model->get_riwayat($id),
            'statusList' => $this->statusList['penawaran'],
        ]);
    }

    /* ---------- UBAH STATUS ---------- */
    public function status($tab, $id)
    {
        $this->cek_tab($tab);
        if ($this->input->method() !== 'post') redirect('pembayaran/detail/penawaran/' . (int) $id);

        $p = $this->Pembayaran_model->get_penawaran($id);
        if (!$p) show_404();

        $status = (string) $this->input->post('status', TRUE);
        if (in_array($status, $this->statusList['penawaran'], TRUE) && $status !== $p->status) {
            $this->Pembayaran_model->update_status_penawaran($id, $status);
            $this->session->set_flashdata('success', 'Status penawaran berhasil diperbarui.');
        }
        redirect('pembayaran/detail/penawaran/' . (int) $id);
    }

    /* ---------- EDIT ---------- */
    public function edit($tab, $id)
    {
        $this->cek_tab($tab);
        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        $from   = $this->input->get('from') === 'detail' ? 'detail' : 'list';
        $action = site_url('pembayaran/update/penawaran/' . (int) $id) . ($from === 'detail' ? '?from=detail' : '');

        $this->render('form', $this->form_data('Edit Penawaran', $penawaran, $action, $from));
    }

    public function update($tab, $id)
    {
        $this->cek_tab($tab);
        if ($this->input->method() !== 'post') redirect('pembayaran');

        $lama = $this->Pembayaran_model->get_penawaran($id);
        if (!$lama) show_404();

        $this->edit_id   = (int) $id;
        $this->tgl_dasar = $lama->tanggal_penawaran;
        $this->mode      = ($lama->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template';

        $ok   = $this->validasi();
        $file = null;
        if ($this->mode === 'unggah') {
            $file = $this->proses_upload(empty($lama->file_path));
            if ($file === false) $ok = FALSE;
        }
        if (!$ok) {
            return $this->edit($tab, $id);
        }

        $data = $this->ambil_input(FALSE);
        if (is_array($file)) $data = array_merge($data, $file);

        $this->Pembayaran_model->update_penawaran($id, $data);

        // File lama dibuang jika diganti
        if (is_array($file) && !empty($lama->file_path)) {
            $f = APPPATH . 'uploads/penawaran/' . basename($lama->file_path);
            if (is_file($f)) @unlink($f);
        }

        $this->session->set_flashdata('success', 'Penawaran berhasil diperbarui.');
        if ($this->input->get('from') === 'detail') {
            redirect('pembayaran/detail/penawaran/' . (int) $id);
        }
        redirect('pembayaran?tab=penawaran');
    }

    /* ---------- PRATINJAU (template, tanpa menyimpan) ---------- */
    public function pratinjau()
    {
        if ($this->input->method() !== 'post') redirect('pembayaran');

        $this->mode = 'template';
        $in    = $this->ambil_input(FALSE);
        $klien = $this->Pembayaran_model->get_klien($in['klien_id']);

        $p = (object) $in;
        $p->no_penawaran      = $this->input->post('pv_nomor', TRUE) ?: $this->Pembayaran_model->next_nomor(date('Y'));
        $p->tanggal_penawaran = $this->input->post('pv_tanggal', TRUE) ?: date('Y-m-d');
        $p->status            = $this->input->post('pv_status', TRUE) ?: 'Draft';
        $p->nama_klien        = $klien ? $klien->nama_klien : NULL;
        $p->nama_pic          = $klien ? $klien->nama_pic : NULL;
        $p->alamat            = $klien ? $klien->alamat : NULL;
        $p->nama_project      = $in['project_id'] ? $this->Pembayaran_model->get_project_nama($in['project_id']) : NULL;

        $this->load->view('content/pembayaran/cetak', ['penawaran' => $p]);
    }

    /* ---------- UNDUH ---------- */
    public function unduh($tab, $id)
    {
        $this->cek_tab($tab);
        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        // Penawaran unggahan: berikan file aslinya
        if ($penawaran->dibuat_lewat === 'Unggahan' && !empty($penawaran->file_path)) {
            redirect('pembayaran/berkas/' . (int) $id);
        }
        $this->load->view('content/pembayaran/cetak', ['penawaran' => $penawaran]);
    }

    /* ---------- FILE UNGGAHAN ---------- */
    public function berkas($id)
    {
        $p = $this->Pembayaran_model->get_penawaran($id);
        if (!$p || empty($p->file_path)) show_404();

        $path = APPPATH . 'uploads/penawaran/' . basename($p->file_path);
        if (!is_file($path)) show_404();

        $ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = isset($mimes[$ext]) ? $mimes[$ext] : 'application/octet-stream';
        $nama = str_replace(['"', "\r", "\n"], '', $p->file_nama ?: basename($path));
        $disp = ($ext === 'pdf') ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disp . '; filename="' . $nama . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    // null = tidak ada file baru, false = gagal, array = berhasil
    private function proses_upload($wajib)
    {
        if (empty($_FILES['berkas']['name'])) {
            if ($wajib) {
                $this->upload_err = 'File penawaran wajib diunggah.';
                return false;
            }
            return null;
        }

        $dir = APPPATH . 'uploads/penawaran/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path'   => $dir,
            'allowed_types' => 'pdf|doc|docx',
            'max_size'      => 5120, // KB
            'encrypt_name'  => TRUE,
        ]);

        if (!$this->upload->do_upload('berkas')) {
            $this->upload_err = strip_tags($this->upload->display_errors('', ''));
            return false;
        }

        $d = $this->upload->data();
        return ['file_path' => $d['file_name'], 'file_nama' => $d['client_name']];
    }

    /* ---------- HAPUS ---------- */
    public function hapus()
    {
        if ($this->input->method() !== 'post') redirect('pembayaran');

        $tab = (string) $this->input->post('tab', TRUE);
        if (!$this->Pembayaran_model->valid_tab($tab)) redirect('pembayaran');

        $this->Pembayaran_model->delete($tab, $this->input->post('id', TRUE));
        $this->session->set_flashdata('success', ($tab === 'invoice' ? 'Invoice' : 'Penawaran') . ' berhasil dihapus.');
        redirect('pembayaran?tab=' . $tab);
    }

    /* ---------- VALIDASI ---------- */
    private function validasi()
    {
        $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'trim|integer');
        $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('berlaku_sampai', 'Berlaku sampai', 'trim|callback__cek_tanggal');

        if ($this->mode === 'unggah') {
            $this->form_validation->set_rules('no_penawaran', 'Nomor penawaran', 'required|trim|max_length[50]|callback__nomor_unik');
            $this->form_validation->set_rules('tanggal_penawaran', 'Tanggal penawaran', 'required|trim');
        } else {
            $this->form_validation->set_rules('isi_surat', 'Isi surat', 'trim');
        }
        return $this->form_validation->run();
    }

    public function _nomor_unik($no)
    {
        if ($this->Pembayaran_model->nomor_dipakai($no, $this->edit_id)) {
            $this->form_validation->set_message('_nomor_unik', 'Nomor penawaran sudah dipakai.');
            return FALSE;
        }
        return TRUE;
    }

    public function _cek_tanggal($akhir)
    {
        if ($this->mode === 'unggah') {
            $dasar = $this->input->post('tanggal_penawaran');
        } else {
            $dasar = $this->tgl_dasar ?: date('Y-m-d');
        }
        if ($akhir && $dasar && strtotime($akhir) < strtotime($dasar)) {
            $this->form_validation->set_message('_cek_tanggal', 'Tanggal berlaku tidak boleh sebelum tanggal penawaran.');
            return FALSE;
        }
        return TRUE;
    }

    private function ambil_input($baru = FALSE)
    {
        $project = (int) $this->input->post('project_id');
        $akhir   = $this->input->post('berlaku_sampai', TRUE);

        $data = [
            'klien_id'       => (int) $this->input->post('klien_id'),
            'project_id'     => $project > 0 ? $project : NULL,
            'perihal'        => $this->input->post('perihal', TRUE),
            'berlaku_sampai' => $akhir ?: NULL,
        ];

        if ($this->mode === 'unggah') {
            $data['no_penawaran']      = trim((string) $this->input->post('no_penawaran', TRUE));
            $data['tanggal_penawaran'] = $this->input->post('tanggal_penawaran', TRUE);
            $data['isi_surat']         = NULL;
            $data['sertakan_kop']      = 0;
            $data['sertakan_ttd']      = 0;
            if ($baru) {
                $data['status']       = 'Draft';
                $data['dibuat_lewat'] = 'Unggahan';
            }
        } else {
            $data['isi_surat']    = $this->input->post('isi_surat', TRUE);
            $data['sertakan_kop'] = $this->input->post('sertakan_kop') ? 1 : 0;
            $data['sertakan_ttd'] = $this->input->post('sertakan_ttd') ? 1 : 0;
            if ($baru) {
                $data['no_penawaran']      = $this->Pembayaran_model->next_nomor(date('Y'));
                $data['tanggal_penawaran'] = date('Y-m-d');
                $data['status']            = 'Draft';
                $data['dibuat_lewat']      = 'Template Baku';
            }
        }
        return $data;
    }
}