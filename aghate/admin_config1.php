<?php
/**
 * admin_config1.php
 * Interface permettant � l'administrateur la configuration de certains param�tres g�n�raux
 * Ce script fait partie de l'application GRR
 * Derni�re modification : $Date: 2010-04-07 17:49:56 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: admin_config1.php,v 1.14 2010-04-07 17:49:56 grr Exp $
 * @filesource
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
/**
 * $Log: admin_config1.php,v $
 * Revision 1.14  2010-04-07 17:49:56  grr
 * *** empty log message ***
 *
 * Revision 1.13  2010-04-07 15:38:14  grr
 * *** empty log message ***
 *
 * Revision 1.12  2009-09-29 18:02:56  grr
 * *** empty log message ***
 *
 * Revision 1.11  2009-04-14 12:59:17  grr
 * *** empty log message ***
 *
 * Revision 1.10  2009-04-09 14:52:31  grr
 * *** empty log message ***
 *
 * Revision 1.9  2009-02-27 13:28:19  grr
 * *** empty log message ***
 *
 * Revision 1.8  2009-01-20 07:19:16  grr
 * *** empty log message ***
 *
 * Revision 1.7  2008-11-16 22:00:58  grr
 * *** empty log message ***
 *
 * Revision 1.6  2008-11-13 21:32:51  grr
 * *** empty log message ***
 *
 * Revision 1.5  2008-11-10 08:17:34  grr
 * *** empty log message ***
 *
 *
 */

if (isset($_POST['title_home_page'])) {
    if (!saveSetting("title_home_page", $_POST['title_home_page'])) {
        echo "Erreur lors de l'enregistrement de title_home_page !<br />";
        die();
    }
}
if (isset($_POST['message_home_page'])) {
    if (!saveSetting("message_home_page", $_POST['message_home_page'])) {
        echo "Erreur lors de l'enregistrement de message_home_page !<br />";
        die();
    }
}
if (isset($_POST['company'])) {
    if (!saveSetting("company", $_POST['company'])) {
        echo "Erreur lors de l'enregistrement de company !<br />";
        die();
    }
}
if (isset($_POST['webmaster_name'])) {
    if (!saveSetting("webmaster_name", $_POST['webmaster_name'])) {
        echo "Erreur lors de l'enregistrement de webmaster_name !<br />";
        die();
    }
}
if (isset($_POST['webmaster_email'])) {
    if (!saveSetting("webmaster_email", $_POST['webmaster_email'])) {
        echo "Erreur lors de l'enregistrement de webmaster_email !<br />";
        die();
    }
}
if (isset($_POST['technical_support_email'])) {
    if (!saveSetting("technical_support_email", $_POST['technical_support_email'])) {
        echo "Erreur lors de l'enregistrement de technical_support_email !<br />";
        die();
    }
}
if (isset($_POST['message_accueil'])) {
    if (!saveSetting("message_accueil", $_POST['message_accueil'])) {
        echo "Erreur lors de l'enregistrement de message_accueil !<br />";
        die();
    }
}
if (isset($_POST['grr_url'])) {
    if (!saveSetting("grr_url", $_POST['grr_url'])) {
        echo "Erreur lors de l'enregistrement de grr_url !<br />";
        die();
    }
}
if (isset($_POST["ok"])) {
  if (isset($_POST['use_grr_url'])) $use_grr_url = "y"; else $use_grr_url = "n";
    if (!saveSetting("use_grr_url", $use_grr_url)) {
      echo "Erreur lors de l'enregistrement de use_grr_url !<br />";
      die();
  }
}


// Style/th�me
if (isset($_POST['default_css'])) {
    if (!saveSetting("default_css", $_POST['default_css'])) {
        echo "Erreur lors de l'enregistrement de default_css !<br />";
        die();
    }
}

// langage
if (isset($_POST['default_language'])) {
    if (!saveSetting("default_language", $_POST['default_language'])) {
        echo "Erreur lors de l'enregistrement de default_language !<br />";
        die();
    }
    unset ($_SESSION['default_language']);

}

// Type d'affichage des listes des domaines et des ressources
if (isset($_POST['area_list_format'])) {
    if (!saveSetting("area_list_format", $_POST['area_list_format'])) {
        echo "Erreur lors de l'enregistrement de area_list_format !<br />";
        die();
    }
}

// site par d�faut
if (isset($_POST['id_site'])) {
    if (!saveSetting("default_site", $_POST['id_site'])) {
        echo "Erreur lors de l'enregistrement de default_site !<br />";
        die();
    }
}

// domaine par d�faut
if (isset($_POST['id_area'])) {
    if (!saveSetting("default_area", $_POST['id_area'])) {
        echo "Erreur lors de l'enregistrement de default_area !<br />";
        die();
    }
}
if (isset($_POST['id_room'])) {
    if (!saveSetting("default_room", $_POST['id_room'])) {
        echo "Erreur lors de l'enregistrement de default_room !<br />";
        die();
    }
}

// Affichage de l'adresse email
if (isset($_POST['display_level_email'])) {
    if (!saveSetting("display_level_email", $_POST['display_level_email'])) {
        echo "Erreur lors de l'enregistrement de display_level_email !<br />";
        die();
    }
}

// display_info_bulle
if (isset($_POST['display_info_bulle'])) {
    if (!saveSetting("display_info_bulle", $_POST['display_info_bulle'])) {
        echo "Erreur lors de l'enregistrement de display_info_bulle !<br />";
        die();
    }
}

// display_full_description
if (isset($_POST['display_full_description'])) {
    if (!saveSetting("display_full_description", $_POST['display_full_description'])) {
        echo "Erreur lors de l'enregistrement de display_full_description !<br />";
        die();
    }
}

// display_short_description
if (isset($_POST['display_short_description'])) {
    if (!saveSetting("display_short_description", $_POST['display_short_description'])) {
        echo "Erreur lors de l'enregistrement de display_short_description !<br />";
        die();
    }
}

// remplissage de la description br�ve
if (isset($_POST['remplissage_description_breve'])) {
    if (!saveSetting("remplissage_description_breve", $_POST['remplissage_description_breve'])) {
        echo "Erreur lors de l'enregistrement de remplissage_description_breve !<br />";
        die();
    }
}

// pview_new_windows
if (isset($_POST['pview_new_windows'])) {
    if (!saveSetting("pview_new_windows", $_POST['pview_new_windows'])) {
        echo "Erreur lors de l'enregistrement de pview_new_windows !<br />";
        die();
    }
}

// gestion_lien_aide
if (isset($_POST['gestion_lien_aide'])) {
    if (($_POST['gestion_lien_aide']=="perso") and (trim($_POST['lien_aide'])==""))
        $_POST['gestion_lien_aide'] = "ext";
    else if ($_POST['gestion_lien_aide']!="perso")
        $_POST['lien_aide']="";

    if (!saveSetting("lien_aide", $_POST['lien_aide'])) {
        echo "Erreur lors de l'enregistrement de lien_aide !<br />";
        die();
    }

    if (!saveSetting("gestion_lien_aide", $_POST['gestion_lien_aide'])) {
        echo "Erreur lors de l'enregistrement de gestion_lien_aide !<br />";
        die();
    }

}

# Lors de l'�dition d'un rapport, valeur par d�faut en nombre de jours
# de l'intervalle de temps entre la date de d�but du rapport et la date de fin du rapport.
if (isset($_POST['default_report_days'])) {
    settype($_POST['default_report_days'],"integer");
    if ($_POST['default_report_days'] <=0) $_POST['default_report_days'] = 0;
    if (!saveSetting("default_report_days", $_POST['default_report_days'])) {
        echo "Erreur lors de l'enregistrement de default_report_days !<br />";
        die();
    }
}

if (isset($_POST['longueur_liste_ressources_max'])) {
    settype($_POST['longueur_liste_ressources_max'],"integer");
    if ($_POST['longueur_liste_ressources_max'] <=0) $_POST['longueur_liste_ressources_max'] = 1;
    if (!saveSetting("longueur_liste_ressources_max", $_POST['longueur_liste_ressources_max'])) {
        echo "Erreur lors de l'enregistrement de longueur_liste_ressources_max !<br />";
        die();
    }
}
$msg = '';
if (isset($_POST["ok"])) {
  // Suppression du logo
  if (isset($_POST['sup_img'])) {
    $dest = './images/';
    $ok1 = false;
    if ($f = @fopen("$dest/.test", "w")) {
      @fputs($f, '<'.'?php $ok1 = true; ?'.'>');
      @fclose($f);
      include("$dest/.test");
    }
    if (!$ok1) {
      $msg .= "L\'image n\'a pas pu �tre supprim�e : probl�me d\'�criture sur le r�pertoire. Veuillez signaler ce probl�me � l\'administrateur du serveur.\\n";
      $ok = 'no';
    } else {
      $nom_picture = "./images/".getSettingValue("logo");
      if (@file_exists($nom_picture)) unlink($nom_picture);
      if (!saveSetting("logo", "")) {
        $msg .= "Erreur lors de l'enregistrement du logo !\\n";
        $ok = 'no';
      }

    }
  }

  // Enregistrement du logo
  $doc_file = isset($_FILES["doc_file"]) ? $_FILES["doc_file"] : NULL;
  if (preg_match("`\.([^.]+)$`", $doc_file['name'], $match)) {
    $ext = strtolower($match[1]);
    if ($ext!='jpg' and $ext!='png'and $ext!='gif') {
      $msg .= "L\'image n\'a pas pu �tre enregistr�e : les seules extentions autoris�es sont gif, png et jpg.\\n";
      $ok = 'no';
    } else {
      $dest = './images/';
      $ok1 = false;
      if ($f = @fopen("$dest/.test", "w")) {
        @fputs($f, '<'.'?php $ok1 = true; ?'.'>');
        @fclose($f);
        include("$dest/.test");
      }
      if (!$ok1) {
        $msg .= "L\'image n\'a pas pu �tre enregistr�e : probl�me d\'�criture sur le r�pertoire \"images\". Veuillez signaler ce probl�me � l\'administrateur du serveur.\\n";
        $ok = 'no';
      } else {
        $ok1 = @copy($doc_file['tmp_name'], $dest.$doc_file['name']);
        if (!$ok1) $ok1 = @move_uploaded_file($doc_file['tmp_name'], $dest.$doc_file['name']);
        if (!$ok1) {
          $msg .= "L\'image n\'a pas pu �tre enregistr�e : probl�me de transfert. Le fichier n\'a pas pu �tre transf�r� sur le r�pertoire IMAGES. Veuillez signaler ce probl�me � l\'administrateur du serveur.\\n";
          $ok = 'no';
        } else {
          $tab = explode(".", $doc_file['name']);
          $ext = strtolower($tab[1]);
          if ($dest.$doc_file['name']!=$dest."logo.".$ext) {
            if (@file_exists($dest."logo.".$ext)) @unlink($dest."logo.".$ext);
            rename($dest.$doc_file['name'],$dest."logo.".$ext);
          }
          @chmod($dest."logo.".$ext, 0666);
          $picture_room = "logo.".$ext;
          if (!saveSetting("logo", $picture_room)) {
            $msg .= "Erreur lors de l'enregistrement du logo !\\n";
            $ok = 'no';
          }
        }
      }
    }
  } else if ($doc_file['name'] != '') {
    $msg .= "L\'image n\'a pas pu �tre enregistr�e : le fichier image s�lectionn� n'est pas valide !\\n";
    $ok = 'no';
  }

}

// nombre de calendriers
if (isset($_POST['nb_calendar'])) {
    settype($_POST['nb_calendar'],"integer");
    if (!saveSetting("nb_calendar", $_POST['nb_calendar'])) {
        echo "Erreur lors de l'enregistrement de nb_calendar !<br />";
        die();
    }
}

$demande_confirmation = 'no';
if (isset($_POST['begin_day']) and isset($_POST['begin_month']) and isset($_POST['begin_year'])) {
    while (!checkdate($_POST['begin_month'],$_POST['begin_day'],$_POST['begin_year']))
        $_POST['begin_day']--;
    $begin_bookings = mktime(0,0,0,$_POST['begin_month'],$_POST['begin_day'],$_POST['begin_year']);
    $test_del1 = mysql_num_rows(mysql_query("select * from ".TABLE_PREFIX."_entry WHERE (end_time < '$begin_bookings' )"));
    $test_del2 = mysql_num_rows(mysql_query("select * from ".TABLE_PREFIX."_repeat WHERE (end_date < '$begin_bookings')"));
    if (($test_del1!=0) or ($test_del2!=0)) {
        $demande_confirmation = 'yes';
    } else {
        if (!saveSetting("begin_bookings", $begin_bookings))
        echo "Erreur lors de l'enregistrement de begin_bookings !<br />";
    }

}
if (isset($_POST['end_day']) and isset($_POST['end_month']) and isset($_POST['end_year'])) {
    while (!checkdate($_POST['end_month'],$_POST['end_day'],$_POST['end_year']))
        $_POST['end_day']--;
    $end_bookings = mktime(0,0,0,$_POST['end_month'],$_POST['end_day'],$_POST['end_year']);
    if ($end_bookings < $begin_bookings) $end_bookings = $begin_bookings;


    $test_del1 = mysql_num_rows(mysql_query("select * from ".TABLE_PREFIX."_entry WHERE (start_time > '$end_bookings' )"));
    $test_del2 = mysql_num_rows(mysql_query("select * from ".TABLE_PREFIX."_repeat WHERE (start_time > '$end_bookings')"));
    if (($test_del1!=0) or ($test_del2!=0)) {
        $demande_confirmation = 'yes';
    } else {
        if (!saveSetting("end_bookings", $end_bookings))
        echo "Erreur lors de l'enregistrement de end_bookings !<br />";
    }


}

if ($demande_confirmation == 'yes') {
    header("Location: ./admin_confirm_change_date_bookings.php?end_bookings=$end_bookings&begin_bookings=$begin_bookings");
    die();
}

if (!loadSettings())
    die("Erreur chargement settings");

// Si pas de probl�me, message de confirmation
if (isset($_POST['ok'])) {
	$_SESSION['displ_msg'] = 'yes';
    if ($msg == '') $msg = get_vocab("message_records");
	Header("Location: "."admin_config.php?msg=".$msg);
	exit();
}

if ((isset($_GET['msg'])) and isset($_SESSION['displ_msg']) and ($_SESSION['displ_msg']=='yes'))  {
   $msg = $_GET['msg'];
}
else
   $msg = '';

// Utilisation de la biblioth�qye prototype dans ce script
$use_prototype = 'y';

# print the page header
print_header("","","","",$type="with_session", $page="admin");
affiche_pop_up($msg,"admin");

// Affichage de la colonne de gauche
include "admin_col_gauche.php";

// Affichage du tableau de choix des sous-configuration
include "./commun/include/admin_config_tableau.inc.php";

//echo "<h2>".get_vocab('admin_config1.php')."</h2>";
//echo "<p>".get_vocab('mess_avertissement_config')."</p>";


// Adapter les fichiers de langue
echo "<h3>".get_vocab("adapter fichiers langue")."</h3>\n";
echo get_vocab("adapter fichiers langue explain").grr_help("aid_grr_adapter_fichiers_langue");

//
// Config g�n�rale
//****************
//
echo "<form enctype=\"multipart/form-data\" action=\"./admin_config.php\" id=\"nom_formulaire\" method=\"post\" style=\"width: 100%;\">";
echo "<h3>".get_vocab("miscellaneous")."</h3>\n";
?>
<table border='0'>

<tr><td><?php echo get_vocab("title_home_page"); ?></td>
<td><input type="text" name="title_home_page" id="title_home_page" size="40" value="<?php echo(getSettingValue("title_home_page")); ?>" /></td>
</tr>
<tr><td><?php echo get_vocab("message_home_page"); ?></td>
<td><textarea name="message_home_page" rows="3" cols="40"><?php echo(getSettingValue("message_home_page")); ?>
</textarea></td>

</tr>
<tr><td><?php echo get_vocab("company"); ?></td>
<td><input type="text" name="company" size="40" value="<?php echo(getSettingValue("company")); ?>" /></td>
</tr>
<tr>
<td><?php echo get_vocab("grr_url"); ?></td>
<td><input type="text" name="grr_url" size="40" value="<?php echo(getSettingValue("grr_url")); ?>" /></td>
</tr>
<tr><td colspan="2"><input type="checkbox" name="use_grr_url" value="y" <?php if (getSettingValue("use_grr_url")=='y') echo " checked=\"checked\" "; ?> /><i><?php echo get_vocab("grr_url_explain"); ?></i></td></tr>
<tr>
<td><?php echo get_vocab("webmaster_name"); ?></td>
<td><input type="text" name="webmaster_name" size="40" value="<?php echo(getSettingValue("webmaster_name")); ?>" /></td>
</tr>
<tr>
<td><?php echo get_vocab("webmaster_email")."<br /><i>".get_vocab("plusieurs_adresses_separees_points_virgules")."</i>"; ?>
</td>
<td><input type="text" name="webmaster_email" size="40" value="<?php echo(getSettingValue("webmaster_email")); ?>" /></td>
</tr>
<tr>
<td><?php echo get_vocab("technical_support_email")."<br /><i>".get_vocab("plusieurs_adresses_separees_points_virgules")."</i>"; ?></td>
<td><input type="text" name="technical_support_email" size="40" value="<?php echo(getSettingValue("technical_support_email")); ?>" /></td>
</tr>
</table>
<?php
echo "<h3>".get_vocab("logo_msg")."</h3>\n";
  echo "<table><tr><td>".get_vocab("choisir_image_logo")."</td>
<td><input type=\"file\" name=\"doc_file\" size=\"30\" /></td></tr>\n";
$nom_picture = "./images/".getSettingValue("logo");
if ((getSettingValue("logo")!='') and (@file_exists($nom_picture))) {
	echo "<tr><td>".get_vocab("supprimer_logo").get_vocab("deux_points");
  echo "<img src=\"".$nom_picture."\" class=\"image\" alt=\"logo\" title=\"".$nom_picture."\"/>\n";
 	echo "</td><td><input type=\"checkbox\" name=\"sup_img\" /></td></tr>";
}
echo "</table>";

echo "<h3>".get_vocab("affichage_calendriers")."</h3>\n";
echo "<p>".get_vocab("affichage_calendriers_msg").get_vocab("deux_points");
echo "<select name=\"nb_calendar\" >\n";
for ($k=0;$k<6;$k++) {
  echo "<option value=\"".$k."\" ";
  if (getSettingValue("nb_calendar") == $k)
    echo " selected=\"selected\" ";
  echo ">".$k."</option>\n";
}
echo "</select></p>";


if (getSettingValue("use_fckeditor") == 1) {
   	echo "<script type=\"text/javascript\" src=\"./ckeditor/ckeditor.js\"></script>\n";
}
echo "<h3>".get_vocab("message perso")."</h3>\n";
echo "<p>".get_vocab("message perso explain");
if (getSettingValue("use_fckeditor") != 1)
    echo " ".get_vocab("description complete2");
if (getSettingValue("use_fckeditor") == 1) {
      echo "<textarea class=\"ckeditor\" id=\"editor1\" name=\"message_accueil\" rows=\"8\" cols=\"120\">\n";
      echo htmlspecialchars(getSettingValue('message_accueil'));
      echo "</textarea>\n";
?>
      <script type="text/javascript">
		//<![CDATA[
			CKEDITOR.replace( 'editor1',
				{
					toolbar :
	[
	 ['Source'],
   ['Cut','Copy','Paste','PasteText','PasteFromWord', 'SpellChecker', 'Scayt'],
   ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
   ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
   ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
   ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
   ['Link','Unlink','Anchor'],
   ['Image','Table','HorizontalRule','SpecialChar','PageBreak'],
  	]
				});

		//]]>
		</script>
<?php
    } else {
        echo "\n<textarea name=\"message_accueil\" rows=\"8\" cols=\"120\">".htmlspecialchars(getSettingValue('message_accueil'))."</textarea>\n";
    }
echo "</p>";



//
// D�but et fin des r�servations
//******************************
//
echo "<hr /><h3>".get_vocab("title_begin_end_bookings")."</h3>\n";
?>
<table border='0'>
<tr><td><?php echo get_vocab("begin_bookings"); ?></td><td>
<?php
$bday = strftime("%d", getSettingValue("begin_bookings"));
$bmonth = strftime("%m", getSettingValue("begin_bookings"));
$byear = strftime("%Y", getSettingValue("begin_bookings"));
genDateSelector("begin_", $bday, $bmonth, $byear,"more_years") ?>
</td>
<td>&nbsp;</td>
</tr>
</table>
<?php echo "<p><i>".get_vocab("begin_bookings_explain")."</i>";

?>
<br /><br /></p>
<table border='0'>
<tr><td><?php echo get_vocab("end_bookings"); ?></td><td>
<?php
$eday = strftime("%d", getSettingValue("end_bookings"));
$emonth = strftime("%m", getSettingValue("end_bookings"));
$eyear= strftime("%Y", getSettingValue("end_bookings"));
genDateSelector("end_",$eday,$emonth,$eyear,"more_years") ?>
</td>
</tr>
</table>
<?php echo "<p><i>".get_vocab("end_bookings_explain")."</i></p>";
//
// Configuration de l'affichage par d�faut
//****************************************
//
?>
<hr />
<?php echo "<h3>".get_vocab("default_parameter_values_title")."</h3>\n";
echo "<p>".get_vocab("explain_default_parameter")."</p>";
//
// Choix du type d'affichage
//
echo "<h4>".get_vocab("explain_area_list_format")."</h4>";
echo "<table><tr><td>".get_vocab("liste_area_list_format")."</td><td>";
echo "<input type='radio' name='area_list_format' value='list' "; if (getSettingValue("area_list_format")=='list') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("select_area_list_format")."</td><td>";
echo "<input type='radio' name='area_list_format' value='select' "; if (getSettingValue("area_list_format")=='select') echo "checked=\"checked\""; echo " />";
echo "</td></tr></table>";

//
// Choix du domaine et de la ressource
// http://www.phpinfo.net/articles/article_listes.html
//
 if (getSettingValue("module_multisite") == "Oui")
   $use_site='y';
 else
   $use_site='n';

?>
 <script type="text/javascript">
 function modifier_liste_domaines(){
    new Ajax.Updater($('div_liste_domaines'),"my_account_modif_listes.php",{method: 'get', parameters: $('id_site').serialize(true)+'&'+'default_area=<?php echo getSettingValue("default_area"); ?>'+'&'+'session_login=<?php echo getUserName(); ?>'+'&'+'use_site=<?php echo $use_site; ?>'+'&'+'type=domaine'});
 }
 function modifier_liste_ressources(action){
     new Ajax.Updater($('div_liste_ressources'),"my_account_modif_listes.php",{method: 'get', parameters: $('id_area').serialize(true)+'&'+'default_room=<?php echo getSettingValue("default_room"); ?>'+'&'+'type=ressource'+'&'+'action='+action});
 }
 </script>
 <?php
if (getSettingValue("module_multisite") == "Oui")
  echo ('
      <h4>'.get_vocab('explain_default_area_and_room_and_site').'</h4>');
else
  echo ('
      <h4>'.get_vocab('explain_default_area_and_room').'</h4>');
/**
 * Liste des sites
 */
 if (getSettingValue("module_multisite") == "Oui")
 {
   $sql = "SELECT id,sitecode,sitename
           FROM ".TABLE_PREFIX."_site
           ORDER BY id ASC";
   $resultat = grr_sql_query($sql);
   echo('
      <table>
        <tr>
          <td>'.get_vocab('default_site').get_vocab('deux_points').'</td>
          <td>
            <select id="id_site" name="id_site" onchange="modifier_liste_domaines();modifier_liste_ressources(2)">
              <option value="-1">'.get_vocab('choose_a_site').'</option>'."\n");
  for ($enr = 0; ($row = grr_sql_row($resultat, $enr)); $enr++)
  {
      echo '              <option value="'.$row[0].'"';
      if (getSettingValue("default_site") == $row[0])
        echo ' selected="selected" ';
      echo '>'.htmlspecialchars($row[2]);
      echo '</option>'."\n";
  }
  echo('            </select>
          </td>
        </tr>');
} else {
 echo '<input type="hidden" id="id_site" name="id_site" value="-1" />
       <table>';
}


// ----------------------------------------------------------------------------
// Liste domaines
// ----------------------------------------------------------------------------
/**
  * Liste des domaines
 */
echo '<tr><td colspan="2">';
echo '<div id="div_liste_domaines">';
// Ici, on ins�re la liste des domaines avec de l'ajax !
echo '</div></td></tr>';

/**
 * Liste des ressources
 */
echo '<tr><td colspan="2">';
echo '<div id="div_liste_ressources">';
echo '<input type="hidden" id="id_area" name="id_area" value="'.getSettingValue("default_area").'" />';
// Ici, on ins�re la liste des ressouces avec de l'ajax !
echo '</div></td></tr></table>';

// Au chargement de la page, on remplit les listes de domaine et de ressources
echo '<script type="text/javascript">modifier_liste_domaines();</script>'."\n";
echo '<script type="text/javascript">modifier_liste_ressources(1);</script>'."\n";

//
// Choix de la feuille de style
//
echo "<h4>".get_vocab("explain_css")."</h4>";
echo "<table><tr><td>".get_vocab("choose_css")."</td><td>";
echo "<select name='default_css'>\n";
$i=0;
while ($i < count($liste_themes)) {
   echo "<option value='".$liste_themes[$i]."'";
   if (getSettingValue("default_css") == $liste_themes[$i]) echo " selected=\"selected\"";
   echo " >".encode_message_utf8($liste_name_themes[$i])."</option>";
   $i++;
}
echo "</select></td></tr></table>\n";

//
// Choix de la langue
//
echo "<h4>".get_vocab("choose_language")."</h4>";
echo "<table><tr><td>".get_vocab("choose_css")."</td><td>";
echo "<select name='default_language'>\n";
$i=0;
while ($i < count($liste_language)) {
   echo "<option value='".$liste_language[$i]."'";
   if (getSettingValue("default_language") == $liste_language[$i]) echo " selected=\"selected\"";
   echo " >".encode_message_utf8($liste_name_language[$i])."</option>\n";
   $i++;
}
echo "</select></td></tr></table>\n";

#
# Affichage du contenu des "info-bulles" des r�servations, dans les vues journ�es, semaine et mois.
# display_info_bulle = 0 : pas d'info-bulle.
# display_info_bulle = 1 : affichage des noms et pr�noms du b�n�ficiaire de la r�servation.
# display_info_bulle = 2 : affichage de la description compl�te de la r�servation.
echo "<hr /><h3>".get_vocab("display_info_bulle_msg")."</h3>\n";
echo "<table>";
echo "<tr><td>".get_vocab("info-bulle0")."</td><td>";
echo "<input type='radio' name='display_info_bulle' value='0' "; if (getSettingValue("display_info_bulle")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("info-bulle1")."</td><td>";
echo "<input type='radio' name='display_info_bulle' value='1' "; if (getSettingValue("display_info_bulle")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("info-bulle2")."</td><td>";
echo "<input type='radio' name='display_info_bulle' value='2' "; if (getSettingValue("display_info_bulle")=='2') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";

# Afficher la description compl�te de la r�servation dans les vues semaine et mois.
# display_full_description=1 : la description compl�te s'affiche.
# display_full_description=0 : la description compl�te ne s'affiche pas.
echo "<hr /><h3>".get_vocab("display_full_description_msg")."</h3>\n";
echo "<table>";
echo "<tr><td>".get_vocab("display_full_description0")."</td><td>";
echo "<input type='radio' name='display_full_description' value='0' "; if (getSettingValue("display_full_description")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("display_full_description1")."</td><td>";
echo "<input type='radio' name='display_full_description' value='1' "; if (getSettingValue("display_full_description")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";

# Afficher la description courte de la r�servation dans les vues semaine et mois.
# display_short_description=1 : la description  s'affiche.
# display_short_description=0 : la description  ne s'affiche pas.
echo "<hr /><h3>".get_vocab("display_short_description_msg")."</h3>\n";
echo "<table>";
echo "<tr><td>".get_vocab("display_short_description0")."</td><td>";
echo "<input type='radio' name='display_short_description' value='0' "; if (getSettingValue("display_short_description")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("display_short_description1")."</td><td>";
echo "<input type='radio' name='display_short_description' value='1' "; if (getSettingValue("display_short_description")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";

###########################################################
# Affichage des  adresses email dans la fiche de r�servation
###########################################################
# Qui peut voir les adresse email ?
# display_level_email  = 0 : N'importe qui allant sur le site, meme s'il n'est pas connect�
# display_level_email  = 1 : Il faut obligatoirement se connecter, m�me en simple visiteur.
# display_level_email  = 2 : Il faut obligatoirement se connecter et avoir le statut "utilisateur"
# display_level_email  = 3 : Il faut obligatoirement se connecter et �tre au moins gestionnaire d'une ressource
# display_level_email  = 4 : Il faut obligatoirement se connecter et �tre au moins administrateur du domaine
# display_level_email  = 5 : Il faut obligatoirement se connecter et �tre administrateur de site
# display_level_email  = 6 : Il faut obligatoirement se connecter et �tre administrateur g�n�ral
echo "<hr /><h3>".get_vocab("display_level_email_msg1")."</h3>\n";
echo "<p>".get_vocab("display_level_email_msg2")."</p>";
echo "<table cellspacing=\"5\">";
echo "<tr><td>".get_vocab("visu_fiche_description0")."</td><td>";
echo "<input type='radio' name='display_level_email' value='0' "; if (getSettingValue("display_level_email")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("visu_fiche_description1")."</td><td>";
echo "<input type='radio' name='display_level_email' value='1' "; if (getSettingValue("display_level_email")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("visu_fiche_description2")."</td><td>";
echo "<input type='radio' name='display_level_email' value='2' "; if (getSettingValue("display_level_email")=='2') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("visu_fiche_description3")."</td><td>";
echo "<input type='radio' name='display_level_email' value='3' "; if (getSettingValue("display_level_email")=='3') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("visu_fiche_description4")."</td><td>";
echo "<input type='radio' name='display_level_email' value='4' "; if (getSettingValue("display_level_email")=='4') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
if (getSettingValue("module_multisite") == "Oui") {
  echo "<tr><td>".get_vocab("visu_fiche_description5")."</td><td>";
  echo "<input type='radio' name='display_level_email' value='5' "; if (getSettingValue("display_level_email")=='5') echo "checked=\"checked\""; echo " />";
  echo "</td></tr>";
}
echo "<tr><td>".get_vocab("visu_fiche_description6")."</td><td>";
echo "<input type='radio' name='display_level_email' value='6' "; if (getSettingValue("display_level_email")=='6') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";



# Remplissage de la description courte
echo "<hr /><h3>".get_vocab("remplissage_description_breve_msg")."</h3>\n";
echo "<table>";
echo "<tr><td>".get_vocab("remplissage_description_breve0")."</td><td>";
echo "<input type='radio' name='remplissage_description_breve' value='0' "; if (getSettingValue("remplissage_description_breve")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("remplissage_description_breve1")."</td><td>";
echo "<input type='radio' name='remplissage_description_breve' value='1' "; if (getSettingValue("remplissage_description_breve")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("remplissage_description_breve2")."</td><td>";
echo "<input type='radio' name='remplissage_description_breve' value='2' "; if (getSettingValue("remplissage_description_breve")=='2') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";

# Ouvrir les pages au format imprimable dans une nouvelle fen�tre du navigateur (0 pour non et 1 pour oui)
echo "<hr /><h3>".get_vocab("pview_new_windows_msg")."</h3>\n";
echo "<table>";
echo "<tr><td>".get_vocab("pview_new_windows0")."</td><td>";
echo "<input type='radio' name='pview_new_windows' value='0' "; if (getSettingValue("pview_new_windows")=='0') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "<tr><td>".get_vocab("pview_new_windows1")."</td><td>";
echo "<input type='radio' name='pview_new_windows' value='1' "; if (getSettingValue("pview_new_windows")=='1') echo "checked=\"checked\""; echo " />";
echo "</td></tr>";
echo "</table>";

# Gestion du lien aide
echo "<hr /><h3>".get_vocab("Gestion lien aide bandeau superieur")."</h3>\n";
echo "<table>\n";
echo "<tr><td>".get_vocab("lien aide pointe vers documentation officielle site GRR")."</td><td>\n";
echo "<input type='radio' name='gestion_lien_aide' value='ext' "; if (getSettingValue("gestion_lien_aide")=='ext') echo "checked=\"checked\""; echo " />";
echo "</td></tr>\n";
echo "<tr><td>".get_vocab("lien aide pointe vers adresse perso").get_vocab("deux_points")."</td><td>\n";
echo "<input type='radio' name='gestion_lien_aide' value='perso' "; if (getSettingValue("gestion_lien_aide")=='perso') echo "checked=\"checked\""; echo " />\n";
echo "<input type=\"text\" name=\"lien_aide\" value=\"".getSettingValue("lien_aide")."\" size=\"40\" />\n";
echo "</td></tr>\n";
echo "</table>\n";


# Lors de l'�dition d'un rapport, valeur par d�faut en nombre de jours
# de l'intervalle de temps entre la date de d�but du rapport et la date de fin du rapport.
echo "<hr /><h3>".get_vocab("default_report_days_msg")."</h3>\n";
echo "<p>".get_vocab("default_report_days_explain").get_vocab("deux_points")."\n<input type=\"text\" name=\"default_report_days\" value=\"".getSettingValue("default_report_days")."\" size=\"5\" />\n";

# Formulaire de r�servation
echo "</p><hr /><h3>".get_vocab("formulaire_reservation")."</h3>\n";
echo "<p>".get_vocab("longueur_liste_ressources").get_vocab("deux_points")."<input type=\"text\" name=\"longueur_liste_ressources_max\" value=\"".getSettingValue("longueur_liste_ressources_max")."\" size=\"5\" />";

/*
# nb_year_calendar permet de fixer la plage de choix de l'ann�e dans le choix des dates de d�but et fin des r�servations
# La plage s'�tend de ann�e_en_cours - $nb_year_calendar � ann�e_en_cours + $nb_year_calendar
# Par exemple, si on fixe $nb_year_calendar = 5 et que l'on est en 2005, la plage de choix de l'ann�e s'�tendra de 2000 � 2010
echo "<hr /><h3>".get_vocab("nb_year_calendar_msg")."</h3>\n";
echo get_vocab("nb_year_calendar_explain").get_vocab("deux_points");
echo "<select name=\"nb_year_calendar\" size=\"1\">\n";
$i = 1;
while ($i < 101) {
    echo "<option value=\".$i.\"";
    if (getSettingValue("nb_year_calendar") == $i) echo " selected=\"selected\" ";
    echo ">".(date("Y") - $i)." - ".(date("Y") + $i)."</option>\n";
    $i++;
}
echo "</select>\n";
*/

echo "<br /><br /></p><div id=\"fixe\" style=\"text-align:center;\"><input type=\"submit\" name=\"ok\" value=\"".get_vocab("save")."\" style=\"font-variant: small-caps;\"/></div>";
echo "</form>";
?>
<script type="text/javascript">
document.getElementById('title_home_page').focus();
</script>
<?php

// fin de l'affichage de la colonne de droite
echo "</td></tr></table>";
?>