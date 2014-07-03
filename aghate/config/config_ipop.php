<?php
/*
########################################################################
* 	Projet Aghate
* 	Module Ipop->Agahte
* 	Auteur Divan Kandiah
* 	date creation 02/07/2014
########################################################################
*
* Fichier config IPOP
* 
* 
*/
 
$Jour	= 60*60*24; //1 jour
$Heure	= 60*60;	//1 heure

//IPOP sejour duree
$IPOP_duree_sejour_total 	= 5 * $Jour; //=> 5 jours
$IPOP_duree_avant			= 1 * $Jour; //entre la veille un jour avant
$IPOP_duree_apres			= ($IPOP_duree_sejour_total - $IPOP_duree_avant); // calcule pour la duree total

//IPOP bloc duree
$IPOP_duree_av_bloc			= 1 * $Heure;  // 1 heure
$IPOP_duree_apr_bloc		= 4 * $Heure; //4 heures

$IPOP_chemin_input	="./ipop/"; //local

$IPOP_chemin_bkup	=$IPOP_chemin_input."bkup/";
$IPOP_chemin_trace	=$IPOP_chemin_input."trace/";

$IPOP_fic_trace		= "trace_".date("Y_m_d.H_i_s").".txt";

//charger fichier mapping pour inserer dans le service
$IPOP_map_file		="./config/ipop_mapping.csv"; 



?>
