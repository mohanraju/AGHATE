<?php
#########################################################################
#                            admin_type_modify.php                      #
#                                                                       #
#      interface de création/modification des types de réservations     #
#               Dernière modification : 28/03/2008                      #
#                                                                       #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau - Pascal Ragot
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
$grr_script_name = "admin_type_modify.php";
$ok = NULL;
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


// Initialisation
$id_type = isset($_GET["id_type"]) ? $_GET["id_type"] : 0;
$type_name = isset($_GET["type_name"]) ? $_GET["type_name"] : NULL;
$order_display = isset($_GET["order_display"]) ? $_GET["order_display"] : NULL;
$type_letter = isset($_GET["type_letter"]) ? $_GET["type_letter"] : NULL;
$couleur = isset($_GET["couleur"]) ? $_GET["couleur"] : NULL;
$msg ='';
if (isset($_GET["change_room_and_back"])) {
    $_GET['change_type'] = "yes";
    $_GET['change_done'] = "yes";
}













// Enregistrement
if (isset($_GET['change_type'])) {
    $_SESSION['displ_msg'] = "yes";
    if ($type_name == '') $type_name = "A définir";
    if ($type_letter == '') $type_letter = "A";
    if ($couleur == '') $couleur = "1";
    
	// ajouté par mohan le 25/03/2014 
	//par default tous les nouvelle coleur sont pas attache au service
	// get all service
	$services = grr_sql_query("select id from agt_service");	
	if ($services) 
	{
		for ($i = 0; ($crow = grr_sql_row($services, $i)); $i++)
		{
			$LstService[]=$crow[0];
		}
	}
 


    
    if ($id_type>0)
    {
        // Test sur $type_letter
        $test = grr_sql_query1("select count(id) from agt_type_area where type_letter='".$type_letter."' and id!='".$id_type."'");
        if ($test > 0) {
            $msg = "Enregistrement impossible : Un type portant la même lettre existe déjà.";
        } else {
            $sql = "UPDATE agt_type_area SET
            type_name='".protect_data_sql($type_name)."',
            order_display =";
            if (is_numeric($order_display))
              $sql= $sql .intval($order_display).",";
            else
              $sql= $sql ."0,";
            $sql = $sql . 'type_letter="'.$type_letter.'",';
            $sql = $sql . 'couleur="'.$couleur.'"';
            $sql = $sql . " WHERE id=$id_type";
            if (grr_sql_command($sql) < 0)
                {
                fatal_error(0, get_vocab('update_type_failed') . grr_sql_error());
                $ok = 'no';
                } else
                  $msg = get_vocab("message_records");
        }
    }
    else
    {
        // Test sur $type_letter
        $test = grr_sql_query1("select count(id) from agt_type_area where type_letter='".$type_letter."'");
        if ($test > 0) {
            $msg = "Enregistrement impossible : Un type portant la même lettre existe déjà.";
        } else {
            $sql = "INSERT INTO agt_type_area SET
            type_name='".protect_data_sql($type_name)."',
            order_display =";
            if (is_numeric($order_display))
              $sql= $sql .intval($order_display).",";
            else
              $sql= $sql ."0,";
            $sql = $sql . 'type_letter="'.$type_letter.'",';
            $sql = $sql . 'couleur="'.$couleur.'"';
            if (grr_sql_command($sql) < 0)
			{
				fatal_error(1, "<p>" . grr_sql_error());
				$ok = 'no';
			} else {
				$msg = get_vocab("message_records");
				
				$id=$test = grr_sql_query1("select max(id) from agt_type_area ");
				 //insert dans le services
				 for($s=0; $s < count($LstService); $s++)
				 {
					  $sql = "INSERT INTO agt_j_type_area set id_type='".$id."', id_area='".$LstService[$s]."'"; 
 					  grr_sql_command($sql);
				 }
 
			}
                
        }

    }
}
    // Si pas de problème, retour à la page d'accueil après enregistrement
    if ((isset($_GET['change_done'])) and (!isset($ok))) {
        $_SESSION['displ_msg'] = 'yes';
        Header("Location: "."admin_type.php?msg=".$msg);
        exit();
    }


# print the page header
    print_header("","","","",$type="with_session", $page="admin");
    affiche_pop_up($msg,"admin");
    ?>
    <script src="./commun/js/functions.js" type="text/javascript" language="javascript"></script>
    <?php

    if ((isset($id_type)) and ($id_type>0)) {
        $res = grr_sql_query("SELECT * FROM agt_type_area WHERE id=$id_type");
        if (! $res) fatal_error(0, get_vocab('message_records_error'));
        $row = grr_sql_row_keyed($res, 0);
        grr_sql_free($res);
        $change_type='modif';
        echo "<h2 ALIGN=center>".get_vocab("admin_type_modify_modify.php")."</h2>";
    } else {
        $row["id"] = '0';
        $row["type_name"] = '';
        $row["type_letter"] = '';
        $row["order_display"]  = 0;
        $row["couleur"]  = '';
        echo "<h2 ALIGN=center>".get_vocab('admin_type_modify_create.php')."</h2>";
    }
    echo get_vocab('admin_type_explications')."<br /><br />";
    ?>
    <form action="admin_type_modify.php" method='get'>
    <?php
    echo "<input type=\"hidden\" name=\"id_type\" value=\"".$id_type."\" />\n";

    echo "<center>
    <TABLE border=1>\n";
    echo "<TR>";
    echo "<TD>".get_vocab("type_name").get_vocab("deux_points")."</TD>\n";
    echo "<TD><input type=\"text\" name=\"type_name\" value=\"".htmlspecialchars($row["type_name"])."\" size=\"20\" /></TD>\n";
    echo "</TR><TR>\n";
    echo "<TD>".get_vocab("type_num").get_vocab("deux_points")."</TD>\n";
    echo "<TD>";
    echo "<select name=\"type_letter\" size=\"1\">\n";
    echo "<option value=''>".get_vocab("choose")."</option>\n";
    $letter = "A";
    for ($i=1;$i<=100;$i++) {
       echo "<option value='".$letter."' ";
       if ($row['type_letter'] == $letter) echo " selected";
       echo ">".$letter."</option>\n";
       $letter++;
    }

    echo "</select>";
    echo "</TD>\n";
    echo "</TR><TR>\n";
    echo "<TD>".get_vocab("type_order").get_vocab("deux_points")."</TD>\n";
    echo "<TD><input type=\"text\" name=\"order_display\" value=\"".htmlspecialchars($row["order_display"])."\" size=\"20\" /></TD>\n";
    echo "</TR>";
   if ($row["couleur"]  != '') {
        echo "<TR>\n";
        echo "<TD>".get_vocab("type_color").get_vocab("deux_points")."</TD>\n";
        echo "<TD bgcolor=\"".$tab_couleur[$row["couleur"]]."\">&nbsp;</TD>";
        echo "</TR>";
    }
    echo "</TABLE>\n";
    echo get_vocab("type_color").get_vocab("deux_points");
    echo "<table border=2><tr>\n";
    $nct = 0;
    foreach($tab_couleur as $key=>$value)
    {
      $checked = " ";
      if ($key == $row["couleur"])
          $checked = "checked";
      if (++$nct > 4)
            {
                $nct = 1;
                echo "</tr><tr>";
            }
      echo "<TD bgcolor=\"".$tab_couleur[$key]."\"><input type=\"radio\" name=\"couleur\" value=\"".$key."\" ".$checked." />______________</TD>";
    }
    echo "</tr></table>\n";
    echo "<TABLE><tr><td>\n";
    echo "<input type=\"submit\" name=\"change_type\"  value=\"".get_vocab("save")."\" />\n";
    echo "</td><td>\n";
    echo "<input type=\"submit\" name=\"change_done\" value=\"".get_vocab("back")."\" />";
    echo "</td><td>\n";
    echo "<input type=\"submit\" name=\"change_room_and_back\" value=\"".get_vocab("save_and_back")."\" />";
    echo "</td></tr></table>";


?>
    </center>
    </form>



</body>
</html>
