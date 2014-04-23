<?php
#########################################################################
#                           admin_right_admin.php                       #
#                                                                       #
#                       Interface de gestion des                        #
#                  droits d\'administration des utilisateurs            #
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
$grr_script_name = "admin_right_admin.php";


$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if(authGetUserLevel(getUserName(),-1) < 5)
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
$reg_admin_login = isset($_GET["reg_admin_login"]) ? $_GET["reg_admin_login"] : NULL;
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';

if ($reg_admin_login) {
    $res = grr_sql_query1("select login from agt_j_useradmin_area where (login = '$reg_admin_login' and id_area = '$area')");
    if ($res == -1) {
        $sql = "insert into agt_j_useradmin_area (login, id_area) values ('$reg_admin_login',$area)";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_user_succeed");}
    }
}

if ($action) {
    if ($action == "del_admin") {
        unset($login_admin); $login_admin = $_GET["login_admin"];
        $sql = "DELETE FROM agt_j_useradmin_area WHERE (login='$login_admin' and id_area = '$area')";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
    }
}
if (empty($area)) $area = get_default_area();

echo "<h2>".get_vocab('admin_right_admin.php').grr_help("aide_grr_administateur_restreint")."</h2>";
echo "<p><i>".get_vocab("admin_right_admin_explain")."</i></p>";

// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

# Table with areas.
echo "<table><tr>";
$this_service_name = "";
# Show all areas
echo "<td ><p><b>".get_vocab("areas")."</b></p>";
$out_html = "<form name=\"area\" action=\"admin_right_admin.php\" method=\"post\"><select name=\"area\" onChange=\"area_go()\">";
$out_html .= "<option value=\"admin_right_admin.php?area=-1\">".get_vocab('select');
$sql = "select id, service_name from agt_service order by order_display";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    $selected = ($row[0] == $area) ? "selected" : "";
    $link = "admin_right_admin.php?area=$row[0]";
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
echo "</td>\n";
echo "</tr></table>\n";

# Don't continue if this area has no rooms:
if ($area <= 0)
{
    echo "<h1>".get_vocab("no_area")."</h1>";
    exit;
}
# Show area:
echo "<table border=1 cellpadding=5><tr><td>";
$is_admin='yes';
echo "<h3>".get_vocab("administration_domaine")."</h3>";
echo "<b>".$this_service_name."</b>";

?>
</td><td>
<?php
$exist_admin='no';
$sql = "select login, nom, prenom from agt_utilisateurs where (statut='utilisateur'   or statut='gestionnaire_utilisateur')";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
    $is_admin='yes';
    $sql3 = "SELECT login FROM agt_j_useradmin_area WHERE (id_area='".$area."' and login='".$row[0]."')";
    $res3 = grr_sql_query($sql3);
    $nombre = grr_sql_count($res3);
    if ($nombre==0) $is_admin='no';

    if ($is_admin=='yes') {
        if ($exist_admin=='no') {
            echo "<h3>".get_vocab("user_admin_area_list")."</h3>";
            $exist_admin='yes';
        }
        echo "<b>";
        echo "$row[1] $row[2]</b> | <a href='admin_right_admin.php?action=del_admin&amp;login_admin=$row[0]&amp;area=$area'><font size=2>".get_vocab("delete")."</font></a><br />";
    }
}
if ($exist_admin=='no') {
    echo "<h3><font color = red>".get_vocab("no_admin_this_area")."</font></h3>";
}

?>
<h3><?php echo get_vocab("add_user_to_list");?></h3>
<form action="admin_right_admin.php" method='get'>
<select size=1 name=reg_admin_login>
<option value=''><?php echo get_vocab("nobody"); ?></option>
<?php
$sql = "SELECT login, nom, prenom FROM agt_utilisateurs WHERE  (etat!='inactif' and (statut='utilisateur'   or statut='gestionnaire_utilisateur')) order by nom, prenom";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
    if (authUserAccesArea($row[0],$area) == 1) {
        echo "<option value=$row[0]>$row[1]  $row[2] </option>";
    }
}
?>
</select>
<input type="hidden" name="area" value="<?php echo $area;?>" />
<input type="submit" value="Enregistrer" />
</form>
</td></tr></table>

<?php
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";
?>
</body>
</html>
