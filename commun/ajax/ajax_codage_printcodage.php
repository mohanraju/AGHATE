<?php
/*
##########################################################################################
	Projet CODAGE
	Insert Codage à partir des infos DP DR DA et ACTES codés
	Script appeler par la page "index.php" au moment de l'enregistrement ou de l'envoi
	Auteur Thierry CELESTE SLS APHP
	Maj le 28/05/2013
##########################################################################################
Parametres $DataCodage
*/
require("../../user/session_check.php");

include("../../config/config.php");
include("../../config/config_".$_SESSION['site_patient'].".php");
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include('../../commun/include/ClassCim10.php');
include('../../commun/include/ClassCodeBarre.php');
include('../../commun/include/ClassUser.php');	
// init des objets
$Commonfunctions= new Commonfunctions(true);
$db		=	new MySQL();
$Cim10=new Cim10();

if ($button_clicked ==""){
	echo "OK";
	exit;
}

//----------------------------------------------
// dossier de depot PDF
// Pour l'hopital saint-louis les fichiers PDF sont centraliser dans un seul dossier MSISLS pour les TIM
// Pour l'hopital lariboisier les fichiers PDF sont deposes dans le dossier du service dont l'UH demand 
//----------------------------------------------
if ($_SESSION["site_patient"] == "076")
	$dospdf='../../codage/depot_pdf/MSISLS/';
else
	$dospdf='../../codage/depot_pdf/'.$service_lib.'/';

//Declaration du nom du fichier PDF
$namepdf=$NOM.'_'.$PRENOM.'_UH_'.$UH.'_'.$NDA.'.pdf';

// date convertion au format mysql
$DATSOR=$Commonfunctions->Normal2Mysql($DATSOR);
$DTEENT=$Commonfunctions->Normal2Mysql($DTEENT);

// recuperation libelle du service
$res_serv=$Cim10->GetServiceLib($UH,$DTEENT,$NDA);

$service_lib=$Commonfunctions->ConvertNomService($res_serv[0]['service_lib']);

// date convertion au format normal
$DATSOR=$Commonfunctions->Mysql2Normal($DATSOR);
$DTEENT=$Commonfunctions->Mysql2Normal($DTEENT);
$_GET["LIBUH"]=$service_lib;



$sortiepdf=$dospdf.$namepdf;

//Récupération de tous les actes codés pour le print
$DATEINTERVENTION="";
$Actes=$Cim10->GetCodageMsiActesForPrint($NDA,$UH,$NOHJO);
$ACTES = array();
for($i=0;$i< count($Actes);++$i)
{
	$ACTES[$i]=$Actes[$i]['libdiag']." [(".$Actes[$i]['diag'].")]";
}

/**************************************************************************
** MAIN
**************************************************************************/
$Codebarre=new CODEBARRE();
$Codebarre->Open();
// PREMIERE PAGE 
$Codebarre->AddPage();
$Codebarre->PageHeader($_GET);
$x=11; //pos colone
$y=45; // pos ligne
$k=0;  // check nbr lignes par page

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
		}
	}
	unset($CurCode);
}


$a=0;
$nombre_rows=count($DataCodage);
for ($i=0; $i < $nombre_rows ; $i++) 
{
	$Codebarre->SetFont('Arial','',11); 
	// decoupe le libelle si len est superieur de 120
	// coupage par le mot complet 
	if ( $Codebarre->GetStringWidth($DataCodage[$i]['LIB']) > 120 ) {
		$Mot=explode(' ',utf8_decode($DataCodage[$i]['LIB']));
		$Str=$Mot[0]." ";
		for($j=1;$j< count($Mot);++$j) {
			if ( $Codebarre->GetStringWidth($Str.$Mot[$j]) > 120 ) break;
				$Str.=$Mot[$j]." ";
		}
	}
	else $Str=utf8_decode($DataCodage[$i]['LIB']);
	
	if($DataCodage[$i]['TYPE']=="ACTES")
	{
		if(($a=="0") || ($Actes[$a]['datrea']!=$Actes[$a-1]['datrea']))
		{
			$DATREA=$Commonfunctions->Mysql2Normal($Actes[$a]['datrea']);
			$UHEXEC=$Actes[$a]['uhexec'];
			$Codebarre->SetFont('Arial','B',6);
			$Codebarre->Text(2,$y,stripslashes($DataCodage[$i]['TYPE']),'N',11);
			$Codebarre->SetFont('Arial','B',10);
			$Codebarre->Text($x,$y,stripslashes(utf8_decode("Date intervention ".$DATREA)),'',11);	
			$Codebarre->Line(2,$y+4,206,$y+4);		
			$y+=10;
		}
		$a++;
	}
	
	
	//Print TYPE DP, DR, DAS, ACTES
	if (($DataCodage[$i]['TYPE']=="DP") || ($DataCodage[$i]['TYPE']=="DR"))
	{
		$Codebarre->SetFont('Arial','B',10);
	}
	else
	{
		$Codebarre->SetFont('Arial','B',6);
	}
	
	if ($DataCodage[$i]['TYPE']!="ACTES")
		$Codebarre->Text(2,$y,stripslashes($DataCodage[$i]['TYPE']),'N',11);
	else
		$Codebarre->Text(2,$y,stripslashes($UHEXEC),'N',11);
	
	//Print LIBELLE
	$Codebarre->SetFont('Arial','',10);
	$Codebarre->Text($x,$y,stripslashes($Str),'',11);	
	
	//Print CODES
	$Codebarre->SetFont('Arial','',10); 
	$Codebarre->Text($x+130,$y,"".$DataCodage[$i]['CODE'],'',11);	
	
	//Print CODE A BARRES
	$Codebarre->SetFont('Arial','',11); 
	$taillecb=8;
	if ($DataCodage[$i]['CODE'] !=  "" )
		$Codebarre->GeneCodeBarre($x+155,$y-($taillecb+1),"",$DataCodage[$i]['CODE'],0.2,$taillecb,false);
	$Codebarre->Line(2,$y+4,206,$y+4);		
	
	//AutoIncrementation next line
	$y+=10;
	$k++;
	
	// verification fin de page pour ajouter un nouvelle page
	if($k > 23)
	{
		$Codebarre->AddPage();
		$Codebarre->PageHeader($_GET);
		$k=0;
		$y=45;
	}
 

}

 
if ($button_clicked =="Enregistrer"){
	$sql_update="UPDATE $TableCodage set 
									etat='ENR'
								WHERE nda='".$NDA."'
								AND uhdem='".$UH."'
								AND valid='A'";	
	$exec=$db->insert($sql_update);	
	//$Codebarre->Output();
 
}
elseif ($button_clicked =="Envoyer"){
	//Insertion dans la base et génération du PDF si tous les codes sont renseignés
	$CheckCodage=$Cim10->CheckCodageBeforePdf($NDA,$UH,$NOHJO);
	
	//Force Envoyer car si pas de DP pas de PDF
	if ($DP_info == "")
		$CheckCodage="1";
		
	if (!count($CheckCodage))
	{
		$User=new User();
		$User->AddLog('Génération PDF codage NDA : '.$NDA.' NIP : '.$NIP);
		$sql_update="UPDATE $TableCodage set 
									etat='PDF'
								WHERE nda='".$NDA."'
								AND uhdem='".$UH."'
								AND valid='A'";	
		$exec=$db->insert($sql_update);	
		//echo "PDF OK";
		//PDF Genere		
		$Codebarre->Output($sortiepdf);
		//include("./mail_codage.php");
	}
	else{
		$sql_update="UPDATE $TableCodage set 
									etat='ENV'
								WHERE nda='".$NDA."'
								AND uhdem='".$UH."'
								AND valid='A'";	
		$exec=$db->insert($sql_update);	
		//echo "ENV OK";
		//echo "<br><br><h1 align='center' ><FONT COLOR='#FF0000'><b>Codage enregistre mais pas envoye car :".$message_sortie." </b></FONT></h1>";
	}
}
echo "OK";
?>
