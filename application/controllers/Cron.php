
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('Dokumen_model', 'dokumen_model');
        $this->load->model('Pembayaran_model', 'pembayaran_model');

        // Load konfigurasi
        $this->load->config('email_reminder');
        $this->load->config('fonnte');

        // Paksa Library Email membaca konfigurasi dari email_reminder.php
        $email_config = array(
            'protocol'  => $this->config->item('protocol'),
            'smtp_host' => $this->config->item('smtp_host'),
            'smtp_port' => $this->config->item('smtp_port'),
            'smtp_user' => $this->config->item('smtp_user'),
            'smtp_pass' => $this->config->item('smtp_pass'),
            'mailtype'  => $this->config->item('mailtype'),
            'charset'   => $this->config->item('charset'),
            'newline'   => $this->config->item('newline'),
            'crlf'      => $this->config->item('crlf')
        );

        $this->load->library('email', $email_config);

        // Load helper Fonnte
        $this->load->helper('fonnte');
    }

    // Validasi key akses via browser
    private function _key_valid() {
        $expected = (string) $this->config->item('cron_key');
        $given    = (string) $this->input->get('key');

        return $expected !== '' && hash_equals($expected, $given);
    }

    public function kirim_reminder() {

        // CLI diizinkan; browser wajib menggunakan key valid
        if (!is_cli() && !$this->_key_valid()) {
            show_404();
        }

        $br = is_cli() ? PHP_EOL : '<br>';

        // Ambil email dan nomor WA admin dari database
        $admin = $this->dokumen_model->get_company_settings();

        if (!$admin) {
            echo "Error: Data company_settings tidak ditemukan.";
            return;
        }

        $admin_email = trim((string) $admin->email);
        $admin_wa    = trim((string) $admin->no_telp);

        // ================================================================
        // FASE 1: Reminder dokumen yang AKAN BERAKHIR (H-30)
        // ================================================================
        echo "[FASE 1] Reminder Dokumen Akan Berakhir" . $br;

        $docs_akan = $this->dokumen_model->get_dokumen_akan_berakhir(
            30,
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

        // ================================================================
        // FASE 2: Notifikasi dokumen yang SUDAH EXPIRED
        // ================================================================
        echo "[FASE 2] Notifikasi Dokumen Sudah Expired" . $br;

        $docs_expired = $this->dokumen_model->get_dokumen_sudah_expired(
            $admin_email !== '',
            $admin_wa !== ''
        );

        $r2 = $this->_proses_reminder(
            $docs_expired,
            $admin_email,
            $admin_wa,
            'expired'
        );

        $error_summary = empty($r2['errors'])
            ? ''
            : ' | Detail: ' . implode('; ', array_slice($r2['errors'], 0, 3));

        echo "Total: {$r2['total']}, Email OK: {$r2['email']}, WA OK: {$r2['wa']}, Gagal: {$r2['gagal']}{$error_summary}" . $br . $br;

        echo "Selesai.";
    }

    /**
     * Helper generik untuk proses kirim email + WA ke daftar dokumen.
     *
     * @param array  $docs
     * @param string $admin_email
     * @param string $admin_wa
     * @param string $tipe 'akan_berakhir' | 'expired'
     * @return array ['total','email','wa','gagal','errors']
     */


/**
 * REMINDER INVOICE OTOMATIS
 * Mengirim email pada H-30 dan hari jatuh tempo.
 */
public function kirim_reminder_invoice()
{
    // CLI diizinkan; browser wajib menggunakan key valid
    if (!is_cli() && !$this->_key_valid()) {
        show_404();
    }

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

        // Hanya memproses H-30 dan hari H
        if (!in_array($selisih, [30, 0], true)) {
            $dilewati++;
            continue;
        }

        $offset = $selisih;

        $total++;

        $email = trim((string) $inv->email_pic);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->pembayaran_model->add_pengiriman(
                $inv->id,
                'email',
                $email,
                false,
                'Alamat email PIC kosong atau tidak valid.',
                1,
                $offset
            );

            $gagal++;
            echo "GAGAL: Email invoice {$inv->no_invoice} tidak valid." . $br;
            continue;
        }

        // Cegah pengiriman otomatis berulang
        if ($this->pembayaran_model->sudah_dikirim_otomatis(
            $inv->id,
            'email',
            $offset
        )) {
            $dilewati++;
            echo "SKIP: Invoice {$inv->no_invoice} sudah dikirim." . $br;
            continue;
        }


// ==============================
// WHATSAPP FONNTE
// ==============================

$nomorWa = trim((string) $inv->hp_pic);

if ($nomorWa === '') {

    echo "WA SKIP: Nomor PIC invoice {$inv->no_invoice} kosong." . $br;

} elseif (!$this->pembayaran_model->sudah_dikirim_otomatis(
    $inv->id,
    'whatsapp',
    $offset
)) {

    $waktu = ($offset === 30)
        ? '30 hari lagi'
        : 'hari ini';

    $pesanWa =
        "*PENGINGAT PEMBAYARAN INVOICE*\n\n" .
        "Yth. " . ($inv->nama_pic ?: 'Pelanggan') . ",\n\n" .
        "Kami ingin mengingatkan bahwa invoice berikut akan jatuh tempo {$waktu}.\n\n" .
        "No. Invoice: {$inv->no_invoice}\n" .
        "Perihal: {$inv->perihal}\n" .
        "Jatuh Tempo: " . date('d-m-Y', strtotime($inv->jatuh_tempo)) . "\n\n" .
        "Mohon melakukan pembayaran sesuai jadwal yang ditentukan.\n" .
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

    if ($waBerhasil) {
        echo "WA BERHASIL: Invoice {$inv->no_invoice} ke {$nomorWa}." . $br;
    } else {
        echo "WA GAGAL: Invoice {$inv->no_invoice} - " .
            ($resWa['reason'] ?? 'Alasan tidak tersedia.') . $br;
    }

} else {

    echo "WA SKIP: Invoice {$inv->no_invoice} sudah dikirim." . $br;
}


        $labelWaktu = ($offset === 30)
            ? '30 hari lagi'
            : 'hari ini';

        $subject = ($offset === 30)
            ? 'Pengingat Invoice - H-30'
            : 'Pengingat Invoice - Jatuh Tempo Hari Ini';

        $namaPIC = htmlspecialchars($inv->nama_pic ?: 'Pelanggan', ENT_QUOTES, 'UTF-8');
        $noInvoice = htmlspecialchars($inv->no_invoice, ENT_QUOTES, 'UTF-8');
        $perihal = htmlspecialchars($inv->perihal, ENT_QUOTES, 'UTF-8');
        $tanggal = date('d-m-Y', strtotime($inv->jatuh_tempo));

        $message = '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
                <h2>Pengingat Pembayaran Invoice</h2>

                <p>Yth. ' . $namaPIC . ',</p>

                <p>
                    Kami ingin mengingatkan bahwa invoice berikut
                    memiliki jadwal jatuh tempo ' . $labelWaktu . '.
                </p>

                <table cellpadding="8" cellspacing="0"
                    style="border-collapse:collapse;border:1px solid #ddd;">
                    <tr>
                        <td><strong>No. Invoice</strong></td>
                        <td>' . $noInvoice . '</td>
                    </tr>
                    <tr>
                        <td><strong>Perihal</strong></td>
                        <td>' . $perihal . '</td>
                    </tr>
                    <tr>
                        <td><strong>Jatuh Tempo</strong></td>
                        <td>' . $tanggal . '</td>
                    </tr>
                </table>

                <p>
                    Mohon melakukan pembayaran sesuai jadwal yang telah
                    ditentukan. Jika pembayaran sudah dilakukan,
                    silakan abaikan email ini.
                </p>

                <p>Terima kasih.</p>
                <hr>
                <small>Tridatu Solution</small>
            </div>
        ';

        $this->email->clear();

        $this->email->from(
            $this->config->item('smtp_user'),
            'Tridatu Solution'
        );

        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message($message);

        $success = $this->email->send();

        $keterangan = '';

        if (!$success) {
            $debug = $this->email->print_debugger(['headers']);
            $keterangan = trim(
                preg_replace('/\s+/', ' ', strip_tags($debug))
            );
        }

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
            echo "BERHASIL: Invoice {$inv->no_invoice} ke {$email}." . $br;
        } else {
            $gagal++;
            echo "GAGAL: Invoice {$inv->no_invoice} - {$keterangan}" . $br;
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
                : 'last_reminder_sent_at';

            $wa_column = $tipe === 'expired'
                ? 'wa_expired_notified_at'
                : 'wa_reminder_sent_at';

            // Sisa hari: positif jika belum expired, negatif jika sudah lewat
            $sisa_hari = (int) (new DateTime(date('Y-m-d')))
                ->diff(new DateTime($doc->tanggal_berakhir))
                ->format('%r%a');

            $email_ok = ($admin_email === '' || !empty($doc->{$email_column}));
            $wa_ok    = ($admin_wa === '' || !empty($doc->{$wa_column}));

            // ---------- A. EMAIL ----------
            if ($admin_email !== '' && empty($doc->{$email_column})) {

                $this->email->clear();

                $this->email->from(
                    $this->config->item('smtp_user'),
                    'Sistem Tridatu'
                );

                $this->email->to($admin_email);

                if ($tipe === 'expired') {

                    $this->email->subject(
                        '[EXPIRED] Dokumen "' . $doc->nama_dokumen . '" Sudah Berakhir'
                    );

                    $this->email->message(
                        $this->_template_email_expired($doc, $sisa_hari)
                    );

                } else {

                    $this->email->subject(
                        '[PENGINGAT] Dokumen "' . $doc->nama_dokumen . '" Berakhir dalam ' . $sisa_hari . ' Hari'
                    );

                    $this->email->message(
                        $this->_template_email_akan_berakhir($doc, $sisa_hari)
                    );
                }

                if ($this->email->send()) {

                    $result['email']++;

                    $email_ok = $tipe === 'expired'
                        ? $this->dokumen_model->mark_expired_notified($doc->id)
                        : $this->dokumen_model->mark_reminder_sent($doc->id);

                    if (!$email_ok) {
                        $result['errors'][] =
                            'Dokumen ID ' . $doc->id .
                            ': email terkirim tetapi statusnya gagal disimpan.';
                    }

                } else {

                    $email_error = trim(
                        preg_replace(
                            '/\s+/',
                            ' ',
                            strip_tags($this->email->print_debugger())
                        )
                    );

                    $email_error = $email_error !== ''
                        ? $email_error
                        : 'server email menolak pengiriman.';

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

                    $wa_ok = $tipe === 'expired'
                        ? $this->dokumen_model->mark_expired_wa_notified($doc->id)
                        : $this->dokumen_model->mark_reminder_wa_sent($doc->id);

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

        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="background: #d97706; color: white; padding: 20px;">
                <h2 style="margin: 0;">⚠️ Pengingat Dokumen</h2>
            </div>
            <div style="padding: 24px;">
                <p>Halo Admin,</p>
                <p>Dokumen berikut akan segera berakhir. Mohon segera ditindaklanjuti:</p>
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
                        <td style="padding: 8px 0; font-weight: bold; color: #d97706;">' . (int) $sisa_hari . ' hari lagi</td>
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
