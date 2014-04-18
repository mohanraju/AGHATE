<?php
#########################################################################
#                           admin_view_connexions.php                   #
#                                                                       #
#                       Interface de gestion des connexions             #
#                                                                       #
#                  Dernière modification : 10/07/2006                   #
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
$grr_script_name = "admin_view_connexions.php";


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

// Date to delete logs to
if (isset($_POST['cleanDay']) && isset($_POST['cleanMonth']) && isset($_POST['cleanYear'])) {
    $sql = "delete from agt_log where START < '" . $_POST['cleanYear'] . "-" . $_POST['cleanMonth'] . "-" . $_POST['cleanDay'] . "' and END < now()";
    $res = grr_sql_query($sql);
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";

?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php
echo "<h2>".get_vocab('admin_view_connexions.php').grr_help("aide_grr_suivi_connexions")."</h2>";
echo "<h3>".get_vocab("users_connected")."</h3>";
?>

<div title="User connected">
<ul>
<?php
// compte le nombre d'enregistrement dans la table
$sql = "select u.login, concat(u.prenom, ' ', u.nom) utilisa, u.email from agt_log l, agt_utilisateurs u where (l.LOGIN = u.login and l.END > now())";

$res = grr_sql_query($sql);
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    if (getSettingValue("ldap_statut") != '')
        echo("<li>" . $row[1]. " | <a href=\"mailto:" . $row[2] . "\">".get_vocab("sen_a_mail")."</a> |") ;
    else
        echo("<li>" . $row[1]. " | <a href=\"mailto:" . $row[2] . "\">".get_vocab("sen_a_mail")."</a> | <a href=\"admin_change_pwd.php?user_login=" . $row[0] . "\">".get_vocab("deconnect_changing_pwd")."</a></li>");
    }
}
?>
</ul>
</div>

<hr style="margin-top: 32px; margin-bottom: 24px;"/>

<?php
if (!isset($_POST['histYear'])) {
    $_POST['histYear'] = strftime("%Y");
}
if (!isset($_POST['histMonth'])) {
    $_POST['histMonth'] = strftime("%m");
}
if (!isset($_POST['histDay'])) {
    $_POST['histDay'] = strftime("%d");
}
?>

<h3><?php echo get_vocab("log").$_POST['histDay']."/".$_POST['histMonth']."/".$_POST['histYear']; ?></h3>
<div title="log" style="width: 100%;">

<p><?php echo get_vocab("msg_explain_log"); ?></p>

<table class="col" style="width: 90%; margin-left: auto; margin-right: auto; margin-bottom: 32px;" cellpadding="5" cellspacing="0">
    <tr>
        <th class="col"><?php echo get_vocab("login_name"); ?></th>
        <th class="col"><?php echo get_vocab("begining_of_session"); ?></th>
        <th class="col"><?php echo get_vocab("end_of_session"); ?></th>
        <th class="col"><?php echo get_vocab("ip_adress"); ?></th>
        <th class="col"><?php echo get_vocab("navigator"); ?></th>
        <th class="col"><?php echo get_vocab("referer"); ?></th>
    </tr>
<?php

$sql = "select u.login, concat(prenom, ' ', nom) utili, l.START, l.SESSION_ID, l.REMOTE_ADDR, l.USER_AGENT, l.REFERER, l.AUTOCLOSE, l.END, u.email from agt_log l, agt_utilisateurs u where l.LOGIN = u.login and l.START > '" . $_POST['histYear'] . "-" . $_POST['histMonth'] . "-" . $_POST['histDay'] . "' order by START desc";

// $row[0] : log.LOGIN
// $row[1] : USER
// $row[2] : START
// $row[3] : SESSION_ID
// $row[4] : REMOTE_ADDR
// $row[5] : USER_AGENT
// $row[6] : REFERER
// $row[7] : AUTOCLOSE
// $row[8] : END
// $row[9] : EMAIL

$day_now   = date("d");
$month_now = date("m");
$year_now  = date("Y");
$hour_now  = date("H");
$minute_now = date("i");
$now = mktime($hour_now, $minute_now, 0, $month_now, $day_now, $year_now);

$res = grr_sql_query($sql);
if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $annee = substr($row[8],0,4);
        $mois =  substr($row[8],5,2);
        $jour =  substr($row[8],8,2);
        $heures = substr($row[8],11,2);
        $minutes = substr($row[8],14,2);
        $secondes = substr($row[8],17,2);
        $end_time = mktime($heures, $minutes, $secondes, $mois, $jour, $annee);

        $temp1 = '';
        $temp2 = '';
        if ($end_time > $now) {
            $temp1 = "<font color=green>";
            $temp2 = "</font>";
        }

        echo("<tr>\n");
        echo "<td class=\"col\">".$temp1."<a href=\"mailto:" .$row[9]. "\">".$row[1] . "</a>".$temp2."</td>\n";
        echo "<td class=\"col\">".$temp1.$row[2].$temp2."</td>";
        if ($end_time > $now) {
            echo "<td class=\"col\" style=\"color: green;\">" .$row[8]. "</td>";
        } else if ($row[7]) {
            echo "<td class=\"col\" style=\"color: red;\">" .$row[8]. "</td>";
        } else {
            echo "<td class=\"col\">" .$row[8]. "</td>";
        }
        echo "<td class=\"col\">".$temp1.$row[4].$temp2. "</td>";
        echo "<td class=\"col\">".$temp1. $row[5] .$temp2. "</td>";
        echo "<td class=\"col\">".$temp1. $row[6] .$temp2. "</td>";
    }
}

?>
</table>

<form action="admin_view_connexions.php" method="post">
    <fieldset style="padding-top: 16px; padding-bottom: 16px; width: 40%; margin-right: auto; margin-left: auto; text-align: center;">
        <legend align="left" style="font-variant: small-caps;"><?php echo get_vocab("start_history"); ?></legend>
                <table style="border: 0; width: 5%; margin: auto;" cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="text-align: center; width: 24%; font-variant: small-caps;">JJ</td>
                        <td style="text-align: center; width: 1%;">/</td>
                        <td style="text-align: center; width: 24%; font-variant: small-caps;">MM</td>
                        <td style="text-align: center; width: 1%;">/</td>
                        <td style="text-align: center; width: 50%; font-variant: small-caps;">AAAA</td>
                    </tr>
                    <tr>
                        <td><input type="text" name="histDay" size="2" value="<?php echo($_POST['histDay']); ?>" style="text-align: center;"/></td>
                        <td>/</td>
                        <td><input type="text" name="histMonth" size="2" value="<?php echo($_POST['histMonth']); ?>" style="text-align: center;"/></td>
                        <td>/</td>
                        <td><input type="text" name="histYear" size="4" value="<?php echo($_POST['histYear']); ?>" style="text-align: center;"/></td>
                    </tr>
                </table>
        <input type="submit" value="<?php echo get_vocab("OK"); ?>" style="font-variant: small-caps;"/>
    </fieldset>
</form>

</div>

<hr style="margin-top: 32px; margin-bottom: 24px;"/>
<h3><?php echo get_vocab("cleaning_log"); ?></h3>

<?php
$sql = "select START from agt_log order by END";
$res = grr_sql_query($sql);
$logs_number = grr_sql_count($res);
$row = grr_sql_row($res, 0);
$annee = substr($row[0],0,4);
$mois =  substr($row[0],5,2);
$jour =  substr($row[0],8,2);


echo "<p>".get_vocab("logs_number")."<b>".$logs_number."</b><br />";
echo get_vocab("older_date_log")."<b>".$jour."/".$mois."/".$annee."</b></p>";

if (!isset($_POST['cleanYear'])) {
    $_POST['cleanYear'] = strftime("%Y");
}
if (!isset($_POST['cleanMonth'])) {
    $_POST['cleanMonth'] = strftime("%m");
}
if (!isset($_POST['cleanDay'])) {
    $_POST['cleanDay'] = strftime("%d");
}
?>

<div title="Nettoyage du journal" style="width: 100%;">

<p><?php echo get_vocab("erase_log")?></p>

<form action="admin_view_connexions.php" method="post">
    <fieldset style="padding-top: 16px; padding-bottom: 16px; width: 40%; margin-right: auto; margin-left: auto; text-align: center;">
        <legend align="left" style="font-variant: small-caps;"><?php echo get_vocab("delete_up_to"); ?></legend>
                <table style="border: 0; width: 5%; margin: auto;" cellpadding="5" cellspacing="0">
                    <tr>
                        <td style="text-align: center; width: 24%; font-variant: small-caps;">JJ</td>
                        <td style="text-align: center; width: 1%;">/</td>
                        <td style="text-align: center; width: 24%; font-variant: small-caps;">MM</td>
                        <td style="text-align: center; width: 1%;">/</td>
                        <td style="text-align: center; width: 50%; font-variant: small-caps;">AAAA</td>
                    </tr>
                    <tr>
                        <td><input type="text" name="cleanDay" size="2" value="<?php echo($_POST['cleanDay']); ?>" style="text-align: center;"/></td>
                        <td>/</td>
                        <td><input type="text" name="cleanMonth" size="2" value="<?php echo($_POST['cleanMonth']); ?>" style="text-align: center;"/></td>
                        <td>/</td>
                        <td><input type="text" name="cleanYear" size="4" value="<?php echo($_POST['cleanYear']); ?>" style="text-align: center;"/></td>
                    </tr>
                </table>
        <input type="submit" value="<?php echo get_vocab("OK"); ?>" style="font-variant: small-caps;" />
    </fieldset>
</form>

</div>
<?php
// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";
?>
</body>
</html>
