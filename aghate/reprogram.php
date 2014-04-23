<?php
#########################################################################
#                            REPORGRAMATION .php                         #
#                                                                       #
#                          Fiche ressource                              #
#               Dernière modification : 10/07/2006                      #
#                                                                       #
#                                                                       #
#########################################################################

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
$grr_script_name = "view_room.php";
// Settings
require_once("./commun/include/settings.inc.php");
//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

// Session related functions
require_once("./commun/include/session.inc.php");
// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};

// Paramètres langage
include "./commun/include/language.inc.php";

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $type_session = "no_session";
} else {
    $type_session = "with_session";
}

if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
$onload="";

$id_non_venue = isset($_GET["id_non_venue"]) ? $_GET["id_non_venue"] : NULL;
// on récupare id de la $_session
if (strlen($id_non_venue) < 1){
	list($id_non_venue,$nip,$nom_pat)=explode("|",$_SESSION['REPROGMATION']) ;
}
echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"));

?>
<script language="JavaScript">
// reload parent et close popup
function refreshParent() {
	window.opener.location.reload();
  	if (window.opener.progressWindow){
		window.opener.progressWindow.close()
  	}
  	window.close();
}
</script>

<?php

echo $_GET['OK'],$_GET['noip'];
	if( ($_GET['OK']=="OK") && (strlen($_GET['noip']) > 2) ){
		$_SESSION['REPROGMATION']=$_GET['id_non_venue']."|".$_GET['noip']."|".$_GET['nom_pat'];
	?>
		<script LANGUAGE="Javascript">
			refreshParent();
		</script> 
<?php
}

if( ($_GET['Annuler']=="CANCEL") && (strlen($_GET['noip']) > 2) ){
	$_SESSION['REPROGMATION']="";
?><script LANGUAGE="Javascript">
	refreshParent();
	</script> 
	


<style type="text/css">
<!--
.Style1 {
	font-size: 14px;
	font-weight: bold;
	font-style: italic;
}
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
 text-align:left;
 }

-->
</style>
	
<?php

}



$sql = "SELECT agt_room.id, start_time, end_time, name, grr_nonvenu.id, type, beneficiaire, statut_entry, grr_nonvenu.description, grr_nonvenu.option_reservation, grr_nonvenu.moderate, beneficiaire_ext,pmsi,cause,reprogram,agt_room.room_name
   FROM grr_nonvenu, agt_room
   WHERE grr_nonvenu.room_id = agt_room.id
   AND grr_nonvenu.id = '"   .protect_data_sql($id_non_venue)."' ";


$res = grr_sql_query($sql);



for ($t = 0; ($row = grr_sql_row($res, $t)); $t++) {
		$room__id=1;
      $id= $row[4];
      $color = $row[5];
		list($nom,$nip,$dtnais,$sexe)=explode("(",str_replace(")","",$row[3]));
      $protocole = affichage_resa_planning_n($row[8],$row[4]);
      $date=  date("d/m/Y",$row[1]) ." à ".date("h:m", $row[1]);
      $salle= $row[15];
		$motif = $row[13];
	}




?>
<h3 ALIGN=center> Réprogramtion d'une consultation </h3> 
<form name='reprogram'  action="<?php print $_SERVER['PHP_SELF'];?>" method='get' > 
<div id='reprogram' class='model1'> 
<input type='hidden' name='noip'  value="<?php print $nip?>"> 
<input type='hidden' name='id_non_venue'  value="<?php print $id_non_venue; ?>" > 
<input type='hidden' name='nom_pat'  value="<?php print $nom; ?>" > 

<table width="420" border="0" cellspacing="2" cellpadding="1" align="center">
  
  <tr>
    <td width="178">Patient</td>
    <td width="232"><?php Print  $nom;?></td>
  </tr>
  <tr>
    <td>Date</td>
    <td><?php Print $date;?></td>
  </tr>
  <tr>
    <td>Salle</td>
    <td><?php Print $salle;?></td>
  </tr>
  <tr>
    <td>Protocole</td>
    <td><?php Print $protocole;?></td>
  </tr>
  <tr>
    <td>Motif non venue</td>
    <td><?php Print $motif;?></td>
  </tr>
  
  <tr>
    <td align="right">Voulez Vous reprogrammez ?</td>
    <td><span class="model1">
      <input type='submit' name='OK'   value='OK' />
      <input type='submit' name='Annuler'   value='CANCEL' />
    </span></td>
  </tr>
</table>
</form>
