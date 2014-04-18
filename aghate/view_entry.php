<?php
#########################################################################
#                         view_entry.php                                #
#                                                                       #
#                  Interface de visualisation d'une réservation         #
#                                                                       #
#                  Dernière modification : 28/03/2008                   #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
 * D'après http://mrbs.sourceforge.net/
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

include_once("./config/config.php");
include_once("./config/config.inc.php");
include_once("./commun/include/functions.inc.php");
include_once("./commun/include/$dbsys.inc.php");
include_once("./commun/include/misc.inc.php");
include_once("./commun/include/mrbs_sql.inc.php");
include_once ('./config/config.php');
include_once ("./commun/include/ClassMysql.php");
include_once ("./commun/include/ClassAghate.php");
error_reporting(E_ALL^E_NOTICE);

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";


$grr_script_name = 'view_entry.php';

// Settings
require_once("./commun/include/settings.inc.php");

//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

// Session related functions
require_once("./commun/include/session.inc.php");

// Paramètres langage
include_once("./commun/include/language.inc.php");

// Resume session
$fin_session = 'n';
if (!grr_resumeSession())
    $fin_session = 'y';

if (($fin_session == 'y') and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $type_session = "no_session";
}
else
{
  $session_login = $_SESSION['login'];
  $type_session = "with_session";
}

// Initialisation
unset($reg_statut_id);
$reg_statut_id = isset($_GET["statut_id"]) ? $_GET["statut_id"] : "-";
if (isset($_GET["id"]))
{
  $id = $_GET["id"];
  settype($id,"integer");
} else {
  die();
}

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if (isset($_GET["action_moderate"])) {
    // on modère
    moderate_entry_do($id,$_GET["moderate"],$_GET["description"]);
};

if (isset($_GET["malade_non_venue"])) {
    // on modère
    nonvenu_entry_do($id,$_GET["moderate"],$_GET["description"],$_GET["desc_cause"],$_GET["cause"]);
        $back = $_GET["page"].'.php?year='.$_GET["year"].'&amp;month='.$_GET["month"].'&amp;day='.$_GET["day"];
    header("Location: ".$back."");
    die();    
};

// Recherche des infos liée à la réservation
/*$sql = "SELECT agt_loc.name, 												row[0]
       agt_loc.description,													row[1]
       agt_room.room_name,														row[2]
       agt_service.service_name,														row[3]
       agt_loc.type,															row[4]
       agt_loc.room_id,														row[5]
             ".grr_sql_syntax_timestamp_to_unix('agt_loc.timestamp').",		row[6]
       (agt_loc.end_time - agt_loc.start_time) as duration,					row[7]
       agt_loc.start_time,													row[8]
       agt_loc.end_time,														row[9]
       agt_service.id,																row[10]
       agt_loc.statut_entry,													row[11]
       agt_room.delais_option_reservation,									    row[12]
       agt_loc.create_by, 													row[13]
       agt_room.active_ressource_empruntee, 									row[14]
       agt_loc.medecin, 														row[15]
       agt_service.urm 															row[16]
FROM agt_loc, agt_room, agt_service
WHERE agt_loc.room_id = agt_room.id
  AND agt_room.service_id = agt_service.id
        AND agt_loc.id='".$id."'";*/

/*$sql_backup = "SELECT agt_loc_moderate.name,											row[0]
       agt_loc_moderate.description,                                                      row[1]
       agt_loc_moderate.beneficiaire,                                                     row[2]
       agt_room.room_name,                                                                  row[3]
       agt_service.service_name,                                                                  row[4]
       agt_loc_moderate.type,                                                             row[5]
       agt_loc_moderate.room_id,                                                          row[6]
       agt_loc_moderate.repeat_id,                                                        row[7]
                    ".grr_sql_syntax_timestamp_to_unix('agt_loc_moderate.timestamp').",   row[8]
       (agt_loc_moderate.end_time - agt_loc_moderate.start_time),                       row[9]
       agt_loc_moderate.start_time,                                                       row[10]
       agt_loc_moderate.end_time,                                                         row[11]
       agt_service.id,                                                                         row[12]
       agt_loc_moderate.statut_entry,                                                     row[13]
       agt_room.delais_option_reservation,                                                  row[14]
       agt_loc_moderate.option_reservation, " .                                           row[15]
       "agt_loc_moderate.moderate,                                                        row[16]
       agt_loc_moderate.beneficiaire_ext,													row[17]
       agt_loc_moderate.create_by															row[18]
FROM agt_loc_moderate, agt_room, agt_service
WHERE agt_loc_moderate.room_id = agt_room.id
  AND agt_room.service_id = agt_service.id
                AND agt_loc_moderate.id='".$id."'";*/

$res = $Aghate->GetInfoReservation($id);
$nb_info = count($res);
if (! $res) fatal_error(0, grr_sql_error());
if($nb_info< 1)
{
  $reservation_is_delete = 'y';
  // La réservation n'est pas présente dans la table agt_loc, cela signifie qu'elle a été supprimée
  // On en cherche donc la trace dans agt_loc_moderate
  $was_del = TRUE;
//  $row = $Aghate->GetInfoReservationBackUp($id);
   fatal_error(0, grr_sql_error());
}
else {
  // la réservation est normalement présente dans agt_loc
  $was_del = FALSE;
  $row = $Aghate->GetInfoReservation($id);
}

$breve_description         = "(".$row['nda'].") ".$row['nom']." ".$row['prenom']." (".$row['noip']
													.") ".$row['ddn']." (".$row['type'].")";
$nda		  = $row['nda'];
$noip         = $row['noip'];
$nom		  = $row['nom'];
$prenom		  = $row['prenom'];	
$ddn		  = $row['ddn'];											
$description  = htmlspecialchars($row['description']);
$room_name    = htmlspecialchars($row['room_name']);
$room_alias    = htmlspecialchars($row['room_alias']);
$service_name    = htmlspecialchars($row['service_name']);
$type         = $row['type'];
$room_id      = $row['room_id'];
$updated      = $row['timestamp']; //time_date_string($row['timestamp'],$dformat);
$duration     = $row['duration'];
$area      = $row['service_id'];
$statut_id = $row['statut_entry'];
$delais_option_reservation = $row['delais_option_reservation'];
//$option_reservation = $row[15];   // prochain row 16 il faut tout décaler
$create_by    = htmlspecialchars($row['create_by']);
//$jour_cycle    = htmlspecialchars($row[19]);
$active_ressource_empruntee = htmlspecialchars($row['active_ressource_empruntee']);
$med = htmlspecialchars($row['medecin']);
$urm = htmlspecialchars($row['urm']);
$rep_type = 0;
// Si l'utilisateur est administrateur, possibilité de modifier le statut de la réservation (en cours / libérée)
if (($fin_session == 'n') and isset($_SESSION['login']) and (authGetUserLevel($_SESSION['login'],$room_id) >= 3) and (isset($_GET['ok'])))
{
  if (!$was_del)
    {
      $upd1 = "update agt_loc set statut_entry='-' where room_id = '".$room_id."'";
      if (grr_sql_command($upd1) < 0) return 0;
      $upd2 = "update agt_loc set statut_entry='$reg_statut_id' where id = '".$id."'";
      if (grr_sql_command($upd2) < 0) return 0;
      if ((isset($_GET["envoyer_mail"])) and (getSettingValue("automatic_mail") == 'yes')) {
          $_SESSION['session_message_error'] = send_mail($id,7,$dformat);
          if ($_SESSION['session_message_error'] == "") {
              $_SESSION['displ_msg'] = "yes";
              $_SESSION["msg_a_afficher"] = get_vocab("un email envoye")." ".$_GET["mail_exist"];
          }
      }
      header("Location: ".$_GET['back']."");
      die();
    }
}
#If we dont know the right date then make it up
if(!isset($day) or !isset($month) or !isset($year))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
}


if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
if(authUserAccesArea($session_login, $area)==0)
{
	if (isset($reservation_is_delete))
	    showNoReservation($day, $month, $year, $area,$back);
	else
	    showAccessDenied($day, $month, $year, $area,$back);
	    exit();
}



$date_now = time();

$page = verif_page();

print_header($day, $month, $year, $area, $type_session);

?>
<script  type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<script  type="text/javascript" >
	function cause_nonvenu(cause)
	{
		document.getElementById("desc_cause").value = cause;
	}


	function OPENPOPUP(param) {
		mywindow=open(param,'myname','resizable=yes,width=780,height=450,status=yes,scrollbars=yes');
	   mywindow.location.href = param;
	   if (mywindow.opener == null) mywindow.opener = self;
	}
</script>
<?php
// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

if($enable_periods=='y'){
	 list( $start_period, $start_date) =  period_date_string($row['start_time']);
	 $start_date = time_date_string($row['start_time'],$dformat);
	}
else $start_date = time_date_string($row['start_time'],$dformat);
$c_date=$row['start_time'];


if($enable_periods=='y') list( , $end_date) =  period_date_string($row['end_time'], -1);
else $end_date = time_date_string($row['end_time'],$dformat);

/*echo '<pre>';
print_r($row);
echo '</pre>';
*/

/*if ($beneficiaire!="") {
    $mail_exist = grr_sql_query1("select email from agt_utilisateurs where login='$beneficiaire'");
} else {
    $tab_benef = donne_nom_email($beneficiaire_ext);
    $mail_exist = $tab_benef["email"];
}*/


if ($enable_periods=='y') toPeriodString($start_period, $duration, $dur_units);
else toTimeString($duration, $dur_units);


# Now that we know all the data we start drawing it
// if ($was_del) echo "effacé"; else echo "OK";

// Cas où la page pointe sur elle-même, on recalcul $back
if (strstr ($back, 'view_entry.php')) {
    $res = $Aghate->GetInfoEntry($id);
    $nb_entry_info = count($res);
    $row = $res;
    if (! $res) echo "Erreur";
    if($nb_entry_info >= 1) {
        $year = date ('Y', $row['start_time']); $month = date ('m', $row['start_time']); $day = date ('d', $row['start_time']);
        $back = $page.'.php?year='.$year.'&amp;month='.$month.'&amp;day='.$day;
        if (($_GET["page"] == "week") or ($_GET["page"] == "month") or ($_GET["page"] == "week_all") or ($_GET["page"] == "month_all"))
        {
        $back .= "&amp;area=".mrbsGetServiceIdByRoomId($row['room_id']);
        }
        if (($_GET["page"] == "week") or ($_GET["page"] == "month") )
        {
        $back .= "&amp;room=".$row['room_id'];
        }

    } else
        $back = "";
}

$all_entry_nda = $Aghate->GetEntriesByNda($nda);

if ($back != "") echo "<a href=\"".$back."\">".get_vocab("returnprev")."</a>";
echo '<br /><br /><fieldset><legend style="font-size:12pt;font-weight:bold">'.get_vocab('entry').get_vocab('deux_points').affichage_lien_resa_planning($breve_description, $id).'</legend>'."\n";
?>
 <table border="0">
 
   <tr>
		<td><b><?php echo "N° de réservation :";  ?></b></td>
		<td><?php    echo $id  ?></td>
   </tr>
   <tr>
		<td><b><?php echo "NOIP :";  ?></b></td>
		<td><?php    echo $noip  ?></td>
   </tr>
   <tr>
		<td><b><?php echo "NDA :";  ?></b></td>
		<td><?php    echo $nda  ?></td>
   </tr>
	<tr>
		<td><b><?php echo "Nom :";  ?></b></td>
		<td><?php    echo $nom  ?></td>
   </tr>
   <tr>
		<td><b><?php echo "Prenom :";  ?></b></td>
		<td><?php    echo $prenom  ?></td>
   </tr>
   <tr>
		<td><b><?php echo "Entrée(s) :";  ?></b></td>
   <?php 
    $prev_sejours = $Aghate->GetPreviousSejours ($all_entry_nda,$id); // retourne le tableau avec l'ensemble des séjours qui precede le entry id courant
	$nb_sejours = count($prev_sejours); // on compte le nb de séjours précedent
	$entrees = "";
	if ($nb_sejours >= 1 && !empty($prev_sejours)) { // on verifie que le tableau n'est pas vide car count(tabvide)=1
		for($i=0;$i<$nb_sejours;$i++) { // on boucle pour chaque séjours precedent pour obtenir les liens
			$AreaName = $Aghate->GetServiceNameByUh($prev_sejours[$i]['uh']);
			$areaname = str_replace(' ','&nbsp;',$AreaName['service_name']);
			$entrees.= "<a href=view_entry.php?id=".$prev_sejours[$i]['id']."&area=".$prev_sejours[$i]['area'].
						"&day=".$prev_sejours[$i]['day']."&month=".$prev_sejours[$i]['month'].
						"&year=".$prev_sejours[$i]['year']."&page=day title=".$areaname.">".$prev_sejours[$i]['uh']."</a>\n";
		} 
	}
	else{
		$entrees = " => ADM "; // si le tableau est vide il n'y a pas de séjours précedent => c'est une entrée
	}
	   ?>
   <td><?php    echo $entrees  ?></td>
   </tr>
   
   <tr>
		<td><b><?php echo "Sortie(s) :";  ?></b></td>
   <?php 
    $next_sejours = $Aghate->GetNextSejours($all_entry_nda,$id);  // retourne le tableau avec les séjours qui suivent le id courant et son indice dans le tab all_entry_nda
    $indice = $next_sejours["indice"];
    $nb_next_sejours = count($next_sejours);
	$nb_sejours = $indice+$nb_next_sejours-1; // indice + le nb de séjours qui suivent + (-1) pour ne pas compter l'indice qui est un élément du tab
	$sorties = "";
	if ($nb_sejours >= 1) {
		for($i=$indice;$i<=$nb_sejours;$i++) { // on boucle a partir de l'indice 
			$AreaName = $Aghate->GetServiceNameByUh($next_sejours[$i]['uh']);
			$areaname = str_replace(' ','&nbsp;',$AreaName['service_name']);
			$sorties.= "<a href=view_entry.php?id=".$next_sejours[$i]['id']."&area=".$next_sejours[$i]['area'].
						"&day=".$next_sejours[$i]['day']."&month=".$next_sejours[$i]['month'].
						"&year=".$next_sejours[$i]['year']."&page=day title=".$areaname.">".$next_sejours[$i]['uh']."</a>\n";
		} 
	}
	else{
		$sorties = "SOR =>"; // si nb_sejours = 0 c'est une sortie 
	}
   ?>
   <td><?php    echo $sorties  ?></td>
   </tr>
   
    <tr>
		<td><b><?php echo "Date de naissance :";  ?></b></td>
		<td><?php    echo $ddn  ?></td>
   </tr>
   <tr>
    <td><b><?php echo get_vocab("description") ?></b></td>
    <td><?php    echo nl2br($description)  ?></td>
   </tr>
   <?php
    //Informations additionnelles
    /*if (!$was_del) {
      $overload_data = mrbsEntryGetOverloadDesc($id);
      foreach ($overload_data as $fieldname=>$fielddata) {
        if ($fielddata["confidentiel"] == 'n')
            $affiche_champ = 'y';
        else
            if (($fin_session != 'n') or (!isset($_SESSION['login'])))
               $affiche_champ = 'n';
            else
               // seuls les administrateurs et le bénéficiaire peut voir un champ confidentiel
               if ((authGetUserLevel($_SESSION['login'],$room_id) >= 4) or ($beneficiaire == getUserName()))
                   $affiche_champ = 'y';
               else
                   $affiche_champ = 'n';
        if ($affiche_champ == 'y') {
            echo "<tr><TD><b>".$fieldname.get_vocab("deux_points")."</b></td>\n";
            echo "<td>".$fielddata["valeur"]."</td></tr>\n";
        }
      }
    }*/
   ?>
   <tr>
    <td><b><?php echo get_vocab("room").get_vocab("deux_points")  ?></b></td>
    <td><?php    echo  nl2br($service_name . " - " . $room_alias) ?></td>
   </tr>
   <tr>
    <td><b><?php echo get_vocab("start_date").get_vocab("deux_points") ?></b></td>
<td><?php    echo $start_date         ?></td>
   </tr>
   <tr>
    <td><b><?php echo get_vocab("duration")            ?></b></td>
    <td><?php    echo $duration . " " . $dur_units ?></td>
   </tr>
   <tr>
    <td><b><?php echo get_vocab("end_date") ?></b></td>
    <td><?php    echo $end_date         ?></td>
   </tr>
   <?php
   echo "<tr><td><b>".get_vocab("type").get_vocab("deux_points")."</b></td>\n";
   $type_name = $Aghate->GetType($type);
  
   if ($type_name == -1) $type_name = "?$type?";
   echo "<td>".$type_name."</td></tr>";
	// affiche medecin par mohan
   echo "<tr><td><b> Médecin : </b></td>\n";
   echo "<td>".$med."</td></tr>";
   
   if ($beneficiaire != $create_by) {
   ?>
   <tr>
    <td><b><?php echo get_vocab("reservation au nom de").get_vocab("deux_points") ?></b></td>
    <td><?php    echo $create_by;         ?></td>
   </tr>
   <?php
   }
   ?>

   <tr>
    <td><b><?php echo get_vocab("created_by").get_vocab("deux_points") ?></b></td>
    <td><?php    echo $create_by;         ?></td>
   </tr>

   <tr>
    <td><b><?php echo get_vocab("lastupdate").get_vocab("deux_points") ?></b></td>
    <td><?php    echo $updated          ?></td>
   </tr>
   <?php

// Option de réservation
if (($delais_option_reservation > 0) and ($option_reservation!=-1))
{
  echo "<tr bgcolor=\"#FF6955\"><td><b>".get_vocab("reservation_a_confirmer_au_plus_tard_le")."<b></td>\n";
  echo "<TD><b>".time_date_string_jma($option_reservation,$dformat)."</b>\n";
  echo "</TD></TR>\n";
}


if ($moderate == 1) {
    // En attente de modération
    echo "<tr><td><b>".get_vocab("moderation").get_vocab("deux_points")."</b></td>"; tdcell("avertissement"); echo "<strong>".get_vocab("en_attente_moderation")."</strong></td></tr>";
 } elseif ($moderate == 2) {
    // Modération acceptée
    // recupération des infos de moderation
    $res = $Aghate->GetInfoModeration($id);
    $row = $res;
    $description = $row['motivation_moderation'];
    // recuperation du nom du moderateur
    $res = $Aghate->GetUserInfo($row['login_moderateur']);
    $res = $res[0];
	if (! $res) fatal_error(0, grr_sql_error());
    $row = $res; $nom_modo = $row['prenom']. ' '. $row['nom'];
    echo '<tr><td><b>'.get_vocab("moderation").get_vocab("deux_points").'</b></td><td><strong>'.get_vocab("moderation_acceptee_par").'&nbsp;'.$nom_modo.'</strong>';
    if ($description != "") echo ' : <br />('.$description.')';
    echo "</td></tr>";

 } elseif ($moderate == 3) {
    // Modération refusée
    // recupération des infos de moderation
	$res = $Aghate->GetInfoModeration($id);
    $row = $res;
    if (! $res) fatal_error(0, grr_sql_error());
        // recuperation du nom du moderateur
    $description = $row['motivation_moderation'];
    $res = $Aghate->GetUserInfo($row['login_moderateur']);
    $res = $res[0];
    if (! $res) fatal_error(0, grr_sql_error());
    $row = $res; $nom_modo = $row['prenom']. ' '. $row['nom'];
    echo '<tr><td><b>'.get_vocab("moderation").get_vocab("deux_points").'</b></td>'; tdcell("avertissement"); echo '<strong>'.get_vocab("moderation_refusee").'</strong> par '.$nom_modo;
    if ($description != "") echo ' : <br />('.$description.')';
    echo "</td></tr>";
 }


if ((getWritable($beneficiaire, getUserName(),$id)) and verif_booking_date(getUserName(), $id, $room_id, -1, $date_now, $enable_periods) and (!$was_del)) { ?>
    <tr>
    <td colspan="2">
    <?php
    echo "<a href=\"edit_entry.php?id=$id&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=$page\">".get_vocab("editentry")."</a>";
    if  ($can_delete_or_create=="y") {
    $message_confirmation = str_replace ( "'"  , "\\'"  , get_vocab("confirmdel").get_vocab("deleteentry"));
    ?>
     - <a href="del_entry.php?id=<?php echo $id ?>&amp;series=0&amp;page=<?php echo $page; ?>" onClick="return confirm('<?php echo $message_confirmation ?>');"><?php echo get_vocab("deleteentry") ?></a></td>
    <?php
    }
    //echo "</tr>";
 
	//====================================================
	//Par mohan pour imprimmer les convocations
	//====================================================
	//Print_rdv($uh,$nip,$date_entree,$med,$Mme_mr,$pat);
	$c_pat=affichage_lien_resa_planning($breve_description, $id);
	$pats = explode("(", $c_pat);
	$p_nom=$pats[0];
	//$noip=substr($pats[1],0,10); // à voir
	$p_ddn=substr($pats[2],0,10); // à voir 
	$p_sexe=substr($pats[3],0,1); // à voir 
	$duree= $duration .' '. $dur_units;
 	$link="convocation_options.php?nip=$noip&uh=$uh&date_entree=$start_date&med=$med&sexe=$p_sexe&pat=$p_nom&duree=$duree&date_nais=$p_ddn";
 	$link="convocation_options.php?pat=$c_pat&uh=$uh&med=$med&duree=$duree&date_nais=$p_ddn&date_entree=$start_date&area=$area";
 	$link="convocation_options.php?entry_id=$id";

/* 	if($urm=="010"){
 		$link="generate_rdv_010.php?date_entree=$start_date&area=$area";
	}
*/
 	?>
 	 <td>
 	 	<a href="#"  onClick="OPENPOPUP('<?php echo $link;?>')" > Imprimmer cette réservation </a>
   </td>
   </tr>
   <?php
    
}

echo "</table>";
echo "</fieldset>\n";

// champs repeat_id supprimer
/*
if($repeat_id != 0) {
    $res = grr_sql_query("select rep_type, end_date, rep_opt, rep_num_weeks, start_time, end_time from agt_repeat where id=$repeat_id");
    if (! $res) fatal_error(0, grr_sql_error());

    if (grr_sql_count($res) == 1)
    {
        $row = grr_sql_row($res, 0);
        $rep_type     = $row[0];
        $rep_end_date = utf8_strftime($dformat,$row[1]);
        $rep_opt      = $row[2];
        $rep_num_weeks = $row[3];
        $start_time =  $row[4];
        $end_time =  $row[5];
        $duration = $row[5] - $row[4];
    }
    grr_sql_free($res);

    if($enable_periods=='y') list( $start_period, $start_date) =  period_date_string($start_time);
        else $start_date = time_date_string($start_time,$dformat);
    if ($enable_periods=='y') toPeriodString($start_period, $duration, $dur_units);
        else toTimeString($duration, $dur_units);

    $weeklist = array("unused","every week","week 1/2","week 1/3","week 1/4","week 1/5");
    if ($rep_type == 2)
        $affiche_period = get_vocab($weeklist[$rep_num_weeks]);
    else
        $affiche_period = get_vocab('rep_type_'.$rep_type);

    echo '<br /><fieldset><legend style="font-weight:bold">'.get_vocab('periodicite_associe').grr_help("aide_grr_periodicite","fonctionnement")."</legend>\n";
    echo '<table cellpadding="1">';
    echo '<tr><td><b>'.get_vocab("rep_type").'</b></td><td>'.$affiche_period.'</td></tr>';
    if($rep_type != 0) {
    // cas d'une periodicité "une semaine sur n", on affiche les jours de périodicité
      if ($rep_type == 2) {
        $opt = "";
        $nb = 0;
        # Display day names according to language and preferred weekday start.
        for ($i = 0; $i < 7; $i++)
        {
            $daynum = ($i + $weekstarts) % 7;
            if ($rep_opt[$daynum]) {
                if ($opt != '') $opt .=', ';
                $opt .= day_name($daynum);
                $nb++;
             }
        }
        if($opt)
            if ($nb == 1)
                echo "<tr><td><b>".get_vocab("rep_rep_day")."</b></td><td>$opt</td></tr>\n";
            else
                echo "<tr><td><b>".get_vocab("rep_rep_days")."</b></td><td>$opt</td></tr>\n";

      }
      // cas d'une periodicité "Jour Cycle", on affiche le numéro du jour cycle
      if ($rep_type == 6) {
        if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
            echo "<tr><td><b>".get_vocab("rep_rep_day")."</b></td><td>".get_vocab('jour_cycle').' '.$jour_cycle."</td></tr>\n";
      }
      echo '<tr><td><b>'.get_vocab("date").get_vocab("deux_points").'</b></td><td>'.$start_date.'</td></tr>';
      echo '<tr><td><b>'.get_vocab("duration").'</b></td><td>'.$duration .' '. $dur_units.'</td></tr>';
      echo '<tr><td><b>'.get_vocab('rep_end_date').'</b></td><td>'.$rep_end_date.'</td></tr>';
    }
    if ((getWritable($beneficiaire, getUserName(),$id)) and verif_booking_date(getUserName(), $id, $room_id, -1, $date_now, $enable_periods) and (!$was_del)) {
        $message_confirmation = str_replace ( "'"  , "\\'"  , get_vocab("confirmdel").get_vocab("deleteseries"));
        echo "<tr><td colspan = \"2\"><a href=\"edit_entry.php?id=$id&amp;edit_type=series&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=$page\">".get_vocab("editseries")."</a></td></tr>";
        echo "<tr><td colspan = \"2\"><a href=\"del_entry.php?id=$id&amp;series=1&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=$page\" onClick=\"return confirm('".$message_confirmation."');\">".get_vocab("deleteseries")."</a></td></tr>";
    }
    echo "</table></fieldset>";
}

*/

// Si l'utilisateur est gestionnaire de la ressource, possibilité de modérer la réservation
if ( isset($_SESSION['login']) and (authGetUserLevel($_SESSION['login'],$room_id) >= 3) and ($moderate == 1)) {
  echo "<form action=\"view_entry.php\" method=\"get\">\n";
  echo "<input type=\"hidden\" name=\"action_moderate\" value=\"y\" />\n";
  echo "<input type=\"hidden\" name=\"id\" value=\"".$id."\" />\n";
  if (isset($_GET['page']))
      echo "<input type=\"hidden\" name=\"page\" value=\"".$_GET['page']."\" />\n";
  echo "<br /><fieldset><legend style=\"font-weight:bold\">".get_vocab("moderate_entry").grr_help("aide_grr_moderation")."</legend>\n";
  echo "<p>";
  echo "<input type=\"radio\" name=\"moderate\" value=\"1\" checked />".get_vocab("accepter_resa");
  echo "<br /><input type=\"radio\" name=\"moderate\" value=\"0\" />".get_vocab("refuser_resa");
  if($repeat_id) {
     echo "<br /><input type=\"radio\" name=\"moderate\" value=\"S1\" />".get_vocab("accepter_resa_serie");
     echo "<br /><input type=\"radio\" name=\"moderate\" value=\"S0\" />".get_vocab("refuser_resa_serie");
  }
  echo "</p><p>";
  echo "<label for=\"description\">".get_vocab("justifier_decision_moderation").get_vocab("deux_points")."</label>\n";
  echo "<textarea name=\"description\" id=\"description\" cols=\"40\" rows=\"3\"></textarea>";
  echo "</p>";
  echo "<br /><center><input type=\"submit\" name=\"commit\" value=\"".get_vocab("save")."\" /></center>\n";
  echo "</fieldset></form>\n";
}




//====================================================
//Par mohan pour les traitements de patients non venu
//====================================================
// Si l'utilisateur est gestionnaire de la ressource, possibilité de modérer la réservation

if ( isset($_SESSION['login']) and (getWritable($beneficiaire, getUserName(),$id)) and (date("d/m/y",$c_date)==date("d/m/y"))) {
  echo "<form action=\"view_entry.php\" method=\"get\">\n";
  echo "<input type=\"hidden\" name=\"malade_non_venue\" value=\"y\" />\n";
  echo "<input type=\"hidden\" name=\"id\" value=\"".$id."\" />\n";
  if (isset($_GET['page']))
      echo "<input type=\"hidden\" name=\"page\" value=\"".$_GET['page']."\" />\n";
  echo "<br /><fieldset><legend style=\"font-weight:bold\"> Patient Non venu</legend>\n";
  echo "<p><b>Causes : <BR></B>" ;
  $cause_liste= array();
  $cause_liste= array("Bilan biologique perturbé",
  							  "Erreur de date par patient",
							  "Non venu à sa consultation d'anesthésie",
							  "Annulation par le malade",
							  "Faute de place",
							  "Préparation aux examens insuffisante",
							  "Autres : "
							  );
	if (!$cause)$cause=$cause_liste[0];
	for($c=0;$c<count($cause_liste);$c++)	{					
		if ($cause==$cause_liste[$c])		  
		  	echo "<br/><input type=\"radio\" name=\"cause\" value=\"".$cause_liste[$c]."\" checked  onclick=\"cause_nonvenu('".$cause_liste[$c]."')\" />".$cause_liste[$c];
		else
		  	echo "<br/><input type=\"radio\" name=\"cause\" value=\"".$cause_liste[$c]."\"  onclick=\"cause_nonvenu('".htmlspecialchars($cause_liste[$c])."')\"   />".$cause_liste[$c];
	}




  echo "<label for=\"description\">".get_vocab("justifier_decision_moderation").get_vocab("deux_points")."</label>\n";
  echo "<textarea name=\"desc_cause\" id=\"desc_cause\" cols=\"40\" rows=\"3\">$cause_liste[0]</textarea>";
  echo "</p>";
  echo "<br /><center><input type=\"submit\" name=\"commit\" value=\"".get_vocab("save")."\" /></center>\n";
  echo "</fieldset></form>\n";
}

include_once("./commun/include/trailer.inc.php");
?>
