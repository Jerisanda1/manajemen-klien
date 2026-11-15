<?php
// test error

require_once __DIR__ . '/../sys/functions.php';

throw new Exception('throw error test3');

$current_time = get_current_time('WITA', '[d-m-Y H:i:s T] ');
$file = fopen(__DIR__ . '/../logs/test.log', 'a');
fwrite($file, $current_time . 'test3' . PHP_EOL);
fclose($file);
