<?php
// test normal

require_once __DIR__ . '/../sys/functions.php';

$current_time = get_current_time('WITA', '[d-m-Y H:i:s T] ');
$file = fopen(__DIR__ . '/../logs/test.log', 'a');
fwrite($file, $current_time . 'test1' . PHP_EOL);
fclose($file);

sleep(30);
