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
ini_set("error_reporting","E_ALL & ~E_NOTICE");

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
$_SESSION["area"]=$this_area_urm;



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
   print_header($day, $month, $year, $area,$type_session);
   header("Content-type:text/html; charset: utf-8");
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
	//echo "-",authUserAccesArea($session_login, $area),$session_login, $area,"-";
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
<script type='text/javascript'>
function newWindow(number) {
	 whichOne = number;
    mywindow=open('print_day.php?'+number,'myname','resizable=yes,width=850,height=770,status=yes,scrollbars=yes,location=0');
    mywindow.location.href = 'print_day.php?'+number;
    if (mywindow.opener == null) mywindow.opener = self;
}
function newWindow_1(number) {
	 whichOne = number;
    mywindow=open('print_day_1.php?'+number,'myname','resizable=yes,width=850,height=770,status=yes,scrollbars=yes,location=0');
    mywindow.location.href = 'print_day_1.php?'+number;
    if (mywindow.opener == null) mywindow.opener = self;
}

function newWindow_2(number) {
	 whichOne = number;
    mywindow=open('print_day_pat_time.php?'+number,'myname','resizable=yes,width=850,height=770,status=yes,scrollbars=yes,location=0');
    mywindow.location.href = 'print_day_pat_time.php?'+number;
    if (mywindow.opener == null) mywindow.opener = self;
}
function Window_010(number) {
	 whichOne = number;
    mywindow=open('rappel_rdv_010.php.php?'+number,'myname','resizable=yes,width=850,height=770,status=yes,scrollbars=yes,location=0');
    mywindow.location.href = 'rappel_rdv_010.php?'+number;
    if (mywindow.opener == null) mywindow.opener = self;
}
function print_new(number) {
	 whichOne = number;
    mywindow=open('print_day.php?param='+number,'myname','resizable=yes,width=800,height=670,status=yes,scrollbars=yes');
    mywindow.location.href = 'print_day.php?param='+number;
    if (mywindow.opener == null) mywindow.opener = self;
}
function OpenPopup(url) {
    mywindow1=window.open(url,'myname','resizable=yes,width=750,height=250,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}

</script>
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
$this_area_urm = grr_sql_query1("select urm from agt_service where id='".protect_data_sql($area)."'");

$_SESSION["URM"]=$this_area_urm;

echo "<h2 align=center>" . ucfirst(utf8_strftime($dformat, $am7)) . " - ";
$c_jour=ucfirst(utf8_strftime($dformat, $am7));
$for_print=ucfirst(utf8_strftime($dformat, $am7))."|";

if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
    if (intval($jour_cycle)>0)
	      echo  get_vocab("rep_type_6")." ".$jour_cycle."<br />";
	  else
	      echo  $jour_cycle."<br />";
echo ucfirst($this_service_name);//." - ";//.get_vocab("all_areas");



#We want to build an array containing all the data we want to show
#and then spit it out.

#Get all appointments for today in the area that we care about
#Note: The predicate clause 'start_time <= ...' is an equivalent but simpler
#form of the original which had 3 BETWEEN parts. It selects all entries which
#occur on or cross the current day.

// count nombre de patient dans le journée
$sql_nbr_pat = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, beneficiaire, statut_entry, agt_loc.description, agt_loc.option_reservation, agt_loc.moderate, beneficiaire_ext,pmsi,hds,overload_desc
   FROM agt_loc, agt_room
   WHERE agt_loc.room_id = agt_room.id
   AND service_id = '".protect_data_sql($area)."'
   AND name not like('ABSENCE%')
   AND name not like('ABSENT INF.%')
   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY start_time";
   
$res_nbr_pat = grr_sql_query($sql_nbr_pat);
if (! $res_nbr_pat) {
    include "./commun/include/trailer.inc.php";
    exit;
}
//count nombre de accompagnée pour le service Endocrinologie URM 010
if ($_SESSION["URM"]=='010'){
	$nbr_acc=0;
	$sql_acc="select id from agt_overload where id_area='".$area."' limit 1";
	$res_nbr_acc = grr_sql_query($sql_acc);
	$row = grr_sql_row($res_nbr_acc, 0);
	$id=$row[0];
	$find_val="@$id@Oui@/$id@";
	$sql_="select count(*) as nbr 
	   FROM agt_loc, agt_room
	   where overload_desc='".$find_val."' 
	   AND agt_loc.room_id = agt_room.id
	   AND service_id = '".protect_data_sql($area)."'
	   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY start_time";
 
	$res_ = grr_sql_query($sql_);
	$row = grr_sql_row($res_, 0);	
	$nbr_acc=$row[0];

	$nbr_pats=grr_sql_count($res_nbr_pat);
	echo " - " .$nbr_pats." patient(s) +".$nbr_acc." Accomp.</h2>\n";

	
}else{
	$nbr_pats=grr_sql_count($res_nbr_pat);
	echo " - " .$nbr_pats." patient(s) </h2>\n";
}

//------------------------------------------------------------
// ajote additional description journalier à affichier ICI
// ajouté par mohan le 21/02/2011
//------------------------------------------------------------
$c_date=$year."-".$month."-".$day;
$sql_="select agt_medecin.titre,agt_medecin.nom,details,service_fermee  FROM agt_loc_parametres
			LEFT JOIN agt_medecin ON agt_medecin.id_medecin = agt_loc_parametres.medecin_id
			where date ='$c_date' and agt_loc_parametres.service_id='".protect_data_sql($area)."'";

	$res_ = grr_sql_query($sql_);
	$row = grr_sql_row($res_, 0);	
	$msg="";
	if((strlen($row[1]) > 1)){
		$msg="Medecin : ".$row[0]." " .$row[1] ." , ";
		}

	if ($row[3]=='1'){
		$msg.=" SERVICE FERMEE ";		
		}
		
	
	$msg.=$row[2];
	if (strlen($msg)< 3)$msg="<img src='./commun/images/post_it.jpg' border='0' height='20'>";
if ((authGetUserLevel($_SESSION['login'],$room_id) >= 2))
{
	$link="<H2 align='center'><a href='#'  onClick=\"OpenPopup('additional_info_journalier.php?date=$c_date&service_id=".$area."')\" >".$msg."</a></H2>";
}else{
	$link="<H2 align='center'> ".$msg." </H2>";
}	
echo $link;
//echo "<H2 align='center'> ".$msg."</H2>";


$sql = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, beneficiaire, statut_entry, agt_loc.description, agt_loc.option_reservation, agt_loc.moderate, beneficiaire_ext,pmsi,hds,picture_room,nda
   FROM agt_loc, agt_room
   WHERE agt_loc.room_id = agt_room.id
   AND service_id = '".protect_data_sql($area)."'
   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY start_time";

//echo $sql;
$res = grr_sql_query($sql);

if (! $res) {
//    fatal_error(0, grr_sql_error());
    include "./commun/include/trailer.inc.php";
    exit;
}





// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
    #Show Go to day before and after links
    echo "<table width=\"100%\"><tr>\n<td>\n<a href=\"day.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area\">&lt;&lt; ".get_vocab('daybefore')."</a></td>\n<td align=right><a href=\"day.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area\">".get_vocab('dayafter')." &gt;&gt;</a></td>\n</tr></table>\n";
}


 
for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
    # Each row weve got here is an appointment.
    #Row[0] = Room ID
    #row[1] = start time
    #row[2] = end time
    #row[3] = short description
    #row[4] = id of this booking
    #row[5] = type (internal/external)
    #row[6] = identifiant du réservant
    #row[7] = satut of the booking
    #row[8] = Full description
    #row[9] = option_reservation
    #row[10] = état de modération de la réservation
    #row[11] = bénéficiaire extérieur

    # $today is a map of the screen that will be displayed
    # It looks like:
    #     $today[Room ID][Time][id]
    #                          [color]
    #                          [data]

    # Fill in the map for this meeting. Start at the meeting start time,
    # or the day start time, whichever is later. End one slot before the
    # meeting end time (since the next slot is for meetings which start then),
    # or at the last slot in the day, whichever is earlier.
    # Note: int casts on database rows for max may be needed for PHP3.
    # Adjust the starting and ending times so that bookings which don't
    # start or end at a recognized time still appear.

    $start_t = max(round_t_down($row[1], $resolution, $am7), $am7);
    $end_t = min(round_t_up($row[2], $resolution, $am7) - $resolution, $pm7);

    // Calcul du nombre de créneaux qu'occupe la réservation
    $cellules[$row[4]]=($end_t-$start_t)/$resolution+1;
    // Initialisation du compteur
    $compteur[$row[4]]=0;

    for ($t = $start_t; $t <= $end_t; $t += $resolution)
    {
        $today[$row[0]][$t]["id"]    = $row[4];
        $today[$row[0]][$t]["color"] = $row[5];
        $today[$row[0]][$t]["data"]  = "";
        $today[$row[0]][$t]["who"] = "";
        $today[$row[0]][$t]["statut"] = $row[7];
        $today[$row[0]][$t]["moderation"] = $row[10];
        $today[$row[0]][$t]["option_reser"] = $row[9];
        // Construction des infos à afficher sur le planning
        $today[$row[0]][$t]["description"] = affichage_resa_planning($row[8],$row[4]);
        $today[$row[0]][$t]["pmsi"] = $row[12];
        $today[$row[0]][$t]["hds"] = $row[13];        
        $today[$row[0]][$t]["nda"] = $row[15];                
		  $today[$row[0]][$t]["duree"] = ($row[2]* 1) - ($row[1] *1);                
		  $today[$row[0]][$t]["heure"] =time_date_string($row[1],""); 
		 
    }

    # Show the name of the booker in the first segment that the booking
    # happens in, or at the start of the day if it started before today.
    if ($row[1] < $am7) {
        $today[$row[0]][$am7]["data"] = affichage_lien_resa_planning($row[3],$row[4]);
        // Info-bulle
        if (getSettingValue("display_info_bulle") == 1)
            $today[$row[0]][$am7]["who"] = get_vocab("reservation au nom de").affiche_nom_prenom_email($row[6],$row[11],"nomail");
        else if (getSettingValue("display_info_bulle") == 2)
            $today[$row[0]][$am7]["who"] = $row[8];
        else
            $today[$row[0]][$am7]["who"] = "";
    } else {
        $today[$row[0]][$start_t]["data"] = affichage_lien_resa_planning($row[3],$row[4]);
        // Info-bulle
        if (getSettingValue("display_info_bulle") == 1)
            $today[$row[0]][$start_t]["who"] = get_vocab("reservation au nom de").affiche_nom_prenom_email($row[6],$row[11]);
        else if (getSettingValue("display_info_bulle") == 2)
            $today[$row[0]][$start_t]["who"] = $row[8];
        else
            $today[$row[0]][$start_t]["who"] = "";
    }
}
# We need to know what all the rooms area called, so we can show them all
# pull the data from the db and store it. Convienently we can print the room
# headings and capacities at the same time

$sql = "select room_name, capacity, id, description, statut_room, show_fic_room, delais_option_reservation, moderate,picture_room from agt_room where service_id='".protect_data_sql($area)."' order by order_display, room_name";
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
    	  $temp_rooms[$row[2]]=$row[0];
        $room_name[$i] = $row[0];
        $id_room[$i] =  $row[2];
        $statut_room[$id_room[$i]] =  $row[4];
        $statut_moderate[$id_room[$i]] =  $row[7];
        if (strlen($row[8]) > 4) 
        	$picture_room="images/".$row[8];
        else
					$picture_room="./commun/images/editor.png";    
					          
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
            echo "<a href='admin_edit_room.php?room=$id_room[$i]'><img src=\"$picture_room\" alt=\"configuration\" border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a>";
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
						$c_date="$year-$month-$day";
            if ((!isset($id)) or (est_hors_reservation(mktime(0,0,0,$month,$day,$year))) or (est_fermee($c_date,$area)) ) // Le créneau est libre
            {
                $hour = date("H",$t);
                $minute  = date("i",$t);
                $date_booking = mktime($hour, $minute, 0, $month, $day, $year);
                echo "<center>";
								if (est_hors_reservation(mktime(0,0,0,$month,$day,$year)) or (est_fermee($c_date,$area))) {
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
                	  $link_pmsi="pmsi_entry.php?id=$id&amp;day=$day&amp;month=$month&amp;year=$year&amp;page=$page\"";
                	  //image HDS
                	  if ($today[$room][$t]["hds"]=="HDS"){
                	  		echo "<br /><img src=\"./commun/images/hds.gif\" alt=\"Codage\" border=\"0\" class=\"print_image\"  /><br /><br /> ";
                	  	}        
      	  
                	  
										//if ((strlen($today[$room][$t]["nda"])==9) and ($_SESSION['login']=="ADMIN")){
                	  		echo "<B>NDA:".$today[$room][$t]["nda"]."</B><br />";
                	  //}                	  
                	  /*
                	  if (86400 < $today[$room][$t]["duree"]){
                	  		echo "<br /><br /><img src=\"./commun/images/hds.gif\" alt=\"Codage\" border=\"0\" class=\"print_image\"  /> ";
                	  	}
                	  	*/ 
                	  	
                	  	
                	  if($_SESSION["URM"]=='470' || $_SESSION["URM"]=='560'){
	                	  if ($today[$room][$t]["pmsi"]=='1')
	                    		echo "<a href='".$link_pmsi."'><img src=\"./commun/images/stethoscope_non.jpg\" alt=\"Codage\" border=\"0\" class=\"print_image\"  /> </a>";                	
	                    else
	                    		echo "<a href='".$link_pmsi."'><img src=\"./commun/images/stethoscope.jpg\" alt=\"Codage\" border=\"0\" class=\"print_image\"  /> </a>";                	
                    	}
									//printing options
									$pats = explode("(", $descr);
									$p_nom=$pats[0];
									$noip=substr($pats[1],0,10); // à voir
									$p_ddn=substr($pats[2],0,10); // à voir 
									$p_sexe=substr($pats[3],0,1); // à voir 
									$duree= $duration .' '. $dur_units;
									$date_rdv	=date("d/m/Y-H:i",$date_rdv);
								 	$link="convocation_options.php?pat=$descr&uh=$uh&med=$med&duree=$duree&date_nais=$p_ddn&date_entree=$date_rdv&area=$area";
									//echo "<a href='#'  onClick=\"OpenPopup('$link')\" >&nbsp;&nbsp;<img src='images/print.jpg' alt='Print' border='0' class='print_image' width='20px' height='20px'/>  </a><br />";
									
									// icon SEXE 

									if($p_sexe=="M"){
										echo "<img src='./commun/images/homme.png' alt='Homme' border='0' class='print_image' width='20px' height='20px'/><br />";
									}else{
										echo "<img src='./commun/images/femme.png' alt='Femme' border='0' class='print_image' width='20px' height='20px'/><br />";									
									}

                    $for_print.=$descr;
                    $for_print.="(".$temp_rooms[$room];
                    $for_print.="(".$today[$room][$t]["heure"]. "Durée :".  ($today[$room][$t]["duree"] / 3600)." Hrs" ;

                	  if ($today[$room][$t]["hds"]=="HDS"){
                	  		$for_print.="(HDS = Oui|";
                	  	}else{
                	  		$for_print.="(HDS = Non|";
                	  	}                      
                    
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

include ("reprogram_div_top.php");


//===============================================================================================================
//Patients non venu
//==============================================================================================================
echo "<h2 align=center> Patients Non venus</h2>";
$sql = "SELECT agt_room.id, start_time, end_time, name, grr_nonvenu.id, type, beneficiaire, statut_entry, grr_nonvenu.description, grr_nonvenu.option_reservation, grr_nonvenu.moderate, beneficiaire_ext,pmsi,cause,reprogram,desc_cause 
   FROM grr_nonvenu, agt_room
   WHERE grr_nonvenu.room_id = agt_room.id
   AND service_id = '".protect_data_sql($area)."'
   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY agt_room.id";

$res = grr_sql_query($sql);

if (! $res) {
    fatal_error(0, grr_sql_error());
    include "./commun/include/trailer.inc.php";
    exit;
}

for ($t = 0; ($row = grr_sql_row($res, $t)); $t++) {
    		$room__id=$row[0];
        $today[$row[0]][$t]["id"]    = $row[4];
        $today[$row[0]][$t]["color"] = $row[5];
        $today[$row[0]][$t]["data"]  = "";
        $today[$row[0]][$t]["who"] = "";
        $today[$row[0]][$t]["statut"] = $row[7];
        $today[$row[0]][$t]["moderation"] = $row[10];
        $today[$row[0]][$t]["option_reser"] = $row[9];
		 $today[$row[0]][$t]["duree"] = $row[2]-$row[1];        
        // Construction des infos à afficher sur le planning
        $today[$row[0]][$t]["description"] = affichage_resa_planning_n($row[8],$row[4]);
        //$today[$row[0]][$t]["description"] = $row[4];

        if(($lastSTtime != $row[1])   
        		||( $lastroom !=$row[0])  
            ||($lastname !=$row[3])      
        		||($lastFNtime !=$row[2]))
        {
			  if (strlen($row[14]) >5)
			  		$link_reprogram= "<br /><b>Reprogrammé </b>".$row[14] ;       	 
			  else
			  		$link_reprogram= "<A href='javascript:centrerpopup(\"reprogram.php?id_non_venue=".$row[4]."\",440,230,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\"><br /><b>REPROGRAM</b></a>" ;       	 
			  
        	  $nonvenu[$room__id].="<span style=\"background-color:".get_color_class($row[5]). "; background-image: none; background-repeat: repeat; background-attachment: scroll;\">";
	        $nonvenu[$room__id].=	"<br />".date(hour_min_format(), $row[1]) . "~" . date(hour_min_format(), $row[2])."<br />";
	        $nonvenu[$room__id].="<B>".$row[3]."</B>-".$today[$row[0]][$t]["description"];        
	        $nonvenu[$room__id].=" (<B>Cause : </B>".$row[15].")";
	        $nonvenu[$room__id].= $link_reprogram;
	        $nonvenu[$room__id].="</span> <br />";

        }
        $lastroom=$row[0];       
        $lastSTtime=$row[1];       
        $lastname=$row[3];       
        $lastFNtime=$row[2];       

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

    echo "<table cellspacing='0' border=\"1\" width=\"100%\" id='myTable'>";
	  //------------------------------------------------		
     // Première ligne du tableau nom de chambre ou LIT
     //------------------------------------------------		
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
        if ($statut_moderate[$id_room[$i]] == "1") $temp .= "<br /><font color=\"#BA2828\"><b>".get_vocab("reservations_moderees")."</b></font>"; // 
        echo "<th width=\"$room_column_width%\"";
        // Si la ressource est temporairement indisponible, on le signale
        if ($statut_room[$id_room[$i]] == "0") echo " class='avertissement' ";
        echo ">" . htmlspecialchars($row[0])."\n";
        if (htmlspecialchars($row[3]. $temp != '')) {
            if (htmlspecialchars($row[3] != '')) $saut = "<br />"; else $saut = "";
            echo $saut."<i><span class =\"small\">". htmlspecialchars($row[3]) . $temp."\n</span></i>";
        }

        echo "<br />";

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


        $rooms_2[] = $row[2];
        $delais_option_reservation[$row[2]] = $row[6];
    }
    echo "<th width=\"5%\">&nbsp;</th></tr>\n";
    $tab[1][] = "&nbsp;";
	  //------------------------------------------------		
    // Deuxième ligne et lignes suivantes du tableau
     //------------------------------------------------	

    $tab_ligne = 3;
    // Début première boucle sur le temps

        # Show the time linked to the URL for highlighting that time
        echo "<tr>\n";


        tdcell("cell_hours");
        echo  "</td>\n";

        // Début Deuxième boucle sur la liste des ressources du domaine
        while (list($key, $room) = each($rooms_2))
        {
        	echo "<td>". $nonvenu[$room]."&nbsp;</td>";
        } // Fin Deuxième boucle sur la liste des ressources du domaine

        // Répétition de la première colonne
        // Si la ressource est temporairement indisponible, on le signale, sinon, couleur normale
        tdcell("cell_hours");

        echo  "&nbsp;</td>\n";
		  echo "</tr>\n";

        reset($rooms_2);
        $tab_ligne++;

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

if ($_SESSION['alert_nonvenu']!="NO"){
   echo "<script type=\"text/javascript\" language=\"javascript\">";
   echo "OpenPopup('alert_nonvenue.php')\n";
	echo "</script>";	
	$_SESSION['alert_nonvenu']	="NO";	
}

$link="area=".$area."&am7=".$am7."&pm7=".$pm7."&this_service_name=".$this_service_name."&jour=".$c_jour."&urm=".$_SESSION["URM"];
 
if($_SESSION["URM"]=="010"){
	echo "<table align='center'><tr><td align='leftr'><a href='#'  onClick=\"Window_010('".mysql_real_escape_string($link)."')\" > Rappel de RDV </a></td></tr></table>";
}else{
	echo "<table width='800px' align='center'><tr><td align='center' ><a href='#'  onClick=\"newWindow('".mysql_real_escape_string($link)."')\" > Format Imprimable  (par Salles)  </a></td> ";	
  echo  "<td align='center' widht='150px'><a href='#'  onClick=\"newWindow_1('".mysql_real_escape_string($link)."')\" >  <b>  Format Imprimable </b>(par Patient) </a></td> ";	
  echo  "<td align='center' widht='150px'><a href='#'  onClick=\"newWindow_2('".mysql_real_escape_string($link)."')\" >  <b>  Format Imprimable </b>(par Heure) </a></td></tr></table>";		
}
include "./commun/include/trailer.inc.php";
?>
