<?php
include "../../config/config.php";
include "../../config/config.inc.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";
include "../include/CommonFonctions.php";


$mysql = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);

$id_protocole = $_GET['id_protocole'];
$date_deb = $_GET['date_deb'];

$res = $Aghate->GetProtocoleDureeById($id_protocole);
$duree_protocole = $res[0][0];

$res_deb 		= explode(" ",$date_deb);
$date_deb_res 	= explode("/",$res_deb[0]);
$day_deb 		= $date_deb_res[0];                
$month_deb 		= $date_deb_res[1];            
$year_deb 		= $date_deb_res[2];
$heure_deb_val = explode(":",$res_deb[1]);
$heure_deb = 	$heure_deb_val[0];
$min_deb = 		$heure_deb_val[1];

$date1 = mktime($heure_deb,$min_deb,00,$month_deb,$day_deb,$year_deb);
$date2 = $date1 + $duree_protocole*60;                   
$tmp = $date2 - $date1;
           
$diff['sec'] = $tmp % 60;                   
 
$tmp = floor(($tmp - $diff['sec']) / 60);    
$diff['min'] = $tmp % 60;                    
 
$tmp = floor(($tmp - $diff['min']) / 60);
$diff['hour'] = $tmp % 24;
     
$tmp = floor(($tmp - $diff['hour']) / 24);
$diff['day'] = $tmp;
$date_sortie = date("d/m/Y H:i", $date2); 
$duree = "";

if ($diff['day'] > 0){
	if($diff['day']==1)
		$duree .= $diff['day']." jour";
	else
		$duree .= $diff['day']." jours";
}

if ($diff['hour'] > 0){
	if ($diff['day'] > 0){
		$duree .= " et ";
	}
	if($diff['hour']==1)
		$duree .= $diff['hour']." heure" ;
	else
		$duree .= $diff['hour']." heures" ;
}

if ($diff['min'] > 0){
	if ($diff['hour'] > 0 || $diff['day'] > 0 ){
		$duree .= " et ";
	}
	$duree .= $diff['min']." minutes" ;
}


print_r($duree."|".$date_sortie."|".$date2."|".$date1);
//print_r($diff['day']." jours et ".$diff['hour']." heures");

?>
