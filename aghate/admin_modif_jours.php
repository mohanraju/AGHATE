<?php
#########################################################################
#                    admin_modif_jours.php                              #
#                                                                       #
#                   Permet la modification d'un Jour cycle              #
#                                                                       #
#                   Dernière modification : 09/12/2007                  #
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
include "./commun/include/misc.inc.php";
if (isset($_GET['newDay'])) {
    $sql = "update agt_calendrier_jours_cycle set Jours =".$_GET['newDay']." WHERE DAY = ".$_GET['date'];
    mysql_query($sql);
    Header("Location: admin_calend_jour_cycle.php?page_calend=2");
}
if(isset($_GET['date'])) {

echo "<FORM method=get action=admin_modif_jours.php?date=".$_GET['date'].">";
echo "<label>".get_vocab("nouveau_jour_cycle");

    $result = mysql_query("SELECT * FROM agt_config WHERE NAME='nombre_jours_Jours/Cycles'");
    $data = mysql_fetch_array($result);
    echo "<SELECT name=newDay>";
    for($i=1;$i<($data['VALUE']+1);$i++){
        echo "<OPTION>".$i."</OPTION>";
    }
    echo "</SELECT";
    echo "</label>";
    echo "<input name=date type=hidden value=".$_GET['date'].">";
    echo "<input type=submit value='Enregistrer'>";
    echo "</FORM>";
}
else {
    echo "Erreur!";
}
    echo "</table>";
echo "<tr><td>";

// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";

?>
