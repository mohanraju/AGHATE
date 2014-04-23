<?php 
include "./commun/include/admin.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
ini_set("display_errors",1);
$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";


$agt_script_name = "titres.php";
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

if (isset($_POST["action_moderate"])) {
    // on modère
    moderate_loc_do($id,$_POST["moderate"],$_POST["description"]);
};

$date_deb = isset($_POST["date_deb"]) ? $_POST["date_deb"] : date("01/01/Y");
$date_fin = isset($_POST["date_fin"]) ? $_POST["date_fin"] : date("31/12/").(date("Y")+2);


$nom=$_POST["nom"];
$nip=$_POST["nip"];

?>
<script language="JavaScript">

function get_link(url) {
  window.opener.location.href = url;
  if (window.opener.progressWindow)
 {
    window.opener.progressWindow.close()
  }
}

function OpenPopupResa(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=850,height=670,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
</script>
<script language="javascript" type="text/javascript" src="./commun/js/JCalender.js"></script>	
<style type="text/css">
<!--
.Style1 {
	color: #0000FF;
	font-weight: bold;
}
-->
</style>
<form method="POST">
<table width="617" border="0" align="center" cellpadding="1" cellspacing="1">
  <tr>
    <td colspan="2" align="center"><span class="Style1">R&eacute;cherche </span></td>
  </tr>
  <tr>
    <td align="right">P&eacute;roide du : </td> 
    <td><input name="date_deb" type="text" id="date_deb" maxlength="10" size="17" value="<?php print $date_deb?>"/> 
	<a href="javascript:PrepareCal('date_deb')"><img src="./commun/images/cal.gif" width="16" height="16" border="0" alt="Calendar"></a>				
    Au : 
      <input name="date_fin" type="text" id="date_fin" maxlength="10"  size="17"  size="15" value="<?php print $date_fin?>" />
	<a href="javascript:PrepareCal('date_fin')"><img src="./commun/images/cal.gif" width="16" height="16" border="0" alt="Calendar"></a>				      
      </td>
  </tr>
  <tr>
    <td align="right">  Recherche par Nom/NIP/NDA : </td>
    <td><input type="text" name="nom" id="nom" value="<?php print $nom?>" /> 
</td>
  </tr>
  <tr>
    <td align="right" valign="middle">Tri Par : </td>
    <td><select name="tri_par" id="select">
        <option value="agt_loc.name">Patient</option>
        <option value="start_time">Date</option>
        <option value="room_name">Service</option>
        <option value="overload_desc">Protocole</option>
      </select>
       <input type="submit" name="button" id="button" value="Afficher" /></td>
  </tr>
</table>
</form>
<?php
	
	$tri_par=$_POST["tri_par"];
	
		$sql_tri_par=" ORDER by  agt_loc.start_time,agt_loc.end_time		";
	
	if ( (strlen($date_deb)==10)){
		list($day,$mon,$year)=explode("/",$date_deb);
		$deb_time=mktime(0,0,0,$mon,$day,$year);
		 $sql_date_deb=" and  start_time >=".$deb_time;
	}
	
	if ((isset($date_fin)) && (strlen($date_fin)==10)) {
		list($day,$mon,$year)=explode("/",$date_fin);
		$fin_time=mktime(24,0,0,$mon,$day,$year);
		
		$sql_date_fin=" and  end_time <=".$fin_time;
		
	}
	$sql_nom ="and nom='----' ";
	if (($nom)&& (strlen($nom) > 2)){
		$sql_nom=" and 
		              (agt_pat.nom like ('%".$_POST["nom"]."%')
		              OR agt_loc.noip like ('%".$_POST["nom"]."%')
		              OR agt_loc.nda like ('%".$_POST["nom"]."%')
		              )
		              ";
	}else{
		echo "Aucun resultat "	;
	}
	//if (($nip)&& (strlen($nip) > 3)) $sql_nip=" and  agt_pat.noip like ('%".$_POST["nip"]."%')";
  
//recherce les patients dans le même service 
	$session_user = $_SESSION['login'];
   $session_statut = $_SESSION['statut'];
   $list_services =get_areas_allowed($session_user,$session_statut);
   
  
// Recherche des infos liée à la réservation
	$sql = "SELECT agt_pat.noip, 	nom, 	prenom ,	nomjf, 	ddn, 	sex,
		       agt_loc.*,		
		       agt_service.service_name,
		       agt_room.room_name,
		       agt_loc.id	as loc_id			
			FROM agt_loc, agt_room, agt_service,agt_pat
			WHERE agt_loc.room_id = agt_room.id
				AND agt_pat.noip=agt_loc.noip
				AND agt_room.service_id = agt_service.id
				AND statut_entry !='SUPPRIMER'
				AND agt_service.id in (".$list_services.")" ;

$sql.=" $sql_date_deb $sql_date_fin $sql_nom $sql_nip $sql_tri_par " ;  
 //echo $sql; 


if( ($_POST['button']=='Afficher') && ( (strlen($nip) > 2) || (strlen($nom)>2) ) )  
{
?>
	<table width="95%" border="1" cellspacing="1" cellpadding="1" align="center">
	  <tr>
	    <th  align="center">Patient</th>
	    <th  align="center">Service</th>
	    <th  align="center">Room</th>	    
	    <th  align="center">NDA</th>	    
	    <th  align="center">UH</th>	    	    	    
	    <th  align="center"> Séjour</th>
	    <th  align="center">Dur&eacute;e</th>
	    <th  align="center">Protocole</th>    
	  </tr>
	 
	  
	  <?php 

	  $res = $mysql->select($sql);
	  
	  $nb_info = count($res);
	  $row=$res;
	 
	  if ($res) {
		  for ($i = 0; $i<$nb_info; $i++) {
		  	$duree_min= (($row[$i]['end_time']- $row[$i]['start_time'] )/60);// en minutes
		  	$duree_hrs=$duree_min/60;
		  	if ($duree_hrs > 24){
		  		$c_duree=$duree_hrs/24;
		  		$mod=$duree_hrs % 24;
		  		$c_duree= intval($c_duree) ." Jour(s) et " .$mod."  heure(s)";
		  	 }else{
		  	 	$mod=$duree_min % 60 ;
		  		$c_duree= intval($duree_hrs) .":" .$mod ." heure(s)";	  	 
		  	 }

		  	$loc_id=$row[$i]['loc_id'];
		  	$day=date('d',$row[$i]['start_time']);
		  	$month=date('m',$row[$i]['start_time']);
		  	$year=date('Y',$row[$i]['start_time']);
		  	$link="get_link('view_entry.php?id=$loc_id')";
		  	//$link="edit_loc.php?id=$loc_id&day=$day&month=$month&year=$year&page=day";
		  	if(($i%2)==0)$bgcolor="";else $bgcolor="#EED7D2";
		  	//noip 	nom 	prenom 	nomjf 	ddn 	sex
				$UrlEdit=$ModuleReservationEdit."?area=".$area."&room=".$room."&hour=".$hour."minute=".$minute."&year=".$year."&month=".$month."&day=".$day."&page=day&table_loc=agt_loc";
				$lien= "<a href='#?'  onClick=\"OpenPopupResa('".$UrlEdit."')\"><img src='./commun/images/new.png' border='0' alt='".get_vocab("add")."' alt='".get_vocab("add")."' class='print_image' /><img src='./commun/images/new.png' border='0'  alt='".get_vocab("add")."' class='print_image' /></a>";                        
				$link="OpenPopupResa('".$ModuleReservationView."?id=".$loc_id."&table_loc=agt_loc')";						  	
		    ?>
		  <tr  bgcolor="<?php print $bgcolor?>">
		    <td><a href='#' onclick="<?php print $link?>"><?php print $row[$i]['noip']." ".$row[$i]['nom']." ".$row[$i]['prenom']." né(e)".$row[$i]['ddn']." (".$row[$i]['sex'].")"?></td>
		    <td><?php print $row[$i]['service_name']?></td>
		    <td><?php print $row[$i]['room_name']?></td>		    
			<td><?php print $row[$i]['nda'];?></td>		    
			<td><?php print $row[$i]['uh'];?></td>		    			
		    <td><?php print  date('d/m/Y à H:i',$row[$i]['start_time']) ." au ". date('d/m/Y à H:i',$row[$i]['end_time']);?></td>
		    <td><?php print $c_duree ?></td>
		    <td><?php print $row[$i]['protocole']; ?></td>
	      </tr>
		  <?php 
		}
		}
	    ?>
	</table>
<?php
}
?>
