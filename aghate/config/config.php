<?php
#########################################################################
#                         Config.php                                	  #
#       Configuration base Mysql local et serveur                       #
#                  																					 	          #
#                 Dernière modification : 10/07/2006                    #
#									MOHANRAJU Sp APHP/SaintLouis                          #
#########################################################################
/*
 * Copyright 2011 MOHANRAJU Sp APHP/SaintLouis
 *
*/

//===============================================
//MYSQL Database settings
//===============================================
date_default_timezone_set("Europe/Paris");
$serveur=true; // facilite la basculemnt de script vers server/local
error_reporting(E_ALL & ~E_NOTICE);
$site='001';

if ($serveur){
	//server settings 
	$DBName = "regulit";
	$DBUser = "regulit";
	$DBPassword = "regulit";
	$DBHost = "nck-ulyssetest";
}else{
	//local settings
	$DBName = "aghate_test";
	$DBUser = "root";
	$DBPassword = "";
	$DBHost = "localhost";
}

//variables commun
$ModuleReservationEdit="../forms/reservation_aghate.php";
$ModuleReservationView="../forms/reservation_aghate.php";

//$ModuleReservationEdit="../edit_entry.php";
//$ModuleReservationView="../forms/view_entry.php";




?>
