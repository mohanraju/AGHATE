<?php
 /* Copyright 2010 MOHANRAJU
 *
 */


include "./commun/include/admin.inc.php";
	include "./commun/include/CustomSql.inc.php";

$grr_script_name = "admin_calend_agents.php";


$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

$day   = date("d");
$month = date("m");
$year  = date("Y");




function make_cal($month, $year)
{
	$last_day=strftime("%d/%m/%Y",mktime(0,0,0,$month+1,0,$year));
	$first_day=strftime("%d/%m/%Y",mktime(0,0,0,$month,1,$year));
	$lastday=strftime("%d",mktime(0,0,0,$month+1,0,$year));
	echo "<h3> $month, $year </h3><br />";
	echo "<table border='1' cellspacing=1><tr>\n";
	echo $lastday;
	
	for ($j=1;$j < $lastday;$j++){
		echo "<td>$j</td\n";
	}
	echo "</tr></table><br />";	
}






if(authGetUserLevel(getUserName(),-1) < 5)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");

// Affichage de la colonne de gauche
include "admin_col_gauche.php";
?>
<script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
<?php

echo "<h2>Gestion des absent agents </h2>";

    echo "<form action=\"admin_calend_agents.php\" method=\"post\" name=\"formulaire\">\n";
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

        echo make_cal($month, $year);
exit;        
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
    echo "<center><div id=\"fixe\"><input type=\"submit\" name=\"".get_vocab('save')."\" value=\"".get_vocab("save")."\" />\n";
    echo "<input type=\"hidden\" name=\"record\" value=\"yes\" />\n";
    echo "</div></center></form>";

// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";

?>


</body>
</html>
