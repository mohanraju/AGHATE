<?php
#########################################################################
#                            month_all.php                              #
#                                                                       #
#            Interface d'accueil avec affichage par mois                #
#             des réservation de toutes les ressources d'un domaine     #
#            Dernière modification : 10/07/2006                         #
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
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
include "./commun/include/mrbs_sql.inc.php";
$grr_script_name = "month_all.php";
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
if (empty($month) || empty($year) || !checkdate($month, 1, $year))
{
    $month = date("m");
    $year  = date("Y");
}
if (!isset($day)) $day = 1;

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
} else {
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}
$back = "";
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ($type_session == "with_session") $_SESSION['type_month_all'] = "month_all";
$type_month_all='month_all';

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

# 3-value compare: Returns result of compare as "< " "= " or "> ".
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

// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

if (empty($area))
    $area = get_default_area();
if (empty($room))
    $room = grr_sql_query1("select min(id) from agt_room where service_id=$area");
# Note $room will be -1 if there are no rooms; this is checked for below.

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

# Month view start time. This ignores morningstarts/eveningends because it
# doesn't make sense to not show all entries for the day, and it messes
# things up when entries cross midnight.
$month_start = mktime(0, 0, 0, $month, 1, $year);

# What column the month starts in: 0 means $weekstarts weekday.
$weekday_start = (date("w", $month_start) - $weekstarts + 7) % 7;

$days_in_month = date("t", $month_start);

$month_end = mktime(23, 59, 59, $month, $days_in_month, $year);

if ($enable_periods=='y') {
    $resolution = 60;
    $morningstarts = 12;
    $eveningends = 12;
    $eveningends_minutes = count($periods_name)-1;
}

$this_service_name = "";
$this_room_name = "";

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    #Table avec areas, rooms, minicals.
    echo "<table width=\"100%\" cellspacing=\"15\" border=\"0\"><tr><td>";

    if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1)) {
        $area_list_format = $_SESSION['default_list_type'];
    } else {
        $area_list_format = getSettingValue("area_list_format");
    }

    # show either a select box or the normal html list
    if ($area_list_format != "list") {
        echo make_area_select_html('month_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        echo make_room_select_html('month', $area, "", $year, $month, $day);
    } else {
        echo "<table cellspacing=15><tr><td>";
        echo make_area_list_html('month_all.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        #Montre toutes les rooms du domaine affiché
        echo "</td><td>";
        make_room_list_html('month.php', $area, "", $year, $month, $day);
        echo "</td></tr></table>";
    }
    echo "</td>\n";

    #Draw the three month calendars
    minicals($year, $month, $day, $area, $room, 'month_all');
    echo "</tr></table>\n";
}

$this_service_name = grr_sql_query1("select service_name from agt_service where id=$area");
$this_room_name = grr_sql_query1("select room_name from agt_room where id=$room");
$this_room_name_des = grr_sql_query1("select description from agt_room where id=$room");

# Don't continue if this area has no rooms:
if ($room <= 0)
{
    echo "<h1>".get_vocab("no_rooms_for_area")."</h1>";
    include "./commun/include/trailer.inc.php";
    exit;
}

# Show Month, Year, Area, Room header:
if (($this_room_name_des) and ($this_room_name_des!="-1")) {
    $this_room_name_des = " (".$this_room_name_des.")";
} else {
    $this_room_name_des = "";
}

 echo "<h2 align=center>" . ucfirst(utf8_strftime("%B %Y", $month_start))
  . "<br />".ucfirst($this_service_name)." - ".get_vocab("all_areas");
 if ($_GET['pview'] != 1) echo " <a href=\"month_all2.php?year=$year&amp;month=$month&amp;area=$area\"><img src=\"./commun/images/change_view.png\" alt=\"".get_vocab("change_view")."\" title=\"".get_vocab("change_view")."\" border=\"0\" /></a>";
 echo "</h2>\n";

# Show Go to month before and after links
#y? are year and month of the previous month.
#t? are year and month of the next month.

$i= mktime(0,0,0,$month-1,1,$year);
$yy = date("Y",$i);
$ym = date("n",$i);

$i= mktime(0,0,0,$month+1,1,$year);
$ty = date("Y",$i);
$tm = date("n",$i);
// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    echo "<table width=\"100%\"><tr><td>
      <a href=\"month_all.php?year=$yy&amp;month=$ym&amp;area=$area&amp;room=$room\">
      &lt;&lt; ".get_vocab("monthbefore")."</a></td>
      <td>&nbsp;</td>
      <td align=right><a href=\"month_all.php?year=$ty&amp;month=$tm&amp;area=$area&amp;room=$room\">
      ".get_vocab("monthafter")." &gt;&gt;</a></td></tr></table>";
}

if ($debug_flag)
    echo "<p>DEBUG: month=$month year=$year start=$weekday_start range=$month_start:$month_end\n";

# Used below: localized "all day" text but with non-breaking spaces:
$all_day = ereg_replace(" ", "&nbsp;", get_vocab("all_day"));

#Get all meetings for this month in the room that we care about
# row[0] = Start time
# row[1] = End time
# row[2] = Entry ID
# row[3] = Entry name (brief description)
# row[4] = beneficiaire of the booking
# row[5] = Nom de la ressource
# row[6] = Description complète
# row[7] = type
# row[8] = Modération
# row[9] = beneficiaire extérieur
$sql = "SELECT start_time, end_time, agt_loc.id, name, beneficiaire, room_name, agt_loc.description, type, agt_loc.moderate, beneficiaire_ext
   FROM agt_loc inner join agt_room on agt_loc.room_id=agt_room.id
   WHERE (start_time <= $month_end AND end_time > $month_start and service_id='".$area."')
   ORDER by start_time, end_time, agt_room.room_name";

# Build an array of information about each day in the month.
# The information is stored as:
#  d[monthday]["id"][] = ID of each entry, for linking.
#  d[monthday]["data"][] = "start-stop" times of each entry.

$res = grr_sql_query($sql);
if (! $res) echo grr_sql_error();
else for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    if ($debug_flag)
        echo "<br />DEBUG: result $i, id $row[2], starts $row[0], ends $row[1]\n";

    # Fill in data for each day during the month that this meeting covers.
    # Note: int casts on database rows for min and max is needed for PHP3.
    $t = max((int)$row[0], $month_start);
    $end_t = min((int)$row[1], $month_end);
    $day_num = date("j", $t);
    if ($enable_periods == 'y')
        $midnight = mktime(12,0,0,$month,$day_num,$year);
    else
        $midnight = mktime(0, 0, 0, $month, $day_num, $year);
    while ($t < $end_t)
    {
        if ($debug_flag) echo "<br />DEBUG: Entry $row[2] day $day_num\n";
        $d[$day_num]["id"][] = $row[2];
        // Info-bulle
        if (getSettingValue("display_info_bulle") == 1)
            $d[$day_num]["who"][] = get_vocab("reservee au nom de").affiche_nom_prenom_email($row[4],$row[9],"nomail");
        else if (getSettingValue("display_info_bulle") == 2)
            $d[$day_num]["who"][] = $row[6];
        else
            $d[$day_num]["who"][] = "";

        $d[$day_num]["who1"][] = affichage_lien_resa_planning($row[3],$row[2]);
        $d[$day_num]["room"][]=$row[5] ;
        $d[$day_num]["color"][] = $row[7];
        $d[$day_num]["description"][] = affichage_resa_planning($row[6],$row[2]);
        $d[$day_num]["moderation"][] = $row[8];

        $midnight_tonight = $midnight + 86400;

        # Describe the start and end time, accounting for "all day"
        # and for entries starting before/ending after today.
        # There are 9 cases, for start time < = or > midnight this morning,
        # and end time < = or > midnight tonight.
        # Use ~ (not -) to separate the start and stop times, because MSIE
        # will incorrectly line break after a -.
        if ($enable_periods == 'y') {
            $start_str = ereg_replace(" ", "&nbsp;", period_time_string($row[0]));
            $end_str   = ereg_replace(" ", "&nbsp;", period_time_string($row[1], -1));
            switch (cmp3($row[0], $midnight) . cmp3($row[1], $midnight_tonight))
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
          switch (cmp3($row[0], $midnight) . cmp3($row[1], $midnight_tonight))
          {
            case "> < ":         # Starts after midnight, ends before midnight
            case "= < ":         # Starts at midnight, ends before midnight
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~" . date(hour_min_format(), $row[1]);
                break;
            case "> = ":         # Starts after midnight, ends at midnight
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~24:00";
                break;
            case "> > ":         # Starts after midnight, continues tomorrow
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~====&gt;";
                break;
            case "= = ":         # Starts at midnight, ends at midnight
                $d[$day_num]["data"][] = $all_day;
                break;
            case "= > ":         # Starts at midnight, continues tomorrow
                $d[$day_num]["data"][] = $all_day . "====&gt;";
                break;
            case "< < ":         # Starts before today, ends before midnight
                $d[$day_num]["data"][] = "&lt;====~" . date(hour_min_format(), $row[1]);
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
        if ($row[1] <= $midnight_tonight) break;
        $day_num++;
        $t = $midnight = $midnight_tonight;
    }
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

// Début du tableau affichant le planning
echo "<table border=2 width=\"100%\">\n";

// Début affichage première ligne (intitulé des jours)
echo "<tr>";
for ($weekcol = 0; $weekcol < 7; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
        echo "<th width=\"14%\">" . day_name($num_week_day) . "</th>\n";
}
echo "</tr>\n";
// Fin affichage première ligne (intitulé des jours)


// Début affichage des lignes affichant les réservations
echo "<tr>\n";

// On grise les cellules appartenant au mois précédent
for ($weekcol = 0; $weekcol < $weekday_start; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
        echo "<td class=\"cell_month_o\" height=100>&nbsp;</td>\n";
}

// Début Première boucle sur les jours du mois
for ($cday = 1; $cday <= $days_in_month; $cday++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    $t=mktime(0,0,0,$month,$cday,$year);
    $name_day = ucfirst(utf8_strftime("%d", $t));
	$jour_cycle = grr_sql_query1("SELECT Jours FROM agt_calendrier_jours_cycle WHERE DAY='$t'");
    if ($weekcol == 0) echo "</tr><tr>\n";
    if ($display_day[$num_week_day] == 1) {// début condition "on n'affiche pas tous les jours de la semaine"
    echo "<td valign=top height=100 class=\"cell_month\">";
    // On affiche les jours du mois dans le coin supérieur gauche de chaque cellule
    echo "<div class=\"monthday\"><a title=\"".htmlspecialchars(get_vocab("see_all_the_rooms_for_the_day"))."\"   href=\"day.php?year=$year&amp;month=$month&amp;day=$cday&amp;area=$area\">".$name_day;
	    if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
        if (intval($jour_cycle)>0)
            echo " - ".get_vocab("rep_type_6")." ".$jour_cycle;
        else
            echo " - ".$jour_cycle;

	echo "</a></div>\n";
    if (est_hors_reservation(mktime(0,0,0,$month,$cday,$year)))
            echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
    else {

     // Des réservation à afficher pour ce jour ?
     if (isset($d[$cday]["id"][0]))
     {
        echo "<font size=-2>";
        $n = count($d[$cday]["id"]);
        # Show the start/stop times, 2 per line, linked to view_entry.
        # If there are 12 or fewer, show them, else show 11 and "...".
        for ($i = 0; $i < $n; $i++)
        {
            if ($i == 11 && $n > 12)
            {
                echo " ...\n";
                break;
            }
            echo span_bgground($d[$cday]["color"][$i]);
            echo "". $d[$cday]["data"][$i]
                . "<br /><i><b>"
                . htmlspecialchars($d[$cday]["room"][$i])
                . "</b></i><br />";
           // si la réservation est à modérer, on le signale
           if ((isset($d[$cday]["moderation"][$i])) and ($d[$cday]["moderation"][$i]==1))
               echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" align=\"middle\" />&nbsp;\n";

          echo "<a title=\"".htmlspecialchars($d[$cday]["who"][$i])."\" href=\"view_entry.php?id=" . $d[$cday]["id"][$i]
          . "&amp;day=$cday&amp;month=$month&amp;year=$year&amp;page=month_all\">"
          . htmlspecialchars($d[$cday]["who1"][$i])
          . "</a>";
          if ($d[$cday]["description"][$i]!= "")
              echo "<br /><i>(".$d[$cday]["description"][$i].")</i>";
          echo "</span><br /><br />";
        }
        echo "</font>";
     }
    }
    echo "</td>\n";
    } // fin condition "on n'affiche pas tous les jours de la semaine"
    if (++$weekcol == 7) $weekcol = 0;
}
// Fin Première boucle sur les jours du mois

// On grise les cellules appartenant au mois suivant
if ($weekcol > 0) for (; $weekcol < 7; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
        echo "<td class=\"cell_month_o\" height=100>&nbsp;</td>\n";
}
echo "</tr></table>\n";
show_colour_key($area);
include "./commun/include/trailer.inc.php";
?>
