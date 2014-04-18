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


//---------------------------------------------------	
// mode Ajout
//---------------------------------------------------	
if ($_GET['MODE'] == "ADD") 
{
	$sql=" INSERT INTO nestor_ctrl set 
 							nas				='".$_GET['nda'].$_GET['dt_sor']." ',
						  date_maj	='".date('Y-m-d')." ', 
					    user			='".$_GET['user']." ',
					    ctrl			='".$_GET['ctrl']." ',
					    commentaire		='".$_GET['commentaire']." '";
  if (strlen($_GET['commentaire']) > 2){
		$res=$db->insert($sql);
	}
}

//---------------------------------------------------	
// mode Ajout
//---------------------------------------------------	
if ($_GET['MODE'] == "DEL") 
{
	$sql=" UPDATE nestor_ctrl set 
							ctrl='SUP'
 				 WHERE id='".$_GET['row_id']."'";
		$res=$db->update($sql);

}
//=====================================================
//PREPARE LA TABLEAU À RENVOYER
//=====================================================
$CtrlRes=$Nestor->GetCommentaires($_GET['nda'],$_GET['dt_sor']);
//-----------------------------------------------------	
// affiche les commentaires existe dans la base
//-----------------------------------------------------	

$ret_val_html="
	<table width='800' border='1' align='left' cellpadding='0' cellspacing='0' >
	 		<thead>
		  	<tr class='header_blue'>
		    	<td>Date</td>	  	
		    	<td>Utilisateur</td>
		    	<td>Commentaire</td>
		    	<td>Statut</td>
			</tr>
		</thead";


for($i=0;$i < count($CtrlRes); $i++)
{
	$ret_val_html .="	
	<tr>
		<td>". $Fonctions->Mysql2Normal($CtrlRes[$i]['date_maj'])."&nbsp;</td>
		<td>". $CtrlRes[$i]['user']	."&nbsp;</td>";
			if($CtrlRes[$i]['ctrl']=='SUP')
			{
				$ret_val_html .="	<td style='text-decoration:line-through;'>". $CtrlRes[$i]['commentaire']	."&nbsp;</td>
						<td > Suprimée  </td>";
			}else{
				$ret_val_html .="<td>". $CtrlRes[$i]['commentaire']	."&nbsp;</td>
						<td> <input type='image' src='../commun/images/ko.jpg'  border='0' height='15' width='15' onclick=\"AddCommentairesTim('DEL','".$_GET['nda']."','".$_GET['dt_sor']."','".$CtrlRes[$i]['id']."');\" /></td>";			
		  }	
		echo "
	</tr>"; 
}

//-----------------------------------------------------	
// propose les nouveau commentaires 
//-----------------------------------------------------	
	$ret_val_html .="	
	<tr> 
		<td>".date('d/m/Y')."</td>       	
		<td>".$_SESSION['user']."</td>       
		<td><textarea name=\"commentaire\"  rows=\"1\" cols=\"60\" id=\"commentaire\"></textarea></td>		
		<td><input type='image' src='../commun/images/ajouter.jpg' border='0' height='20' width='20' onclick=\"AddCommentairesTim('ADD','".$_GET['nda']."','".$_GET['dt_sor']."');\" />   
		</td>    				
	</tr>
</table>
";		

 echo $ret_val_html;
?>
