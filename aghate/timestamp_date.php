<?php
if(strlen($_GET['date']) < 1)
	$date= date("d/m/Y h:i:s");
else
	$date= $_GET['date'];
	
if(strlen($_GET['unixtime']) < 1)
	$unixtime= mktime();
else
	$unixtime= $_GET['unixtime'];
 
 
 
if ($_GET['convert']=="convert")
{
	list($_dt,$_hr)=explode(' ',$date);
	
	list($d,$m,$y)=explode('/',$_dt);
	list($hr,$mn,$sc)=explode(':',$_hr);
	echo "<br>DATE => Unixtime : ".$date ." => ".mktime($hr,$mn,$sc,$m,$d,$y);
	
	echo "<br>UnixTime => Date : ".$unixtime." => ".date("d/m/Y H:i:s",$unixtime);	
	
}
?>
<br>	
<form method='GET'>
	DATE :<input type="text" name="date" value="<?php print $date?>" />
	Unixtime :<input type="text" name="unixtime" value="<?php print $unixtime?>" />	
	<input type =submit value="convert"  name="convert"> 
</form>