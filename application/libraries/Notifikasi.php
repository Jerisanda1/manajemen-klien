<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Pengaturan_model', 'notification_sender_model');
    }

    // Mengembalikan [berhasil(bool), keterangan(string)]
   public function email($ke, $subjek, $isi)
{
    return $this->email_smtp($ke, $subjek, $isi);
}

    /* ---------- SMTP (Utama saat ini) ---------- */
    private function email_smtp($ke, $subjek, $isi)
    {
        try {
            $sender = $this->CI->notification_sender_model->get_notification_sender_config();
        } catch (RuntimeException $e) {
            log_message('error', 'Pengaturan pengirim email tidak dapat dibaca: ' . $e->getMessage());
            return [FALSE, 'Pengaturan pengirim email tidak dapat dibaca. Periksa kunci enkripsi server.'];
        }

        if ($sender['smtp_user'] === '' || $sender['smtp_password'] === '') {
            return [FALSE, 'Akun SMTP pengirim belum dikonfigurasi di Pengaturan.'];
        }

        $this->CI->load->library('email');
        $this->CI->email->clear();
        $this->CI->email->initialize([
            'protocol'       => 'smtp',
            'smtp_host'      => $sender['smtp_host'],
            'smtp_port'      => (int) $sender['smtp_port'],
            'smtp_user'      => $sender['smtp_user'],
            'smtp_pass'      => $sender['smtp_password'],
            'smtp_crypto'    => $sender['smtp_crypto'],
            'smtp_timeout'   => 15,
            'smtp_keepalive' => FALSE,
            'mailtype'       => 'html',
            'charset'        => 'utf-8',
            'newline'        => "\r\n",
            'crlf'           => "\r\n",
            'validate'       => TRUE,
        ]);
        $this->CI->email->from(
            $sender['email_from'] ?: $sender['smtp_user'],
            $sender['email_from_name']
        );
        $this->CI->email->to($ke);
        $this->CI->email->subject($subjek);
        $this->CI->email->message($isi);
        if ($this->CI->email->send(FALSE)) {
            return [TRUE, ''];
        }


        $debug = $this->CI->email->print_debugger();

        // Deteksi batas pengiriman Gmail
        if (
            stripos($debug, 'Daily user sending limit exceeded') !== FALSE ||
            stripos($debug, '550-5.4.5') !== FALSE
        ) {
            return [
                FALSE,
                'Email belum terkirim karena akun Gmail mencapai batas pengiriman. Silakan coba kembali dalam 1-24 jam.'
            ];
        }

        // Untuk error lainnya, tetap simpan pesan diagnostik
        $err = trim(
            preg_replace('/\s+/', ' ', strip_tags($debug))
        );

        return [
            FALSE,
            $err !== '' ? $err : 'Pengiriman email gagal. Silakan coba kembali.'
        ];
    }

    // $hari: selisih hari ke jatuh tempo (positif = belum, 0 = hari ini, negatif = terlambat)
    public function pesan_pengingat($inv, $hari)
    {
        $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = strtotime($inv->jatuh_tempo);
        $tempo = $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';

        if ($hari > 0) {
            $subjek = 'Pengingat: invoice ' . $inv->no_invoice . ' jatuh tempo ' . $hari . ' hari lagi';
            $kalimat = "akan jatuh tempo pada {$tempo} ({$hari} hari lagi)";
        } elseif ($hari === 0) {
            $subjek = 'Pengingat: invoice ' . $inv->no_invoice . ' jatuh tempo hari ini';
            $kalimat = "jatuh tempo hari ini ({$tempo})";
        } else {
            $subjek = 'Pengingat: invoice ' . $inv->no_invoice . ' telah melewati jatuh tempo';
            $kalimat = "telah melewati jatuh tempo pada {$tempo} (terlambat " . abs($hari) . " hari)";
        }

        $isi = "Yth. " . ($inv->nama_pic ?: 'Bapak/Ibu') . ",\n\n"
             . "Mengingatkan bahwa invoice {$inv->no_invoice} ({$inv->perihal}) {$kalimat}. "
             . "Mohon segera melakukan pembayaran. Abaikan pesan ini jika pembayaran sudah dilakukan."
             . "\n\nTerima kasih.\nTridatu Solution";

        return [$subjek, $isi];
    }
}