<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

$historical = json_decode(file_get_contents(__DIR__ . '/speedtest.json'), true);

while (true) {

    $time = time();
    $y = (int) date('Y', $time);
    $m = (int) date('m', $time);
    $d = (int) date('d', $time);
    $h = (int) date('H', $time);
    $i = (int) date('i', $time);

    if (!isset($last) || date('i', $last) != $i) {

        $last = $time;

        $exec = exec('/usr/local/bin/speedtest --json --secure --server 10390');
        $current = json_decode($exec);
        $historical['ping'][$y][$m][$d][$h][$i] = $ping = (double) $current->ping;
        $historical['download'][$y][$m][$d][$h][$i] = $download = $current->download / 1000000;
        $historical['upload'][$y][$m][$d][$h][$i] = $upload = $current->upload / 1000000;

        echo date('Y-m-d H:i', $time) .  ": {$ping} ms | {$download} Mbps | {$upload} Mbps" . PHP_EOL;

        file_put_contents(__DIR__ . '/speedtest.json', json_encode($historical), LOCK_EX);

    }

    sleep(1);

}

//header('Content-Type: application/json');
//exit($exec);
