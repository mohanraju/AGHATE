<?php
#########################################################################
#                        edit_entry_champs_add.php                      #
#                                                                       #
#            Page "Ajax" utilisée pour générer les champs               #
#                additionnels dans la page de réservation               #
#                                                                       #
#            Dernière modification : 09/04/2008                           #
#                                                                       #
#########################################################################

include "./commun/include/admin.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/CommonFonctions.php";
include "./commun/include/ClassAghate.php";


$CommonFonction = new CommonFunctions(true);
$mysql = new MySQL();

/* Ce script a besoin de trois arguments passés par la méthode GET :
$id : l'identifiant de la réservation (0 si nouvelle réservation)
$areas : l'identifiant du domaine
$room : l'identifiant de la ressource
*/
$Aghate = new Aghate();



// Champs additionneles : on récupère les données de la réservation si il y en a
if ($id !=0){
    $overload_data = $Aghate->EntryGetOverloadDesc($id);
}

header("Content-Type: text/html;charset=".$charset_html);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

echo "</td></tr>";
// Boucle sur les areas
$overload_fields =  $Aghate->OverloadGetFieldslist($areas);
if ($overload_fields){
	foreach ($overload_fields as $fieldname=>$fieldtype) {
			if ($overload_fields[$fieldname]["obligatoire"] == "y") $flag_obli = " *" ; else $flag_obli = "";
			echo "<tr><td><table width=\"100%\" id=\"id_".$areas."_".$overload_fields[$fieldname]["id"]."\">";
			echo "<TR><TD class=E><b>".removeMailUnicode($fieldname).$flag_obli."</b></TD></TR>\n";
			if (isset($overload_data[$fieldname]["valeur"]))
				$data = $overload_data[$fieldname]["valeur"];
			else
				$data = "";
			if ($overload_fields[$fieldname]["type"] == "textarea" )
				echo "<TR><TD><TEXTAREA COLS=\"80\" ROWS=\"2\" name=\"addon_".$overload_fields[$fieldname]["id"]."\">"
				.htmlentities(removeMailUnicode($data))."</TEXTAREA></TD></TR>\n";
			else if ($overload_fields[$fieldname]["type"] == "text" )
				echo "<TR><TD><INPUT size=\"80\" type=\"text\" id=\"addon_".removeMailUnicode($fieldname)."\" name=\"addon_"
				.$overload_fields[$fieldname]["id"]."\" value=\"".htmlentities(removeMailUnicode($data))."\" />
					 
				</TD></TR>\n";
			else {
				echo "<TR><TD><select name=\"addon_".$overload_fields[$fieldname]["id"]."\" size=\"1\">\n";
				echo '<option value="">'.get_vocab('choose').'</option>';
				foreach ($overload_fields[$fieldname]["list"] as $value) {
					echo "<option ";
					if ($data == trim($value,"&") or ($data=="" and $value[0]=="&")) echo " selected";
					echo ">".trim($value,"&")."</option>\n";
				}
				echo "</select>\n</TD></TR>\n";
			}
			echo "</table>\n";
	}
}
else
{
	exit;
}
?>
