<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('kirim_wa_fonnte')) {
    function kirim_wa_fonnte($target, $message)
    {
        $CI =& get_instance();
        $CI->load->model('Pengaturan_model', 'notification_sender_model');
        $target = trim((string) $target);

        try {
            $sender = $CI->notification_sender_model->get_notification_sender_config();
        } catch (RuntimeException $e) {
            log_message('error', 'Pengaturan pengirim WhatsApp tidak dapat dibaca: ' . $e->getMessage());
            return ['status' => false, 'reason' => 'Pengaturan pengirim WhatsApp tidak dapat dibaca. Periksa kunci enkripsi server.'];
        }

        $token = trim((string) $sender['fonnte_token']);
        if ($token === '') {
            return ['status' => false, 'reason' => 'Akun WhatsApp pengirim belum dikonfigurasi di Pengaturan.'];
        }
        if ($target === '') {
            return ['status' => false, 'reason' => 'Nomor WhatsApp admin kosong di company_settings.no_telp.'];
        }
        if (!function_exists('curl_init')) {
            return ['status' => false, 'reason' => 'Ekstensi cURL PHP belum aktif.'];
        }

        $caInfo = trim((string) ini_get('curl.cainfo'));
        if ($caInfo === '' || !is_readable($caInfo)) {
            $caInfo = trim((string) ini_get('openssl.cafile'));
        }
        if ($caInfo === '' || !is_readable($caInfo)) {
            $laragonCaInfo = dirname(PHP_BINARY, 4)
                . DIRECTORY_SEPARATOR . 'etc'
                . DIRECTORY_SEPARATOR . 'ssl'
                . DIRECTORY_SEPARATOR . 'cacert.pem';
            $caInfo = is_readable($laragonCaInfo) ? $laragonCaInfo : '';
        }
        if ($caInfo === '' || !is_readable($caInfo)) {
            return ['status' => false, 'reason' => 'CA bundle TLS tidak ditemukan atau tidak bisa dibaca.'];
        }

        $curl = curl_init('https://api.fonnte.com/send');
        if ($curl === false) {
            return ['status' => false, 'reason' => 'Gagal memulai koneksi cURL ke Fonnte.'];
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CAINFO => $caInfo,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ],
            CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return ['status' => false, 'reason' => 'Koneksi ke Fonnte gagal: ' . $curlError];
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {
            return ['status' => false, 'reason' => 'Respons Fonnte bukan JSON valid (HTTP ' . $httpCode . ').'];
        }

        $apiStatus = isset($result['status']) ? $result['status'] : false;
        $sent = $apiStatus === true
            || $apiStatus === 1
            || (is_string($apiStatus) && in_array(strtolower(trim($apiStatus)), ['true', '1'], true));
        if ($httpCode >= 200 && $httpCode < 300 && $sent) {
            return ['status' => true, 'reason' => ''];
        }
        

        $reason = isset($result['reason']) && is_scalar($result['reason'])
            ? (string) $result['reason']
            : (isset($result['detail']) && is_scalar($result['detail'])
                ? (string) $result['detail']
                : 'Fonnte menolak pengiriman.');

                
        return ['status' => false, 'reason' => 'HTTP ' . $httpCode . ': ' . $reason];
    }
}