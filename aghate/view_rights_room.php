<?php
#########################################################################
#                            view_rights_room.php                       #
#                                                                       #
#                          Liste des privilèges d'une ressource         #
#               Dernière modification : 10/07/2006                      #
#                                                                       #
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
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
$grr_script_name = "view_rights_room.php";
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

$id_room = isset($_GET["id_room"]) ? $_GET["id_room"] : NULL;
if (isset($id_room)) settype($id_room,"integer");

if (authGetUserLevel(getUserName(),$id_room) < 4)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
echo begin_page(getSettingValue("company").get_vocab("deux_points").get_vocab("mrbs"));

$res = grr_sql_query("SELECT * FROM agt_room WHERE id=$id_room");
if (! $res) fatal_error(0, get_vocab('error_room') . $id_room . get_vocab('not_found'));

$row = grr_sql_row_keyed($res, 0);
grr_sql_free($res);

?>
<h3 ALIGN=center><?php echo get_vocab("room").get_vocab("deux_points")."&nbsp;".htmlspecialchars($row["room_name"]);
$id_area = mrbsGetServiceIdByRoomId($id_room);
$service_name = grr_sql_query1("select service_name from agt_service where id='".$id_area."'");
$area_access = grr_sql_query1("select access from agt_service where id='".$id_area."'");
echo "<br />(".$service_name;
if ($area_access == 'r') echo " - <font color=\"#FF0000\">".get_vocab("access")."</font>";
echo ")";
echo "</H3>";

// On affiche pour les administrateurs les utilisateurs ayant des privilèges sur cette ressource
    echo "<h2>".get_vocab('utilisateurs ayant privileges')."</h2>";
    $a_privileges = 'n';
    // on teste si des utilateurs administre le domaine
    $req_admin = "select u.login, u.nom, u.prenom  from agt_utilisateurs u
    left join agt_j_useradmin_area j on u.login=j.login
    where j.id_area = '".$id_area."' order by u.nom, u.prenom";
    $res_admin = grr_sql_query($req_admin);
    $is_admin = '';
    if ($res_admin) {
        for ($j = 0; ($row_admin = grr_sql_row($res_admin, $j)); $j++) {
            $is_admin .= $row_admin[1]." ".$row_admin[2]." (".$row_admin[0].")<br />";
        }
    }
    if ($is_admin != '') {
        $a_privileges = 'y';
        echo "<H3><b>".get_vocab("utilisateurs administrateurs")."</b></H3>";
        echo $is_admin;
    }

    // On teste si des utilisateurs administrent la ressource
    $req_room = "select u.login, u.nom, u.prenom  from agt_utilisateurs u
    left join agt_j_user_room j on u.login=j.login
    where j.id_room = '".$id_room."' order by u.nom, u.prenom";
    $res_room = grr_sql_query($req_room);
    $is_gestionnaire = '';
    if ($res_room) {
       for ($j = 0; ($row_room = grr_sql_row($res_room, $j)); $j++) {
            $is_gestionnaire .= $row_room[1]." ".$row_room[2]." (".$row_room[0].")<br />";
       }
    }
    if ($is_gestionnaire != '') {
        $a_privileges = 'y';
        echo "<H3><b>".get_vocab("utilisateurs gestionnaires ressource")."</b></H3>";
        echo $is_gestionnaire;
    }

    // On teste si des utilisateurs reçoivent des mails automatiques
    $req_mail = "select u.login, u.nom, u.prenom  from agt_utilisateurs u
    left join agt_j_mailuser_room j on u.login=j.login
    where j.id_room = '".$id_room."' order by u.nom, u.prenom";
    $res_mail = grr_sql_query($req_mail);
    $is_mail = '';
    if ($res_mail) {
        for ($j = 0; ($row_mail = grr_sql_row($res_mail, $j)); $j++) {
            $is_mail .= $row_mail[1]." ".$row_mail[2]." (".$row_mail[0].")<br />";
        }
    }
    if ($is_mail != '') {
        $a_privileges = 'y';
        echo "<H3><b>".get_vocab("utilisateurs mail automatique")."</b></H3>";
        echo $is_mail;
    }

    // Si le domaine est restreint, on teste si des utilateurs y ont accès
    if ($area_access == 'r') {
        $req_restreint = "select u.login, u.nom, u.prenom  from agt_utilisateurs u
        left join agt_j_user_area j on u.login=j.login
        where j.id_area = '".$id_area."' order by u.nom, u.prenom";
        $res_restreint = grr_sql_query($req_restreint);
        $is_restreint = '';
        if ($res_restreint) {
            for ($j = 0; ($row_restreint = grr_sql_row($res_restreint, $j)); $j++) {
                $is_restreint .= $row_restreint[1]." ".$row_restreint[2]." (".$row_restreint[0].")<br />";
            }
        }
        if ($is_restreint != '') {
            $a_privileges = 'y';
            echo "<H3><b>".get_vocab("utilisateurs acces restreint")."</b></H3>";
            echo $is_restreint;
        }
    }
    if ($a_privileges == 'n') {
      echo get_vocab("aucun autilisateur").".";
  }
include "./commun/include/trailer.inc.php";
