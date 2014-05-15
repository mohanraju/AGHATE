<?php
/*
#########################################################################
#                  ajax_aghate_del_demande.php                          #

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

$info_entry = $Aghate->GetInfoDemandeById($id);

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
// Prepare tableau pour l'update
//-----------------------------------------------------------------------------
$TableauProg = array();

$TableauProg['statut_entry'] = 'Refuse' ;
$TableauProg['motif'] = $motif;
//echo $motif;
$TableauProgCd['id'] = $id;

$del = $Aghate->UpdateEntry('agt_prog',$TableauProg,$TableauProgCd);
	
if (!($del)){
	echo "|ERR|Un problème a eu lieu lors de la suppression de la réservation!";
	exit;
}

//==========================================
//update dans la table agt_user_consult
//==========================================
$TableauConsCd['id'] 	= $id;
$TableauCons['statut_consult'] 	= "N"; //O oui ou N non 

$mysql->update_('agt_prog',$TableauCons,$TableauConsCd);  


	
grr_sql_mutex_unlock('agt_loc');

echo "|OK|$id|"; 
?>
