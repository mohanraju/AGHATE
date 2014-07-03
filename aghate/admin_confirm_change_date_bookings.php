<?php
#########################################################################
#                admin_confirm_change_date_bookings.php                 #
#                                                                       #
#            interface de confirmation des changements de date          #
#            de début et de fin de réservation                          #
#               Dernière modification : 01/12/2005                      #
#                                                                       #
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
$grr_script_name = "admin_confirm_change_date_bookings.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
unset($display);
$display = isset($_GET["display"]) ? $_GET["display"] : NULL;
if(authGetUserLevel(getUserName(),-1) < 5)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
if (isset($_GET['valid']) and ($_GET['valid'] == "yes")) {
    if (!saveSetting("begin_bookings", $_GET['begin_bookings'])) {
        echo "Erreur lors de l'enregistrement de begin_bookings !<br />";
    } else {
        $del = grr_sql_query("DELETE FROM agt_loc WHERE (end_time < ".getSettingValue('begin_bookings').")");
        $del = grr_sql_query("DELETE FROM agt_repeat WHERE end_date < ".getSettingValue("begin_bookings"));
        $del = grr_sql_query("DELETE FROM agt_loc_moderate WHERE (end_time < ".getSettingValue('begin_bookings').")");
        $del = grr_sql_query("DELETE FROM agt_loc_moderate WHERE end_date < ".getSettingValue("begin_bookings"));
    }

    if (!saveSetting("end_bookings", $_GET['end_bookings'])) {
        echo "Erreur lors de l'enregistrement de end_bookings !<br />";
    } else {
        $del = grr_sql_query("DELETE FROM agt_loc WHERE start_time > ".getSettingValue("end_bookings"));
        $del = grr_sql_query("DELETE FROM agt_repeat WHERE start_time > ".getSettingValue("end_bookings"));
    }

    header("Location: ./admin_config.php");

} else if (isset($_GET['valid']) and ($_GET['valid'] == "no")) {
    header("Location: ./admin_config.php");
}
# print the page header
print_header("","","","",$type="with_session", $page="admin");
echo "<h2>".get_vocab('admin_confirm_change_date_bookings.php')."</h2>";
echo "<p>".get_vocab("msg_del_bookings")."</p>";
?>
<form action="admin_confirm_change_date_bookings.php" method='get'>

<input type=submit value=<?php echo get_vocab("save");?> />
<input type=hidden name=valid value="yes" />
<input type=hidden name=begin_bookings value=" <?php echo $_GET['begin_bookings']; ?>" />
<input type=hidden name=end_bookings value=" <?php echo $_GET['end_bookings']; ?>" />

</form>

<form action="admin_confirm_change_date_bookings.php" method='get'>
<input type=submit value=<?php echo get_vocab("cancel");?> />
<input type="hidden" name="valid" value="no" />
</form>
</body>
</html>
