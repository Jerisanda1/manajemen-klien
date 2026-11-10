<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('kirim_wa_fonnte')) {
    function kirim_wa_fonnte($target, $message)
    {
        // Ambil instance CodeIgniter
        $CI =& get_instance();
        
        // Load config fonnte untuk mengambil token
        $CI->config->load('fonnte');
        $token = $CI->config->item('fonnte_token');

        // Proses cURL ke API Fonnte
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Otomatis ubah 08xx menjadi 628xx
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token
            ),
        ));

        $response = curl_exec($curl);
        
        // Cek jika ada error pada cURL
        if (curl_errno($curl)) {
            log_message('error', 'Fonnte cURL Error: ' . curl_error($curl));
            curl_close($curl);
            return false;
        }
        
        curl_close($curl);
        return json_decode($response, true);
    }
}