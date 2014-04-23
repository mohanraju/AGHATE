<?php
/*
##########################################################################################
	Projet CODAGE
	Get Date Sortie d'un séjour 
	Script appelé dans la page "ajax_codage_get_sejour.js"
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
	1 $nda=$_GET["nda"];
	2 $uh=$_GET["uh"];
	3 $dtent=$_GET["dtent"];
*/
require("../../user/session_check.php");
//-------------------------------------------------------------------------
// 		Vérifiaction du site déclared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur accès au IPOP, Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
	//echo $_SESSION["site"];
}
include("../../config/config_".$site.".php");
require_once("../include/CommonFonctions.php");
require_once('../include/ClassGilda.php');
$Gilda =new Gilda($ConnexionStringGILDA);
$ComFunc =new CommonFunctions();
			
//Declarer dans ./inc/config.php

$nda=$_GET["nda"];
$uh=$_GET["uh"];
$dtent=$_GET["dtent"];
/*
$sql="Select tydos from dos where NODA='$nda'";

$data=$Gilda->OraSelect($sql);    	

$type_dossier=$data[0]['TYDOS'];
*/
$type_dossier=$_GET['tydos'];
switch($type_dossier)
{
	case "A": // aigu / HC
		$sqlDASOR="SELECT nouf as uh,to_char(DAMVAD,'DD/MM/YYYY') as DATE_MVT
  		FROM mvt
  		WHERE mvt.noda='$nda'
  		and mvt.tymaj <>'D'
      AND TYMVAD = 'SH'
  		order by damvad
		";
		// recupares les premier et le dernière dates d'uh  	

		$res=$Gilda->OraSelect($sqlDASOR);
		$dasor=$res[0]['DATE_MVT'];
		break;			
	default :
		$dasor=$dtent;
		break;
}

$Jours=$ComFunc->JoursBetween2Dates($dasor,$dtent);

$retdate=trim($dasor)."|".trim($Jours);

echo trim($retdate);
?>
