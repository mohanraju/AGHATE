<?php
#########################################################################
#                         day.php                                       #
#                                                                       #
#    Permet l'affichage de la page d'accueil lorsque l'on est en mode   #
#    d'affichage "jour".                                                #
#                                                                       #
#                  Dernière modification : 20/03/2008                   #
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
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
$grr_script_name = "day.php";
#Paramètres de connection
require_once("./commun/include/settings.inc.php");

#Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");

#Fonction relative à la session
require_once("./commun/include/session.inc.php");
   #Si nous ne savons pas la date, nous devons la créer
$date_now = time();
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
} else
{
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

    #Si la date n'est pas valide, ils faut la modifier (Si le nombre de jours est suppérieur au nombre de jours dans un mois)
    while (!checkdate($month, $day, $year))
        $day--;
}


// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};
if (empty($area)) $area = get_default_area();

// Paramètres langage
include "./commun/include/language.inc.php";

// On affiche le lien "format imprimable" en bas de la page
$affiche_pview = '1';
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
} else {
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Si aucun domaine n'est défini
if ($area == 0) {
   print_header($day, $month, $year, $area,$type_sessionn);
   echo "<H1>".get_vocab("noareas")."</H1>";
   echo "<A HREF='admin_accueil.php'>".get_vocab("admin")."</A>\n
   </BODY>
   </HTML>";
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

if (check_begin_end_bookings($day, $month, $year))
{
    showNoBookings($day, $month, $year, $area,$back,$type_session);
    exit();
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
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {

// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

echo "<table width=\"100%\" cellspacing=15><tr>\n<td>";

if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1)) {
    $area_list_format = $_SESSION['default_list_type'];
} else {
    $area_list_format = getSettingValue("area_list_format");
}

#Show all avaliable areas
# need to show either a select box or a normal html list,
if ($area_list_format != "list") {
  echo make_area_select_html('day.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
	echo make_room_select_html('week', $area, "", $year, $month, $day);
} else {
	echo "\n<table cellspacing=15><tr><td>\n";
  echo make_area_list_html('day.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
	echo "</td><td>";
	make_room_list_html('week.php', $area, "", $year, $month, $day);
	echo "</td></tr></table>";
}
echo "</td>\n";
#Draw the three month calendars
minicals($year, $month, $day, $area, -1, 'day');
echo "</tr></table>";

// fin de la condition "Si format imprimable"
}

#y? are year, month and day of yesterday
#t? are year, month and day of tomorrow
$ind = 1;
$test = 0;
while (($test == 0) and ($ind < 7)) {
    $i= mktime(0,0,0,$month,$day-$ind,$year);
    $test =$display_day[date("w",$i)];
    $ind++;
}
$yy = date("Y",$i);
$ym = date("m",$i);
$yd = date("d",$i);

$i= mktime(0,0,0,$month,$day,$year);
$jour_cycle = grr_sql_query1("SELECT Jours FROM agt_calendrier_jours_cycle WHERE DAY='$i'");

$ind = 1;
$test = 0;
while (($test == 0) and ($ind < 7)) {
    $i= mktime(0,0,0,$month,$day+$ind,$year);
    $test =$display_day[date("w",$i)];
    $ind++;
}
$ty = date("Y",$i);
$tm = date("m",$i);
$td = date("d",$i);

# Define the start and end of the day.
$am7=mktime($morningstarts,0,0,$month,$day,$year);
$pm7=mktime($eveningends,$eveningends_minutes,0,$month,$day,$year);

#Show current date
$this_service_name = grr_sql_query1("select service_name from agt_service where id='".protect_data_sql($area)."'");

echo "<h2 align=center>" . ucfirst(utf8_strftime($dformat, $am7)) . " - ";
if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
    if (intval($jour_cycle)>0)
	      echo  get_vocab("rep_type_6")." ".$jour_cycle."<br />";
	  else
	      echo  $jour_cycle."<br />";
echo ucfirst($this_service_name)." - ".get_vocab("all_areas")."</h2>\n";


// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    #Show Go to day before and after links
    echo "<table width=\"100%\"><tr>\n<td>\n<a href=\"day.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area\">&lt;&lt; ".get_vocab('daybefore')."</a></td>\n<td align=right><a href=\"day.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area\">".get_vocab('dayafter')." &gt;&gt;</a></td>\n</tr></table>\n";
}

#We want to build an array containing all the data we want to show
#and then spit it out.

#Get all appointments for today in the area that we care about
#Note: The predicate clause 'start_time <= ...' is an equivalent but simpler
#form of the original which had 3 BETWEEN parts. It selects all entries which
#occur on or cross the current day.
$sql = "SELECT agt_room.id, start_time, end_time, name, grr_nonvenu.id, type, beneficiaire, statut_entry, grr_nonvenu.description, grr_nonvenu.option_reservation, grr_nonvenu.moderate, beneficiaire_ext,pmsi
   FROM grr_nonvenu, agt_room
   WHERE grr_nonvenu.room_id = agt_room.id
   AND service_id = '".protect_data_sql($area)."'
   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY start_time";

$res = grr_sql_query($sql);
if (! $res) {
//    fatal_error(0, grr_sql_error());
    include "./commun/include/trailer.inc.php";
    exit;
}

for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
    $start_t = max(round_t_down($row[1], $resolution, $am7), $am7);
    $end_t = min(round_t_up($row[2], $resolution, $am7) - $resolution, $pm7);

    // Calcul du nombre de créneaux qu'occupe la réservation
    $cellules[$row[4]]=($end_t-$start_t)/$resolution+1;
    // Initialisation du compteur
    $compteur[$row[4]]=0;

    for ($t = $start_t; $t <= $end_t; $t += $resolution)
    {
    		$room__id== $row[0];
        $today[$row[0]][$t]["id"]    = $row[4];
        $today[$row[0]][$t]["color"] = $row[5];
        $today[$row[0]][$t]["data"]  = "";
        $today[$row[0]][$t]["who"] = "";
        $today[$row[0]][$t]["statut"] = $row[7];
        $today[$row[0]][$t]["moderation"] = $row[10];
        $today[$row[0]][$t]["option_reser"] = $row[9];
        // Construction des infos à afficher sur le planning
        $today[$row[0]][$t]["description"] = affichage_resa_planning($row[8],$row[4]);
        
        $nonvenu[$room__id].=$today[$row[0]][$t]["description"];
    }
}
# We need to know what all the rooms area called, so we can show them all
# pull the data from the db and store it. Convienently we can print the room
# headings and capacities at the same time

$sql = "select room_name, capacity, id, description, statut_room, show_fic_room, delais_option_reservation, moderate from agt_room where service_id='".protect_data_sql($area)."' order by order_display, room_name";
$res = grr_sql_query($sql);

# It might be that there are no rooms defined for this area.
# If there are none then show an error and dont bother doing anything
# else
if (! $res) fatal_error(0, grr_sql_error());
if (grr_sql_count($res) == 0)
{
    echo "<h1>".get_vocab('no_rooms_for_area')."</h1>";
    grr_sql_free($res);
}
else
{
    #This is where we start displaying stuff
    echo "<table cellspacing=0 border=1 width=\"100%\">";

    // Première ligne du tableau
    echo "<tr>\n<th width=\"5%\">&nbsp;</th>";
    $tab[1][] = "&nbsp;";
    $room_column_width = (int)(90 / grr_sql_count($res));
    $nbcol = 0;
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $room_name[$i] = $row[0];
        $id_room[$i] =  $row[2];
        $statut_room[$id_room[$i]] =  $row[4];
        $statut_moderate[$id_room[$i]] =  $row[7];
        $nbcol++;
        if ($row[1]) {
            $temp = "<br />($row[1] ".($row[1] >1 ? get_vocab("number_max2") : get_vocab("number_max")).")";
        } else {
            $temp="";
        }
        if ($statut_room[$id_room[$i]] == "0") $temp .= "<br /><font color=\"#BA2828\"><b>".get_vocab("ressource_temporairement_indisponible")."</b></font>"; // Ressource temporairement indisponible
        if ($statut_moderate[$id_room[$i]] == "1") $temp .= "<br /><font color=\"#BA2828\"><b>".get_vocab("reservations_moderees")."</b></font>"; // Ressource temporairement indisponible
        echo "<th width=\"$room_column_width%\"";
        // Si la ressource est temporairement indisponible, on le signale
        if ($statut_room[$id_room[$i]] == "0") echo " class='avertissement' ";
        echo ">" . htmlspecialchars($row[0])."\n";
        if (htmlspecialchars($row[3]. $temp != '')) {
            if (htmlspecialchars($row[3] != '')) $saut = "<br />"; else $saut = "";
            echo $saut."<i><span class =\"small\">". htmlspecialchars($row[3]) . $temp."\n</span></i>";
        }

        echo "<br />";
        if (verif_display_fiche_ressource(getUserName(), $id_room[$i]) and $_GET['pview'] != 1)
            echo "<A href='javascript:centrerpopup(\"view_room.php?id_room=$id_room[$i]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\">
           <img src=\"./commun/images/details.png\" alt=\"d&eacute;tails\" border=\"0\" class=\"print_image\"  /></a>";
        if (authGetUserLevel(getUserName(),$id_room[$i]) > 2 and $_GET['pview'] != 1)
            echo "<a href='admin_edit_room.php?room=$id_room[$i]'><img src=\"./commun/images/editor.png\" alt=\"configuration\" border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a>";
        echo "</th>";
        // stockage de la première ligne :
        $tab[1][$i+1] = htmlspecialchars($row[0]);
        if (htmlspecialchars($row[3]. $temp != '')) {
            if (htmlspecialchars($row[3] != '')) $saut = "<br />"; else $saut = "";
            $tab[1][$i+1] .="<br />-".$saut."<i><span class =\"small\">". htmlspecialchars($row[3]) . $temp."\n</span></i>";
        }
        $tab[1][$i+1] .= "<br />";
        if (verif_display_fiche_ressource(getUserName(), $id_room[$i]))
            $tab[1][$i+1] .= "<A href='javascript:centrerpopup(\"view_room.php?id_room=$id_room[$i]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\">
           <img src=\"./commun/images/details.png\" alt=\"détails\" border=\"0\" class=\"print_image\"  /></a>";
        if (authGetUserLevel(getUserName(),$id_room[$i]) > 2 and $_GET['pview'] != 1)
            $tab[1][$i+1] .= "<a href='admin_edit_room.php?room=$id_room[$i]'><img src=\"./commun/images/editor.png\" alt=\"configuration\" border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a>";
        // fin stockage de la première ligne :


        $rooms[] = $row[2];
        $delais_option_reservation[$row[2]] = $row[6];
    }
    echo "<th width=\"5%\">&nbsp;</th></tr>\n";
    $tab[1][] = "&nbsp;";

    // Deuxième ligne et lignes suivantes du tableau
    echo "<tr>\n";
    tdcell("cell_hours");
    if ($enable_periods == 'y')
        echo get_vocab('period');
    else
        echo get_vocab('time');
    echo "</td>\n";

    if ($enable_periods == 'y')
        $tab[2][] = get_vocab('period');
    else
        $tab[2][] = get_vocab('time');

    for ($i = 0; $i < $nbcol; $i++)
    {
        // Si la ressource est temporairement indisponible, on le signale, sinon, couleur normale
        if ($statut_room[$id_room[$i]] == "0") tdcell("avertissement"); else tdcell("cell_hours");
        if ($_GET['pview'] != 1)
           echo "<a title=\"".htmlspecialchars(get_vocab("see_week_for_this_room"))."\"  href=\"week.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$id_room[$i]\">".get_vocab("week")."</a><br /><a title=\"".htmlspecialchars(get_vocab("see_month_for_this_room"))."\" href=\"month.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$id_room[$i]\">".get_vocab("month")."</a>";
        if ($_GET['pview'] != 1)
           $tab[2][] = "<a title=\"".htmlspecialchars(get_vocab("see_week_for_this_room"))."\"  href=\"week.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$id_room[$i]\">".get_vocab("week")."</a><br /><a title=\"".htmlspecialchars(get_vocab("see_month_for_this_room"))."\" href=\"month.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$id_room[$i]\">".get_vocab("month")."</a>";
        else
           $tab[2][] = "";

        echo "</td>\n";
    }
    tdcell("cell_hours");
    if ($enable_periods == 'y')
        echo get_vocab('period');
    else
        echo get_vocab('time');

    if ($enable_periods == 'y')
        $tab[2][] = get_vocab('period');
    else
        $tab[2][] = get_vocab('time');


    echo "</td>\n</tr>\n";


    $tab_ligne = 3;
    // Début première boucle sur le temps
    for ($t = $am7; $t <= $pm7; $t += $resolution)
    {
        # Show the time linked to the URL for highlighting that time
        echo "<tr>\n";


        tdcell("cell_hours");
        if( $enable_periods == 'y' ){
            $time_t = date("i", $t);
            $time_t_stripped = preg_replace( "/^0/", "", $time_t );
            echo $periods_name[$time_t_stripped] . "</td>\n";
            $tab[$tab_ligne][] = $periods_name[$time_t_stripped];
        } else {
            echo date(hour_min_format(),$t) . "</td>\n";
            $tab[$tab_ligne][] = date(hour_min_format(),$t);
        }


        // Début Deuxième boucle sur la liste des ressources du domaine
        while (list($key, $room) = each($rooms))
        {
            if(isset($today[$room][$t]["id"])) // il y a une réservation sur le créneau
            {
                $id    = $today[$room][$t]["id"];
                $color = $today[$room][$t]["color"];
                $descr = htmlspecialchars($today[$room][$t]["data"]);
            }
            else
                unset($id);  // $id non défini signifie donc qu'il n'y a pas de résa sur le créneau

            // Définition des couleurs de fond de cellule
            if  ((isset($id)) and (!est_hors_reservation(mktime(0,0,0,$month,$day,$year))))   // 1er cas : il y a une réservation sur le créneau
            {
                $c = $color;
            } else if ($statut_room[$room] == "0") // 2ème cas : ou bien la ressource est temporairement indisponible
                $c = "avertissement"; // on le signale par une couleur spécifique
            else  // 3ème cas : sinon, il s'agit d'un créneau libre
                $c = "empty_cell";

            // S'il s'agit d'un créneau avec une resa :
            // s'il s'agit du premier passage ($compteur[$id]=0), on fait un tdcell_rowspan
            // Sinon, pas de <td>
            if  ((isset($id)) and (!est_hors_reservation(mktime(0,0,0,$month,$day,$year)))) {
                if( $compteur[$id] == 0 ) {
                    // Y-a-il chevauchement de deux blocs dans le cas où la hauteur du bloc est supérieure à 1 ?
                    if ($cellules[$id] != 1) {
                       // Dans ce cas, on s'intéresse à la dernière ligne du bloc
                       if(isset($today[$room][$t+($cellules[$id]-1)*$resolution]["id"])) {
                         // Il y a chevaussement seulement si l'id correspondant est différent de l'id actuel
                         $id_derniere_ligne_du_bloc = $today[$room][$t+($cellules[$id]-1)*$resolution]["id"];
                         // Dan ce cas, on réduit la taille du bloc pour éviter le chevaussement
                         if ($id_derniere_ligne_du_bloc != $id) $cellules[$id] = $cellules[$id]-1;
                       }
                    }
                    tdcell_rowspan ($c, $cellules[$id]);
                }
                $compteur[$id] = 1; // on incrémente le compteur initialement à zéro
            } else
                tdcell ($c); // il s'agit d'un créneau libre  -> <td> normal
            // Si $compteur[$id] a atteint == $cellules[$id]+1

            if ((!isset($id)) or (est_hors_reservation(mktime(0,0,0,$month,$day,$year)))) // Le créneau est libre
            {
                $hour = date("H",$t);
                $minute  = date("i",$t);
                $date_booking = mktime($hour, $minute, 0, $month, $day, $year);
                echo "<center>";
                if (est_hors_reservation(mktime(0,0,0,$month,$day,$year))) {
                    echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
                    $tab[$tab_ligne][] = "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
                } else

                if (((authGetUserLevel(getUserName(),-1) > 1) or (auth_visiteur(getUserName(),$room) == 1))
                 and (UserRoomMaxBooking(getUserName(), $room, 1) != 0)
                 and verif_booking_date(getUserName(), -1, $room, $date_booking, $date_now, $enable_periods)
                 and verif_delais_max_resa_room(getUserName(), $room, $date_booking)
                 and verif_delais_min_resa_room(getUserName(), $room, $date_booking)
                 and (($statut_room[$room] == "1") or
                  (($statut_room[$room] == "0") and (authGetUserLevel(getUserName(),$room) > 2) ))
                  and $_GET['pview'] != 1) {
                    if ($enable_periods == 'y') {
                        echo "<a href=\"edit_entry.php?area=$area&amp;room=$room&amp;period=$time_t_stripped&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" ><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" width=\"16\" height=\"16\" class=\"print_image\" /></a>";
                        $tab[$tab_ligne][] = "<a href=\"edit_entry.php?area=$area&amp;room=$room&amp;period=$time_t_stripped&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" ><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" width=\"16\" height=\"16\" class=\"print_image\" /></a>";
                    } else {
                        echo "<a href=\"edit_entry.php?area=$area&amp;room=$room&amp;hour=$hour&amp;minute=$minute&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" ><img src=./commun/images/new.png border=0 alt=\"".get_vocab("add")."\" class=\"print_image\" /></a>";
                        $tab[$tab_ligne][] =  "<a href=\"edit_entry.php?area=$area&amp;room=$room&amp;hour=$hour&amp;minute=$minute&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" ><img src=./commun/images/new.png border=0 alt=\"".get_vocab("add")."\" class=\"print_image\" /></a>";
                    }
                } else {
                    echo "&nbsp;";
                    $tab[$tab_ligne][] = "&nbsp;";
                }
                echo "</center>";
                echo "</td>\n";
            }
            elseif ($descr != "")
            {
                // si la réservation est "en cours", on le signale
                if ((isset($today[$room][$t]["statut"])) and ($today[$room][$t]["statut"]!='-')) {
                    echo "&nbsp;<img src=\"./commun/images/buzy.png\" alt=\"".get_vocab("reservation_en_cours")."\" title=\"".get_vocab("reservation_en_cours")."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
                    $tab[$tab_ligne][] = "&nbsp;<img src=\"./commun/images/buzy.png\" alt=\"".get_vocab("reservation_en_cours")."\" title=\"".get_vocab("reservation_en_cours")."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
                }
                // si la réservation est à confirmer, on le signale
                if (($delais_option_reservation[$room] > 0) and (isset($today[$room][$t]["option_reser"])) and ($today[$room][$t]["option_reser"]!=-1)) {
                    echo "&nbsp;<img src=\"./commun/images/small_flag.png\" alt=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."\" title=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."&nbsp;".time_date_string_jma($today[$room][$t]["option_reser"],$dformat)."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
                    $tab[$tab_ligne][] = "&nbsp;<img src=\"./commun/images/small_flag.png\" alt=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."\" title=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."&nbsp;".time_date_string_jma($today[$room][$t]["option_reser"],$dformat)."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
                }
                // si la réservation est à modérer, on le signale
                if ((isset($today[$room][$t]["moderation"])) and ($today[$room][$t]["moderation"]=='1')) {
                    echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" />&nbsp;\n";
                    $tab[$tab_ligne][] = "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" />&nbsp;\n";
                }

                #if it is booked then show
                if (($statut_room[$room] == "1") or
                (($statut_room[$room] == "0") and (authGetUserLevel(getUserName(),$room) > 2) )) {
                    echo "<img src=\"./commun/images/stethoscope.jpg\" alt=\"d&eacute;tails\" border=\"0\" class=\"print_image\"  />";                	
                    echo " <a title=\"".htmlspecialchars($today[$room][$t]["who"])."\" href=\"view_entry.php?id=$id&amp;area=$area&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=day\">$descr</a>";
                    $tab[$tab_ligne][] = " <a title=\"".htmlspecialchars($today[$room][$t]["who"])."\" href=\"view_entry.php?id=$id&amp;area=$area&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=day\">$descr</a>";
                    if ($today[$room][$t]["description"]!= "") {
                        echo "<br /><i>".$today[$room][$t]["description"]."</i>";
                        $tab[$tab_ligne][] = "<br /><i>".$today[$room][$t]["description"]."</i>";
                    }

                } else {
                    echo " $descr";
                    $tab[$tab_ligne][] = " $descr";
                }
                echo "</td>\n";
            }
        } // Fin Deuxième boucle sur la liste des ressources du domaine

        // Répétition de la première colonne
        // Si la ressource est temporairement indisponible, on le signale, sinon, couleur normale
        tdcell("cell_hours");
        if( $enable_periods == 'y' ){
            $time_t = date("i", $t);
            $time_t_stripped = preg_replace( "/^0/", "", $time_t );
            echo $periods_name[$time_t_stripped] . "</td>\n";
            $tab[$tab_ligne][] =  $periods_name[$time_t_stripped];

        } else {
            echo date(hour_min_format(),$t) . "</td>\n";
            $tab[$tab_ligne][] = date(hour_min_format(),$t);
        }

        echo "</tr>\n";

        reset($rooms);
        $tab_ligne++;
    }
    // répétition de la ligne d'en-tête
    echo "<tr>\n<th>&nbsp;</th>";
    for ($i = 0; $i < $nbcol; $i++)
    {
        echo "<th";
        if ($statut_room[$id_room[$i]] == "0") echo " class='avertissement' ";
        echo ">" . htmlspecialchars($room_name[$i])."</th>";
    }
    echo "<th>&nbsp;</th></tr>\n";

    echo "</table> ";
    show_colour_key($area);
}


include "./commun/include/trailer.inc.php";
?>
