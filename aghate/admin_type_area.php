<?php
#########################################################################
#                            admin_type_area.php                        #
#                                                                       #
#            interface de gestion des types de réservations             #
#                           pour un domaine                             #
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
$grr_script_name = "admin_type_area.php";

// Initialisation
$service_id = isset($_GET["service_id"]) ? $_GET["service_id"] : NULL;

if(authGetUserLevel(getUserName(),$service_id,'area') < 4)
{
    $back = '';
    if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

$back = "";
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

// Gestion du retour à la page précédente sans enregistrement
if (isset($_GET['change_done']))
{
    Header("Location: "."admin_room.php");
    exit();
}

if ((isset($_GET['msg'])) and isset($_SESSION['displ_msg'])  and ($_SESSION['displ_msg']=='yes') )  {
   $msg = $_GET['msg'];
}
else
   $msg = '';
# print the page header
print_header("","","","",$type="with_session", $page="admin");

?>
<script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
<?php

$sql = "SELECT id, type_name, order_display, couleur, type_letter FROM agt_type_area
ORDER BY order_display, type_letter";


//
// Enregistrement
//
if (isset($_GET['valider']))  {
    $res = grr_sql_query($sql);
    $nb_types_valides = 0;
    if ($res) {
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        if (isset($_GET[$row[0]])) {
            $nb_types_valides ++;
            $del = grr_sql_query("delete from agt_j_type_area where id_area='".$service_id."' and id_type = '".$row[0]."'");
        } else {
            $type_si_aucun = $row[0];
            $test = grr_sql_query1("select count(id_type) from agt_j_type_area where id_area = '".$service_id."' and id_type = '".$row[0]."'");
            if ($test == 0) {
                // faire le test si il existe une réservation en cours avec ce type de réservation
//                $type_id = grr_sql_query1("select type_letter from agt_type_area where id = '".$row[0]."'");
//                $test1 = grr_sql_query1("select count(id) from agt_loc where type= '".$type_id."'");
//                $test2 = grr_sql_query1("select count(id) from agt_repeat where type= '".$type_id."'");
//                if (($test1 != 0) or ($test2 != 0)) {
//                    $msg =  "Suppression impossible : des réservations ont été enregistrées avec ce type.";
//                } else {
                    $sql1 = "insert into agt_j_type_area set id_area='".$service_id."', id_type = '".$row[0]."'";
                    if (grr_sql_command($sql1) < 0) {fatal_error(1, "<p>" . grr_sql_error());}
//                }

            }
        }
        }
    }
    if ($nb_types_valides == 0) {
        // Aucun type n'a été sélectionné. Dans ce cas, on impose au moins un type :
        $del = grr_sql_query("delete from agt_j_type_area where id_area='".$service_id."' and id_type = '".$type_si_aucun."'");
        $msg = "Vous devez au définir au moins un type valide !";
    }

    // Type par défaut :
    // On enregistre le nouveau type par défaut :
    $reg_type_par_defaut = grr_sql_query("update agt_service set id_type_par_defaut='".$_GET['id_type_par_defaut']."' where id='".$service_id."'");


}
affiche_pop_up($msg,"admin");

$service_name = grr_sql_query1("select service_name from agt_service where id='".$service_id."'");
echo "<center><h2>".get_vocab('admin_type.php')."</h2>";
echo "<h2>".get_vocab("match_area").get_vocab('deux_points')." ".$service_name."</h2></center>";

$res = grr_sql_query($sql);
$nb_lignes = grr_sql_count($res);
if ($nb_lignes == 0) {
    echo "</body></html>";
    die();
}
echo "<form action=\"admin_type_area.php\" name=\"type\" method=\"get\">\n";
echo "<center><table width=\"80%\">";
if(authGetUserLevel(getUserName(),-1) >= 5)
echo "<tr><td><a href=\"admin_type_modify.php?id=0\">".get_vocab("display_add_type")."</a></td></tr>";
echo "<tr><td>".get_vocab("explications_active_type")."</td></tr>";
echo "<tr><td>\n";
// Affichage du tableau
echo "<table border=\"1\" cellpadding=\"3\"><tr>\n";
// echo "<tr><td><b>".get_vocab("type_num")."</a></b></td>\n";
echo "<td><b>".get_vocab("type_num")."</b></td>\n";
echo "<td><b>".get_vocab("type_name")."</b></td>\n";
echo "<td><b>".get_vocab("type_color")."</b></td>\n";
echo "<td><b>".get_vocab("type_order")."</b></td>\n";
echo "<td><b>".get_vocab("type_valide_domaine")."</b></td>";
echo "<td><b>".get_vocab("type_par_defaut")."</b></td>";
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
    echo "<td>{$col[$i][3]}</td>\n";
    echo "<td bgcolor='".$tab_couleur[$col[$i][5]]."'></td>\n";
    echo "<td>{$col[$i][4]}</td>\n";
    echo "<td><input type=\"checkbox\" name=\"".$col[$i][2]."\" value=\"y\" ";
    $test = grr_sql_query1("select count(id_type) from agt_j_type_area where id_area = '".$service_id."' and id_type = '".$row[0]."'");
    if ($test < 1) echo " checked";
    echo " /></td>";
    echo "<td><input type=\"radio\" name=\"id_type_par_defaut\" value=\"".$col[$i][2]."\" ";
    $test = grr_sql_query1("select id_type_par_defaut from agt_service where id = '".$service_id."'");
    if ($test == $col[$i][2]) echo " checked";
    echo " /></td>";

    // Fin de la ligne courante
    echo "</tr>";
    }

   echo "<tr><td>&nbsp;</td>\n";
   echo "<td>&nbsp;</td>\n";
   echo "<td>&nbsp;</td>\n";
   echo "<td>&nbsp;</td>\n";
   echo "<td>&nbsp;</td>\n";
   echo "<td><input type=\"radio\" name=\"id_type_par_defaut\" value=\"-1\" ";
       $test = grr_sql_query1("select id_type_par_defaut from agt_service where id = '".$service_id."'");
       if ($test <= 0) echo " checked";
   echo " />".$vocab["nobody"]."    </td>";
   echo "</tr>";
}
echo "</table>";
echo "</tr></table></center>";
echo "<input type=\"hidden\" name=\"service_id\" value=\"".$service_id."\" />";
echo "<center><input type=\"submit\" name=\"valider\" value=\"".get_vocab("save")."\" />\n";
echo "&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"change_done\" value=\"".get_vocab("back")."\" />";
echo "</center>";
echo "</form>\n";


?>
</body>
</html>
