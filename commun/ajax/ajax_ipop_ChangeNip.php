<?Php  
/*
#########################################################################
	PROJET MSI 
	Module IPOP                      	  																	
 	Chenge NIP 
	Author MOHANRAJU Sp SLS-APHP 
#########################################################################
	Dernière modification : 10/05/2013                    
*/
header('Content-Type: text/html; charset=utf-8');
// Lss inclusion du site
require("../../user/session_check.php"); 
require("../../config/config.php"); 
require("../../config/config_".strtolower($_SESSION['site']).".php"); 
require("../../commun/include/CommonFonctions.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassSimpa.php");


$Fonctions = new CommonFunctions();  // true mode developpment 
$db=new MySQL();


$Simpa= new Simpa($ConnexionStringSIMPA);

//---------------------------------------------------	
// Update statut
//---------------------------------------------------	
// check ipop_id
if(strlen($_GET['ipop_id']) < 1){
	echo "Erreur :: ipop_id,Reference du table IPOP est incorrect :".$_GET['ipop_id'];						
	exit;
} 
// check le bon nip
if (strlen($_GET['NIP_OK']) ==10)
{
	//check nip dans Gilda/Simpa	
	$res=$Simpa->GetPatInfoByNip($_GET['NIP_OK']);
	if(strlen($res[0]['NMMAL']) < 1 ) 
	{
		echo "Erreur ::NIP inconnu dans Gilda :".$_GET['NIP_OK'];						
		exit;
	}
	else{
		$patient=$res[0]['NMMAL']." ".$res[0]['NMPMAL'];
	}
}else{
	echo "Erreur ::NIP taille  incorrect :".$_GET['NIP_OK'];						
	exit;

}

// si tous va bien on update le nip
if (strlen($_GET['ipop_id']) > 0)
{ 
		$sql=" UPDATE ipop set nip_ok ='".$_GET['NIP_OK']."' where id ='".$_GET['ipop_id']."'";
		$db->update($sql);
		echo "[OK]".$patient;
		exit;					
}else{
		echo "Impossible de mettre à jour le NIP,\n Veuillez  essayer à nouveau";
		exit;
}
?>
