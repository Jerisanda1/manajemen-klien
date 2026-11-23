<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('notifikasi', TRUE);
    }

    private function c($k)
    {
        return $this->CI->config->item($k, 'notifikasi');
    }

    // Mengembalikan [berhasil(bool), keterangan(string)]
   public function email($ke, $subjek, $isi)
{
    return $this->email_smtp($ke, $subjek, $isi);
}

    /* ---------- SMTP (Utama saat ini) ---------- */
    private function email_smtp($ke, $subjek, $isi)
    {
        if (!$this->c('email_smtp_user') || !$this->c('email_smtp_pass')) {
            return [FALSE, 'SMTP belum diisi di application/config/notifikasi.php.'];
        }

        $this->CI->load->library('email');
        $this->CI->email->clear();
        $this->CI->email->initialize([
            'protocol'     => 'smtp',
            'smtp_host'    => $this->c('email_smtp_host'),
            'smtp_port'    => (int) $this->c('email_smtp_port'),
            'smtp_user'    => $this->c('email_smtp_user'),
            'smtp_pass'    => $this->c('email_smtp_pass'),
            'smtp_crypto'  => $this->c('email_smtp_crypto'),
            'smtp_timeout' => 15,
            'mailtype'     => 'text',
            'charset'      => 'utf-8',
            'newline'      => "\r\n",
            'crlf'         => "\r\n",
            'validate' => TRUE,
        ]);
        $this->CI->email->from($this->c('email_from') ?: $this->c('email_smtp_user'), $this->c('email_from_name'));
        $this->CI->email->to($ke);
        $this->CI->email->subject($subjek);
        $this->CI->email->message($isi);

       if ($this->CI->email->send(FALSE)) {
    return [TRUE, ''];
}

$debug = $this->CI->email->print_debugger();

$err = trim(
    preg_replace('/\s+/', ' ', strip_tags($debug))
);

return [
    FALSE,
    $err !== '' ? $err : 'Server email menolak pengiriman.'
];}

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