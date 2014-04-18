<?php 
/*
#########################################################################
	PROJET MSI 
	Module Nestor                      	  																	
 	comentaires
	Author MOHANRAJU Sp SLS-APHP 
#########################################################################
	Dernière modification : 10/05/2013                    
*/
include("../../user/session_check.php"); 

// Incluede config file du site
include("../../config/config.php"); 
include("../../config/config_".strtolower($_SESSION['site']).".php"); 
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include("../../commun/include/ClassNestor.php");

header('Content-Type: text/html; charset=utf-8');
$Fonctions = new CommonFunctions(true);  // true mode developpment 
$Nestor =new Nestor($_SESSION['site']); // declaraion site
$db=new MySQL();

// recupare les varibels cle
$nas=$_GET['nda'].$_GET['date_sortie'];
$user=$_GET['user'];

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
$sql ="DELETE FROM ".$TableCommentaires." where  
						date_maj='".date('Y-m-d')."' AND 
						nas='".$nas."' AND 
						user='".$user."'";
//$db->delete($sql);
 

//---------------------------------------------------	
// insert les mnoveau commentaires
//---------------------------------------------------	
foreach ($_GET as $key => $val){
	if ( ($key != "nda") && ($key != "date_sortie")&& ($key != "user") & ($key != "undefined")  && strlen($val) > 0 ){
		$sql ="INSERT  into ".$TableCommentaires." set  
						date_maj='".date('Y-m-d h:i:s')."',
						nas='".$nas."',
						user='".$user."',
						type ='".$key."', 
						commentaire='".addslashes($val)."'";
	//echo $sql;						
	$db->insert($sql);
	}
}
 
?>
