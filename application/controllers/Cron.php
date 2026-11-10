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

    public function kirim_reminder()
    {
        // PERBAIKAN: Jika lewat browser wajib pakai key, jika lewat CLI (Task Scheduler) izinkan.
        if ( ! is_cli() && $this->input->get('key') !== 'tridatu-rahasia-2026') {
            show_404();
        }

        // Ambil email & no_hp admin dari database
        $admin = $this->dokumen_model->get_company_settings();
        if (!$admin) {
            echo "Error: Data company_settings tidak ditemukan.";
            return;
        }

        $admin_email = $admin->email;
        $admin_wa    = $admin->no_telp;

        // Ambil dokumen yang akan berakhir dalam 30 hari
        $docs = $this->dokumen_model->get_dokumen_akan_berakhir(30);

        if (empty($docs)) {
            echo "Tidak ada dokumen yang akan berakhir dalam 30 hari ke depan.";
            return;
        }

        $sukses_email = 0;
        $sukses_wa    = 0;

        // Looping setiap dokumen
        foreach ($docs as $doc) {
            // Hitung sisa hari
            $sisa_hari = (int)(new DateTime(date('Y-m-d')))
                ->diff(new DateTime($doc->tanggal_berakhir))
                ->format('%r%a');

            // ==========================================
            // A. PROSES KIRIM EMAIL
            // ==========================================
            $this->email->clear();
            $this->email->from($this->config->item('smtp_user'), 'Sistem Tridatu');
            $this->email->to($admin_email);
            $this->email->subject('[PENGINGAT] Dokumen "' . $doc->nama_dokumen . '" Berakhir dalam ' . $sisa_hari . ' Hari');
            $this->email->message($this->_template_email($doc, $sisa_hari));

            if ($this->email->send()) {
                $sukses_email++;
            } else {
                echo "<div style='background:#fee; padding:16px; border:1px solid #f00; margin:10px;'>";
                echo "<b>Gagal kirim email ke: {$admin_email}</b><br><br>";
                echo $this->email->print_debugger();
                echo "</div>";
            }

            // ==========================================
            // B. PROSES KIRIM WHATSAPP
            // ==========================================
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

            $res_wa = kirim_wa_fonnte($admin_wa, $pesan_wa);
            
            // Cek jika Fonnte merespons sukses
            if ($res_wa && isset($res_wa['status']) && $res_wa['status'] == true) {
                $sukses_wa++;
            }

            // ==========================================
            // C. TANDAI SUDAH DIINGATKAN
            // ==========================================
            // Agar besok tidak dikirim email/WA lagi untuk dokumen yang sama
            $this->dokumen_model->mark_reminder_sent($doc->id);
        }

        echo "Proses Selesai. <br> Email terkirim: {$sukses_email} <br> WA terkirim: {$sukses_wa}";
    }

    // Template HTML untuk isi Email
    private function _template_email($doc, $sisa_hari)
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
            <div style="background: #9f2b2b; color: white; padding: 20px;">
                <h2 style="margin: 0;">Pengingat Dokumen</h2>
            </div>
            <div style="padding: 24px;">
                <p>Halo Admin,</p>
                <p>Dokumen berikut akan segera berakhir. Mohon segera ditindaklanjuti:</p>
                <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; width: 120px;">Nama Dokumen</td>
                        <td style="padding: 8px 0; font-weight: bold;">' . htmlspecialchars($doc->nama_dokumen) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Klien</td>
                        <td style="padding: 8px 0;">' . htmlspecialchars($doc->nama_klien) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">PIC Klien</td>
                        <td style="padding: 8px 0;">' . htmlspecialchars($doc->nama_pic) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Tanggal Berakhir</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #dc2626;">' . date('d M Y', strtotime($doc->tanggal_berakhir)) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Sisa Waktu</td>
                        <td style="padding: 8px 0; font-weight: bold; color: #dc2626;">' . $sisa_hari . ' hari lagi</td>
                    </tr>
                </table>
                <p>Mohon segera hubungi PIC klien untuk proses perpanjangan dokumen.</p>
                <a href="' . site_url('dokumen/detail/' . $doc->id) . '" style="display: inline-block; background: #9f2b2b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 12px;">Lihat Detail Dokumen</a>
            </div>
            <div style="background: #f4f6f9; padding: 16px; text-align: center; font-size: 12px; color: #6b7280;">
                Email otomatis dari Sistem Tridatu.
            </div>
        </div>
        ';
    }
}