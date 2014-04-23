<?php 
/*
#########################################################################
	PROJET MSI 
	Module Lamda                      	  																	
 	update statut d'un dossier 
	Author MOHANRAJU Sp SLS-APHP 
#########################################################################
	Dernière modification : 10/05/2013                    
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
// Update statut
//---------------------------------------------------	
if (strlen($_GET['ipop_id']) >0)
{ 
	$sql=" SELECT etat from ".$TableIpop."  where id ='".$_GET['ipop_id']."'";
	$res=$db->select($sql);
	if((strlen($res[0]['etat']) < 1 ) or (is_null($res[0]['etat']) ))
	{
		$sql=" UPDATE ".$TableIpop." set etat ='Annulation par  :".$_SESSION['user']." le ".date('d/m/Y h:i:s')."' where id ='".$_GET['ipop_id']."'";
		$db->update($sql);
		echo "[KO]";					
	}else{
		$sql=" UPDATE ".$TableIpop." set etat ='' where id ='".$_GET['ipop_id']."'";
		$db->update($sql);
		echo "[OK]";
	}
}

?>
