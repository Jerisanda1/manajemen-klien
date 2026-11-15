<?php
// test output

require_once __DIR__ . '/../sys/functions.php';

$current_time = get_current_time('WITA', '[d-m-Y H:i:s T] ');
$file = fopen(__DIR__ . '/../logs/test.log', 'a');
fwrite($file, $current_time . 'test2' . PHP_EOL);
fclose($file);

echo 'output test2' . PHP_EOL;
