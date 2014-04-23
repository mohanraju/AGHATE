<?php
#########################################################################
#                         Config_NCK.php                                #
#                                                                       #
#                 configuration spéfique du chaque site     	          #
#                 Dernière modification : 10/07/2006                    #
#									MOHANRAJU Sp APHP/SaintLouis                          #
#########################################################################
/*
 * Copyright 20011 MOHANRAJU Sp APHP/SaintLouis
 *

#############################################################################
					Common CONFIGURATIONS
#############################################################################
*/

// connextion string Simpa (il faut bien respecter les espace / retour chariot ..)


//====================SAG					  	

$ConnexionStringSAG="CCP1NCK.WORLD";   //****************> Attn cette config besion d'installtion client oracle sur le serveur et le TNSNAMES
    		

//====================SIMPA	 

$ConnexionStringSIMPA="SIP1NCK.WORLD"; //**************> Attn cette config besion d'installtion client orcale  sur le serveur et le TNSNAMES
	

  
/*
###########################################################################################################################
					NESTOR CONFIGURATIONS
###########################################################################################################################
*/
// mysql table nestor permet de gérer diffrent sites dans la même base
$TableNestor="erreur";
$FicherNestor="./files/sls_nestor.fic"; // fichier nestor sortie par l'outil NESTOR , renomme le pour gérer plusiers sites
$TableNestor ="erreur"; 				// Table Nestor dans Mysql 
$FichierCodeErrorsNestor="./files/NestorCodeErreurs.fic"; 	// Tous les code d'erreurs possible dans nestor
$FichierSQLControl=("./sql_qualite_inc.php"); 				// fichier optionnel si on traites les erreurs via SQL;



/*
###########################################################################################################################
					IPOP CONFIGURATIONS
########################################################################################################################
*/
$TableIpop="ipop_nck";
$TableIpop_bloc_structure="ipop_struc_nck";

//==============>serveur
$chemin					="/data/www/intra_PMSI/ipop_necker/"; //serveur
$chemin_input		="/data/www/intra_PMSI/depot01/IPOP-SAG-NCK/"; //serveur

//=============>Local
//$chemin						="D:/www/Projet_INTRANET/ipop_nck/ipop/"; //local
//$chemin_input			="D:/www/Projet_INTRANET/ipop_nck/ipop/input_nck/"; //local
$chemin_bkup			=$chemin."bkup/";
$chemin_err				=$chemin."erreur/";
$fic_trace				="trace.txt";		
$ListeUhExecutant	="253,253,981,013,586,731,121,781,781,782,123,739,738,666,869,009,429,125,736,294,322,784,783,428,427,132,131,675,194,542,938,543,939,936,581,937,979,980,021,426,787,786,151,878,995";
	
?>
