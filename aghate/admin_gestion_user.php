<?php
require("./config/config.php");
require("./commun/include/CommonFonctions.php");
require("./commun/include/ClassMysql.php");
require("./commun/include/ClassAghate.php");
include("./commun/include/ClassHtml.php");
include "./commun/include/admin.inc.php";

$grr_script_name = "admin_access_area.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
$day   = date("d");
$month = date("m");
$year  = date("Y");

if(authGetUserLevel(getUserName(),-1,'area') < 4)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";

?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php
$reg_user_login = isset($_GET["reg_user_login"]) ? $_GET["reg_user_login"] : NULL;
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';

// Si la table j_user_area est vide, il faut modifier la requ�te
//$test_agt_j_user_area = grr_sql_count(grr_sql_query("SELECT * from agt_j_user_area"));

// init les objets
$Aghate=new Aghate();
$html= new Html($db);

//================================================================= 
// gestion action 
// add item de Gauche vers Droite ==>
//=================================================================
if($action=="ADDITEM")
{
	$err=false;
	if(strlen($id_user)<1){
		echo "<br>Gauche :: invalide user ID";
		$err=true;
	}

	/*if(strlen($target_user_id)<1){
		echo "<br>Droite :: invalide serviceID";
		$err=true;
	}*/
	
	if(strlen($source_service)<1){
		echo "<br>Gauche:: Aucun service selectionn�";
		$err=true;
	}
	
	if(!$err)
	{
		/*echo "</br>".$id_user;
		echo "</br>".$source_service;
		echo "</br>".$target_droit;*/
		$verif_sql="SELECT COUNT(*) FROM agt_j_user_area WHERE login='".$id_user."' AND id_area='".$source_service."'";
		$res=$Aghate->select($verif_sql);
		if($res[0][0] < 1){
			//echo "</br>insert";
			$insert_sql="INSERT INTO agt_j_user_area VALUES('".$id_user."','".$source_service."','".$target_droit."')"; 
			$Aghate->update($insert_sql);
		}else{
			//echo "</br>update";
			$update_sql="UPDATE agt_j_user_area set droit='".$target_droit."' WHERE login='".$id_user."' AND id_area='".$source_service."'"; 
			$Aghate->update($update_sql);
		}
		header("../admin_gestion_user.php?target_user_id=&source_service=&target_service=&target_droit=&action=&id_user=".$login."&droit=r#");	
	}

}
 
// add item de droite vers Gauche <==
if($action=="DELITEM")
{

	$err=false;
	/*if(strlen($id_user) < 1){
		echo "<br>Gauche :: invalide userID";
		$err=true;
	}*/

	if(strlen($target_user_id)<1){
		echo "<br>Droite :: invalide serviceID";
		$err=true;
	}
	
	/*if(strlen($source_service)<1){
		echo "<br>Gauche:: Aucun lit selectionn?ans le service";
		$err=true;
	}*/
	if(strlen($target_service)< 1){
		echo "<br>Droite ::Aucune selection";
		$err=true;
	}
	
	if(!$err)
	{
		$delete_sql="DELETE FROM agt_j_user_area WHERE login='".$id_user."' AND id_area='".$target_service."'"; 
		$Aghate->update($delete_sql);
	}	

}
 
//================================================================= 
// recup�re les utilisateurs
//=================================================================
//$res=$Aghate->GetAllArea();
$res=$Aghate->select("select * from agt_utilisateurs order by nom");
for ($i=0 ;$i < count($res) ;$i++)
{
	if (trim($res[$i]['login']) != "admin" && trim($res[$i]['login']) != "Automate"){
		$ListeUtilisateurs[] =	$res[$i]['login']."|".$res[$i]['nom']." ".$res[$i]['prenom']."";
	}
}

//================================================================= 
// recup�re les services 
//=================================================================
//$res=$Aghate->GetAllArea();
$res=$Aghate->select("select * from agt_service where etat = 'n' order by service_name");
for ($i=0 ;$i < count($res) ;$i++)
{
	$ListeServices .= "<option value='".$res[$i]['id']."'>".$res[$i]['service_name']."</option>";
	
}

 
//================================================================= 
// Recup�re les services d�ja administr� 
//=================================================================
//MODIF ICI
if (strlen($id_user) > 0){
	/*$sql="SELECT agt_j_user_area.login AS login, agt_j_user_area.id_area AS id_area, agt_service.service_name as service_name
		FROM agt_j_user_area, agt_service, agt_utilisateurs
		WHERE agt_j_user_area.id_area = agt_service.id
		AND agt_j_user_area.login = ".$id_user."
		ORDER BY nom, service_name";*/
	
	$sql="SELECT agt_j_user_area.login AS login, agt_j_user_area.id_area AS id_area, agt_service.service_name AS service_name
		FROM agt_j_user_area, agt_service
		WHERE agt_j_user_area.id_area = agt_service.id
		AND agt_j_user_area.login = '".$id_user."'
		ORDER BY service_name";
	
	$res=$Aghate->select($sql);
	
	for ($i=0 ;$i < count($res) ;$i++)
	{
		$ListeServicesAttribues.=	"<option value='".$res[$i]['id_area']."' id_user='".$res[$i]['login']."'>".$res[$i]['service_name']."</option>";
	}

}
  
 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="body_left-type" body_left="text/html; charset=UTF-8" />
		<title>Aghate Gestion Services</title>
   	 	<script src="./commun/js/jquery-1.10.2.js"></script>		
		<style type="text/css" title="currentStyle">
			.container {/* encadre le tableau */
				width:550px ; /* largeur du tableau */
				height:240px ; /* hauteur minimale du tableau */
				margin:0 ; padding:0 ; /* marges int?eures et ext?eures du tableau */
				border:1px solid black ;/* bordure du tableau */
				background-color:#E7E4E4;
				text-align: center;
				position: fixed;
				left: 250;
			
			}
			
			.left { /* d?nition de la colonne gauche */
				float:left ; /* flotte ?auche */
				width:300px ; /* largeur : 24% de .container */
				overflow:auto;
				height:100% ; /* hauteur : 100% du .container */
				margin:2px ; padding:0;
			}  
			
			.middle {/* d?nition de la colonne centre */
				float:left ; /* flotte ?auche */
				width:25px ; /* largeur : 50% de .container */
				height:100% ; /* hauteur : 100% du .container */
				margin:0;padding:0 ; /* marges */
			} 
			
			.right { /* colonne droite */
				float:left ; /* flotte ?auche */
				width:300px ; /* largeur : 23% de .container */
				height:100% ; /* hauteur : 100% du .container */
				overflow:auto;
				margin:0 ; padding-left:20px;
			}

		</style>
		<script>
		function RemoveItem()
		{
			if($("#target").val() )
			{
				var id_user = $('option:selected', "#target").attr('id_user');
				$("#target_user_id").val(id_user);				
				$("#source_service").val($("#sources").val());
				$("#target_service").val($("#target").val());
				$("#action").val("DELITEM");
				$('form#frmservices').submit();				
			}else
			  alert("Veuillez selectionner un service pour envoyer");
		}
		
		function AddItem()
		{
			if($("#sources").val())
			{
				var id_user = $('option:selected', "#target").attr('id_user');
				$("#target_user_id").val(id_user);				
				$("#source_service").val($("#sources").val());
				$("#target_service").val($("#target").val());
				
				  /* Recupere le groupe de radio bouton */
				  var radiosGrp = document.getElementsByName("droit");
				  /* Pacours tous les boutons du groupe pour trouver celui qui est "checked" */
				  for(var radio in radiosGrp)
				  {
					if(radiosGrp[radio].checked)
					{
					  var res = radiosGrp[radio].value;
					}
				  }
				
				$("#target_droit").val(res);
				
				$("#action").val("ADDITEM");
				$('form#frmservices').submit();								
			}
			else
			  alert("Veuillez selectionner un service pour envoyer");
			
		}		
		</script>
	</head>	
 <form method="get" id='frmservices'>
	<input type="hidden" name="target_user_id" id="target_user_id">
	<input type="hidden" name="source_service" id="source_service">
	<input type="hidden" name="target_service" id="target_service">
	<input type="hidden" name="target_droit" id="target_droit">
	<input type="hidden" name="action" id="action"> 
	
	<table border=0 cellpadding=2 cellspacing=2 align='center'  >
                
    <tr> 
      <th  align='left' >Utilisateurs</th>
    </tr>
		<tr> 
      <td colspan='2'>
		 
			<div class="left">	
				<h5><?php Print $html->InputSelect($ListeUtilisateurs ,'id_user',$id_user,200,"onchange='javascript:submit();'"); ?></h5>
				<select name="sources[]" id="sources" size="30" width="300px" style="width: 300px;overflow:auto;"  >
					<?php print $ListeServices;?>
				</select>
				<?php /*echo "Nombre des lits :".$nbrlits;*/?>
			</div>
			
			<div class="middle"><br><br>
				<br>
				<form>
				<INPUT type= "radio" class="test_droit" name="droit" value="r" checked> Lecture seule
				<INPUT type= "radio" class="test_droit" name="droit" value="w"> Ecriture
				</form>				
				<br>
				<a href="#" onclick="AddItem( )"><img src="../commun/images/right_arrow.jpg" border='0' width='30'></a>
				<br><br><br>
				<a href="#"  onclick="RemoveItem()"><img src="../commun/images/left_arrow.jpg" border='0' width='30'></a>
			  <p></p>
			</div>
			
			<div class="right">
				<h5>Lits dans l'autre service </h5>
				<select name="target[]"  id="target" size="30" width="300px" style="width: 300px;overflow:auto;"  >
			 	<?php print $ListeServicesAttribues; ?>
				</select>
			</div>
			
		</div> <!-- fin container -->

</td>
    </tr>
		<tr> 

   </table>
  
   	</form>	
