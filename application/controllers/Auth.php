<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->library('session'); 
        $this->load->model('User_model', 'user_model'); // Memuat Model User
    }

    public function index() {
        // ==========================================
        // TAMBAHAN: JIKA SUDAH LOGIN, JANGAN TAMPILKAN HALAMAN LOGIN
        // ==========================================
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        // Jika belum login, tampilkan halaman login
        $this->load->view('auth/login');
    }

    public function proses_login() {
        $username = $this->input->post('username', true);
        $password = $this->input->post('password', true);
        
        // 1. Ambil token dari turnstile
        $turnstile_response = $this->input->post('cf-turnstile-response');

        // Validasi apakah user sudah mencentang / melewati turnstile
        if (empty($turnstile_response)) {
            $this->session->set_flashdata('error', 'Silakan verifikasi bahwa Anda bukan robot!');
            redirect('auth');
        }

        // 2. Kirim permintaan verifikasi ke API Cloudflare
        $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => '0x4AAAAAAF2cRI7qJZNhuNXXUpFigoULq3M', // Secret key dari rekan Anda
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

        // 3. Cek hasil verifikasi Cloudflare
        if ($response_keys["success"]) {
            
            // 4. CEK DATABASE (Bukan hardcode lagi)
            $user = $this->user_model->cek_login($username);
            
            // Pengecekan password menggunakan MD5 (karena di database kita simpan md5('12345'))
            if ($user && md5($password) === $user->password) {
                
                // 5. SIMPAN SESSION (Penting agar sistem tahu siapa yang login)
                $session_data = [
                    'id_user'      => $user->id,
                    'username'     => $user->username,
                    'nama_lengkap' => $user->nama_lengkap,
                    'logged_in'    => TRUE // Penanda sudah login
                ];
                $this->session->set_userdata($session_data);
                
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Username atau Password salah!');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('error', 'Verifikasi Cloudflare gagal, silakan coba lagi.');
            redirect('auth');
        }
    }

    // Fungsi Logout untuk menghapus session
    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }
}   