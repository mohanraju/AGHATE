<?php
#########################################################################
#                         admin_accueil                                 #
#                                                                       #
#                       Interface d'accueil de l'administration         #
#                     des domaines et des ressources                    #
#                                                                       #
#                  Dernière modification : 21/05/2005                   #
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
$grr_script_name = "admin_accueil.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if ((authGetUserLevel(getUserName(),-1,'area') < 1) and  (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");

include "admin_col_gauche.php";
?>
<td>
<table><tr><td>&nbsp;</td>
<td align="center" ><img src="./commun/images/hall1.jpg" alt="GRR !"  border="0" /><br />
  <br /><p style="font-size:20pt"><?php echo get_vocab("admin"); ?> </p>
  <p style="font-size:40pt"><i>Gestion des Salles!</i></p></td></tr></table>
</td>
</tr>
</table>
</body>
</html>
