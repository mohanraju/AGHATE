<?php
#########################################################################
#                         admin_email_manager.php                       #
#                                                                       #
#                  Interface de gestion des mails automatiques          #
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
$grr_script_name = "admin_email_manager.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if(authGetUserLevel(getUserName(),-1,'area') < 4)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
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
// Si le
if (getSettingValue("automatic_mail") != 'yes')
    echo "<table border=\"1\"><tr><td><b><font color=\"#BA2828\" size=\"+1\">".get_vocab("attention_mail_automatique_désactive")."</font></b></td></tr></table>";

if (isset($_POST['mail1'])) {
  if (isset($_POST['send_always_mail_to_creator']))
      $temp = '1';
  else
      $temp = '0';
  if (!saveSetting("send_always_mail_to_creator", $temp)) {
        echo "Erreur lors de l'enregistrement de send_always_mail_to_creator !<br />";
        die();
  }
}


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

        $sql = "SELECT * FROM agt_j_mailuser_room WHERE (login = '$reg_admin_login' and id_room = '$room')";
        $res = grr_sql_query($sql);
        $test = grr_sql_count($res);
        if ($test != "0") {
            $msg = get_vocab("warning_exist");
        } else {
            if ($reg_admin_login != '') {
                $sql = "INSERT INTO agt_j_mailuser_room SET login= '$reg_admin_login', id_room = '$room'";
                if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("add_user_succeed");}
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

        $sql = "DELETE FROM agt_j_mailuser_room WHERE (login='".$_GET['login_admin']."' and id_room = '$room')";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("del_user_succeed");}
    }
}
affiche_pop_up($msg,"admin");

if (empty($area)) $area = get_default_area();
if (empty($room)) $room = -1;

echo "<h2>".get_vocab('admin_email_manager.php').grr_help("aide_grr_mail_auto")."</h2>";
echo get_vocab("explain_automatic_mail3")."<br /><br /><hr />";
echo "<form name=\"mail1\" action=\"admin_email_manager.php\" method=\"post\">";
echo "<INPUT TYPE=\"checkbox\" NAME=\"send_always_mail_to_creator\" VALUE=\"y\" ";
if (getSettingValue('send_always_mail_to_creator')=='1')
    echo ' checked ';
echo ' />';
echo get_vocab("explain_automatic_mail1");
echo '<br /><br /><center><input type="submit" name="mail1" value="'.get_vocab('save').'" /></center></form><hr />';
echo get_vocab("explain_automatic_mail2")."<br />";
echo $msg;
# Table with areas, rooms.
echo "<table><tr>";
$this_service_name = "";
$this_room_name = "";
# Show all areas
echo "<td ><p><b>".get_vocab("areas")."</b></p>";
$out_html = "<form name=\"area\" action=\"admin_email_manager.php\" method=\"post\"><select name=\"area\" onChange=\"area_go()\">";
$out_html .= "<option value=\"admin_email_manager.php?area=-1\">".get_vocab('select');
    $sql = "select id, service_name from agt_service order by service_name";
       $res = grr_sql_query($sql);
       if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
       {
        $selected = ($row[0] == $area) ? "selected" : "";
        $link = "admin_email_manager.php?area=$row[0]";
        // On affiche uniquement les domaines administrés par l'utilisateur
        if(authGetUserLevel(getUserName(),$row[0],'area') >= 4)
            $out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1]);
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
echo "<td><p><b>".get_vocab("rooms")."</b></p>";

# should we show a drop-down for the room list, or not?
$out_html = "<form name=\"room\" action=\"admin_email_manager.php\" method=\"post\"><select name=\"room\" onChange=\"room_go()\">";
$out_html .= "<option value=\"admin_email_manager.php?area=$area&amp;room=-1\">".get_vocab('select');

    $sql = "select id, room_name, description from agt_room where service_id=$area order by room_name";
       $res = grr_sql_query($sql);
       if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
       {
        if ($row[2]) {$temp = " (".htmlspecialchars($row[2]).")";} else {$temp="";}
        $selected = ($row[0] == $room) ? "selected" : "";
        $link = "admin_email_manager.php?area=$area&amp;room=$row[0]";
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
if ($room=='-1') {
    echo "<h1>".get_vocab("no_room")."</h1>";
    // fin de l'affichage de la colonne de droite
    echo "</td></tr></table></body></html>";
    exit;
} else {
    echo "<table border=1 cellpadding=5><tr><td>";
    $sql = "SELECT u.login, u.nom, u.prenom FROM agt_utilisateurs u, agt_j_mailuser_room j WHERE (j.id_room='$room' and u.login=j.login)  order by u.nom, u.prenom";
    $res = grr_sql_query($sql);
    $nombre = grr_sql_count($res);
    if ($nombre!=0) echo "<h3>".get_vocab("mail_user_list")."</h3>";
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $login_admin = $row[0];
        $nom_admin = $row[1];
        $prenom_admin = $row[2];
        echo "<b>";
        echo "$nom_admin $prenom_admin</b> | <a href='admin_email_manager.php?action=del_admin&amp;login_admin=$login_admin&amp;room=$room&amp;area=$area'><font size=2>".get_vocab("delete")."</font></a><br />";
    }
    if ($nombre == 0) {
        echo "<h3><font color = red>".get_vocab("no_mail_user_list")."</font></h3>";
    }
}
?>
<h3><?php echo get_vocab("add_user_to_list"); ?></h3>
<form action="admin_email_manager.php" method='get'>
<select size=1 name=reg_admin_login>
<option value=''><?php echo get_vocab("nobody"); ?></option>
<?php
$sql = "SELECT DISTINCT login, nom, prenom FROM agt_utilisateurs WHERE  (etat!='inactif' and email!='' and statut!='visiteur' ) order by nom, prenom";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
    if (authUserAccesArea($row[0],$area) == 1) {
        echo "<option value=$row[0]>$row[1]  $row[2] </option>";
    }
}
?>
</select>
<input type="hidden" name="add_admin" value="yes" />
<input type=hidden name=area value="<?php echo $area;?>" />
<input type=hidden name=room value=<?php echo $room;?> />
<input type="submit" value="Enregistrer" />
</form>
</td></tr></table>
<?php
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";
?>
</body>
</html>
