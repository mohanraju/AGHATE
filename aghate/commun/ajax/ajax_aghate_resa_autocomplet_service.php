<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
############################################################################################
*/
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
 
//==========================================================================================
// script s d'inclusion
//==========================================================================================
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

include "../../commun/include/CommonFonctions.php";

$db = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);


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

$tb=$_GET['tb'];

// escape your parameters to prevent sql injection
$param   = utf8_decode($_GET['term']);
$ret_vals = array();

//recupère session user
$user = getUserName();

$service_autoriser = $Aghate->GetAllServiceAuthoriser($user,$_SESSION['statut']);
//echo "tableau : ";
//print_r($service_autoriser);
for($i=0;$i<count($service_autoriser);$i++){
	$service_id[]=$service_autoriser[$i]['id'];
}
$str_srv_autoriser = implode(",",$service_id);
//echo "service autoriser".$str_srv_autoriser;

//echo $sql_ajax;
$ret_vals = array();

//execute SQL
$res=$Aghate->GetServiceInfoByValRech($param,$str_srv_autoriser,$tb);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i][0];
	$lib=$res[$i][1];
	
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => utf8_encode($lib),
        				'id'    => $id,
    						);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
