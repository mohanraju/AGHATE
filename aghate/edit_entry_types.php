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

/* Ce script a besoin de trois arguments passés par la méthode GET :
$id : l'identifiant de la réservation (0 si nouvelle réservation)
$areas : l'identifiant du domaine
$room : l'identifiant de la ressource
*/

// Initialisation
if (isset($_GET["type"])) {
  $type = $_GET["type"];
} else die();

if (isset($_GET['areas'])) {
  $areas = $_GET['areas'];
  settype($areas,"integer");
}

else die();


if (isset($_GET['room'])) {
  $room = $_GET['room'];
  if ($room != "") settype($room,"integer");
}
else die();


if ((authGetUserLevel(getUserName(),-1) < 2) and (auth_visiteur(getUserName(),$room) == 0))
{
    showAccessDenied("","","","","");
    exit();
}

if(authUserAccesArea($_SESSION['login'], $areas)==0)
{
    showAccessDenied("","","","","");
    exit();
}

// Type de réservation
// modifée par mohan

if ($_SESSION["URM"]=="470"){
	$desc_type="Sexe";
	}elseif($_SESSION["URM"]=="560"){
		$desc_type="UH";
	}elseif($_SESSION["URM"]=="010"){
		$desc_type="Type";
	}else{
		$desc_type="Type";
	}



// Avant d'afficher la liste déroulante des types, on stocke dans $display_type et on teste le nombre de types à afficher
// Si ne nombre est égal à 1, on ne laisse pas le choix
$nb_type = 0;
$type_nom_unique = "??";
$type_id_unique = "??";
///$display_type = "<B>".get_vocab("type")." *".get_vocab("deux_points")."</B></TD></TR>\n";
$display_type = "<B>".$desc_type." *".get_vocab("deux_points")."</B></TD></TR>\n";
$display_type .= "<TR><TD class=\"CL\">";
$display_type .= "<SELECT name=\"type\" size=\"1\">\n";
$display_type .= "<OPTION VALUE='0'>".get_vocab("choose")."\n";
$sql = "SELECT DISTINCT t.type_name, t.type_letter, t.id FROM agt_type_area t
LEFT JOIN agt_j_type_area j on j.id_type=t.id
WHERE (j.id_area  IS NULL or j.id_area != '".$areas."')
ORDER BY t.order_display";
$res = grr_sql_query($sql);

if ($res)
  for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
      // La requête sql précédente laisse passer les cas où un type est non valide
      // dans le domaine concerné ET au moins dans un autre domaine, d'où le test suivant
      $test = grr_sql_query1("select id_type from agt_j_type_area where id_type = '".$row[2]."' and id_area='".$areas."'");
      if ($test == -1)
    {
      $nb_type ++;
      $type_nom_unique = $row[0];
      $type_id_unique = $row[1];
      $display_type .= "<OPTION VALUE=\"".$row[1]."\" ";
      // Modification d'une réservation
      if ($type != "") {
        if ($type == $row[1])  {
          $display_type .=  " SELECTED";
        }
      } else {
      // Nouvelle réservation
          $id_type_par_defaut = grr_sql_query1("select id_type_par_defaut from agt_service where id = '".$areas."'");
          if ($id_type_par_defaut == $row[2])  $display_type .=  " SELECTED";
      }
      $display_type .=  " >".htmlentities(removeMailUnicode($row[0]))."</option>\n";
    }
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
