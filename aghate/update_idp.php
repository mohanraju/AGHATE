<?php
/*
 * Update_idp.php
 * Fichier qui apelle les fonctions de la classe GildaToAghate
 * Insère,met a jour, ferme les séjours
 * Ecrit dans un fichier trace
*/

header('Content-Type: text/html; charset=utf-8');
set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);

// Include
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda1.php";
include "./commun/include/ClassGildaToAghate.php";
include "config/config_".$site.".php";

$debut = time();

// Initialisation
$Mysql = new MySQL();
$Aghate= new GildaToAghate();
$Gilda= new Gilda($ConnexionStringGILDA);
echo "<hr> Syncronisation gilda <hr>";

$idp_tab = $Gilda->GetIdpTab ();
$Aghate->InsertIdp($idp_tab);

$fin = time();

$result = $fin - $debut;
echo "<br />Temps de traitement : ";
echo gmdate("H:i:s", $result); // convertit $result en heure, min et sec
?>
