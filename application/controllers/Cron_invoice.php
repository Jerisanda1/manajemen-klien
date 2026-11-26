
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
        $this->load->model('Pengaturan_model');
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

        $emailKirim = 0;
        $emailGagal = 0;
        $waKirim = 0;
        $waGagal = 0;
        $lewat = 0;

        echo '[' . date('Y-m-d H:i:s') . '] Pengingat invoice'
            . ($dry ? ' (DRY RUN)' : '') . "\n";

        // Ambil jadwal reminder dari Pengaturan
        $rules = $this->Pengaturan_model->get_all_reminder_rules();

        // Ambil hanya jadwal yang aktif
        $rulesAktif = [];

        foreach ($rules as $rule) {
            if ((int) $rule->aktif === 1) {
                $rulesAktif[] = $rule;
            }
        }

        if (empty($rulesAktif)) {
            echo "Tidak ada jadwal reminder yang aktif.\n";
            return;
        }

        // Ambil seluruh invoice yang belum dibayar
        $invoices = $this->Pembayaran_model->get_invoice_belum_bayar();

        // Ambil seluruh invoice yang belum dibayar
$invoices = $this->Pembayaran_model->get_invoice_belum_bayar();

// DEBUG: Periksa jumlah invoice dan jadwal aktif
echo "Total invoice belum dibayar: " . count($invoices) . "\n";
echo "Total jadwal aktif: " . count($rulesAktif) . "\n";

// Mulai proses invoice

        foreach ($invoices as $inv) {

            // Hitung selisih hari dengan tanggal jatuh tempo
            $tanggalJatuhTempo = strtotime(
                date('Y-m-d', strtotime($inv->jatuh_tempo))
            );

            $tanggalHariIni = strtotime(date('Y-m-d'));

            $hari = (int) round(
                ($tanggalJatuhTempo - $tanggalHariIni) / 86400
            );

            // Cari jadwal yang cocok dengan selisih hari invoice
            foreach ($rulesAktif as $rule) {

                $offsetReminder = (int) $rule->offset_hari;

                // Hanya proses jika jadwal cocok
                if ($hari !== $offsetReminder) {
                    continue;
                }

                $labelTahap = $rule->label;

                // Buat pesan berdasarkan sisa hari aktual
                list($subjek, $isi) =
                    $this->notifikasi->pesan_pengingat($inv, $hari);

                // Kontak PIC
                $email = trim((string) $inv->email_pic);
                $hp = preg_replace('/\D/', '', (string) $inv->hp_pic);

                echo "\nInvoice: {$inv->no_invoice}"
                    . " | Jadwal: {$labelTahap}"
                    . " | Selisih hari: {$hari}\n";

                // =========================
                // PENGIRIMAN EMAIL
                // =========================

                if ((int) $rule->kirim_email === 1) {

                    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

                        echo "- Email PIC kosong/tidak valid\n";
                        $lewat++;

                    } elseif (!$this->Pembayaran_model->sudah_dikirim_otomatis(
                        $inv->id,
                        'email',
                        $offsetReminder
                    )) {

                        if ($dry) {

                            echo "- AKAN dikirim melalui EMAIL ke {$email}\n";

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
                                    'Gagal menyimpan riwayat email invoice '
                                    . $inv->no_invoice
                                );
                            }

                            if ($ok) {
                                echo "- EMAIL berhasil dikirim\n";
                                $emailKirim++;
                            } else {
                                echo "- EMAIL gagal: {$ket}\n";
                                $emailGagal++;
                            }
                        }
                    } else {
                        echo "- EMAIL sudah pernah dikirim untuk jadwal ini\n";
                    }
                }

                // =========================
                // PENGIRIMAN WHATSAPP
                // =========================

                if ((int) $rule->kirim_whatsapp === 1) {

                    if ($hp === '') {

                        echo "- Nomor WhatsApp PIC kosong\n";
                        $lewat++;

                    } elseif (!$this->Pembayaran_model->sudah_dikirim_otomatis(
                        $inv->id,
                        'whatsapp',
                        $offsetReminder
                    )) {

                        if ($dry) {

                            echo "- AKAN dikirim melalui WHATSAPP ke {$hp}\n";

                        } else {

                            $pesanWA = $this->_html_to_text($isi);

                            $hasil = kirim_wa_fonnte($hp, $pesanWA);

                            $ok = !empty($hasil['status']);
                            $ket = isset($hasil['reason'])
                                ? $hasil['reason']
                                : '';

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
                                    'Gagal menyimpan riwayat WhatsApp invoice '
                                    . $inv->no_invoice
                                );
                            }

                            if ($ok) {
                                echo "- WHATSAPP berhasil dikirim\n";
                                $waKirim++;
                            } else {
                                echo "- WHATSAPP gagal: {$ket}\n";
                                $waGagal++;
                            }
                        }
                    } else {
                        echo "- WHATSAPP sudah pernah dikirim untuk jadwal ini\n";
                    }
                }
            }
        }

        echo "\n";
        echo "====================================\n";
        echo "EMAIL berhasil : {$emailKirim}\n";
        echo "EMAIL gagal    : {$emailGagal}\n";
        echo "WA berhasil    : {$waKirim}\n";
        echo "WA gagal       : {$waGagal}\n";
        echo "Dilewati       : {$lewat}\n";
        echo "Selesai.\n";
    }

    // Mengubah HTML menjadi teks biasa untuk WhatsApp
    private function _html_to_text($html)
    {
        // Ubah elemen blok menjadi baris baru
        $html = preg_replace(
            '/<\s*br\s*\/?>|<\/\s*(p|div|li|tr|h[1-6])\s*>/i',
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
