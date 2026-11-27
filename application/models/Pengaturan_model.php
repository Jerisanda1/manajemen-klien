
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

    /* =========================================
     * 1. PENGATURAN PERUSAHAAN & ASET
     * ========================================= */

    public function get_settings() {
        return $this->db->get_where('company_settings', ['id' => 1])->row();
    }

    public function update_settings($data) {
        return $this->db->where('id', 1)->update('company_settings', $data);
    }


    /* =========================================
     * 2. MASTER DATA JENIS DOKUMEN
     * ========================================= */

    public function get_all_types() {
        return $this->db->order_by('id', 'ASC')
                        ->get('document_types')
                        ->result();
    }

    public function insert_type($name) {
        return $this->db->insert('document_types', ['name' => $name]);
    }

    public function update_type($id, $name) {
        return $this->db->where('id', $id)
                        ->update('document_types', ['name' => $name]);
    }

    public function delete_type($id) {
        return $this->db->where('id', $id)
                        ->delete('document_types');
    }


    /* =========================================
     * 3. AKUN USER (ADMIN)
     * ========================================= */

    public function get_user($id) {
        return $this->db->get_where('users', ['id' => (int) $id])
                        ->row();
    }

    public function update_user($id, $data) {
        return $this->db->where('id', (int) $id)
                        ->update('users', $data);
    }


    /* =========================================
     * 4. REMINDER INVOICE
     * ========================================= */

    // Mengambil semua jadwal reminder
    public function get_all_reminder_rules() {
        return $this->db
            ->order_by('offset_hari', 'DESC')
            ->get('invoice_reminder_rules')
            ->result();
    }

    // Mengambil satu jadwal berdasarkan ID
    public function get_reminder_rule($id) {
        return $this->db
            ->get_where('invoice_reminder_rules', [
                'id' => (int) $id
            ])
            ->row();
    }

    // Menambahkan jadwal reminder
    public function insert_reminder_rule($data) {
        return $this->db->insert('invoice_reminder_rules', $data);
    }

    // Memperbarui jadwal reminder
    public function update_reminder_rule($id, $data) {
        return $this->db
            ->where('id', (int) $id)
            ->update('invoice_reminder_rules', $data);
    }

    // Menghapus jadwal reminder
    public function delete_reminder_rule($id) {
        return $this->db
            ->where('id', (int) $id)
            ->delete('invoice_reminder_rules');
    }

    // Mengaktifkan / menonaktifkan jadwal reminder
    public function toggle_reminder_rule($id, $status) {
        return $this->db
            ->where('id', (int) $id)
            ->update('invoice_reminder_rules', [
                'aktif' => (int) $status
            ]);
    }

    // Memeriksa apakah offset sudah digunakan
    public function reminder_offset_exists($offset_hari, $exclude_id = NULL) {

        $this->db->where('offset_hari', (int) $offset_hari);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db
            ->count_all_results('invoice_reminder_rules') > 0;
    }

    public function get_clients_for_reminder()
    {
        return $this->db
            ->select('id, nama_klien, nama_pic, reminder_days_before')
            ->order_by('nama_klien', 'ASC')
            ->get('clients')
            ->result();
    }

    public function get_client_for_reminder($id)
    {
        return $this->db
            ->select('id, nama_klien')
            ->get_where('clients', ['id' => (int) $id])
            ->row();
    }

    public function update_client_reminder_days($id, $days)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update('clients', ['reminder_days_before' => (int) $days]);
    }

    private function get_legacy_notification_sender_config()
    {
        $this->config->load('email_reminder', TRUE);
        $this->config->load('notifikasi', TRUE);
        $this->config->load('fonnte', TRUE);

        $reminderConfig = $this->config->item('email_reminder') ?: [];
        $invoiceConfig = $this->config->item('notifikasi') ?: [];
        $fonnteConfig = $this->config->item('fonnte') ?: [];

        $smtpHost = trim((string) ($reminderConfig['smtp_host'] ?? ''));
        if ($smtpHost === '') {
            $smtpHost = (string) ($invoiceConfig['email_smtp_host'] ?? '');
        }

        $smtpCrypto = (string) ($reminderConfig['smtp_crypto'] ?? '');
        if ($smtpCrypto === '') {
            $smtpCrypto = (string) ($invoiceConfig['email_smtp_crypto'] ?? '');
        }

        if (stripos($smtpHost, 'ssl://') === 0) {
            $smtpHost = substr($smtpHost, 6);
            $smtpCrypto = 'ssl';
        } elseif (stripos($smtpHost, 'tls://') === 0) {
            $smtpHost = substr($smtpHost, 6);
            $smtpCrypto = 'tls';
        }

        $smtpUser = (string) ($reminderConfig['smtp_user'] ?? '');
        if ($smtpUser === '') {
            $smtpUser = (string) ($invoiceConfig['email_smtp_user'] ?? '');
        }

        $smtpPassword = (string) ($reminderConfig['smtp_pass'] ?? '');
        if ($smtpPassword === '') {
            $smtpPassword = (string) ($invoiceConfig['email_smtp_pass'] ?? '');
        }

        $smtpPort = (int) ($reminderConfig['smtp_port'] ?? 0);
        if ($smtpPort < 1) {
            $smtpPort = (int) ($invoiceConfig['email_smtp_port'] ?? 0);
        }

        $emailFrom = (string) ($invoiceConfig['email_from'] ?? '');
        if ($emailFrom === '') {
            $emailFrom = $smtpUser;
        }
        $emailFromName = (string) ($invoiceConfig['email_from_name'] ?? '');
        if ($emailFromName === '') {
            $emailFromName = 'Sistem Tridatu';
        }

        return [
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_crypto' => $smtpCrypto,
            'smtp_user' => $smtpUser,
            'smtp_password' => $smtpPassword,
            'email_from' => $emailFrom,
            'email_from_name' => $emailFromName,
            'fonnte_token' => (string) ($fonnteConfig['fonnte_token'] ?? ''),
        ];
    }

    public function get_notification_sender_config()
    {
        $config = $this->get_legacy_notification_sender_config();
        $saved = $this->db->get_where('notification_sender_settings', ['id' => 1])->row();

        if (!$saved) {
            return $config;
        }

        foreach (['smtp_host', 'smtp_user', 'email_from', 'email_from_name'] as $field) {
            if (isset($saved->$field) && trim((string) $saved->$field) !== '') {
                $config[$field] = (string) $saved->$field;
            }
        }

        if (isset($saved->smtp_crypto)) {
            $config['smtp_crypto'] = (string) $saved->smtp_crypto;
        }
        if (!empty($saved->smtp_port)) {
            $config['smtp_port'] = (int) $saved->smtp_port;
        }
        if (!empty($saved->smtp_password_ciphertext)) {
            $config['smtp_password'] = $this->decrypt_notification_secret($saved->smtp_password_ciphertext);
        }
        if (!empty($saved->fonnte_token_ciphertext)) {
            $config['fonnte_token'] = $this->decrypt_notification_secret($saved->fonnte_token_ciphertext);
        }

        return $config;
    }

    public function get_notification_sender_form_data()
    {
        $config = $this->get_notification_sender_config();
        $formData = $config;
        $formData['smtp_password_configured'] = $config['smtp_password'] !== '';
        $formData['fonnte_token_configured'] = $config['fonnte_token'] !== '';
        unset($formData['smtp_password'], $formData['fonnte_token']);

        return $formData;
    }

    public function save_notification_sender_settings($data)
    {
        $saved = $this->db->get_where('notification_sender_settings', ['id' => 1])->row();
        $update = [
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => (int) $data['smtp_port'],
            'smtp_crypto' => $data['smtp_crypto'],
            'smtp_user' => $data['smtp_user'],
            'email_from' => $data['email_from'],
            'email_from_name' => $data['email_from_name'],
        ];

        if ($data['smtp_password'] !== '') {
            $update['smtp_password_ciphertext'] = $this->encrypt_notification_secret($data['smtp_password']);
        }
        if ($data['fonnte_token'] !== '') {
            $update['fonnte_token_ciphertext'] = $this->encrypt_notification_secret($data['fonnte_token']);
        }

        if ($saved) {
            return $this->db->where('id', 1)->update('notification_sender_settings', $update);
        }

        $update['id'] = 1;
        return $this->db->insert('notification_sender_settings', $update);
    }

    private function encrypt_notification_secret($secret)
    {
        $key = (string) getenv('NOTIFICATION_SETTINGS_KEY');
        if ($key === '') {
            throw new RuntimeException('NOTIFICATION_SETTINGS_KEY belum tersedia pada environment server.');
        }
        if (!function_exists('openssl_encrypt') || !function_exists('openssl_random_pseudo_bytes')) {
            throw new RuntimeException('Ekstensi OpenSSL PHP diperlukan untuk menyimpan kredensial pengirim.');
        }

        $strong = FALSE;
        $iv = openssl_random_pseudo_bytes(12, $strong);
        if ($iv === FALSE || !$strong) {
            throw new RuntimeException('Gagal membuat kunci acak untuk mengenkripsi kredensial pengirim.');
        }

        $tag = '';
        $ciphertext = openssl_encrypt(
            $secret,
            'aes-256-gcm',
            hash('sha256', $key, TRUE),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        if ($ciphertext === FALSE || strlen($tag) !== 16) {
            throw new RuntimeException('Gagal mengenkripsi kredensial pengirim.');
        }

        return 'v1:' . base64_encode($iv . $tag . $ciphertext);
    }

    private function decrypt_notification_secret($payload)
    {
        $key = (string) getenv('NOTIFICATION_SETTINGS_KEY');
        if ($key === '') {
            throw new RuntimeException('NOTIFICATION_SETTINGS_KEY tidak tersedia; kredensial pengirim tidak dapat dibuka.');
        }
        if (strpos($payload, 'v1:') !== 0 || !function_exists('openssl_decrypt')) {
            throw new RuntimeException('Format kredensial pengirim tidak didukung atau ekstensi OpenSSL tidak aktif.');
        }

        $data = base64_decode(substr($payload, 3), TRUE);
        if ($data === FALSE || strlen($data) < 28) {
            throw new RuntimeException('Data kredensial pengirim tersimpan tidak valid.');
        }

        $plaintext = openssl_decrypt(
            substr($data, 28),
            'aes-256-gcm',
            hash('sha256', $key, TRUE),
            OPENSSL_RAW_DATA,
            substr($data, 0, 12),
            substr($data, 12, 16)
        );
        if ($plaintext === FALSE) {
            throw new RuntimeException('Kredensial pengirim tidak dapat dibuka; periksa NOTIFICATION_SETTINGS_KEY.');
        }

        return $plaintext;
    }

}
