<?php
/*
#########################################################################
	PROJET MSI 
	Module Nestor                      	  																	
 	comentaires
	Author MOHANRAJU Sp SLS-APHP 
#########################################################################
	Dernière modification : 10/07/2013                    
*/
header('Content-Type: text/html; charset=utf-8');
include("../../user/session_check.php"); 

// Incluede config file du site
include("../../config/config.php"); 
include("../../config/config_".strtolower($_SESSION['site']).".php"); 
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include("../../commun/include/ClassNestor.php");


$Fonctions = new CommonFunctions(true);  // true mode developpment 
$Nestor =new Nestor($_SESSION['site']); // declaraion site
$Nestor->SetTableNestor($TableNestor); // nom de table dan mysql
$db=new MySQL();


//---------------------------------------------------	
// mode Ajout
//---------------------------------------------------	
if ($_GET['MODE'] == "ADD") 
{
	// pour la tracabilité on ajoute une ligne dans la table
	// nestor_ctrl
	$sql=" INSERT INTO nestor_ctrl set 
 							nas				='".$_GET['nda'].$_GET['dt_sor']." ',
						  date_maj	='".date('Y-m-d h:i:s')." ', 
					    user			='".$_GET['user']." ',
					    ctrl			='".$_GET['ctrl']." ',
					    commentaire		='Résume validé par l\'utilisateur le ".date("d/m/Y à h:i:s")."'";
	$res=$db->insert($sql);
	
	// on aojute le control dans la table nestor
	$Nestor->ValideResume($_GET['id_table'],$_GET['ctrl']);
	echo  "Valdé";		
	exit;
}

echo "Erreur maj , vauillez contacter l'administrateur du site";

?>
