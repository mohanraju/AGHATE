<?php
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
$grr_script_name = "day.php";
#Paramètres de connection
require_once("./commun/include/settings.inc.php");
?>
<html>
<head>
<title>test </title>
<script type="text/javascript"><!--
	div = {
		show: function(elem) {
			document.getElementById(elem).style.visibility = 'visible';
		},
		hide: function(elem) {
			document.getElementById(elem).style.visibility = 'hidden';
		}
	}
--></script>
<style type="text/css"><!--
	body {margin:0; padding:0 10px 0 10px; border:0; height:100%; overflow-y:auto; }
	body {font-family: georgia, serif; font-size:10px;}
	#page {margin:110px 0 10px 10px; display:block; width:500px; border:1px solid #000; background:#fff; padding:10px;}
	#cal {display:block; top:10px; left:270px; width:510px; position:fixed; border:1px solid #888; padding:10px; text-align:center; font-weight:bold; color:#FFF; background:#FFF000;}
	* html #cal {position:absolute;}
	#cal a:visited, #cal a {display:block; width:300px; height:20px; margin:0 auto; border-top:1px solid #fff; border-bottom:1px solid #000; text-align:center; text-decoration:none; line-height:20px; color:#FF0000;}
	#cal a:hover {background:#aaa; color:#fff;}


--></style>
</head>

<body onload="div.hide('cal')" >
<a href="#" onMouseOver="div.show('cal')">calander</a>
<br />
this is sample textes here 


<div id="cal" onmouseover="this.style.backgroundColor='#990000';" onmouseout="div.hide('cal');">
	<?php minicals($year, $month, $day, $area, -1, 'day'); ?>
</div>

</body>
</html>
