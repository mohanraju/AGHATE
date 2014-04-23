<?php  include "./commun/include/admin.inc.php";
	include ("./commun/include/CustomSql.inc.php");
	$db = New CustomSQL($DBName);

$grr_script_name = "titres.php";
$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((authGetUserLevel(getUserName(),-1) < 3) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    //showAccessDenied($day, $month, $year, $area,$back);

}
# print the page header
simple_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche



$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if (isset($_GET["action_moderate"])) {
    // on modère
    moderate_entry_do($id,$_GET["moderate"],$_GET["description"]);
};


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
	switch ($jour){
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
	
?>
<style type="text/css">
<!--
.Style1 {
	color: #0000FF;
	font-weight: bold;
}
.Style2 {
	font-size: x-small;
	font-weight: bold;
	font-style: italic;
	color: #FF0000;
}
-->
</style>

<style type="text/css">
<!--

 table {
 width:90%;
 border-top:1px solid #e5eff8;
 border-right:1px solid #e5eff8;
 margin:1em auto;
 border-collapse:collapse;
 }
 td {
 border-bottom:1px solid #e5eff8;
 border-left:1px solid #e5eff8;
 padding:.1em 1em;

 }

-->
</style>




<form>

<table width="500" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr>
    <td height="36" colspan="6" align="center"><span class="Style1">R&eacute;cherche premi&egrave;re place disponible</span></td>
  </tr>

  <tr>
    <td align="right">Protocole :
      <input name="Protocole" type="text" id="Protocole" maxlength="10" value="<?php print $Protocole?>"/></td> 
    <td align="right">Dur&eacute;e :
      <input name="duree_cherche" type="text" id="duree_cherche" value="<?php print $duree_cherche?>" size="10" maxlength="3" />
      <span class="Style2">en munites</span></td>
    <td align="right">Date debut de recherche
      <input name="date_deb" type="text" id="date_deb" value="<?php print $date_deb?>" size="10" maxlength="10"/></td>
    <td align="right">Nbr
       
      Jours 
      <input name="nbr_jours" type="text" id="nbr_jours" value="<?php print $nbr_jours?>" size="10" maxlength="2"/></td>
    <td align="right"><input type="submit" name="Rechercher" id="button" value="Rechercher" /></td>
    <td>&nbsp;</td>
  </tr>
</table>
</form>
<?php
	
//===========================================================================================================
// notre stuff de recherche starts here
//==========================================================================================================  
//recherce les patients dans le même service 
	$session_user = $_SESSION['login'];
   $session_statut = $_SESSION['statut'];
   $list_areas =get_areas_allowed($session_user,$session_statut);
   
	$duree_en_sec=$duree_cherche * 60;// converte en seconds

   list($d,$m,$y)=explode("/",$date_deb);
   $input_start=mktime(8, 0, 0, $m, $d-1,$y);	   
   $input_end=mktime(19, 0, 0, $m, $d-1,$y);	   
   

   //------------------------------
   // get all lits dans le AREA
   //------------------------------
	$sql_main="Select agt_room.id, agt_room.room_name from agt_room where service_id in ('5','6')" ;
	$res_main=$db->select($sql_main);
	$cpt=0;
	$nbr_jours= $nbr_jours + 1;	// 3 jours max (verify jour férié et congée);
	   //---------------------------------------------------
	   // Boucle sur nbr des jours  
	   //---------------------------------------------------	
	   
	for ($jour=1;$jour < $nbr_jours;$jour++){
	   $compteur_day = ($jour*24*3600); 
	   $start = $input_start + $compteur_day; 
	   $end   = $input_end   + $compteur_day; 
		
		// convert to timestrap
		$starttime_midnight = mktime(0, 0, 0, date("m",$start), date("d",$start),date("Y",$start));	   
		$endtime_midnight = mktime(0, 0, 0, date("m",$end), date("d",$end),date("Y",$end));	   	   
		// vérify hors péroide 
		if (resa_est_hors_reservation($starttime_midnight , $endtime_midnight )) {
			$nbr_jours=$nbr_jours+1;
			continue; 	
		}
		//---------------------------------------------------
		// Boucle sur chaque LIT pout tester les palace libre
		//---------------------------------------------------			
		for ($m=0;$m< count($res_main);$m++){
			$LITID=$res_main[$m]['id'];
			$LITS[$m]=$LITID;
			$LITNAME[$m]=$res_main[$m]['room_name'];
			
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
				$libre[$cpt][$LITID]['DATE']=	$start;		  
				$libre[$cpt][$LITID]['LIT']=	$res_main[$m]['room_name'];	
				if ($duree_en_sec <= $duree_before){
					$libre[$cpt][$LITID]['AM'].=	" ["	.date("H:i",ChkEte($last_deb)). " à ". date("H:i",ChkEte($rdv_deb))."]\n";
				}	
				$last_deb=$rdv_fin;					
	
			}
			if ($d!=0){
				// check juqu'au fin de journée aprés le last RDV 
				$rdv_deb=$end;
		  		$duree_before=duree($rdv_deb ,$last_deb);
				$libre[$cpt][$LITID]['DATE']=	$start;		  
				$libre[$cpt][$LITID]['LIT']=	$res_main[$m]['room_name'];	
				if ($duree_en_sec <= $duree_before){
					$libre[$cpt][$LITID]['AM'].=	" ["	.date("H:i",ChkEte($last_deb)). " à ". date("H:i",ChkEte($rdv_deb))."]\n ";
				}	
			
			}else{	
				// si aucune RDV dans le journée on display Libre complet 
				$libre[$cpt][$LITID]['DATE']=	$start;		  					
				$libre[$cpt][$LITID]['LIT']=	$res_main[$m]['room_name']	;
				$libre[$cpt][$LITID]['AM']=	"[08:00 à 19:00]";
	
			}
				$cpt++;		
				if ($cpt >100) break;
		}
   
	}// fin boucle nbr jours

	?>


<table width="500" border="1" cellspacing="1" cellpadding="1" align="center">
  <tr>
    <th align="center"> Date</th>  
    <th align="center"> Lit</th>  
	<?php     
		for($l=0;$l < count($LITS);$l++){
	   	print "<td>".$LITNAME[$l] ."</TD>"; 	
		}		 
?>			    
    

  </tr>
 
  
  <?php 

	$last_lit="";
		$_date="";
		for ($i = 0; $i < $cpt; $i++) {
	  		if(($i%2)==0)$bgcolor="";else $bgcolor="#EED7D2";
  			if ($_date<>$libre[$i]['DATE']){
  				$_date=$libre[$i]['DATE'];
  				$dt_affiche=GetFrench_day(date("N",$libre[$i]['DATE'])). " ".date("d/m/Y",$libre[$i]['DATE']);
  			}else{
  				$dt_affiche="&nbsp;";
  			}
	    	?>
		  <tr  bgcolor="<?php print $bgcolor?>">
  			 <td><?php print $dt_affiche; ?></td>		  
		    <td><?php print $libre[$i]['LIT']?></td>	   
			<?php
			for($l=0;$l < count($LITS);$l++){
					     print "<td>".$libre[$i][$LITS[$l]]['AM'] ."</TD>"; 	
					     $i++;
				}		 
		
			?>	    	  


	      </tr>
	  <?php 
	}

    ?>
</table>

<?php  
  
include_once("./commun/include/trailer.inc.php");
?>
