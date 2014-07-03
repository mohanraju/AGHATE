<?php
#########################################################################
#                    admin_config_sso.php                               #
#                                                                       #
#               interface permettant l'activation                       #
#                  de la prise en compte d'un environnement SSO         #
#               Dernière modification : 11/04/2008                      #
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
$grr_script_name = "admin_config_sso.php";

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

if (isset($_POST['valid'])) {
    VerifyModeDemo();
    if ($_POST['sso_statut'] == "no_sso") {
        $req = grr_sql_query("delete from agt_config where NAME = 'sso_statut'");
        $grrSettings['sso_statut'] = '';
    } else {
        if (!saveSetting("sso_statut", $_POST['sso_statut'])) {
            echo "Erreur lors de l'enregistrement de sso_statut !<br />";
        }
        $grrSettings['sso_statut'] = $_POST['sso_statut'];

    }
    if (!saveSetting("lcs_statut_prof", $_POST['lcs_statut_prof'])) {
        echo "Erreur lors de l'enregistrement de lcs_statut_prof !<br />";
    }
    $grrSettings['lcs_statut_prof'] = $_POST['lcs_statut_prof'];

    if (!saveSetting("lcs_statut_eleve", $_POST['lcs_statut_eleve'])) {
        echo "Erreur lors de l'enregistrement de lcs_statut_eleve !<br />";
    }
    $grrSettings['lcs_statut_eleve'] = $_POST['lcs_statut_eleve'];
    if (!saveSetting("lcs_liste_groupes_autorises", $_POST['lcs_liste_groupes_autorises'])) {
        echo "Erreur lors de l'enregistrement de lcs_liste_groupes_autorises !<br />";
    }
    $grrSettings['lcs_liste_groupes_autorises'] = $_POST['lcs_liste_groupes_autorises'];

}

if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if ((authGetUserLevel(getUserName(),-1) < 5) and ($valid != 'yes'))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";

?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php
echo "<FORM action=\"admin_config_sso.php\" method=\"post\">\n";

echo "<h2 align=\"center\">".get_vocab("admin_config_sso.php")."</h2>\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"no_sso\" ";
if (getSettingValue("sso_statut")=='') echo " checked ";
echo "/>".get_vocab("Ne_pas_activer_Service_sso")."<br />\n";


echo "<hr />\n";
// Configuration cas
echo "<h2 align=\"center\">".get_vocab("config_cas_title").grr_help("aide_grr_cas_sso")."</h2>\n";
echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"1\" />\n";
echo "<p>".get_vocab("CAS_SSO_explain")."</p>\n";
echo "<H3>".get_vocab("Statut_par_defaut_utilisateurs_importes")."</H3>\n";
echo get_vocab("choix_statut_CAS_SSO")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"cas_visiteur\" ";
if (getSettingValue("sso_statut")=='cas_visiteur') echo " checked ";
echo "/>".get_vocab("statut_visitor")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"cas_utilisateur\" ";
if (getSettingValue("sso_statut")=='cas_utilisateur') echo " checked ";
echo "/>".get_vocab("statut_user")."<br />\n";

echo "<hr />\n";
// Configuration lemonldap
echo "<h2 align=\"center\">".get_vocab("config_lemon_title").grr_help("aide_grr_lemon")."</h2>\n";
echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"1\" />\n";
echo "<p>".get_vocab("lemon_SSO_explain")."</p>\n";
echo "<H3>".get_vocab("Statut_par_defaut_utilisateurs_importes")."</H3>\n";
echo get_vocab("choix_statut_lemon_SSO")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"lemon_visiteur\" ";
if (getSettingValue("sso_statut")=='lemon_visiteur') echo " checked ";
echo "/>".get_vocab("statut_visitor")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"lemon_utilisateur\" ";
if (getSettingValue("sso_statut")=='lemon_utilisateur') echo " checked ";
echo "/>".get_vocab("statut_user")."<br />\n";

echo "<hr />\n";
// Configuration lcs
echo "<h2 align=\"center\">".get_vocab("config_lcs_title").grr_help("aide_grr_lcs")."</h2>\n";
echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"1\" />\n";
echo "<p>".get_vocab("lcs_SSO_explain")."</p>\n";
echo "<H3>".get_vocab("Statut_par_defaut_utilisateurs_importes")."</H3>\n";
echo get_vocab("choix_statut_lcs_SSO")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"lcs\" ";
if (getSettingValue("sso_statut")=='lcs') echo " checked ";
echo "/>".get_vocab("active_lcs")."<br />\n";

echo "<table>\n";
echo "<tr><td>".get_vocab("statut_eleve").get_vocab("deux_points")."</td>\n";
echo "<td><select name=\"lcs_statut_eleve\" size=\"1\">\n";
echo "<option value=\"aucun\"";
if (getSettingValue("lcs_statut_eleve")=='aucun') echo " selected ";
echo ">(ne pas importer)</option>\n";
echo "<option value=\"utilisateur\"";
if (getSettingValue("lcs_statut_eleve")=='utilisateur') echo " selected ";
echo ">usager</option>\n";
echo "<option value=\"visiteur\"";
if (getSettingValue("lcs_statut_eleve")=='visiteur') echo " selected ";
echo ">visiteur</option>\n";
echo "</select></td></tr>\n";

echo "<tr><td>".get_vocab("statut_non_eleve").get_vocab("deux_points")."</td>\n";
echo "<td><select name=\"lcs_statut_prof\" size=\"1\">\n";
echo "<option value=\"aucun\"";
if (getSettingValue("lcs_statut_prof")=='aucun') echo " selected ";
echo ">(ne pas importer)</option>\n";
echo "<option value=\"utilisateur\"";
if (getSettingValue("lcs_statut_prof")=='utilisateur') echo " selected ";
echo ">usager</option>\n";
echo "<option value=\"visiteur\"";
if (getSettingValue("lcs_statut_prof")=='visiteur') echo " selected ";
echo ">visiteur</option>\n";
echo "</select></td></tr>\n";
echo "</table>";
echo get_vocab("lcs_SSO_explain_2");
echo "<br /><br />";
echo get_vocab("lcs_SSO_explain_3");
echo "<br /><input type=\"text\" name=\"lcs_liste_groupes_autorises\" value=\"".htmlentities( getSettingValue("lcs_liste_groupes_autorises"))."\" size=\"50\" />";

echo "<hr />\n";
// Configuration Lasso
echo "<h2 align=\"center\">".get_vocab("config_lasso_title").grr_help("aide_grr_lasso_sso")."</h2>\n";
echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"1\" />\n";
echo "<p>".get_vocab("lasso_SSO_explain")."</p>\n";
echo "<H3>".get_vocab("Statut_par_defaut_utilisateurs_importes")."</H3>\n";
echo get_vocab("choix_statut_lasso_SSO")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"lasso_visiteur\" ";
if (getSettingValue("sso_statut")=='lasso_visiteur') echo " checked ";
echo "/>".get_vocab("statut_visitor")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"lasso_utilisateur\" ";
if (getSettingValue("sso_statut")=='lasso_utilisateur') echo " checked ";
echo "/>".get_vocab("statut_user")."<br />\n";

echo "<hr />\n";
// Configuration apache
echo "<h2 align=\"center\">".get_vocab("config_http_title").grr_help("aide_grr_authentification_http")."</h2>\n";
echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"1\" />\n";
echo "<p>".get_vocab("http_SSO_explain")."</p>\n";
echo "<H3>".get_vocab("Statut_par_defaut_utilisateurs_importes")."</H3>\n";
echo get_vocab("choix_statut_http_SSO")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"http_visiteur\" ";
if (getSettingValue("sso_statut")=='http_visiteur') echo " checked ";
echo "/>".get_vocab("statut_visitor")."<br />\n";
echo "<INPUT TYPE=\"radio\" name=\"sso_statut\" value=\"http_utilisateur\" ";
if (getSettingValue("sso_statut")=='http_utilisateur') echo " checked ";
echo "/>".get_vocab("statut_user")."<br />\n";

echo "<hr />\n";

echo "<center><INPUT type=\"submit\" name=\"Valider\"value=\"".get_vocab("save")."\" />\n</center>\n";
echo "</FORM>\n";

// fin de l'affichage de la colonne de droite
echo "</td></tr></table>\n";

?>
</body>
</html>



