<?php
/*
Syncronisation GILDA
Recupère les patients from gilda et mettre a jour

DebTime= debut de péroide si vide date d'aujourdhi est prise 
FinTime= Fin de péroide si vide date d'aujourdhi est prise 

*/

set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);

include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/ClassGildaToAghate.php";
include "config/config_076.php";

$debut = time();

$Aghate= new GildaToAghate();
$Gilda= new Gilda($ConnexionStringGILDA);

//check dates
if(strlen($_GET['DebTime']) != 10)
	$DebTime=date("d/m/Y");
else
	$DebTime=$_GET['DebTime'];	

if(strlen($_GET['FinTime']) != 10)
	$FinTime=date("d/m/Y");
else
	$FinTime=$_GET['FinTime'];	
	
 echo "<hr> Syncronisation gilda pour le periode du ".$DebTime." au ". $FinTime. "<hr>";
 
$DebTime = str_replace('/', '-', $DebTime); 
$DebTime = date("Y-m-d", strtotime($DebTime));
$FinTime = str_replace('/', '-', $FinTime);
$FinTime = date("Y-m-d", strtotime($FinTime));
 
// recup les patients

$Patients=$Gilda->GetPatientsGilda($DebTime,$FinTime);
//insert les patients

$Aghate -> PutAllPatient($Patients);


$fin = time();

$result = $fin - $debut;
echo "<br />Temps du traitement : ";
echo gmdate("H:i:s", $result); // convertit $result en heure, min et sec
?>
