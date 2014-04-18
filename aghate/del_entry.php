<?php
#########################################################################
#                          del_entry.php                                #
#                                                                       #
#                  Interface de suppresssion d'une réservation          #
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
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
$grr_script_name = "del_entry.php";
// Settings
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


$mysql = new MySQL();


$series = isset($_GET["series"]) ? $_GET["series"] : NULL;
if (isset($series)) settype($series,"integer");
$page = verif_page();
if (isset($_GET["id"])) {
    $id = $_GET["id"];
    settype($id,"integer");
} else {
    die();
}

if($info = mrbsGetEntryInfo($id))
{
    $day   = strftime("%d", $info["start_time"]);
    $month = strftime("%m", $info["start_time"]);
    $year  = strftime("%Y", $info["start_time"]);
    $area  = mrbsGetServiceIdByRoomId($info["room_id"]);
    $back = "";
    if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
    if(authGetUserLevel(getUserName(),-1) < 1)
    {
        showAccessDenied($day, $month, $year, $area,$back);
        //echo "ok1";
        exit();
    }
    if(!getWritable($info["beneficiaire"], getUserName(),$id))
    {
        showAccessDenied($day, $month, $year, $area,$back);
        //echo "ok2";
        exit;
    }
    if(authUserAccesArea($_SESSION['login'], $area)==0)
    {
        showAccessDenied($day, $month, $year, $area,$back);
        //echo "ok3";
        exit();
    }
	//echo "passer";
    grr_sql_begin();
    if (getSettingValue("automatic_mail") == 'yes') {
        $_SESSION['session_message_error'] = send_mail($id,3,$dformat);
    }
    // On vérifie les dates
    $room_id = grr_sql_query1("SELECT agt_loc.room_id FROM agt_loc, agt_room WHERE agt_loc.room_id = agt_room.id AND agt_loc.id='".$id."'");
    $date_now = time();
    //echo "passer1";
    get_planning_area_values($area); // Récupération des données concernant l'affichage du planning du domaine
    //echo "stoped";
    if ((!(verif_booking_date(getUserName(), $id, $room_id, -1, $date_now, $enable_periods))) or
    ((verif_booking_date(getUserName(), $id, $room_id, -1, $date_now, $enable_periods)) and ($can_delete_or_create!="y"))
    )
    {
          showAccessDenied($day, $month, $year, $area,$back);
          exit();
    }

    $result = $Aghate->DelEntry(getUserName(), $id, $series, 1);
    grr_sql_commit();
    $c_day = $_SESSION['c_day']; // recuperer depuis day.php
    $c_month = $_SESSION['c_month'];// recuperer depuis day.php
    $c_year = $_SESSION['c_year'];// recuperer depuis day.php
    if ($result)
    {
        $_SESSION['displ_msg'] = 'yes';
        Header("Location: ".$page.".php?day=$c_day&month=$c_month&year=$c_year&area=$area&room=".$info["room_id"]);
        exit();
    }
}

// If you got this far then we got an access denied.
$day   = date("d");
$month = date("m");
$year  = date("Y");
showAccessDenied($day, $month, $year, $area,$back);
?>
