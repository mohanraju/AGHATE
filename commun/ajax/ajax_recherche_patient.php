<?php
/*
#########################################################################################
		ProjetMSI
		Module Outil MSI
		Recherche Patient
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/
//commun include pour les modules outil MSI
require("../user/session_check.php");
//-------------------------------------------------------------------------
// 		Vérifiction du site declared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> OUTILMSI::Erreur acces , Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site"];
}
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------

include("../config/config.php");
include("../config/config_".strtolower($site).".php"); 
include("../commun/include/ClassGilda.php");
require("../commun/include/CommonFonctions.php");

//Objet init
$Gilda=new Gilda($ConnexionStringGILDA);

// 	preparation de requettes
//	===================================================================

// 	si au moins 3 char de nom renseingné
$SQL="";
$LESCHAMPS="PAT.NOIP as NIP,PAT.NMMAL as NOM,PAT.NMPMAL as PRENOM,to_char(PAT.DANAIS,'DD/MM/YYYY') as DANAIS,PAT.CDSEXM as SEXE";
switch($desc_rech)
{
	case "NIP":
		$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NOIP =('".trim($val_rech)."') ";	
		break;
	case "NOM":
		$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".strtoupper(trim($val_rech))."%') ORDER BY NMMAL,NMPMAL";	
		break;
	case "NDA":
		$SQL= " SELECT $LESCHAMPS FROM PAT,DOS WHERE DOS.NOIP=PAT.NOIP AND DOS.NODA ='".trim($val_rech)."' ";	
		break;
	default:
		//echo "<div align='center'> Veuillez selectionner un type de recherche </div>";
		break;
		
}		
//echo $SQL;
if (strlen($SQL) > 1)
{
	$result=$Gilda->OraSelect($SQL);
}

$nbr_rec=count($result);
 
for($i=0;$i < $nbr_rec; $i++)
{
	$row = array();
	$row[]= $result[$i]['NIP'];
	$row[]= $result[$i]['NOM'];
	$row[]= $result[$i]['PRENOM'];
	$row[]= $result[$i]['DANAIS'];
	$row[]= ($result[$i]['SEXE']=='M'?'Male':'Female');
	$output['aaData'][] = $row;
}
if (count($output) < 1)	
	$output['aaData'][]=array('','','','',''); ;
echo json_encode( $output ); 	
?>
