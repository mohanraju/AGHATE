<?php
#########################################################################
#                           admin_right.php                             #
#                                                                       #
#                       Interface de gestion des                        #
#                  droits de gestion des utilisateurs                   #
#                                                                       #
#                  Dernière modification : 28/03/2008                   #
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

include "./commun/include/admin.inc.php";
$grr_script_name = "admin_right.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

$day   = date("d");
$month = date("m");
$year  = date("Y");

if(authGetUserLevel(getUserName(),-1,'area') < 4)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";

?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php
$reg_admin_login = isset($_GET["reg_admin_login"]) ? $_GET["reg_admin_login"] : NULL;
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';

if ($reg_admin_login) {
    // On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
    if ($room !=-1) {
        // Ressource
        // On vérifie que la ressource $room existe
        $test = grr_sql_query1("select id from agt_room where id='".$room."'");
        if ($test == -1) {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }
        // La ressource existe : on vérifie les privilèges de l'utilisateur
        if(authGetUserLevel(getUserName(),$room) < 4)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }

        $sql = "SELECT * FROM agt_j_user_room WHERE (login = '$reg_admin_login' and id_room = '$room')";
        $res = grr_sql_query($sql);
        $test = grr_sql_count($res);
        if ($test != "0") {
            $msg = get_vocab("warning_exist");
        } else {
            if ($reg_admin_login != '') {
                $sql = "INSERT INTO agt_j_user_room SET login= '$reg_admin_login', id_room = '$room'";
                if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_user_succeed");}
            }
        }
    } else {
        // Domaine
        // On vérifie que le domaine $area existe
        $test = grr_sql_query1("select id from agt_service where id='".$area."'");
        if ($test == -1) {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }
        // Le domaine existe : on vérifie les privilèges de l'utilisateur
        if(authGetUserLevel(getUserName(),$area,'area') < 4)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }

        $sql = "select id from agt_room where service_id=$area";
        $res = grr_sql_query($sql);
        if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
            $sql2 = "select login from agt_j_user_room where (login = '$reg_admin_login' and id_room = '$row[0]')";
            $res2 = grr_sql_query($sql2);
            $nb = grr_sql_count($res2);
            if ($nb==0) {
                $sql3 = "insert into agt_j_user_room (login, id_room) values ('$reg_admin_login',$row[0])";
                if (grr_sql_command($sql3) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_user_succeed");}
            }
        }
    }
}

if ($action) {
    if ($action == "del_admin") {
        if(authGetUserLevel(getUserName(),$room) < 4)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }

        unset($login_admin); $login_admin = $_GET["login_admin"];
        $sql = "DELETE FROM agt_j_user_room WHERE (login='$login_admin' and id_room = '$room')";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
    }
    if ($action == "del_admin_all") {
        if(authGetUserLevel(getUserName(),$area,'area') < 4)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }

        $sql = "select id from agt_room where service_id=$area order by room_name";
        $res = grr_sql_query($sql);
        if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
            $sql2 = "DELETE FROM agt_j_user_room WHERE (login='".$_GET['login_admin']."' and id_room = '$row[0]')";
            if (grr_sql_command($sql2) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
        }
    }

}

if ((empty($area)) and (isset($row[0]))) {
    if(authGetUserLevel(getUserName(),$row[0],'area') >= 5) $area = get_default_area();
    else {
    # Retourne le domaine par défaut; Utilisé si aucun domaine n'a été défini.
// On cherche le premier domaine à accès non restreint
    $area = grr_sql_query1("SELECT a.id FROM agt_service a, agt_j_useradmin_area j
    WHERE a.id=j.id_area and j.login='".getUserName()."'
    ORDER BY a.access, a.order_display, a.service_name
    LIMIT 1");
    }
}
if (empty($room)) $room = -1;

echo "<h2>".get_vocab('admin_right.php').grr_help("aide_grr_gestion_ressources")."</h2>";
echo "<p><i>".get_vocab("admin_right_explain")."</i></p>";
// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

# Table with areas, rooms.
echo "<table><tr>";
$this_service_name = "";
$this_room_name = "";
# Show all areas
echo "<td ><p><b>".get_vocab("areas")."</b></p>";
$out_html = "<form name=\"area\" action=\"admin_right.php\" method=\"post\">\n<select name=\"area\" onChange=\"area_go()\">\n";
$out_html .= "<option value=\"admin_right.php?area=-1\">".get_vocab('select')."</option>\n";
    $sql = "select id, service_name from agt_service order by service_name";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $selected = ($row[0] == $area) ? "selected" : "";
        $link = "admin_right.php?area=$row[0]";
        // On affiche uniquement les domaines administrés par l'utilisateur
        if(authGetUserLevel(getUserName(),$row[0],'area') >= 4)
            $out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1])."</option>\n";
    }
    $out_html .= "</select>
    <SCRIPT type=\"text/javascript\" language=\"JavaScript\">
    <!--
    function area_go()
    {
    box = document.forms[\"area\"].area;
    destination = box.options[box.selectedIndex].value;
    if (destination) location.href = destination;
    }
    // -->
    </SCRIPT>
    <noscript>
    <input type=\"submit\" value=\"Change\" />
    </noscript>
    </form>";

echo $out_html;


$this_service_name = grr_sql_query1("select service_name from agt_service where id=$area");
$this_room_name = grr_sql_query1("select room_name from agt_room where id=$room");
$this_room_name_des = grr_sql_query1("select description from agt_room where id=$room");
echo "</td>\n";

# Show all rooms in the current area
echo "<td><p><b>".get_vocab('rooms')."</b></p>";

# should we show a drop-down for the room list, or not?
$out_html = "<form name=\"room\" action=\"admin_right.php\" method=\"post\"><select name=\"room\" onChange=\"room_go()\">";
$out_html .= "<option value=\"admin_right.php?area=$area&amp;room=-1\">".get_vocab('select_all');

    $sql = "select id, room_name, description from agt_room where service_id=$area order by order_display,room_name";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        if ($row[2]) {$temp = " (".htmlspecialchars($row[2]).")";} else {$temp="";}
        $selected = ($row[0] == $room) ? "selected" : "";
        $link = "admin_right.php?area=$area&amp;room=$row[0]";
        $out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1].$temp);
    }
    $out_html .= "</select>
       <SCRIPT type=\"text/javascript\" language=\"JavaScript\">
       <!--
       function room_go()
        {
        box = document.forms[\"room\"].room;
        destination = box.options[box.selectedIndex].value;
        if (destination) location.href = destination;
        }
        // -->
        </SCRIPT>

        <noscript>
        <input type=\"submit\" value=\"Change\" />
        </noscript>
        </form>";

echo $out_html;
echo "</td>\n";
echo "</tr></table>\n";

# Don't continue if this area has no rooms:
if ($area <= 0)
{
    echo "<h1>".get_vocab("no_area")."</h1>";
    // fin de l'affichage de la colonne de droite
    echo "</td></tr></table></body></html>";
    exit;
}
# Show area and room:
if ($this_room_name_des!='-1') {$this_room_name_des = " (".$this_room_name_des.")";} else {$this_room_name_des='';}
echo "<table border=1 cellpadding=5><tr><td>";
if ($room!='-1') {
    echo "<h3>".get_vocab("administration1")."</h3>";
    echo "<p>$this_room_name $this_room_name_des</p>\n";
} else {
    $is_admin='yes';
    echo "<h3>".get_vocab("administration2")."</h3>";
    $sql = "select id, room_name, description from agt_room where service_id=$area order by order_display,room_name";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        if ($row[2]) {$temp = " ($row[2])";} else {$temp="";}
        echo $row[1].$temp."<br />";
    }
}
?>
</td><td>
<?php
if ($room != -1) {
    $sql = "SELECT u.login, u.nom, u.prenom FROM agt_utilisateurs u, agt_j_user_room j WHERE (j.id_room='$room' and u.login=j.login)  order by u.nom, u.prenom";
    $res = grr_sql_query($sql);
    $nombre = grr_sql_count($res);
    if ($nombre!=0) echo "<h3>".get_vocab("user_list")."</h3>";
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $login_admin = $row[0];
        $nom_admin = $row[1];
        $prenom_admin = $row[2];
        echo "<b>";
        echo "$nom_admin $prenom_admin</b> | <a href='admin_right.php?action=del_admin&amp;login_admin=$login_admin&amp;room=$room&amp;area=$area'><font size=2>".get_vocab("delete")."</font></a><br />";
    }
    if ($nombre == 0) {
        echo "<h3><font color = red>".get_vocab("no_admin")."</font></h3>";
    }
} else {
    $exist_admin='no';
    $sql = "select login, nom, prenom from agt_utilisateurs where (statut='utilisateur' or statut='gestionnaire_utilisateur')";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $is_admin='yes';
        $sql2 = "select id, room_name, description from agt_room where service_id=$area order by order_display,room_name";
        $res2 = grr_sql_query($sql2);
        if ($res2) {
            $test = grr_sql_count($res2);
            if ($test != 0) {
                for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++)
                {
                $sql3 = "SELECT login FROM agt_j_user_room WHERE (id_room='".$row2[0]."' and login='".$row[0]."')";
                $res3 = grr_sql_query($sql3);
                $nombre = grr_sql_count($res3);
                if ($nombre==0) $is_admin='no';
                }
            } else {
                $is_admin='no';
            }
        }

        if ($is_admin=='yes') {
            if ($exist_admin=='no') {
                echo "<h3>".get_vocab("user_list")."</h3>";
                $exist_admin='yes';
            }
            echo "<b>";
            echo "$row[1] $row[2]</b> | <a href='admin_right.php?action=del_admin_all&amp;login_admin=$row[0]&amp;area=$area'><font size=2>".get_vocab("delete")."</font></a><br />";
        }
    }
    if ($exist_admin=='no') {
        echo "<h3><font color = red>".get_vocab("no_admin_all")."</font></h3>";
    }
}
?>
<h3><?php echo get_vocab("add_user_to_list");?></h3>
<form  action="admin_right.php" method='get'>
<select size=1 name=reg_admin_login>
<option value=''><?php echo get_vocab("nobody"); ?></option>
<?php
$sql = "SELECT login, nom, prenom FROM agt_utilisateurs WHERE  (etat!='inactif' and (statut='utilisateur'  or statut='gestionnaire_utilisateur')) order by nom, prenom";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
    if (authUserAccesArea($row[0],$area) == 1) {
        echo "<option value=$row[0]>$row[1]  $row[2] </option>";
    }
}
?>
</select>
<input type="hidden" name="area" value="<?php echo $area;?>" />
<input type="hidden" name="room" value=<?php echo $room;?> />
<input type="submit" value="Enregistrer" />
</form>
</td></tr></table>

<?php
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";
?>
</body>
</html>
