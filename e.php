<?php
$total_run_time = ($_POST['run-time-hour'] * 60) + $_POST['run-time-minute'] + round(($_POST['run-time-second'] / 60),2);

//距離と時間からVDOTの算出
//VDOT = 酸素摂取量(VO2) / %VO2max
$velocity = ($_POST['run-distance'] * 1000) / $total_run_time; // m / min 
$VO2 = -4.6 + 0.182258 * $velocity + 0.000104 * ($velocity*$velocity);
$VO2max = 0.8 + 0.1894393 * pow(2.71828, (-0.012788 * $total_run_time)) + 0.2989558 * pow(2.71828, (-0.1932605 * $total_run_time));
$VDOT = $VO2 / $VO2max;

$VO2 = -4.6 + 0.182258 * 266 + 0.000104 * (266*266);
$VO2max = (0.8 + 0.1894393 * pow(2.71828, (-0.012788 * 30)) + 0.2989558 * pow(2.71828, (-0.1932605 * 30))) * 100;

echo round($VO2 / $VO2max, 2) . "\n";
printf("%.2f\n", 5);
echo 10 * .5;