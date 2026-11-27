
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('Dokumen_model', 'dokumen_model');
        $this->load->model('Pembayaran_model', 'pembayaran_model');
        $this->load->model('Project_model', 'project_model');

        $this->load->config('email_reminder', TRUE);

        $this->load->library('Notifikasi');

        // Load helper Fonnte
        $this->load->helper('fonnte');
    }

    // Validasi key akses via browser
    private function _key_valid() {
        $expected = (string) $this->config->item('cron_key', 'email_reminder');
        $given    = (string) $this->input->get('key');

        return $expected !== '' && hash_equals($expected, $given);
    }

    public function kirim_reminder() {

        // CLI diizinkan; browser wajib menggunakan key valid
        if (!is_cli() && !$this->_key_valid()) {
            show_404();
        }

        date_default_timezone_set('Asia/Makassar');
        $br = is_cli() ? PHP_EOL : '<br>';

        // Ambil email dan nomor WA admin dari database
        $admin = $this->dokumen_model->get_company_settings();

        if (!$admin) {
            echo "Error: Data company_settings tidak ditemukan.";
            return;
        }

        $admin_email = trim((string) $admin->email);
        $admin_wa    = trim((string) $admin->no_telp);

        echo "[REMINDER DOKUMEN]" . $br;

        $docs_akan = $this->dokumen_model->get_dokumen_akan_berakhir(
            $admin_email !== '',
            $admin_wa !== ''
        );

        $r1 = $this->_proses_reminder(
            $docs_akan,
            $admin_email,
            $admin_wa,
            'akan_berakhir'
        );

        $error_summary = empty($r1['errors'])
            ? ''
            : ' | Detail: ' . implode('; ', array_slice($r1['errors'], 0, 3));

        echo "Total: {$r1['total']}, Email OK: {$r1['email']}, WA OK: {$r1['wa']}, Gagal: {$r1['gagal']}{$error_summary}" . $br . $br;

        echo "[PENGINGAT DOKUMEN HARI H]" . $br;
        $docs_hari_h = $this->dokumen_model->get_dokumen_berakhir_hari_ini(
            $admin_email !== '',
            $admin_wa !== ''
        );
        $hariHResult = $this->_proses_reminder(
            $docs_hari_h,
            $admin_email,
            $admin_wa,
            'hari_h'
        );
        echo "Total: {$hariHResult['total']}, Email OK: {$hariHResult['email']}, WA OK: {$hariHResult['wa']}, Gagal: {$hariHResult['gagal']}" . $br . $br;

        echo "[NOTIFIKASI DOKUMEN KEDALUWARSA]" . $br;
        $docs_expired = $this->dokumen_model->get_dokumen_sudah_expired(
            $admin_email !== '',
            $admin_wa !== ''
        );
        $expiredResult = $this->_proses_reminder(
            $docs_expired,
            $admin_email,
            $admin_wa,
            'expired'
        );
        echo "Total: {$expiredResult['total']}, Email OK: {$expiredResult['email']}, WA OK: {$expiredResult['wa']}, Gagal: {$expiredResult['gagal']}" . $br . $br;

        echo $br . "[REMINDER PROJECT]" . $br;
        $projects = $this->project_model->get_projects_for_reminder(
            $admin_email !== '',
            $admin_wa !== ''
        );
        $projectResult = $this->_proses_reminder_project(
            $projects,
            $admin_email,
            $admin_wa
        );
        echo "Total: {$projectResult['total']}, Email OK: {$projectResult['email']}, WA OK: {$projectResult['wa']}, Gagal: {$projectResult['gagal']}" . $br;

        echo "Selesai.";
    }

    private function _proses_reminder_project($projects, $admin_email, $admin_wa)
    {
        $result = ['total' => 0, 'email' => 0, 'wa' => 0, 'gagal' => 0];

        foreach ($projects as $project) {
            $result['total']++;
            $daysLeft = (int) (new DateTime(date('Y-m-d')))
                ->diff(new DateTime($project->tanggal_selesai))
                ->format('%r%a');
            $isOverdue = $daysLeft < 0;
            $projectUrl = site_url('project/detail/' . (int) $project->id);
            $emailSentAt = $isOverdue
                ? $project->expired_notified_at
                : $project->last_reminder_sent_at;
            $waSentAt = $isOverdue
                ? $project->wa_expired_notified_at
                : $project->wa_reminder_sent_at;

            if ($admin_email !== '' && empty($emailSentAt)) {
                $subject =
                    $isOverdue
                        ? '[TERLAMBAT] Project "' . $project->nama_project . '" melewati tanggal selesai'
                        : '[PENGINGAT] Project "' . $project->nama_project
                            . ($daysLeft === 0 ? '" selesai hari ini' : '" selesai dalam ' . $daysLeft . ' hari');
                $message =
                    '<p>Project <strong>' . html_escape($project->nama_project)
                    . '</strong> untuk klien ' . html_escape($project->nama_klien) . (
                        $isOverdue
                            ? ' telah melewati tanggal selesai ' . abs($daysLeft) . ' hari yang lalu ('
                            : ($daysLeft === 0
                                ? ' dijadwalkan selesai hari ini ('
                                : ' dijadwalkan selesai dalam ' . $daysLeft . ' hari (')
                    )
                    . date('d M Y', strtotime($project->tanggal_selesai)) . ').</p>'
                    . '<p><a href="' . html_escape($projectUrl) . '">Lihat project</a></p>';
                list($emailSent, $emailError) = $this->notifikasi->email(
                    $admin_email,
                    $subject,
                    $message
                );

                if ($emailSent) {
                    $marked = $isOverdue
                        ? $this->project_model->mark_expired_notified($project->id)
                        : $this->project_model->mark_reminder_sent($project->id);
                    if ($marked) {
                        $result['email']++;
                    } else {
                        $result['gagal']++;
                        log_message('error', 'Gagal menyimpan status reminder email project ID ' . $project->id);
                    }
                } else {
                    $result['gagal']++;
                    log_message(
                        'error',
                        'Gagal mengirim reminder email project ID ' . $project->id . ': ' . $emailError
                    );
                }
            }

            if ($admin_wa !== '' && empty($waSentAt)) {
                $message = ($isOverdue ? "PROJECT TERLAMBAT\n\n" : "PENGINGAT PROJECT\n\n")
                    . "Project: {$project->nama_project}\n"
                    . "Klien: {$project->nama_klien}\n"
                    . "PIC: {$project->nama_pic}\n"
                    . "Tanggal selesai: " . date('d M Y', strtotime($project->tanggal_selesai)) . "\n"
                    . ($isOverdue
                        ? "Terlambat: " . abs($daysLeft) . " hari\n"
                        : ($daysLeft === 0 ? "Sisa: hari ini\n" : "Sisa: {$daysLeft} hari\n"))
                    . "Detail: {$projectUrl}";
                $response = kirim_wa_fonnte($admin_wa, $message);

                if (!empty($response['status'])) {
                    $marked = $isOverdue
                        ? $this->project_model->mark_expired_wa_notified($project->id)
                        : $this->project_model->mark_reminder_wa_sent($project->id);
                    if ($marked) {
                        $result['wa']++;
                    } else {
                        $result['gagal']++;
                        log_message('error', 'Gagal menyimpan status reminder WhatsApp project ID ' . $project->id);
                    }
                } else {
                    $result['gagal']++;
                    log_message(
                        'error',
                        'Gagal mengirim reminder WhatsApp project ID ' . $project->id
                        . ': ' . (isset($response['reason']) ? $response['reason'] : 'Tidak ada keterangan.')
                    );
                }
            }
        }

        return $result;
    }

/**
 * REMINDER INVOICE OTOMATIS
 * Menggunakan jarak pengingat H-N yang diatur pada data klien.
 */
public function kirim_reminder_invoice()
{
    // CLI diizinkan; browser wajib menggunakan key valid
    if (!is_cli() && !$this->_key_valid()) {
        show_404();
    } 

    date_default_timezone_set('Asia/Makassar');

    $this->load->library('invoice_reminder');
    $channels = $this->invoice_reminder->get_channels();

    $br = is_cli() ? PHP_EOL : '<br>';

    echo "[REMINDER INVOICE]" . $br;

    $today = new DateTime(date('Y-m-d'));

    $invoices = $this->pembayaran_model->get_invoice_belum_bayar();

    if (empty($invoices)) {
        echo "Tidak ada invoice yang belum dibayar." . $br;
        return;
    }

    $total = 0;
    $berhasil = 0;
    $gagal = 0;
    $dilewati = 0;

    foreach ($invoices as $inv) {

        $jatuhTempo = new DateTime(date('Y-m-d', strtotime($inv->jatuh_tempo)));

        $selisih = (int) $today->diff($jatuhTempo)->format('%r%a');

        $offset = (int) $inv->reminder_days_before;
        if ($offset < 1 || $offset > 365 || $selisih !== $offset) {
            $dilewati++;
            continue;
        }

        $total++;


// ==============================
// WHATSAPP FONNTE
// ==============================

if ($channels['whatsapp']) {
$nomorWa = trim((string) $inv->hp_pic);

if ($nomorWa === '') {

    echo "WA SKIP: Nomor PIC invoice {$inv->no_invoice} kosong." . $br;

} elseif (!$this->pembayaran_model->sudah_dikirim_otomatis(
    $inv->id,
    'whatsapp',
    $offset
)) {

    $waktu = $offset . ' hari lagi';

    $pesanWa =
        "*PENGINGAT PEMBAYARAN INVOICE*\n\n" .
        "Yth. " . ($inv->nama_pic ?: 'Pelanggan') . ",\n\n" .
        "Invoice berikut akan jatuh tempo {$waktu}.\n\n" .
        "No. Invoice: {$inv->no_invoice}\n" .
        "Perihal: {$inv->perihal}\n" .
        "Jatuh Tempo: " . date('d-m-Y', strtotime($inv->jatuh_tempo)) . "\n\n" .
        "Mohon melakukan pembayaran sesuai jadwal.\n" .
        "Jika pembayaran sudah dilakukan, silakan abaikan pesan ini.\n\n" .
        "Terima kasih.\nTridatu Solution";

    $resWa = kirim_wa_fonnte($nomorWa, $pesanWa);

    $waBerhasil = isset($resWa['status']) && $resWa['status'] === true;

    $this->pembayaran_model->add_pengiriman(
        $inv->id,
        'whatsapp',
        $nomorWa,
        $waBerhasil,
        $waBerhasil ? '' : ($resWa['reason'] ?? 'Pengiriman WhatsApp gagal.'),
        1,
        $offset
    );

    echo $waBerhasil
        ? "WA BERHASIL: Invoice {$inv->no_invoice} ke {$nomorWa}." . $br
        : "WA GAGAL: Invoice {$inv->no_invoice} - " .
            ($resWa['reason'] ?? 'Alasan tidak tersedia.') . $br;

} else {

    echo "WA SKIP: Invoice {$inv->no_invoice} sudah dikirim." . $br;
}
}


// ==============================
// EMAIL MELALUI NOTIFIKASI
// ==============================

if ($channels['email']) {
$email = trim((string) $inv->email_pic);

if ($this->pembayaran_model->sudah_dikirim_otomatis($inv->id, 'email', $offset)) {
    echo "EMAIL SKIP: Invoice {$inv->no_invoice} sudah dikirim." . $br;
} elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $keterangan = 'Alamat email PIC kosong atau tidak valid.';
    $this->pembayaran_model->add_pengiriman(
        $inv->id,
        'email',
        $email,
        false,
        $keterangan,
        1,
        $offset
    );
    $gagal++;
    echo "EMAIL GAGAL: Invoice {$inv->no_invoice} - {$keterangan}" . $br;
} else {
    list($subject, $message) = $this->notifikasi->pesan_pengingat($inv, $offset);
    list($success, $keterangan) = $this->notifikasi->email($email, $subject, $message);

    $this->pembayaran_model->add_pengiriman(
        $inv->id,
        'email',
        $email,
        $success,
        $keterangan,
        1,
        $offset
    );

    if ($success) {
        $berhasil++;
        echo "EMAIL BERHASIL: Invoice {$inv->no_invoice} ke {$email}." . $br;
    } else {
        $gagal++;
        echo "EMAIL GAGAL: Invoice {$inv->no_invoice} - {$keterangan}" . $br;
    }
}
        }
    }

    echo $br . "Selesai memproses reminder invoice." . $br;
    echo "Total diproses: {$total}" . $br;
    echo "Email berhasil: {$berhasil}" . $br;
    echo "Email gagal: {$gagal}" . $br;
    echo "Dilewati: {$dilewati}" . $br;
}

    private function _proses_reminder($docs, $admin_email, $admin_wa, $tipe) {

        $result = [
            'total'  => 0,
            'email'  => 0,
            'wa'     => 0,
            'gagal'  => 0,
            'errors' => []
        ];

        if (empty($docs)) {
            return $result;
        }

        if ($admin_wa === '') {
            $result['errors'][] = 'Nomor WhatsApp admin kosong di company_settings.no_telp.';
        }

        foreach ($docs as $doc) {

            $result['total']++;

            $email_column = $tipe === 'expired'
                ? 'expired_notified_at'
                : ($tipe === 'hari_h' ? 'expiry_day_notified_at' : 'last_reminder_sent_at');

            $wa_column = $tipe === 'expired'
                ? 'wa_expired_notified_at'
                : ($tipe === 'hari_h' ? 'wa_expiry_day_notified_at' : 'wa_reminder_sent_at');

            // Sisa hari: positif jika belum expired, negatif jika sudah lewat
            $sisa_hari = (int) (new DateTime(date('Y-m-d')))
                ->diff(new DateTime($doc->tanggal_berakhir))
                ->format('%r%a');

            $email_ok = ($admin_email === '' || !empty($doc->{$email_column}));
            $wa_ok    = ($admin_wa === '' || !empty($doc->{$wa_column}));

            // ---------- A. EMAIL ----------
            if ($admin_email !== '' && empty($doc->{$email_column})) {

                if ($tipe === 'expired') {

                    $subject = '[EXPIRED] Dokumen "' . $doc->nama_dokumen . '" Sudah Berakhir';
                    $message = $this->_template_email_expired($doc, $sisa_hari);

                } elseif ($tipe === 'hari_h') {

                    $subject = '[HARI H] Dokumen "' . $doc->nama_dokumen . '" Berakhir Hari Ini';
                    $message = $this->_template_email_akan_berakhir($doc, 0);

                } else {

                    $subject = '[PENGINGAT] Dokumen "' . $doc->nama_dokumen . '" Berakhir dalam ' . $sisa_hari . ' Hari';
                    $message = $this->_template_email_akan_berakhir($doc, $sisa_hari);
                }

                list($emailSent, $email_error) = $this->notifikasi->email(
                    $admin_email,
                    $subject,
                    $message
                );

                if ($emailSent) {

                    $result['email']++;

                    if ($tipe === 'expired') {
                        $email_ok = $this->dokumen_model->mark_expired_notified($doc->id);
                    } elseif ($tipe === 'hari_h') {
                        $email_ok = $this->dokumen_model->mark_expiry_day_notified($doc->id);
                    } else {
                        $email_ok = $this->dokumen_model->mark_reminder_sent($doc->id);
                    }

                    if (!$email_ok) {
                        $result['errors'][] =
                            'Dokumen ID ' . $doc->id .
                            ': email terkirim tetapi statusnya gagal disimpan.';
                    }

                } else {

                    $result['errors'][] =
                        'Dokumen ID ' . $doc->id . ' email: ' . $email_error;

                    log_message(
                        'error',
                        'Cron reminder: gagal email dokumen ID ' .
                        $doc->id . ' - ' . $email_error
                    );
                }
            }

            // ---------- B. WHATSAPP FONNTE ----------
            if ($admin_wa !== '' && empty($doc->{$wa_column})) {

                if ($tipe === 'expired') {

                    $pesan_wa = "🔴 *DOKUMEN SUDAH BERAKHIR* 🔴\n\n" .
                                "Halo Admin,\n" .
                                "Dokumen berikut telah melewati tanggal berakhir:\n\n" .
                                "📄 *Nama*: {$doc->nama_dokumen}\n" .
                                "🏢 *Klien*: {$doc->nama_klien}\n" .
                                "👤 *PIC*: {$doc->nama_pic}\n" .
                                "📅 *Berakhir*: " . date('d M Y', strtotime($doc->tanggal_berakhir)) . "\n" .
                                "⏳ *Lewat*: " . abs($sisa_hari) . " hari\n\n" .
                                "Segera tindak lanjuti perpanjangan dokumen.\n\n" .
                                "🔗 Detail: " . site_url('dokumen/detail/' . $doc->id);

                } elseif ($tipe === 'hari_h') {

                    $pesan_wa = "⚠️ *DOKUMEN BERAKHIR HARI INI* ⚠️\n\n" .
                                "Halo Admin,\n" .
                                "Dokumen berikut berakhir hari ini:\n\n" .
                                "📄 *Nama*: {$doc->nama_dokumen}\n" .
                                "🏢 *Klien*: {$doc->nama_klien}\n" .
                                "👤 *PIC*: {$doc->nama_pic}\n" .
                                "📅 *Berakhir*: " . date('d M Y', strtotime($doc->tanggal_berakhir)) . "\n\n" .
                                "Mohon segera tindak lanjuti perpanjangan dokumen.\n\n" .
                                "🔗 Detail: " . site_url('dokumen/detail/' . $doc->id);

                } else {

                    $pesan_wa = "⚠️ *PENGINGAT DOKUMEN* ⚠️\n\n" .
                                "Halo Admin,\n" .
                                "Dokumen berikut akan segera berakhir:\n\n" .
                                "📄 *Nama*: {$doc->nama_dokumen}\n" .
                                "🏢 *Klien*: {$doc->nama_klien}\n" .
                                "👤 *PIC*: {$doc->nama_pic}\n" .
                                "📅 *Berakhir*: " . date('d M Y', strtotime($doc->tanggal_berakhir)) . "\n" .
                                "⏳ *Sisa Waktu*: {$sisa_hari} hari lagi\n\n" .
                                "Mohon segera hubungi PIC klien untuk proses perpanjangan.\n\n" .
                                "🔗 Detail: " . site_url('dokumen/detail/' . $doc->id);
                }

                // Kirim melalui Fonnte
                $res_wa = kirim_wa_fonnte($admin_wa, $pesan_wa);

                if (isset($res_wa['status']) && $res_wa['status'] === true) {

                    $result['wa']++;

                    if ($tipe === 'expired') {
                        $wa_ok = $this->dokumen_model->mark_expired_wa_notified($doc->id);
                    } elseif ($tipe === 'hari_h') {
                        $wa_ok = $this->dokumen_model->mark_expiry_day_wa_notified($doc->id);
                    } else {
                        $wa_ok = $this->dokumen_model->mark_reminder_wa_sent($doc->id);
                    }

                    if (!$wa_ok) {
                        $result['errors'][] =
                            'Dokumen ID ' . $doc->id .
                            ': WhatsApp terkirim tetapi statusnya gagal disimpan.';
                    }

                } else {

                    $reason = isset($res_wa['reason'])
                        ? (string) $res_wa['reason']
                        : 'alasan tidak tersedia';

                    $result['errors'][] =
                        'Dokumen ID ' . $doc->id . ' WhatsApp: ' . $reason;

                    log_message(
                        'error',
                        'Cron reminder: gagal kirim WA dokumen ID ' .
                        $doc->id . ' - ' . $reason
                    );
                }
            }

            // ---------- C. PENGHITUNGAN KEGAGALAN ----------
            if (
                ($admin_email !== '' && !$email_ok) ||
                ($admin_wa !== '' && !$wa_ok)
            ) {
                $result['gagal']++;
            }
        }

        return $result;
    }

    // ---------- TEMPLATE EMAIL: AKAN BERAKHIR ----------
    private function _template_email_akan_berakhir($doc, $sisa_hari) {

        $nama_dok   = htmlspecialchars((string) $doc->nama_dokumen);
        $nama_klien = htmlspecialchars((string) $doc->nama_klien);
        $nama_pic   = htmlspecialchars((string) $doc->nama_pic);
        $keterangan_sisa = (int) $sisa_hari === 0
            ? 'berakhir hari ini'
            : 'berakhir dalam ' . (int) $sisa_hari . ' hari';
        $judul = (int) $sisa_hari === 0 ? 'Dokumen Berakhir Hari Ini' : 'Pengingat Dokumen';

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="background: #d97706; color: white; padding: 20px;">
                <h2 style="margin: 0;">⚠️ ' . $judul . '</h2>
            </div>
            <div style="padding: 24px;">
                <p>Halo Admin,</p>
                <p>Dokumen berikut ' . $keterangan_sisa . '. Mohon segera ditindaklanjuti:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; width: 120px;">Nama Dokumen</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . $nama_dok . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Klien</td>
                        <td style="padding: 8px 0;">' . $nama_klien . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">PIC Klien</td>
                        <td style="padding: 8px 0;">' . $nama_pic . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Tanggal Berakhir</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #d97706;">' . date('d M Y', strtotime($doc->tanggal_berakhir)) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Sisa Waktu</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #d97706;">' . ((int) $sisa_hari === 0 ? 'Hari ini' : (int) $sisa_hari . ' hari lagi') . '</td>
                    </tr>
                </table>
                <p>Mohon segera hubungi PIC klien untuk proses perpanjangan dokumen.</p>
                <a href="' . site_url('dokumen/detail/' . $doc->id) . '" style="display: inline-block; background: #d97706; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 12px;">Lihat Detail Dokumen</a>
            </div>
            <div style="background: #f4f6f9; padding: 16px; text-align: center; font-size: 12px; color: #6b7280;">
                Email otomatis dari Sistem Tridatu.
            </div>
        </div>
        ';
    }

    // ---------- TEMPLATE EMAIL: EXPIRED ----------
    private function _template_email_expired($doc, $sisa_hari) {

        $nama_dok   = htmlspecialchars((string) $doc->nama_dokumen);
        $nama_klien = htmlspecialchars((string) $doc->nama_klien);
        $nama_pic   = htmlspecialchars((string) $doc->nama_pic);
        $lewat_hari = abs((int) $sisa_hari);

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="background: #dc2626; color: white; padding: 20px;">
                <h2 style="margin: 0;">🔴 Dokumen Sudah Berakhir</h2>
            </div>
            <div style="padding: 24px;">
                <p>Halo Admin,</p>
                <p>Dokumen berikut <strong style="color:#dc2626;">telah melewati tanggal berakhir</strong>. Segera lakukan tindak lanjut:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; width: 120px;">Nama Dokumen</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . $nama_dok . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Klien</td>
                        <td style="padding: 8px 0;">' . $nama_klien . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">PIC Klien</td>
                        <td style="padding: 8px 0;">' . $nama_pic . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Tanggal Berakhir</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #dc2626;">' . date('d M Y', strtotime($doc->tanggal_berakhir)) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Sudah Lewat</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #dc2626;">' . $lewat_hari . ' hari</td>
                    </tr>
                </table>
                <p>Mohon segera hubungi PIC klien untuk proses perpanjangan atau penutupan dokumen.</p>
                <a href="' . site_url('dokumen/detail/' . $doc->id) . '" style="display: inline-block; background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 12px;">Lihat Detail Dokumen</a>
            </div>
            <div style="background: #f4f6f9; padding: 16px; text-align: center; font-size: 12px; color: #6b7280;">
                Email otomatis dari Sistem Tridatu.
            </div>
        </div>
        ';
    }
}
