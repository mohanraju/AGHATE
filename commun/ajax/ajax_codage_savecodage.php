<?php
/*
##########################################################################################
	Projet CODAGE
	Insert Codage à partir des infos DP DR DA et ACTES codés
	Script appeler par la page "index.php" au moment de l'enregistrement ou de l'envoi
	Auteur Thierry CELESTE SLS APHP
	Maj le 28/05/2013
##########################################################################################
	Parametres de page 
*/
session_start();
//require("../../user/session_check.php");

//Si on appel cet page depuis le module code_liste n'include pas les fichiers car deja present
include("../../config/config.php");
include("../../commun/include/CommonFonctions.php");
$site_patient="0".substr(trim($NDA),0,2);
//include("../../config/config_".$site_patient.".php");
 
include("../../commun/include/ClassMysql.php");
include('../../commun/include/ClassCim10.php');
include('../../commun/include/ClassCodeBarre.php');
include('../../commun/include/ClassUser.php');	
include('../../commun/include/ClassGilda.php');	

// init des objets
$Commonfunctions= new Commonfunctions();
$db		=	new MySQL();
$Cim10=new Cim10();
$Gilda		=	new Gilda($ConnexionStringGILDA);

$User=new User();
$User->AddLog($button_clicked.' codage NDA : '.$NDA.' NIP : '.$NIP);

switch ($button_clicked)
	{
		case "Enregistrer":
			$etat="etat='ENR',";
			break;
		case "Envoyer":
			$etat="etat='ENV',";
			break;
		case "Simulateur":
			$etat="etat='ENR',";
			break;
	}

//Vérification des correspondance entre NIP et NDA
$sql="Select NODA from DOS where NODA='$NDA' and NOIP='$NIP'";
$res=$Gilda->OraSelect($sql);
if (count($res) < 1)
{
	echo "Pas de correspondance entre se NIP et NDA, veuillez verifier et reessayer";
	exit;
}
// decalaration variables
$datmaj=date("Y-m-d H:i:s");
$DATSOR=$Commonfunctions->Normal2Mysql($DATSOR);
$DTEENT=$Commonfunctions->Normal2Mysql($DTEENT);
$DATEINTERVENTION=$Commonfunctions->Normal2Mysql($DATEINTERVENTION);



$sql_nohjo="";
if ($NOHJO !="" && $NOHJO!="0"){
	$sql_nohjo="AND nohjo='".$NOHJO."' ";
}
//----------------------------------------------
//Update DP DR DAS pour le meme NDA et UH avec statut Valid='A'
//----------------------------------------------
$sql_update="UPDATE $TableCodage set 
									valid='D',
									datmaj='".$datmaj."' 
								WHERE nda='".$NDA."'
								AND uhdem='".$UH."'
								".$sql_nohjo."
								AND type!='ACTES'
								AND valid='A'";	
$exec=$db->insert($sql_update);	

//----------------------------------------------
//Update ACTES pour le meme NDA, UH et DATEINTERVENTION avec statut Valid='A'
//----------------------------------------------
$sql_update="UPDATE $TableCodage set 
									valid='D',
									datmaj='".$datmaj."' 
								WHERE nda='".$NDA."'
								AND uhdem='".$UH."'
								".$sql_nohjo."
								AND datrea='".$DATEINTERVENTION."'
								AND valid='A'";	
$exec=$db->insert($sql_update);	

//----------------------------------------------
//preapre tableau Codage
//----------------------------------------------
$DataCodage = array();

for($n=0; $n < 4 ;$n++){
	switch ($n)
	{
		case 0:
			$CurCode[0]=$DP_info;
			$type="DP";
			break;
		case 1:
			$CurCode[0]=$DR_info;
			$type="DR";
			break;
		case 2:
			$CurCode=$DAS;
			$type="DAS";
			break;
		case 3:
			$CurCode=$ACTES;
			$type="ACTES";
			break;
	}

	for($i=0;$i < count($CurCode); $i++)
	{	
		list($lib, $code) = explode("[(", trim($CurCode[$i]));
		list($code, $reste) = explode(")]", trim($code));	
		if (($code != '') || ($lib != ''))
		{
			$codage["TYPE"]	=$type;
			$codage["CODE"]	=$code;
			$codage["LIB"]	=$lib;
			$DataCodage[]=$codage;
			unset($codage);
			//echo "tata ".$DataCodage[0]["LIB"];
			//exit;
		}
	}
	unset($CurCode);
}

//----------------------------------------------
//preapre SQL
//----------------------------------------------

$champ_uniques="nip				='".$NIP."',
							  nda				='".$NDA."',
							  dteent		='".$DTEENT."',
						    datsor		='".$DATSOR."',
							  ".$etat."
							  libuhdem	='".$LIBUH."',
							  datmaj		='".$datmaj."',
							  valid			='A',
							  username	='".$user."',";
	
$sql_idipop="";
if ($IDIPOP !="" && $IDIPOP!="0"){
	$sql_idipop=" id_ipop='".$IDIPOP."', ";
}

$sql_nohjo="";
if ($NOHJO !="" && $NOHJO!="0"){
	$sql_nohjo=" nohjo='".$NOHJO."', ";
}

//Si on ne peu pas determiner l'uhexec alors elle est par defaut celle de l'uhdem
if ($uhexec ==""){
	$uhexec=$UH;
}
							  
for($n=0; $n < count($DataCodage);$n++){
	//A faire
	//UH demandeuse from Gilda & UH exécutante liée à l'utilisateur
		if($DataCodage[$n]['TYPE'] == "ACTES")
		$champ_autre="uhexec		='".$uhexec."', uhdem			='".$UH."', datrea			='".$DATEINTERVENTION."', ".$sql_nohjo.$sql_idipop;
	else
		$champ_autre="uhexec		='".$uhexec."', uhdem			='".$UH."', ".$sql_nohjo.$sql_idipop;
	$sql=" INSERT INTO $TableCodage set ".$champ_uniques.$champ_autre."
								  type			='".$DataCodage[$n]['TYPE']."',
								  diag			='".$DataCodage[$n]['CODE']."',
								  libdiag		=\"".utf8_decode(trim($DataCodage[$n]['LIB']))."\"";
	
	$data=$db->insert($sql);
	//echo "tata ".$sql;
}

if ($button_clicked !="Simulateur")
	echo "Vos donnees ont bien ete enregistrees";
?>
