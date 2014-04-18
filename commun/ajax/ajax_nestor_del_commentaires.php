<?php
/*
#########################################################################################
		ProjetMSI
		Module Nestor
		del commentaires tim
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
module included dans sejour complet.php
appellé partir de sejour_complet.php
Dernière modification : 10/05/2013                    
*/
// Incluede config file du site
header('Content-Type: text/html; charset=utf-8');
include("../../user/session_check.php"); 
include("../../config/config.php"); 
include("../../config/config_".strtolower($_SESSION['site']).".php"); 
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include("../../commun/include/ClassNestor.php");


$Fonctions = new CommonFunctions(true);  // true mode developpment 
$Nestor =new Nestor($_SESSION['site']); // declaraion site
$db=new MySQL();

// recupare les varibels cle
$row_id=$_GET['row_id'];
 
//---------------------------------------------------	
// vérification du nom de table dans config
//---------------------------------------------------	
if(strlen($TableCommentaires)< 1){
	echo "Configuration erreur => Variable TableCommentaires non declaré!!!";
	exit;
}

//---------------------------------------------------	
// delete l'anciene corrections 
// si par le me^me utilisateur et dans le même journe
//---------------------------------------------------	
$sql ="UPDATE ".$TableCommentaires." set 		ctrl='SUP' 	where id=".$row_id;
$db->update($sql);

?>
