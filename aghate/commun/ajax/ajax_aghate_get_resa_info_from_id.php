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

if(strlen($login)< 1)
{
		$return=json_encode(array('Erreur'=>'Session Expiré '));
		echo $return;
		exit;
}

// 	preparation de requettes
$Result=$Aghate->GetInfoReservation ($entry_id);
//$ResEnt=$Aghate->GetInfoEntry ($entry_id);

$nbr_rec=count($Result);

if ($nbr_rec > 0){
	$Result['entry']=date('d-m-Y H:i' ,$Result['start_time']);
	$Result['end']=date('d-m-Y H:i' ,$Result['end_time']);
	$Aghate->toTimeString($Result['duration'], $dur_units);
	$Result['naissance']=$com->Mysql2Normal($Result['ddn']);

	
		if($Result['description']){
			$Result['description']=JsonDecode($Result['description']);		
		}
		if($Result['medecin']){
			$infomed=$Aghate->GetInfoMedecinById($Result['medecin']);
			$Result['nom_medecin']=$infomed['nom'];
			$Result['prenom_medecin']=$infomed['prenom'];
			$Result['specialite']=$infomed['specialite'];
		}
		// Check droit de modification
		if ($Aghate->GetUserLevel($login,$Result['room_id']) > 2)
			$Result['droit_modif']= "yes";
		else
			$Result['droit_modif']= "non";

	
	$return=json_encode($Result);
	echo $return;
} 

else
{
		$return=json_encode(array('Erreur'=>'Pas de réseravation trouvée'));
		echo $return;
}
 
?>
