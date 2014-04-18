<?php
#########################################################################
#                           admin_room_del                              #
#                                                                       #
#                       Interface de confirmation                       #
#             de suppression d'un domaine ou d'une ressource            #
#                                                                       #
#                  Dernière modification : 24/05/2005                   #
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

include "./commun/include/admin.inc.php";
$grr_script_name = "admin_room_del.php";

$type = isset($_GET["type"]) ? $_GET["type"] : NULL;
$confirm = isset($_GET["confirm"]) ? $_GET["confirm"] : NULL;
$service_id = isset($_POST["service_id"]) ? $_POST["service_id"] : (isset($_GET["service_id"]) ? $_GET["service_id"] : NULL);
$area = isset($_POST["area"]) ? $_POST["area"] : (isset($_GET["area"]) ? $_GET["area"] : NULL);


if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);


#If we dont know the right date then make it up

if (empty($area))
    $area = get_default_area();

$day   = date("d");
$month = date("m");
$year  = date("Y");


# This is gonna blast away something. We want them to be really
# really sure that this is what they want to do.

if($type == "room")
{
    // Seuls les admin de la ressources peuvent supprimer la ressource

    if(authGetUserLevel(getUserName(),$room) < 4)
    {
        showAccessDenied($day, $month, $year, $area,$back);
        exit();
    }

    # We are supposed to delete a room
    if(isset($confirm))
    {
        # They have confirmed it already, so go blast!
        grr_sql_begin();
        # First take out all appointments for this room
        grr_sql_command("delete from agt_loc where room_id=$room");
        grr_sql_command("delete from agt_loc_moderate where room_id=$room");

        # Now take out the room itself
        grr_sql_command("delete from agt_room where id=$room");
        grr_sql_commit();

        # Go back to the admin page
        Header("Location: admin_room.php?area=$service_id");
    }
    else
    {
        # print the page header
        print_header("","","","",$type="with_session", $page="admin");


        # We tell them how bad what theyre about to do is
        # Find out how many appointments would be deleted

        $sql = "select name, start_time, end_time from agt_loc where room_id=$room";
        $res = grr_sql_query($sql);
        if (! $res) echo grr_sql_error();
        elseif (grr_sql_count($res) > 0)
        {
            echo get_vocab("deletefollowing") . ":<ul>";

            for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
            {
                echo "<li>$row[0] (";
                echo time_date_string($row[1],$dformat) . " -> ";
                echo time_date_string($row[2],$dformat) . ")";
            }

            echo "</ul>";
        }

        echo "<center>";
        echo "<H1>" .  get_vocab("sure") . "</h1>";
        echo "<H1><a href=\"admin_room_del.php?type=room&amp;room=$room&amp;confirm=Y&amp;service_id=$service_id\">" . get_vocab("YES") . "!</a> &nbsp;&nbsp;&nbsp; <a href=admin_room.php?area=$service_id>" . get_vocab("NO") . "!</a></h1>";
        echo "</center>";
    }
}

if($type == "area")
{
    // Seul l'admin peut supprimer un domaine
    if(authGetUserLevel(getUserName(),$room) < 5)
    {
        showAccessDenied($day, $month, $year, $area,$back);
        exit();
    }

    # We are only going to let them delete an area if there are
    # no rooms. its easier
    $n = grr_sql_query1("select count(*) from agt_room where service_id=$area");
    if ($n == 0)
    {
        // Suppression des champ additionnels
        $sqlstring = "select id from agt_overload where id_area='".$area."'";
        $result = grr_sql_query($sqlstring);
        for ($i = 0; ($field_row = grr_sql_row($result, $i)); $i++) {
            $id_overload = $field_row[0];
            // Suppression des données dans les réservations déjà effectuées
            grrDelOverloadFromEntries($id_overload);
            $sql = "delete from agt_overload where id=$id_overload;";
            grr_sql_command($sql);
        }
        # OK, nothing there, lets blast it away
        grr_sql_command("delete from agt_service where id=$area");
        grr_sql_command("update agt_utilisateurs set default_area = '0', default_room = '0' where default_area='".$area."'");
        $test = grr_sql_query1("select VALUE from agt_config where NAME='default_area'");
        if ($test==$area) {
            grr_sql_command("delete from agt_config where NAME='default_area'");
            grr_sql_command("delete from agt_config where NAME='default_room'");
            // Settings
            require_once("./commun/include/settings.inc.php");
            //Chargement des valeurs de la table settingS
            if (!loadSettings())
                die("Erreur chargement settings");

        }
        # Redirect back to the admin page
        header("Location: admin_room.php");
    }
    else
    {
        # There are rooms left in the area
        # print the page header
        print_header("","","","",$type="with_session", $page="admin");


        echo get_vocab('delarea');
        echo "<a href=admin_room.php?area=$area>" . get_vocab('back') . "</a>";
    }
}
?>
</body>
</html>