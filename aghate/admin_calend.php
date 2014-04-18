<?php
#########################################################################
#                    admin_calendar.php                                 												   #
#                                                                      														   #
#            interface permettant la la réservation en bloc             											   #
#                  de journées entières                                 												   #
#               Dernière modification : 06/12/2007                     											   #
#                                                                       													   #
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
include "./commun/include/mrbs_sql.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";

$grr_script_name = "admin_calend.php";

$mysql= new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";



$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

$day   = date("d");
$month = date("m");
$year  = date("Y");


function cal($month, $year)
{
    global $weekstarts;
    if (!isset($weekstarts)) $weekstarts = 0;
    $s = "";
    $daysInMonth = getDaysInMonth($month, $year);
    $date = mktime(12, 0, 0, $month, 1, $year);
    $first = (strftime("%w",$date) + 7 - $weekstarts) % 7;
    $monthName = utf8_strftime("%B",$date);
    $s .= "<table class=\"calendar2\" border=1 cellspacing=3>\n";
    $s .= "<tr>\n";
    $s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader2\" colspan=\"7\">$monthName&nbsp;$year</td>\n";
    $s .= "</tr>\n";
    $s .= "<tr>\n";
    $s .= getFirstDays();
    $s .= "</tr>\n";
    $d = 1 - $first;
    while ($d <= $daysInMonth)
    {
        $s .= "<tr>\n";
        for ($i = 0; $i < 7; $i++)
        {
            $basetime = mktime(12,0,0,6,11+$weekstarts,2000);
            $show = $basetime + ($i * 24 * 60 * 60);
            $nameday = utf8_strftime('%A',$show);

            $s .= "<td class=\"calendar2\" align=\"center\" valign=\"top\">";
            if ($d > 0 && $d <= $daysInMonth)
            {
                $s .= $d;
                $temp = mktime(0,0,0,$month,$d,$year);
                // On teste si le jour est férié :
                $test = grr_sql_query1("select DAY from grr_calendar where DAY = '".$temp."'");
                if ($test == '-1')
                    $s .= "<br /><INPUT TYPE=\"checkbox\" NAME=\"$temp\" VALUE=\"$nameday\" />";
                else
                    $s .= "<br /><INPUT TYPE=\"checkbox\" name=\"$temp\" value=\"$nameday\"  disabled />";
            } else {
                $s .= "&nbsp;";
            }
            $s .= "</td>\n";
            $d++;
        }
        $s .= "</tr>\n";
    }
    $s .= "</table>\n";
    return $s;
}


if(authGetUserLevel(getUserName(),-1) < 5)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

// Initialisation
$etape = isset($_POST["etape"]) ? $_POST["etape"] : NULL;
$areas = isset($_POST["areas"]) ? $_POST["areas"] : NULL;
$rooms = isset($_POST["rooms"]) ? $_POST["rooms"] : NULL;
$name = isset($_POST["name"]) ? $_POST["name"] : NULL;
$beneficiaire = isset($_POST["beneficiaire"]) ? $_POST["beneficiaire"] : NULL;
$description = isset($_POST["description"]) ? $_POST["description"] : NULL;
$type_ = isset($_POST["type_"]) ? $_POST["type_"] : NULL;
$type_resa = isset($_POST["type_resa"]) ? $_POST["type_resa"] : NULL;
$hour = isset($_POST["hour"]) ? $_POST["hour"] : NULL;
settype($hour,"integer");
$end_hour = isset($_POST["end_hour"]) ? $_POST["end_hour"] : NULL;
settype($end_hour,"integer");
$minute = isset($_POST["minute"]) ? $_POST["minute"] : NULL;
settype($minute,"integer");
$end_minute = isset($_POST["end_minute"]) ? $_POST["end_minute"] : NULL;
settype($end_minute,"integer");
$period = isset($_POST["period"]) ? $_POST["period"] : NULL;
$end_period = isset($_POST["end_period"]) ? $_POST["end_period"] : NULL;
$all_day = isset($_POST["all_day"]) ? $_POST["all_day"] : NULL;

# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";
?>
<script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
<?php

echo "<h2>".get_vocab('admin_calendar_title.php')."</h2>";


if (isset($_POST['record']) and  ($_POST['record'] == 'yes')) {
    $etape = 4;
    $result = 0;
    $end_bookings = getSettingValue("end_bookings");
    // On reconstitue le tableau des ressources
    $sql = "select id from agt_room";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        $temp = "id_room_".$row[0];
        if (isset($_POST[$temp])) {
            // La ressource est selectionnée
//            $rooms[] = $id;
            // On récupère les données du domaine
            $service_id = grr_sql_query1("SELECT service_id FROM agt_room WHERE id = '".$row[0]."'");
            get_planning_area_values($service_id);
            $n = getSettingValue("begin_bookings");
            $month = strftime("%m", getSettingValue("begin_bookings"));
            $year = strftime("%Y", getSettingValue("begin_bookings"));
            $day = 1;
            while ($n <= $end_bookings) {
                $daysInMonth = getDaysInMonth($month, $year);
                $day = 1;
                while ($day <= $daysInMonth) {
                    $n = mktime(0,0,0,$month,$day,$year);
                    if (isset($_POST[$n])) {
                    $erreur = 'n';
                    // Le jour a été selectionné dans le calendrier
                        if (!isset($all_day)) {
                        // Cas des réservation par créneaux pré-définis
                          if($enable_periods=='y') {
                              $resolution = 60;
                              $hour = 12;
                              $end_hour = 12;
                              if (isset($period))
                                  $minute = $period;
                              else
                                  $minute = 0;
                              if (isset($end_period))
                                  $end_minute = $end_period+1;
                              else
                                  $end_minute = $eveningends_minutes+1;
                            }
                            $starttime = mktime($hour, $minute, 0, $month, $day, $year);
                            $endtime   = mktime($end_hour, $end_minute, 0, $month, $day, $year);
                            if ($endtime <= $starttime) $erreur = 'y';
                        } else {
                            $starttime = mktime($morningstarts, 0, 0, $month, $day  , $year);
                            $endtime   = mktime($eveningends, $eveningends_minutes , $resolution, $month, $day, $year);
                        }
                        if ($erreur != 'y') {
                          // On efface toutes les résa en conflit
                          $result += $Aghate->DelEntryInConflict($row[0], $starttime, $endtime, 0, 0, 1);
                          // S'il s'agit d'une action de réservation, on réserve !
                          if ($type_resa == "resa") {
                              // Par sécurité, on teste quand même s'il reste des conflits
                              $err = $Aghate->CheckRoomDispo($row[0], $starttime, $endtime);
                              if (!$err) {
                                  $Aghate->mrbsCreateSingleEntry($starttime, $endtime,$row[0], getUserName(),$name, $type_, $description, -1,array(),0,0);
                              }
                          }
                        }
                    }
                    $day++;
                }
                $month++;
                if ($month == 13) {
                    $year++;
                    $month = 1;
                }
            }
        }
    }
}

if ($etape==4) {
    if ($result == '') $result = 0;
    if ($type_resa == "resa") {
        echo "<center><H3>".get_vocab("reservation_en_bloc")."</H3></center>";
        echo "<H3>".get_vocab("reservation_en_bloc_result")."</H3>";
        if ($result != 0) echo "<p>".get_vocab("reservation_en_bloc_result2")."<b>".$result."</b></p>";
    } else {
        echo "<center><H3><font color=\"#FF0000\">".get_vocab("suppression_en_bloc")."</font></H3></center>";
        echo "<h3>".get_vocab("suppression_en_bloc_result")."<b>".$result."</b></h3>";
    }

}
if ($etape==3) {
    // Etape N° 3
    echo "<center><H3>".get_vocab("etape_n")."3/3</H3></center>";
    if ($type_resa == "resa")
        echo "<center><H3>".get_vocab("reservation_en_bloc")."</H3></center>";
    else
        echo "<center><H3><font color=\"#FF0000\">".get_vocab("suppression_en_bloc")."</font></H3></center>";

    if (!isset($rooms)) {
        echo "<h3>".get_vocab("noarea")."</h3>";
        // fin de l'affichage de la colonne de droite
        echo "</td></tr></table>";
        echo "</body></html>";
        die();
    }

    echo "<form action=\"admin_calend.php\" method=\"post\" name=\"formulaire\">\n";

    $test_enable_periods_y = 0;
    $test_enable_periods_n = 0;

    foreach ( $rooms as $room_id ) {
        $temp = "id_room_".$room_id;
        echo "<input type=\"hidden\" name=\"".$temp."\" value=\"yes\" />\n";
        $service_id = grr_sql_query1("SELECT service_id FROM agt_room WHERE id = '".$room_id."'");
        $test_enable_periods_y += grr_sql_query1("select count(enable_periods) FROM agt_service WHERE (id = '".$service_id."' and enable_periods='y')");
        $test_enable_periods_n += grr_sql_query1("select count(enable_periods) FROM agt_service WHERE (id = '".$service_id."' and enable_periods='n')");

    }
    // On teste si tous les domaines selectionnés sont du même type d'affichage à savoir :
    // soit des créneaux de réservation basés sur le temps,
    // soit des créneaux de réservation basés sur des intitulés pré-définis.
    if ($test_enable_periods_y == 0)
        $all_enable_periods = 'n';
    else if ($test_enable_periods_n == 0)
        $all_enable_periods = 'y';
    else
        $all_enable_periods = 'incompatible';

    if ($all_enable_periods != "incompatible") {
        // On propose une heure de début et une heure de fin de réservation
        $texte_debut_fin_reservation = "";
        // On prend comme domaine de référence le dernier domaine de la boucle  foreach ( $rooms as $room_id ) {
        // C'est pas parfait mais bon !
        get_planning_area_values($service_id);

        if ($all_enable_periods=='y') {
            // Créneaux basés sur les intitulés pré-définis
            // Heure ou créneau de début de réservation
            $texte_debut_fin_reservation .= "<b>".get_vocab("date").get_vocab("deux_points")."</b>";
            $texte_debut_fin_reservation .= "<br />".get_vocab("period")."\n";
            $texte_debut_fin_reservation .= "<SELECT NAME=\"period\">";
            foreach ($periods_name as $p_num => $p_val)
                {
                $texte_debut_fin_reservation .= "<OPTION VALUE=$p_num>$p_val";
                }
            $texte_debut_fin_reservation .= "</SELECT>\n";
            $texte_debut_fin_reservation .= "<br /><br /><b>".get_vocab("fin_reservation").get_vocab("deux_points")."</b>";
            $texte_debut_fin_reservation .= "<br />".get_vocab("period")."\n";
            $texte_debut_fin_reservation .= "<SELECT NAME=\"end_period\">";
            foreach ($periods_name as $p_num => $p_val)
                {
                $texte_debut_fin_reservation .= "<OPTION VALUE=$p_num>$p_val";
                }
            $texte_debut_fin_reservation .= "</SELECT>\n";

        } else {
            // Créneaux basés sur le temps
            // Heure ou créneau de début de réservation
            $texte_debut_fin_reservation .= "<b>".get_vocab("date").get_vocab("deux_points")."</b>";
            $texte_debut_fin_reservation .= "<br />".get_vocab("time")."
            <INPUT NAME=\"hour\" SIZE=2 VALUE=\"".$morningstarts."\" MAXLENGTH=2 />
            <INPUT NAME=\"minute\" SIZE=2 VALUE=\"0\" MAXLENGTH=2 />";
            $texte_debut_fin_reservation .= "<br /><br /><b>".get_vocab("fin_reservation").get_vocab("deux_points")."</b>";
            $texte_debut_fin_reservation .= "<br />".get_vocab("time")."
            <INPUT NAME=\"end_hour\" SIZE=2 VALUE=\"".$morningstarts."\" MAXLENGTH=2 />
            <INPUT NAME=\"end_minute\" SIZE=2 VALUE=\"0\" MAXLENGTH=2 />";

        }

      }  else {
          $texte_debut_fin_reservation = get_vocab("domaines_de_type_incompatibles");
          echo "<input type=\"hidden\" name=\"all_day\" value=\"y\" />";
      }

    echo "<table cellpadding=\"3\" border=\"0\">\n";
    $basetime = mktime(12,0,0,6,11+$weekstarts,2000);
    for ($i = 0; $i < 7; $i++)
    {
        $show = $basetime + ($i * 24 * 60 * 60);
        $lday = utf8_strftime('%A',$show);
        echo "<tr>\n";
        echo "<td width=\"25%\"><span class='small'><a href='admin_calend.php' onclick=\"setCheckboxesGrr('formulaire', true, '$lday' ); return false;\">".get_vocab("check_all_the").$lday."s</a></span></td>\n";
        echo "<td width=\"25%\"><span class='small'><a href='admin_calend.php' onclick=\"setCheckboxesGrr('formulaire', false, '$lday' ); return false;\">".get_vocab("uncheck_all_the").$lday."s</a></span></td>\n";
        if ($i == 0) echo "<td rowspan=\"8\">&nbsp;&nbsp;</td><td rowspan=\"8\">$texte_debut_fin_reservation</td>\n";
        echo "</tr>\n";
    }
    echo "<tr>\n<td><span class='small'><a href='admin_calend.php' onclick=\"setCheckboxesGrr('formulaire', false, 'all'); return false;\">".get_vocab("uncheck_all_")."</a></span></td>\n";
    echo "<td>&nbsp;</td></tr>\n";
    echo "</table>\n";
    echo "<table cellspacing=20>\n";

    $n = getSettingValue("begin_bookings");
    $end_bookings = getSettingValue("end_bookings");

    $debligne = 1;
    $month = strftime("%m", getSettingValue("begin_bookings"));
    $year = strftime("%Y", getSettingValue("begin_bookings"));

    while ($n <= $end_bookings) {
        if ($debligne == 1) {
            echo "<tr>\n";
            $inc = 0;
            $debligne = 0;
        }
        $inc++;
        echo "<td>\n";
        echo cal($month, $year);
        echo "</td>";
        if ($inc == 3) {
            echo "</tr>";
            $debligne = 1;
        }
        $month++;
        if ($month == 13) {
            $year++;
            $month = 1;
        }
        $n = mktime(0,0,0,$month,1,$year);
    }
    echo "</table>";
    echo "<center><div id=\"fixe\"><input type=\"submit\" name=\"".get_vocab('save')."\" /></div></center>\n";
    echo "<input type=\"hidden\" name=\"record\" value=\"yes\" />\n";
    echo "<input type=\"hidden\" name=\"etape\" value=\"4\" />\n";
    echo "<input type=\"hidden\" name=\"name\" value=\"".$name."\" />\n";
    echo "<input type=\"hidden\" name=\"description\" value=\"".$description."\" />\n";
    echo "<input type=\"hidden\" name=\"beneficiaire\" value=\"".$beneficiaire."\" />\n";
    echo "<input type=\"hidden\" name=\"type_\" value=\"".$type_."\" />\n";
    echo "<INPUT TYPE=\"hidden\" name=\"type_resa\" value=\"".$type_resa."\" />\n";
    echo "</form>";
} else if ($etape==2) {
    // Etape 2
    ?>
    <SCRIPT  type="text/javascript"  LANGUAGE="JavaScript">
    <?php
    if ($type_resa == "resa") {
    ?>
    function validate_and_submit ()
    {
    if(document.forms["main"].name.value == "")
    {
    alert ( "<?php echo get_vocab('you_have_not_entered') . '\n' . get_vocab('brief_description') ?>");
    return false;
    }
    if  (document.forms["main"].elements[3].value =='')
    {
    alert("<?php echo get_vocab("choose_a_room"); ?>");
    return false;
    }
    if  (document.forms["main"].type_.value=='0')
    {
    alert("<?php echo get_vocab("choose_a_type"); ?>");
    return false;
    }
    document.forms["main"].submit();
    return true;
    }
    <?php
    } else {
    ?>

    function validate_and_submit ()
    {
    if  (document.forms["main"].elements[1].value =='')
    {
    alert("<?php echo get_vocab("choose_a_room"); ?>");
    return false;
    }
    document.forms["main"].submit();
    return true;
    }
    <?php
    }
    ?>
    </SCRIPT>
    <?php

    echo "<center><H3>".get_vocab("etape_n")."2/3</H3></center>";
    if ($type_resa == "resa")
        echo "<center><H3>".get_vocab("reservation_en_bloc")."</H3></center>";
    else
        echo "<center><H3><font color=\"#FF0000\">".get_vocab("suppression_en_bloc")."</font></H3></center>";

    if (!isset($areas)) {
        echo "<h3>".get_vocab("noarea")."</h3>";
        // fin de l'affichage de la colonne de droite
        echo "</td></tr></table>";
        echo "</body></html>";
        die();
    }

    // Choix des ressources
    echo "<FORM action=\"admin_calend.php\" method=\"post\" name=\"main\">";
    echo "<TABLE BORDER=0>\n";
    if ($type_resa == "resa") {
      echo "<TR><TD CLASS=CR><B>".ucfirst(trim(get_vocab("reservation au nom de"))).get_vocab("deux_points")."</B></TD>\n";
      echo "<TD class=\"CL\"><select size=1 name=beneficiaire>\n";
      $sql = "SELECT DISTINCT login, nom, prenom FROM agt_utilisateurs WHERE  (etat!='inactif' and statut!='visiteur' ) order by nom, prenom";
      $res = grr_sql_query($sql);
      if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        echo "<option value=$row[0] ";
        if (getUserName() == $row[0])  echo " selected";
        echo ">$row[1]  $row[2] </option>";
     }
     echo "</select></TD></TR>\n";

      echo "<TR><TD CLASS=CR><B>".get_vocab("namebooker").get_vocab("deux_points")."</B></TD>\n";
      echo "<TD CLASS=CL><INPUT NAME=\"name\" SIZE=\"40\" VALUE=\"\" /></TD></TR>";
      echo "<TR><TD CLASS=TR><B>".get_vocab("fulldescription")."</B></TD>\n";
      echo "<TD CLASS=TL><TEXTAREA NAME=\"description\" ROWS=\"8\" COLS=\"40\" ></TEXTAREA></TD></TR>";
    }
    echo "<tr><td class=CR><b>".get_vocab("rooms").get_vocab("deux_points")."</b></td>\n";
    echo "<td class=\"CL\" valign=\"top\"><table border=0><tr><td>";
    echo "<select name=\"rooms[]\" multiple>";
    foreach ( $areas as $service_id ) {
        # then select the rooms in that area
        $sql = "select id, room_name from agt_room where service_id=$service_id order by order_display,room_name";
        $res = grr_sql_query($sql);
        if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
            echo "<option value=\"".$row[0]."\">".$row[1];
        }
    }
    echo "</select></td><td>".get_vocab("ctrl_click")."</td></tr></table>\n";
    echo "</td></tr>\n";
    if ($type_resa == "resa") {
      echo "<TR><TD CLASS=CR><B>".get_vocab("type").get_vocab("deux_points")."</B></TD>\n";
      echo "<TD CLASS=CL><SELECT NAME=\"type_\">\n";
      echo "<OPTION VALUE='0'>".get_vocab("choose")."\n";
      $sql = "SELECT t.type_name, t.type_letter FROM agt_type_area t
      LEFT JOIN agt_j_type_area j on j.id_type=t.id
      WHERE (j.id_area  IS NULL or (";
      $ind = 0;
      foreach ( $areas as $service_id ) {
          if ($ind != 0) $sql .= " and ";
          $sql .= "j.id_area != '".$service_id."'";
          $ind = 1;
      }
      $sql .= "))
      ORDER BY order_display";
      $res = grr_sql_query($sql);
      if ($res) {
      for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
        echo "<OPTION VALUE=\"".$row[1]."\" ";
        if ($type_ == $row[1]) echo " SELECTED";
        echo " >".$row[0]."</option>\n";
        }
      }
      echo "</SELECT></TD></TR>";
    }
    echo "</table>\n";
    echo "<INPUT TYPE=\"hidden\" name=\"etape\" value=\"3\" />\n";
    echo "<INPUT TYPE=\"hidden\" name=\"type_resa\" value=\"".$type_resa."\" />\n";
    ?>
    <SCRIPT  type="text/javascript" LANGUAGE="JavaScript">
    document.writeln ( '<center><INPUT TYPE="button" VALUE="<?php echo get_vocab("next")?>" ONCLICK="validate_and_submit()" /><\/center>' );
    </SCRIPT>
    <NOSCRIPT>
    <INPUT TYPE="submit" VALUE="<?php echo get_vocab("next")?>" />
    </NOSCRIPT>
    <?php
    echo "</FORM>";

} else if (!$etape) {
    // Etape 1 :
    echo get_vocab("admin_calendar_explain_1.php");
    echo "<center><H3>".get_vocab("etape_n")."1/3</H3></center>";
    // Choix des domaines
    echo "<FORM action=\"admin_calend.php\" method=\"post\">\n";
    echo "<table border=\"1\"><tr><td>\n";
    echo "<p><b>".get_vocab("choix_domaines")."</b></p>";
    echo "<select name=\"areas[]\" multiple>\n";
    $sql = "select id, service_name from agt_service order by order_display, service_name";
    $res = grr_sql_query($sql);
    if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        echo "<option value=\"".$row[0]."\">".$row[1]."</option>\n";
    }
    echo "</select><br />".get_vocab("ctrl_click");
    echo "</td><td>";
    echo "<p><b>".get_vocab("choix_action")."</b></p>";
    echo "<table><tr>";
    echo "<td><input type=\"radio\" name=\"type_resa\" value=\"resa\" checked /></td>\n";
    echo "<td>".get_vocab("reservation_en_bloc")."</td>\n";
    echo "</tr><tr>\n";
    echo "<td><input type=\"radio\" name=\"type_resa\" value=\"suppression\" /></td>\n";
    echo "<td>".get_vocab("suppression_en_bloc")."</td>\n";
    echo "</tr></table>\n";
    echo "</td></tr></table>\n";
    echo "<INPUT TYPE=\"hidden\" name=\"etape\" value=\"2\" />\n";
    echo "<br /><center><INPUT type=\"submit\" name=\"Continuer\" value=\"".get_vocab("next")."\" /></center>\n";
    echo "</FORM>\n";
}

// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";

?>


</body>
</html>
