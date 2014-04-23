<?php
#########################################################################
#                        edit_entry_types.php                           #
#                                                                       #
#            Page "Ajax" utilisée pour générer les types                #
#                                                                       #
#            Dernière modification : 09/04/2008                         #
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
include "./commun/include/mrbs_sql.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/CommonFonctions.php";


$CommonFonction = new CommonFunctions(true);
$mysql = new MySQL();

/* Ce script a besoin de trois arguments passés par la méthode GET :
$id : l'identifiant de la réservation (0 si nouvelle réservation)
$areas : l'identifiant du domaine
$room : l'identifiant de la ressource
*/

if ($_SESSION["URM"]=="470"){
	$desc_type="Sexe";
	}elseif($_SESSION["URM"]=="560"){
		$desc_type="UH";
	}elseif($_SESSION["URM"]=="010"){
		$desc_type="Type";
	}else{
		$desc_type="Type";
	}



//~ / Avant d'afficher la liste déroulante des types, on stocke dans $display_type et on teste le nombre de types à afficher
// Si ne nombre est égal à 1, on ne laisse pas le choix
$nb_type = 0;
$type_nom_unique = "??";
$type_id_unique = "??";
///$display_type = "<B>".get_vocab("type")." *".get_vocab("deux_points")."</B></TD></TR>\n";
$display_type = "<B>".$desc_type." *".get_vocab("deux_points")."</B></TD></TR>\n";
$display_type .= "<TR><TD class=\"CL\">";
$display_type .= "<SELECT name=\"type\" size=\"1\">\n";
$display_type .= "<OPTION VALUE='0'>".get_vocab("choose")."\n";
$sql = "SELECT DISTINCT t.type_name as type_name, t.type_letter as type_letter, t.id as id FROM agt_type_area t
				LEFT JOIN agt_j_type_area j on j.id_type=t.id
				WHERE (j.id_area  IS NULL or j.id_area != '".$areas."')
				ORDER BY t.order_display";
//echo $sql;
$row = $mysql->select($sql);

/*==================================================================================
 * 
 * SI PROBLEME, DEUXIEME TEST MIS EN COMMENTAIRE  
 * 
 * ==============================================================================*/

if ($row)
	for ($i = 0; $i<count($row); $i++){
		// La requête sql précédente laisse passer les cas où un type est non valide
		// dans le domaine concerné ET au moins dans un autre domaine, d'où le test suivant
		$sql_test = "select id_type from agt_j_type_area where id_type = '".$row[$i]['id']."' and id_area='".$areas."'";
		$test = $mysql->select($sql_test);
		
		//if (!$test)
		//{
		  $nb_type ++;
		  $type_nom_unique = $row[$i]['type_name'];
		  $type_id_unique = $row[$i]['type_letter'];
		  $display_type .= "<OPTION VALUE=\"".$row[$i]['type_letter']."\" ";
		  // Modification d'une réservation
		  if ($type != "") {
			if ($type == $row[$i]['type_letter'])  {
			  $display_type .=  " SELECTED";
			}
		  }else{
			// Nouvelle réservation
			$sql_res = $mysql->select("select id_type_par_defaut from agt_service where id = '".$areas."'");
			$id_type_par_defaut = $sql_res[0]['id_type_par_defaut'];
			if ($id_type_par_defaut == $row[$i]['id'])  $display_type .=  " SELECTED";
		  }
		  $display_type .=  " >".htmlentities(removeMailUnicode($row[$i]['type_name']))."</option>\n";
		//}
    }

$display_type .=  "</SELECT>\n";
header("Content-Type: text/html;charset=".$charset_html);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if ($nb_type > 1) {
    echo $display_type;
} else {
    echo "<b>".get_vocab("type").get_vocab("deux_points").htmlentities(removeMailUnicode($type_nom_unique))."</b><input type=\"hidden\" name=\"type\" value=\"".$type_id_unique."\" />\n";
}
?>
