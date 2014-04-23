<?php
/*	predfinition stat_time et fin_time pour les plages agt_loc
 *  20 munites par plage est calculées par palge d'horaure
 */
//gest variables
$plage_pos = $_GET['plage_pos'];
$start_date = $_GET['start_date'];

//decoupage
list($dt,$time)=explode(" ",$start_date);
list($j,$m,$y)=explode("/",$dt);
$newtimestamp = strtotime("$y-$m-$j 00:00");

//construction deb et fin  
$duree_plage =   30 * 60 ;  //30 minutes
$duree_deb = $plage_pos * $duree_plage;
$duree_fin = ($plage_pos + 1) * $duree_plage -1;
$duree_deb = $newtimestamp + $duree_deb;
$duree_fin = $newtimestamp + $duree_fin;
 
if (strlen($_GET['start_date']) > 10)
	echo "|".date("d/m/Y H:i",$duree_deb) ."|".date("d/m/Y H:i",$duree_fin)  ;
?>