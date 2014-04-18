<?php
#########################################################################
#                           admin_room                                  #
#                                                                       #
#                       Interface de gestion                            #
#             des accès restreints aux domainesrces                     #
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
$grr_script_name = "admin_access_area.php";

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
$reg_user_login = isset($_GET["reg_user_login"]) ? $_GET["reg_user_login"] : NULL;
$action = isset($_GET["action"]) ? $_GET["action"] : NULL;
$msg='';

// Si la table j_user_area est vide, il faut modifier la requête
$test_agt_j_user_area = grr_sql_count(grr_sql_query("SELECT * from agt_j_user_area"));

if ($reg_user_login) {
    // On commence par vérifier que le professeur n'est pas déjà présent dans cette liste.
    if ($area !=-1) {
        if(authGetUserLevel(getUserName(),$area,'area') < 4)
        {
            showAccessDenied($day, $month, $year, $area,$back);
            exit();
        }

        $sql = "SELECT * FROM agt_j_user_area WHERE (login = '$reg_user_login' and id_area = '$area')";
        $res = grr_sql_query($sql);
        $test = grr_sql_count($res);
        if ($test != "0") {
            $msg = get_vocab("warning_exist");
        } else {
            if ($reg_user_login != '') {
                $sql = "INSERT INTO agt_j_user_area SET login= '$reg_user_login', id_area = '$area'";
                if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}  else {$msg=get_vocab("add_user_succeed");}
            }
        }
        // define default area pour les utlisateurs par MOHAN le 13/09/10
        $sql = "SELECT default_area FROM agt_utilisateurs WHERE (login = '$reg_user_login')";
        $res = grr_sql_query($sql);
        $row = grr_sql_row($res, 0);
        if ($row[0][default_area] == "0") {
            $sql_update="update agt_utilisateurs set default_area = '$area'  WHERE (login = '$reg_user_login')";
            $res = grr_sql_query($sql_update);
         }
    }
}

if ($action=='del_user') {
    if(authGetUserLevel(getUserName(),$area,'area') < 4)
    {
        showAccessDenied($day, $month, $year, $area,$back);
        exit();
    }
    unset($login_user); $login_user = $_GET["login_user"];
    $sql = "DELETE FROM agt_j_user_area WHERE (login='$login_user' and id_area = '$area')";
    if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());} else {$msg=get_vocab("del_user_succeed");}
}
if (empty($area)) $area = -1;
echo "<h2>".get_vocab('admin_access_area.php').grr_help("aide_grr_domaine_restreint")."</h2>";
affiche_pop_up($msg,"admin");

echo "<table><tr>";
$this_service_name = "";
# Show all areas
$existe_domaine = 'no';
echo "<td ><p><b>".get_vocab('areas')."</b></p>";
$out_html = "<form name=\"area\" action=\"admin_access_area.php\" method=\"post\"><select name=\"area\" onChange=\"area_go()\">";
$out_html .= "<option value=\"admin_access_area.php?area=-1\">".get_vocab('select');
    $sql = "select id, service_name from agt_service where access='r' order by service_name";
    $res = grr_sql_query($sql);
    $nb = grr_sql_count($res);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $selected = ($row[0] == $area) ? "selected" : "";
        $link = "admin_access_area.php?area=$row[0]";
        // on affiche que les domaines que l'utilisateur connecté a le droit d'administrer
        if(authGetUserLevel(getUserName(),$row[0],'area') >= 4) {
            $out_html .= "<option $selected value=\"$link\">" . htmlspecialchars($row[1]);
            $existe_domaine = 'yes';
        }
    }
    $out_html .= "</select>
    <SCRIPT  type=\"text/javascript\" language=\"JavaScript\">
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

if ($existe_domaine == 'yes') echo $out_html;

$this_service_name = grr_sql_query1("select service_name from agt_service where id=$area");
echo "</td>\n";
echo "</tr></table>\n";

# Show area :
if ($area != -1) {
    echo "<table border=1 cellpadding=5><tr><td>";
    $sql = "SELECT u.login, u.nom, u.prenom FROM agt_utilisateurs u, agt_j_user_area j WHERE (j.id_area='$area' and u.login=j.login)  order by u.nom, u.prenom";
    $res = grr_sql_query($sql);
    $nombre = grr_sql_count($res);
    if ($nombre!=0) echo "<h3>".get_vocab("user_area_list")."</h3>";
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $login_user = $row[0];
        $nom_admin = $row[1];
        $prenom_admin = $row[2];
        echo "<b>";
        echo "$nom_admin $prenom_admin</b> | <a href='admin_access_area.php?action=del_user&amp;login_user=$login_user&amp;area=$area'><font size=2>".get_vocab("delete")."</font></a><br />";
    }
    if ($nombre == 0) {
        echo "<h3><font color = red>".get_vocab("no_user_area")."</font></h3>";
    }
    ?>
    <h3><?php echo get_vocab("add_user_to_list");?></h3>
    <form action="admin_access_area.php" method='get'>
    <select size=1 name=reg_user_login>
    <option value=''><p><?php echo get_vocab("nobody"); ?></p></option>;
    <?php
    $sql = "SELECT login, nom, prenom FROM agt_utilisateurs WHERE (etat!='inactif' and (statut='utilisateur' or statut='visiteur' or statut='gestionnaire_utilisateur' )) order by nom, prenom";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        echo "<option value=$row[0]><p>$row[1]  $row[2] </p></option>";
    }
    ?>
    </select>
    <input type="hidden" name="add_admin" value="yes" />
    <input type="hidden" name="area" value="<?php echo $area;?>" />
    <input type="submit" value="Enregistrer" />
    </form>
    </td></tr></table>
<?php
} else {
    if (($nb =0) or ($existe_domaine != 'yes')) {
        echo "<H3>".get_vocab("no_restricted_area")."</H3>";
    } else {
        echo "<H3>".get_vocab("no_area")."</H3>";
    }
}
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";

?>
</body>
</html>
