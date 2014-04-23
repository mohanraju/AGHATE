<?php
/*
##########################################################################################
	Projet CODAGE
	Get codage print dans la base MySql pour un sejour selectionner
	Script appeler dans la page "ajax_codage_get_sejour.js"
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
		$NDA=$_GET['NDA'];
		$UH=$_GET['UH'];
		$NOHJO=$_GET['NOHJO'];
*/
require("../../user/session_check.php");


//-------------------------------------------------------------------------
// 		Verification du site declared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur acce Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
	//echo $_SESSION["site"];
}

include("../../config/config_".$site.".php");
require_once("../include/CommonFonctions.php");
require_once("../../config/config.php");
require_once('../include/ClassMysql.php');
require_once('../include/ClassCim10.php');

$ComFunc =new CommonFunctions();
$Cim10 =new Cim10();

$Codage=$Cim10->GetInfoCodageMsi($NDA,$UH,$NOHJO);


if (count($Codage) > 0){
	
 
	$UH=$Codage[0]['uhdem'];
	$LIBUH=$Codage[0]['libuhdem'];
	$DTEENT=$ComFunc->Mysql2Normal($Codage[0]['dteent']);
	$DATSOR=$ComFunc->Mysql2Normal($Codage[0]['datsor']);
	$AGE=$ComFunc->CalculAge($DDN,$DTEENT);

  $retval="<codage>";
	for($i=0;$i< count($Codage);++$i) 
	{
		$val=($Codage[$i]['diag'] != "")?$Codage[$i]['libdiag']." [(".$Codage[$i]['diag'].")]":$Codage[$i]['libdiag'];			
		switch($Codage[$i]["type"])
		{
			case "DP":
				$retval.="<dp>".$val."</dp>";			
				break;
			case "DR":
				$retval.="<dr>".$val."</dr>";			
				break;
			case "DAS":
				$retval.="<das>".$val."</das>";			
				break;
			/*case "ACTES":
				$retval.="<actes>".$val."</actes>";			
				break;*/
		}
	}
	$retval.="</codage>";
	print $retval;
}
else
	print "<ERROR>Pas de patient </ERROR>";	
?>
