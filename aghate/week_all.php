<?php
#########################################################################
#                            week_all.php                               #
#    Permet l'affichage des réservation d'une semaine                   #
#              pour toutes les ressources d'un domaine.                 #
#             Dernière modification : 16/09/2006                        #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
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
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";


$grr_script_name = "week_all.php";
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

// On affiche le lien "format imprimable" en bas de la page
$affiche_pview = '1';
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

# Default parameters:
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

// Fonction de comparaison
// 3-value compare: Returns result of compare as "< " "= " or "> ".
function cmp3($a, $b)
{
    if ($a < $b) return "< ";
    if ($a == $b) return "= ";
    return "> ";
}

// On vérifie une fois par jour si le délai de confirmation des réservations est dépassé
// Si oui, les réservations concernées sont supprimées et un mail automatique est envoyé.
// On vérifie une fois par jour que les ressources ont été rendue en fin de réservation
// Si non, une notification email est envoyée
if (getSettingValue("verif_reservation_auto")==0) {
    verify_confirm_reservation();
    verify_retard_reservation();
}

# print the page header
print_header($day, $month, $year, $area, $type_session);
?>
<script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
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
	  prevText: '<Préc',
	  nextText: 'Suiv>',
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
		showCurrentAtPos: 1,
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
		var url ="./week_all.php?year="+currentYear+"&month="+currentMonth+"&day="+currentDay+"&area="+<?php print $area?>;
		location.href=url        
	 });
 });

</script>
<?php

// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

if (empty($area))
    $area = get_default_area();
if (empty($room))
    $room = $Aghate->GetMinRoomId($area);

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

if($enable_periods=='y') {
    $resolution = 60;
    $morningstarts = 12;
    $morningstarts_minutes = 0;
    $eveningends = 12;
    $eveningends_minutes = count($periods_name)-1;
}

$time = mktime(0, 0, 0, $month, $day, $year);
$time_old = $time;
// date("w", $time) : jour de la semaine en partant de dimancche
// date("w", $time) - $weekstarts : jour de la semaine en partant du jour défini dans GRR
// Si $day ne correspond pas au premier jour de la semaine tel que défini dans GRR,
// on recule la date jusqu'au précédent début de semaine
// Evidemment, problème possible avec les changement été-hiver et hiver-été
if (($weekday = (date("w", $time) - $weekstarts + 7) % 7) > 0){
    $time -= $weekday * 86400;
}
if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
    // Si le dimanche correspondant au changement d'heure est entre $time et $time_old, on corrige de +1 h ou -1 h.
    if  ((heure_ete_hiver("ete",$year,0) <= $time_old) and (heure_ete_hiver("ete",$year,0) >= $time) and ($time_old != $time) and (date("H", $time)== 23))
        $decal = 3600;
    else
        $decal = 0;
    $time += $decal;
}

// $day_week, $month_week, $year_week sont jours, semaines et années correspondant au premier jour de la semaine
$day_week   = date("d", $time);
$month_week = date("m", $time);
$year_week  = date("Y", $time);


//$date_start : date de début des réservation à extraire
$date_start = mktime($morningstarts,0,0,$month_week,$day_week,$year_week);

// Nombre de jours dans le mois
$days_in_month = date("t", $date_start);

if ($debug_flag)
    echo "$month_week $day_week ";

// $date_end : date de fin des réservation à extraire
$date_end = mktime($eveningends, $eveningends_minutes, 0, $month_week, $day_week+6, $year_week);

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    # Table with areas, rooms, minicals.
    echo "\n<table width=\"100%\" cellspacing=15><tr><td>\n";
    $this_service_name = "";
    $this_room_name = "";

    if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1)) {
        $area_list_format = $_SESSION['default_list_type'];
    } else {
        $area_list_format = getSettingValue("area_list_format");
    }

    # show either a select box or the normal html list
    if ($area_list_format != "list") {
        echo make_area_select_html('week_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
       // echo make_room_select_html('week', $area, "", $year, $month, $day);
    # echo make_room_select_html('month', $area, $room, $year, $month, $day);
    } else {
        echo "\n<table cellspacing=15><tr><td>\n";
        echo make_area_list_html('week_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        # Show all rooms in the current area
        echo "</td><td>";
        make_room_list_html('week.php', $area, "", $year, $month, $day);
        echo "</td></tr></table>";
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


$cur_date = date("d/m/Y",$date_start);

# Don't continue if this area has no rooms:
if ($room <= 0)
{
    echo "<h1>".get_vocab('no_rooms_for_area')."</h1>\n";
    include "./commun/include/trailer.inc.php";
    exit;
}

# Show Month, Year, Area, Room header:
if (($this_room_name_des) and ($this_room_name_des!="-1")) {
    $this_room_name_des = " (".$this_room_name_des.")";
} else {
    $this_room_name_des = "";
}
switch ($dateformat) {
    case "en":
    $dformat = "%A, %b %d %Y";
    break;
    case "fr":
    $dformat = "%A %d %b %Y";
    break;
}
 echo "<center><h2><input type='hidden'  id='cur_date' name='cur_date'  value=".$cur_date." title='change dates' />
	  ".get_vocab("week").get_vocab("deux_points").utf8_strftime($dformat, $date_start)." - ". utf8_strftime($dformat, $date_end)
	  ."<br /> $this_service_name - ".get_vocab("all_rooms")."</h2></center>\n";

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
    echo "\n<table width=\"100%\"><tr><td>
      <a href=\"week_all.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area&amp;room=$room\">
      &lt;&lt; ".get_vocab("weekbefore")."</a></td>
      <td>&nbsp;</td>
      <td align=right><a href=\"week_all.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area&amp;room=$room\">".
      get_vocab('weekafter')." &gt;&gt;</a></td></tr></table>";
}	

# Used below: localized "all day" text but with non-breaking spaces:
$all_day = preg_replace("# #", "&nbsp;", get_vocab("all_day"));

#Get all meetings for this month in the room that we care about


/*$sql = "SELECT start_time, end_time, agt_loc.id, name, beneficiaire, agt_room.id,type, 
statut_entry, agt_loc.description, agt_loc.option_reservation, agt_room.delais_option_reservation, 
agt_loc.moderate, beneficiaire_ext
   FROM agt_loc, agt_room, agt_service
   where
   agt_loc.room_id=agt_room.id and
   agt_service.id = agt_room.service_id and
   agt_service.id = '".$area."' and
   start_time <= $date_end AND
   end_time > $date_start
   ORDER by start_time, end_time, agt_loc.id";
*/
# Build an array of information about each day in the month.
# The information is stored as:
#  d[monthday]["id"][] = ID of each entry, for linking.
#  d[monthday]["data"][] = "start-stop" times of each entry.

# row[0] = Start time
# row[1] = End time
# row[2] = Entry ID
# row[3] =  name (brief description)
# row[4] = beneficiaire of the booking
# row[5] = room_id
# row[6] = type
# row[7] = status of the booking
# row[8] = Full description
# row[9] = option reservation
# row[10] = delais option reservation
# row[11] = moderate
# row[12] = beneficiaire_ext

$debug_flag = false;
if ($debug_flag) {
	echo $area;
	echo $date_start;
	echo "<br />". $date_end;
}

$res = $Aghate->GetWeekInfo($area,$date_start,$date_end);
$nb_week_info = count($res);
$row=$res;
//if (! $res) echo grr_sql_error();
for ($i = 0; $i<$nb_week_info; $i++)
{
    # Fill in data for each day during the month that this meeting covers.
    # Note: int casts on database rows for min and max is needed for PHP3.
    $t = max((int)$row[$i]['start_time'], $date_start);
    $end_t = min((int)$row[$i]['end_time'], $date_end);
    $day_num = date("j", $t);
    $month_num = date("m", $t);
    $year_num = date("Y", $t);
    if ($enable_periods == 'y')
        $midnight = mktime(12,0,0,$month_num,$day_num,$year_num);
    else
        $midnight = mktime(0, 0, 0, $month_num, $day_num, $year_num);
// bug changement heure été/hiver
//    $midnight2 = gmmktime(0, 0, 0, $month_num, $day_num, $year_num);

    if ($debug_flag)
        echo "<br />DEBUG: result $i, id" .$row[$i]['entry_id']."., starts ".$row[$i]['start_time'].", ends". $row[$i]['end_time'].", temps en heures : ".($row[$i]['end_time']- $row[$i]['start_time'])/(60*60).", midnight : $midnight \n";
    while ($t < $end_t)
    {
        if ($debug_flag) echo "<br />DEBUG: Entry". $row[$i]['entry_id'] ."day $day_num\n";
        $d[$day_num]["id"][] = $row[$i]['entry_id'];
        // Info-bulle
        /*if (getSettingValue("display_info_bulle") == 1)
           $d[$day_num]["who"][] = get_vocab("reservee au nom de").affiche_nom_prenom_email($row[4],$row[12],"nomail");
        else */if (getSettingValue("display_info_bulle") == 2)
           $d[$day_num]["who"][] = $row[$i]['description'];
        else
           $d[$day_num]["who"][] = "";
        $d[$day_num]["who1"][] = "(".$row[$i]['nda'].")\n".$row[$i]['nom']."\n".$row[$i]['prenom']."\n(".$row[$i]['noip']
													.")\n".$row[$i]['ddn']."\n(".$row[$i]['type'].")";
        $d[$day_num]["id_room"][]=$row[$i]['room_id'] ;
        $d[$day_num]["color"][]=$row[$i]['type'];
        $d[$day_num]["res"][] = $row[$i]['statut_entry'];
        $d[$day_num]["description"][] = affichage_resa_planning($row[$i]['description'],$row[$i]['entry_id']);;
        /*if ($row[$i]['delais_option_reservation'] > 0)
            $d[$day_num]["option_reser"][] = $row[$i][9];
        else*/
            $d[$day_num]["option_reser"][] = -1;
        //$d[$day_num]["moderation"][] = $row[11];
        $midnight_tonight = $midnight + 86400;
        if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
            // on s'arrange pour que l'heure $midnight_tonight corresponde à 0 h (00:00:00: )
            if  (heure_ete_hiver("hiver",$year_num,0) == mktime(0,0,0,$month_num,$day_num,$year_num))
                $midnight_tonight +=3600;
            if (date("H",$midnight_tonight) == "01")
                $midnight_tonight -=3600;
        }

        # Describe the start and end time, accounting for "all day"
        # and for entries starting before/ending after today.
        # There are 9 cases, for start time < = or > midnight this morning,
        # and end time < = or > midnight tonight.
        # Use ~ (not -) to separate the start and stop times, because MSIE
        # will incorrectly line break after a -.
        if ($enable_periods == 'y') {
              $start_str = preg_replace("# #", "&nbsp;", period_time_string($row[$i]['start_time']));
              $end_str   = preg_replace("# #", "&nbsp;", period_time_string($ro[$i]['end_time'], -1));
              // Debug
              //echo affiche_date($row[0])." ".affiche_date($midnight)." ".affiche_date($row[1])." ".affiche_date($midnight_tonight)."<br />";
              switch (cmp3($row[$i]['start_time'], $midnight) . cmp3($row[$i]['end_time'], $midnight_tonight))
              {
            case "> < ":         # Starts after midnight, ends before midnight
            case "= < ":         # Starts at midnight, ends before midnight
                    if ($start_str == $end_str)
                        $d[$day_num]["data"][] = $start_str;
                    else
                        $d[$day_num]["data"][] = $start_str . "~" . $end_str;
                    break;
            case "> = ":         # Starts after midnight, ends at midnight
                    $d[$day_num]["data"][] = $start_str . "~24:00";
                    break;
            case "> > ":         # Starts after midnight, continues tomorrow
                    $d[$day_num]["data"][] = $start_str . "~====&gt;";
                    break;
            case "= = ":         # Starts at midnight, ends at midnight
                    $d[$day_num]["data"][] = $all_day;
                    break;
            case "= > ":         # Starts at midnight, continues tomorrow
                    $d[$day_num]["data"][] = $all_day . "====&gt;";
                    break;
            case "< < ":         # Starts before today, ends before midnight
                    $d[$day_num]["data"][] = "&lt;====~" . $end_str;
                    break;
            case "< = ":         # Starts before today, ends at midnight
                    $d[$day_num]["data"][] = "&lt;====" . $all_day;
                    break;
            case "< > ":         # Starts before today, continues tomorrow
                    $d[$day_num]["data"][] = "&lt;====" . $all_day . "====&gt;";
                    break;
              }
        } else {
          switch (cmp3($row[$i]['start_time'], $midnight) . cmp3($row[$i]['end_time'], $midnight_tonight))
          {
            case "> < ":         # Starts after midnight, ends before midnight
            case "= < ":         # Starts at midnight, ends before midnight
                $d[$day_num]["data"][] = date(hour_min_format(), $row[$i]['start_time']) . "~" . date(hour_min_format(), $row[$i]['end_time']);
                break;
            case "> = ":         # Starts after midnight, ends at midnight
                $d[$day_num]["data"][] = date(hour_min_format(), $row[$i]['start_time']) . "~24:00";
                break;
            case "> > ":         # Starts after midnight, continues tomorrow
                $d[$day_num]["data"][] = date(hour_min_format(), $row[$i]['start_time']) . "~===&gt;";
                break;
            case "= = ":         # Starts at midnight, ends at midnight
                $d[$day_num]["data"][] = $all_day;
                break;
            case "= > ":         # Starts at midnight, continues tomorrow
                $d[$day_num]["data"][] = $all_day . "====&gt;";
                break;
            case "< < ":         # Starts before today, ends before midnight
                $d[$day_num]["data"][] = "&lt;====~" . date(hour_min_format(), $row[$i]['end_time']);
                break;
            case "< = ":         # Starts before today, ends at midnight
                $d[$day_num]["data"][] = "&lt;====" . $all_day;
                break;
            case "< > ":         # Starts before today, continues tomorrow
                $d[$day_num]["data"][] = "&lt;====" . $all_day . "====&gt;";
                break;
          }
        }

        # Only if end time > midnight does the loop continue for the next day.
        if ($row[$i]['end_time'] <= $midnight_tonight) break;

        $t = $midnight = $midnight_tonight;
        $day_num = date("j", $t);
    }
}
$debug_flag = false;
if ($debug_flag) {
	echo "<pre>";
	print_r($d);
	echo "</pre>";
}
if ($debug_flag)
{
    echo "<p>DEBUG: Array of month day data:<p><pre>\n";
    for ($i = 1; $i <= $days_in_month; $i++)
    {
        if (isset($d[$i]["id"]))
        {
            $n = count($d[$i]["id"]);
            echo "Day $i has $n entries:\n";
            for ($j = 0; $j < $n; $j++)
                echo "  ID: " . $d[$i]["id"][$j] .
                    " Data: " . $d[$i]["data"][$j] . "\n";
        }
    }
    echo "</pre>\n";
}

echo "<table cellspacing=0 border=1 width=\"100%\"><tr>";

# We need to know what all the rooms area called, so we can show them all
# pull the data from the db and store it. Convienently we can print the room
# headings and capacities at the same time

//sql = "select room_name, capacity, id, description, statut_room from agt_room where service_id='".$area."' order by order_display, room_name";
$room_info = $Aghate->GetRoomsByServiceId($area);
$nb_info = count($room_info);
$row= $room_info;
$debug_flag = false;

if ($debug_flag){
	echo "ROW : ";
	echo "<pre>";
	print_r($row);
	echo "</pre>";
}
	
# It might be that there are no rooms defined for this area.
# If there are none then show an error and dont bother doing anything
# else
if (! $row) fatal_error(0, grr_sql_error());
if ($nb_info == 0)
{
    echo "<h1>".get_vocab("no_rooms_for_area")."</h1>";
    grr_sql_free($row);
} else {
    // Affichage de la première ligne contenant le nom des jours (lundi, mardi, ...) et les dates ("10 juil", "11 juil", ...)
    echo "<th width=\"10%\">&nbsp;</th>\n"; // Première cellule vide
    $t = $time;
    $num_week_day = $weekstarts; // Pour le calcul des jours à afficher
    for ($weekcol = 0; $weekcol < 7; $weekcol++)
    {
        $num_day = strftime("%d", $t);
        $temp_month = strftime("%m", $t);
        $temp_month2 = strftime("%b", $t);
        $temp_year = strftime("%Y", $t);
        $jour_cycle = $Aghate->GetJour($t);//grr_sql_query1("SELECT Jours FROM agt_calendrier_jours_cycle WHERE DAY='$t'");
        $t += 86400;
        if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
            // Correction dans le cas d'un changement d'heure
            if  (heure_ete_hiver("hiver",$temp_year,0) == mktime(0,0,0,$temp_month,$num_day,$temp_year))
                $t +=3600;
            if (date("H",$t) == "01")
                $t -=3600;
        }
        if ($display_day[$num_week_day] == 1) {// on n'affiche pas tous les jours de la semaine
            echo "<th width=\"10%\">" . day_name(($weekcol + $weekstarts)%7) . " ".$num_day. " ".$temp_month2;
            if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
                if (intval($jour_cycle)>0)
                    echo "<br />".get_vocab("rep_type_6")." ".$jour_cycle;
                else
                    echo "<br />".$jour_cycle;
            echo "</th>\n";
        }
        $num_week_day++;// Pour le calcul des jours à afficher
        $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher
    }
    echo "</tr>";
    // Fin Affichage de la première ligne contenant les jours
    // Affichage de la deuxième ligne contenant un lien "journée"
    if ($_GET['pview'] != 1)
    {
      echo "<tr>";
      echo tdcell("cell_hours", 12)."<b>".get_vocab("rooms")."</b></td>\n"; // Première cellule
      $t = $time;
      $num_week_day = $weekstarts; // Pour le calcul des jours à afficher
      for ($weekcol = 0; $weekcol < 7; $weekcol++)
      {
        $num_day = strftime("%d", $t);
        $temp_month = strftime("%m", $t);
        $temp_year = strftime("%Y", $t);
        $t += 86400;
        if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
            // Correction dans le cas d'un changement d'heure
            if  (heure_ete_hiver("hiver",$temp_year,0) == mktime(0,0,0,$temp_month,$num_day,$temp_year))
                $t +=3600;
            if (date("H",$t) == "01")
                $t -=3600;
        }
        if ($display_day[$num_week_day] == 1) // on n'affiche pas tous les jours de la semaine
            echo tdcell("cell_hours", 12.5)."<a title=\"".htmlspecialchars(get_vocab("see_all_the_rooms_for_the_day"))."\" href='day.php?year=".$temp_year."&amp;month=".$temp_month."&amp;day=".$num_day."&amp;area=".$area."'>" . get_vocab("allday")."</a></td>\n";
        $num_week_day++;// Pour le calcul des jours à afficher
        $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher
      }
      echo "</tr>\n";
    }
    // Fin Affichage de la deuxième ligne contenant les jours

  $li=0;
  // Boucle sur les ressources
  for ($ir = 0; $ir<$nb_info; $ir++)
    {
    // Affichage de la première colonne (nom des ressources)
    echo "<tr>\n";
    echo tdcell("cell_hours","5")."<a title=\"".htmlspecialchars(get_vocab("see_week_for_this_room"))."\" href='week.php?year=".$year."&amp;month=".$month."&amp;day=".$day."&amp;room=".$row[$ir]['id']."'>" . htmlspecialchars($row[$ir]['room_alias']) ."</a><br />\n";
    if (verif_display_fiche_ressource(getUserName(), $row[$ir]['id']) and $_GET['pview'] != 1)
        echo "<a href='javascript:centrerpopup(\"view_room.php?id_room=".$row[$ir]['id']."\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' \" title=\"".get_vocab("fiche_ressource")."\">
        <img src=\"./commun/images/details.png\" alt=\"Détails\" border=\"0\" class=\"print_image\"  /></a>";
  	if (authGetUserLevel(getUserName(),$row[$ir]['id']) > 2 and $_GET['pview'] != 1)
        echo "<a href='admin_edit_room.php?room=".$row[$ir]['id']."'><img src=\"./commun/images/editor.png\" alt=\"configuration\" border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a></td>";


    $li++;

    $t = $time;
    $t2 = $time;
    $num_week_day = $weekstarts; // Pour le calcul des jours à afficher
    for ($k = 0; $k<=6; $k++)
      {
        $cday = date("j", $t2);
        $cmonth = strftime("%m", $t2);
        $cyear = strftime("%Y", $t2);

        $t2 += 86400;
        if (!isset($correct_heure_ete_hiver) or ($correct_heure_ete_hiver == 1)) {
            // Correction dans le cas d'un changement d'heure
            $temp_day = strftime("%d", $t2);
            $temp_month = strftime("%m", $t2);
            $temp_year = strftime("%Y", $t2);
            // on s'arrange pour que l'heure $t2 corresponde à 0 h (00:00:00: )
            if  (heure_ete_hiver("hiver",$temp_year,0) == mktime(0,0,0,$temp_month,$temp_day,$temp_year))
                $t2 +=3600;
            if (date("H",$t2) == "01")
                $t2 -=3600;

        }
        if ($display_day[$num_week_day] == 1) { // condition "on n'affiche pas tous les jours de la semaine"
        echo "<td style=\"vertical-align: middle;\" class=\"cell_month\">";
        # Anything to display for this day?
       
        if ((isset($d[$cday]["id"][0])) /*and  !(est_hors_reservation(mktime(0,0,0,$cmonth,$cday,$cyear)))*/) {
            $n = count($d[$cday]["id"]);
            
            # Show the start/stop times, 2 per line, linked to view_entry.
            # If there are 12 or fewer, show them, else show 11 and "...".
            for ($i = 0; $i < $n; $i++) {
                /*if ($i == 11 && $n > 12) {
                    echo " ...\n";
                    break;
                } */
                
                if ($d[$cday]["id_room"][$i]==$row[$ir]['id']) {
                    #if ($i > 0 && $i % 2 == 0) echo "<br />"; else echo " ";
                    echo "\n<table width='100%' border='0'><tr>";
						tdcell($d[$cday]["color"][$i]);
						//if ($d[$cday]["res"][$i]!='-')
						  // echo "&nbsp;<img src=\"./commun/images/buzy.png\" alt=\"".get_vocab("reservation_en_cours")."\" title=\"".get_vocab("reservation_en_cours")."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
						// si la réservation est à confirmer, on le signale
						if ((isset($d[$cday]["option_reser"][$i])) and ($d[$cday]["option_reser"][$i]!=-1)) 
							echo "&nbsp;<img src=\"./commun/images/small_flag.png\" alt=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."\" 
								title=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."&nbsp;".time_date_string_jma($d[$cday]["option_reser"][$i],$dformat)."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
						// si la réservation est à modérer, on le signale
						if ((isset($d[$cday]["moderation"][$i])) and ($d[$cday]["moderation"][$i]==1))
							echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" align=\"middle\"/>&nbsp;\n";

						echo "<font size=-2><b>". $d[$cday]["data"][$i]
						. "<br /></b>"
						. "<a title=\"".htmlspecialchars($d[$cday]["who"][$i])."\" href=\"view_entry.php?id=" . $d[$cday]["id"][$i]."&amp;page=week_all&amp;area=$area&amp;day=$cday&amp;month=$cmonth&amp;year=$cyear&amp;\">"
						. htmlspecialchars($d[$cday]["who1"][$i])
						. "</a></font>";
						if ($d[$cday]["description"][$i]!= "")
							echo "<br /><i>".$d[$cday]["description"][$i]."</i>";
						
                    echo "</td></table>";
                }
            }
        }
        //  Possibilité de faire une nouvelle réservation
        $hour = date("H",$date_now); // Heure actuelle
        $date_booking = mktime(24, 0, 0, $cmonth, $cday, $cyear); // minuit
        echo "<center>";
        //if (est_hors_reservation(mktime(0,0,0,$cmonth,$cday,$cyear)))
           // echo "<img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  />";
       // else
            if (((authGetUserLevel(getUserName(),-1) > 1) or (auth_visiteur(getUserName(),$row[$ir]['id']) == 1))
            and (UserRoomMaxBooking(getUserName(), $row[$ir]['id'], 1) != 0)
            and verif_booking_date(getUserName(), -1, $row[$ir]['id'], $date_booking, $date_now, $enable_periods)
            and verif_delais_max_resa_room(getUserName(), $row[$ir]['id'], $date_booking)
            and verif_delais_min_resa_room(getUserName(), $row[$ir]['id'], $date_booking)
            and plages_libre_semaine_ressource($row[$ir]['id'], $cmonth, $cday, $cyear)
            and (($row[$ir]['statut_room'] == "1") or
              (($row[$ir]['statut_room'] == "0") and (authGetUserLevel(getUserName(),$room) > 2) ))) {
                if ($enable_periods == 'y')
                    echo "<a href=\"edit_entry.php?area=$area&amp;room=".$row[$ir]['id']."&amp;period=&amp;year=$cyear&amp;month=$cmonth&amp;day=$cday&amp;page=week_all\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\"><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" class=\"print_image\"  /></a>";
                else
                    echo "<a href=\"edit_entry.php?area=$area&amp;room=".$row[$ir]['id']."&amp;hour=$hour&amp;minute=0&amp;year=$cyear&amp;month=$cmonth&amp;day=$cday&amp;page=week_all\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\"><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" class=\"print_image\"  /></a>";
            } else {
                echo "&nbsp;";
            }
        echo "</center>";
        echo "</td>\n";
        }  // Fin de la condition "on n'affiche pas tous les jours de la semaine"
        $num_week_day++;// Pour le calcul des jours à afficher
        $num_week_day = $num_week_day % 7;// Pour le calcul des jours à afficher

      }
      echo "</tr>";
    }
}
echo "</table>\n";
show_colour_key($area);

$link="print_week_a4.php?area=".$area."&am7=".$date_start."&pm7=".$date_end;
echo "<table align='center'><tr><td align='left'><a href='#'  onClick=\"OpenPopup('".mysql_real_escape_string($link)."')\" > Format imprimable en A4 </a></td></tr></table>";

include "./commun/include/trailer.inc.php";
?>
