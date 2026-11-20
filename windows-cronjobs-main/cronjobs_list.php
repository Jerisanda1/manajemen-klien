<?php

require_once __DIR__ . '/sys/functions.php';

$CONFIG = get_user_config();

$php = $CONFIG['php_path'] ?? '';

if (empty($php) || !is_file($php)) {
    throw new Exception(
        'PHP executable tidak ditemukan. Periksa php_path di windows-cronjobs.config'
    );
}

// ADD YOUR CRON JOBS HERE!!!
$cronjobs_list = [
    "* * * * * \"$php\" \"D:/laragon/www/manajemen_klien/index.php\" cron kirim_reminder",
    "* * * * * \"$php\" \"D:/laragon/www/manajemen_klien/index.php\" cron_invoice pengingat_email",
];

// Jangan ubah kode di bawah ini
if (php_sapi_name() == "cli") {
    // Jika dijalankan lewat command line
} else {
    // Jika diakses lewat browser
}