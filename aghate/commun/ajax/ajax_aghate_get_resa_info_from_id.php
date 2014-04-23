<?php
/*
#########################################################################################
		Projet AGHATE
		Get resrvation information from ID
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 23/01/2014
*/
 
// script s d'inclusion
include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassAghate.php");
/// ATTENTION FONCITONS dans MSI
require("../../../commun/include/CommonFonctions.php");

//Objet init
//$Functions = new CommonFunctions(false);
$Aghate=new Aghate();
$Aghate->NomTableLoc=$table_loc;
$com=new CommonFunctions(true);


include("../include/settings.inc.php");
session_name('GRR');
session_start();

if(strlen($_SESSION['login'])< 1)
{
		$return=json_encode(array('Erreur'=>'Session Expiré '));
		echo $return;
		exit;
}

// 	preparation de requettes
if($type_reservation=="Demande"){
	$Result=$Aghate->GetInfoDemandeById($entry_id);	
}
else{
	$Result=$Aghate->GetInfoReservation($entry_id);
}

$ResEnt=$Aghate->GetInfoEntry ($entry_id);
$nbr_rec=count($Result);
if ($nbr_rec > 0){
	$Result['entry']=date('d-m-Y H:i' ,$Result['start_time']);
	$Result['end']=date('d-m-Y H:i' ,$Result['end_time']);
	$Aghate->toTimeString($Result['duration'], $dur_units);
	$Result['naissance']=$com->Mysql2Normal($Result['ddn']);

	if(count($ResEnt)>0){
		if($ResEnt['description']){
			$ResEnt['description']=JsonDecode($ResEnt['description']);		
		}
		if($ResEnt['medecin']){
			$infomed=$Aghate->GetInfoMedecinById($ResEnt['medecin']);
			$ResEnt['nom_medecin']=$infomed['nom'];
			$ResEnt['prenom_medecin']=$infomed['prenom'];
			$ResEnt['specialite']=$infomed['specialite'];
		}
		// Check droit de modification
		if ($Aghate->GetUserLevel($_SESSION['login'],$ResEnt['room_id']) > 2)
			$ResEnt['droit_modif']= "yes";
		else
			$ResEnt['droit_modif']= "non";
	}
	$return=json_encode(array_merge($Result,$ResEnt));
	echo $return;
} 
else
{
		$return=json_encode(array('Erreur'=>'Pas de réseravation trouvée'));
		echo $return;
}
 
?>
