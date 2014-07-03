<?php
require("./config/config.php");
require("./commun/include/CommonFonctions.php");
require("./commun/include/ClassMysql.php");
require("./commun/include/ClassAghate.php");
include("./commun/include/ClassHtml.php");

// init les objets
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$html= new Html($db);

//================================================================= 
// gestion action 
// add item de Gauche vers Droite ==>
//=================================================================
if($action=="ADDITEM")
{
	$err=false;
	if(strlen($service_id)<1){
		echo "<br>Gauche :: invalide service ID";
		$err=true;
	}

	if(strlen($target_service_id)<1){
		echo "<br>Droite :: invalide serviceID";
		$err=true;
	}
	
	if(strlen($source_room)<1){
		echo "<br>Gauche:: Aucun lit selectionn?ans le service";
		$err=true;
	}
	if(strlen($target_room)< 1){
		echo "<br>Droite ::Aucun lit selectionn?ans list tous les  Lits";
		$err=true;
	}
	
	if(!$err)
	{
		$update_sql="UPDATE agt_room set service_id='".$target_service_id."' WHERE id='".$source_room."'"; 
		$Aghate->update($update_sql);
	}	

}
 
// add item de droite vers Gauche <==
if($action=="DELITEM")
{

	$err=false;
	if(strlen($service_id) < 1){
		echo "<br>Gauche :: invalide service ID";
		$err=true;
	}

	if(strlen($target_service_id)<1){
		echo "<br>Droite :: invalide serviceID";
		$err=true;
	}
	
	if(strlen($source_room)<1){
		echo "<br>Gauche:: Aucun lit selectionn?ans le service";
		$err=true;
	}
	if(strlen($target_room)< 1){
		echo "<br>Droite ::Aucun lit selectionn?ans list tous les  Lits";
		$err=true;
	}
	
	if(!$err)
	{
		$update_sql="UPDATE agt_room set service_id='".$service_id."' WHERE id='".$target_room."'"; 
		$Aghate->update($update_sql);
	}	

}
 
//================================================================= 
// recupare les service 
//=================================================================
//$res=$Aghate->GetAllArea();
$res=$Aghate->select("select * from agt_service order by service_name");
for ($i=0 ;$i < count($res) ;$i++)
{
	$ListeServices[] =	$res[$i]['id']."|".$res[$i]['service_name'];
}

//================================================================= 
// Recupare lits du  services 
//=================================================================
if (strlen($service_id) > 0){

	$res=$Aghate->GetAllRooms($service_id);
	$nbrlits=count($res);
	for ($i=0 ;$i < count($res) ;$i++)
	{
		$ListeLitsDansService.=	"<option value='".$res[$i]['id']."' >".$res[$i]['room_name']."</option>";
	}

}
 
//================================================================= 
// Recupare lits du  da l'autre services 
//=================================================================
if (strlen($service_id) > 0){
	$sql="select service_name,agt_service.id as service_id,
					room_name,agt_room.id as room_id
					from agt_room
					left join agt_service on agt_room.service_id=agt_service.id 
					where room_name !='Panier'
					and  agt_service.id not in ('".$service_id."') order by room_name,service_name";
	
	$res=$Aghate->select($sql);
	
	for ($i=0 ;$i < count($res) ;$i++)
	{
		$ListeLitsHorsService.=	"<option value='".$res[$i]['room_id']."' id_service='".$res[$i]['service_id']."'>".$res[$i]['room_name']." =>".$res[$i]['service_name']."</option>";
	}

}
  
 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="body_left-type" body_left="text/html; charset=$charset" />
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
				width:250px ; /* largeur : 24% de .container */
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
				width:250px ; /* largeur : 23% de .container */
				height:100% ; /* hauteur : 100% du .container */
				margin:0 ; padding-left:20px;
			}

		</style>
		<script>
		function RemoveItem()
		{
			if($("#sources").val() )
			{
				var id_service = $('option:selected', "#target").attr('id_service');
				$("#target_service_id").val(id_service);				
				$("#source_room").val($("#sources").val());
				$("#target_room").val($("#target").val());
				$("#action").val("DELITEM");
				$('form#frmservices').submit();				
			}else
			  alert("Veuillez	 selectionner un lit pour envoyer");
		}
		function AddItem()
		{
			if($("#target").val())
			{
				var id_service = $('option:selected', "#target").attr('id_service');
				$("#target_service_id").val(id_service);				
				$("#source_room").val($("#sources").val());
				$("#target_room").val($("#target").val());

				$("#action").val("ADDITEM");
				$('form#frmservices').submit();								
			}
			else
			  alert("Veuillez	 selectionner un lit pour envoyer");
			
		}		
		</script>
	</head>	
 <form method="get" id='frmservices'>
	<input type="hidden" name="target_service_id" id="target_service_id">
	<input type="hidden" name="source_room" id="source_room">
	<input type="hidden" name="target_room" id="target_room">
	<input type="hidden" name="action" id="action"> 
	
	<table border=0 cellpadding=2 cellspacing=2 align='center'  >
                
    <tr> 
      <th  align='left' >Services</th>
      <td>rrr</td>
    </tr>
		<tr> 
      <td colspan='2'>
		 
			<div class="left">	
				<h5><?php Print $html->InputSelect($ListeServices ,'service_id',$service_id,200,"onchange='javascript:submit();'"); ?></h5>
				<select name="sources[]" id="sources" size="30"   width="250px" style="width: 250px"  >
					<?php print $ListeLitsDansService;?>
				</select>
				<?php echo "Nombre des lits :".$nbrlits;?>
			</div>
			
			<div class="middle"><br><br>
				<br>
				<a href="#" onclick="AddItem( )"><img src="../commun/images/right_arrow.jpg" border='0' width='30'></a>
				<br><br><br>
				<a href="#"  onclick="RemoveItem()"><img src="../commun/images/left_arrow.jpg" border='0' width='30'></a>
			  <p></p>
			</div>
			
			<div class="right">
				<h5>Lits dans l'autre service </h5>
				<select name="target[]"  id="target" size="30"   width="250px" style="width: 250px"  >
			 	<?php print $ListeLitsHorsService;?>
				</select>
			</div>
			
		</div> <!-- fin container -->

</td>
    </tr>
		<tr> 

   </table>
  
   	</form>	
