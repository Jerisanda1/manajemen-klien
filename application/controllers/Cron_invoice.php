
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_invoice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Hanya dapat dijalankan melalui CLI
        if (!is_cli() && php_sapi_name() !== 'cli' && empty($_SERVER['argv'])) {
            show_404();
        }

        $this->load->model('Pembayaran_model');
        $this->load->library('notifikasi');
        $this->load->helper('fonnte');
        $this->config->load('notifikasi', TRUE);
        $this->config->load('fonnte');
    }

    // php index.php cron_invoice pengingat_email
    // php index.php cron_invoice pengingat_email dry
    public function pengingat_email($mode = '')
    {
        date_default_timezone_set('Asia/Makassar');

        $dry = ($mode === 'dry');

        // Reminder pertama: rentang H-30 sampai H-1
        // Reminder kedua: tepat hari H
        $emailKirim = 0;
        $emailGagal = 0;
        $waKirim = 0;
        $waGagal = 0;
        $lewat = 0;

        echo '[' . date('Y-m-d H:i:s') . '] Pengingat invoice'
            . ($dry ? ' (DRY RUN)' : '') . "\n";

        foreach ($this->Pembayaran_model->get_invoice_belum_bayar() as $inv) {

            // Hitung selisih hari dengan tanggal jatuh tempo
            $hariJatuhTempo = strtotime(date('Y-m-d', strtotime($inv->jatuh_tempo)));
            $hariIni = strtotime(date('Y-m-d'));
            $hari = (int) (($hariJatuhTempo - $hariIni) / 86400);

            // Lewati invoice di luar rentang H-30 sampai H-0
            if ($hari > 30 || $hari < 0) {
                continue;
            }

            // Tentukan tahap reminder
            if ($hari === 0) {
                $offsetReminder = 0;
                $labelTahap = 'hari H';
            } else {
                // H-30 sampai H-1 menggunakan satu penanda reminder
                $offsetReminder = 30;
                $labelTahap = 'rentang H-30';
            }

            // Template pesan sesuai sisa hari aktual
            list($subjek, $isi) = $this->notifikasi->pesan_pengingat($inv, $hari);

            // Kontak PIC
            $email = trim((string) $inv->email_pic);
            $hp = preg_replace('/\D/', '', (string) $inv->hp_pic);

            // =========================
            // PENGIRIMAN EMAIL
            // =========================

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

                echo "- {$inv->no_invoice}: email PIC kosong/tidak valid\n";
                $lewat++;

            } elseif (!$this->Pembayaran_model->sudah_dikirim_otomatis(
                $inv->id,
                'email',
                $offsetReminder
            )) {

                if ($dry) {

                    echo "- {$inv->no_invoice}: AKAN dikirim lewat email ke {$email} ({$labelTahap})\n";

                } else {

                    list($ok, $ket) = $this->notifikasi->email(
                        $email,
                        $subjek,
                        $isi
                    );

                    $tersimpan = $this->Pembayaran_model->add_pengiriman(
                        $inv->id,
                        'email',
                        $email,
                        $ok,
                        $ket,
                        1,
                        $offsetReminder
                    );

                    if (!$tersimpan) {
                        log_message(
                            'error',
                            'Gagal menyimpan riwayat email invoice ' . $inv->no_invoice
                        );
                    }

                    if ($ok) {
                        echo "- {$inv->no_invoice}: EMAIL terkirim ke {$email}\n";
                        $emailKirim++;
                    } else {
                        echo "- {$inv->no_invoice}: EMAIL GAGAL ({$ket})\n";
                        $emailGagal++;
                    }
                }
            }

            // =========================
            // PENGIRIMAN WHATSAPP
            // =========================

            if ($hp === '') {

                echo "- {$inv->no_invoice}: nomor WhatsApp PIC kosong\n";
                $lewat++;

            } elseif (!$this->Pembayaran_model->sudah_dikirim_otomatis(
                $inv->id,
                'whatsapp',
                $offsetReminder
            )) {

                if ($dry) {

                    echo "- {$inv->no_invoice}: AKAN dikirim lewat WhatsApp ke {$hp} ({$labelTahap})\n";

                } else {

                    // Ubah isi HTML menjadi teks untuk WhatsApp
                    $pesanWA = $this->_html_to_text($isi);

                    $hasil = kirim_wa_fonnte($hp, $pesanWA);

                    $ok = !empty($hasil['status']);
                    $ket = isset($hasil['reason']) ? $hasil['reason'] : '';

                    $tersimpan = $this->Pembayaran_model->add_pengiriman(
                        $inv->id,
                        'whatsapp',
                        $hp,
                        $ok,
                        $ket,
                        1,
                        $offsetReminder
                    );

                    if (!$tersimpan) {
                        log_message(
                            'error',
                            'Gagal menyimpan riwayat WhatsApp invoice ' . $inv->no_invoice
                        );
                    }

                    if ($ok) {
                        echo "- {$inv->no_invoice}: WHATSAPP terkirim ke {$hp}\n";
                        $waKirim++;
                    } else {
                        echo "- {$inv->no_invoice}: WHATSAPP GAGAL ({$ket})\n";
                        $waGagal++;
                    }
                }
            }
        }

        echo "\n";
        echo "====================================\n";
        echo "EMAIL berhasil : {$emailKirim}\n";
        echo "EMAIL gagal   : {$emailGagal}\n";
        echo "WA berhasil   : {$waKirim}\n";
        echo "WA gagal      : {$waGagal}\n";
        echo "Dilewati      : {$lewat}\n";
        echo "Selesai.\n";
    }

    // Mengubah HTML menjadi teks biasa untuk WhatsApp
    private function _html_to_text($html)
    {
        $html = preg_replace(
            '/<br\s*\/?>|<\/p>|<\/div>|<\/li>|<\/tr>/i',
            "\n",
            $html
        );

        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

        // Rapikan baris kosong berlebihan
        $html = preg_replace("/\n{3,}/", "\n\n", $html);

        return trim($html);
    }
}
