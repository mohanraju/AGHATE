<?php
#########################################################################
#                           admin_room                                  #
#                                                                       #
#                       Interface d'accueil                             #
#             de Gestion des domaines et ressources                     #
#                                                                       #
#                  Dernière modification : 28/03/2008                   #
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
$grr_script_name = "admin_room.php";


if(authGetUserLevel(getUserName(),-1,'area') < 4)
{
    $back = '';
    if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
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


// If area is set but area name is not known, get the name.

if (isset($_GET['msg']))   {
   $msg = $_GET['msg'];
   affiche_pop_up($msg,"admin");
}

$area = isset($_POST["area"]) ? $_POST["area"] : (isset($_GET["area"]) ? $_GET["area"] : NULL);

if (isset($area))
{
    if (empty($service_name))
    {
        $res = grr_sql_query("select service_name, access from agt_service where id=$area order by service_name");
        if (! $res) fatal_error(0, grr_sql_error());
        if (grr_sql_count($res) == 1)
        {
            $row = grr_sql_row($res, 0);
            $service_name = $row[0];
        }
        grr_sql_free($res);
    } else {
        $service_name = unslashes($service_name);
    }
}
?>

<h2><?php echo get_vocab("admin_room.php"); ?></h2>

<table border="1" width="100%" cellpadding="8" cellspacing="1">
<tr>
<th width="50%"><center><b><?php echo get_vocab('areas') ?></b></center></th>
<th><center><b><?php echo get_vocab('rooms') ?> <?php if(isset($area)) { echo get_vocab('in') . " " .
  htmlspecialchars($service_name); }?></b></center></th>
</tr>
<?php
// Seul l'administrateur a le droit d'ajouter des domaines
if(authGetUserLevel(getUserName(),-1,'area') >= 5) {
    echo "<tr><td><a href=\"admin_edit_room.php?add_area='yes'\">".get_vocab('addarea')."</a></td>";
} else {
    echo "<tr><td>&nbsp;</td>";
}
if(isset($area))
    echo "<td><a href=\"admin_edit_room.php?service_id=$area\">".get_vocab('addroom')."</a></td></tr>";
else
    echo "<td>&nbsp;</td></tr>";
# This cell has the areas
$res = grr_sql_query("select id, service_name, access from agt_service order by service_name");
if (! $res) fatal_error(0, grr_sql_error());

if (grr_sql_count($res) != 0) {
    echo "<tr><td>\n";
    echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\">\n";
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
		//echo $row[1];
        // on affiche que les domaines que l'utilisateur connecté a le droit d'administrer
        if(authGetUserLevel(getUserName(),$row[0],'area') >= 4) {
            echo "<tr>";
            if ($row[2]=='r')
                echo "<td><a href='admin_access_area.php?area=$row[0]' title='".get_vocab('admin_access_area.php')."'><img src=\"./commun/images/restricted_s.png\" alt=\"".get_vocab('admin_access_area.php')."\" title=\"".get_vocab('admin_access_area.php')."\" align=\"middle\" border=\"0\" /></a></td>\n";
            else
                echo "<td>&nbsp;</td>\n";
            if(isset($area) and ($area==$row[0])) {
                echo "<td><span class=\"bground\"><b>&gt;&gt;&gt; ".($row[1])." &lt;&lt;&lt; </b></span>";
            } else {
                echo "<td><a href=\"admin_room.php?area=$row[0]\">"
                . ($row[1]) . "</a> ";
            }
            echo "</td>\n";
            echo "<td><a href=\"admin_edit_room.php?area=$row[0]\"><img src=\"./commun/images/edit_s.png\" alt=\"". get_vocab("edit") ."\" title=\"".get_vocab("edit")."\" align=\"middle\" border=\"0\" /></a></td>\n";
            if(authGetUserLevel(getUserName(),$row[0],'area') >= 5)
                echo "<td><a href=\"admin_room_del.php?type=area&amp;area=$row[0]\"><img src=\"./commun/images/delete_s.png\" alt=\"".get_vocab('delete')."\" title=\"".get_vocab('delete')."\" align=\"middle\" border=\"0\" /></a></td>\n";

            echo "<td><a href=\"admin_type_area.php?service_id=$row[0]\"><img src=\"./commun/images/type.png\" alt=\"".get_vocab('edittype')."\" title=\"".get_vocab('edittype')."\" align=\"middle\" border=\"0\" /></a></td>\n";
            echo "<td><a href='javascript:centrerpopup(\"view_rights_area.php?service_id=$row[0]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("privileges")."\">
           <img src=\"./commun/images/rights.png\" alt=\"".get_vocab("privileges")."\" border=\"0\" align=\"middle\" /></a></td>";

            echo "</tr>\n";
        }
    }
    echo "</table>\n";
    echo "</td><td>\n";

    # This one has the rooms
    if(isset($area)) {
        $res = grr_sql_query("select id, room_name, description, capacity, max_booking, statut_room,room_alias from agt_room where service_id=$area order by order_display, room_name");
        if (! $res) fatal_error(0, grr_sql_error());
        if (grr_sql_count($res) != 0) {
            echo "<table cellpadding=\"3\" cellspacing=\"1\">";
            for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
                $color = '';
                //if ($row[5] == "0") $color =  " bgcolor=\"#BA2828\"";
                echo "<tr><td".$color.">" . htmlspecialchars($row[6]) . "<i> - " . htmlspecialchars($row[2]);
				if ($row[3]>0) echo " ($row[3] max.)";
                echo "</i></td>\n<td><a href=\"admin_edit_room.php?room=$row[0]\"><img src=\"./commun/images/edit_s.png\" alt=\"".get_vocab('edit')."\" title=\"".get_vocab('edit')."\" align=\"middle\" border=\"0\" /></a></td>\n";
                echo "<td><a href=\"admin_room_del.php?type=room&amp;room=$row[0]&amp;service_id=$area\"><img src=\"./commun/images/delete_s.png\" alt=\"".get_vocab('delete')."\" title=\"".get_vocab('delete')."\" align=\"middle\" border=\"0\" /></a></td>";
                echo "<td><A href='javascript:centrerpopup(\"view_rights_room.php?id_room=$row[0]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("privileges")."\">
               <img src=\"./commun/images/rights.png\" alt=\"".get_vocab("privileges")."\" border=\"0\" align=\"middle\" /></a></td>";
                echo "<td><A href='javascript:centrerpopup(\"view_room.php?id_room=$row[0]\",600,480,\"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\">
               <img src=\"./commun/images/details_s.png\" alt=\"d&eacute;tails\" border=\"0\" align=\"middle\" /></a></td>";
                echo "</tr>\n";
            }
            echo "</table>";
        }  else echo get_vocab("no_rooms_for_area");
    } else {
        echo get_vocab('noarea');
    }

    ?>
    </td></tr>
    <?php
}
echo  "</table>\n";
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>\n";
?>
</body>
</html>
