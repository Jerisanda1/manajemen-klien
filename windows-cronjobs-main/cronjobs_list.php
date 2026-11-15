<?php

require_once __DIR__ . '/sys/functions.php';
$CONFIG = get_user_config();
$php = $CONFIG['php_path'];


// ADD YOUR CRON JOBS HERE!!!
$cronjobs_list = [
    // Menjalankan CodeIgniter Cron Controller setiap 1 menit
    // Format: <jadwal> <path_php> <path_index_ci> <controller> <method>
    // Tanda ../ artinya naik satu folder dari folder windows-cronjobs ke folder utama proyek
    "* * * * * $php ../index.php cron kirim_reminder",
];

// Jangan ubah kode di bawah ini
if (php_sapi_name() == "cli") {
    // Jika dijalankan lewat command line
} else {
    // Jika diakses lewat browser
}