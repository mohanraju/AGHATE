<?php 
##############################################################################################
# Ajax save User Info dans le table
# tous données sont envoyée par GET
# 
################################################################################################
header('Content-Type: text/html; charset=utf-8');

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/CommonFonctions.php");

$login=$_POST['login'];
// init les objets
$db=new Mysql();
$TableUser="utilisateur";
 
//=================================================================
// if save button clicked 
// update user informations dans la base
// update les droit selectionnées ou modifié
//=================================================================
if (strlen($login) > 1)
{ 
	$sql_update  ="UPDATE ".$TableUser." set";
	$sql_update .=" etat ='".$etat."',";
	$sql_update .=" default_page ='".$default_page."',";
	$sql_update .=" default_site ='".$default_site."',";
	$sql_update .=" profile ='".$profile."'";
	$sql_update .=" WHERE login ='".$login."'";	 
	$db->update($sql_update);
 
	//=================================================================	
	// update user droit spécifiques, si selectionnés ou modifèés
	//=================================================================

  // delete tous les droits existant
  $db->delete("delete from user_droits where droit_type='SERVICE' and login='".$login."'");	
  
  //insert les nouvaux droits	
  for($i=0; $i < count($actuel_droits);$i++){
  	$db->insert("INSERT INTO user_droits (login,droit_type,droit_value) values('$login','SERVICE','".$actuel_droits[$i]."')");	
  }
 
	echo "Les modifications sont bien enregistré"	;
}else{
	echo "Erreur enregistrement, veuillez recomancer ";
}

 
  
?>
