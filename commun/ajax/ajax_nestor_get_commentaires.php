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
header('Content-Type: text/html; charset=utf-8');
// Les inclusion
include("../../config/config.php"); 
include("../../config/config_".strtolower($_SESSION['site']).".php"); 
include("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassMysql.php");
include("../../commun/include/ClassNestor.php");


$Fonctions = new CommonFunctions(true);  // true mode developpment 
$Nestor =new Nestor($_SESSION['site']); // declaraion site
$db=new MySQL();

//=====================================================
//PREPARE LA TABLEAU À RENVOYER
//=====================================================
$CtrlRes=$Nestor->GetCommentaires($_GET['nda'],$_GET['date_sortie']);

for($i=0;$i < count($CtrlRes); $i++)
{
	if($CtrlRes[$i]['ctrl'] != "SUP")
	{ 
		echo "	
		<tr>
			<td>". $Fonctions->Mysql2Normal($CtrlRes[$i]['date_maj'])."&nbsp;</td>	
			<td>". $CtrlRes[$i]['user']	."&nbsp;</td> 
		  <td>". str_replace("_"," ",$CtrlRes[$i]['type'])." " .($CtrlRes[$i]['commentaire']==1?" ":$CtrlRes[$i]['commentaire'])	."&nbsp;</td>
			<td> <img class='DEL_COM' src='../commun/images/ko.jpg'  border='0' height='15' width='15' TAG='".$CtrlRes[$i]['id']."' /></td>
		</tr>";	
	}
}	
 
?>
