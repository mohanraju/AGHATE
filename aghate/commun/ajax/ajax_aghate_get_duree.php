<?Php  
/*
* PROJET AGHATE
* Ajax get patients pour les recherche par nom/noip
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
* 
*/
// script s d'inclusion
include "../../resume_session.php";
include "../../config/config.php";
include "../../config/config.inc.php";
include "../include/CommonFonctions.php";

$Common = new CommonFunctions();

//echo $date_deb;
//echo $date_fin;
$res_deb 		= explode(" ",$date_deb);
$date_deb_res 	= explode("/",$res_deb[0]);
$day_deb 		= $date_deb_res[0];                
$month_deb 		= $date_deb_res[1];            
$year_deb 		= $date_deb_res[2];

$heure_deb_val = explode(":",$res_deb[1]);
$heure_deb = 	$heure_deb_val[0];
$min_deb = 		$heure_deb_val[1];


$res_fin 		= explode(" ",$date_fin);
$date_fin_res 	= explode("/",$res_fin[0]);
$day_fin 		= $date_fin_res[0];                
$month_fin 		= $date_fin_res[1];            
$year_fin 		= $date_fin_res[2];

$heure_fin_val = explode(":",$res_fin[1]);
$heure_fin = 	$heure_fin_val[0];
$min_fin = 		$heure_fin_val[1];



$start_time = mktime($heure_deb,$min_deb,00,$month_deb,$day_deb,$year_deb);
$end_time 	= mktime($heure_fin,$min_fin,00,$month_fin,$day_fin,$year_fin);

$tmp = $end_time - $start_time;
           
$diff['sec'] = $tmp % 60;                   
 
$tmp = floor(($tmp - $diff['sec']) / 60);    
$diff['min'] = $tmp % 60;                    
 
$tmp = floor(($tmp - $diff['min']) / 60);
$diff['hour'] = $tmp % 24;
     
$tmp = floor(($tmp - $diff['hour']) / 24);
$diff['day'] = $tmp;

$duree = "";

if ($diff['day'] > 0){
	if($diff['day']==1)
		$duree .= $diff['day']." Jour ";
	else
		$duree .= $diff['day']." Jours ";
}

if ($diff['hour'] > 0){
	if ($diff['day'] > 0){
		$duree .= " et ";
	}
	if($diff['hour']==1)
		$duree .= $diff['hour']."h" ;
	else
		$duree .= $diff['hour']."h" ;
}

if ($diff['min'] > 0){
	if ($diff['hour'] > 0 || $diff['day'] > 0 ){
		$duree .= "";
	}
	$duree .= $diff['min']."" ;
}

echo($duree);
//print_r($diff['day']." jours et ".$diff['hour']." heures");

?>
