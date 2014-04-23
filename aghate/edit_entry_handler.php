<?php
#########################################################################
#                        edit_entry_handler.php                         #
#                                                                       #
#            Permet de vérifier la validitée de l'édition               #
#                ou de la crétion d'une réservation                     #
#                                                                       #
#            Dernière modification : 20/03/2008                         #
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
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/misc.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";

$grr_script_name = "edit_entry_handler.php";

$mysql = new MySQL();
$Aghate = new Aghate();

// Settings
date_default_timezone_set('Europe/Paris');
require_once("./commun/include/settings.inc.php");
//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

// Session related functions
require_once("./commun/include/session.inc.php");
// Resume session
if (!grr_resumeSession()) {
    header("Location: ./logout.php?auto=1");
    die();
};

// Paramètres langage
include "./commun/include/language.inc.php";

// Initialisation du test d'erreur
$erreur = 'n';

// Initialisation
$message_error = "";

if (isset($_GET["id"])) {
    // Il s'agit d'une modification de réservation
    // $id : identifiant de la réservation
    $id = $_GET["id"];
    settype($id,"integer");
} else
    // Il s'agit d'une nouvelle réservation
    $id = NULL;

$name = isset($_GET["name"]) ? $_GET["name"] : NULL;

// On verifie que le nom a bien été défini
if ((!isset($name) or (trim($name) == "")) and (getSettingValue("remplissage_description_breve")=='1')) {
    print_header($day, $month, $year, $area);
    echo "<H2>".get_vocab("required")."</H2>";
    include "./commun/include/trailer.inc.php";
    die();
}

// mohan portion pour inserer les patient 

$noip=isset($_GET["noip"]) ? $_GET["noip"] : NULL;
$nom=isset($_GET["nom"]) ? $_GET["nom"] : NULL;
$prenom=isset($_GET["prenom"]) ? $_GET["prenom"] : NULL;
$ddn=isset($_GET["ddn"]) ? $_GET["ddn"] : NULL;
$sexe=isset($_GET["sexe"]) ? $_GET["sexe"] : NULL;
    if ($noip == NULL) {
        print_header($day, $month, $year, $area);
        echo "<H2> Erreur récuperation Nip, veuillez recommencer </H2>";
        include "./commun/include/trailer.inc.php";
        die();
    }
Patients_add($noip,$nom,$prenom,$njf,$ddn,$sexe);
// fin patient add

$description = isset($_GET["description"]) ? $_GET["description"] : NULL;
$ampm = isset($_GET["ampm"]) ? $_GET["ampm"] : NULL;
$duration = isset($_GET["duration"]) ? $_GET["duration"] : NULL;
$duration = str_replace(",", ".", "$duration ");
$hour = isset($_GET["hour"]) ? $_GET["hour"] : NULL;

if (isset($hour)) {
    settype($hour,"integer");
    if ($hour > 23) $hour = 23;
}
$minute = isset($_GET["minute"]) ? $_GET["minute"] : NULL;
if (isset($minute)) {
    settype($minute,"integer");
    if ($minute > 59) $hour = 59;
}
/*
// rep_jour = variable Jours/Cycle sélectionnée
$rep_jour = isset($_GET["rep_jour"]) ? $_GET["rep_jour"] : NULL;
//Prend la valeur du Jour cycle
$rep_jour_c = 0;
for($i=1;$i<=getSettingValue("nombre_jours_Jours/Cycles");$i++){
	if(isset($rep_jour[$i])) {
		$rep_jour_c = $i;
	}
}*/
$rep_jour_c = isset($_GET["rep_jour_"]) ? $_GET["rep_jour_"] : 0;
$type = isset($_GET["type"]) ? $_GET["type"] : NULL;
$rep_type = isset($_GET["rep_type"]) ? $_GET["rep_type"] : NULL;
if (isset($rep_type)) settype($rep_type,"integer");
$rep_num_weeks = isset($_GET["rep_num_weeks"]) ? $_GET["rep_num_weeks"] : NULL;
if (isset($rep_num_weeks)) settype($rep_num_weeks,"integer");
if ($rep_num_weeks < 2) $rep_num_weeks = 1;
$rep_month = isset($_GET["rep_month"]) ? $_GET["rep_month"] : NULL;
if (($rep_type==3) and ($rep_month == 3)) $rep_type =3;
if (($rep_type==3) and ($rep_month == 5)) $rep_type =5;
$create_by = isset($_GET["create_by"]) ? $_GET["create_by"] : NULL;
$beneficiaire = isset($_GET["beneficiaire"]) ? $_GET["beneficiaire"] : "";
$benef_ext_nom = isset($_GET["benef_ext_nom"]) ? $_GET["benef_ext_nom"] : "";
$benef_ext_email = isset($_GET["benef_ext_email"]) ? $_GET["benef_ext_email"] : "";
$beneficiaire_ext = concat_nom_email($benef_ext_nom, $benef_ext_email);
$rep_id = isset($_GET["rep_id"]) ? $_GET["rep_id"] : NULL;
$rep_day = isset($_GET["rep_day"]) ? $_GET["rep_day"] : NULL;
$rep_end_day = isset($_GET["rep_end_day"]) ? $_GET["rep_end_day"] : NULL;
$rep_end_month = isset($_GET["rep_end_month"]) ? $_GET["rep_end_month"] : NULL;
$rep_end_year = isset($_GET["rep_end_year"]) ? $_GET["rep_end_year"] : NULL;
$room_back = isset($_GET["room_back"]) ? $_GET["room_back"] : NULL;
$hds= isset($_GET["hds"]) ? $_GET["hds"] : NULL;
$medecin= isset($_GET["medecin"]) ? $_GET["medecin"] : NULL;

$uh = isset($_GET["uh"]) ? $_GET["uh"] : NULL;
$id_prog = isset($_GET["id_prog"]) ? $_GET["id_prog"] : NULL;
$protocole_value = isset($_GET["protocole"]) ? $_GET["protocole"] : NULL;

if (isset($room_back)) settype($room_back,"integer");
$page = verif_page();
if ($page == '') $page="day";
$option_reservation = isset($_GET["option_reservation"]) ? $_GET["option_reservation"] : NULL;

if (isset($option_reservation))
    settype($option_reservation,"integer");
else
    $option_reservation = -1;
if (isset($_GET["confirm_reservation"]))
    $option_reservation = -1;
$type_affichage_reser = isset($_GET["type_affichage_reser"]) ? $_GET["type_affichage_reser"] : NULL;

// Dans le cas ou $beneficiaire est vide, on verifie que $beneficiaire_ext est correct
if (($beneficiaire) == "") {
    if ($beneficiaire_ext == "-1") {
        print_header($day, $month, $year, $area);
        echo "<H2>".get_vocab("required")."</H2>";
        include "./commun/include/trailer.inc.php";
        die();
    }
    if ($beneficiaire_ext == "-2") {
        print_header($day, $month, $year, $area);
        echo "<H2>Adresse email du bénéficiaire incorrecte</H2>";
        include "./commun/include/trailer.inc.php";
        die();
    }
} else $beneficiaire_ext = "";



// On verifie qu'une ressource au moins a bien été sélectionnée
if (!isset($_GET['rooms'][0])) {
    print_header($day, $month, $year, $area);
    echo "<H2>".get_vocab("choose_a_room")."</H2>";
    include "./commun/include/trailer.inc.php";
    die();
}
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

// On récupère la valeur de $area
$area = $Aghate->GetServiceIdByRoomId($_GET['rooms'][0]);

//Début de récupération des données additionnelles
$overload_data = array();
$overload_fields_list = mrbsOverloadGetFieldslist($area);

foreach ($overload_fields_list as $overfield=>$fieldtype)
{
  $id_field = $overload_fields_list[$overfield]["id"];
  $fieldname = "addon_".$id_field;
  if (($overload_fields_list[$overfield]["obligatoire"] == 'y') and ((!isset($_GET[$fieldname])) or (trim($_GET[$fieldname]) == ""))) {
    print_header($day, $month, $year, $area);
    echo "<H2>".get_vocab("required")."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
  }
  if (isset($_GET[$fieldname])) $overload_data[$id_field] = $_GET[$fieldname];
  else $overload_data[$id_field] = "";
}
//Fin de récupération des données additionnelles.

//If we dont know the right date then make it up
if(!isset($day) or !isset($month) or !isset($year))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
}

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

if(authGetUserLevel(getUserName(),-1) < 1)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}



if (check_begin_end_bookings($day, $month, $year))
{
    if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) $type_session = "no_session";
    else $type_session = "with_session";
    showNoBookings($day, $month, $year, $area, $back."&amp;Err=yes", $type_session);
    exit();
}


if ($type_affichage_reser == 0) {
    // La fin de réservation est calculée à partir d'une durée
    $period = isset($_GET["period"]) ? $_GET["period"] : NULL;
    if (isset($period)) settype($period,"integer");
    $dur_units = isset($_GET["dur_units"]) ? $_GET["dur_units"] : NULL;
    $all_day = isset($_GET["all_day"]) ? $_GET["all_day"] : NULL;
    
    // par mohan force  toutes la journe pour le service group de education et type est FERMER (attn area=11)
    if (($area=='11') && ($type=='AZ')){
    	$all_day="yes";
   	
    }
    
    if($enable_periods=='y') {
        $resolution = 60;
        $hour = 12;
        $minute = $period;
        $max_periods = count($periods_name);
        if( $dur_units == "periods" && ($minute + $duration) > $max_periods )
        {
            $duration = (24*60*floor($duration/$max_periods)) + ($duration%$max_periods);
        }
/*
        // Si la personne a spécifié des jours pour la durée et que le début de la réservation correspond au premier créneau
        if( $dur_units == "days" && $minute == 0 )
        {
            $dur_units = "periods";
            $duration = $max_periods + ($duration-1)*60*24;
        }
*/
    }
    // Units start in seconds
    $units = 1.0;

    switch($dur_units)
    {
        case "years":
            $units *= 52;
        case "weeks":
            $units *= 7;
        case "days":
            $units *= 24;
        case "hours":
            $units *= 60;
        case "periods":
        case "minutes":
            $units *= 60;
        case "seconds":
           break;
    }
    // Units are now in "$dur_units" numbers of seconds
   if(isset($all_day) && ($all_day == "yes") && ($dur_units!="days")) {
        if($enable_periods=='y') {
            $starttime = mktime(12, 0, 0, $month, $day, $year);
            $endtime   = mktime(12, $max_periods, 0, $month, $day, $year);
        } else {
      $starttime = mktime($morningstarts, 0, 0, $month, $day  , $year);
      $endtime   = mktime($eveningends, 0, $resolution, $month, $day, $year);
        }
    } else {
        if (!$twentyfourhour_format)
        {
          if (isset($ampm) && ($ampm == "pm"))
         {
           $hour += 12;
         }
        }
       $starttime = mktime($hour, $minute, 0, $month, $day, $year);
       $endtime   = mktime($hour, $minute, 0, $month, $day, $year) + ($units * $duration);
       if ($endtime <= $starttime)
           $erreur = 'y';

       # Round up the duration to the next whole resolution unit.
       # If they asked for 0 minutes, push that up to 1 resolution unit.
       $diff = $endtime - $starttime;
       if($resolution < 1 ) $resolution=1;
        if (($tmp = $diff % $resolution) != 0 || $diff == 0)
            $endtime += $resolution - $tmp;
            
           

   }
} else {

    // La fin de réservation est calculée à  partir d'une date
    // Cas particulier des réservation par créneaux pré-définis
    if($enable_periods=='y') {
        $resolution = 60;
        $hour = 12;
        $_GET["end_hour"] = 12;
        if (isset($_GET["period"]))
            $minute = $_GET["period"];
        else
            $erreur='y';
        if (isset($_GET["end_period"]))
            $_GET["end_minute"] = $_GET["end_period"]+1;
        else
            $erreur='y';
    }

    if (!isset($_GET["end_day"]) or !isset($_GET["end_month"]) or !isset($_GET["end_year"]) or !isset($_GET["end_hour"]) or !isset($_GET["end_minute"]))
    {
        $erreur = 'y';
    } else {
        $end_day = $_GET["end_day"];
        $end_year = $_GET["end_year"];
        $end_month = $_GET["end_month"];
        $end_hour = $_GET["end_hour"];
        $end_minute = $_GET["end_minute"];
        settype($end_month,"integer");
        settype($end_day,"integer");
        settype($end_year,"integer");
        settype($end_minute,"integer");
        settype($end_hour,"integer");
        $minyear = strftime("%Y", getSettingValue("begin_bookings"));
        $maxyear = strftime("%Y", getSettingValue("end_bookings"));
        if ($end_day < 1) $end_day = 1;
        if ($end_day > 31) $end_day = 31;
        if ($end_month < 1) $end_month = 1;
        if ($end_month > 12) $end_month = 12;
        if ($end_year < $minyear) $end_year = $minyear;
        if ($end_year > $maxyear) $end_year = $maxyear;

    //Si la date n'est pas valide on arrête
        if (!checkdate($end_month, $end_day, $end_year))
            $erreur = 'y';

        $starttime = mktime($hour, $minute, 0, $month, $day, $year);
        $endtime   = mktime($end_hour, $end_minute, 0, $end_month, $end_day, $end_year);

        if ($endtime <= $starttime)
            $erreur = 'y';

        # Round up the duration to the next whole resolution unit.
        # If they asked for 0 minutes, push that up to 1 resolution unit.
        $diff = $endtime - $starttime;
        if (($tmp = $diff % $resolution) != 0 || $diff == 0)
            $endtime += $resolution - $tmp;
        }
}



if ($endtime <= $starttime)
    $erreur = 'y';


// Si il y a tentative de réserver en-deça du temps limite
if ($erreur == 'y') {
    print_header($day, $month, $year, $area);
    echo "<H2>Erreur dans la date de fin de r&eacute;servation</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

if(isset($rep_type) && isset($rep_end_month) && isset($rep_end_day) && isset($rep_end_year))
// Si une périodicité a été définie
{
    // Get the repeat entry settings
    // Calcul de la date de fin de périodicité
    $rep_enddate = mktime($hour, $minute, 0, $rep_end_month, $rep_end_day, $rep_end_year);
    // Cas où la date de fin de périodicité est supérieure à la date de fin de réservation
    if ($rep_enddate > getSettingValue("end_bookings")) $rep_enddate = getSettingValue("end_bookings");
} else
    //  Si aucune périodicité n'a été définie
    $rep_type = 0;

if(!isset($rep_day))
    $rep_day = "";

// Dans le cas d'une réservation sans périodicité, on teste si la résa tombe un jour "hors réservation"
// On définit les jours temps "minuit" de début et fin
$day_temp   = date("d",$starttime);
$month_temp = date("m",$starttime);
$year_temp  = date("Y",$starttime);
$starttime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
$day_temp   = date("d",$endtime);
$month_temp = date("m",$endtime);
$year_temp  = date("Y",$endtime);
$endtime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
// On teste
if (resa_est_hors_reservation($starttime_midnight , $endtime_midnight )) {
    print_header($day, $month, $year, $area);
    echo "<H2>Erreur dans la date de début ou de fin de réservation</H2>";
    echo "<H3>Rservation dans Hors péroide/Congée/jour fériée </H3>";    
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}


# For weekly repeat(2), build string of weekdays to repeat on:
$rep_opt = "";
if ($rep_type == 2)
    for ($i = 0; $i < 7; $i++) $rep_opt .= empty($rep_day[$i]) ? "0" : "1";


# Expand a series into a list of start times:
if ($rep_type != 0)
    // $reps est un tableau des dates de début de réservation
    $reps = mrbsGetRepeatEntryList($starttime, isset($rep_enddate) ? $rep_enddate : 0,
        $rep_type, $rep_opt, $max_rep_entrys, $rep_num_weeks,$rep_jour_c);

# When checking for overlaps, for Edit (not New), ignore this entry and series:
$repeat_id = 0;
$ignore_id = 0;

# Acquire mutex to lock out others trying to book the same slot(s).
if (!grr_sql_mutex_lock('agt_loc'))
    fatal_error(1, get_vocab('failed_to_acquire'));

$date_now = time();
$error_booking_in_past = 'no';
$error_booking_room_out = 'no';
$error_duree_max_resa_area = 'no';
$error_delais_max_resa_room = 'no';
$error_delais_min_resa_room = 'no';
$error_date_option_reservation = 'no';
$error_chevaussement = 'no';
$error_qui_peut_reserver_pour = 'no';

foreach ( $_GET['rooms'] as $room_id ) {
	

 
if(authUserReserveRoom($_SESSION['login'], $room_id)==0)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}


	
    # On verifie qu'aucune réservation ne se situe dans la passé
    if ($rep_type != 0 && !empty($reps))  {
        $diff = $endtime - $starttime;
        // Dans le cas d'une réservation avec périodicité, on  vérifie que les différents créneaux ne se chevaussent pas.
        if (!$reservation->CheckOverlap($reps, $diff)) $error_chevaussement = 'yes';
        $i = 0;
        while (($i < count($reps)) and ($error_booking_in_past == 'no') and ($error_duree_max_resa_area == 'no') and ($error_delais_max_resa_room == 'no') and ($error_delais_min_resa_room == 'no') and ($error_date_option_reservation=='no') and ($error_qui_peut_reserver_pour ='no')) {
            if ((authGetUserLevel(getUserName(),-1) < 2) and (auth_visiteur(getUserName(),$room_id) == 0)) $error_booking_room_out = 'yes';
            if (!(verif_booking_date(getUserName(), -1, $room_id, $reps[$i], $date_now, $enable_periods))) $error_booking_in_past = 'yes';
            if (!(verif_duree_max_resa_area(getUserName(), $room_id, $starttime, $endtime))) $error_duree_max_resa_aera = 'yes';
            if (!(verif_delais_max_resa_room(getUserName(), $room_id, $reps[$i]))) $error_delais_max_resa_room = 'yes';
            if (!(verif_delais_min_resa_room(getUserName(), $room_id, $reps[$i]))) $error_delais_min_resa_room = 'yes';
            if (!(verif_date_option_reservation($option_reservation, $reps[$i]))) $error_date_option_reservation = 'yes';
            if (!(verif_qui_peut_reserver_pour($room_id, $create_by))) $error_qui_peut_reserver_pour = 'yes';
            //echo "Verif1".$error_qui_peut_reserver_pour;
            $i++;
        }
    } else {
        if ((authGetUserLevel(getUserName(),-1) < 2) and (auth_visiteur(getUserName(),$room_id) == 0)) $error_booking_room_out = 'yes';
        if (isset($id) and ($id!=0)) {
            if (!(verif_booking_date(getUserName(), $id, $room_id, $starttime, $date_now, $enable_periods, $endtime))) $error_booking_in_past = 'yes';
        } else {
            if (!(verif_booking_date(getUserName(), -1, $room_id, $starttime, $date_now, $enable_periods))) $error_booking_in_past = 'yes';
        }
        if (!(verif_duree_max_resa_area(getUserName(), $room_id, $starttime, $endtime))) $error_duree_max_resa_area = 'yes';
        if (!(verif_delais_max_resa_room(getUserName(), $room_id, $starttime))) $error_delais_max_resa_room = 'yes';
        if (!(verif_delais_min_resa_room(getUserName(), $room_id, $starttime))) $error_delais_min_resa_room = 'yes';
        if (!(verif_date_option_reservation($option_reservation, $starttime))) $error_date_option_reservation = 'yes';
        if (!(verif_qui_peut_reserver_pour($room_id, $create_by))) $error_qui_peut_reserver_pour = 'yes';
//        echo "Verif2".$error_qui_peut_reserver_pour;
    }
    $info_room = $Aghate->GetRoomInfoByRoomId($room_id);
    $statut_room = $info_room['statut_room'];
    // on vérifie qu\'un utilisateur non autorisé ne tente pas de réserver une ressource non disponible
    if (($statut_room == "0") and authGetUserLevel(getUserName(),$room_id) < 3)
        $error_booking_room_out = 'yes';
} # end foreach rooms

// Si le test précédent est passé avec succès,
# Check for any schedule conflicts in each room we're going to try and
# book in
$err = "";
$room_name = $Aghate->GetRoomNameByRoomId($room_id);


if (($error_booking_in_past == 'no') and ($error_chevaussement=='no') and ($error_duree_max_resa_area == 'no') and ($error_delais_max_resa_room == 'no') and ($error_delais_min_resa_room == 'no')  and ($error_date_option_reservation == 'no') and ($error_qui_peut_reserver_pour == 'no')) {
   
    foreach ( $_GET['rooms'] as $room_id ) {
        if ($rep_type != 0 && !empty($reps))  {
            if(count($reps) < $max_rep_entrys) {
                $diff = $endtime - $starttime;
                for($i = 0; $i < count($reps); $i++) {
                    // Suppression des résa en conflit
                    if (isset($_GET['del_entry_in_conflict']) and ($_GET['del_entry_in_conflict']=='yes'))
                        $Aghate->DelEntryInConflict($room_id, $reps[$i], $reps[$i] + $diff, $ignore_id, $repeat_id, 0);
                    // On teste s'il reste des conflits
                    if ($room_name != 'Panier') 
                    {
						if ($i == (count($reps)-1)) {
						   $tmp = $Aghate->CheckRoomDispo($room_id, $reps[$i], $reps[$i] + $diff);
						} else{
						   $tmp = $Aghate->CheckRoomDispo($room_id, $reps[$i], $reps[$i] + $diff);
					   }
				    }
				 
                    if(!empty($tmp)) $err = $err . $tmp;
                }
            } else {
                $err .= get_vocab("too_may_entrys") . "<P>";
                $hide_title  = 1;
            }
        } 
        else 
        {
					// Suppression des résa en conflit
          if ($room_name != 'Panier') 
          {
			   		if (isset($_GET['del_entry_in_conflict']) and ($_GET['del_entry_in_conflict']=='yes'))
				   	$Aghate->DelEntryInConflict($room_id, $starttime, $endtime-1, $ignore_id, $repeat_id, 0);
			   		// On teste s'il y a des conflits
						$err .= $Aghate->CheckRoomDispo($room_id, $starttime, $endtime-1,0,$id);
						
					}
					else
					{
						// Delete the original entry
						/*if(isset($id) and ($id!=0)) {
							if ($rep_type != 0)
								$Aghate->DelEntry(getUserName(), $id, "series", 1);
							else
								$Aghate->DelEntry(getUserName(), $id, "", 1);
						}*/
						$var = $Aghate->CheckDispoLitInArea($starttime, $endtime,$area,$id);
						if (strlen($var)>1){ 
							$err .= $var." " .$room_name ;
						}
					}
        }
    } # end foreach rooms
}



// Si tous les tests précédents sont passés avec succès :
if (empty($err)
    and ($error_booking_in_past == 'no')
    and ($error_duree_max_resa_area == 'no')
    and ($error_delais_max_resa_room == 'no')
    and ($error_delais_min_resa_room == 'no')
    and ($error_booking_room_out == 'no')
    and ($error_date_option_reservation == 'no')
    and ($error_chevaussement == 'no')
    and ($error_qui_peut_reserver_pour == 'no')
    )
{
    // On teste si l'utilisateur a le droit d'effectuer la série de réservation, compte tenu des
    // réser déjà effectuées et de la limite posée sur la ressource
    foreach ( $_GET['rooms'] as $room_id ) {
        $area = $Aghate->GetServiceIdByRoomId($room_id);
        // Contrôle droit d'écriture
        if (isset($id) and ($id!=0)) {
            if(!getWritable($beneficiaire, getUserName(),$id))
            {
                showAccessDenied($day, $month, $year, $area,$back);
                exit;
            }
        }
        // Contrôle accès restreint
        if(authUserAccesArea($_SESSION['login'], $area)==0)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }
        if ($rep_type != 0 && !empty($reps))  {
            if(UserRoomMaxBooking(getUserName(), $room_id, count($reps)) == 0) {
               showAccessDeniedMaxBookings($day, $month, $year, $area, $room_id, $back);
              exit();
            }
        } else {
            if(UserRoomMaxBooking(getUserName(), $room_id, 1) == 0) {
               showAccessDeniedMaxBookings($day, $month, $year, $area, $room_id, $back);
              exit();
            }
        }
    }




	foreach ( $_GET['rooms'] as $room_id )
	{
	  // On détertermine s'il faut ou non modérer la réservation et s'il faut ou non envoyer un mail de demande de modération
	  $info_room = $Aghate->GetRoomInfoByRoomId($room_id);
	  $moderate = $info_room['moderate'];
	  if ($moderate==1) { // La ressource est modérée
		$send_mail_moderate = 1; // Par défaut on envoie un mail de demande de modération
		if (isset($id)) { // Il s'agit d'une modification
			//$old_entry_moderate =  grr_sql_query1("select moderate from agt_loc where id='".$id."'");
			if (authGetUserLevel(getUserName(),$room_id) < 3)
				// l'utilisateur n'est pas gestionnaire ou admin de la ressource donc on met la réservation en attente de modération
				$entry_moderate = 1;
			else
				// l'utilisateur est gestionnaire ou admin de la ressource donc on ne modifie pas l'état de modération.
				$entry_moderate = $old_entry_moderate;
			if ($entry_moderate==0) // la résa est déjà modérée, il s'agit donc d'une modification
				$send_mail_moderate = 0;
	/*        else if ($entry_moderate==3) {
				// Il n'est pas possible de modifier une réservatiion refusée
				fatal_error(0,"Opération interdite");
				exit();
			}*/

		} else { // Il s'agit d'une création :
			if (authGetUserLevel(getUserName(),$room_id) < 3)
				// l'utilisateur n'est pas gestionnaire ou admin de la ressource donc on modére la réservation
				$entry_moderate = 1;
			else {
				// l'utilisateur est gestionnaire ou admin de la ressource donc on ne modère pas !
				$entry_moderate = 0;
				$send_mail_moderate = 0;
			}
		 }
	  } else {
		 $entry_moderate = 0;
		 $send_mail_moderate = 0;
	  }
  //par mohan vérifiation reprogramation
	/*if (strlen($_SESSION['REPROGMATION']) > 2){
		list($r_id,$r_noip,$nom_pat)=explode("|",$_SESSION['REPROGMATION']);
			
	}*/
 
  /*if($rep_type != 0)
    {
      mrbsCreateRepeatingEntrys($starttime, $endtime,   $rep_type, $rep_enddate, $rep_opt,
                $room_id, $create_by, $beneficiaire, $beneficiaire_ext, $name, $type, $description, $rep_num_weeks, $option_reservation,$overload_data, $entry_moderate,$rep_jour_c,$noip,$hds,$medecin);
		// annulation de reprogramation
		if(trim($r_noip)==trim($noip)){
			$_SESSION['REPROGMATION']="";
			$sql= "select room_name from agt_room where id='".protect_data_sql($room_id)."'";
			$room_name="Dans :".grr_sql_query1($sql);
			$room_name.=" Le ".date("d/m/Y",$starttime) ." à ".date(hour_min_format(), $starttime) . "~" . date(hour_min_format(), $endtime);
			$sql="update grr_nonvenu set reprogram='".protect_data_sql($room_name)."' where grr_nonvenu.id='".$r_id."'";
			$cr =  grr_sql_query1($sql);
		}    

//      $new_id = grr_sql_insert_id("agt_loc", "id");
      if (getSettingValue("automatic_mail") == 'yes')
    {
      if (isset($id) and ($id!=0))
          // $id_first_resa, calculé dans  mrbsCreateRepeatingEntrys correspond à l'id de la première réservation de la série
          if ($send_mail_moderate)
              $message_error = send_mail($id_first_resa,5,$dformat);
          else
              $message_error = send_mail($id_first_resa,2,$dformat);

      else
          if ($send_mail_moderate)
              $message_error = send_mail($id_first_resa,5,$dformat);
          else
              $message_error = send_mail($id_first_resa,1,$dformat);
    }

    }
  else
    {*/
      // Mark changed entry in a series with entry_type 2:
      if ($repeat_id > 0)
    $entry_type = 2;
      else
    $entry_type = 0;
    
    // Delete the original entry
    /*if(isset($id) and ($id!=0)) {
        if ($rep_type != 0)
            $Aghate->DelEntry(getUserName(), $id, "series", 1);
        else
            $Aghate->DelEntry(getUserName(), $id, "", 1);
    } */

    
    $service_id = $Aghate->GetServiceIdByRoomId($room_id);
    
	$TableauData = array();
	$TableauData['start_time'] = $starttime;
	$TableauData['end_time'] = $endtime -1;
	$TableauData['room_id'] = $room_id;
	$TableauData['create_by'] = $create_by;
	//$TableauData['name'] = $name;
	$TableauData['type'] = $type;
	$TableauData['description'] = $description;
	$TableauData['statut_entry'] = '' ;
	$TableauData['noip'] = $noip;
	$TableauData['medecin'] = $medecin;
	$TableauData['uh'] = $uh;
	$TableauData['de_source'] = "Programme";
	$TableauData['ds_source'] = "Programme";
	$TableauData['protocole'] = $protocole_value;
	$TableauDataCd['id'] = $id;
	
	$TableauProg['noip'] = $noip;
	$TableauProg['start_time_prog'] = $starttime;
	$TableauProg['end_time_prog'] = $endtime-1; 
	$TableauProg['medecin'] = $medecin;
	$TableauProg['room_id'] = $room_id;
	$TableauProg['service_id'] = $service_id;
	$TableauProg['medecin'] = $medecin;
	$TableauProg['protocole'] =  $protocole_value;
	$TableauProg['create_by'] = $create_by;
	$TableauProgCd['id'] = $id_prog;
	
	if (strlen($id_prog)>0){
		$TableauData['id_prog'] = $id_prog;				
		$id_prog = $Aghate->UpdateEntry('agt_prog',$TableauProg,$TableauProgCd);
	}
	else{	
		// Create the entry:
		$id_prog = $Aghate->CreateSingleEntry('agt_prog',$TableauProg);
		$TableauData['id_prog'] = $id_prog;		
	}
	

	
 
	if(strlen($id)>0){
		$id = $Aghate->UpdateEntry('agt_loc',$TableauData,$TableauDataCd);
		$Aghate->InsertOverloadData($id,$overload_data,$room_id);
	}
	else{
		$new_id = $Aghate->CreateSingleEntry('agt_loc',$TableauData);
		$Aghate->InsertOverloadData($new_id,$overload_data,$room_id);
	}
   
   
 
    


} // end foreach $rooms

 
    grr_sql_mutex_unlock('agt_loc');

    $area = $Aghate->GetServiceIdByRoomId($room_id);
    # Now its all done go back to the day view
    $_SESSION['displ_msg'] = 'yes';
    if ($message_error != "")
        $_SESSION['session_message_error'] = $message_error;
    Header("Location: ".$page.".php?year=$year&month=$month&day=$day&area=$area&room=$room_back");
    exit;
}

# The room was not free.
grr_sql_mutex_unlock('agt_loc');



// Si il y a tentative de réserver dans le passé
if ($error_booking_in_past == 'yes') {
    $str_date = utf8_strftime("%d %B %Y, %H:%M", $date_now);
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("booking_in_past") . "</H2>";
    if ($rep_type != 0 && !empty($reps))  {
        echo "<p>" . get_vocab("booking_in_past_explain_with_periodicity") . $str_date."</p>";
    } else {
        echo "<p>" . get_vocab("booking_in_past_explain") . $str_date."</p>";
    }
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Si il y a tentative de réserver pendant une durée dépassant la durée max
if ($error_duree_max_resa_area == 'yes') {
	$info_room = $Aghate->GetRoomInfoByRoomId($room_id);
    $service_id = $info_room['service_id'];
    $info_area = $Aghate->GetServiceInfoByServiceId($service_id);
    $duree_max_resa_area = $info_area[0]["duree_max_resa_area"];
    print_header($day, $month, $year, $area);
    $temps_format = $duree_max_resa_area*60;
    toTimeString($temps_format, $dur_units);
    echo "<H2>" . get_vocab("error_duree_max_resa_area").$temps_format ." " .$dur_units."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Si il y a tentative de réserver au delà du temps limite
if ($error_delais_max_resa_room == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("error_delais_max_resa_room") ."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Dans le cas d'une réservation avec périodicité, s'il y a des créneaux qui se chevaussent
if ($error_chevaussement == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("error_chevaussement") ."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Si il y a tentative de réserver en-deça du temps limite
if ($error_delais_min_resa_room == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("error_delais_min_resa_room") ."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Si la date confirmation est supérieure à la date de début de réservation
if ($error_date_option_reservation == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("error_date_confirm_reservation") ."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}



// Si l'utilisateur tente de réserver une ressource non disponible
if ($error_booking_room_out == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("norights") . "</H2>";
    echo "<p><b>" . get_vocab("tentative_reservation_ressource_indisponible") . "</b></p>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

// Si l'utilisateur tente de réserver au nom d'une autre personne pour une ressource pour laquelle il n'a pas le droit
if ($error_qui_peut_reserver_pour == 'yes') {
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("error_qui_peut_reserver_pour") ."</H2>";
    echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a>";
    include "./commun/include/trailer.inc.php";
    die();
}

echo $err;

if(strlen($err))
{
    print_header($day, $month, $year, $area);
    echo "<H2>" . get_vocab("sched_conflict") . "</H2>";
    if(!isset($hide_title))
    {
        echo get_vocab("conflict");
        echo "<UL>";
    }
    echo $err;

    if(!isset($hide_title))
        echo "</UL>";
        // possibilité de supprimer la (les) réservation(s) afin de valider la nouvelle réservation.
        if(authGetUserLevel(getUserName(),$area,'area') >= 4)
            echo "<center><table border=\"1\" cellpadding=\"10\" cellspacing=\"1\"><tr><td class='avertissement'><h3><a href='".traite_grr_url("","y")."edit_entry_handler.php?".$_SERVER['QUERY_STRING']."&amp;del_entry_in_conflict=yes'>".get_vocab("del_entry_in_conflict")."</a></h4></td></tr></table></center><br />";

}
// Retour au calendrier


echo "<a href=\"".$back."&amp;Err=yes\">".get_vocab('returnprev')."</a><p>";

include "./commun/include/trailer.inc.php"; 

?>
