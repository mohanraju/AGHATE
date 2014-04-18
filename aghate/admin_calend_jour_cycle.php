<?php
#########################################################################
#                            admin_calend_jour_cycle.php                #
#                                                                       #
#        Interface permettant à l'administrateur                        #
#        la configuration de certains paramètres généraux               #
#                Dernière modification : 10/12/2007                     #
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
$grr_script_name = "admin_calend_jour_cycle.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

$_SESSION['chemin_retour'] = "admin_calend_jour_cycle.php";

if(authGetUserLevel(getUserName(),-1) < 5)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

$page_calend = isset($_GET["page_calend"]) ? $_GET["page_calend"] : '3';

if ($page_calend == 3) include "./admin_config_calend3.php";
else if ($page_calend == 2) include "./admin_config_calend2.php";
else if ($page_calend == 1) include "./admin_config_calend1.php";
else die();

?>
