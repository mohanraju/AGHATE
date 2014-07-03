<?php
#########################################################################
#                            admin_type.php                             #
#                                                                       #
#            interface de gestion des types de réservations             #
#               Dernière modification : 28/03/2008                      #
#                                                                       #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau - Pascal Ragot
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
$grr_script_name = "admin_type.php";


if(authGetUserLevel(getUserName(),-1) < 5)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

$back = "";
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((isset($_GET['msg'])) and isset($_SESSION['displ_msg'])  and ($_SESSION['displ_msg']=='yes') )  {
   $msg = $_GET['msg'];
}
else
   $msg = '';
# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";

?>
<script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
<?php
//
// Suppression d'un type de réservation
//
if ((isset($_GET['action_del'])) and ($_GET['js_confirmed'] ==1) and ($_GET['action_del']='yes')) {
    // faire le test si il existe une réservation en cours avec ce type de réservation
    $type_id = grr_sql_query1("select type_letter from agt_type_area where id = '".$_GET['type_del']."'");
    $test1 = grr_sql_query1("select count(id) from agt_loc where type= '".$type_id."'");
    $test2 = grr_sql_query1("select count(id) from agt_repeat where type= '".$type_id."'");
    if (($test1 != 0) or ($test2 != 0)) {
        $msg =  "Suppression impossible : des réservations ont été enregistrées avec ce type.";
    } else {
        $sql = "DELETE FROM agt_type_area WHERE id='".$_GET['type_del']."'";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}
        $sql = "DELETE FROM agt_j_type_area WHERE id_type='".$_GET['type_del']."'";
        if (grr_sql_command($sql) < 0) {fatal_error(1, "<p>" . grr_sql_error());}

    }
}
affiche_pop_up($msg,"admin");

echo "<h2>".get_vocab('admin_type.php')."</h2>";
echo get_vocab('admin_type_explications');
echo "<br />\n";
echo "<br />\n";
echo "| <a href=\"admin_type_modify.php?id=0\">".get_vocab("display_add_type")."</a> |\n";
echo "<br />\n";
echo "<br />\n";
$sql = "SELECT id, type_name, order_display, couleur, type_letter FROM agt_type_area
ORDER BY order_display,type_letter";
$res = grr_sql_query($sql);
$nb_lignes = grr_sql_count($res);
if ($nb_lignes == 0) {
    // fin de l'affichage de la colonne de droite
    echo "</td></tr></table>";
    echo "</body></html>";
    die();
}



// Affichage du tableau
echo "<table border=\"1\" cellpadding=\"3\"><tr>\n";
// echo "<tr><td><b>".get_vocab("type_num")."</a></b></td>\n";
echo "<td><b>".get_vocab("type_num")."</b></td>\n";
echo "<td><b>".get_vocab("type_name")."</b></td>\n";
echo "<td><b>".get_vocab("type_color")."</b></td>\n";
echo "<td><b>".get_vocab("type_order")."</b></td>\n";
echo "<td><b>".get_vocab("delete")."</b></td>";
echo "</tr>";
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    $id_type        = $row[0];
    $type_name      = $row[1];
    $order_display     = $row[2];
    $couleur = $row[3];
    $type_letter = $row[4];
    // Affichage des numéros et descriptions
    $col[$i][1] = $type_letter;
    $col[$i][2] = $id_type;
    $col[$i][3] = $type_name;
    // Affichage de l'ordre
    $col[$i][4]= $order_display;
    $col[$i][5]= $couleur;

    echo "<tr>\n";
    echo "<td>{$col[$i][1]}</td>\n";
    echo "<td><a href='admin_type_modify.php?id_type={$col[$i][2]}'>{$col[$i][3]}</a></td>\n";
    echo "<td bgcolor='".$tab_couleur[$col[$i][5]]."'></td>\n";
    echo "<td>{$col[$i][4]}</td>\n";
    $themessage = get_vocab("confirm_del");
    echo "<td><a href='admin_type.php?&amp;type_del={$col[$i][2]}&amp;action_del=yes' onclick='return confirmlink(this, \"{$col[$i][1]}\", \"$themessage\")'>".get_vocab("delete")."</a></td>";
    // Fin de la ligne courante
    echo "</tr>";
    }
}

echo "</table>";

// Test de cohérence des types de réservation

    $res = grr_sql_query("select distinct type from agt_loc order by type");
    if ($res) {
        $liste = "";
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
            $test = grr_sql_query1("select type_letter from agt_type_area where type_letter='".$row[0]."'");
            if ($test == -1) $liste .= $row[0]." ";
        }
        if ($liste != "") {
            echo "<br /><table border=\"1\" cellpadding=\"5\"><tr><td><p><font color=red><b>ATTENTION : votre table des types de réservation n'est pas à jour :</b></font></p>";
            echo "<p>Un ou plusieurs types sont actuellement utilisés dans les réservations
            mais ne figurent pas dans la tables des types. Cela risque d'engendrer des messages d'erreur. <b>Il s'agit du ou des types suivants : ".$liste."</b>";
            echo "<br /><br />Vous devez donc définir ci-dessus, le ou les types manquants.</p></td></tr></table>";
        }
    }


// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";

?>
</body>
</html>
