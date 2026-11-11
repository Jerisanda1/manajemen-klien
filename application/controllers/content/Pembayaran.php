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

    private $form_err = '';

    private $cara_inv = 'template';

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
    if (!$this->Pembayaran_model->valid_tab($tab)) {
        show_404();
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
          if ($tab === 'invoice') return $this->tambah_invoice();
        $this->cek_tab($tab);
        $this->render('form', $this->form_data('Buat Penawaran', null, site_url('pembayaran/simpan/penawaran'), 'list'));
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

        $this->Pembayaran_model->insert_penawaran($data);
        $this->session->set_flashdata('success', 'Penawaran berhasil dibuat.');
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
        ]);
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
    $this->render('form_invoice', $data);
}

private function update_invoice($id)
{
    if ($this->input->method() !== 'post') redirect('pembayaran?tab=invoice');

    $lama = $this->Pembayaran_model->get_invoice($id);
    if (!$lama) show_404();

    $cara    = ($lama->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template';
    $dariPw  = !empty($lama->penawaran_id);
    $this->cara_inv  = $cara;
    $this->edit_id   = (int) $id;
    $this->tgl_dasar = $lama->tanggal_invoice;

    $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|max_length[200]');
    $this->form_validation->set_rules('jatuh_tempo', 'Tanggal jatuh tempo', 'required|trim|callback__cek_tempo');
    if ($cara === 'unggah') {
        $this->form_validation->set_rules('no_invoice', 'Nomor invoice', 'required|trim|max_length[50]|callback__nomor_invoice_unik');
        $this->form_validation->set_rules('tanggal_invoice', 'Tanggal invoice', 'required|trim');
    }
    if (!$dariPw) {
        $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'trim|integer');
    }
    $ok = $this->form_validation->run();

    $file = null;
    if ($ok && $cara === 'unggah') {
        $file = $this->proses_upload(empty($lama->file_path), 'invoice');
        if ($file === false) $ok = FALSE;
    }
    if (!$ok) {
        return $this->edit_invoice($id);
    }

    $data = [
        'perihal'     => $this->input->post('perihal', TRUE),
        'jatuh_tempo' => $this->input->post('jatuh_tempo', TRUE),
    ];

    // Klien & project hanya bisa diubah kalau invoice bukan dari penawaran
    if (!$dariPw) {
        $project = (int) $this->input->post('project_id');
        $data['klien_id']   = (int) $this->input->post('klien_id');
        $data['project_id'] = $project > 0 ? $project : NULL;
    }

    if ($cara === 'unggah') {
        $data['no_invoice']      = trim((string) $this->input->post('no_invoice', TRUE));
        $data['tanggal_invoice'] = $this->input->post('tanggal_invoice', TRUE);
        if (is_array($file)) $data = array_merge($data, $file);
    } else {
        $data['isi_surat']    = $this->input->post('isi_surat', TRUE);
        $data['sertakan_kop'] = $this->input->post('sertakan_kop') ? 1 : 0;
        $data['sertakan_ttd'] = $this->input->post('sertakan_ttd') ? 1 : 0;
    }

    $this->Pembayaran_model->update_invoice($id, $data);

    // File lama dibuang jika diganti
    if (is_array($file) && !empty($lama->file_path)) {
        $f = APPPATH . 'uploads/invoice/' . basename($lama->file_path);
        if (is_file($f)) @unlink($f);
    }

    $this->session->set_flashdata('success', 'Invoice berhasil diperbarui.');
if ($this->input->get('from') === 'detail') {
    redirect('pembayaran/detail/invoice/' . (int) $id);
}
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
    ]);
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

/* ---------- INVOICE: KIRIM PENGINGAT ---------- */
public function kirim($tab, $id)
{
    if ($tab !== 'invoice' || $this->input->method() !== 'post') redirect('pembayaran?tab=invoice');

    $inv = $this->Pembayaran_model->get_invoice($id);
    if (!$inv) show_404();

    $balik = 'pembayaran/detail/invoice/' . (int) $id;
    if ($inv->status === 'Dibayar') {
        $this->session->set_flashdata('error', 'Invoice sudah dibayar, pengingat tidak diperlukan.');
        redirect($balik);
    }

    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($inv->jatuh_tempo);
    $tempo = $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';

    $pesan = "Yth. " . ($inv->nama_pic ?: 'Bapak/Ibu') . ",\n\n"
           . "Mengingatkan bahwa invoice {$inv->no_invoice} ({$inv->perihal}) jatuh tempo pada {$tempo}. "
           . "Mohon segera melakukan pembayaran.\n\nTerima kasih.\nTridatu Solution";

    $saluran = $this->input->post('saluran') === 'whatsapp' ? 'whatsapp' : 'email';

    if ($saluran === 'whatsapp') {
        $no = preg_replace('/\D/', '', (string) $inv->hp_pic);
        if ($no === '') {
            $this->session->set_flashdata('error', 'Nomor HP PIC klien belum diisi.');
            redirect($balik);
        }
        if ($no[0] === '0') $no = '62' . substr($no, 1);
        $url = 'https://wa.me/' . $no . '?text=' . rawurlencode($pesan);
    } else {
        if (!filter_var($inv->email_pic, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Email PIC klien belum diisi atau tidak valid.');
            redirect($balik);
        }
        $url = 'mailto:' . $inv->email_pic
             . '?subject=' . rawurlencode('Pengingat jatuh tempo invoice ' . $inv->no_invoice)
             . '&body=' . rawurlencode($pesan);
    }

    $this->Pembayaran_model->add_pengiriman($id, $saluran);

    // Halaman perantara (tab baru): membuka aplikasi email / WhatsApp lalu menutup diri
    $h = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $j = json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Membuka...</title>'
       . '<body style="font-family:Arial;padding:32px">Membuka ' . ($saluran === 'whatsapp' ? 'WhatsApp' : 'aplikasi email') . '...'
       . '<p><a href="' . $h . '">Jika tidak terbuka, klik di sini.</a></p>'
       . '<script>location.href=' . $j . ';setTimeout(function(){window.close()},1500);</script></body>';
    exit;
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

    $this->render('form', $this->form_data('Edit Penawaran', $penawaran, $action, $from));
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

    // Ambil data berdasarkan tab
    if ($tab === 'invoice') {
        $data_row = $this->Pembayaran_model->get_invoice($id);
    } else {
        $data_row = $this->Pembayaran_model->get_penawaran($id);
    }

    if (!$data_row) show_404();

    if (!class_exists('Dompdf\Dompdf')) {
        show_error('Dompdf belum terpasang.', 500);
    }

    // Render view PDF yang sesuai
    if ($tab === 'invoice') {
        $html = $this->load->view('content/pembayaran/pdf_invoice', ['invoice' => $data_row], TRUE);
        $no_dokumen = isset($data_row->no_invoice) ? $data_row->no_invoice : 'invoice';
    } else {
        $html = $this->load->view('content/pembayaran/pdf', ['penawaran' => $data_row], TRUE);
        $no_dokumen = isset($data_row->no_penawaran) ? $data_row->no_penawaran : 'penawaran';
    }

    $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => FALSE]);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $nama = preg_replace('/[^A-Za-z0-9_\-]/', '-', $no_dokumen) . '.pdf';
    $dompdf->stream($nama, ['Attachment' => true]);
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

    // ?unduh=1 memaksa unduh; tanpa itu PDF dibuka di browser (untuk pratinjau)
    $paksa = $this->input->get('unduh') ? TRUE : FALSE;
    $disp  = ($ext === 'pdf' && !$paksa) ? 'inline' : 'attachment';

    header('Content-Type: ' . $mime);
    header('Content-Disposition: ' . $disp . '; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

    // null = tidak ada file baru, false = gagal, array = berhasil
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

    // Proses hapus data
    $this->Pembayaran_model->delete($tab, $this->input->post('id', TRUE));

    // Kirim pesan sukses
    $this->session->set_flashdata('success', ($tab === 'invoice' ? 'Invoice' : 'Penawaran') . ' berhasil dihapus.');

    // Kembali ke halaman pembayaran (tanpa halaman putih)
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

    /* ---------- INVOICE: TAMBAH ---------- */
private function data_invoice($title, $inv, $action)
{
    return [
        'title'         => $title,
        'inv'           => $inv,
        'klienList'     => $this->Pembayaran_model->get_klien_options(),
        'projectList'   => $this->Pembayaran_model->get_project_options(),
        'penawaranList' => $inv ? [] : $this->Pembayaran_model->get_penawaran_disetujui(),
        'nomorBaru'     => $this->Pembayaran_model->next_nomor_invoice(date('Y')),
        'pre'           => $inv ? 0 : (int) $this->input->get('penawaran_id'),
        'action'        => $action,
        'formErr'       => $this->form_err,
        'uploadErr'     => $this->upload_err,
    ];
}

private function tambah_invoice()
{
    $this->render('form_invoice', $this->data_invoice('Tambah Invoice', null, site_url('pembayaran/simpan/invoice')));
}

private function simpan_invoice()
{
    if ($this->input->method() !== 'post') redirect('pembayaran?tab=invoice');

    $sumber = $this->input->post('sumber') === 'penawaran' ? 'penawaran' : 'baru';
    $cara   = $this->input->post('cara') === 'unggah' ? 'unggah' : 'template';
    $this->cara_inv = $cara;

    $this->form_validation->set_rules('perihal', 'Perihal', 'required|trim|max_length[200]');
    $this->form_validation->set_rules('jatuh_tempo', 'Tanggal jatuh tempo', 'required|trim|callback__cek_tempo');
    if ($cara === 'unggah') {
        $this->form_validation->set_rules('no_invoice', 'Nomor invoice', 'required|trim|max_length[50]|callback__nomor_invoice_unik');
        $this->form_validation->set_rules('tanggal_invoice', 'Tanggal invoice', 'required|trim');
    }
    if ($sumber === 'penawaran') {
        $this->form_validation->set_rules('penawaran_id', 'Penawaran', 'required|integer');
    } else {
        $this->form_validation->set_rules('klien_id', 'Klien', 'required|integer');
        $this->form_validation->set_rules('project_id', 'Project', 'trim|integer');
    }
    $ok = $this->form_validation->run();

    // Klien & project diambil dari penawaran di server (bukan dari isian form)
    $klien   = (int) $this->input->post('klien_id');
    $project = (int) $this->input->post('project_id');
    $pwId    = NULL;
    if ($ok && $sumber === 'penawaran') {
        $pw = $this->Pembayaran_model->get_penawaran($this->input->post('penawaran_id'));
        if (!$pw || $pw->status !== 'Disetujui') {
            $this->form_err = 'Penawaran harus berstatus Disetujui.';
            $ok = FALSE;
        } else {
            $pwId    = (int) $pw->id;
            $klien   = (int) $pw->klien_id;
            $project = (int) $pw->project_id;
        }
    }

    $file = null;
    if ($ok && $cara === 'unggah') {
        $file = $this->proses_upload(TRUE, 'invoice');
        if ($file === false) $ok = FALSE;
    }
    if (!$ok) {
        return $this->tambah_invoice();
    }

    $data = [
        'klien_id'     => $klien,
        'project_id'   => $project > 0 ? $project : NULL,
        'penawaran_id' => $pwId,
        'perihal'      => $this->input->post('perihal', TRUE),
        'jatuh_tempo'  => $this->input->post('jatuh_tempo', TRUE),
        'status'       => 'Belum dibayar',
    ];

    if ($cara === 'unggah') {
        $data['no_invoice']      = trim((string) $this->input->post('no_invoice', TRUE));
        $data['tanggal_invoice'] = $this->input->post('tanggal_invoice', TRUE);
        $data['dibuat_lewat']    = 'Unggahan';
        $data['isi_surat']       = NULL;
        $data['sertakan_kop']    = 0;
        $data['sertakan_ttd']    = 0;
        $data = array_merge($data, $file);
    } else {
        $data['no_invoice']      = $this->Pembayaran_model->next_nomor_invoice(date('Y'));
        $data['tanggal_invoice'] = date('Y-m-d');
        $data['dibuat_lewat']    = 'Template Baku';
        $data['isi_surat']       = $this->input->post('isi_surat', TRUE);
        $data['sertakan_kop']    = $this->input->post('sertakan_kop') ? 1 : 0;
        $data['sertakan_ttd']    = $this->input->post('sertakan_ttd') ? 1 : 0;
    }

    $this->Pembayaran_model->insert_invoice($data);
    $this->session->set_flashdata('success', 'Invoice berhasil dibuat.');
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