<?php
session_start();
include("./config/config.php");
header("Content-type:text/html; charset= utf-8");
include("./commun/include/functions.inc.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");
include("../commun/layout/header.php");

$com=new CommonFunctions(true);
$Html=new Html();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Mysql = new MySql();
$functions= new CommonFunctions();

$res=$Aghate->GetPatParLit ($PanierId, $Cdate,$HeureDeb,$HeureFin) ;
$ListeLits=$Aghate->GetAllRooms ($ServiceId);
?>
<script type="text/javascript" src="./commun/js/fonctions_aghate.js" charset="utf-8"></script>
<SCRIPT type="text/javascript">

function PanierVersLit(resa_id)
 {

	// get selected lit_id
	var SelectedLit=$("#IdDispo_"+resa_id+" option:selected").val();
	var SelectedLitNom=$("#IdDispo_"+resa_id+" option:selected").text();


	//check id et lit sont bonne
 	if (resa_id.length < 1 && SelectedLit.lenght < 1)
 	{
 		alert('probleme sur le selection lit');
 		return false
 	}
	// confirm le choix d'user
	if(confirm("Voulez vous programmer ce jour ?")){
		lnk="./commun/ajax/ajax_aghate_update_room_from_panier.php";
		vars="id_resa="+resa_id+"&new_room="+SelectedLit;		
		alert(lnk+vars);
		res =LanceAjax(lnk,vars)
		res=res.split("|")
		if(res.lenght < 1)
		{
			alert(res)
			return false	
		}
		if (res[1]=="OK")
		{
			alert("Lit Basculé..");
			document.location.reload(true);
 			window.opener.location.reload();			
		}else{			
			alert(res[1])
		}
	}else{
		window.location.reload()
		return false;
	}	
}

</script>
</head>
<body style="">
	<div id="PanierList">
		<table class="table">
			<thead>
				<tr>
					<th>Patient</th>
					<th>Date Debut</th>
					<th>Date fin</th>
					<th>Reservé par</th>
					<th>Lit disponible</th>
				</tr>
			</thead>
			<tbody>	
<?php
for ($c=0; $c < count($res);$c++){
	$pat="NIP:".$res[$c]['noip']."<br> ".$res[$c]['nom'] ." ".$res[$c]['prenom'] ."<br>  Ne(é) ".$functions->Mysql2Normal($res[$c]['ddn'])." (".$res[$c]['sex'].")<br> NDA:".$res[$c]['nda'];
	$nb_libre=0;
	$list_sel="";
	// Boucle sur chaque LIT pout tester les dispo
	for($l=0; $l < count($ListeLits);$l++)
	{
		$chkdispo=$Aghate->CheckRoomDispo($ListeLits[$l]['id'],$res[$c]['start_time'],$res[$c]['end_time'],0,$res[$c]['id']);
		//echo "<br>".$ListeLits[$l]['id']." ".$chkdispo;
		if(strlen($chkdispo) < 1){
			$ListDispo[]=$ListeLits[$l];
			$list_sel.="<option value='".$ListeLits[$l]['id']."'>".$ListeLits[$l]['room_name']."</option>";
			$nb_libre++;
		}
	}

	echo "<tr>
					<td>" .$pat." </td>
					<td>".date('d/m/Y H:i',$res[$c]['start_time'])."</td>
					<td>".date('d/m/Y H:i',$res[$c]['end_time'])."</td>
					<td>".$res[$c]['create_by']."</td>
					<td>
							<select id='IdDispo_".$res[$c]['id']."' class='form-control' onchange=\"PanierVersLit('".$res[$c]['id']."')\">
							<option id='retour' value='0' >Nombre de lits libres: ".$nb_libre."</option>
							".$list_sel."
							</select>
         </td>
         </tr>";
	
}
echo "</tbody></table></div>";

?>					
</body>
</html>
