<?php 
/* Projet Aghate
 * Gestion medecin
 * 
 * 
 * 
 */ 
include "./commun/include/admin.inc.php";
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");


$Cfunctions=new CommonFunctions(true);
$Mysql = new MySQL();
$Html=new Html();
$Aghate = new Aghate();
$Aghate->SetTable="agt_loc";

$grr_script_name = "medecin.php";
$back = '';


if ((authGetUserLevel(getUserName(),-1) < 3) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

	
# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";
?>

	

<link href="../commun/styles/bootstrap.css" rel="stylesheet" media="screen">
<link href="../commun/styles/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">  

<script src="../commun/js/jquery.js"></script>
<script src="../commun/js/jquery_ui.js"></script>
<script src="../commun/js/jquery.dataTables.js" type="text/javascript" language="javascript"></script>

<style>
	div#body_left {
    float: left;
    width: 600px;
	min-height:150px;			    
	margin-left:50px;			    					
}
</style>
 		
 


<script type="text/javascript" charset="ISO-8859-1">
$(document).ready(function() 
{
	var oTable =$('#MedecinList').dataTable( {
	"sScrollY": 300,
	"bJQueryUI": true,
	"iDisplayLength": 10,	
	"bPaginate": false,							
	"oLanguage": {"sUrl": "../commun/js/datatable.french.txt"},
	});

});
function popup_medecin(id_medecin){
	service_id=$("#service_id").val();
	OpenPopup("./medecin_add.php?id_medecin="+id_medecin+"&service_id="+service_id)
} 
function OpenPopup(url) {
    mywindow1=window.open(url,'myname','resizable=yes,width=550,height=250,left=250,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
function popUpClosed() {
    window.location.reload();
}
</script>
	
<script type="text/javascript" src="./commun/js/fonctions_aghate.js"></script>
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<div align="center" style="overflow:auto;width:600px;" >
	<h2>Medecins</h2>
</div>
<?php 

//perpare list de service autorisé pour cette utilisateur
$_Services=$Aghate->GetAllServiceAuthoriser($_SESSION['login'],$_SESSION['statut']);

for($s=0;$s < count($_Services) ; $s++)
{
	$LstServices[]=$_Services[$s]['id']."|".$_Services[$s]['service_name'];
	if(strlen($service_id) <1) $service_id =$_Services[$s]['id'];
}

 
$sql="select * from agt_medecin where service_id='".$service_id."' order by etat desc, nom,prenom";
$results=$Mysql->select($sql);
 
?>
<form name="UserInfo" id="UserInfo" method="POST" action="">	
	<div id="body_left">
		 <?php 
			Print "<b>Services :</b>".$Html->InputSelect($LstServices,'service_id',$service_id,250);
		 ?>
 	 		<input type="button" name="sync" value="Actualiser" onclick="javascript:submit();" 	class="btn btn-info" > 
			<input type="button" name="ajout" value="Ajouter" onclick="popup_medecin('NEW')" 	class="btn btn-success" >  	 		
			<table cellpadding="0" cellspacing="0" border="0" class="display" id="MedecinList" >
				<thead>
					<tr>
						<th>Medecin</th>
						<th>Tél</th>
						<th>Specialité</th>
						<th>Etat</th>						
						<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?Php   
				for($i=0; $i < count($results);$i++) {
					$etat=($results[$i]['actif']=='1')?'Actif':'Inactif';
					print 
					"<tr>
						<td>".$results[$i]['titre']." ".$results[$i]['nom']." ".$results[$i]['prenom']."</td>
						<td>".$results[$i]['tel']."</td>
						<td>".$results[$i]['specialite']."</td>
						<td>".$etat."</td>
						<td><a href='#' onclick='popup_medecin(".$results[$i]['id_medecin'].")'><img src='./commun/images/edit.jpg' border='0' height='15px'></a></td>
					</tr>	
					";
				}
				?> 
				<//tbody>							
			</table>
	</div>
</form>	
 
	


	
	
  
  </body>
</html>


		
 


