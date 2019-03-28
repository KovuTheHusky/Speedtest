<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

$time = time();
$host = $_SERVER['HTTP_HOST'];

$y = (int) date('Y', $time);
$m = (int) date('m', $time);
$d = (int) date('d', $time);

exit(header("Location: http://{$host}/daily/{$y}/{$m}/{$d}"));
