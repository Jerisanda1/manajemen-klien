<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Dokumen_model', 'dokumen_model');

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

        // Load helper
        $this->load->helper('fonnte');
    }

    // Validasi key akses via browser (key dibaca dari config, bukan hardcode)
    private function _key_valid() {
        $expected = (string) $this->config->item('cron_key');
        $given    = (string) $this->input->get('key');
        return $expected !== '' && hash_equals($expected, $given);
    }

    public function kirim_reminder() {
        // CLI (Task Scheduler/cron) diizinkan; browser wajib pakai key yang valid.
        if ( ! is_cli() && ! $this->_key_valid()) {
            show_404();
        }

        $br = is_cli() ? PHP_EOL : '<br>';

        // Ambil email & no_hp admin dari database
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
        $docs_akan = $this->dokumen_model->get_dokumen_akan_berakhir(30, $admin_email !== '', $admin_wa !== '');
        $r1 = $this->_proses_reminder($docs_akan, $admin_email, $admin_wa, 'akan_berakhir');
        $error_summary = empty($r1['errors']) ? '' : ' | Detail: ' . implode('; ', array_slice($r1['errors'], 0, 3));
        echo "Total: {$r1['total']}, Email OK: {$r1['email']}, WA OK: {$r1['wa']}, Gagal: {$r1['gagal']}{$error_summary}" . $br . $br;

        // ================================================================
        // FASE 2: Notifikasi dokumen yang SUDAH EXPIRED
        // ================================================================
        echo "[FASE 2] Notifikasi Dokumen Sudah Expired" . $br;
        $docs_expired = $this->dokumen_model->get_dokumen_sudah_expired($admin_email !== '', $admin_wa !== '');
        $r2 = $this->_proses_reminder($docs_expired, $admin_email, $admin_wa, 'expired');
        $error_summary = empty($r2['errors']) ? '' : ' | Detail: ' . implode('; ', array_slice($r2['errors'], 0, 3));
        echo "Total: {$r2['total']}, Email OK: {$r2['email']}, WA OK: {$r2['wa']}, Gagal: {$r2['gagal']}{$error_summary}" . $br . $br;

        echo "Selesai.";
    }

    /**
     * Helper generik untuk proses kirim email + WA ke daftar dokumen.
     * @param  array  $docs
     * @param  string $admin_email
     * @param  string $admin_wa
     * @param  string $tipe  'akan_berakhir' | 'expired'
     * @return array  ['total','email','wa','gagal','errors']
     */
    private function _proses_reminder($docs, $admin_email, $admin_wa, $tipe) {
        $result = ['total' => 0, 'email' => 0, 'wa' => 0, 'gagal' => 0, 'errors' => []];

        if (empty($docs)) return $result;
        if ($admin_wa === '') {
            $result['errors'][] = 'Nomor WhatsApp admin kosong di company_settings.no_telp.';
        }

        foreach ($docs as $doc) {
            $result['total']++;
            $email_column = $tipe === 'expired' ? 'expired_notified_at' : 'last_reminder_sent_at';
            $wa_column = $tipe === 'expired' ? 'wa_expired_notified_at' : 'wa_reminder_sent_at';

            // Sisa hari: positif kalau belum expired, negatif kalau sudah lewat
            $sisa_hari = (int)(new DateTime(date('Y-m-d')))
                ->diff(new DateTime($doc->tanggal_berakhir))
                ->format('%r%a');

            $email_ok = ($admin_email === '' || !empty($doc->{$email_column}));
            $wa_ok    = ($admin_wa === '' || !empty($doc->{$wa_column}));

            // ---------- A. EMAIL ----------
            if ($admin_email !== '' && empty($doc->{$email_column})) {
                $this->email->clear();
                $this->email->from($this->config->item('smtp_user'), 'Sistem Tridatu');
                $this->email->to($admin_email);

                if ($tipe === 'expired') {
                    $this->email->subject('[EXPIRED] Dokumen "' . $doc->nama_dokumen . '" Sudah Berakhir');
                    $this->email->message($this->_template_email_expired($doc, $sisa_hari));
                } else {
                    $this->email->subject('[PENGINGAT] Dokumen "' . $doc->nama_dokumen . '" Berakhir dalam ' . $sisa_hari . ' Hari');
                    $this->email->message($this->_template_email_akan_berakhir($doc, $sisa_hari));
                }

                if ($this->email->send()) {
                    $result['email']++;
                    $email_ok = $tipe === 'expired'
                        ? $this->dokumen_model->mark_expired_notified($doc->id)
                        : $this->dokumen_model->mark_reminder_sent($doc->id);
                    if (!$email_ok) {
                        $result['errors'][] = 'Dokumen ID ' . $doc->id . ': email terkirim tetapi statusnya gagal disimpan.';
                    }
                } else {
                    $email_error = trim(preg_replace('/\s+/', ' ', strip_tags($this->email->print_debugger())));
                    $email_error = $email_error !== '' ? $email_error : 'server email menolak pengiriman.';
                    $result['errors'][] = 'Dokumen ID ' . $doc->id . ' email: ' . $email_error;
                    log_message('error', 'Cron reminder: gagal email dokumen ID ' . $doc->id . ' - ' . $email_error);
                }
            }

            // ---------- B. WHATSAPP ----------
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

                $res_wa = kirim_wa_fonnte($admin_wa, $pesan_wa);
                if (isset($res_wa['status']) && $res_wa['status'] === true) {
                    $result['wa']++;
                    $wa_ok = $tipe === 'expired'
                        ? $this->dokumen_model->mark_expired_wa_notified($doc->id)
                        : $this->dokumen_model->mark_reminder_wa_sent($doc->id);
                    if (!$wa_ok) {
                        $result['errors'][] = 'Dokumen ID ' . $doc->id . ': WhatsApp terkirim tetapi statusnya gagal disimpan.';
                    }
                } else {
                    $reason = isset($res_wa['reason']) ? (string) $res_wa['reason'] : 'alasan tidak tersedia';
                    $result['errors'][] = 'Dokumen ID ' . $doc->id . ' WhatsApp: ' . $reason;
                    log_message('error', 'Cron reminder: gagal kirim WA dokumen ID ' . $doc->id . ' - ' . $reason);
                }
            }

            if (($admin_email !== '' && !$email_ok) || ($admin_wa !== '' && !$wa_ok)) {
                $result['gagal']++;
            }
        }

        return $result;
    }

    // ---------- TEMPLATE EMAIL: AKAN BERAKHIR (warna kuning/orange) ----------
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

    // ---------- TEMPLATE EMAIL: EXPIRED (warna merah) ----------
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