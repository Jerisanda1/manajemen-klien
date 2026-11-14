<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'form_validation']); 
        $this->load->model('User_model', 'user_model');
        $this->load->model('Pengaturan_model', 'pengaturan_model'); 
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $data['settings'] = $this->pengaturan_model->get_settings();
        $this->load->view('auth/login', $data);
    }

    public function proses_login() {
        // 1. Validasi Input (Server-side)
        $this->form_validation->set_rules('username', 'Username', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', strip_tags(validation_errors()));
            redirect('auth');
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');
        
        // 2. Verifikasi Cloudflare Turnstile
        $turnstile_response = $this->input->post('cf-turnstile-response');

        if (empty($turnstile_response)) {
            $this->session->set_flashdata('error', 'Silakan verifikasi bahwa Anda bukan robot!');
            redirect('auth');
        }

        $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret'   => '0x4AAAAAAF2cRI7qJZNhuNXXUpFigoULq3M',
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
        $result   = @file_get_contents($verify_url, false, $context);
        
        if ($result === FALSE) {
            $this->session->set_flashdata('error', 'Gagal terhubung ke server verifikasi. Coba lagi.');
            redirect('auth');
        }

        $response_keys = json_decode($result, true);

        if (!$response_keys['success']) {
            $this->session->set_flashdata('error', 'Verifikasi Cloudflare gagal, silakan coba lagi.');
            redirect('auth');
        }

        // 3. Cek Database (Gunakan password_verify)
        $user = $this->user_model->cek_login($username);
        
        // PERBAIKAN: Menggunakan password_verify() bukan md5()
        if ($user && password_verify($password, $user->password)) {
            
            // PERBAIKAN: Regenerasi Session ID untuk mencegah Session Fixation Attack
            $this->session->sess_regenerate(TRUE);

            $session_data = [
                'id_user'      => $user->id,
                'username'     => $user->username,
                'nama_lengkap' => $user->nama_lengkap,
                'logged_in'    => TRUE
            ];
            $this->session->set_userdata($session_data);
            
            redirect('dashboard');
        } else {
            // Pesan error disamakan, jangan beri tahu mana yang salah (username/password)
            $this->session->set_flashdata('error', 'Username atau Password salah!');
            redirect('auth');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }
}