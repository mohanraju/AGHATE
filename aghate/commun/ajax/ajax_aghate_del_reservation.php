<?php
/*
#########################################################################
#                  ajax_edit_entry_handler.php                         #

#                                                                       #
#            Dernière modification : 20/03/2008                         #
#                                                                       #
#########################################################################
modifie par mohanraju le 13/01/2014

 
 */
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

if (strlen($table_loc)< 1){
    echo "|ERR|Erreur declaration Table LOC ";
	exit;
}else
	$Aghate->NomTableLoc =$table_loc;



$Gilda= new Gilda($ConnexionStringGILDA);

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

$info_entry = $Aghate->GetInfoReservation($id);

$user_level = authGetUserLevel(getUserName(),-1);


//-----------------------------------------------------------------------------
// Récupération des données concernant l'affichage du planning du domaine
//-----------------------------------------------------------------------------
get_planning_area_values($area);

if($user_level < 1)
{
	echo "|ERR|Access Denied !";
	exit();
}

//----------------------------------------------------------------------------- 
# Acquire mutex to lock out others trying to book the same slot(s).
//-----------------------------------------------------------------------------

if(!grr_sql_mutex_lock('agt_loc'))
{
   echo "|ERR|unable to lock table agt_loc!";
   exit;
}

//----------------------------------------------------------------------------- 
// Check si la réservation existe dans la base
//-----------------------------------------------------------------------------
if (!(isset($info_entry))){
	echo "|ERR|Cette réservation n'existe plus!";
	exit;
}

//----------------------------------------------------------------------------- 
// Check si de_source est Gilda + vérif droit
// decommentez la ligne pour laisser l'admin supprimer la résa
//-----------------------------------------------------------------------------
if (strcasecmp($info_entry['de_source'],'Gilda')==0 ){
	//if($user_level < 5){
		echo "|ERR|Vous ne pouvez pas supprimer l'hospitalisation provenant de Gilda!";
		exit;
	//}
}

//----------------------------------------------------------------------------- 
// Check si ds_source est Gilda + vérif droit
// decommentez la ligne pour laisser l'admin supprimer la résa
//----------------------------------------------------------------------------- 
if (strcasecmp($info_entry['de_source'],'Gilda')==0 ){ 
	//if($user_level < 5){
		echo "|ERR|Vous ne pouvez pas supprimer une réservation sortie par Gilda!";
		exit;
	//}
}
//----------------------------------------------------------------------------- 
// Prepare tableau pour l'update
//-----------------------------------------------------------------------------
$TableauData = array();
$TableauData['create_by'] = $_SESSION['login'];
$TableauData['statut_entry'] = 'SUPPRIMER' ;

$TableauDataCd['id'] = $id;
	
	/*
	 * Il faut aussi géré la table agt_prog ????
	 * 
  */
 
    
$update_res = $Aghate->UpdateEntry($table_loc,$TableauData,$TableauDataCd,'Delete');
	
if (!($update_res)){
	echo "|ERR|Un problème a eu lieu lors de la suppression de la réservation!";
	exit;
}
	
	
grr_sql_mutex_unlock('agt_loc');

echo "|OK|$id|"; 
?>
