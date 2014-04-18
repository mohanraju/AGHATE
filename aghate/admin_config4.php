<?php
/**
 * admin_config4.php
 * Interface permettant � l'administrateur
 * la configuration de certains param�tres g�n�raux
 * Ce script fait partie de l'application GRR
 * Derni�re modification : $Date: 2010-04-07 15:38:14 $
 * @author    Laurent Delineau <laurent.delineau@ac-poitiers.fr>
 * @copyright Copyright 2003-2008 Laurent Delineau
 * @link      http://www.gnu.org/licenses/licenses.html
 * @package   root
 * @version   $Id: admin_config4.php,v 1.8 2010-04-07 15:38:14 grr Exp $
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
 * $Log: admin_config4.php,v $
 * Revision 1.8  2010-04-07 15:38:14  grr
 * *** empty log message ***
 *
 * Revision 1.7  2009-10-09 07:55:48  grr
 * *** empty log message ***
 *
 * Revision 1.6  2009-09-29 18:02:56  grr
 * *** empty log message ***
 *
 * Revision 1.5  2009-02-27 13:28:19  grr
 * *** empty log message ***
 *
 * Revision 1.4  2008-11-16 22:00:58  grr
 * *** empty log message ***
 *
 *
 */

if (isset($_GET['motdepasse_backup'])) {
    if (!saveSetting("motdepasse_backup", $_GET['motdepasse_backup'])) {
        echo "Erreur lors de l'enregistrement de motdepasse_backup !<br />";
        die();
    }
}


if (isset($_GET['disable_login'])) {
    if (!saveSetting("disable_login", $_GET['disable_login'])) {
        echo "Erreur lors de l'enregistrement de disable_login !<br />";
        die();
    }
}
if (isset($_GET['url_disconnect'])) {
    if (!saveSetting("url_disconnect", $_GET['url_disconnect'])) {
        echo "Erreur lors de l'enregistrement de url_disconnect ! <br />";
    }
}
// Max session length
if (isset($_GET['sessionMaxLength'])) {
    settype($_GET['sessionMaxLength'],"integer");
    if ($_GET['sessionMaxLength'] < 1) $_GET['sessionMaxLength'] =30;
    if (!saveSetting("sessionMaxLength", $_GET['sessionMaxLength'])) {
        echo "Erreur lors de l'enregistrement de sessionMaxLength !<br />";
    }
}
// pass_leng
if (isset($_GET['pass_leng'])) {
    settype($_GET['pass_leng'],"integer");
    if ($_GET['pass_leng'] < 1) $_GET['pass_leng'] = 1;
    if (!saveSetting("pass_leng", $_GET['pass_leng'])) {
        echo "Erreur lors de l'enregistrement de pass_leng !<br />";
    }
}

if (!loadSettings())
    die("Erreur chargement settings");

# print the page header
print_header("","","","",$type="with_session", $page="admin");
if (isset($_GET['ok'])) {
    $msg = get_vocab("message_records");
	affiche_pop_up($msg,"admin");
}

// Affichage de la colonne de gauche
include "admin_col_gauche.php";

// Affichage du tableau de choix des sous-configuration
include "./commun/include/admin_config_tableau.inc.php";

//echo "<h2>".get_vocab('admin_config4.php')."</h2>";


//
// dans le cas de mysql, on propose une sauvegarde et l'ouverture de la base
//
if ($dbsys == "mysql") {
    //
    // Saving base
    //********************************
    //
    echo "<h3>".get_vocab('title_backup')."</h3>\n";
    echo "<p>".get_vocab("explain_backup")."</p>\n";
    echo "<p><i>".get_vocab("warning_message_backup")."</i></p>\n";
    ?>

    <form action="admin_save_mysql.php" method="get" style="width:100%;">
    <div style="text-align:center;">
    <input type="hidden" name="flag_connect" value="yes" />
    <input type="submit" value=" <?php echo get_vocab("submit_backup"); ?>" style="font-variant: small-caps;" />
    </div></form>
    <?php

    //
    // Loading base
    //********************************
    //
    echo "\n<hr /><h3>".get_vocab('Restauration de la base GRR')."</h3>";
    echo "\n<p>En cas de perte de donn�e ou de probl�me sur la base GRR, cette fonction vous permet de la retrouver dans l'�tat ant�rieur lors d'une sauvegarde. Vous devez s�lectionner un fichier cr�� � l'aide de la fonction Lancer une sauvegarde.</p>";
    echo "\n<p><span class=\"avertissement\"><i>Attention! Restaurer la base vous fera perdre toutes les donn�es qu'elle contient actuellement. De plus, tous les utilisateurs pr�sentement connect�s, ainsi que vous-m�mes, serez d�connect�s. Alors, il est conseill� de cr�er d'abord une sauvegarde et de vous assurer que vous �tes le seul connect�.</i></span></p>\n";
    ?>
    <form method="post" enctype="multipart/form-data" action="admin_open_mysql.php">
    <div style="text-align:center;">
    <input type="file" name="sql_file" size="30" /><br /><br />
    <input type="submit" value="<?php echo get_vocab('Restaurer la sauvegarde'); ?>" style="font-variant: small-caps;" />
    </div>
    </form>
    <?php
}

echo "<form action=\"./admin_config.php\" method=\"get\" style=\"width: 100%;\">";

# Backup automatique
echo "\n<hr /><h3>".get_vocab("execution automatique backup")."</h3>";

echo "<p>".get_vocab("execution automatique backup explications")."</p>";
echo "\n<p>".get_vocab("execution automatique backup mdp").get_vocab("deux_points");
echo "\n<input type=\"password\" name=\"motdepasse_backup\" value=\"".getSettingValue("motdepasse_backup")."\" size=\"20\" /></p>";




//
// Suspendre les connexions
//*************************
//
echo "\n<hr /><h3>".get_vocab('title_disable_login')."</h3>";
echo "\n<p>".get_vocab("explain_disable_login");
?>
<br /><input type='radio' name='disable_login' value='yes' id='label_1' <?php if (getSettingValue("disable_login")=='yes') echo "checked=\"checked\"";?> /> <label for='label_1'><?php echo get_vocab("disable_login_on");

?></label>
<br /><input type='radio' name='disable_login' value='no' id='label_2' <?php if (getSettingValue("disable_login")=='no') echo "checked=\"checked\"";?> /> <label for='label_2'><?php echo get_vocab("disable_login_off"); ?></label></p>
<?php
echo "\n<hr />";

//
// Dur�e d'une session
//********************
//
echo "<h3>".get_vocab("title_session_max_length")."</h3>";
?>
<table border='0'>
<tr><td><?php echo get_vocab("session_max_length"); ?></td><td>
<input type="text" name="sessionMaxLength" size="16" value="<?php echo(getSettingValue("sessionMaxLength")); ?>" /></td>
</tr>
</table>
<?php echo "<p>".get_vocab("explain_session_max_length")."</p>";


//Longueur minimale du mot de passe exig�
echo "<hr /><h3>".get_vocab("pwd")."</h3>";
echo "\n<p>".get_vocab("pass_leng_explain").get_vocab("deux_points")."
<input type=\"text\" name=\"pass_leng\" value=\"".htmlentities(getSettingValue("pass_leng"))."\" size=\"20\" /></p>";

//
// Url de d�connexion
//*******************
//
echo "<hr /><h3>".get_vocab("Url_de_deconnexion")."</h3>\n";
echo "<p>".get_vocab("Url_de_deconnexion_explain")."</p>\n";
echo "<p><i>".get_vocab("Url_de_deconnexion_explain2")."</i>";
echo "<br />".get_vocab("Url_de_deconnexion").get_vocab("deux_points")."\n";
$value_url=getSettingValue("url_disconnect");
echo "<input type=\"text\" name=\"url_disconnect\" size=\"40\" value =\"$value_url\"/>\n<br /><br /></p>";
echo "\n<hr />";



echo "\n<p><input type=\"hidden\" name=\"page_config\" value=\"4\" />";
echo "\n<br /></p><div id=\"fixe\" style=\"text-align:center;\"><input type=\"submit\" name=\"ok\" value=\"".get_vocab("save")."\" style=\"font-variant: small-caps;\"/></div>";
echo "\n</form>";

// fin de l'affichage de la colonne de droite
echo "\n</td></tr></table>";
?>