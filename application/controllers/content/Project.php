<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran extends CI_Controller
{
    private $statusList = [
        'penawaran' => ['Draft', 'Dikirim', 'Disetujui', 'Ditolak'],
        'invoice'   => ['Belum dibayar', 'Lunas', 'Jatuh tempo'],
    ];
    private $edit_id = 0;

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


    private function render($view, $data)
    {
        $data['content'] = 'content/pembayaran/' . $view;
        $this->load->view('main', $data);
    }

    private function form_data($title, $penawaran, $action, $from)
    {
        return [
            'title'       => $title,
            'penawaran'   => $penawaran,
            'klienList'   => $this->Pembayaran_model->get_klien_options(),
            'projectList' => $this->Pembayaran_model->get_project_options(),
            'statusList'  => $this->statusList['penawaran'],
            'nomorBaru'   => $this->Pembayaran_model->next_nomor(date('Y')),
            'from'        => $from,
            'action'      => $action,
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

        if (!$this->validasi()) {
            return $this->tambah($tab);
        }
        $this->Pembayaran_model->insert_penawaran($this->ambil_input());
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
        if (!$this->Pembayaran_model->get_penawaran($id)) show_404();

        $this->edit_id = (int) $id;
        if (!$this->validasi()) {
            return $this->edit($tab, $id);
        }

        $this->Pembayaran_model->update_penawaran($id, $this->ambil_input());
        $this->session->set_flashdata('success', 'Penawaran berhasil diperbarui.');

        if ($this->input->get('from') === 'detail') {
            redirect('pembayaran/detail/penawaran/' . (int) $id);
        }
        redirect('pembayaran?tab=penawaran');
    }

    /* ---------- UNDUH (halaman siap cetak) ---------- */
    public function unduh($tab, $id)
    {
        $this->cek_tab($tab);
        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        $this->load->view('content/pembayaran/cetak', ['penawaran' => $penawaran]);
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
        $this->form_validation->set_rules('no_penawaran', 'No. penawaran', 'required|trim|max_length[50]|callback__nomor_unik');
        $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'trim|integer');
        $this->form_validation->set_rules('tanggal_penawaran', 'Tanggal penawaran', 'required|trim');
        $this->form_validation->set_rules('berlaku_sampai', 'Berlaku sampai', 'trim|callback__cek_tanggal');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[Draft,Dikirim,Disetujui,Ditolak]');
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
        $mulai = $this->input->post('tanggal_penawaran');
        if ($akhir && $mulai && strtotime($akhir) < strtotime($mulai)) {
            $this->form_validation->set_message('_cek_tanggal', 'Tanggal berlaku tidak boleh sebelum tanggal penawaran.');
            return FALSE;
        }
        return TRUE;
    }

    private function ambil_input()
    {
        $project = (int) $this->input->post('project_id');
        $akhir   = $this->input->post('berlaku_sampai', TRUE);

        return [
            'no_penawaran'      => $this->input->post('no_penawaran', TRUE),
            'klien_id'          => (int) $this->input->post('klien_id'),
            'project_id'        => $project > 0 ? $project : NULL,
            'perihal'           => $this->input->post('perihal', TRUE),
            'tanggal_penawaran' => $this->input->post('tanggal_penawaran', TRUE),
            'berlaku_sampai'    => $akhir ?: NULL,
            'status'            => $this->input->post('status', TRUE),
        ];
    }
}