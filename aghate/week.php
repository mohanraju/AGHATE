<?php
#########################################################################
#                            week.php                                   #
#    Permet l'affichage de la page d'accueil lorsque l'on est en mode   #
#    d'affichage "semaine".                                             #
#       Dernière modification : 19/09/2006                              #
#                                                                       #
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
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/CommonFonctions.php";

$ModuleReservationEdit = "reservation_exam_compl.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_exam_compl";


$grr_script_name = "week.php";
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
if (empty($room))
    $room = grr_sql_query1("select min(id) from agt_room where service_id=$area");
$area =  mrbsGetServiceIdByRoomId($room);


# Note $room will be -1 if there are no rooms; this is checked for below.

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Paramètres langage
include "./commun/include/language.inc.php";

// On affiche le lien "format imprimable" en bas de la page
$affiche_pview = '1';
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

if (empty($debug_flag)) $debug_flag = 0;
$date_now = time();
# If we don't know the right date then use today:
if (!isset($day) or !isset($month) or !isset($year))
{
    if ($date_now < getSettingValue("begin_bookings"))
        $date_ = getSettingValue("begin_bookings");
    else if ($date_now > getSettingValue("end_bookings"))
        $date_ = getSettingValue("end_bookings");
    else
        $date_ = $date_now;
    $day   = date("d",$date_);
    $month = date("m",$date_);
    $year  = date("Y",$date_);
} else {
    // Vérification des dates
    settype($month,"integer");
    settype($day,"integer");
    settype($year,"integer");
    $minyear = strftime("%Y", getSettingValue("begin_bookings"));
    $maxyear = strftime("%Y", getSettingValue("end_bookings"));
    if ($day < 1) $day = 1;
    if ($day > 31) $day = 31;
    if ($month < 1) $month = 1;
    if ($month > 12) $month = 12;
    if ($year < $minyear) $year = $minyear;
    if ($year > $maxyear) $year = $maxyear;
    # Make the date valid if day is more then number of days in month:
    while (!checkdate($month, $day, $year))
        $day--;
}

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
} else {
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}

// Si aucun domaine n'est défini
if ($area == 0) {
   print_header($day, $month, $year, $area,$type_session);
   echo "<H1>".get_vocab("noareas")."</H1>";
   echo "<A HREF='admin_accueil.php'>".get_vocab("admin")."</A>\n
   </BODY>
   </HTML>";
   exit();
}

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if (check_begin_end_bookings($day, $month, $year))
{
    showNoBookings($day, $month, $year, $area,$back,$type_session);
    exit();
}
if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
if(authUserAccesArea($session_login, $area)==0)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
# Set the date back to the previous $weekstarts day (Sunday, if 0):
$time = mktime(0, 0, 0, $month, $day, $year);
$time_old = $time;

// date("w", $time) : jour de la semaine en partant de dimancche
// date("w", $time) - $weekstarts : jour de la semaine en partant du jour défini dans GRR
// Si $day ne correspond pas au premier jour de la semaine tel que défini dans GRR,
// on recule la date jusqu'au précédent début de semaine
// Evidemment, problème possible avec les changement été-hiver et hiver-été
if (($weekday = (date("w", $time) - $weekstarts + 7) % 7) > 0)
{
    $time -= $weekday * 86400;
}
// Si le dimanche correspondant au changement d'heure est entre $time et $time_old, on corrige de +1 h ou -1 h.
if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
    if  ((heure_ete_hiver("ete",$year,0) <= $time_old) and (heure_ete_hiver("ete",$year,0) >= $time) and ($time_old != $time) and (date("H", $time)== 23))
        $decal = 3600;
    else
        $decal = 0;
    $time += $decal;
}

// On vérifie une fois par jour si le délai de confirmation des réservations est dépassé
// Si oui, les réservations concernées sont supprimées et un mail automatique est envoyé.
// On vérifie une fois par jour que les ressources ont été rendue en fin de réservation
// Si non, une notification email est envoyée
if (getSettingValue("verif_reservation_auto")==0) {
    verify_confirm_reservation();
    verify_retard_reservation();
}

// $day_week, $month_week, $year_week sont jours, semaines et années correspondant au premier jour de la semaine
$day_week   = date("d", $time);
$month_week = date("m", $time);
$year_week  = date("Y", $time);

# print the page header
print_header($day, $month, $year, $area, $type_session);

if($enable_periods=='y') {
    $resolution = 60;
    $morningstarts = 12;
    $morningstarts_minutes = 0;
    $eveningends = 12;
    $eveningends_minutes = count($periods_name)-1;
}
?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<script type="text/javascript" src="./commun/js/functions.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-1.10.2.js" charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/jquery-ui-1.10.4.custom.js"  charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/info_bulle.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="./commun/js/jquery.contextMenu.js"></script>
<style>
td.ui-datepicker-week-col{	
	cursor:pointer;
	cusor:hand;}

div#ui-datepicker-div.ui-datepicker.ui-widget.ui-widget-content.ui-helper-clearfix.ui-corner-all {
	font-size: 1.3em; 
}

	
</style>
<link href="./commun/style/smoothness/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css" media="all"  charset="utf-8"/>
<link href="./commun/style/day.css" rel="stylesheet" type="text/css" media="all" />
<link href="./commun/style/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
<script type='text/javascript'>
	
function OpenPopup(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=750,height=250,left=150,top=100,status=yes,scrollbars=yes').focus();
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
function OpenPopupResa(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=850,height=670,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
	
/*
#####################################################
		Ducuement ready funtions
#######################################################	
*/
// SET REGIONAL SETTINGS to FR
jQuery(function($){
   $.datepicker.regional['fr'] = {
	  showWeek: true,
	  closeText: 'Fermer',
	  prevText: 'Préc',
	  nextText: 'Suiv',
	  currentText: 'Courant',
	  monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
	  'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
	  monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
	  'Jul','Aoû','Sep','Oct','Nov','Déc'],
	  dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
	  dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
	  dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
	  weekHeader: 'Sm',
	  //dateFormat: 'dd/mm/yy',
				dateFormat: 'dd/mm/yy',
	  firstDay: 1,
	  isRTL: false,
	  showMonthAfterYear: false,
	  yearSuffix: ''};
   $.datepicker.setDefaults($.datepicker.regional['fr']);
});

$(function() {
	//$('#cur_date').val(<?php print $day ?>+"/"+<?php print $month ?>+"/"+<?php print $year ?>);
	/*
	#####################################################
			Jquery Calandar en 3 mois
	#######################################################	
	*/	
	$('#cur_date').datepicker({
		showWeek : true,
		showOn: "button",
		buttonImage: "./commun/images/calendar.gif",
		buttonImageOnly: true,		
		numberOfMonths: 1, 
		showCurrentAtPos: 0,
		changeMonth: true,
		changeYear: true,	
		showAnim: "drop",		
		dateFormat: 'dd/mm/yy',
		onSelect: function(dateText, inst) {
			dt= dateText.split("/");
			var url ="./day.php?year="+dt[2]+"&month="+dt[1]+"&day="+dt[0]+"&area="+<?php print $area?>;
			location.href=url
		  }			
	});			

	/*#####################################################
		DROG AND DROP TBODY 
		avec le class "DrogDropTBody" sur tbody
	#######################################################	
	*/
  $( "tbody.DrogDropTBody" )
      .sortable({
          connectWith: ".DrogDropTBody",
          items: "> tr:not(:first)",
          helper:"clone",
          zIndex: 999990,
          update: function( event, ui ) {UpdateTimes()},
      })
      .disableSelection() ;
 
 	/*#####################################################
		DROG AND DROP TBODY 
		avec le class "DrogDropTBody" sur tbody
	#######################################################	
	*/
  $("#SAVETABLE").click(function()
  {
  	var tbl;
  	var lignes = new Array();
  	var count=0;
  	alert("Option Enregistrer bientôt disponible");
  	$('#CORO tbody tr ').each(function() {
  		lignes[count]=$(this).text();
  		count++;
      tbl = tbl +"DEB:"+$(this).text()+"\n";
 		});
 		//alert(tbl);
  	
  });
}); // fin document ready

//===================================================
//onclick de semaine => envoyer vers week_all
//===================================================
$(function() {
	$("td.ui-datepicker-week-col").live("click", function() {         
		$(this).next().click();
		var cpt = 0;
		var bool = true;
		var currentDay;
		var currentMonth ;
		var currentYear  ;
		$(this).parent().children().each(function(){
			if(cpt>=1 && bool==true){
				if($(this).children().length > 0){
					currentDay	 = ($(this).children()[0].innerHTML);
					currentMonth = 	parseInt($(this).attr("data-month"))+1;
					currentYear  = 	parseInt($(this).attr("data-year"));
					bool=false;
				}
			}
			cpt++;
		});
		var fromDate = $("#cur_date").datepicker("getDate");
		var url ="./week.php?year="+currentYear+"&month="+currentMonth+"&day="+currentDay+"&area="+<?php print $area?>;
		location.href=url        
	 });
 });

</script>
<?php

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {

// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

// fin de la condition "Si format imprimable"
}

# Define the start of day and end of day (default is 7-7)
$am7=mktime(0,0,0,$month_week,$day_week,$year_week);
$pm7=mktime(23,59,0,$month,$day_week,$year_week);

# Start and end of week:
$week_midnight = mktime(0, 0, 0, $month_week, $day_week, $year_week);
$week_start = $am7;
$week_end = mktime($eveningends, $eveningends_minutes, 0, $month_week, $day_week+6, $year_week);
$this_service_name = "";
$this_room_name = "";

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    # Table with areas, rooms, minicals.
    echo "<table width=\"100%\" cellspacing=15><tr><td>\n";

    if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1)) {
        $area_list_format = $_SESSION['default_list_type'];
    } else {
        $area_list_format = getSettingValue("area_list_format");
    }

    # show either a select box or the normal html list
    if ($area_list_format != "list") {
        echo make_area_select_html('week_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
		echo make_room_select_html('week', $area, $room, $year, $month, $day);
    } else {
        echo "<table cellspacing=15><tr><td>\n";
        echo make_area_list_html('week_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        # Show all rooms in the current area
        echo "</td>\n<td>\n";
        make_room_list_html('week.php', $area, $room, $year, $month, $day);
        echo "</td>\n</tr>\n</table>\n";
    }
    echo "</td>\n";

    #Draw the three month calendars
    echo "</tr></table>\n";
}
$area_info = $Aghate->GetServiceInfoByServiceId($area);
$room_info = $Aghate->GetRoomInfoByRoomId($room);
$this_service_name = $area_info[0]['service_name'];
$this_room_name = $room_info['room_name'];
$this_room_name_des = $room_info['description'];
$this_statut_room = $room_info['statut_room'];
$this_moderate_room = $room_info['moderate'];
$this_delais_option_reservation = $room_info['delais_option_reservation'];


# Don't continue if this area has no rooms:
if ($room <= 0)
{
    echo "<h1>".get_vocab("no_rooms_for_area")."</h1>";
    include "./commun/include/trailer.inc.php";
    exit;
}


# Show area and room:
if (($this_room_name_des) and ($this_room_name_des!="-1")) {
    $this_room_name_des = " (".$this_room_name_des.")";
} else {
    $this_room_name_des = "";
}
// Les cellules "jours de semaine"
switch ($dateformat) {
    case "en":
    $dformat = "%A, %b&nbsp;%d";
    break;
    case "fr":
    $dformat = "%A %d&nbsp;%b";
    break;
}
echo "<h2 align=center><input type='hidden'  id='cur_date' name='cur_date'  value=".$cur_date." title='change dates' />".get_vocab("week").get_vocab("deux_points").utf8_strftime($dformat, $week_start)." - ". utf8_strftime($dformat, $week_end);
echo "<br />".ucfirst($this_service_name)." - $this_room_name $this_room_name_des\n";

if (verif_display_fiche_ressource(getUserName(), $room) and $_GET['pview'] != 1)
    echo "<A href='javascript:centrerpopup(\"view_room.php?id_room=$room\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\">
           <img src=\"./commun/images/details.png\" alt=\"détails\" border=\"0\" class=\"print_image\"  /></a>";
if (authGetUserLevel(getUserName(),$room) > 2 and $_GET['pview'] != 1)
    echo "<a href='admin_edit_room.php?room=$room'><img src=\"./commun/images/editor.png\" alt=\"configuration\" border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a>";


if ($this_statut_room == "0")
    echo "<br /><font color=\"#BA2828\">".get_vocab("ressource_temporairement_indisponible")."</font>";
if ($this_moderate_room == "1")
    echo "<br /><font color=\"#BA2828\">".get_vocab("reservations_moderees")."</font>";
echo "</h2>";


#y? are year, month and day of the previous week.
#t? are year, month and day of the next week.

$i= mktime(0,0,0,$month_week,$day_week-7,$year_week);
$yy = date("Y",$i);
$ym = date("m",$i);
$yd = date("d",$i);

$i= mktime(0,0,0,$month_week,$day_week+7,$year_week);
$ty = date("Y",$i);
$tm = date("m",$i);
$td = date("d",$i);

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    #Show Go to week before and after links
    echo "<table width=\"100%\"><tr><td>\n
      <a href=\"week.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area&amp;room=$room\">
      &lt;&lt; ".get_vocab("weekbefore")."</a></td>\n
      <td>&nbsp;</td>\n
      <td align=right><a href=\"week.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area&amp;room=$room\">
      ".get_vocab("weekafter")." &gt;&gt;</a></td>\n</tr></table>\n";
}
#Get all appointments for this week in the room that we care about
# row[0] = Start time
# row[1] = End time
# row[2] = Entry type
# row[3] = Entry name (brief description)
# row[4] = Entry ID
# row[5] = beneficiaire of the booking
# row[6] = status of the booking
# row[7] = Full description
# The range predicate (starts <= week_end && ends > week_start) is
# equivalent but more efficient than the original 3-BETWEEN clauses.
/*$sql = "SELECT start_time, end_time, type, name, id, 
		beneficiaire, statut_entry, description, option_reservation, 
		moderate, beneficiaire_ext
	   FROM agt_loc
	   WHERE room_id=$room
	   AND start_time < ".($week_end+$resolution)." AND end_time > $week_start ORDER BY start_time";*/
# Chaque tableau row retourné par la requête est une réservation.
# On construit alors un tableau de la forme :
# d[weekday][slot][x], où x = id, color, data.
# [slot] is based at 0 for midnight, but only slots within the hours of
# interest (morningstarts : eveningends) are filled in.
# [id] and [data] are only filled in when the meeting should be labeled,
# which is once for each meeting on each weekday.
# Note: weekday here is relative to the $weekstarts configuration variable.
# If 0, then weekday=0 means Sunday. If 1, weekday=0 means Monday.
$week_end_res = $week_end+$resolution;

$first_slot = 0;
$last_slot = count($periods_name)-1;

if ($debug_flag) echo "<br />DEBUG: query=$sql <br />first_slot=$first_slot - last_slot=$last_slot\n";

// On Load page charger les TypeResa(coleur)
$AllTypes = $Aghate->GetResrvationTypes($area);

 
foreach($AllTypes as $key )
{
	// attn colorcode est ajouté devant separateur
	$ListeTypeResa[]=$key['type_letter'].":".$tab_couleur[$key['color']]."|". $key['type_name'];
}
$res = $Aghate->GetWeekEntryInfo($room,$week_start,$week_end_res);
$nb_week_entry_info = count($res);
$row= $res;


if (! $res) echo grr_sql_error();
else for ($i = 0; $i<$nb_week_entry_info; $i++)
{
    if ($debug_flag)
        echo "<br />DEBUG: result $i, id ".$row[$i]['id'].", starts". $row[$i]['start_time']."
				(".affiche_date($row[$i]['start_time'])."), ends". $row[$i]['end_time'] ."(".affiche_date($row[$i]['end_time']).")\n";
    # Fill in slots for the meeting. Start at the meeting start time or
    # week start (which ever is later), and end one slot before the meeting
    # end time or week end (which ever is earlier).
    # Note: int casts on database rows for min and max is needed for PHP3.

    // Pour la réservation en cours, on détermine le début de la journée $debut_jour
    $month_current 	= date("m",$row[$i]['start_time']);
    $day_current 	= date("d",$row[$i]['start_time']);
    $year_current  	= date("Y",$row[$i]['start_time']);
    $debut_jour=mktime(0,0,0,$month_current,$day_current,$year_current);

    $t = max(round_t_down($row[$i]['start_time'], $resolution, $debut_jour), $week_start);
    $end_t = min((int)round_t_up((int)$row[$i]['end_time'],
                     (int)$resolution, $debut_jour),
                             (int)$week_end+1);
    $weekday = (date("w", $t) + 7 - $weekstarts) % 7;

    $prev_weekday = -1; # Invalid value to force initial label.
    $slot = $row[$i]['plage_pos'];
    do
    {
        if ($debug_flag) echo "<br />DEBUG: t=$t (".affiche_date($t)."), end_t=$end_t (".affiche_date($end_t)."), weekday=$weekday, slot=$slot\n";
		
        if ($slot < $first_slot)
        {
            # This is before the start of the displayed day; skip to first slot.
            $slot = $first_slot;
            $t = $weekday * 86400 + $am7;
            continue;
        }

        if ($slot <= $last_slot)
        {
            # This is within the working day; color it.
            # Only label it if it is the first time on this day:
            if ($prev_weekday != $weekday)
            {
                $prev_weekday = $weekday;
                if(strlen($row[$i]['noip'])>0){
					$d[$weekday][$slot]["data"] = $row[$i]['nom']." ".$row[$i]['prenom']." (".$row[$i]['noip']
													.")".$row[$i]['ddn'];
				}
				else{
					$d[$weekday][$slot]["data"] = $row[$i]['patient'];
				}
				$d[$weekday][$slot]["color"] = $row[$i]['type'];
                $d[$weekday][$slot]["id"] = $row[$i]['id'];
                $d[$weekday][$slot]["who"] = "";
                $d[$weekday][$slot]["statut"] = $row[$i]['statut_entry'];
                if (strlen($row[$i]['medecin'])>0){
					$med =$Aghate->GetInfoMedecinById($row[$i]['medecin']);
					$desc_medecin="Medecin : ". $med['nom']. " ". $med['prenom'];
 
                }else
					$desc_medecin="";
                $d[$weekday][$slot]["description"] = $desc_medecin." <br>".$Aghate->GetDescComplementaire($row[$i]['description']) ;
				//echo "slot".$slot;
            }
        }
        # Step to next time period and slot:
        $t += $resolution;
        $slot++;

        if ($slot > $last_slot)
        {
            # Skip to first slot of next day:
            $weekday++;
            $slot = $first_slot;
            $t = $weekday * 86400 + $am7;
        }
    } while ($t < $end_t);
}




if ($debug_flag)
{
    echo "<p>DEBUG:<p><pre>\n";
    if (gettype($d) == "array")
    while (list($w_k, $w_v) = each($d))
        while (list($t_k, $t_v) = each($w_v))
            while (list($k_k, $k_v) = each($t_v))
                echo "d[$w_k][$t_k][$k_k] = '$k_v'\n";
    else echo "d is not an array!\n";
    echo "</pre><p>\n";
}

#This is where we start displaying stuff
echo "<table cellspacing=0 border=1 width=\"100%\">";
// Affichage de la première ligne contenant le nom des jours (lundi, mardi, ...) et les dates ("10 juil", "11 juil", ...)
echo "<tr>\n<th width=\"5%\">&nbsp;</th>\n"; // Première cellule vide

$k=$day_week;
$i = $time;
$num_week_day = $weekstarts; // Pour le calcul des jours à afficher
for ($t = $week_start; $t <= $week_end; $t += 86400) {
    $jour_cycle = $Aghate->GetJour($i);
    if ($display_day[$num_week_day] == 1) {// on n'affiche pas tous les jours de la semaine
        echo "<th width=\"14%\">" . utf8_strftime($dformat, $t);
        if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
            if (intval($jour_cycle)>0)
                echo "<br />".get_vocab("rep_type_6")." ".$jour_cycle;
            else
                echo "<br />".$jour_cycle;

        echo "</th>\n";
    }
    if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
        $num_day = strftime("%d", $t);
        // Si le dernier dimanche d'octobre est dans la semaine, on avance d'une heure
        if  (heure_ete_hiver("hiver",$year,0) == mktime(0,0,0,$month,$num_day,$year))
            $t +=3600;
        if ((date("H",$t) == "13") or (date("H",$t) == "02"))
            $t -=3600;
    }
   	$i += 86400;
    $k++;
    $num_week_day++;// Pour le calcul des jours à afficher
    $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher
}
echo "<th width=\"5%\">&nbsp;</th>\n</tr>\n"; // Dernière cellule vide
// Fin de l'affichage de la première ligne

// Affichage de la deuxième ligne du tableau contenant l'intitulé "Journée" avec lien vers day.php
echo "<tr>\n";
tdcell("cell_hours");
if ($enable_periods=='y')
    echo get_vocab("period");
else
    echo get_vocab("time");
echo "</td>\n";
$num_week_day = $weekstarts;// Pour le calcul des jours à afficher
for ($t = $week_start; $t <= $week_end; $t += 86400)// Pour le calcul des jours à afficher
{
    if ($display_day[$num_week_day] == 1) {
    tdcell("cell_hours");
    $num_day = strftime("%d", $t);
    if ($_GET['pview'] != 1)
    {
        echo "<a title=\"".htmlspecialchars(get_vocab("see_all_the_rooms_for_the_day"))."\" href=\"day.php?year=$year&amp;month=$month&amp;day=$num_day&amp;area=$area\">".get_vocab("allday")."</a>";
    }
    echo "</td>\n";
    }
    if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
        // Si le dernier dimanche d'octobre est dans la semaine, on avance d'une heure
        if  (heure_ete_hiver("hiver",$year,0) == mktime(0,0,0,$month,$num_day,$year))
            $t +=3600;
        if ((date("H",$t) == "13") or (date("H",$t) == "02"))
            $t -=3600;

    }
    $num_week_day++;// Pour le calcul des jours à afficher
    $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher

}
tdcell("cell_hours");
if ($enable_periods=='y')
    echo get_vocab("period");
else
    echo get_vocab("time");
echo "</td>\n</tr>\n";
// Fin affichage de la deuxième ligne du tableau contenant l'intitulé "Journée" avec lien vers day.php


// Début affichage des lignes contenant les réservation
// Première boucle basée sur les créneaux de temps
// Deuxième boucle interne sur les jours de la semaine


# $t is the date/time for the first day of the week (Sunday, if $weekstarts=0).
# $wt is for the weekday in the inner loop.
$t = $am7;
$nb_case=0;
$semaine_changement_heure_ete = 'no';
$semaine_changement_heure_hiver = 'no';
for ($slot = $first_slot; $slot <= $last_slot; $slot++)
{
    # Show the time linked to the URL for highlighting that time:
    echo "<tr>";
    tdcell("cell_hours");
    if($enable_periods=='y'){
        $time_t = date("i", $t);
         $time_t_stripped = preg_replace( "/^0/", "", $time_t );
         echo $periods_name[$time_t_stripped] . "</td>\n";
    } else {
        echo date(hour_min_format(),$t) ."</td>\n";
    }
    $wt = $t;

    $empty_color = "empty_cell";

    # See note above: weekday==0 is day $weekstarts, not necessarily Sunday.
    $num_week_day = $weekstarts;// Pour le calcul des jours à afficher
    for ($weekday = 0; $weekday < 7; $weekday++)
    {
        # Three cases:
        # color:  id:   Slot is:   Color:    Link to:
        # -----   ----- --------   --------- -----------------------
        # unset   -     empty      white,red add new entry
        # set     unset used       by type   none (unlabelled slot)
        # set     set   used       by type   view entry

        $wday = date("d", $wt);
        $wmonth = date("m", $wt);
        $wyear = date("Y", $wt);
        $hour = date("H",$wt);
        $minute  = date("i",$wt);

        if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
            // Gestion du passage à l'heure d'été
            $temp =   mktime(0,0,0,$wmonth,$wday,$wyear);
            // On regarde s'il s'agit du dernier dimanche de mars
            if  (heure_ete_hiver("ete",$wyear,0) == $temp) {
                $semaine_changement_heure_ete = 'yes';
                $temp2 =   mktime($hour,0,0,$wmonth,$wday,$wyear);
                // 2 h du matin
                if  (heure_ete_hiver("ete", $wyear,2) == $temp2) {
                    // On insère une case vide
                    if ($display_day[$num_week_day] == 1)
                        echo tdcell($empty_color)."-</td>\n";
                    // On compte le nombre de cases insérées
                    $nb_case++;
                    $insere_case = 'y';
                // Après deux heures du matin, on avance d'une heure
                } else if  (heure_ete_hiver("ete", $wyear,2) < $temp2) {
                    $hour = date("H",$wt-3600);
                    $decale_slot = 1;
                    $insere_case = 'n';
                }
            // On regarde s'il s'agit du dernier dimanche d'octobre
            } else if  (heure_ete_hiver("hiver",$wyear,0) == $temp) {
                $semaine_changement_heure_hiver = 'yes';
                $temp2 =   mktime($hour,0,0,$wmonth,$wday,$wyear);
                // 2 h du matin
                if  (heure_ete_hiver("hiver", $wyear,2) == $temp2) {
                    // On compte le nombre de cases
                    $nb_case = $nb_case + 0.5;
                    // On n'insère pas de cellule
                    $insere_case = 'n';
                // Après deux heures du matin, on retarde d'une heure
                } else if  (heure_ete_hiver("hiver", $wyear,2) < $temp2) {
                    $hour = date("H",$wt+3600);
                    $decale_slot = -1;
                    $insere_case = 'n';
                }
            } else {
                $decale_slot = 0;
                $insere_case = 'n';
                // Dans le cas où on est dans une semaine de changement d'heure
                // Pour les jours qui suivent le dimanche, il faut continuer de décaler
                if (($semaine_changement_heure_ete == 'yes') and (heure_ete_hiver("ete",$wyear,0) < $temp)) {
                    $decale_slot = 1;
                    $hour = date("H",$wt-3600);
                }
                if (($semaine_changement_heure_hiver == 'yes') and (heure_ete_hiver("hiver",$wyear,0) < $temp)) {
                    $decale_slot = -1;
                    $hour = date("H",$wt+3600);
                }

            }
        } else {
            $decale_slot = 0;
            $insere_case = 'n';
        }
        // Fin gestion du passage à l'heure d'été

        if (($insere_case=='n') and ($display_day[$num_week_day] == 1)) {
        if(!isset($d[$weekday][$slot-$decale_slot*$nb_case]["color"])) // il s'agit d'un créneau libre
        {
            $date_booking = mktime($hour, $minute, 0, $wmonth, $wday, $wyear);
            if ($this_statut_room == "0") tdcell("avertissement"); else tdcell($empty_color);

            if (est_hors_reservation(mktime(0,0,0,$wmonth,$wday,$wyear)))
                echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
            else

            if (((authGetUserLevel(getUserName(),-1) > 1) or  (auth_visiteur(getUserName(),$room) == 1))
            and (UserRoomMaxBooking(getUserName(), $room, 1) != 0)
            and verif_booking_date(getUserName(), -1,$room, $date_booking, $date_now, $enable_periods)
            and verif_delais_max_resa_room(getUserName(), $room, $date_booking)
            and verif_delais_min_resa_room(getUserName(), $room, $date_booking)
            and (($this_statut_room == "1") or
              (($this_statut_room == "0") and (authGetUserLevel(getUserName(),$room) > 2) ))
            and $_GET['pview'] != 1) {
                echo "<center>";
                if ($enable_periods=='y') {
					$UrlView	= $ModuleReservationEdit."?room=$room&amp;area=$area&amp;plage_pos=$time_t_stripped&amp;year=$wyear&amp;month=$wmonth"
									."&amp;day=$wday&amp;table_loc=".$Aghate->NomTableLoc;
					echo "<a href='#?'  title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\"
							onClick=\"OpenPopupResa('".$UrlView."')\">"."<img src=\"./commun/images/new.png\" border=\"0\" 
									alt=\"".get_vocab("add")."\" width=\"16\" height=\"16\"  class=\"print_image\"  />".
							"</a>";
                } else {
					$UrlView	= $ModuleReservationEdit."?room=$room&amp;area=$area"
									."&amp;hour=$hour&amp;minute=$minute&amp;year=$wyear&amp;month=$wmonth"
									."&amp;day=$wday&amp;table_loc=".$Aghate->NomTableLoc;
                    
                    echo "<a href='#?'  title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\"
							onClick=\"OpenPopupResa('".$UrlView."')\">"."<img src=\"./commun/images/new.png\" border=\"0\" 
									alt=\"".get_vocab("add")."\" width=\"16\" height=\"16\"  class=\"print_image\"  />".
							"</a>";
                }
                echo "</center>";
            } else {
                echo "&nbsp;";
            }
        } else {
          if (est_hors_reservation(mktime(0,0,0,$wmonth,$wday,$wyear)))
            echo tdcell($empty_color)."<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
          else {
            tdcell($d[$weekday][$slot-$decale_slot*$nb_case]["color"]);
            // si la ressource est "occupée, on l'affiche
            //if ((isset($d[$weekday][$slot-$decale_slot*$nb_case]["statut"])) and ($d[$weekday][$slot-$decale_slot*$nb_case]["statut"]!='-')) echo "&nbsp;<img src=\"./commun/images/buzy.png\" alt=\"".get_vocab("reservation_en_cours")."\" title=\"".get_vocab("reservation_en_cours")."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
            // si la réservation est à confirmer, on le signale
            if (($this_delais_option_reservation > 0) and 
            (isset($d[$weekday][$slot-$decale_slot*$nb_case]["option_reser"])) and
             ($d[$weekday][$slot-$decale_slot*$nb_case]["option_reser"]!=-1)) 
					echo "&nbsp;<img src=\"./commun/images/small_flag.png\" 
					alt=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."\" 
					title=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."&nbsp;".time_date_string_jma($d[$weekday][$slot-$decale_slot*$nb_case]["option_reser"],$dformat)."\" 
					width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
            // si la réservation est à modérer, on le signale
            //if ((isset($d[$weekday][$slot-$decale_slot*$nb_case]["moderation"])) and ($d[$weekday][$slot-$decale_slot*$nb_case]["moderation"]=='1'))
               // echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" align=\"middle\" />&nbsp;\n";

            if (!isset($d[$weekday][$slot-$decale_slot*$nb_case]["id"])) {
                echo "<center><a href=\"$ModuleReservationEdit?room=$room&amp;area=$area"
                        . "&amp;plage_pos=$time_t_stripped&amp;year=$wyear&amp;month=$wmonth"
                        . "&amp;day=$wday&amp;page=week\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\">
                        <img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" width=\"16\" height=\"16\"  class=\"print_image\"  />
                    </a></center>";
            } else {
				//gestion couleurs
				$colclass=$d[$weekday][$slot-$decale_slot*$nb_case]['color'];
				if (($colclass >= "A") and ($colclass <= "Z")) {
					$res_couleur = $mysql->select("select couleur from agt_type_area where type_letter='".$colclass."'");
					$num_couleur=$res_couleur[0]['couleur'];
					$CurColeur=$tab_couleur[$num_couleur];
				}else{
					$CurColeur=$colclass;
				}
				//echo "curcoleur".$CurColeur;
                if (($this_statut_room == "1") or(($this_statut_room == "0") and (authGetUserLevel(getUserName(),$room) > 2) )){
					$UrlEdit	= $ModuleReservationEdit."?id=".$d[$weekday][$slot-$decale_slot*$nb_case]["id"]."&amp;table_loc=".$Aghate->NomTableLoc;	
					echo "<div style='background-color :".$CurColeur."'><font size=-2><a href='#?'  title=\"".htmlspecialchars($d[$weekday][$slot-$decale_slot*$nb_case]["who"])."\"
							onClick=\"OpenPopupResa('".$UrlEdit."')\">".htmlspecialchars($d[$weekday][$slot-$decale_slot*$nb_case]["data"]).
							"</a></font></div>";                        
                } else {
                    echo htmlspecialchars($d[$weekday][$slot-$decale_slot*$nb_case]["data"]);
                }
                if ($d[$weekday][$slot-$decale_slot*$nb_case]["description"]!= "")
                    echo "<br /><i>".$d[$weekday][$slot-$decale_slot*$nb_case]["description"]."</i>";
            }
          }
        }
        echo "</td>\n";
        }
        $wt += 86400;
        $num_week_day++;// Pour le calcul des jours à afficher
        $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher

    }
    // répétition de la première colonne
    tdcell("cell_hours");
    if($enable_periods=='y'){
        $time_t = date("i", $t);
         $time_t_stripped = preg_replace( "/^0/", "", $time_t );
         echo $periods_name[$time_t_stripped] . "</td>\n";
    } else {
        echo date(hour_min_format(),$t) . "</td>\n";
    }
    echo "</tr>\n";
    $t += $resolution;
}

// répétition de la première ligne
echo "<tr>\n<th>&nbsp;</th>\n";
$num_week_day = $weekstarts;
$i=$time;
for ($t = $week_start; $t <= $week_end; $t += 86400)
{
    $jour_cycle = $Aghate->GetJour($i);
    if ($display_day[$num_week_day] == 1) {// on n'affiche pas tous les jours de la semaine
        echo "<th width=\"14%\">" . utf8_strftime($dformat, $t);
        if (getSettingValue("jours_cycles_actif") == "Oui" and $jour_cycle>0)
			      echo "<br />".get_vocab("rep_type_6")." ".$jour_cycle;
        echo "</th>\n";
    }
    $k++;
    if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
        $num_day = strftime("%d", $t);
        // Si le dernier dimanche d'octobre est dans la semaine, on avance d'une heure
        if  (heure_ete_hiver("hiver",$year,0) == mktime(0,0,0,$month,$num_day,$year))
            $t +=3600;
        if ((date("H",$t) == "13") or (date("H",$t) == "02"))
            $t -=3600;

    }
   	$i += 86400;
    $num_week_day++;
    $num_week_day = $num_week_day % 7;
}
echo "<th>&nbsp;</th>\n</tr>\n";
// Fin Répétition de la première ligne


echo "</table>";

show_colour_key($area);

include "./commun/include/trailer.inc.php";
?>
