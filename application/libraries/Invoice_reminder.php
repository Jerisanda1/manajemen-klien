<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_reminder
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Pembayaran_model');
        $this->CI->load->model('Pengaturan_model');
        $this->CI->load->library('notifikasi');
        $this->CI->load->helper('fonnte');
    }

    public function run($dry = FALSE)
    {
        date_default_timezone_set('Asia/Makassar');

        $channels = $this->get_channels();

        if (!$channels['email'] && !$channels['whatsapp']) {
            echo "Tidak ada saluran reminder invoice yang aktif.\n";
            return;
        }

        $invoices = $this->CI->Pembayaran_model->get_invoice_belum_bayar();
        $emailSent = 0;
        $emailFailed = 0;
        $waSent = 0;
        $waFailed = 0;
        $skipped = 0;

        echo '[' . date('Y-m-d H:i:s') . '] Pengingat invoice'
            . ($dry ? ' (DRY RUN)' : '') . "\n";
        echo "Jadwal aktif: satu pengingat per klien, dikirim saat sisa hari masuk rentang 0 s/d H-N.\n";

        foreach ($invoices as $invoice) {
            $dueDate = strtotime(date('Y-m-d', strtotime($invoice->jatuh_tempo)));
            $today = strtotime(date('Y-m-d'));
            $daysLeft = (int) round(($dueDate - $today) / 86400);
            $offset = (int) $invoice->reminder_days_before;

            // Kirim jika sisa hari berada di rentang 0..offset (bukan harus pas H-offset).
            // Pengiriman hanya sekali per invoice karena dicek lewat sudah_dikirim_otomatis().
            if ($offset < 1 || $offset > 365 || $daysLeft < 0 || $daysLeft > $offset) {
                if ($dry) {
                    echo "Lewati {$invoice->no_invoice}: sisa hari={$daysLeft}, rentang=0-{$offset}\n";
                }
                continue;
            }

            list($subject, $message) =
                $this->CI->notifikasi->pesan_pengingat($invoice, $daysLeft);

            echo "\nInvoice: {$invoice->no_invoice} | Sisa {$daysLeft} hari (rentang H-{$offset})\n";

            if ($channels['email']) {
                $email = trim((string) $invoice->email_pic);

                if ($this->CI->Pembayaran_model->sudah_dikirim_otomatis(
                    $invoice->id,
                    'email',
                    $offset
                )) {
                    echo "- EMAIL sudah dikirim\n";
                } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo "- Email PIC kosong/tidak valid\n";
                    $skipped++;
                } elseif ($dry) {
                    echo "- AKAN dikirim melalui EMAIL ke {$email}\n";
                } else {
                    list($ok, $detail) = $this->CI->notifikasi->email(
                        $email,
                        $subject,
                        $message
                    );
                    $saved = $this->CI->Pembayaran_model->add_pengiriman(
                        $invoice->id,
                        'email',
                        $email,
                        $ok,
                        $detail,
                        1,
                        $offset
                    );

                    if (!$saved) {
                        log_message('error', 'Gagal menyimpan riwayat email invoice ' . $invoice->no_invoice);
                    }

                    if ($ok) {
                        echo "- EMAIL berhasil dikirim\n";
                        $emailSent++;
                    } else {
                        echo "- EMAIL gagal: {$detail}\n";
                        $emailFailed++;
                    }
                }
            }

            if ($channels['whatsapp']) {
                $phone = preg_replace('/\D/', '', (string) $invoice->hp_pic);

                if ($this->CI->Pembayaran_model->sudah_dikirim_otomatis(
                    $invoice->id,
                    'whatsapp',
                    $offset
                )) {
                    echo "- WHATSAPP sudah dikirim\n";
                } elseif ($phone === '') {
                    echo "- Nomor WhatsApp PIC kosong\n";
                    $skipped++;
                } elseif ($dry) {
                    echo "- AKAN dikirim melalui WHATSAPP ke {$phone}\n";
                } else {
                    $plainMessage = preg_replace(
                        "/\n{3,}/",
                        "\n\n",
                        trim(html_entity_decode(strip_tags($message), ENT_QUOTES, 'UTF-8'))
                    );
                    $result = kirim_wa_fonnte($phone, $plainMessage);
                    $ok = !empty($result['status']);
                    $detail = isset($result['reason']) ? $result['reason'] : '';
                    $saved = $this->CI->Pembayaran_model->add_pengiriman(
                        $invoice->id,
                        'whatsapp',
                        $phone,
                        $ok,
                        $detail,
                        1,
                        $offset
                    );

                    if (!$saved) {
                        log_message('error', 'Gagal menyimpan riwayat WhatsApp invoice ' . $invoice->no_invoice);
                    }

                    if ($ok) {
                        echo "- WHATSAPP berhasil dikirim\n";
                        $waSent++;
                    } else {
                        echo "- WHATSAPP gagal: {$detail}\n";
                        $waFailed++;
                    }
                }
            }
        }

        echo "\nEMAIL berhasil: {$emailSent}\n";
        echo "EMAIL gagal: {$emailFailed}\n";
        echo "WA berhasil: {$waSent}\n";
        echo "WA gagal: {$waFailed}\n";
        echo "Dilewati: {$skipped}\n";
        echo "Selesai.\n";
    }

    public function get_channels()
    {
        $channels = ['email' => FALSE, 'whatsapp' => FALSE];
        $activeRules = [];

        foreach ($this->CI->Pengaturan_model->get_all_reminder_rules() as $rule) {
            if ((int) $rule->aktif !== 1) {
                continue;
            }

            $activeRules[] = $rule;
            $channels['email'] = $channels['email'] || (int) $rule->kirim_email === 1;
            $channels['whatsapp'] = $channels['whatsapp'] || (int) $rule->kirim_whatsapp === 1;
        }

        if (empty($activeRules)) {
            return ['email' => TRUE, 'whatsapp' => TRUE];
        }

        return $channels;
    }
}