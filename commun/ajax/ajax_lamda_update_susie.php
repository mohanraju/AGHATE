<?php 
/*
#########################################################################
	PROJET MSI 
	Module Lamda                      	  																	
 	update statut d'un dossier maj dans OR oui/non
	Author MOHANRAJU Sp SLS-APHP 
#########################################################################
	Dernière modification : 10/05/2013                    
	appellé par functions_lamda.js
*/
header('Content-Type: text/html; charset=utf-8');

// Les inclusion
include("../../user/session_check.php"); 
include("../../config/config.php"); 
include("../../config/config_".strtolower($_SESSION['site']).".php"); 
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include("../../commun/include/ClassNestor.php");


$Fonctions = new CommonFunctions(true);  // true mode developpment 
$Nestor =new Nestor($_SESSION['site']); // declaraion site
$db=new MySQL();

//---------------------------------------------------	
// Update Susie TR transmis  / ATT attent
// retourne Erreur/TR/ATT 
//---------------------------------------------------	
if (strlen($_GET['id']) >0)
{ 
	$sql=" SELECT susie from lamda where id ='".$_GET['id']."'";
	$res=$db->select($sql);
	if (count($res) < 1)
	{
		echo "Erreur mise a jour, vuillez ressaier pls tard (pas de resultat :"+$_GET['id']+")";
		exit;
	}

	$new_stat=($res[0]['susie']=='TR' ? 'ATT' : 'TR' );
	$sql=" UPDATE lamda set susie ='$new_stat' where id ='".$_GET['id']."'";
	$db->update($sql);
	echo $new_stat;
}else
	echo "Erreur mise a jour, vuillez ressaier pls tard ! (id vide)";
?>
