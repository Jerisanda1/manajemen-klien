<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Pengaturan_model', 'pengaturan_model');
    }

    public function index() {
        // PERBAIKAN: Menggunakan 'id_user' sesuai session dari Auth.php
        $user_id = $this->session->userdata('id_user') ?: 1; 
        $notification_sender_error = '';

        try {
            $notification_sender = $this->pengaturan_model->get_notification_sender_form_data();
        } catch (RuntimeException $e) {
            log_message('error', 'Pengaturan pengirim tidak dapat ditampilkan: ' . $e->getMessage());
            $notification_sender = NULL;
            $notification_sender_error = $e->getMessage();
        }

        $data = [
            'title'    => 'Pengaturan - Tridatu',
            'content'  => 'content/pengaturan/index',
            'settings' => $this->pengaturan_model->get_settings(),
            'types'    => $this->pengaturan_model->get_all_types(),
            'user'     => $this->pengaturan_model->get_user($user_id),
            'reminder_rules'  => $this->pengaturan_model->get_all_reminder_rules(),
            'reminder_clients' => $this->pengaturan_model->get_clients_for_reminder(),
            'notification_sender' => $notification_sender,
            'notification_sender_error' => $notification_sender_error
        ];
        $this->load->view('main', $data);
    }

    public function update_notification_sender()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $smtpHost = trim((string) $this->input->post('smtp_host', TRUE));
        $smtpPort = trim((string) $this->input->post('smtp_port', TRUE));
        $smtpCrypto = trim((string) $this->input->post('smtp_crypto', TRUE));
        $smtpUser = trim((string) $this->input->post('smtp_user', TRUE));
        $emailFrom = trim((string) $this->input->post('email_from', TRUE));
        $emailFromName = trim((string) $this->input->post('email_from_name', TRUE));
        $smtpPassword = (string) $this->input->post('smtp_password', FALSE);
        $fonnteToken = trim((string) $this->input->post('fonnte_token', FALSE));

        $validHost = preg_match('/^[a-zA-Z0-9.-]+$/', $smtpHost) === 1;
        $validPort = ctype_digit($smtpPort) && (int) $smtpPort >= 1 && (int) $smtpPort <= 65535;
        $validCrypto = in_array($smtpCrypto, ['', 'ssl', 'tls'], TRUE);
        $validSmtpUser = filter_var($smtpUser, FILTER_VALIDATE_EMAIL) !== FALSE;
        $validFrom = filter_var($emailFrom, FILTER_VALIDATE_EMAIL) !== FALSE;

        if (
            !$validHost || strlen($smtpHost) > 255 ||
            !$validPort ||
            !$validCrypto ||
            !$validSmtpUser ||
            !$validFrom ||
            $emailFromName === '' || strlen($emailFromName) > 150 ||
            strlen($smtpPassword) > 4096 ||
            strlen($fonnteToken) > 4096
        ) {
            $this->session->set_flashdata(
                'error',
                'Periksa kembali host, port, enkripsi, email, nama pengirim, dan panjang kredensial.'
            );
            redirect('pengaturan#pengirim');
            return;
        }

        try {
            $saved = $this->pengaturan_model->save_notification_sender_settings([
                'smtp_host' => $smtpHost,
                'smtp_port' => (int) $smtpPort,
                'smtp_crypto' => $smtpCrypto,
                'smtp_user' => $smtpUser,
                'email_from' => $emailFrom,
                'email_from_name' => $emailFromName,
                'smtp_password' => $smtpPassword,
                'fonnte_token' => $fonnteToken,
            ]);
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal mengenkripsi pengaturan pengirim: ' . $e->getMessage());
            $this->session->set_flashdata('error', $e->getMessage());
            redirect('pengaturan#pengirim');
            return;
        }

        $this->session->set_flashdata(
            $saved ? 'success' : 'error',
            $saved
                ? 'Pengaturan akun pengirim untuk semua reminder berhasil disimpan.'
                : 'Gagal menyimpan pengaturan akun pengirim.'
        );
        redirect('pengaturan#pengirim');
    }

    public function update_reminder_klien()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $clientId = $this->input->post('client_id');
        $days = $this->input->post('reminder_days_before');

        if ($clientId === NULL || !ctype_digit((string) $clientId) || (int) $clientId < 1) {
            $this->session->set_flashdata('error', 'Klien reminder tidak valid.');
            redirect('pengaturan');
            return;
        }

        if (
            $days === NULL ||
            !ctype_digit((string) $days) ||
            (int) $days < 1 ||
            (int) $days > 365
        ) {
            $this->session->set_flashdata('error', 'Hari reminder harus berupa angka 1 sampai 365.');
            redirect('pengaturan');
            return;
        }

        $client = $this->pengaturan_model->get_client_for_reminder($clientId);
        if (!$client) {
            show_404();
        }

        $saved = $this->pengaturan_model->update_client_reminder_days($clientId, $days);
        $this->session->set_flashdata(
            $saved ? 'success' : 'error',
            $saved ? 'Pengaturan reminder klien berhasil disimpan.' : 'Gagal menyimpan pengaturan reminder klien.'
        );
        redirect('pengaturan');
    }

    // Proses Simpan Informasi Perusahaan
    public function update_info() {
        $update_data = [
            'nama_perusahaan' => $this->input->post('nama_perusahaan', TRUE),
            'alamat'          => $this->input->post('alamat', TRUE),
            'no_telp'         => $this->input->post('no_telp', TRUE),
            'email'           => $this->input->post('email', TRUE),
        ];

        // Handle upload logo di form informasi
        if (!empty($_FILES['logo']['name'])) {
            $config['upload_path']   = FCPATH . 'uploads/settings/logo/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = 2048; // 2MB
            $config['encrypt_name']  = TRUE;
            
            if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, TRUE);
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('logo')) {
                $up = $this->upload->data();
                $update_data['logo'] = 'uploads/settings/logo/' . $up['file_name'];
                
                // Hapus logo lama
                //$old = $this->pengaturan_model->get_settings();
                //if ($old->logo && file_exists(FCPATH . $old->logo)) @unlink(FCPATH . $old->logo);
            } 
            else {
                $this->session->set_flashdata('error', 'Gagal upload logo: ' . strip_tags($this->upload->display_errors()));
                redirect('pengaturan');
            }
        }

        $this->pengaturan_model->update_settings($update_data);
        $this->session->set_flashdata('success', 'Informasi perusahaan berhasil disimpan!');
        redirect('pengaturan');
    }

    // Proses simpan rekening bank dan penanggung jawab
// Proses Simpan Rekening Bank dan Penanggung Jawab
public function update_billing()
{
    $update_data = [
        'nama_bank' => trim($this->input->post('nama_bank', TRUE)),
        'nama_cabang' => trim($this->input->post('nama_cabang', TRUE)),
        'swift_code' => trim($this->input->post('swift_code', TRUE)),
        'nomor_rekening' => trim($this->input->post('nomor_rekening', TRUE)),
        'nama_pemilik_rekening' => trim($this->input->post('nama_pemilik_rekening', TRUE)),
        'nama_penanggung_jawab' => trim($this->input->post('nama_penanggung_jawab', TRUE)),
        'jabatan_penanggung_jawab' => trim($this->input->post('jabatan_penanggung_jawab', TRUE)),
    ];

    $tersimpan = $this->pengaturan_model->update_settings($update_data);

    if ($tersimpan) {
        $this->session->set_flashdata(
            'success',
            'Informasi rekening dan penanggung jawab berhasil diperbarui!'
        );
    } else {
        $this->session->set_flashdata(
            'error',
            'Gagal menyimpan informasi rekening.'
        );
    }

    redirect('pengaturan');
}


    // Proses Ubah Username & Password
    public function update_account() {
        // PERBAIKAN: Menggunakan 'id_user' sesuai session dari Auth.php
        $user_id = $this->session->userdata('id_user') ?: 1; 
        
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');
        $confirm  = $this->input->post('confirm_password');

        if (empty($username)) {
            $this->session->set_flashdata('error', 'Username tidak boleh kosong.');
            redirect('pengaturan');
        }

        $update_data = ['username' => $username];

        if (!empty($password)) {
            if ($password !== $confirm) {
                $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok.');
                redirect('pengaturan');
            }
            if (strlen($password) < 6) {
                $this->session->set_flashdata('error', 'Password minimal 6 karakter.');
                redirect('pengaturan');
            }
            
            // Menggunakan password_hash untuk keamanan modern (Bcrypt)
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->pengaturan_model->update_user($user_id, $update_data);
        
        // Update session username agar langsung berubah di UI tanpa logout
        $this->session->set_userdata('username', $username);

        $this->session->set_flashdata('success', 'Pengaturan akun berhasil diperbarui!');
        redirect('pengaturan');
    }

    // Proses Simpan Kop Surat & Tanda Tangan

public function update_assets()
{
    $update_data = [];
    $files = ['kop_surat', 'ttd'];

    foreach ($files as $file) {
        if (empty($_FILES[$file]['name'])) {
            continue;
        }

        $folder_name = ($file === 'ttd')
            ? 'tanda_tangan'
            : 'kop_surat';

        $upload_path = FCPATH . 'uploads/settings/' . $folder_name . '/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, TRUE);
        }

        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ];

        if (!$this->load->is_loaded('upload')) {
            $this->load->library('upload');
        }

        // Inisialisasi ulang untuk setiap jenis file.
        $this->upload->initialize($config);

        if (!$this->upload->do_upload($file)) {
            $this->session->set_flashdata(
                'error',
                'Gagal upload ' . $file . ': ' .
                strip_tags($this->upload->display_errors())
            );

            redirect('pengaturan');
            return;
        }

        $up = $this->upload->data();

        $update_data[$file] =
            'uploads/settings/' . $folder_name . '/' . $up['file_name'];
    }

    if (!empty($update_data)) {
        $tersimpan = $this->pengaturan_model->update_settings($update_data);

        if ($tersimpan) {
            $this->session->set_flashdata(
                'success',
                'Aset dokumen berhasil diperbarui!'
            );
        } else {
            $this->session->set_flashdata(
                'error',
                'Gagal memperbarui data aset.'
            );
        }
    }

    redirect('pengaturan');
}

    // Hapus Kop Surat / TTD / Logo
   
public function hapus_asset($type)
{
    if (!in_array($type, ['kop_surat', 'ttd', 'logo'], TRUE)) {
        show_404();
    }

    // Hanya hapus referensi dari database.
    // File fisik dipertahankan untuk kebutuhan snapshot invoice lama.
    $tersimpan = $this->pengaturan_model->update_settings([
        $type => NULL
    ]);

    if ($tersimpan) {
        $this->session->set_flashdata(
            'success',
            'Referensi file berhasil dihapus dari pengaturan aktif.'
        );
    } else {
        $this->session->set_flashdata(
            'error',
            'Gagal memperbarui pengaturan aset.'
        );
    }

    redirect('pengaturan');
}

    // === AJAX: Kelola Jenis Dokumen ===
    public function simpan_jenis() {
        $id = $this->input->post('id');
        $name = $this->input->post('name', TRUE);

        if ($id) {
            $this->pengaturan_model->update_type($id, $name);
            $msg = 'Jenis dokumen berhasil diperbarui!';
        } else {
            $this->pengaturan_model->insert_type($name);
            $msg = 'Jenis dokumen berhasil ditambahkan!';
        }
        echo json_encode(['status' => true, 'msg' => $msg]);
    }

    public function hapus_jenis($id) {
        $this->pengaturan_model->delete_type($id);
        $this->session->set_flashdata('success', 'Jenis dokumen berhasil dihapus!');
        redirect('pengaturan');
    }



    /* =========================================
     * 5. KELOLA REMINDER INVOICE
     * ========================================= */

    // Tambah jadwal reminder
    public function simpan_reminder()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $offset = $this->input->post('offset_hari');
        $label  = trim($this->input->post('label', TRUE));

        if ($offset === NULL || $offset === '' || !is_numeric($offset)
            || (string)(int)$offset !== (string)$offset) {
            $this->session->set_flashdata(
                'error',
                'Jumlah hari reminder tidak valid.'
            );
            redirect('pengaturan');
            return;
        }

        $offset = (int) $offset;

        if ($label === '') {
            $this->session->set_flashdata(
                'error',
                'Nama jadwal reminder wajib diisi.'
            );
            redirect('pengaturan');
            return;
        }

        $email = $this->input->post('kirim_email') ? 1 : 0;
        $wa    = $this->input->post('kirim_whatsapp') ? 1 : 0;

        if (!$email && !$wa) {
            $this->session->set_flashdata(
                'error',
                'Pilih minimal satu saluran pengiriman.'
            );
            redirect('pengaturan');
            return;
        }

        if ($this->pengaturan_model->reminder_offset_exists($offset)) {
            $this->session->set_flashdata(
                'error',
                'Jadwal pada waktu tersebut sudah tersedia.'
            );
            redirect('pengaturan');
            return;
        }

        $data = [
            'offset_hari'    => $offset,
            'label'          => $label,
            'aktif'          => 1,
            'kirim_email'    => $email,
            'kirim_whatsapp' => $wa
        ];

        $tersimpan = $this->pengaturan_model
                          ->insert_reminder_rule($data);

        $this->session->set_flashdata(
            $tersimpan ? 'success' : 'error',
            $tersimpan
                ? 'Jadwal reminder berhasil ditambahkan.'
                : 'Gagal menambahkan jadwal reminder.'
        );

        redirect('pengaturan');
    }


    // Edit jadwal reminder
    public function update_reminder($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $rule = $this->pengaturan_model->get_reminder_rule($id);

        if (!$rule) {
            show_404();
        }

        $offset = $this->input->post('offset_hari');
        $label  = trim($this->input->post('label', TRUE));

        if ($offset === NULL || $offset === '' || !is_numeric($offset)
            || (string)(int)$offset !== (string)$offset
            || $label === '') {
            $this->session->set_flashdata(
                'error',
                'Data jadwal reminder tidak valid.'
            );
            redirect('pengaturan');
            return;
        }

        $offset = (int) $offset;

        $email = $this->input->post('kirim_email') ? 1 : 0;
        $wa    = $this->input->post('kirim_whatsapp') ? 1 : 0;

        if (!$email && !$wa) {
            $this->session->set_flashdata(
                'error',
                'Pilih minimal satu saluran pengiriman.'
            );
            redirect('pengaturan');
            return;
        }

        if ($this->pengaturan_model
                 ->reminder_offset_exists($offset, $id)) {
            $this->session->set_flashdata(
                'error',
                'Jadwal pada waktu tersebut sudah digunakan.'
            );
            redirect('pengaturan');
            return;
        }

        $data = [
            'offset_hari'    => $offset,
            'label'          => $label,
            'kirim_email'    => $email,
            'kirim_whatsapp' => $wa
        ];

        $tersimpan = $this->pengaturan_model
                          ->update_reminder_rule($id, $data);

        $this->session->set_flashdata(
            $tersimpan ? 'success' : 'error',
            $tersimpan
                ? 'Jadwal reminder berhasil diperbarui.'
                : 'Gagal memperbarui jadwal reminder.'
        );

        redirect('pengaturan');
    }


    // Aktifkan / nonaktifkan jadwal
    public function toggle_reminder($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $rule = $this->pengaturan_model->get_reminder_rule($id);

        if (!$rule) {
            show_404();
        }

        $status = $rule->aktif ? 0 : 1;

        $tersimpan = $this->pengaturan_model
                          ->toggle_reminder_rule($id, $status);

        $this->session->set_flashdata(
            $tersimpan ? 'success' : 'error',
            $tersimpan
                ? 'Status reminder berhasil diperbarui.'
                : 'Gagal memperbarui status reminder.'
        );

        redirect('pengaturan');
    }


    // Hapus jadwal reminder
    public function hapus_reminder($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_404();
        }

        $rule = $this->pengaturan_model->get_reminder_rule($id);

        if (!$rule) {
            show_404();
        }

        $terhapus = $this->pengaturan_model
                         ->delete_reminder_rule($id);

        $this->session->set_flashdata(
            $terhapus ? 'success' : 'error',
            $terhapus
                ? 'Jadwal reminder berhasil dihapus.'
                : 'Gagal menghapus jadwal reminder.'
        );

        redirect('pengaturan');
    }

}