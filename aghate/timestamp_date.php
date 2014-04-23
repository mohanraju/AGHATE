<?php
//echo mktime(11,0,0,10,10,2013);
$time = $_GET['time'];
$date_now = $_GET['date'];
if ($time)
	echo date('d/m/Y H:i:s',$time);
if ($date_now)
{
	list($d,$m,$y)=explode('/',$date_now);
	echo mktime(0,0,0,$m,$d,$y);
}
//1381363200
//1381402800
?>
