<?php 
/*
############################################################
Project IntranetMSI
Changement hopital courant 
par un popup  uniquement pour le group ADMIN et MSI

scripts lié :
ajax_changer_hopital_popup.html (div d'affichage des hopitals)
ajax_changer_hopital.js (script d'action jquery pour appeller ce script avec le variables site)

par Mohanraju @sls
############################################################
*/
session_start();
header('Content-Type: text/html; charset=utf-8');

if (strlen($_GET['site']) > 0){ 
	$_SESSION['site']=$_GET['site'];
	echo "OK";
}else{
	echo "KO";
}
?>
