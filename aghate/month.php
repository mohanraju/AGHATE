<?php
#########################################################################
#                            month.php                                  #
#                                                                       #
#            Interface d'accueil avec affichage par mois                #
#            Dernière modification : 09/12/2006                         #
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
$grr_script_name = "month.php";
    #Settings
require_once("./commun/include/settings.inc.php");
        #Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");

    #Fonction relative à la session
require_once("./commun/include/session.inc.php");
    #Si il n'y a pas de session crée et que l'identification est requise, on déconnecte l'utilisateur.
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1))
{
    header("Location: ./logout.php?auto=1");
    die();
};

if (empty($area))
    $area = get_default_area();
if (empty($room))
    $room = grr_sql_query1("select min(id) from agt_room where service_id=$area");
    #Si il n'y a pas de room, $room va être a -1

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Paramètres langage
include "./commun/include/language.inc.php";

// On affiche le lien "format imprimable" en bas de la page
$affiche_pview = '1';
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

    #Paramètres par défaut
if (empty($debug_flag)) $debug_flag = 0;
if (empty($month) || empty($year) || !checkdate($month, 1, $year))
{
    $month = date("m");
    $year  = date("Y");
}
$day = 1;
    #Renseigne la session de l'utilisateur, sans identification ou avec identification.
if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login'])))
{
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
}
else
{
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}
    #Récupération des informations relatives au serveur.
$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
    #Renseigne les droits de l'utilisateur, si les droits sont insufisants, l'utilisateur est avertit.
if (check_begin_end_bookings($day, $month, $year))
{
    showNoBookings($day, $month, $year, $area,$back,$type_session );
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
    #Fonction de comparaison, retourne "<" "=" ou ">"
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

    #Affiche les informations dans l'header
print_header($day, $month, $year, $area, $type_session);
// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

    #Heure de dénut du mois, cela ne sert à rien de reprndre les valeur morningstarts/eveningends
$month_start = mktime(0, 0, 0, $month, 1, $year);
    #Dans quel colonne l'affichage commence: 0 veut dire $weekstarts
$weekday_start = (date("w", $month_start) - $weekstarts + 7) % 7;
$days_in_month = date("t", $month_start);
$month_end = mktime(23, 59, 59, $month, $days_in_month, $year);

if ($enable_periods=='y') {
    $resolution = 60;
    $morningstarts = 12;
    $eveningends = 12;
    $eveningends_minutes = count($periods_name)-1;
}

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    #Table avec areas, rooms, minicals.
    echo "<table width=\"100%\" cellspacing=15><tr><td>";
    $this_service_name = "";
    $this_room_name = "";
    if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1))
        $area_list_format = $_SESSION['default_list_type'];
    else
        $area_list_format = getSettingValue("area_list_format");
        #Affiche une liste déroulante ou bien un liste HTML
    if ($area_list_format != "list")
    {
        echo make_area_select_html($type_month_all.'.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        echo make_room_select_html('month', $area, $room, $year, $month, $day);
    }
    else
    {
        echo "<table cellspacing=15><tr><td>";
        echo make_area_list_html($type_month_all.'.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
        #Montre toutes les rooms du domaine affiché
        echo "</td><td>";
        make_room_list_html('month.php', $area, $room, $year, $month, $day);
        echo "</td></tr></table>";
    }
    echo "</td>\n";
    #Affiche le calendrier des 3 mois
    minicals($year, $month, $day, $area, $room, 'month');
    echo "</tr></table>\n";
}
$this_service_name = grr_sql_query1("select service_name from agt_service where id=$area");
$this_room_name = grr_sql_query1("select room_name from agt_room where id=$room");
$this_room_name_des = grr_sql_query1("select description from agt_room where id=$room");

    #O,n arrête si il n'y a pas de room dans cet area
if ($room <= 0)
{
    echo "<h1>".get_vocab("no_rooms_for_area")."</h1>";
    include "./commun/include/trailer.inc.php";
    exit;
}
    #Affiche le mois, l'année, la room et l'area
if (($this_room_name_des) and ($this_room_name_des!="-1"))
    $this_room_name_des = " (".$this_room_name_des.")";
else
    $this_room_name_des = "";

echo "<h2 align=center>" . ucfirst(utf8_strftime("%B %Y", $month_start))
  . "<br />".ucfirst($this_service_name)." - $this_room_name $this_room_name_des</h2>\n";

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
      <a href=\"month.php?year=$yy&amp;month=$ym&amp;area=$area&amp;room=$room\">
      &lt;&lt; ".get_vocab("monthbefore")."</a></td>
      <td>&nbsp;</td>
      <td align=right><a href=\"month.php?year=$ty&amp;month=$tm&amp;area=$area&amp;room=$room\">
      ".get_vocab("monthafter")." &gt;&gt;</a></td></tr></table>";
}
if ($debug_flag)
    echo "<p>DEBUG: month=$month year=$year start=$weekday_start range=$month_start:$month_end\n";
    #Remplace l'espace pour qu'il n'y ai pas de problèmes
$all_day = ereg_replace(" ", "&nbsp;", get_vocab("all_day"));
    #Récupérer toutes les réservations pour le mois de la room affichée
    # row[0] = Début de réservation
    # row[1] = Fin de réservation
    # row[2] = ID de la réservation
    # row[3] = Nom de la réservation
    # row[4] = Bénéficiaire de la réservation
    # row[5] = Description complète
    # row[6] = type
    # row[7] = modération
    # row[8] = Bénéficiaire extérier

$sql = "SELECT start_time, end_time, id, name, beneficiaire, description, type, moderate, beneficiaire_ext
   FROM agt_loc
   WHERE room_id=$room
   AND start_time <= $month_end AND end_time > $month_start
   ORDER by 1";
    # Contruit un array des informations de chaques jours dans le mois
    # Ces informations sont sauvegardées:
    #  d[monthday]["id"][] = ID de chaque réservation, pour le lien
    #  d[monthday]["data"][] = Début et fin pour chaque réservation
$res = grr_sql_query($sql);
if (! $res)
    echo grr_sql_error();
else for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    if ($debug_flag)
        echo "<br />DEBUG: result $i, id $row[2], starts $row[0], ends $row[1]\n";
    #Remplir tous les jours ou cette réservation s'opère

    // début de la première réservation trouvée
    $t = max((int)$row[0], $month_start);
    // fin de la première réservation trouvée
    $end_t = min((int)$row[1], $month_end);
    // numéro du jour de la première réservation
    $day_num = date("j", $t);
    // On fixe le début de la journée ($midnight)
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
            $d[$day_num]["who"][] = get_vocab("reservee au nom de").affiche_nom_prenom_email($row[4],$row[8],"nomail");
        else if (getSettingValue("display_info_bulle") == 2)
            $d[$day_num]["who"][] = $row[5];
        else
            $d[$day_num]["who"][] = "";
        $d[$day_num]["who1"][] = affichage_lien_resa_planning($row[3],$row[2]);
        $d[$day_num]["color"][] = $row[6];
        $d[$day_num]["description"][] =  affichage_resa_planning($row[5],$row[2]);
        $d[$day_num]["moderation"][] = $row[7];
        // On incrémente de 24 h = 86400 secondes
        $midnight_tonight = $midnight + 86400;

        #Début et fin pour tous les jours
        #9 cas: Début < = ou > minuit
        #       Fin < = ou > minuit
        #Utiliser ~ (pas -) pour séparer l'heure de début et de fin (MSIE)
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
            case "> < ":            #Début après minuit, fin avant minuit
            case "= < ":            #Début à minuit, fin avant minuit
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~" . date(hour_min_format(), $row[1]);
                break;
            case "> = ":            #Début après minuit, fin à minuit
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~24:00";
                break;
            case "> > ":            #Début après minuit, continue le lendemain
                $d[$day_num]["data"][] = date(hour_min_format(), $row[0]) . "~====&gt;";
                break;
            case "= = ":            #Début à minuit, fin à minuit
                $d[$day_num]["data"][] = $all_day;
                break;
            case "= > ":            #Début à minuit, continue le lendemain
                $d[$day_num]["data"][] = $all_day . "====&gt;";
                break;
            case "< < ":            #Début avant aujourdhui, fin avant minuit
                $d[$day_num]["data"][] = "&lt;====~" . date(hour_min_format(), $row[1]);
                break;
            case "< = ":            #Début avant aujourd'hui', fin à minuit
                $d[$day_num]["data"][] = "&lt;====" . $all_day;
                break;
            case "< > ":            #Début avant aujourd'hui', continue le lendemain
                $d[$day_num]["data"][] = "&lt;====" . $all_day . "====&gt;";
                break;
        }
        }
        #Seulement si l'heure de fin est pares minuit, on continue le jour prochain.
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
echo "<table border=2 width=\"100%\">\n<tr>";
    #Affichage des jours en entête
for ($weekcol = 0; $weekcol < 7; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
    echo "<th width=\"14%\">" . day_name(($weekcol + $weekstarts)%7) . "</th>";
}
echo "</tr><tr>\n";
    #Ne pas tenir compte des jours avant le début du mois
for ($weekcol = 0; $weekcol < $weekday_start; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
        echo "<td class=\"cell_month_o\" height=100>&nbsp;</td>\n";
}
    #Afficher le jour du mois
for ($cday = 1; $cday <= $days_in_month; $cday++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    $t=mktime(0,0,0,$month,$cday,$year);
    $name_day = ucfirst(utf8_strftime("%d", $t));
	$jour_cycle = grr_sql_query1("SELECT Jours FROM agt_calendrier_jours_cycle WHERE DAY='$t'");
    if ($weekcol == 0) echo "</tr><tr>\n";
    if ($display_day[$num_week_day] == 1) {// début condition "on n'affiche pas tous les jours de la semaine"
    echo "<td valign=\"top\" height=\"100\" class=\"cell_month\"><div class=\"monthday\"><a title=\"".htmlspecialchars(get_vocab("see_all_the_rooms_for_the_day"))."\"   href=\"day.php?year=$year&amp;month=$month&amp;day=$cday&amp;area=$area\">".$name_day;
    if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
        if (intval($jour_cycle)>0)
            echo " - ".get_vocab("rep_type_6")." ".$jour_cycle;
        else
            echo " - ".$jour_cycle;
  	echo "</a></div>\n";
    if (est_hors_reservation(mktime(0,0,0,$month,$cday,$year)))
        echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\" /></center>";
    else {
        # Anything to display for this day?
        if (isset($d[$cday]["id"][0])) {
            echo "<font size=-2>";
            $n = count($d[$cday]["id"]);
            #Affiche l'heure de début et de fin, 2 par lignes avec lien pour voie la reservation
            #Si il y en a plus que 123, on affiche "..." après le 11ème
            for ($i = 0; $i < $n; $i++) {
                if ($i == 11 && $n > 12) {
                    echo " ...\n";
                    break;
                }
                if ($i > 0) echo "<br />"; else echo " ";
                echo "<b>";
                echo span_bgground($d[$cday]["color"][$i]);
                echo $d[$cday]["data"][$i]
                    . "<br />";
               // si la réservation est à modérer, on le signale
               if ((isset($d[$cday]["moderation"][$i])) and ($d[$cday]["moderation"][$i]==1))
                   echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" align=\"middle\" />&nbsp;\n";

              echo "<a title=\"".htmlspecialchars($d[$cday]["who"][$i])."\" href=\"view_entry.php?id=" . $d[$cday]["id"][$i]
              . "&amp;day=$cday&amp;month=$month&amp;year=$year&amp;page=month\">"
              . htmlspecialchars($d[$cday]["who1"][$i])
              . "</a>";
              if ($d[$cday]["description"][$i]!= "")
                  echo "<br /><i>(".$d[$cday]["description"][$i].")</i>";
              echo "<br /></span></b>";
            }
            echo "</font>";
        }
    }
    echo "</td>\n";
    } // fin condition "on n'affiche pas tous les jours de la semaine"
    if (++$weekcol == 7) $weekcol = 0;
}
    #Ne tiens pas en compte les journées après le derbier jour du mois
if ($weekcol > 0) for (; $weekcol < 7; $weekcol++)
{
    $num_week_day = ($weekcol + $weekstarts)%7;
    if ($display_day[$num_week_day] == 1)  // on n'affiche pas tous les jours de la semaine
        echo "<td class=\"cell_month_o\" height=100>&nbsp;</td>\n";
}
echo "</tr></table>\n";
include "./commun/include/trailer.inc.php";
?>
