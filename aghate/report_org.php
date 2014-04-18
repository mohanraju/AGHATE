<?php
#########################################################################
#                            report.php                                 #
#                                                                       #
#            interface afficheant un rapport des réservations           #
#               Dernière modification : 10/07/2006                      #
#                                                                       #
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
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
$grr_script_name = "view_rights_room.php";
    #Paramètres de connection
require_once("./commun/include/settings.inc.php");
    #Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");

    #Fonction relative à la session
require_once("./commun/include/session.inc.php");
    #Si il n'y a pas de session crée, on déconnecte l'utilisateur.
// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};

// Paramètres langage
include "./commun/include/language.inc.php";

// On affiche le lien "format imprimable" en bas de la page
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

    #Récupération des informations relatives au serveur.
$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
    #Renseigne les droits de l'utilisateur, si les droits sont insufisants, l'utilisateur est avertit.
if (!verif_access_search(getUserName()))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

    #Champs de création du rapport.
$From_day = isset($_GET["From_day"]) ? $_GET["From_day"] : NULL;
$From_month = isset($_GET["From_month"]) ? $_GET["From_month"] : NULL;
$From_year = isset($_GET["From_year"]) ? $_GET["From_year"] : NULL;
$To_day = isset($_GET["To_day"]) ? $_GET["To_day"] : NULL;
$To_month = isset($_GET["To_month"]) ? $_GET["To_month"] : NULL;
$To_year = isset($_GET["To_year"]) ? $_GET["To_year"] : NULL;
$champ = array();
$texte = array();
$type_recherche = array();
$k = 0;
if (isset($_GET['champ'][0]))
 while($k < count($_GET['champ'])) {
    if ((isset($_GET['champ'][$k])) and ($_GET['champ'][$k] != "") and (isset($_GET['texte'][$k])) and ($_GET['texte'][$k] != ""))
    {
        $champ[] = $_GET['champ'][$k];
        $texte[] = $_GET['texte'][$k];
        $type_recherche[] =$_GET['type_recherche'][$k];
    }
    $k++;
 }

$summarize = isset($_GET["summarize"]) ? $_GET["summarize"] : NULL;
if (!isset($_GET["sumby"])) $_GET["sumby"] = "6"; else settype($_GET["sumby"],"integer");

$sortby = isset($_GET["sortby"]) ? $_GET["sortby"] : "a";

// Si la table j_user_area est vide, il faut modifier la requête
$test_agt_j_user_area = grr_sql_count(grr_sql_query("SELECT * from agt_j_user_area"));


# Report on one entry. See below for columns in $row[].
function reporton(&$row, $dformat)
{
    global $vocab, $enable_periods;
    echo "<tr>";
    #Affiche "area"
    $area = htmlspecialchars($row[8]);
    $areadescrip = htmlspecialchars($row[10]);
    if ($areadescrip != "") $titre_area_descript = "title=\"".$areadescrip."\""; else $titre_area_descript = "";
    echo "<td ".$titre_area_descript." >".$area."</td>";
    #Affiche "room"
    $room = htmlspecialchars($row[9]);
    echo "<td>".$room."</td>";

    # Breve description (title), avec un lien
    $breve_description = htmlspecialchars(affichage_lien_resa_planning($row[3],$row[0]));
    $breve_description = "<a href=\"view_entry.php?id=$row[0]\">". $breve_description . "</a>";
    echo "<td>".$breve_description."</td>\n";

    # From date-time and duration:
    echo "<td>";
    if ($enable_periods=='y') {
        echo describe_period_span($row[1], $row[2]);
        echo "</td>\n";;

    } else {
        echo describe_span($row[1], $row[2],$dformat);
        echo "<br />".   date("d\/m\/Y\ \-\ H\:i",$row[1])." ==> ".date("d\/m\/Y\ \-\ H\:i",$row[2])."</td>\n";
    }
    #Description
    if ($row[4] != "")
        $description = nl2br(htmlspecialchars($row[4]));
    else
        $description = "&nbsp;";
    echo "<td>". $description . "</td>\n";

    #Type de réservation
    $et = grr_sql_query1("select type_name from agt_type_area where type_letter='".$row[5]."'");
    if ($et == -1) $et = "?".$row[5]."?";
    echo "<td>".$et."</td>\n";

    #Affichage de "crée par"
    $sql_beneficiaire = "SELECT prenom, nom FROM agt_utilisateurs WHERE login = '".$row[6]."'";
    $res_beneficiaire = grr_sql_query($sql_beneficiaire);
    if ($res_beneficiaire) $row_user = grr_sql_row($res_beneficiaire, 0);

    echo "<td>".htmlspecialchars($row_user[0]) ." ". htmlspecialchars($row_user[1])."</td>";

    #Affichage de la date de la dernière mise à jour

    echo "<td>". date_time_string($row[7],$dformat) . "</td>\n";

    echo "</tr>\n";
}

# $breve_description est soit une "description brève" soit un "bénéficiaire", selon la valeur de $_GET["sumby"]
# La fonction renvoie :
# $count[$room][$breve_description] : nombre de réservation pour $room et $breve_description donné
# $hours[$room][$breve_description] : nombre de d'heures de réservation pour $room et $breve_description donné
# $room_hash[$room]  : tableau des $room concernés par le décompte
# $breve_description_hash[$breve_description]  : tableau des $breve_description concernés par le décompte
# Cela va devenir la colonne et la ligne d'entête de la table de statistique.'
function accumulate(&$row, &$count, &$hours, $report_start, $report_end,
    &$room_hash, &$breve_description_hash, $csv="n")
{
    global $vocab;
    if ($_GET["sumby"] == "5")
        $temp = grr_sql_query1("select type_name from agt_type_area where type_letter = '".$row[$_GET["sumby"]]."'");
    else if (($_GET["sumby"] == "3") or ($_GET["sumby"] == "6"))
        $temp = $row[$_GET["sumby"]];
    else
        $temp = grrExtractValueFromOverloadDesc($row[12],$_GET["sumby"]);
    if ($temp == "") $temp = "(Autres)";
    if ($csv == "n") {
      #Description "Créer par":
      # [3]   Descrition brêve,(HTML) -> e.name
      # [4]   Descrition,(HTML) -> e.description
      # [5]   Type -> e.type
      # [6]   réservé par (nom ou IP), (HTML) -> e.beneficiaire
      # [12]  les champs additionnele -> e.overload_desc
      $breve_description = htmlspecialchars($temp);
      #   $row[8] : Area , $row[9]:Room
      $room = htmlspecialchars($row[8]) .$vocab["deux_points"]. "<br />" . htmlspecialchars($row[9]);
    } else {
      $breve_description = ($temp);
      #   $row[8] : Area , $row[9]:Room
      $room = removeMailUnicode($row[9]) ." (". removeMailUnicode($row[8]).")";
    }
    #Ajoute le nombre de réservations pour cette "room" et nom.
    @$count[$room][$breve_description]++;
    #Ajoute le nombre d'heure ou la ressource est utilisée.
    @$hours[$room][$breve_description] += (min((int)$row[2], $report_end)
        - max((int)$row[1], $report_start)) / 3600.0;
    $room_hash[$room] = 1;
    $breve_description_hash[$breve_description] = 1;
}
# Identique à la fonction accumulate mais adapté aux cas ou $enable_periode = 'y'
function accumulate_periods(&$row, &$count, &$hours, $report_start, $report_end,
    &$room_hash, &$breve_description_hash, $csv="n")
{
    global $vocab, $periods_name;
    $max_periods = count($periods_name);
    if ($_GET["sumby"] == "5")
        $temp = grr_sql_query1("select type_name from agt_type_area where type_letter = '".$row[$_GET["sumby"]]."'");
    else if (($_GET["sumby"] == "3") or ($_GET["sumby"] == "6"))
        $temp = $row[$_GET["sumby"]];
    else
        $temp = grrExtractValueFromOverloadDesc($row[12],$_GET["sumby"]);
    if ($temp == "") $temp = "(Autres)";
    if ($csv == "n") {
        $breve_description = htmlspecialchars($temp);
        # Area and room separated by break:
        $room = htmlspecialchars($row[8]) .$vocab["deux_points"]. "<br />" . htmlspecialchars($row[9]);
    } else {
        # Use brief description or created by as the name:
        $breve_description = ($temp);
        # Area and room separated by break:
        $room = ($row[9]) . " " . ($row[10]);
    }
    # Accumulate the number of bookings for this room and name:
    @$count[$room][$breve_description]++;
    # Accumulate hours used, clipped to report range dates:
        $dur = (min((int)$row[2], $report_end) - max((int)$row[1], $report_start))/60;
    if ($dur < (24*60))
        @$hours[$room][$breve_description] += $dur;
    else
        @$hours[$room][$breve_description] += ($dur % $max_periods) + floor( $dur/(24*60) ) * $max_periods;
    $room_hash[$room] = 1;
    $breve_description_hash[$breve_description] = 1;
}

    #Table contenant un compteur (int) et une heure (float):
function cell($count, $hours, $csv="n", $decompte="heure")
{
    if ($csv == "n")
       echo "<td class=\"BR\" align=right>($count) ". sprintf("%.2f", $hours) . "</td>\n";
    else if (($csv == "y") and ($decompte=="heure"))     // Cas CSV : affichage du décompte des heures uniquement
       echo sprintf("%.2f", $hours) . ";";
    else if (($csv == "y") and ($decompte=="resa"))     // Cas CSV : affichage du décompte des réservations uniquement
       echo "$count;";
}

# Output the summary table (a "cross-tab report"). $count and $hours are
# 2-dimensional sparse arrays indexed by [area/room][name].
# $room_hash & $breve_description_hash are arrays with indexes naming unique rooms and names.
function do_summary(&$count, &$hours, &$room_hash, &$breve_description_hash,$enable_periods,$decompte,$csv="n")
{
    global $vocab;
    if ($csv != "n") echo" ;";
    # Make a sorted array of area/rooms, and of names, to use for column
    # and row indexes. Use the rooms and names hashes built by accumulate().
    # At PHP4 we could use array_keys().
    reset($room_hash);
    while (list($room_key) = each($room_hash)) $rooms[] = $room_key;
    ksort($rooms);
    reset($breve_description_hash);
    while (list($breve_description_key) = each($breve_description_hash)) $breve_descriptions[] = $breve_description_key;
    ksort($breve_descriptions);
    $n_rooms = sizeof($rooms);
    $n_names = sizeof($breve_descriptions);

    // On affiche uniquement pour une sortie HTML
    if ($csv == "n") {
        if ($_GET["sumby"]=="6")
            $premiere_cellule = get_vocab("sum_by_creator");
        else if ($_GET["sumby"]=="3")
            $premiere_cellule = get_vocab("sum_by_descrip");
        else if ($_GET["sumby"]=="5")
            $premiere_cellule = get_vocab("type");
        else
            $premiere_cellule = grr_sql_query1("select fieldname from agt_overload where id='".$_GET["sumby"]."'");

      if ($enable_periods == 'y')
        echo "<hr /><h1>".get_vocab("summary_header_per")."</h1><table border=2 cellspacing=4>\n";
      else
        echo "<hr /><h1>".get_vocab("summary_header")."</h1><table border=2 cellspacing=4>\n";
      echo "<tr><td class=\"BL\" align=left><b>".$premiere_cellule." \ ".get_vocab("room")."</b></td>\n";
    }

    for ($c = 0; $c < $n_rooms; $c++)
    {
        if ($csv == "n")
            echo "<td class=\"BL\" align=left><b>$rooms[$c]</b></td>\n";
        else
            echo "$rooms[$c];";

        $col_count_total[$c] = 0;
        $col_hours_total[$c] = 0.0;
    }
    if ($csv == "n")
         echo "<td class=\"BR\" align=right><br /><b>".get_vocab("total")."</b></td></tr>\n";
    else
        echo html_entity_decode_all_version($vocab['total']).";\r\n";

    $grand_count_total = 0;
    $grand_hours_total = 0;

    for ($r = 0; $r < $n_names; $r++)
    {
        $row_count_total = 0;
        $row_hours_total = 0.0;
        $breve_description = $breve_descriptions[$r];
        if ($csv == "n")
            echo "<tr><td class=\"BR\" align=right><b>$breve_description</b></td>\n";
        else
            echo "$breve_description;";
        for ($c = 0; $c < $n_rooms; $c++)
        {
            $room = $rooms[$c];
            if (isset($count[$room][$breve_description]))
            {
                $count_val = $count[$room][$breve_description];
                $hours_val = $hours[$room][$breve_description];
                cell($count_val, $hours_val, $csv,$decompte);
                $row_count_total += $count_val;
                $row_hours_total += $hours_val;
                $col_count_total[$c] += $count_val;
                $col_hours_total[$c] += $hours_val;
            } else {
                if ($csv == "n")
                    echo "<td>&nbsp;</td>\n";
                else
                    echo ";";
            }
        }
        cell($row_count_total, $row_hours_total, $csv,$decompte);
        if ($csv == "n")
            echo "</tr>\n";
        else
            echo "\r\n";
        $grand_count_total += $row_count_total;
        $grand_hours_total += $row_hours_total;
    }
    if ($csv == "n")
        echo "<tr><td class=\"BR\" align=right><b>".get_vocab("total")."</b></td>\n";
    else
        echo html_entity_decode_all_version($vocab['total']).";";
    for ($c = 0; $c < $n_rooms; $c++)
        cell($col_count_total[$c], $col_hours_total[$c], $csv,$decompte);
    cell($grand_count_total, $grand_hours_total, $csv,$decompte);
    if ($csv == "n") echo "</tr></table>\n";
}
   #Si nous ne savons pas la date, nous devons la créer
if(!isset($day) or !isset($month) or !isset($year))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
}
if(empty($area))
    $area = get_default_area();

if (($summarize != 4) and ($summarize != 5)) {
    #Affiche les informations dans l'header
    print_header($day, $month, $year, $area);
    ?>
    <script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
    <?php
}

if (isset($champ[0]))
{
    #Applique les paramètres par defaut.
    #S'assurer que ces paramètres ne sont pas cités.
    $k = 0;
    while ($k < count($texte)) {
        $texte[$k] = unslashes($texte[$k]);
        #Mettre les valeurs par défaut quand le formulaire est réutilisé.
        $texte_default[$k] = htmlspecialchars($texte[$k]);
        $k++;
    }

}
else
{
    $From_time = mktime(0, 0, 0, $month, $day - getSettingValue("default_report_days"), $year);
    $To_day = $day;
    $To_month = $month;
    $To_year = $year;
    $From_day   = date("d", $From_time);
    $From_month = date("m", $From_time);
    $From_year  = date("Y", $From_time);
}
    #$summarize:
    # 1=Rapport seulement,
    # 2=Résumé seulement,
    # 3=Les deux,
    # 4=Télécharger le CSV du rapport
    # 5=Télécharger le CSV du résumé
if (empty($summarize)) $summarize = 1;

if (($summarize != 4) and ($summarize != 5)) {
?>
<div align=center><h1><?php echo get_vocab("search report stats").grr_help("aide_grr_recherche");?></h1>
<form method='get' action=report.php>
<?php
// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {
?>
<table border="0">
<tr><td class="CR"><?php echo get_vocab("report_start").get_vocab("deux_points");?></td>
    <td class="CL"> <font size="-1">
    <?php genDateSelector("From_", $From_day, $From_month, $From_year,""); ?>
    </font></td></tr>
<tr><td class="CR"><?php echo get_vocab("report_end").get_vocab("deux_points");?></td>
    <td class="CL"> <font size="-1">
    <?php genDateSelector("To_", $To_day, $To_month, $To_year,""); ?>
    </font></td></tr>
<?php
if (!isset($_GET["condition_et_ou"]) or ($_GET["condition_et_ou"] != "OR"))
    $_GET["condition_et_ou"] = "AND";
echo "<tr><td align=\"right\"><input type=\"radio\" name=\"condition_et_ou\" value=\"AND\" ";
if ($_GET["condition_et_ou"] == "AND") echo "checked";
echo " /></td>\n";
echo "<td>".get_vocab("valide toutes les conditions suivantes")."</td></tr>";
echo "<tr><td align=\"right\"><input type=\"radio\" name=\"condition_et_ou\" value=\"OR\" ";
if ($_GET["condition_et_ou"] != "AND") echo "checked";
echo " /></td>\n";
echo "<td>".get_vocab("Valide au moins une des conditions suivantes")."</td></tr>\n";

if (isset($texte))
   $nb_ligne = max((count($texte) +2),5);
else
   $nb_ligne = 5;
$k = 0;
while($k < $nb_ligne) {
    echo "<tr><td><select name=\"champ[]\" size=\"1\">\n";
    echo "<option value=''>".get_vocab("choose")."</option>\n";
    echo "<option value='area' ";
    if (isset($champ[$k]) and ($champ[$k] == "area")) echo " selected ";
    echo ">".get_vocab("match_area")."</option>\n";
    echo "<option value='room' ";
    if (isset($champ[$k]) and ($champ[$k] == "room")) echo " selected ";
    echo ">".get_vocab("room")."</option>\n";
    echo "<option value='type' ";
    if (isset($champ[$k]) and ($champ[$k] == "type")) echo " selected ";
    echo ">".get_vocab("type")."</option>\n";
    echo "<option value='name' ";
    if (isset($champ[$k]) and ($champ[$k] == "name")) echo " selected ";
    echo ">".get_vocab("namebooker")."</option>\n";
    echo "<option value='descr' ";
    if (isset($champ[$k]) and ($champ[$k] == "descr")) echo " selected ";
    echo ">".get_vocab("match_descr")."</option>\n";
    echo "<option value='login' ";
    if (isset($champ[$k]) and ($champ[$k] == "login")) echo " selected ";
    echo ">".get_vocab("match_login")."</option>\n";
    // On récupère les infos sur le champ add
    $overload_fields = mrbsOverloadGetFieldslist("");
    // Boucle sur tous les champs additionnels de l'area
    foreach ($overload_fields as $fieldname=>$fieldtype) {
        if ($overload_fields[$fieldname]["confidentiel"] != 'y') {
            echo "<option value='addon_".$overload_fields[$fieldname]["id"]."' ";
            if (isset($champ[$k]) and ($champ[$k] == "addon_".$overload_fields[$fieldname]["id"])) echo " selected ";
            echo ">".$fieldname."</option>\n";
        }
    }
    echo "</select></td>";

    echo "<td>\n";
    echo "<select name=\"type_recherche[]\" size=\"1\">\n";
    echo "<option value=\"1\" ";
    if (isset($type_recherche[$k]) and ($type_recherche[$k] == "1")) echo " selected ";
    echo ">".get_vocab("contient").get_vocab("deux_points")."</option>\n";
    echo "<option value=\"0\" ";
    if (isset($type_recherche[$k]) and ($type_recherche[$k] == "0")) echo " selected ";
    echo ">".get_vocab("ne contient pas").get_vocab("deux_points")."</option>\n";
    echo "</select>";
    if (!isset($texte_default[$k])) $texte_default[$k] ="";
    echo "<input type=\"text\" name=\"texte[]\" value=\"".$texte_default[$k]."\" size=\"20\" /><br /></td></tr>";
    $k++;
}

?>

</table><table border="0" cellpadding="5"><tr><td class="CR"><?php echo get_vocab("include").get_vocab("deux_points");?></td>
    <td class="CL">
      <input type=radio name=summarize value=1<?php if ($summarize==1) echo " checked";
        echo " />" . get_vocab("report_only");?>
      <input type=radio name=summarize value=2<?php if ($summarize==2) echo " checked";
        echo " />" . get_vocab("summary_only");?>
      <input type=radio name=summarize value=3<?php if ($summarize==3) echo " checked";
        echo " />" . get_vocab("report_and_summary");?>
      <br /><input type=radio name=summarize value=4<?php if ($summarize==4) echo " checked";
        echo " />" . get_vocab("dlrapportcsv");?>
      <input type=radio name=summarize value=5<?php if ($summarize==4) echo " checked";
        echo " />" . get_vocab("dlresumecsv");?>

    <br /></td></tr>
<tr><td class="CR"><?php echo get_vocab("summarize_by").get_vocab("summarize_by_precisions").get_vocab("deux_points");?></td>
    <td class="CL"><?php
      # [3]   Descrition brêve,(HTML) -> e.name
      # [4]   Descrition,(HTML) -> e.description
      # [5]   Type -> e.type
      # [6]   réservé par (nom ou IP), (HTML) -> e.beneficiaire
      # [12]  les champs additionnele -> e.overload_desc
      echo "<select name=\"sumby\" size=\"1\">\n";
      echo "<option value=\"6\" ";
      if ($_GET["sumby"]=="6") echo " selected";
      echo ">".get_vocab("sum_by_creator")."</option>\n";
      echo "<option value=\"3\" ";
      if ($_GET["sumby"]=="3") echo " selected";
      echo ">".get_vocab("sum_by_descrip")."</option>\n";
      echo "<option value=\"5\" ";
      if ($_GET["sumby"]=="5") echo " selected";
      echo ">".get_vocab("type")."</option>\n";
      // On récupère les infos sur le champ add
      $overload_fields = mrbsOverloadGetFieldslist("");
      // Boucle sur tous les champs additionnels de l'area
      foreach ($overload_fields as $fieldname=>$fieldtype) {
       if ($overload_fields[$fieldname]["confidentiel"] != 'y') {
        echo "<option value='".$overload_fields[$fieldname]["id"]."' ";
        if ($_GET["sumby"] == $overload_fields[$fieldname]["id"]) echo " selected ";
        echo ">".$fieldname."</option>\n";
       }
    }

      echo "</select>";
    ?>
    </td></tr>
<tr><td colspan=2 align=center><input type=submit value="<?php echo get_vocab('submit') ?>" />
</td></tr>
</table>
<?php
}
?>
</form>
</div>
<?php
// Fin de if (($summarize != 4) and ($summarize != 5)) {
}

    # Résultats:
if (isset($champ[0]))
{
    if (($summarize != 4) and ($summarize != 5)) echo "<hr />";
    // Affichage d'un lien pour format imprimable
    if (( !isset($_GET['pview'])  or ($_GET['pview'] != 1)) and (($summarize != 4) and ($summarize != 5))) {
        echo '<center><p><a href="' . traite_grr_url("","y")."report.php" . '?' . htmlspecialchars($_SERVER['QUERY_STRING']) . '&amp;pview=1" ';
        if (getSettingValue("pview_new_windows")==1) echo ' target="_blank"';
        echo '>' . get_vocab("ppreview") . '</a></p></center>';
    }

    #S'assurer que ces paramètres ne sont pas cités.
    $k = 0;
    while ($k < count($texte)) {
        $texte[$k] = unslashes($texte[$k]);
        $k++;
    }

    #Les heures de début et de fin sont aussi utilisés pour mettre l'heure dans le rapport.
    $report_start = mktime(0, 0, 0, $From_month, $From_day, $From_year);
    $report_end = mktime(0, 0, 0, $To_month, $To_day+1, $To_year);
#   La requête SQL va contenir les colonnes suivantes:
# Col Index  Description:
#   1  [0]   Entry ID, Non affiché -> e.id
#   2  [1]   Date de début (Unix) -> e.start_time
#   3  [2]   Date de fin (Unix) -> e.end_time
#   4  [3]   Descrition brêve,(HTML) -> e.name
#   5  [4]   Descrition,(HTML) -> e.description
#   6  [5]   Type -> e.type
#   7  [6]   réservé par (nom ou IP), (HTML) -> e.beneficiaire
#   8  [7]   Timestamp (création), (Unix) -> e.timestamp
#   9  [8]   Area (HTML) -> a.service_name
#  10  [9]   Room (HTML) -> r.room_name
#  11  [10]  Room description -> r.description
#  12  [11]  id de l'area -> a.id
#  13  [12]  les champs additionnele -> e.overload_desc
    $sql = "SELECT distinct e.id, e.start_time, e.end_time, e.name, e.description, "
        . "e.type, e.beneficiaire, "
        .  grr_sql_syntax_timestamp_to_unix("e.timestamp")
        . ", a.service_name, r.room_name, r.description, a.id, e.overload_desc"
        . " FROM agt_loc e, agt_service a, agt_room r, agt_type_area t";

    // Si l'utilisateur n'est pas administrateur, seuls les domaines auxquels il a accès sont pris en compte
    if(authGetUserLevel(getUserName(),-1) < 5)
        if ($test_agt_j_user_area != 0)
           $sql .= ", agt_j_user_area j ";
        $sql .= " WHERE e.room_id = r.id AND r.service_id = a.id";
    // Si l'utilisateur n'est pas administrateur, seuls les domaines auxquels il a accès sont pris en compte
    if(authGetUserLevel(getUserName(),-1) < 5)
        if ($test_agt_j_user_area == 0)
            $sql .= " and a.access='a' ";
        else
            $sql .= " and ((j.login='".$_SESSION['login']."' and j.id_area=a.id and a.access='r') or (a.access='a')) ";

        $sql .= " AND e.start_time < $report_end AND e.end_time > $report_start";

    $k = 0;
    $sql .= " AND (";
    while ($k < count($texte)) {
        if ($champ[$k] == "area")
            $sql .=  grr_sql_syntax_caseless_contains("a.service_name", $texte[$k], $type_recherche[$k]);
        if ($champ[$k] == "room")
            $sql .=  grr_sql_syntax_caseless_contains("r.room_name", $texte[$k], $type_recherche[$k]);
        if ($champ[$k] == "type")
            $sql .=  grr_sql_syntax_caseless_contains("t.type_name", $texte[$k], $type_recherche[$k]);
        if ($champ[$k] == "name")
            $sql .=  grr_sql_syntax_caseless_contains("e.name", $texte[$k], $type_recherche[$k]);
        if ($champ[$k] == "descr")
            $sql .=  grr_sql_syntax_caseless_contains("e.description", $texte[$k], $type_recherche[$k]);
        if ($champ[$k] == "login")
            $sql .=  grr_sql_syntax_caseless_contains("e.beneficiaire", $texte[$k], $type_recherche[$k]);
        // On récupère les infos sur le champ add
        $overload_fields = mrbsOverloadGetFieldslist("");
        // Boucle sur tous les champs additionnels de l'area
        foreach ($overload_fields as $fieldname=>$fieldtype) {
          if ($overload_fields[$fieldname]["confidentiel"] != 'y')
            if ($champ[$k] == "addon_".$overload_fields[$fieldname]["id"])
                $sql .=  grr_sql_syntax_caseless_contains_overload("e.overload_desc", $texte[$k], $overload_fields[$fieldname]["id"], $type_recherche[$k]);
        }

        if ($k < (count($texte)-1))
            $sql .= " ".$_GET["condition_et_ou"]." ";
        $k++;
    }
    $sql .= ")";
    $sql .= " AND  t.type_letter = e.type ";
    if( $sortby == "a" )
        #Trié par: Area, room, debut, date/heure.
        $sql .= " ORDER BY 9,r.order_display,10,t.type_name,2";
    else if( $sortby == "r" )
        #Trié par: room, area, debut, date/heure.
        $sql .= " ORDER BY r.order_display,10,9,t.type_name,2";
    else if( $sortby == "d" )
        # Order by Start date/time, Area, Room
        $sql .= " ORDER BY 2,9,r.order_display,10,t.type_name";
    else if( $sortby == "t" )
        #Trié par: type, Area, room, debut, date/heure.
        $sql .= " ORDER BY t.type_name,9,r.order_display,10,2";
    else if( $sortby == "c" )
        #Trié par: réservant, Area, room, debut, date/heure.
        $sql .= " ORDER BY e.beneficiaire,9,r.order_display,10,2";
    else if( $sortby == "b" )
        #Trié par: réservant, Area, room, debut, date/heure.
        $sql .= " ORDER BY e.name,9,r.order_display,10,2";
//    echo $sql." <br /><br />";
        $res = grr_sql_query($sql);
    if (! $res) fatal_error(0, grr_sql_error());

    $nmatch = grr_sql_count($res);

    if (($nmatch == 0) and (($summarize != 4) and ($summarize != 5)))
    {
        echo "<P><B>" . get_vocab("nothing_found") . "</B>\n";
        grr_sql_free($res);
    }
    else
    {
        if (($summarize != 4) and ($summarize != 5)) {
            echo "<P><B>" . $nmatch . " "
            . ($nmatch == 1 ? get_vocab("entry_found") : get_vocab("entries_found"))
            .  "</B>\n";
        }

        if (($summarize == 1) or ($summarize == 3)) {
            echo "<center>";
            echo "<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">";
            //    echo "<tr><td colspan=\"6\" align=\"center\">".get_vocab("trier_par").get_vocab("deux_points")."</td></tr>";
            echo "<tr>";
            // Colonne domaine
            echo "<td>";
            $m = 0;
            $param="";
            while ($m < count($champ)) {
                $param .= "&amp;champ[".$m."]=".$champ[$m]."&amp;texte[".$m."]=".$texte[$m]."&amp;type_recherche[".$m."]=".$type_recherche[$m];
                $m++;
            }
            $param .= "&amp;condition_et_ou=".$_GET["condition_et_ou"]."&amp;sumby=".$_GET["sumby"];
            if ($sortby != "a") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=a";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("match_area")."</a>";
            } else
                echo "<b>&gt;&gt; ".get_vocab("match_area")." &lt;&lt;</b>";
            echo "</td>";
            // Colonne ressource
            echo "<td>";
            if ($sortby != "r") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=r";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("room")."</a>";
            } else
                echo "<b>&gt;&gt; ".get_vocab("room")." &lt;&lt;</b>";
            echo "</td>";
            // Colonne "nom"
             echo "<td>";
             if ($sortby != "b") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=b";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("namebooker")."</a>";
            } else
                echo "<b>&gt;&gt; ".get_vocab("namebooker")." &lt;&lt;</b>";
            echo "</td>";
                // Date de début
            echo "<td>";
            if ($sortby != "d") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=d";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("start_date")."</a>";
            } else
                echo "<b>&gt;&gt; ".get_vocab("start_date")." &lt;&lt;</b>";
            echo "</td>";
            // Colonne "nom"
            echo "<td>".get_vocab("match_descr")."</td>";
            // Colonne Type
            echo "<td>";
            if ($sortby != "t") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=t";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("type")."</a>";
           } else
                echo "<b>&gt;&gt; ".get_vocab("type")." &lt;&lt;</b>";
           echo "</td>";
            // Colonne bénéficiaire
            echo "<td>";
            if ($sortby != "c") {
                echo "<a href='report.php?From_day=$From_day&amp;From_month=$From_month&amp;From_year=$From_year&amp;To_day=$To_day&amp;To_month=$To_month&amp;To_year=$To_year$param&amp;summarize=$summarize&amp;sumby=".$_GET["sumby"]."&amp;sortby=c";
                if ($_GET['pview'] != 0) echo "&amp;pview=1";
                echo "'>".get_vocab("match_login")."</a>";
            } else
                echo "<b>&gt;&gt; ".get_vocab("match_login")." &lt;&lt;</b>";
            echo "</td>";
            // Colonne "dernière modification"
            echo "<td>".get_vocab("lastupdate")."</td>";
            echo "</tr>";
        }
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
            // Récupération des données concernant l'affichage du planning du domaine
            get_planning_area_values($row[11]);

            if (($summarize == 1) or ($summarize == 3))
                reporton($row, $dformat);
            if (($summarize == 2) or ($summarize == 3))
                if ($enable_periods=='y') {
                    accumulate_periods($row, $count, $hours, $report_start, $report_end,
                    $room_hash, $breve_description_hash, "n");
                    //
                    $do_sum1 = 'y';
                } else {
                    accumulate($row, $count2, $hours2, $report_start, $report_end,
                    $room_hash2, $breve_description_hash2, "n");
                    $do_sum2 = 'y';
                }
        }
        if (($summarize == 1) or ($summarize == 3))
            echo "</table></center>";
        if (($summarize == 2) or ($summarize == 3)) {
            echo "<center>";
            // Décompte des créneaux réservées
            if (isset($do_sum1)) do_summary($count, $hours, $room_hash, $breve_description_hash,'y','',"n");
            // Décompte des heures réservées
            if (isset($do_sum2)) do_summary($count2, $hours2, $room_hash2, $breve_description_hash2,'n','',"n");
            echo "</center>";
        }

        if ($summarize == 4) {
        //Télécharger le fichier CSV
            header("Content-Type: application/csv-tab-delimited-table");
            header("Content-disposition: filename=rapport.csv");
            #Trié par: Area, room, debut, date/heure.
            $res = grr_sql_query($sql);
            if (! $res) fatal_error(0, grr_sql_error());
            $nmatch = grr_sql_count($res);
            if ($nmatch == 0) {
                echo html_entity_decode_all_version($vocab["nothing_found"]) . "\r\n";
                grr_sql_free($res);
            } else {
                // Ligne d'en-tête
                echo html_entity_decode_all_version($vocab["reservee au nom de"]).";".html_entity_decode_all_version($vocab["areas"]).";".html_entity_decode_all_version($vocab["room"]).html_entity_decode_all_version(ereg_replace("&nbsp;", " ",$vocab["deux_points"])).";".html_entity_decode_all_version($vocab["description"]).";".html_entity_decode_all_version($vocab["time"])." - ".html_entity_decode_all_version($vocab["duration"]).";".html_entity_decode_all_version($vocab["namebooker"]).html_entity_decode_all_version(ereg_replace("&nbsp;", " ",$vocab["deux_points"])).";".html_entity_decode_all_version($vocab["match_descr"]).";".html_entity_decode_all_version($vocab["lastupdate"]).";\n";
            }
            for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
                #Affichage de "crée par" et de la date de la dernière mise à jour
                echo ($row[6]) . ";";
                #Area
                echo (removeMailUnicode($row[8])) . ";";
                #Ressource
                echo (removeMailUnicode($row[9])) . ";";
                #Description de la ressource
                echo (removeMailUnicode($row[10])) . ";";
                // Récupération des données concernant l'affichage du planning du domaine
                get_planning_area_values($row[11]);
                #Affichage de l'heure et de la durée de réservation
                if ($enable_periods=='y')
                    echo describe_period_span($row[1], $row[2]) . ";";
                else
                    echo describe_span($row[1], $row[2],$dformat) . ";";
                #Destination
                echo (removeMailUnicode(affichage_lien_resa_planning($row[3], $row[0]))) . ";";
                #Description de la réservation
                $texte=str_replace(CHR(10)," ",removeMailUnicode($row[4]));
                $texte=str_replace(CHR(13)," ",$texte);
                echo ltrim(rtrim(($texte))) . ";";
                #Date derniere modif
                echo date_time_string($row[7],$dformat) . ";";
                echo "\r\n";
            }

        }
        if ($summarize == 5) {
        //Télécharger le fichier CSV
            header("Content-Type: application/csv-tab-delimited-table");
            header("Content-disposition: filename=resume.csv");
            $res = grr_sql_query($sql);
            if (! $res) fatal_error(0, grr_sql_error());
            $nmatch = grr_sql_count($res);
            if ($nmatch == 0) {
                echo html_entity_decode_all_version($vocab["nothing_found"]) . "\r\n";
                grr_sql_free($res);
            } else {
                if ($_GET["sumby"]=="6")
                    echo html_entity_decode_all_version($vocab["summarize_by"])." " .html_entity_decode_all_version($vocab["sum_by_creator"])." - $day $month $year;";
                else if ($_GET["sumby"]=="3")
                    echo html_entity_decode_all_version($vocab["summarize_by"])." " .html_entity_decode_all_version($vocab["sum_by_descrip"])." - $day $month $year;";
                else if ($_GET["sumby"]=="5")
                    echo html_entity_decode_all_version($vocab["summarize_by"])." " .html_entity_decode_all_version($vocab["type"])." - $day $month $year;";
                else {
                    $fieldname = grr_sql_query1("select fieldname from agt_overload where id='".$_GET["sumby"]."'");
                    echo html_entity_decode_all_version($vocab["summarize_by"])." " .html_entity_decode_all_version($fieldname)." - $day $month $year;";
                }
                echo "\r\n";

            }
            for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
                // Récupération des données concernant l'affichage du planning du domaine
                get_planning_area_values($row[11]);
                if ($enable_periods=='y') {
                    // pour le décompte des créneaux
                    accumulate_periods($row, $count1, $hours1, $report_start, $report_end, $room_hash1, $breve_description_hash1, "y");
                    $do_sum1 = 'y';
                } else {
                    // pour le décompte des heures
                    accumulate($row, $count2, $hours2, $report_start, $report_end, $room_hash2, $breve_description_hash2, "y");
                    $do_sum2 = 'y';
                }
                // pour le décompte des réservations
                accumulate($row, $count, $hours, $report_start, $report_end, $room_hash, $breve_description_hash,"y");
            }
            // Décompte des heures (cas ou $enable_periods != 'y')
            if (isset($do_sum1)) {
                echo "\r\n".html_entity_decode_all_version($vocab["summary_header"])."\r\n";
                do_summary($count1, $hours1, $room_hash1, $breve_description_hash1, "n", "heure", "y");
            }
            // Décompte des créneaux (cas ou $enable_periods == 'y')
            if (isset($do_sum2)) {
                echo "\r\n".html_entity_decode_all_version($vocab["summary_header_per"])."\r\n";
                do_summary($count2, $hours2, $room_hash2, $breve_description_hash2, "y", "heure", "y" );
            }
            // Décompte des réservations
            echo "\r\n\r\n\r\n".html_entity_decode_all_version($vocab["summary_header_resa"])."\r\n";
            do_summary($count, $hours, $room_hash, $breve_description_hash, "", "resa", "y");

        }

    }
}
if (($summarize != 4) and ($summarize != 5))
    include "./commun/include/trailer.inc.php";
