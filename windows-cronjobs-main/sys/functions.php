<?php

if (!defined('CRONJOBS_FUNCTIONS_LOADED')) {
    define('CRONJOBS_FUNCTIONS_LOADED', true);
}

/**
 * Membaca konfigurasi pengguna.
 * File windows-cronjobs.config berada di root windows-cronjobs-main.
 */
function get_user_config()
{
    $configFile = dirname(__DIR__) . '/windows-cronjobs.config';

    if (!file_exists($configFile)) {
        throw new Exception('File windows-cronjobs.config tidak ditemukan: ' . $configFile);
    }

    $configLines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($configLines === false) {
        throw new Exception('Gagal membaca windows-cronjobs.config');
    }

    $CONFIG = [];

    foreach ($configLines as $line) {
        $line = trim($line);

        // Abaikan baris kosong dan komentar
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, ';') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);

        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Hapus tanda kutip jika ada
            $value = trim($value, "\"'");

            $CONFIG[$key] = $value;
        }
    }

    return $CONFIG;
}


/**
 * Membaca konfigurasi sistem.
 */
function get_system_config()
{
    $configFile = __DIR__ . '/system.config';

    if (!file_exists($configFile)) {
        return [];
    }

    $configLines = file(
        $configFile,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    if ($configLines === false) {
        throw new Exception('Gagal membaca system.config');
    }

    $SYSTEM = [];

    foreach ($configLines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0 || strpos($line, ';') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);

        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            $SYSTEM[$key] = $value;
        }
    }

    return $SYSTEM;
}


/**
 * Mengatur timezone dan mengambil waktu.
 */
function get_current_time($timezone = 'WITA', $format = '', $timestamp = null)
{
    if ($timezone === 'WIB') {
        $timezone = 'Asia/Jakarta';
    } elseif ($timezone === 'WITA') {
        $timezone = 'Asia/Makassar';
    } elseif ($timezone === 'WIT') {
        $timezone = 'Asia/Jayapura';
    }

    if ($timestamp === null) {
        $timestamp = time();
    }

    $dt = new DateTime();

    $dt->setTimezone(new DateTimeZone($timezone));
    $dt->setTimestamp($timestamp);

    return $dt->format($format);
}


/**
 * Membuat direktori jika belum tersedia.
 */
function create_logs_directory($path)
{
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new Exception('Gagal membuat direktori log: ' . $path);
        }
    }
}


/**
 * Menulis log.
 */
function log_data($path, $message = '')
{
    $directory = dirname($path);

    if (!is_dir($directory)) {
        create_logs_directory($directory);
    }

    $result = file_put_contents(
        $path,
        $message . PHP_EOL,
        FILE_APPEND
    );

    if ($result === false) {
        throw new Exception('Gagal menulis log: ' . $path);
    }
}


/**
 * Menjalankan perintah cron.
 */
function run_command($command, $index, $logs_path)
{
    $number = $index + 1;
    $output = $logs_path . '/command_' . $number . '.log';

    $descriptorspec = [
        0 => ['pipe', 'r'],
        1 => ['file', $output, 'a'],
        2 => ['file', $output, 'a'],
    ];

    // Folder utama project manajemen_klien
    $project_path = dirname(__DIR__, 2);

    $process = proc_open(
        $command,
        $descriptorspec,
        $pipes,
        $project_path
    );

    if (!is_resource($process)) {
        throw new Exception('Gagal menjalankan perintah cronjobs.');
    }

    // Tutup stdin
    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    // Tunggu proses selesai
    $exit_code = proc_close($process);

    // Simpan hasil eksekusi
    log_data(
        $logs_path . '/windows-cronjobs.log',
        '- run command: ' . $command . ' | exit code: ' . $exit_code
    );

    return $exit_code;
}