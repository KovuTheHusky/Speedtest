<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
$host = $_SERVER['HTTP_HOST'];

if (isset($_GET['y'])) {
    $time = mktime(0, 0, 0, $_GET['m'], $_GET['d'], $_GET['y']);
} else {
    $time = time();
}

$y = isset($_GET['y']) ? (int) $_GET['y'] : (int) date('Y', $time);
$m = isset($_GET['m']) ? (int) $_GET['m'] : (int) date('m', $time);
$d = isset($_GET['d']) ? (int) $_GET['d'] : (int) date('d', $time);
$h = (int) date('H', $time);
$i = (int) date('i', $time);

if (!isset($_GET['y']))
    exit(header("Location: http://{$host}/daily/{$y}/{$m}/{$d}"));

$plus = $time + 86400;
$yplus = (int) date('Y', $plus);
$mplus = (int) date('m', $plus);
$dplus = (int) date('d', $plus);
$plus = "http://{$host}/daily/{$yplus}/{$mplus}/{$dplus}";
$minus = $time - 86400;
$yminus = (int) date('Y', $minus);
$mminus = (int) date('m', $minus);
$dminus = (int) date('d', $minus);
$minus = "http://{$host}/daily/{$yminus}/{$mminus}/{$dminus}";

$st = json_decode(file_get_contents(__DIR__ . '/speedtest.json'), true);

if (!isset($st['ping'][$y][$m][$d]) || count($st['ping'][$y][$m][$d]) < 1) {
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

foreach ($st['ping'][$y][$m][$d] as $key => $val) {
    $ampm = 'am';
    if ($key >= 12) {
        $key -= 12;
        $ampm = 'pm';
    }
    if ($key == 0) {
        $key = 12;
    }
    $label[] = "\"{$key}{$ampm}\"";
}
$label = implode(',', $label);

foreach ($st['ping'][$y][$m][$d] as $hour) {
    $ping[] = round(array_sum($hour) / count($hour) / 1000, 3);
}
$ping = implode(',', $ping);

foreach ($st['download'][$y][$m][$d] as $hour) {
    $download[] = round(array_sum($hour) / count($hour), 2);
}
$download = implode(',', $download);

foreach ($st['upload'][$y][$m][$d] as $hour) {
    $upload[] = round(array_sum($hour) / count($hour), 2);
}
$upload = implode(',', $upload);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo date('Y-m-d', $time); ?></title>
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
            text: '<?php echo date('Y-m-d', $time); ?>'
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        },
        elements: {
            point: {
                hitRadius: 20
            }
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

ctx.onclick = function(evt){
    var activePoints = chart.getElementsAtEvent(evt);
    location = '<?php echo "http://{$host}/hourly/{$y}/{$m}/{$d}/"; ?>' + activePoints[0]._index;
};

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

</script>
</body>
</html>
