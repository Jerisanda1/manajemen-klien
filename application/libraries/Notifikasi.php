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
        // Default menggunakan SMTP, kecuali driver secara eksplisit diset ke 'brevo'
        return ($this->c('email_driver') === 'brevo')
            ? $this->email_brevo($ke, $subjek, $isi)
            : $this->email_smtp($ke, $subjek, $isi);
    }

    /* ---------- Brevo (API) ---------- */
    private function email_brevo($ke, $subjek, $isi)
    {
        $key  = $this->c('brevo_api_key');
        $dari = $this->c('email_from');
        if (!$key)  return [FALSE, 'API key Brevo belum diisi di application/config/notifikasi.php.'];
        if (!$dari) return [FALSE, 'email_from belum diisi (harus sender yang sudah diverifikasi di Brevo).'];
        if (!function_exists('curl_init')) return [FALSE, 'Ekstensi cURL PHP belum aktif.'];

        $payload = [
            'sender'      => ['name' => $this->c('email_from_name') ?: 'Tridatu Solution', 'email' => $dari],
            'to'          => [['email' => $ke]],
            'subject'     => $subjek,
            'textContent' => $isi,
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $key,
            ],
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $res  = curl_exec($ch);
        $err  = curl_error($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res === FALSE) return [FALSE, 'Tidak bisa terhubung ke Brevo: ' . $err];
        if ($http >= 200 && $http < 300) return [TRUE, ''];

        $j   = json_decode($res, TRUE);
        $msg = isset($j['message']) ? $j['message'] : 'respons tidak dikenal';
        if ($http === 401) $msg = 'API key Brevo tidak valid. ' . $msg;
        return [FALSE, 'Brevo (' . $http . '): ' . $msg];
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
        ]);
        $this->CI->email->from($this->c('email_from') ?: $this->c('email_smtp_user'), $this->c('email_from_name'));
        $this->CI->email->to($ke);
        $this->CI->email->subject($subjek);
        $this->CI->email->message($isi);

        if ($this->CI->email->send(FALSE)) return [TRUE, ''];

        $err = trim(preg_replace('/\s+/', ' ', strip_tags($this->CI->email->print_debugger([]))));
        return [FALSE, $err !== '' ? $err : 'Server email menolak pengiriman.'];
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