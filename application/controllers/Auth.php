<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->library('session'); // <-- Pastikan session dimuat di sini
    }

    public function index() {
        $this->load->view('auth/login');
    }

    public function proses_login() {
    $username = $this->input->post('username', true);
    $password = $this->input->post('password', true);
    
    // Ambil token dari turnstile
    $turnstile_response = $this->input->post('cf-turnstile-response');
    $secret_key = 'MASUKKAN_SECRET_KEY_ANDA_DI_SINI';

    // Validasi apakah user sudah mencentang / melewati turnstile
    if (empty($turnstile_response)) {
        $this->session->set_flashdata('error', 'Silakan verifikasi bahwa Anda bukan robot!');
        redirect('auth');
    }

    // Kirim permintaan verifikasi ke API Cloudflare
    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => '0x4AAAAAAF2cRI7qJZNhuNXXUpFigoULq3M',
        'response' => $turnstile_response,
        'remoteip' => $this->input->ip_address()
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($verify_url, false, $context);
    $response_keys = json_decode($result, true);

    // Cek hasil verifikasi dari Cloudflare
    if ($response_keys["success"]) {
        // Jika verifikasi berhasil, cek username & password
        if($username == 'admin' && $password == '12345') {
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Username atau Password salah!');
            redirect('auth');
        }
    } else {
        // Jika verifikasi gagal
        $this->session->set_flashdata('error', 'Verifikasi Cloudflare gagal, silakan coba lagi.');
        redirect('auth');
    }
}
  
}