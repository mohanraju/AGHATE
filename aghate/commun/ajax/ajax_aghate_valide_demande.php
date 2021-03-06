<?php
/*
#########################################################################
#                  ajax_edit_entry_handler.php                          #

#                                                                       #
#            Derni�re modification : 20/03/2008                         #
#                                                                       #
#########################################################################
modifie par mohanraju le 13/01/2014
*/

header('Content-type: text/html; charset=utf-8'); 
include "../../config/config.php";
include "../../config/config.inc.php";
include "../../commun/include/functions.inc.php";
include "../../commun/include/$dbsys.inc.php";
include "../../commun/include/mrbs_sql.inc.php";
include "../../commun/include/misc.inc.php";

include "../../config/config.php";
include "../../config/config_".$site.".php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";

include "../../commun/include/ClassGilda.php";
include "../../commun/include/CommonFonctions.php";
$mysql = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);
$Gilda= new Gilda($ConnexionStringGILDA);

if (strlen($table_loc)< 1){
    echo "|ERR|Erreur declaration Table LOC ";
	exit;
}else
	$Aghate->NomTableLoc =$table_loc;

// Settings
date_default_timezone_set('Europe/Paris');
require_once("../include/settings.inc.php");

//-----------------------------------------------------------------------------
//Chargement des valeurs de la table settings
//-----------------------------------------------------------------------------
if (!loadSettings()){
    echo "|ERR|Erreur chargement settings";
	exit;
}
//-----------------------------------------------------------------------------
// Session related functions
//-----------------------------------------------------------------------------
require_once("../include/session.inc.php");
// Resume session
if (!grr_resumeSession()) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};

//----------------------------------------------------------------------------- 
// check patient present dans base local si non insert le
//-----------------------------------------------------------------------------
if(strlen($patient)>0) {
	if (strlen($noip) > 0){
		$PatInfo=$Gilda->GetPatInfoByNip($noip);
		// err nip no patient trouv�
		if(count($PatInfo) < 1)
		{
			echo "|ERR| Invalide NIP =>$noip, Nip non trouv� dans la base Administrative";
			exit;
		}
		$PatInfo[0]['DANAIS']=$CommonFonctions->Normal2Mysql($PatInfo[0]['DTNAIS']); //attn pourrecumarer datenaisse au format dd/mm/YYYY
		$Aghate->InsertPatient ($PatInfo[0]); 
	}
}else{
	echo "|ERR| Patient vide, Patient obligatoire ";
	exit;
}
 
 
//----------------------------------------------------------------------------- 
// check reservation present plus au moins 5 jours dans n'importe quel service
// A FAIRE
//-----------------------------------------------------------------------------
 
 
 
//----------------------------------------------------------------------------- 
// check medecin present dans la table agt_medecin
//-----------------------------------------------------------------------------
 
if (strlen($id_medecin) < 1)
{
	echo "|ERR| Invalide Medecin => $medecin, medecin non trouv� dans la base locale";
		exit;

}
//-----------------------------------------------------------------------------
// check service_id ou room_id
//-----------------------------------------------------------------------------
 
if ((strlen($id_service) < 1) && (strlen($room_id) < 1) && (intval($id_service) < 1 ) )
{
	echo "|ERR|Service ou Room obligaire !";
	exit;
}
$ServiceInfo=$Aghate->GetServiceInfoByServiceId ($id_service);


 
//-----------------------------------------------------------------------------
//si le room non declar� on force Coulour/Panier
if (strlen($room_id) < 1){
	$room_id=$Aghate->GetPanierIdByServiceId($id_service);
}
  
// R�cup�ration des donn�es concernant l'affichage du planning du domaine
//-----------------------------------------------------------------------------
get_planning_area_values($area);

if(authGetUserLevel(getUserName(),-1) < 1)
{
	echo "|ERR|Access Denied !";
  exit();
}
 
//-----------------------------------------------------------------------------
// check dorit de reservation
//-----------------------------------------------------------------------------
if(!verif_qui_peut_reserver_pour($room_id, $_SESSION['login']))
{
	echo "|ERR|Access denied pour cette utilisateur!";
	exit;
} 

//-----------------------------------------------------------------------------
// check dorit de reservation
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------
// v�rify le droit de reservation 
//-----------------------------------------------------------------	

if($Aghate->GetUserLevel($_SESSION['login'],$room_id ) < 3){
	echo "|ERR|Access denied pour cette utilisateur!";
	exit;
}

//-----------------------------------------------------------------------------
// check start time et dur�e
//-----------------------------------------------------------------------------
list($dt,$time)=explode(" ",$start_time);
if((strlen($dt) != 10) || (strlen($time) != 5))
{
	echo "|ERR|Invalide start time($start_time), format JJ/MM/AAAA HH:mm attendu!";	
	exit;
}
list($day,$month,$year)=explode("/",$dt);
list($hour,$minute)=explode(":",$time);

//exit;
 
//-----------------------------------------------------------------------------
// check end time 
//-----------------------------------------------------------------------------
list($df,$timef)=explode(" ",$end_time);
if((strlen($df) != 10) || (strlen($timef) != 5))
{
	echo "|ERR|Invalide end time($end_time), format JJ/MM/AAAA HH:mm attendu!";	
	exit;
}
list($day_f,$month_f,$year_f)=explode("/",$df);
list($hour_f,$minute_f)=explode(":",$timef);

// les controls sur datetime a faire **************************

if (strlen($duree)< 1){
	echo "|ERR|Duree de convocation est inconnu!";
	exit;	
}
 
$starttime = mktime($hour, $minute, 0, $month, $day, $year);
$endtime   = mktime($hour_f, $minute_f, 0, $month_f, $day_f, $year_f);

/*if ($starttime < time()){
	echo "|ERR| Warning : La r�servation est effectu� avant la date courrante";
	exit;
}*/


if ($endtime <= $starttime)
{
	echo "|ERR|probl�me de calcul start_time et end_time endtime <=  starttime ($endtime <=  $starttime)"	;
	exit;
}

//-----------------------------------------------------------------------------
// Check Hors reservation ou Service ferme 
//-----------------------------------------------------------------------------
$day_temp   = date("d",$starttime);
$month_temp = date("m",$starttime);
$year_temp  = date("Y",$starttime);
$starttime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
$day_temp   = date("d",$endtime);
$month_temp = date("m",$endtime);
$year_temp  = date("Y",$endtime);
$endtime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
// On teste
if (resa_est_hors_reservation($starttime_midnight , $endtime_midnight )) {
	echo "|ERR|Erreur dans la date de d�but ou de fin de r�servation  \n Rservation dans Hors p�roide/Cong�e/jour f�ri�e";
	exit;
}
 
//----------------------------------------------------------------------------- 
# Acquire mutex to lock out others trying to book the same slot(s).
//-----------------------------------------------------------------------------
/*
if(!grr_sql_mutex_lock($table_loc))
{
   echo "|ERR|unable to lock table $table_loc!";
   exit;
}*/

//-----------------------------------------------------------------------------
//check droit d'utlisateur sur la room
//-----------------------------------------------------------------------------
$info_room = $Aghate->GetRoomInfoByRoomId($room_id);
$statut_room = $info_room['statut_room'];
// on v�rifie qu\'un utilisateur non autoris� ne tente pas de r�server une ressource non disponible
if (($statut_room == "0") and authGetUserLevel(getUserName(),$room_id) < 3)
{
	echo "|ERR| Salle est indisponible ou l'utilisateur n'a pas le droit de r�server dans cette salle";
	exit;
}

//-----------------------------------------------------------------------------
// check room dispo ou non 
// sauf Panier et reservattion par Plages
//--------------------------------------------------------------------------

if (strlen($room_name)>0 && ($room_name != 'Panier') && ($ServiceInfo[0]['enable_periods']!='y') )  {
	$tmp = $Aghate->CheckRoomDispo($room_id, $starttime, $endtime,"",$id );
	if (strlen($tmp) > 0){
		echo "|ERR|Pas de place disopnible ";
		exit;
	}
}
// force end time pour les plages
if($ServiceInfo[0]['enable_periods']=='y')
{
	$TableauData['plage_pos'] = $plage_pos;
	$endtime = $starttime + 30;
}

$id_service = $Aghate->GetServiceIdByRoomId($room_id);

// tableau RESERVATION info
$TableauData = array();
$TableauData['start_time'] 		= $starttime;
$TableauData['end_time'] 		= $endtime;
$TableauData['room_id'] 		= $room_id;
$TableauData['create_by'] 		= $_SESSION['login'];
$TableauData['type'] 			= $type;
$TableauData['patient']			= $patient;
$TableauData['noip'] 			= $noip;
$TableauData['nda'] 			= $nda;
$TableauData['medecin'] 		= $id_medecin;
$TableauData['uh'] 				= $uh;
$TableauData['de_source'] 		= "Programme";	
$TableauData['ds_source']		= "Programme";
$TableauData['protocole']		= $protocole;
$TableauData['id_prog'] 		= $id_prog;
$TableauData['statut_entry'] 	= "Programme";

// tableau PROGRAMATION info
$TableauProgCd['id'] 			= $id_prog;
$TableauProg['noip'] 			= $noip;
$TableauProg['start_time']		= $starttime;
$TableauProg['end_time'] 		= $endtime; 
$TableauProg['patient']			= $patient;
$TableauProg['service_id'] 		= $id_service;	
$TableauProg['room_id'] 		= $room_id;
$TableauProg['medecin'] 		= $id_medecin;
$TableauProg['protocole'] 		= $protocole;
$TableauProg['statut_entry'] 	= "Valide";
$TableauProg['type'] 			= $type;

$Aghate->NomTableLoc ='agt_prog';

//PROGRAMATION 
if (strlen($id_prog) > 0 ){
	$id_prog = $Aghate->UpdateEntry('agt_prog',$TableauProg,$TableauProgCd);
	if (strlen($_description) > 1)
		$Aghate->UpdateDescriptionFromId ($id,"DESC___COMPL",$_description,"DESC___COMPL");
}


$Aghate->NomTableLoc ='agt_loc';
// RESERVATION (LOC) Cree nouvelle ligne
$id = $Aghate->CreateSingleEntry('agt_loc',$TableauData);
if (strlen($_description) > 1)
	$Aghate->UpdateDescriptionFromId ($id,"DESC___COMPL",$_description,"DESC___COMPL");

//==========================================
//update dans la table agt_user_consult
//==========================================
$TableauConsCd['id'] 	= $id_prog;
$TableauCons['statut_demande'] 	= "N"; //O oui ou N non 

$mysql->update_('agt_prog',$TableauCons,$TableauConsCd);  

//grr_sql_mutex_unlock($table_loc);

echo "|OK|$id|"; 
?>
