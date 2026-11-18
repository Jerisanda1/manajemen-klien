<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_invoice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Perbaikan agar lolos deteksi CLI di Windows Task Scheduler
        if (!is_cli() && php_sapi_name() !== 'cli' && empty($_SERVER['argv'])) {
            show_404();
        }
        
        $this->load->model('Pembayaran_model');
        $this->load->library('notifikasi');
        $this->config->load('notifikasi', TRUE);
    }

    // php index.php cron_invoice pengingat_email        -> kirim
    // php index.php cron_invoice pengingat_email dry    -> hanya tampilkan, tanpa kirim
    public function pengingat_email($mode = '')
    {
        $dry     = ($mode === 'dry');
        $offsets = $this->config->item('pengingat_offset', 'notifikasi');
        if (!is_array($offsets) || !$offsets) $offsets = [3, 0, -3];

        // Gunakan timezone WITA
date_default_timezone_set('Asia/Makassar');

$hariIni = strtotime(date('Y-m-d'));
$jamSekarang = date('H:i');

$kirim = 0;
$gagal = 0;
$lewat = 0;

// Pengiriman hanya dimulai pukul 08.00 WITA
if ($jamSekarang < '08:00') {
    echo "Belum waktunya mengirim pengingat. Jadwal: 08:00 WITA.\n";
    return;
}
        echo '[' . date('Y-m-d H:i:s') . "] Pengingat invoice" . ($dry ? ' (DRY RUN)' : '') . "\n";

        foreach ($this->Pembayaran_model->get_invoice_belum_bayar() as $inv) {
            if (!filter_var($inv->email_pic, FILTER_VALIDATE_EMAIL)) {
                echo "- {$inv->no_invoice}: dilewati, email PIC kosong/tidak valid\n";
                $lewat++;
                continue;
            }

            $hariJatuhTempo = strtotime(date('Y-m-d', strtotime($inv->jatuh_tempo)));

$hari = (int) (($hariJatuhTempo - $hariIni) / 86400);

// Hanya kirim pada tahap yang sesuai dengan tanggal hari ini
if (!in_array($hari, $offsets, true)) {
    continue;
}

$tahap = $hari;

            if ($this->Pembayaran_model->sudah_dikirim_otomatis($inv->id, 'email', $tahap)) continue;

            list($subjek, $isi) = $this->notifikasi->pesan_pengingat($inv, $hari);

            if ($dry) {
                echo "- {$inv->no_invoice}: AKAN dikirim ke {$inv->email_pic} (tahap {$tahap}, selisih {$hari} hari)\n";
                continue;
            }

            list($ok, $ket) = $this->notifikasi->email($inv->email_pic, $subjek, $isi);
            $this->Pembayaran_model->add_pengiriman($inv->id, 'email', $inv->email_pic, $ok, $ket, 1, $tahap);

            if ($ok) { echo "- {$inv->no_invoice}: terkirim ke {$inv->email_pic}\n"; $kirim++; }
            else     { echo "- {$inv->no_invoice}: GAGAL ({$ket})\n"; $gagal++; }
        }

        echo "Selesai. Terkirim: {$kirim}, gagal: {$gagal}, dilewati: {$lewat}\n";
    }
}