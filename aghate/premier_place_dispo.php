<?php  
/* 
=======================================================================================
Recherche premier place disponible 
recherche dans Gilda ou base local

=======================================================================================
*/
//inclusion des objets
header('Content-Type: text/html; charset=utf-8');
include "./config/config.php";
include "./config/config.inc.php";

include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include"./commun/include/settings.inc.php";
include("./commun/include/session.inc.php"); //#Fonction relative à la session

include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/CommonFonctions.php";
include "./commun/include/ClassHtml.php";
include "./commun/include/language.inc.php"; // Paramètres langage

// init objets
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Html 	= new Html();
$db		= new MySQL();
$funtions= new CommonFunctions(true);



$grr_script_name = "premier_place_dispo.php";
$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);


// Affichage de la colonne de gauche
$back = '';
if (isset($_GET["action_moderate"])) {
    // on modère
    moderate_entry_do($id,$_GET["moderate"],$_GET["description"]);
};

#
$date_deb=$_GET["date_deb"];
$date_fin=$_GET["date_fin"];
$nom=$_GET["nom"];
$nip=$_GET["nip"];

$duree_cherche= isset($_GET['duree_cherche']) ? $_GET['duree_cherche'] : "120";
if (($duree_cherche*1)==0)$duree_cherche="120";

$date_deb= isset($_GET['date_deb']) ? $_GET['date_deb'] : date("d/m/Y");
if (strlen($date_deb)< 8) $date_deb=date("d/m/Y");

$nbr_jours= isset($_GET['nbr_jours']) ? $_GET['nbr_jours'] : "3";
if (strlen($nbr_jours)< 1) $nbr_jours=3;




function GetFrench_day($jour){
	switch ($jour)
	{
		case 1:
			return "Lundi";
			break;
		case 2:
			return "Mardi";
			break;
		case 3:
			return "Mercredi";
			break;
		case 4:
			return "Jeudi";
			break;
		case 5:
			return "Vendredi";
			break;
		case 6:
			return "Samedi";
			break;
		case 6:
			return "Dimanche";
			break;
	}
}

function duree($fin,$deb){
	if (date("I")==1)
		return $fin-$deb;
	else
		return ($fin-$deb);
		//return (($fin-$deb)-(3600));
	}
	
function ChkEte($dt){
		if (date("I")==1) // l'heure d'été
			return ($dt-3600);
		else
			return ($dt);
	}	
	
//selection dans le services 
$session_user = $_SESSION['login'];
$session_statut = $_SESSION['statut'];
$list_areas =get_areas_allowed($session_user,$session_statut);

//----------------------------------------------------
// Preparation Service liste
//----------------------------------------------------
$Services=$Aghate->GetAllArea();

for($i = 0; $i < count($Services); $i++)
{
	$ListeServices[] = $Services[$i]['id']."|".$Services[$i]['service_name'];
}
$area_select=$Html->InputSelect($ListeServices,'area','100','100');
 

//----------------------------------------------------
// Preparation Protocoles  list
//----------------------------------------------------
$sql = "SELECT protocole from agt_protocole order by protocole";
$Protocoles = $db->Select($sql);
    
for($i = 0; $i < count($Protocoles); $i++)
{
	$ListeProtocoles[] = $Protocoles[$i]['protocole']."|".$Protocoles[$i]['protocole'];
}

	
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Intranet MSI <?php print $PageHeader?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=8" > 
  <meta http-equiv="X-UA-Compatible" content="IE=edge">  
	<link rel="shortcut icon" type="image/x-icon" href="../commun/images/favicon.ico" />      
  <meta name="description" content="">
  <meta name="author" content="mohanraju">
<script src="./commun/js/jquery-1.9.1.js"></script>
<script src="./commun/js/jquery-ui.js"></script>
<script language="javascript" type="text/javascript" src="./commun/js/JCalender.js"></script>
<script language="javascript" type="text/javascript" src="./commun/js/Communfonctions.js"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="./commun/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="./commun/js/DT_bootstrap.js"></script>
<script>
	$(function() {
		$( "#date_deb" ).datepicker();
		$( "#date_fin" ).datepicker();
	});

	function change_val() 
	{
		document.getElementById('view').value="NON";	
		document.reports.submit();
	}
	
	function get_link(url) {
		if(confirm("Voulez vous programmer ce jour ?")){
			window.opener.location.href = url;
		  	if (window.opener.progressWindow){
				window.opener.progressWindow.close()
			}
			window.close();
		}else{
		return false;
	}
}
 
</script>
<!-- DEBUT de parite utulise pour DataTable Bootstrap -->
<link rel="stylesheet" type="text/css" href="./commun/style/bootstrap.css">
<link rel="stylesheet" href="./commun/style/redmond/jquery-ui-1.10.3.custom.css">
<link rel="stylesheet" type="text/css" href="./commun/style/bootstrap_extra.css">


<style>
	#DivDataTable{
	width :1200px; 
	margin:0px auto; 
	text-align:center;
	} 	
	th{
	text-align:center;
	}
</style>

<body>
<form>
	<table  border="0" align="center" cellpadding="1" cellspacing="1">
  	<tr>
    	<td height="36" colspan="4" align="center"><h3> Recherche premi&egrave;re place disponible</h3></td>
  	</tr>
  	<tr>
    	<td ><label>Protocole</label></td>
			<td>	<?php Print $Html->InputSelect($ListeProtocoles,'protocole',$protocole,'200');?></td>
    	<td ><label for="dat">Durée de traitement </label></td>
			<td><label><?php Print $Html->InputTextBox('duree_cherche',$duree_cherche,3,10);?>En minutes</<label></td>
  	</tr>
  	<tr>
    	<td ><label>Service </label></td>
			<td>	<?php Print $Html->InputSelect($ListeServices,'services',$services,'200');?>	</td> 
    	<td ><label>Recherche à parir de </label></td>
			<td><?php Print $Html->InputTextBox('date_deb',$date_deb,10,10);?></td>
  	</tr>
  	<tr>
    	<td colspan="2" align='center'> 
				<input type="submit" name="Rechercher" id="Rechercher" value="Rechercher" class="btn btn-success"/> 	
    	</td>

    	<td ><label for="dat">Nombre de jours</label></td>	
    	<td><?php Print $Html->InputTextBox('nbr_jours',$nbr_jours,2,10);?></td>
			
   	</tr>
</table>
</form>
<?php
	
//===========================================================================================================
// notre stuff de recherche starts here
//==========================================================================================================  

	// si sucun service selectionné on select tous les service de utilisateur
	if($services == "'0'"){$services=$list_areas;}

	if(strlen($services)< 1 ){
		echo "<h4>Veuillez selectionnez un service </h4>";
		exit;
		}
	// convert munite en sesondes
	$duree_en_sec=$duree_cherche * 60;// converte en seconds

   list($d,$m,$y)=explode("/",$date_deb);
   $input_start=mktime(8, 0, 0, $m, $d-1,$y);	   
   $input_end=mktime(19, 0, 0, $m, $d-1,$y);	   
   

   // get all lits dans le AREA
		$sql_main=" SELECT agt_room.id, agt_room.room_name, agt_room.service_id, service_name
					FROM agt_room, agt_service
					WHERE agt_service.id = agt_room.service_id
					AND service_id in ($services)  and agt_room.room_name !='Panier' ";

		$res_main=$db->select($sql_main);
		$cpt=0;
		$nbr_jours= $nbr_jours + 1;	// 3 jours max (verify jour férié et congée);
		
	  // Boucle sur nbr des jours  
		for ($jour=1;$jour < $nbr_jours;$jour++)
		{
			$compteur_day = ($jour*24*3600); 
	   	$start = $input_start + $compteur_day; 
	   	$end   = $input_end   + $compteur_day; 
		
			// convert to timestrap
			$starttime_midnight = mktime(0, 0, 0, date("m",$start), date("d",$start),date("Y",$start));	   
			$endtime_midnight = mktime(0, 0, 0, date("m",$end), date("d",$end),date("Y",$end));	   	   

			// vérify hors péroide 
			if (resa_est_hors_reservation($starttime_midnight , $endtime_midnight )) 
			{
				$nbr_jours=$nbr_jours+1;
				continue; 	
			}
		//---------------------------------------------------
		// Boucle sur chaque LIT pout tester les palace libre
		//---------------------------------------------------			
		for ($m=0;$m< count($res_main);$m++){
			$LITID=$res_main[$m]['id'];
			$sql_dtl="SELECT start_time,end_time,type
						FROM agt_loc 
						WHERE room_id = '".$res_main[$m]['id']."' 
			 			AND start_time < $end AND end_time > $start
			 			ORDER BY start_time ";
			$res_dtl=$db->select($sql_dtl);
			$last_deb=$start;		
			for ($d=0;$d < count($res_dtl);$d++){
		  		$rdv_deb=$res_dtl[$d]['start_time'];
		  		$rdv_fin=$res_dtl[$d]['end_time'];
		  		//check  libre
		  		$duree_before=duree($rdv_deb ,$last_deb);
				$libre[$cpt]['DATE']=	$start;		  
				$libre[$cpt]['LIT']=	$res_main[$m]['room_name'];	
				$libre[$cpt]['room']=	$res_main[$m]['id'];				
				$libre[$cpt]['area']=	$res_main[$m]['service_id'];		
				$libre[$cpt]['service']=	$res_main[$m]['service_name'];		
											
				if ($duree_en_sec <= $duree_before){
					$libre[$cpt]['AM'].=	" "	.date("H:i",ChkEte($last_deb)). " à ". date("H:i",ChkEte($rdv_deb))."\n";
				}	
				$last_deb=$rdv_fin;					
	
			}
			if ($d!=0){
				// check juqu'au fin de journée aprés le last RDV 
				$rdv_deb=$end;
		  		$duree_before=duree($rdv_deb ,$last_deb);
				$libre[$cpt]['DATE']=	$start;		  
				$libre[$cpt]['LIT']=	$res_main[$m]['room_name'];	
				$libre[$cpt]['room']=	$res_main[$m]['id'];				
				$libre[$cpt]['area']=	$res_main[$m]['service_id'];				
				$libre[$cpt]['service']=	$res_main[$m]['service_name'];							
				if ($duree_en_sec <= $duree_before){
					$libre[$cpt]['AM'].=	" "	.date("H:i",ChkEte($last_deb)). " à ". date("H:i",ChkEte($rdv_deb))."\n ";
				}	
			
			}else{	
				// si aucune RDV dans le journée on display Libre complet 
				$libre[$cpt]['DATE']=	$start;		  					
				$libre[$cpt]['LIT']=	$res_main[$m]['room_name']	;
				$libre[$cpt]['room']=	$res_main[$m]['id'];				
				$libre[$cpt]['area']=	$res_main[$m]['service_id'];	
				$libre[$cpt]['service']=	$res_main[$m]['service_name'];										
				$libre[$cpt]['AM']=	"08:00 à 19:00";
	
			}
				$cpt++;		
				if ($cpt >100) break;
		}
   
	}// fin boucle nbr jours

	?>


<table width="600" border="1" cellspacing="1" cellpadding="1" align="center">
  <tr>
    <th align="center"> Date</th>  
    <th align="center"> [Service] : Lit</th>  
    <th align="center">Disponibilité</th>    

  </tr>
  <?php 
	$last_lit="";
	$_date="";
	$cmpt=0;
	for ($i = 0; $i < $cpt; $i++) 
	{
		if(($cmpt%2)==0)$bgcolor="";else $bgcolor="#EED7D2";
		$_day=date("d",$libre[$i]['DATE']);
		$_mon=date("m",$libre[$i]['DATE']);
		$_year=date("Y",$libre[$i]['DATE']);	  			  	
		$time_t_stripped="heur=".substr($libre[$i]['AM'],1,2)."&minute=".substr($libre[$i]['AM'],4,2);
			

		
		if(strlen($libre[$i]['AM']) > 0)
		{ 
		if ($_date<>$libre[$i]['DATE']){
			$_date=$libre[$i]['DATE'];
			$dt_affiche=GetFrench_day(date("N",$libre[$i]['DATE'])). " ".date("d/m/Y",$libre[$i]['DATE']);
		}else{
			$dt_affiche="&nbsp;";
		}
		$link="edit_entry.php?area=".$libre[$i]['area']."&room=".$libre[$i]['room']."&period=$time_t_stripped&year=$_year&month=$_mon&day=$_day&page=day";  			
    $link="get_link('$link')";										
		$link_print="<a href=\"#\" onclick=\"".$link."\">".str_replace(":","H",$libre[$i]['AM'])."</a>";      
		//$link_print="<a href=".$link.">".$libre[$i]['AM']."</a>";
		// print only dispo jours					
  	?>
  		<tr  bgcolor="<?php print $bgcolor?>">
		 		<td><?php print $dt_affiche; ?></td>		  
    		<td><?php print "[".$libre[$i]['service']."] : ".$libre[$i]['LIT']?></td>	    	  
    		<td><?php print $link_print?></td>	 
    	</tr>
		<?php 
		$cmpt++;
		}
	}

    ?>
</table>

</body>
</html>
