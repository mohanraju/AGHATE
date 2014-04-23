<?php 
/*
##########################################################################################
	Projet CODAGE
	Get All Codes
	Script appelé dans la page "index.php" && au Onchange Thésaurus Service
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
	$thesaurus 
*/
require("../../user/session_check.php");
//  initialise variables 
$amt=20000; //Nombre enregistrements a afficher
$start=0;

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");

// init les objets
$db=new Mysql();
$TableCodage="codage_msi";
$sitesearch=substr(trim($_SESSION["site"]),1,2);
//echo $_SESSION["site"];
// get code from the base
if ($_SESSION["droits"] == 'MSI'){
	$result=$db->select("SELECT * from ".$TableCodage." WHERE diag='' AND valid='A' AND etat !='ENR' AND nda like '".$sitesearch."%' Order by `dteent` desc");
}
elseif  ($_SESSION["droits"] == 'ADMIN'){
	$result=$db->select("SELECT * from ".$TableCodage." WHERE diag='' AND valid='A' AND etat !='ENR' Order by `dteent` desc");
}
//echo "SELECT * from ".$TableCodage." WHERE diag='' AND valid='A' AND etat !='ENR' AND nda like '".$sitesearch."%' Order by `dteent` desc";
// nombre de row dans le r ltat
$total_records=count($result);

// prepare la tableu structure vec les donnn a retournes
$res1= '{  "aaData": [';

for($i=0;$i < $total_records; $i++)
{
	//echo 
	if ($i > 0) $res1.=',';
	//Recuperation des informations Codage dans la table codage_msi ET aussi Patient grace jax_getpat(); appell dans la Fonction EditCodeInfo()
	$editlink=addslashes('<a href="#"  onClick="EditCodeInfo('.$result[$i]['id_codage_msi'].','.$result[$i]['nda'].','.$result[$i]['uhdem'].')"><img src="../commun/images/voir.jpg" border="0" height="15" width="15"></a>');
	$editlinkmail=addslashes('<a href="mailto:thierry.celeste@sls.aphp.fr?subject=Outil de codage">'.$result[$i]['username'].'</a>');
	//$editlink="<a href='#'  onClick=\\\"EditCodeInfo('".$result[$i]["id_codage_msi"]."','".$result[$i]["nda"]."','".$result[$i]["uhdem"]."')\\\"><img src='../commun/images/edit.jpg' border='0' height='15' width='15'></a>";	
	//$editlink="<a href='#' onClick=\\\"EditCodeInfo('".$result[$i]["id_codage_msi"]."')\\\" ><img src='../commun/images/edit.jpg' border='0' height='15' width='15'></a>";	
	$res1.= ' ["'.$result[$i]['nda'].'",
	
					 "'.addslashes(trim($result[$i]['uhdem'])).'",
					 "'.addslashes(trim($result[$i]['type'])).'",
					 "'.addslashes($result[$i]['username']).'",
					 "'.addslashes($result[$i]['id_codage_msi']).'"]';}
 $res1.= ']}'; 
 echo $res1;
 
  
?>
