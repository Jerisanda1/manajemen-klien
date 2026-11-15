<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../cronjobs_list.php';

$SYSTEM = get_system_config();

$timezone = $CONFIG['timezone'] ?: 'WITA';
$logs_path = __DIR__ . '/../logs/' . get_current_time($timezone, 'Y-m-d_His');

if (empty($SYSTEM['logs_path'])) {
    $systemConfig = fopen(__DIR__ . '/system.config', 'w');
    if ($systemConfig) {
        fwrite($systemConfig, "logs_path=$logs_path");
        fclose($systemConfig);
        create_logs_directory($logs_path);
    } else {
        throw new Exception('Unable to open system.config file.');
    }
} else {
    if (!is_dir($SYSTEM['logs_path'])) {
        create_logs_directory($SYSTEM['logs_path']);
    }

    $logs_path = $SYSTEM['logs_path'];
}

$current_time = get_current_time($timezone, '[d-m-Y H:i:s T] ');
$log_message = $current_time . 'windows-cronjobs:';
log_data($logs_path . '/windows-cronjobs.log', $log_message);

foreach ($cronjobs_list as $index => $cronjobs_string) {
    $cronjobs = preg_split('/\s+/', $cronjobs_string);

    if (count($cronjobs) < 6) {
        throw new Exception("Invalid cronjobs length: \"$cronjobs_list[0]\"");
    }

    $cronjobs_data = [
        'time'    => implode(' ', array_slice($cronjobs, 0, 5)),
        'command' => implode(' ', array_slice($cronjobs, 5)),
    ];

    // Once Per Minute
    if ($cronjobs_data['time'] === '* * * * *') {
        run_command($cronjobs_data['command'], $index, $logs_path);
    }

    // Once Per Five Minutes
    if ($cronjobs_data['time'] === '*/5 * * * *') {
        $current_minute = get_current_time($timezone, 'i');

        if ($current_minute % 5 === 0) {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Twice Per Hour
    if ($cronjobs_data['time'] === '0,30 * * * *') {
        $current_minute = get_current_time($timezone, 'i');

        if ($current_minute === '00' || $current_minute === '30') {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Once Per Hour
    if ($cronjobs_data['time'] === '0 * * * *') {
        $current_minute = get_current_time($timezone, 'i');

        if ($current_minute === '00') {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Twice Per Day
    if ($cronjobs_data['time'] === '0 0,12 * * *') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');

        if (
            ($current_minute === '00' && $current_hour === '00') ||
            ($current_minute === '00' && $current_hour === '12')
        ) {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Once Per Day
    if ($cronjobs_data['time'] === '0 0 * * *') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');

        if ($current_minute === '00' && $current_hour === '00') {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Once Per Week
    if ($cronjobs_data['time'] === '0 0 * * 0') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');
        $current_week   = get_current_time($timezone, 'w');

        if ($current_minute === '00' && $current_hour === '00' && $current_week === '00') {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // On the 1st and 15th of the Month
    if ($cronjobs_data['time'] === '0 0 1,15 * *') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');
        $current_day    = get_current_time($timezone, 'd');

        if (
            ($current_minute === '00' && $current_hour === '00' && $current_day === '01') ||
            ($current_minute === '00' && $current_hour === '00' && $current_day === '15')
        ) {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Once Per Month
    if ($cronjobs_data['time'] === '0 0 1 * *') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');
        $current_day    = get_current_time($timezone, 'd');

        if ($current_minute === '00' && $current_hour === '00' && $current_day === '01') {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }

    // Once Per Year
    if ($cronjobs_data['time'] === '0 0 1 1 *') {
        $current_minute = get_current_time($timezone, 'i');
        $current_hour   = get_current_time($timezone, 'H');
        $current_day    = get_current_time($timezone, 'd');
        $current_month  = get_current_time($timezone, 'm');

        if (
            $current_minute === '00' && $current_hour  === '00' &&
            $current_day    === '01' && $current_month === '01'
        ) {
            run_command($cronjobs_data['command'], $index, $logs_path);
        }
    }
}
