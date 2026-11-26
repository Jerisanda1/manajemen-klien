<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran extends CI_Controller
{
    private $statusList = [
        'penawaran' => ['Draft', 'Dikirim', 'Disetujui', 'Ditolak'],
        'invoice'   => ['Belum dibayar', 'Dibayar'],
    ];

    private $edit_id    = 0;
    private $tgl_dasar  = null;
    private $mode       = 'template';
    private $upload_err = '';
    private $form_err   = '';
    private $cara_inv   = 'template';

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        $this->load->model('Pembayaran_model');
        $this->load->model('Pengaturan_model');
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
        if (!$this->Pembayaran_model->valid_tab($tab)) show_404();
    }

    private function render($view, $data, $use_main = TRUE)
    {
        $data['content'] = 'content/pembayaran/' . $view;
        if ($use_main) {
            $this->load->view('main', $data);
        } else {
            $this->load->view($data['content'], $data);
        }
    }

    private function form_data($title, $penawaran, $action, $from)
    {
        $mode = $penawaran ? (($penawaran->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template') : $this->mode;
        $projectList = $this->Pembayaran_model->get_project_options();
        $projectContext = $this->project_context($projectList);

        return [
            'title'       => $title,
            'penawaran'   => $penawaran,
            'klienList'   => $this->Pembayaran_model->get_klien_options(),
            'projectList' => $projectList,
            'preKlien'    => $projectContext['klien_id'],
            'preProject'  => $projectContext['project_id'],
            'nomorBaru'   => $this->Pembayaran_model->next_nomor(date('Y')),
            'from'        => $from,
            'action'      => $this->project_context_action($action),
            'mode'        => $mode,
            'uploadErr'   => $this->upload_err,
        ];
    }

    private function project_context($projectList)
    {
        $projectId = (int) $this->input->get('project_id');
        foreach ($projectList as $project) {
            if ((int) $project->id === $projectId && $projectId > 0) {
                return ['project_id' => $projectId, 'klien_id' => (int) $project->klien_id];
            }
        }

        return ['project_id' => 0, 'klien_id' => 0];
    }

    private function project_context_action($action)
    {
        $projectId = (int) $this->input->get('project_id');
        if ($projectId <= 0) {
            return $action;
        }

        $query = ['project_id' => $projectId];
        if ($this->input->get('from') === 'project_detail') {
            $query['from'] = 'project_detail';
        }

        return $action . '?' . http_build_query($query);
    }

    /* ---------- TAMBAH ---------- */
    public function tambah($tab = 'penawaran')
    {
        if ($tab === 'invoice') return $this->tambah_invoice();
        $this->cek_tab($tab);
        $this->render('form', $this->form_data('Buat Penawaran', null, site_url('pembayaran/simpan/penawaran'), 'list'), FALSE);
    }

    public function simpan($tab = 'penawaran')
    {
        if ($tab === 'invoice') return $this->simpan_invoice();
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

        $projectId = (int) $data['project_id'];
        $this->Pembayaran_model->insert_penawaran($data);
        $this->session->set_flashdata('success', 'Penawaran berhasil dibuat.');
        if ($this->input->get('from') === 'project_detail' && $projectId > 0) {
            redirect('project/detail/' . $projectId);
        }
        redirect('pembayaran?tab=penawaran');
    }

    /* ---------- DETAIL ---------- */
    public function detail($tab, $id)
    {
        if ($tab === 'invoice') return $this->detail_invoice($id);
        $this->cek_tab($tab);
        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        $this->render('detail', [
            'title'      => 'Detail Penawaran',
            'penawaran'  => $penawaran,
            'riwayat'    => $this->Pembayaran_model->get_riwayat($id),
            'statusList' => $this->statusList['penawaran'],
        ], FALSE);
    }

    /* ---------- UBAH STATUS ---------- */
    public function status($tab, $id)
    {
        if ($tab === 'invoice') return $this->status_invoice($id);
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

    /* ---------- INVOICE: EDIT ---------- */
    private function edit_invoice($id)
    {
        $inv = $this->Pembayaran_model->get_invoice($id);
        if (!$inv) show_404();

        $from   = $this->input->get('from') === 'detail' ? 'detail' : 'list';
        $action = site_url('pembayaran/update/invoice/' . (int) $id) . ($from === 'detail' ? '?from=detail' : '');

        $data = $this->data_invoice('Edit Invoice', $inv, $action);
        $data['from'] = $from;
        $this->render('form_invoice', $data, FALSE);
    }
    private function update_invoice($id)
    {
        if ($this->input->method() !== 'post') redirect('pembayaran?tab=invoice');
        $lama = $this->Pembayaran_model->get_invoice($id);
        if (!$lama) show_404();
        $cara = ($lama->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template';
        $dariPw = !empty($lama->penawaran_id);
        $this->cara_inv = $cara;
        $this->edit_id = (int) $id;
        $this->tgl_dasar = $lama->tanggal_invoice;

        $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('jatuh_tempo', 'Tanggal jatuh tempo', 'required|trim|callback__cek_tempo');
        if ($cara === 'unggah') {
            $this->form_validation->set_rules('no_invoice', 'Nomor invoice', 'required|trim|max_length[50]|callback__nomor_invoice_unik');
            $this->form_validation->set_rules('tanggal_invoice', 'Tanggal invoice', 'required|trim');
        }
        if (!$dariPw) {
            $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
            $this->form_validation->set_rules('project_id', 'Project', 'required|integer');
        }
        $ok = $this->form_validation->run();
        $rincian = NULL;
        if ($ok && $cara === 'template') {
            $rincian = $this->ambil_rincian_invoice();
            if ($rincian === FALSE) $ok = FALSE;
        }
        $file = NULL;
        if ($ok && $cara === 'unggah') {
            $file = $this->proses_upload(empty($lama->file_path), 'invoice');
            if ($file === FALSE) $ok = FALSE;
        }
        if (!$ok) return $this->edit_invoice($id);

        $data = [
            'perihal' => $this->input->post('perihal', TRUE),
            'jatuh_tempo' => $this->input->post('jatuh_tempo', TRUE),
        ];
        if (!$dariPw) {
            $project = (int) $this->input->post('project_id');
            $data['klien_id'] = (int) $this->input->post('klien_id');
            $data['project_id'] = $project > 0 ? $project : NULL;
        }
        if ($cara === 'unggah') {
            $data['no_invoice'] = trim((string) $this->input->post('no_invoice', TRUE));
            $data['tanggal_invoice'] = $this->input->post('tanggal_invoice', TRUE);
            if (is_array($file)) $data = array_merge($data, $file);
            $tersimpan = $this->Pembayaran_model->update_invoice($id, $data);
        } else {
            $data['isi_surat'] = $this->input->post('isi_surat', TRUE);
            $data['sertakan_kop'] = $this->input->post('sertakan_kop') ? 1 : 0;
            $data['sertakan_ttd'] = $this->input->post('sertakan_ttd') ? 1 : 0;
            $data['subtotal'] = $rincian['subtotal'];
            $data['discount_amount'] = $rincian['discount_amount'];
            $data['total_amount'] = $rincian['total_amount'];
            $data['payment_method'] = $rincian['payment_method'];
            $data['payment_account'] = $rincian['payment_account'];
            $data['notes'] = $rincian['notes'];
            $this->db->trans_begin();
            $this->Pembayaran_model->update_invoice($id, $data);
            $this->Pembayaran_model->replace_invoice_details($id, $rincian['items'], $rincian['taxes']);
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $tersimpan = FALSE;
            } else {
                $this->db->trans_commit();
                $tersimpan = TRUE;
            }
        }
        if (!$tersimpan) {
            $this->form_err = 'Invoice gagal diperbarui.';
            return $this->edit_invoice($id);
        }
        if (is_array($file) && !empty($lama->file_path)) {
            $f = APPPATH . 'uploads/invoice/' . basename($lama->file_path);
            if (is_file($f)) @unlink($f);
        }
        $this->session->set_flashdata('success', 'Invoice berhasil diperbarui.');
        if ($this->input->get('from') === 'detail') redirect('pembayaran/detail/invoice/' . (int) $id);
        redirect('pembayaran?tab=invoice');
    }

    /* ---------- INVOICE: DETAIL ---------- */
    private function detail_invoice($id)
    {
        $inv = $this->Pembayaran_model->get_invoice($id);
        if (!$inv) show_404();

        $this->render('detail_invoice', [
            'title'      => 'Detail Invoice',
            'inv'        => $inv,
            'pengiriman' => $this->Pembayaran_model->get_pengiriman_terakhir($id),
            'items'      => $this->Pembayaran_model->get_invoice_details($id),
            'taxes'      => $this->Pembayaran_model->get_invoice_taxes($id),
        ], FALSE);
    }


    public function kirim_email($id)
{
    if ($this->input->method() !== 'post') {
        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $inv = $this->Pembayaran_model->get_invoice($id);
    if (!$inv) {
        show_404();
    }

    if ($inv->status === 'Dibayar') {
        $this->session->set_flashdata('error', 'Invoice sudah dibayar. Email pengingat tidak dikirim.');
        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $tujuan = trim((string) $inv->email_pic);

    if (!filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
        $this->Pembayaran_model->add_pengiriman(
            $id,
            'email',
            $tujuan,
            FALSE,
            'Alamat email PIC tidak valid.'
        );

        $this->session->set_flashdata('error', 'Alamat email PIC tidak valid.');
        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $this->load->library('Notifikasi');

    $hari = (int) floor(
        (strtotime($inv->jatuh_tempo) - strtotime(date('Y-m-d'))) / 86400
    );

    list($subjek, $isi) = $this->notifikasi->pesan_pengingat($inv, $hari);

    list($berhasil, $keterangan) = $this->notifikasi->email(
        $tujuan,
        $subjek,
        $isi
    );

    $this->Pembayaran_model->add_pengiriman(
        $id,
        'email',
        $tujuan,
        $berhasil,
        $keterangan,
        0,
        NULL
    );

    if ($berhasil) {
        $this->session->set_flashdata('success', 'Email pengingat berhasil dikirim ke ' . $tujuan . '.');
    } else {
        $this->session->set_flashdata('error', 'Email gagal dikirim: ' . $keterangan);
    }

    redirect('pembayaran/detail/invoice/' . (int) $id);
}

public function kirim_wa($id)
{
    if ($this->input->method() !== 'post') {
        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $inv = $this->Pembayaran_model->get_invoice($id);

    if (!$inv) {
        show_404();
    }

    if ($inv->status === 'Dibayar') {
        $this->session->set_flashdata(
            'error',
            'Invoice sudah dibayar. WhatsApp pengingat tidak dikirim.'
        );

        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $tujuan = trim((string) $inv->hp_pic);

    if ($tujuan === '') {
        $this->Pembayaran_model->add_pengiriman(
            $id,
            'whatsapp',
            $tujuan,
            FALSE,
            'Nomor WhatsApp PIC kosong.'
        );

        $this->session->set_flashdata(
            'error',
            'Nomor WhatsApp PIC belum tersedia.'
        );

        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    $this->load->helper('fonnte');

    $hari = (int) floor(
        (strtotime($inv->jatuh_tempo) - strtotime(date('Y-m-d'))) / 86400
    );

    $pesan = "Halo {$inv->nama_pic},\n\n"
        . "Kami ingin mengingatkan bahwa invoice {$inv->no_invoice} "
        . "dengan perihal {$inv->perihal} "
        . "jatuh tempo pada " . date('d-m-Y', strtotime($inv->jatuh_tempo)) . ".\n\n"
        . "Mohon segera melakukan pembayaran. "
        . "Jika pembayaran sudah dilakukan, silakan abaikan pesan ini.\n\n"
        . "Terima kasih,\nTridatu Solution.";

    $hasil = kirim_wa_fonnte($tujuan, $pesan);

    $berhasil = !empty($hasil['status']);
    $keterangan = $berhasil
        ? ''
        : (isset($hasil['reason']) ? $hasil['reason'] : 'Pengiriman WhatsApp gagal.');

    $this->Pembayaran_model->add_pengiriman(
        $id,
        'whatsapp',
        $tujuan,
        $berhasil,
        $keterangan
    );

    if ($berhasil) {
        $this->session->set_flashdata(
            'success',
            'WhatsApp pengingat berhasil dikirim ke ' . $tujuan . '.'
        );
    } else {
        $this->session->set_flashdata(
            'error',
            'WhatsApp gagal dikirim: ' . $keterangan
        );
    }

    redirect('pembayaran/detail/invoice/' . (int) $id);
}

    private function status_invoice($id)
    {
        if ($this->input->method() !== 'post') redirect('pembayaran/detail/invoice/' . (int) $id);

        $inv = $this->Pembayaran_model->get_invoice($id);
        if (!$inv) show_404();

        $status = (string) $this->input->post('status', TRUE);
        if (in_array($status, $this->statusList['invoice'], TRUE) && $status !== $inv->status) {
            $this->Pembayaran_model->set_status_invoice($id, $status);
            $this->session->set_flashdata('success', 'Status pembayaran berhasil diperbarui.');
        }
        redirect('pembayaran/detail/invoice/' . (int) $id);
    }

    /* ---------- EDIT ---------- */
    public function edit($tab, $id)
    {
        if ($tab === 'invoice') return $this->edit_invoice($id);
        $this->cek_tab($tab);

        $penawaran = $this->Pembayaran_model->get_penawaran($id);
        if (!$penawaran) show_404();

        $from   = $this->input->get('from') === 'detail' ? 'detail' : 'list';
        $action = site_url('pembayaran/update/penawaran/' . (int) $id) . ($from === 'detail' ? '?from=detail' : '');

        $this->render('form', $this->form_data('Edit Penawaran', $penawaran, $action, $from), FALSE);
    }

    public function update($tab, $id)
    {
        if ($tab === 'invoice') return $this->update_invoice($id);
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

        $this->stream_pdf_penawaran($p, TRUE);
    }

    /* ---------- UNDUH ---------- */
    public function unduh($tab, $id)
    {
        if ($tab === 'invoice') return $this->unduh_invoice($id);
        $this->cek_tab($tab);

        $data_row = $this->Pembayaran_model->get_penawaran($id);
        if (!$data_row) show_404();

        $inline = $this->input->get('inline') ? TRUE : FALSE;
        $this->stream_pdf_penawaran($data_row, $inline);
    }

    // Helper khusus Penawaran
    private function stream_pdf_penawaran($p, $inline)
    {
        if (file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }
        if (!class_exists('Dompdf\Dompdf')) {
            show_error('Dompdf belum terpasang. Jalankan "composer require dompdf/dompdf" di root project.', 500);
        }

        $settings = $this->Pengaturan_model->get_settings();

        $html = $this->load->view('content/pembayaran/pdf', [
            'penawaran' => $p,
            'settings'  => $settings
        ], TRUE);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => FALSE]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nama = preg_replace('/[^A-Za-z0-9_\-]/', '-', $p->no_penawaran ?? 'penawaran') . '.pdf';
        $dompdf->stream($nama, ['Attachment' => !$inline]);
        exit;
    }

    /* ---------- INVOICE: UNDUH / PRATINJAU ---------- */
    private function unduh_invoice($id)
    {
        $inv = $this->Pembayaran_model->get_invoice($id);
        if (!$inv) show_404();

        if ($inv->dibuat_lewat === 'Unggahan' && !empty($inv->file_path)) {
            redirect('pembayaran/berkas-invoice/' . (int) $id . '?unduh=1');
        }
        $this->stream_pdf_invoice($inv, (bool) $this->input->get('inline'));
    }

    public function pratinjau_invoice()
    {
        if ($this->input->method() !== 'post') redirect('pembayaran?tab=invoice');

        $klien   = $this->Pembayaran_model->get_klien((int) $this->input->post('klien_id'));
        $project = (int) $this->input->post('project_id');

        $o = (object) [
            'no_invoice'      => $this->input->post('pv_nomor', TRUE) ?: $this->Pembayaran_model->next_nomor_invoice(date('Y')),
            'tanggal_invoice' => $this->input->post('pv_tanggal', TRUE) ?: date('Y-m-d'),
            'jatuh_tempo'     => $this->input->post('jatuh_tempo', TRUE) ?: NULL,
            'perihal'         => $this->input->post('perihal', TRUE),
            'isi_surat'       => $this->input->post('isi_surat', TRUE),
            'sertakan_kop'    => $this->input->post('sertakan_kop') ? 1 : 0,
            'sertakan_ttd'    => $this->input->post('sertakan_ttd') ? 1 : 0,
            'nama_klien'      => $klien ? $klien->nama_klien : NULL,
            'nama_pic'        => $klien ? $klien->nama_pic : NULL,
            'alamat'          => $klien ? $klien->alamat : NULL,
            'nama_project'    => $project > 0 ? $this->Pembayaran_model->get_project_nama($project) : NULL,
        ];
        $this->stream_pdf_invoice($o, TRUE);
    }

    private function stream_pdf_invoice($o, $inline)
    {
        if (file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }
        if (!class_exists('Dompdf\Dompdf')) {
            show_error('Dompdf belum terpasang. Jalankan "composer require dompdf/dompdf" di root project.', 500);
        }

       $settings = NULL;

if (!empty($o->snapshot_data)) {
    $snapshot = json_decode($o->snapshot_data);

    if (json_last_error() === JSON_ERROR_NONE && is_object($snapshot)) {
        $settings = $snapshot;
    }
}

// Fallback untuk invoice lama atau snapshot yang tidak valid.
if (!$settings) {
    $settings = $this->Pengaturan_model->get_settings();
}

        $items = !empty($o->id) ? $this->Pembayaran_model->get_invoice_details($o->id) : [];
        $taxes = !empty($o->id) ? $this->Pembayaran_model->get_invoice_taxes($o->id) : [];

        $html = $this->load->view('content/pembayaran/pdf_invoice', [
            'invoice'  => $o,
            'settings' => $settings,
            'items'    => $items,
            'taxes'    => $taxes
        ], TRUE);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => FALSE]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nama = preg_replace('/[^A-Za-z0-9_\-]/', '-', $o->no_invoice) . '.pdf';
        $dompdf->stream($nama, ['Attachment' => !$inline]);
        exit;
    }

    public function berkas_invoice($id)
    {
        $inv = $this->Pembayaran_model->get_invoice($id);
        if (!$inv || empty($inv->file_path)) show_404();

        $path = APPPATH . 'uploads/invoice/' . basename($inv->file_path);
        if (!is_file($path)) show_404();

        $ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = isset($mimes[$ext]) ? $mimes[$ext] : 'application/octet-stream';
        $nama = str_replace(['"', "\r", "\n"], '', $inv->file_nama ?: basename($path));
        $disp = ($ext === 'pdf' && !$this->input->get('unduh')) ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disp . '; filename="' . $nama . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
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

        $paksa = $this->input->get('unduh') ? TRUE : FALSE;
        $disp  = ($ext === 'pdf' && !$paksa) ? 'inline' : 'attachment';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disp . '; filename="' . $nama . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    private function proses_upload($wajib, $sub = 'penawaran')
    {
        if (empty($_FILES['berkas']['name'])) {
            if ($wajib) {
                $this->upload_err = 'File ' . $sub . ' wajib diunggah.';
                return false;
            }
            return null;
        }

        $dir = APPPATH . 'uploads/' . $sub . '/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path'   => $dir,
            'allowed_types' => 'pdf|doc|docx',
            'max_size'      => 5120,
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
        // PROJECT DIWAJIBKAN
        $this->form_validation->set_rules('project_id', 'Project', 'required|integer');
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

    /* ---------- INVOICE: TAMBAH ---------- */
    private function data_invoice($title, $inv, $action)
    {
        $projectList = $this->Pembayaran_model->get_project_options();
        $projectContext = $this->project_context($projectList);

        return [
            'title'         => $title,
            'inv'           => $inv,
            'settings'      => $this->Pengaturan_model->get_settings(),
            'klienList'     => $this->Pembayaran_model->get_klien_options(),
            'projectList'   => $projectList,
            'preKlien'      => $projectContext['klien_id'],
            'preProject'    => $projectContext['project_id'],
            'penawaranList' => $inv ? [] : $this->Pembayaran_model->get_penawaran_disetujui(),
            'nomorBaru'     => $this->Pembayaran_model->next_nomor_invoice(date('Y')),
            'pre'           => $inv ? 0 : (int) $this->input->get('penawaran_id'),
            'action'        => $this->project_context_action($action),
            'formErr'       => $this->form_err,
            'uploadErr'     => $this->upload_err,
            'items'         => $inv ? $this->Pembayaran_model->get_invoice_details($inv->id) : [],
            'taxes'         => $inv ? $this->Pembayaran_model->get_invoice_taxes($inv->id) : [],
        ];
    }

    private function tambah_invoice()
    {
        $this->render('form_invoice', $this->data_invoice('Tambah Invoice', null, site_url('pembayaran/simpan/invoice')), FALSE);
    }

    private function ambil_rincian_invoice()
    {
        $itemsPost = $this->input->post('items');
        $taxesPost = $this->input->post('taxes');
        if (!is_array($itemsPost)) {
            $this->form_err = 'Rincian item invoice belum diisi.';
            return FALSE;
        }

        $items = [];
        $subtotal = 0;
        $urut = 1;
        foreach ($itemsPost as $row) {
            if (!is_array($row)) continue;
            $nama = trim((string) ($row['description'] ?? ''));
            $qty = (float) ($row['quantity'] ?? 0);
            $satuan = trim((string) ($row['unit'] ?? ''));
            $harga = (float) ($row['unit_price'] ?? 0);
            if ($nama === '' || $qty <= 0 || $harga < 0) continue;
            $jumlah = $qty * $harga;
            $subtotal += $jumlah;
            $items[] = [
                'nama_item' => $nama,
                'deskripsi' => NULL,
                'kuantitas' => $qty,
                'satuan' => $satuan !== '' ? $satuan : NULL,
                'harga_satuan' => $harga,
                'subtotal' => $jumlah,
                'urutan' => $urut++,
            ];
        }
        if (!$items) {
            $this->form_err = 'Minimal harus ada satu item invoice yang valid.';
            return FALSE;
        }

        $diskon = max(0, (float) $this->input->post('discount_amount'));
        $diskon = min($diskon, $subtotal);
        $dasarPajak = $subtotal - $diskon;
        $taxes = [];
        $totalPajak = 0;
        $urutPajak = 1;

        // Default sesuai template Tridatu: PPN 11% dan PPh 2%.
        // Jika form tidak mengirim baris pajak sama sekali, gunakan nilai default
        // agar invoice template tidak tersimpan tanpa pajak secara tidak sengaja.
        if (!is_array($taxesPost) || empty($taxesPost)) {
            $taxesPost = [
                ['tax_name' => 'PPN', 'tax_type' => 'percentage', 'tax_rate' => 11],
                ['tax_name' => 'PPh', 'tax_type' => 'percentage', 'tax_rate' => 2],
            ];
        }

        foreach ($taxesPost as $row) {
            if (!is_array($row)) continue;
            $namaPajak = trim((string) ($row['tax_name'] ?? ''));
            $tipe = $row['tax_type'] ?? 'percentage';
            $rate = max(0, (float) ($row['tax_rate'] ?? 0));
            if ($namaPajak === '' || !in_array($tipe, ['percentage', 'fixed'], TRUE)) continue;

            $nilai = $tipe === 'percentage' ? ($dasarPajak * $rate / 100) : $rate;
            $totalPajak += $nilai;
            $taxes[] = [
                'nama_pajak' => $namaPajak,
                'jenis' => 'pajak',
                'persentase' => $tipe === 'percentage' ? $rate : 0,
                'dasar_perhitungan' => $dasarPajak,
                'nilai' => $nilai,
                'urutan' => $urutPajak++,
            ];
        }
        return [
            'items' => $items,
            'taxes' => $taxes,
            'subtotal' => $subtotal,
            'discount_amount' => $diskon,
            'total_amount' => $dasarPajak + $totalPajak,
            'payment_method' => trim((string) $this->input->post('payment_method', TRUE)),
            'payment_account' => trim((string) $this->input->post('payment_account', TRUE)),
            'notes' => trim((string) $this->input->post('notes', TRUE)),
        ];
    }


/**
 * Membuat salinan aset perusahaan untuk arsip invoice.
 * File asli di Pengaturan tidak diubah.
 */
private function buat_snapshot_aset_invoice()
{
    $settings = $this->Pengaturan_model->get_settings();

    if (!$settings) {
        return FALSE;
    }

    // Salin object agar data pengaturan asli tidak berubah.
    $snapshot = clone $settings;

    $folderToken = bin2hex(random_bytes(12));
    $folderRelatif = 'uploads/invoice_snapshots/' . $folderToken . '/';
    $folderAbsolut = FCPATH . $folderRelatif;

    if (!is_dir($folderAbsolut) &&
        !mkdir($folderAbsolut, 0755, TRUE)) {
        return FALSE;
    }

    $assets = ['logo', 'kop_surat', 'ttd'];

    foreach ($assets as $asset) {
        $relativePath = isset($snapshot->$asset)
            ? trim((string) $snapshot->$asset)
            : '';

        if ($relativePath === '') {
            $snapshot->$asset = NULL;
            continue;
        }

        // Pastikan file sumber berada di dalam folder aplikasi.
        $source = realpath(FCPATH . ltrim($relativePath, '/\\'));
        $base = realpath(FCPATH);

        if (
            !$source ||
            !$base ||
            strpos($source, $base . DIRECTORY_SEPARATOR) !== 0 ||
            !is_file($source)
        ) {
            $this->hapus_folder_snapshot($folderAbsolut);
            return FALSE;
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], TRUE)) {
            $this->hapus_folder_snapshot($folderAbsolut);
            return FALSE;
        }

        $namaBaru = $asset . '.' . $extension;
        $tujuan = $folderAbsolut . $namaBaru;

        if (!copy($source, $tujuan)) {
            $this->hapus_folder_snapshot($folderAbsolut);
            return FALSE;
        }

        // Simpan alamat salinan, bukan alamat file aktif.
        $snapshot->$asset = $folderRelatif . $namaBaru;
    }

    return $snapshot;
}

/**
 * Membersihkan folder snapshot jika proses penyalinan gagal.
 */
private function hapus_folder_snapshot($folder)
{
    if (!is_dir($folder)) {
        return;
    }

    foreach (glob($folder . '*') as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    @rmdir($folder);
}

    private function simpan_invoice()
    {
        if ($this->input->method() !== 'post') redirect('pembayaran?tab=invoice');
        $sumber = $this->input->post('sumber') === 'penawaran' ? 'penawaran' : 'baru';
        $cara = $this->input->post('cara') === 'unggah' ? 'unggah' : 'template';
        $this->cara_inv = $cara;

        $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('jatuh_tempo', 'Tanggal jatuh tempo', 'required|trim|callback__cek_tempo');
        if ($cara === 'unggah') {
            $this->form_validation->set_rules('no_invoice', 'Nomor invoice', 'required|trim|max_length[50]|callback__nomor_invoice_unik');
            $this->form_validation->set_rules('tanggal_invoice', 'Tanggal invoice', 'required|trim');
        }
        if ($sumber === 'penawaran') $this->form_validation->set_rules('penawaran_id', 'Penawaran', 'required|integer');
        else {
            $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
            $this->form_validation->set_rules('project_id', 'Project', 'required|integer');
        }
        $ok = $this->form_validation->run();
        $klien = (int) $this->input->post('klien_id');
        $project = (int) $this->input->post('project_id');
        $pwId = NULL;
        if ($ok && $sumber === 'penawaran') {
            $pw = $this->Pembayaran_model->get_penawaran($this->input->post('penawaran_id'));
            if (!$pw || $pw->status !== 'Disetujui') {
                $this->form_err = 'Penawaran harus berstatus Disetujui.';
                $ok = FALSE;
            } else {
                $pwId = (int) $pw->id;
                $klien = (int) $pw->klien_id;
                $project = (int) $pw->project_id;
            }
        }

        $rincian = NULL;
        if ($ok && $cara === 'template') {
            $rincian = $this->ambil_rincian_invoice();
            if ($rincian === FALSE) $ok = FALSE;
        }
        $file = NULL;
        if ($ok && $cara === 'unggah') {
            $file = $this->proses_upload(TRUE, 'invoice');
            if ($file === FALSE) $ok = FALSE;
        }
        if (!$ok) return $this->tambah_invoice();

        $data = [
            'klien_id' => $klien,
            'project_id' => $project > 0 ? $project : NULL,
            'penawaran_id' => $pwId,
            'perihal' => $this->input->post('perihal', TRUE),
            'jatuh_tempo' => $this->input->post('jatuh_tempo', TRUE),
            'status' => 'Belum dibayar',
        ];
        // Simpan snapshot identitas perusahaan saat invoice dibuat.

// Buat snapshot identitas dan salinan aset perusahaan.
$settingsSnapshot = $this->buat_snapshot_aset_invoice();

if ($settingsSnapshot === FALSE) {
    $this->form_err = 'Gagal membuat arsip logo, kop surat, atau tanda tangan perusahaan. Invoice belum disimpan.';
    return $this->tambah_invoice();
}

$data['snapshot_data'] = json_encode(
    $settingsSnapshot,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

if ($data['snapshot_data'] === FALSE) {
    $this->form_err = 'Gagal menyimpan snapshot perusahaan.';
    return $this->tambah_invoice();
}

        if ($cara === 'unggah') {
            $data['no_invoice'] = trim((string) $this->input->post('no_invoice', TRUE));
            $data['tanggal_invoice'] = $this->input->post('tanggal_invoice', TRUE);
            $data['dibuat_lewat'] = 'Unggahan';
            $data['isi_surat'] = NULL;
            $data['sertakan_kop'] = 0;
            $data['sertakan_ttd'] = 0;
            $data = array_merge($data, $file);
            $tersimpan = $this->Pembayaran_model->insert_invoice($data);
        } else {
            $data['no_invoice'] = trim((string) ($this->input->post('no_invoice', TRUE) ?: $this->Pembayaran_model->next_nomor_invoice(date('Y'))));
            $data['tanggal_invoice'] = date('Y-m-d');
            $data['dibuat_lewat'] = 'Template Baku';
            $data['isi_surat'] = $this->input->post('isi_surat', TRUE);
            $data['sertakan_kop'] = $this->input->post('sertakan_kop') ? 1 : 0;
            $data['sertakan_ttd'] = $this->input->post('sertakan_ttd') ? 1 : 0;
            $data['subtotal'] = $rincian['subtotal'];
            $data['discount_amount'] = $rincian['discount_amount'];
            $data['total_amount'] = $rincian['total_amount'];
            $data['payment_method'] = $rincian['payment_method'];
            $data['payment_account'] = $rincian['payment_account'];
            $data['notes'] = $rincian['notes'];
            $tersimpan = $this->Pembayaran_model->insert_invoice_lengkap($data, $rincian['items'], $rincian['taxes']);
        }
        if (!$tersimpan) {
            $this->form_err = 'Invoice gagal disimpan. Periksa kembali data dan struktur database.';
            return $this->tambah_invoice();
        }
        $this->session->set_flashdata('success', 'Invoice berhasil dibuat.');
        if ($this->input->get('from') === 'project_detail' && $project > 0) redirect('project/detail/' . $project);
        redirect('pembayaran?tab=invoice');
    }

    public function _cek_tempo($tgl)
    {
        if ($this->cara_inv === 'unggah') {
            $dasar = $this->input->post('tanggal_invoice');
            $pesan = 'Tanggal jatuh tempo tidak boleh sebelum tanggal invoice.';
        } elseif ($this->tgl_dasar) {
            $dasar = $this->tgl_dasar;
            $pesan = 'Tanggal jatuh tempo tidak boleh sebelum tanggal invoice.';
        } else {
            $dasar = date('Y-m-d');
            $pesan = 'Tanggal jatuh tempo tidak boleh sebelum hari ini.';
        }
        if ($tgl && $dasar && strtotime($tgl) < strtotime($dasar)) {
            $this->form_validation->set_message('_cek_tempo', $pesan);
            return FALSE;
        }
        return TRUE;
    }

    public function _nomor_invoice_unik($no)
    {
        if ($this->Pembayaran_model->nomor_invoice_dipakai($no, $this->edit_id)) {
            $this->form_validation->set_message('_nomor_invoice_unik', 'Nomor invoice sudah dipakai.');
            return FALSE;
        }
        return TRUE;
    }

}
