<?php

function get_user_config()
{
    $configFile = 'C:\\laragon\\www\\manajemen_klien\\windows-cronjobs-main\\windows-cronjobs.config';
    $configLines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $CONFIG = [];
    foreach ($configLines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, ';') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Bersihkan tanda kutip jika tak sengaja ikut tersimpan
            $value = trim($value, "\"'");
            $CONFIG[$key] = $value;
        }
    }

    return $CONFIG;
}


function get_system_config()
{
    $configFile = __DIR__ . '/system.config';
    $configLines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $SYSTEM = [];
    foreach ($configLines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos(trim($line), ';') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $SYSTEM[trim($key)] = trim($value);
    }

    return $SYSTEM;
}


function get_current_time($timezone = 'WITA', $format = '', $timestamp = null)
{
    if ($timezone === 'WIB') {
        $timezone = 'Asia/Jakarta';
    } else if ($timezone === 'WITA') {
        $timezone = 'Asia/Makassar';
    } else if ($timezone === 'WIT') {
        $timezone = 'Asia/Jayapura';
    }

    if ($timestamp === null) $timestamp = time();

    $dt = new DateTime();
    $dt->setTimezone(new DateTimeZone($timezone));
    $dt->setTimestamp($timestamp);
    return $dt->format($format);
}


function create_logs_directory($path)
{
    while (true) {
        if (!is_dir($path)) {
            mkdir($path);
            break;
        }
    }
}


function log_data($path, $message = '')
{
    $file = fopen($path, 'a');
    fwrite($file, $message . PHP_EOL);
    fclose($file);
}



function run_command($command, $index, $logs_path)
{
    $number = $index + 1;
    $output = $logs_path . "/command_$number.log";

    $descriptorspec = [
        0 => ['pipe', 'r'],
        1 => ['file', $output, 'a'],
        2 => ['file', $output, 'a'],
    ];

    // Jalankan dari folder utama project
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

    // Hanya pipes[0] yang merupakan resource pipe
    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    $exit_code = proc_close($process);

    log_data(
        $logs_path . '/windows-cronjobs.log',
        "- run command: $command | exit code: $exit_code"
    );

    return $exit_code;
}

