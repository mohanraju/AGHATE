<?php 
##############################################################################################
# Ajax save Code Info dans la table
# toutes données sont envoyée par GET
# 
################################################################################################
session_start();
//require("../../user/session_check.php");
// insertion des objets
require_once("../../config/config.php");
 
require_once("../../commun/include/ClassMysql.php");
require_once("../../commun/include/CommonFonctions.php");
require_once('../../commun/include/ClassCim10.php');
require_once('../../commun/include/ClassUser.php');	

//$id_codage_msi=$_POST['id_codage_msi'];

// init les objets
$ComFunc= new Commonfunctions(true);
$db		=	new MySQL();
$Cim10=new Cim10();

$TableCodage="codage_msi";

if($type == "ACTES")
	$TableSpec="ACTESSPEC";
else
	$TableSpec="CIM10SPEC";

//Declaration du mode code_liste pour ne pas avoir l'echo à la fin de ajax_codage_get_info_codage_msi.php

//=================================================================
// if save button clicked 
// update code informations dans la base
// insert code + libelle dans table CIM10SPEC
//=================================================================

//list($DP_info, $DR_info, $LIB) = explode("|", trim($retcodage));
$libdiag=utf8_decode($libdiag);
if (strlen($id_codage_msi) > 1)
{
	$User=new User();
	$User->AddLog('Enregistrement codage via Code liste NDA : '.$NDA.' NIP : '.$NIP);
	// update code
	$sql_update  ="UPDATE ".$TableCodage." set";
	$sql_update .=" libdiag ='".$libdiag."',";
	$sql_update .=" diag ='".$diag."'";
	$sql_update .=" WHERE id_codage_msi ='".$id_codage_msi."'";	 
	$db->update($sql_update);
	
  //vérif si libdiag + diag not exist in CIM10SPEC
 	$sql="SELECT * from ".$TableSpec." WHERE LIB='".$libdiag."' AND CODE1='".$diag."'";
	$res=$db->select($sql);
	
	//insert code + libelle
  if(!count($res)){
  	$db->insert("INSERT INTO ".$TableSpec." (LIB,CODE1,LOGIN,FREQ) values('$libdiag','$diag','$user','10000')");
  }
 
 	//Récuperation des informations Codage dans la table codage_msi
 	$Codage=$Cim10->GetInfoCodageMsi($NDA,$UH,$nohjo);

	if (count($Codage) > 0)
	{
		/*echo "start<pre>";
		print_r($Codage);
		echo "</pre>end";
		exit;*/
		$UH=$Codage[0]['uhdem'];
		$LIBUH=$Codage[0]['libuhdem'];
		$DTEENT=$ComFunc->Mysql2Normal($Codage[0]['dteent']);
		$DATSOR=$ComFunc->Mysql2Normal($Codage[0]['datsor']);
		$AGE=$ComFunc->CalculAge($DDN,$DTEENT);
		
		$DP_info="";
		$CodageDP=$Cim10->GetCodageMsiDp($NDA,$UH,$nohjo);
		$DP_info=$CodageDP[0]['libdiag']." [(".$CodageDP[0]['diag'].")]";
		
		$DR_info="";	
		$CodageDR=$Cim10->GetCodageMsiDr($NDA,$UH,$nohjo);
		$DR_info=$CodageDR[0]['libdiag']." [(".$CodageDR[0]['diag'].")]";
		
		$DAS=array();
		$CodageDAS=$Cim10->GetCodageMsiDas($NDA,$UH,$nohjo);
		for($i=0;$i< count($CodageDAS);++$i) 
		{
			$DAS[$i]=$CodageDAS[$i]['libdiag']." [(".$CodageDAS[$i]['diag'].")]";
		}
		
		$ACTES=array();
		$DATEINTERVENTION="";
		$CodageACTES=$Cim10->GetCodageMsiActesForPrint($NDA,$UH,$nohjo);
		for($i=0;$i< count($CodageACTES);++$i) 
		{
			$ACTES[$i]=$CodageACTES[$i]['libdiag']." [(".$CodageACTES[$i]['diag'].")]";
		}
	}
	
	//Insertion dans la base et génération du PDF si tous les codes sont renseignés
	$CheckCodage=$Cim10->CheckCodageBeforePdf($NDA,$UH,$nohjo);
	//Force Envoyer car si pas de DP pas de PDF
	if (!count($CodageDP))
		$CheckCodage="1";
	if (!count($CheckCodage))
	{
		//echo "PDF OK";
	  $button_clicked ="Envoyer";
	  include("../../codage/printcodage_codeliste.php");
	}
  
	//echo "Les modifications sont bien enregistré"	;
}else{
	echo "Erreur enregistrement, veuillez recommencer ";
}
  
?>
