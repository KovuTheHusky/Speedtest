<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
$host = $_SERVER['HTTP_HOST'];

if (isset($_GET['y'])) {
    $time = mktime($_GET['h'], 0, 0, $_GET['m'], $_GET['d'], $_GET['y']);
} else {
    $time = time();
}

$y = isset($_GET['y']) ? (int) $_GET['y'] : (int) date('Y', $time);
$m = isset($_GET['m']) ? (int) $_GET['m'] : (int) date('m', $time);
$d = isset($_GET['d']) ? (int) $_GET['d'] : (int) date('d', $time);
$h = isset($_GET['h']) ? (int) $_GET['h'] : (int) date('H', $time);
$i = (int) date('i', $time);

if (!isset($_GET['y']))
    exit(header("Location: http://{$host}/hourly/{$y}/{$m}/{$d}/{$h}"));

$uplevel = $plus = "http://{$host}/daily/{$y}/{$m}/{$d}";
$plus = $time + 3600;
$yplus = (int) date('Y', $plus);
$mplus = (int) date('m', $plus);
$dplus = (int) date('d', $plus);
$hplus = (int) date('H', $plus);
$plus = "http://{$host}/hourly/{$yplus}/{$mplus}/{$dplus}/{$hplus}";
$minus = $time - 3600;
$yminus = (int) date('Y', $minus);
$mminus = (int) date('m', $minus);
$dminus = (int) date('d', $minus);
$hminus = (int) date('H', $minus);
$minus = "http://{$host}/hourly/{$yminus}/{$mminus}/{$dminus}/{$hminus}";

$st = json_decode(file_get_contents(__DIR__ . '/speedtest.json'), true);

if (!isset($st['ping'][$y][$m][$d][$h]) || count($st['ping'][$y][$m][$d][$h]) < 1) {
    echo "No entries. Going back in 1 second.";
?>

<script>

setTimeout(function() {
    window.history.back();
}, 1000);

</script>

<?php
exit;
}

$ampm = 'am';
$hhh = $h;
if ($hhh >= 12) {
    $hhh -= 12;
    $ampm = 'pm';
}
if ($hhh == 0) {
    $hhh = 12;
}

foreach ($st['ping'][$y][$m][$d][$h] as $iii => $val) {
    if (strlen($iii) == 1)
        $iii = '0' . $iii;
    $label[] = "\"{$hhh}:{$iii}{$ampm}\"";
}
$label = implode(',', $label);

foreach ($st['ping'][$y][$m][$d][$h] as $iii => $val) {
    $ping[] = round($val / 1000, 3);
}
$ping = implode(',', $ping);

foreach ($st['download'][$y][$m][$d][$h] as $iii => $val) {
    $download[] = round($val, 2);
}
$download = implode(',', $download);

foreach ($st['upload'][$y][$m][$d][$h] as $iii => $val) {
    $upload[] = round($val, 2);
}
$upload = implode(',', $upload);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo date('Y-m-d g:ia', $time); ?></title>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js" integrity="sha256-Uv9BNBucvCPipKQ2NS9wYpJmi8DTOEfTA/nH2aoJALw=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@0.5.7/chartjs-plugin-annotation.min.js" integrity="sha256-Olnajf3o9kfkFGloISwP1TslJiWUDd7IYmfC+GdCKd4=" crossorigin="anonymous"></script>
<style>
html, body {
    margin: 0;
    padding: 0;
}
.ui-loader {
    display: none;
}
</style>
</head>
<body>
<canvas id="chart" width="100%" height="100%"></canvas>
<script>
var ctx = document.getElementById("chart");
ctx.width = window.innerWidth;
ctx.height = window.innerHeight;
var chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo $label; ?>],
        datasets: [{
            label: 'Ping',
            data: [<?php echo $ping; ?>],
            borderColor: 'red',
            backgroundColor: 'transparent'
        },
        {
            label: 'Download',
            data: [<?php echo $download; ?>],
            borderColor: 'green',
            backgroundColor: 'transparent'
        },
        {
            label: 'Upload',
            data: [<?php echo $upload; ?>],
            borderColor: 'blue',
            backgroundColor: 'transparent'
        }]
    },
    options: {
        animation: false,
        title: {
            display: true,
            text: '<?php echo date('Y-m-d g:ia', $time); ?>'
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        },
        tooltips: {
            mode: 'index',
            intersect: false,
            position: 'nearest'
        },
        annotation: {
            annotations: [
                {
                    type: 'line',
                    mode: 'horizontal',
                    scaleID: 'y-axis-0',
                    value: 40,
                    borderColor: 'green',
                    borderDash: [10, 10],
                    borderWidth: 1,
                    label: {
                    enabled: false,
                    content: 'Download'
                    }
                },
                {
                    type: 'line',
                    mode: 'horizontal',
                    scaleID: 'y-axis-0',
                    value: 2,
                    borderColor: 'blue',
                    borderDash: [10, 10],
                    borderWidth: 1,
                    label: {
                    enabled: false,
                    content: 'Upload'
                    }
                }
            ]
        }
    }
});

$( window ).resize(function() {
    location.reload();
});

$(window).on('orientationchange', function (e){
    location.reload();
});

$(document).keydown(function (e){ 
    if(e.keyCode == 37) // left arrow
    {
        location = '<?php echo $minus; ?>';
    }
    else if(e.keyCode == 38) // left arrow
    {
        location = '<?php echo $uplevel; ?>';
    }
    else if(e.keyCode == 39)    // right arrow
    { 
        location = '<?php echo $plus; ?>';
    }
});

$(window).on('swipeleft', function (e){
    location = '<?php echo $plus; ?>';
});

$(window).on('swiperight', function (e){
    location = '<?php echo $minus; ?>';
});

$(window).on('swipedown', function (e){
    location = '<?php echo $uplevel; ?>';
});

</script>
</body>
</html>
