<?Php  
/*
* PROJET AGHATE
* Ajax get patients pour les recherche par nom/noip
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
* 
*/
// script s d'inclusion
include "../../resume_session.php";
include "../../config/config.php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/CommonFonctions.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);

if (strlen($table_loc)< 1){
    echo "|ERR|Erreur declaration Table LOC ";
	exit;
}else
	$Aghate->NomTableLoc =$table_loc;
 
$info_entry = $Aghate->GetInfoReservation($id);

 
//-----------------------------------------------------------------------------
// Récupération des données concernant l'affichage du planning du domaine
//-----------------------------------------------------------------------------
get_planning_area_values($area);

if($Aghate->GetUserLevel($_SESSION['login'],$info_entry['room_id'] ) < 3){
{
	echo "|ERR|Access Denied !";
	exit();
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
	

echo "|OK|$id|"; 
?>
