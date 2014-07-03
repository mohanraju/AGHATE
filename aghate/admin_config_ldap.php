<?php
//error_reporting (E_ALL);
#########################################################################
#                    admin_config_ldap.php                              #
#                                                                       #
#            interface permettant la configuration de l'accès           #
#                     à un annuaire LDAP                                #
#               Dernière modification : 10/07/2006                      #
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

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
$grr_script_name = "admin_config_ldap.php";
// Settings
require_once("./commun/include/settings.inc.php");

// Session related functions
require_once("./commun/include/session.inc.php");

// Paramètres langage
include "./commun/include/language.inc.php";

//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");


$valid = isset($_POST["valid"]) ? $_POST["valid"] : 'no';
$etape = isset($_POST["etape"]) ? $_POST["etape"] : '0';
$adresse = isset($_POST["adresse"]) ? $_POST["adresse"] : NULL;
$port = isset($_POST["port"]) ? $_POST["port"] : NULL;
$login_ldap = isset($_POST["login_ldap"]) ? $_POST["login_ldap"] : NULL;
$pwd_ldap = isset($_POST["pwd_ldap"]) ? $_POST["pwd_ldap"] : NULL;
if (isset($_POST["use_tls"])) {
    if ($_POST["use_tls"]=='y') $use_tls = TRUE; else $use_tls = FALSE;
} else $use_tls = FALSE;
$base_ldap = isset($_POST["base_ldap"]) ? $_POST["base_ldap"] : NULL;
$base_ldap_autre = isset($_POST["base_ldap_autre"]) ? $_POST["base_ldap_autre"] : NULL;
$ldap_filter = isset($_POST["ldap_filter"]) ? $_POST["ldap_filter"] : NULL;
$titre_ldap = "Configuration de l'authentification LDAP";


if (isset($_POST['reg_ldap_statut'])) {
    if ($_POST['ldap_statut'] == "no_ldap") {
        $req = grr_sql_query("delete from agt_config where NAME = 'ldap_statut'");
        $grrSettings['ldap_statut'] = '';
    } else {
        if (!saveSetting("ldap_statut", $_POST['ldap_statut'])) {
            echo encode_message_utf8("Erreur lors de l'enregistrement de ldap_statut !<br />");
        }
        $grrSettings['ldap_statut'] = $_POST['ldap_statut'];
    }
    if (isset($_POST['Valider1'])) {
      if (!isset($_POST['ConvertLdapUtf8toIso'])) $ConvertLdapUtf8toIso = "n"; else $ConvertLdapUtf8toIso = "y";
      if (!saveSetting("ConvertLdapUtf8toIso", $ConvertLdapUtf8toIso))
          echo "Erreur lors de l'enregistrement de ConvertLdapUtf8toIso !<br />";
      $grrSettings['ConvertLdapUtf8toIso'] = $ConvertLdapUtf8toIso;

      if (!isset($_POST['ActiveModeDiagnostic'])) $ActiveModeDiagnostic = "n"; else $ActiveModeDiagnostic = "y";
      if (!saveSetting("ActiveModeDiagnostic", $ActiveModeDiagnostic))
          echo "Erreur lors de l'enregistrement de ActiveModeDiagnostic !<br />";
      $grrSettings['ActiveModeDiagnostic'] = $ActiveModeDiagnostic;


      if (!saveSetting("ldap_champ_recherche", $_POST['ldap_champ_recherche'])) {
          echo "Erreur lors de l'enregistrement de ldap_champ_recherche !<br />";
      }
      $grrSettings['ldap_champ_recherche'] = $_POST['ldap_champ_recherche'];

      if ($_POST['ldap_champ_nom']=='') $_POST['ldap_champ_nom'] = "sn";
      if (!saveSetting("ldap_champ_nom", $_POST['ldap_champ_nom'])) {
          echo "Erreur lors de l'enregistrement de ldap_champ_nom !<br />";
      }
      $grrSettings['ldap_champ_nom'] = $_POST['ldap_champ_nom'];

      if ($_POST['ldap_champ_prenom']=='') $_POST['ldap_champ_prenom'] = "sn";
      if (!saveSetting("ldap_champ_prenom", $_POST['ldap_champ_prenom'])) {
          echo "Erreur lors de l'enregistrement de ldap_champ_prenom !<br />";
      }
      $grrSettings['ldap_champ_prenom'] = $_POST['ldap_champ_prenom'];

      if ($_POST['ldap_champ_email']=='') $_POST['ldap_champ_email'] = "sn";
      if (!saveSetting("ldap_champ_email", $_POST['ldap_champ_email'])) {
          echo "Erreur lors de l'enregistrement de ldap_champ_email !<br />";
      }
      $grrSettings['ldap_champ_email'] = $_POST['ldap_champ_email'];



      if (!saveSetting("se3_liste_groupes_autorises", $_POST['se3_liste_groupes_autorises'])) {
          echo "Erreur lors de l'enregistrement de se3_liste_groupes_autorises !<br />";
      }
      $grrSettings['se3_liste_groupes_autorises'] = $_POST['se3_liste_groupes_autorises'];
    }

}

//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

if (isset($_POST['submit'])) {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $sql = "select upper(login) login, password, prenom, nom, statut from agt_utilisateurs where login = '" . $_POST['login'] . "' and password = md5('" . $_POST['password'] . "') and etat != 'inactif' and statut='administrateur' ";
        $res_user = grr_sql_query($sql);
        $num_row = grr_sql_count($res_user);
        if ($num_row == 1) {
            $valid='yes';
        } else {
            $message = get_vocab("wrong_pwd");
        }
    }
}


if ((!grr_resumeSession()) and $valid!='yes') {
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <HTML>
    <HEAD>
    <link REL="stylesheet" href="style.css" type="text/css">
    <TITLE> GRR </TITLE>
    <LINK REL="SHORTCUT ICON" href="./commun/images/favicon.ico">
    </HEAD>
    <BODY>
    <form action="admin_config_ldap.php" method='post' style="width: 100%; margin-top: 24px; margin-bottom: 48px;">
    <div class="center">
    <H2>Configuration de l'accès à LDAP</H2>

    <?php
    if (isset($message)) {
        echo("<p><font color=red>" . $message . "</font></p>");
    }
    ?>
    <fieldset style="padding-top: 8px; padding-bottom: 8px; width: 40%; margin-left: auto; margin-right: auto;">
    <legend style="font-variant: small-caps;"><?php echo get_vocab("identification"); ?></legend>
    <table style="width: 100%; border: 0;" cellpadding="5" cellspacing="0">
    <tr>
    <td style="text-align: right; width: 40%; font-variant: small-caps;"><label for="login"><?php echo get_vocab("login"); ?></label></td>
    <td style="text-align: center; width: 60%;"><input type="text" name="login" size="16" /></td>
    </tr>
    <tr>
    <td style="text-align: right; width: 40%; font-variant: small-caps;"><label for="password"><?php echo get_vocab("pwd"); ?></label></td>
    <td style="text-align: center; width: 60%;"><input type="password" name="password" size="16" /></td>
    </tr>
    </table>
    <input type="submit" name="submit" value="<?php echo get_vocab("OK"); ?>" style="font-variant: small-caps;" />
    </fieldset>
    </div>
    </form>
    </body>
    </html>
    <?php
    die();
};

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if ((authGetUserLevel(getUserName(),-1) < 5) and ($valid != 'yes'))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
if ($valid == 'no') {
    # print the page header
    print_header("","","","",$type="with_session", $page="admin");
    // Affichage de la colonne de gauche
    include "admin_col_gauche.php";
} else {
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <HTML>
    <HEAD>
    <link REL="stylesheet" href="style.css" type="text/css">
    <LINK REL="SHORTCUT ICON" href="./commun/images/favicon.ico">
    <TITLE> GRR </TITLE>
    </HEAD>
    <BODY>
    <?php
}

?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<?php

if ($etape == 3) {
    echo "<h2 align=\"center\">".$titre_ldap.grr_help("aide_grr_configuration_LDAP")."</h2>";
    echo "<h2 align=\"center\">".encode_message_utf8("Enregistrement de la configuration.")."</h2>";
    if (!$base_ldap) $base_ldap = $base_ldap_autre;
    $ds = grr_connect_ldap($adresse,$port,$login_ldap,$pwd_ldap,$use_tls);
    // On verifie le chemin fourni

    $result = grr_ldap_search_user($ds, $base_ldap, "objectClass", "*",$ldap_filter,"y");
    if ($result == "error_1") {
        if ($ldap_filter == "") {
            echo encode_message_utf8("<b>Problème</b> : Le chemin que vous avez choisi <b>ne semble pas valide</b>.<br /><br />");
        } else {
            echo encode_message_utf8("<b>Problème</b> : Le chemin et/ou le filtre additionnel que vous avez choisi <b>ne semblent pas valides</b>.<br /><br />");
        }
    } else if ($result == "error_2") {
        if ($ldap_filter == "") {
            echo encode_message_utf8("<b>Problème</b> : Le chemin que vous avez choisi semble valide mais la recherche sur ce chemin ne renvoie aucun résultat.<br /><br />");
        } else {
            echo encode_message_utf8("<b>Problème</b> : Le chemin et le filtre additionnel que vous avez choisi semblent valides  mais la recherche sur ce chemin ne renvoie aucun résultat.<br /><br />");
        }
    }
    // Le cas "error_3" n'est pas analusé car on accepte les  cas où il y a plusieurs entrées dans l'annuaire à l'issus de la recherche

        $erreur = '';
        $nom_fic = "./config/config_ldap.inc.php";
        if (@file_exists($nom_fic)) {
            $f = @fopen($nom_fic, "r+");
            if (!$f) $erreur = "Le fichier \"".$nom_fic."\" n'est pas accessible en écriture.<br />Vous devez modifier les permissions sur ce fichier puis recharger cette page.";
        } else {
            $f = @fopen($nom_fic, "wb");
            if (!$f)  {
                $erreur = "Impossible de créer le fichier \"".$nom_fic."\".";
                if (@file_exists($nom_fic.".ori")) {
                    $erreur .= "<br />Vous pouvez renommer manuellement le fichier \"".$nom_fic.".ori\" en \"".$nom_fic."\", et lui donner les droits suffisants.";
                } else {
                    $erreur .= "<br />Vous devez modifier les droits sur le répertoire include.";
                }
            }
        }
        if ($erreur == '') {
            // On a ouvert un fichier config_ldap.inc.php
            $conn = "<"."?php\n";
            $conn .= "# Les quatre lignes suivantes sont à modifier selon votre configuration\n";
            $conn .= "# ligne suivante : l'adresse de l'annuaire LDAP.\n";
            $conn .= "# Si c'est le même que celui qui heberge les scripts, mettre \"localhost\"\n";
            $conn .= "\$ldap_adresse=\"$adresse\";\n";
            $conn .= "# ligne suivante : le port utilisé\n";
            $conn .= "\$ldap_port=\"$port\";\n";
            $conn .= "# ligne suivante : l'identifiant et le mot de passe dans le cas d'un accès non anonyme\n";
            $conn .= "\$ldap_login=\"$login_ldap\";\n";
            $conn .= "# Remarque : des problèmes liés à un mot de passe contenant un ou plusieurs caractères accentués ont déjà été constatés.\n";
            $conn .= "\$ldap_pwd=\"$pwd_ldap\";\n";
            $conn .= "# ligne suivante : le chemin d'accès dans l'annuaire\n";
            $conn .= "\$ldap_base=\"$base_ldap\";\n";
            $conn .= "# ligne suivante : filtre LDAP supplémentaire (facultatif)\n";
            $conn .= "\$ldap_filter=\"$ldap_filter\";\n";
            $conn .= "# ligne suivante : utiliser TLS\n";
            if ($use_tls)
                $conn .= "\$use_tls=TRUE;\n";
            else
                $conn .= "\$use_tls=FALSE;\n";
            $conn .= "# Attention : si vous configurez manuellement ce fichier (sans passer par la configuration en ligne)\n";
            $conn .= "# vous devez tout de même activer LDAP en choisissant le \"statut par défaut des utilisateurs importés\".\n";
            $conn .= "# Pour cela, rendez-vous sur la page : configuration -> Configuration LDAP.\n";
            $conn .= "?".">";
            @fputs($f, $conn);
            if (!@fclose($f)) $erreur="Impossible d'enregistrer le fichier \"".$nom_fic."\".";
        }
        if ($erreur == '') {
            echo encode_message_utf8("<B>Les données concernant l'accès à l'annuaire LDAP sont maintenant enregistrées dans le fichier \"".$nom_fic."\".</b>");
        } else {
            echo encode_message_utf8("<P>".$erreur."</p>");
        }
        if ($erreur == '') {
            echo "<FORM action=\"admin_config_ldap.php\" method=\"post\">";
            echo "<INPUT TYPE=\"hidden\" name=\"etape\" value=\"0\" />";
            echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"$valid\" />";
            echo "<center><INPUT type=\"submit\" name=\"Valider\" value=\"Terminer\" /></center>";
            echo "</FORM>";
        }

} else if ($etape == 2) {
    echo "<h2 align=\"center\">".$titre_ldap.grr_help("aide_grr_configuration_LDAP")."</h2>";
    echo "<h2 align=\"center\">".encode_message_utf8("Connexion à l'annuaire LDAP.")."</h2>";
    // Connexion à l'annuaire
    $ds = grr_connect_ldap($adresse,$port,$login_ldap,$pwd_ldap,$use_tls);
    if ($ds) {
        $connexion_ok = 'yes';
    } else {
        $connexion_ok = 'no';
    }
    if ($connexion_ok == 'yes') {
        echo encode_message_utf8("<b>La connexion LDAP a réussi.</b>");
        echo "<FORM action=\"admin_config_ldap.php\" method=\"post\">";
        // On lit toutes les infos (objectclass=*) dans le dossier
        // Retourne un identifiant de résultat ($result), ou bien FALSE en cas d'erreur.
        $result = ldap_read($ds, "", "objectclass=*", array("namingContexts"));
        $info = ldap_get_entries($ds, $result);
        // Retourne un tableau associatif multi-dimensionnel ou FALSE en cas d'erreur. :
        // $info["count"] = nombre d'entrées dans le résultat
        // $info[0] : sous-tableau renfermant les infos de la première entrée
        // $info[n]["dn"] : dn de la n-ième entrée du résultat
        // $info[n]["count"] : nombre d'attributs de la n-ième entrée
        // $info[n][m] : m-ième attribut de la n-ième entrée
        // info[n]["attribut"]["count"] : nombre de valeur de cet attribut pour la n-ième entrée
        // $info[n]["attribut"][m] : m-ième valeur de l'attribut pour la n-ième entrée
        $checked = false;
        if (is_array($info) AND $info["count"] > 0) {
            echo encode_message_utf8("<P>Sélectionnez ci-dessous le chemin d'accès dans l'annuaire :</p>");
            $n = 0;
            for ($i = 0; $i < $info["count"]; $i++) {
                $names[] = $info[$i]["dn"];
                if (is_array($names)) {
                    for ($j = 0; $j < count($names); $j++) {
                        $n++;
                        echo "<br /><INPUT NAME=\"base_ldap\" VALUE=\"".htmlspecialchars($names[$j])."\" TYPE='radio' id='tab$n'";
                        if (!$checked) {
                            echo " CHECKED";
                            $checked = true;
                        }
                        echo " />";
                        echo "<label for='tab$n'>".htmlspecialchars($names[$j])."</label>\n";
                    }
                }
            }
            echo "<br />Ou bien ";
          }
        echo "<br /><INPUT NAME=\"base_ldap\" VALUE=\"\" TYPE='radio' id=\"autre\"";
        if (!$checked) {
            echo " CHECKED";
            $checked = true;
        }
        echo " />";
        echo "<label for=\"autre\">".encode_message_utf8("Précisez le chemin : ")."</label> ";
        if (isset($_POST["ldap_base"])) $ldap_base = $_POST["ldap_base"]; else $ldap_base ="";
        if (isset($_POST["ldap_filter"])) $ldap_filter = $_POST["ldap_filter"]; else $ldap_filter ="";
        echo "<INPUT TYPE=\"text\" name=\"base_ldap_autre\" value=\"$ldap_base\" size=\"40\" />";


        echo "<br /><br />".encode_message_utf8("Filtre LDAP supplémentaire (facultatif) :");
        echo "<br /><input type=\"text\" name=\"ldap_filter\" value=\"$ldap_filter\" size=\"50\" />";
        echo "<br /><br />";
        echo encode_message_utf8("<b>Remarque : pour le moment, aucune modification n'a été apportée au fichier de configuration \"config_ldap.inc.php\".</b><br />
        Pour enregistrer les informations, cliquez sur le bouton \"Enregistrer les informations\".<br /><br />");

        echo "<INPUT TYPE=\"hidden\" name=\"etape\" value=\"3\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"adresse\" value=\"$adresse\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"port\" value=\"$port\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"login_ldap\" value=\"$login_ldap\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"pwd_ldap\" value=\"$pwd_ldap\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"$valid\" />\n";
        if ($use_tls)
            echo "<INPUT TYPE=\"hidden\" name=\"use_tls\" value=\"y\" />\n";
        echo "<center><INPUT type=\"submit\" name=\"Valider\" value=\"Enregistrer les informations\" /></center>\n";
        echo "</FORM>";
    } else {
        echo encode_message_utf8("<B>La connexion au serveur LDAP a échoué.</B><br />");
        echo encode_message_utf8("Revenez à la page précédente et vérifiez les informations fournies.");
        echo "<form name=\"etape2\" method=\"post\" action=admin_config_ldap.php>\n";
        echo "<input type=\"hidden\" name=\"etape\" value=\"1\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"ldap_adresse\" value=\"$adresse\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"ldap_port\" value=\"$port\" />\n";
        echo "<INPUT TYPE=\"hidden\" name=\"ldap_login\" value=\"$login_ldap\" />\n";
        if ($use_tls)
            echo "<INPUT TYPE=\"hidden\" name=\"use_tls\" value=\"y\" />\n";
        echo "<input type=\"submit\" name=\"valider\" value=\"".encode_message_utf8("Page précédente")."\" />\n";
        echo "</form>\n";
    }
} else if ($etape == 1) {
    if (isset($_POST["valider"])) {
        $ldap_adresse = $_POST["ldap_adresse"];
        $ldap_port = $_POST["ldap_port"];
        $ldap_login = $_POST["ldap_login"];
    } else if (@file_exists("/config/config_ldap.inc.php"))
         include "./config/config_ldap.inc.php";
    echo "<h2 align=\"center\">".$titre_ldap.grr_help("aide_grr_configuration_LDAP")."</h2>";
    echo "<h2 align=\"center\">".encode_message_utf8("Informations de connexion à l'annuaire LDAP.")."</h2>";
    echo "<form action=\"admin_config_ldap.php\" method=\"post\">";

    if ((!(isset($ldap_adresse))) or ($ldap_adresse == "")) $ldap_adresse = 'localhost';
    if ((!(isset($ldap_port))) or ($ldap_port == "")) $ldap_port = 389;
    if (!(isset($ldap_login))) $ldap_login = "";
    if (!(isset($ldap_pwd))) $ldap_pwd = "";


    echo "<INPUT TYPE=\"hidden\" NAME=\"etape\" VALUE=\"2\" />";
    echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"$valid\" />";
    echo encode_message_utf8("<H3>Adresse de l'annuaire</H3>
    Laissez «localhost» si l'annuaire est installé sur la même machine que GRR. Sinon, indiquez l'adresse du serveur.<br />");
    echo "<INPUT TYPE=\"text\" NAME=\"adresse\" VALUE=\"".$ldap_adresse."\" SIZE=\"20\" />";
    echo encode_message_utf8("<H3>Numéro de port de l'annuaire</H3>
    Dans le doute, laissez la valeur par défaut : 389<br />(3268 pour serveur de catalogues global AD)<br />");
    echo "<INPUT TYPE='text' NAME='port' VALUE=\"$ldap_port\" SIZE=\"20\" />";

    echo encode_message_utf8("<h3>Type d'accès</H3>Si le serveur LDAP n'accepte pas d'accès anonyme,
    veuillez préciser un identifiant (par exemple « cn=jean, o=lycée, c=fr »).
    Dans le doute, laissez les champs suivants vides pour un accès anonyme.<br /><b>Identifiant :</b><br />");
    echo "<INPUT TYPE=\"text\" NAME=\"login_ldap\" VALUE=\"".$ldap_login."\" SIZE=\"40\" /><br />";

    echo "<b>Mot de passe :</b><br />";
    echo encode_message_utf8("Remarque : des problèmes liés à un mot de passe contenant un ou plusieurs caractères accentués ont déjà été constatés.<br />");
    echo "<INPUT TYPE=\"password\" NAME=\"pwd_ldap\" VALUE=\"".$ldap_pwd."\" SIZE=\"40\" /><br />";
    echo "<H3>Utiliser TLS :</H3>";
    echo "<input type=\"radio\" name=\"use_tls\" value=\"y\" ";
    if ($use_tls) echo " checked ";
    echo "/> Oui\n";
    echo "<input type=\"radio\" name=\"use_tls\" value=\"n\" ";
    if (!($use_tls)) echo " checked ";
    echo "/> Non\n";
    if (isset($ldap_filter))
        echo "<INPUT TYPE=\"hidden\" name=\"ldap_filter\" value=\"$ldap_filter\" />";
    if (isset($ldap_base))
        echo "<INPUT TYPE=\"hidden\" name=\"ldap_base\" value=\"$ldap_base\" />";
    echo encode_message_utf8("<br /><br /><b>Remarque : pour le moment, aucune modification n'a été apportée au fichier de configuration \"config_ldap.inc.php\".</b><br />
    Les informations ne seront enregistrées qu'à la fin de la procédure de configuration.");


    echo "<center><input type=\"submit\" value=\"Suivant\" /></center>";
    echo "</form>";

} else if ($etape == 0) {
    if (!(function_exists("ldap_connect"))) {
        echo "<h2 align=\"center\">".$titre_ldap.grr_help("aide_grr_configuration_LDAP")."</h2>";
        echo encode_message_utf8("<p align=\"center\"><b>Attention </b> : les fonctions liées à l'authentification <b>LDAP</b> ne sont pas activées sur votre serveur PHP.
        <br />La configuration LDAP est donc actuellement impossible.</p></td></tr></table></body></html>");
        die();
    }
    echo "<h2 align=\"center\">".$titre_ldap.grr_help("aide_grr_configuration_LDAP")."</h2>";
    echo encode_message_utf8("Si vous avez accès à un annuaire <b>LDAP</b>, vous pouvez configurer GRR afin que cet annuaire soit utilisé pour importer automatiquement des utilisateurs.");
    echo "<FORM action=\"admin_config_ldap.php\" method=\"post\">";
    echo "<INPUT TYPE=\"hidden\" name=\"etape\" value=\"0\" />";
    echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"$valid\" />";
    echo "<INPUT TYPE=\"hidden\" name=\"reg_ldap_statut\" value=\"yes\" />";
    if (getSettingValue("ldap_statut") != '') {
        echo encode_message_utf8("<H3>L'authentification LDAP est activée.</H3>");
        echo encode_message_utf8("<H3>Statut par défaut des utilisateurs importés</H3>");
        echo encode_message_utf8("Choisissez le statut qui sera attribué aux personnes présentes
        dans l'annuaire LDAP lorsqu'elles se connectent pour la première fois.
        Vous pourrez par la suite modifier cette valeur pour chaque utilisateur.<br />");
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"visiteur\" ";
        if (getSettingValue("ldap_statut")=='visiteur') echo " checked ";
        echo "/>Visiteur<br />";
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"utilisateur\" ";
        if (getSettingValue("ldap_statut")=='utilisateur') echo " checked ";
        echo "/>Usager<br />";
        echo "Ou bien <br />";
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"no_ldap\" />".encode_message_utf8("Désactiver l'authentification LDAP")."<br />";
        echo "<br />";

        echo "<input type=\"checkbox\" name=\"ConvertLdapUtf8toIso\" value=\"y\" ";
        if (getSettingValue("ConvertLdapUtf8toIso")=="y") echo " checked";
        echo " />";
        echo encode_message_utf8("Les données (noms, prénom...) sont stockées en UTF-8 dans l'annuaire (configuration par défaut)");
        echo "<br />";
        echo "<input type=\"checkbox\" name=\"ActiveModeDiagnostic\" value=\"y\" ";
        if (getSettingValue("ActiveModeDiagnostic")=="y") echo " checked";
        echo " />";
        echo encode_message_utf8("Activer le mode \"diagnostic\" (en cas d'erreur de connexion, les messages renvoyés par GRR sont plus explicites. De cette façon, il peut être plus facile de déterminer la cause du problème.");
        echo "<br /><br />";
        if (getSettingValue("ldap_champ_recherche")=='') echo "<font color='red'>";
        echo encode_message_utf8("<b>Attribut utilisé pour la recherche dans l'annuaire</b> :");
        echo "<input type=\"text\" name=\"ldap_champ_recherche\" value=\"".htmlentities( getSettingValue("ldap_champ_recherche"))."\" size=\"50\" />";
        if (getSettingValue("ldap_champ_recherche")=='') echo "<br />Le champ ci-dessous ne doit pas être vide.</font>";
        echo "<br />";
        echo encode_message_utf8("La valeur à indiquer ci-dessus varie selon le type d'annuaire utilisé et selon sa configuration
        <br /><span class='small'>Exemples de champs généralement utilisés pour les annuaires ldap : \"uid\", \"cn\", \"sn\".
        <br />Exemples de champs généralement utilisés pour les Active Directory : \"samaccountname\", \"userprincipalname\".
        <br />Même si cela n'est pas conseillé, vous pouvez indiquer plusieurs attributs séparés par le caractère | (exemple : uid|sn|cn).</span>
        ");
        echo "<br /><br /><b>Liaisons GRR/LDAP</b>";
        echo "<table><tr>";
        echo "<td>Nom de famille : </td>";
        echo "<td><input type=\"text\" name=\"ldap_champ_nom\" value=\"".htmlentities( getSettingValue("ldap_champ_nom"))."\" size=\"20\" /></td>";
        echo "<td>Prénom : </td>";
        echo "<td><input type=\"text\" name=\"ldap_champ_prenom\" value=\"".htmlentities( getSettingValue("ldap_champ_prenom"))."\" size=\"20\" /></td>";
        echo "<td>Email : </td>";
        echo "<td><input type=\"text\" name=\"ldap_champ_email\" value=\"".htmlentities( getSettingValue("ldap_champ_email"))."\" size=\"20\" /></td>";
        echo "</tr></table>";

        echo encode_message_utf8("<br /><br /><b>Cas particulier des serveur SE3</b> : <span class=\"small\">dans le champs ci-dessous, vous pouvez préciser la liste des groupes SE3 autorisés à accéder à GRR.
        Si le champ est laissé vide, il n'y a pas de restrictions.
        Dans le cas contraire, seuls les utilisateurs appartenant à au moins l'un des groupes listés seront autorisés à accéder à GRR.
        Ecrivez les groupes en les séparant par un point-vigule, par exemple : \"Profs;Administratifs\".
        Seuls les groupes de type \"posixGroup\" sont supportés (les groupes de type \"groupOfNames\" ne sont pas supportés).</span>");
        echo "<br /><input type=\"text\" name=\"se3_liste_groupes_autorises\" value=\"".htmlentities( getSettingValue("se3_liste_groupes_autorises"))."\" size=\"50\" />";
        echo "<center><INPUT type=\"submit\" name=\"Valider1\" value=\"Valider\" /></center>";
    } else {
        echo encode_message_utf8("<H3>L'authentification LDAP n'est pas activée.</H3>");
        echo encode_message_utf8("<b>L'authentification LDAP est donc pour le moment impossible</b>. Activez l'authentification LDAP en choisissant le statut qui sera attribué aux personnes présentes
        dans l'annuaire LDAP lorsqu'elles se connectent pour la première fois.
        Vous pourrez par la suite modifier cette valeur pour chaque utilisateur.<br />");
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"visiteur\" />Visiteur<br />";
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"utilisateur\" />Usager<br />";
        echo "<INPUT TYPE=\"radio\" name=\"ldap_statut\" value=\"no_ldap\" checked />Ne pas activer<br />";
        echo "<center><INPUT type=\"submit\" name=\"Valider2\" value=\"Valider\"  /></center>";
    }
    echo "</FORM>";

    if (@file_exists("/config/config_ldap.inc.php")) {
        $test_chemin = '';
         include "./config/config_ldap.inc.php";
        if (($ldap_adresse != '') and ($ldap_port != '')) {
            $ok = "<b><font color=\"green\">OK</font></b>";
            $failed = "<b><font color=\"red\">Echec</font></b>";
            echo "<hr />";
            echo "<H3>Test de connexion à l'annuaire : ";
            $ds = grr_connect_ldap($ldap_adresse,$ldap_port,$ldap_login,$ldap_pwd,$use_tls,'y');
            if ($ds=="error_1") {
               echo encode_message_utf8($failed)."</h3>";
               echo encode_message_utf8("(<font color=\"red\">Impossible d'utiliser la norme LDAP V3</font>)<br />\n");
            } else if ($ds=="error_2") {
               echo encode_message_utf8($failed)."</h3>";
               echo encode_message_utf8("(<font color=\"red\">Impossible d'utiliser TLS</font>)<br />\n");
            } else if ($ds=="error_3") {
               echo encode_message_utf8($failed)."</h3>";
               echo encode_message_utf8("(<font color=\"red\">Connexion établie mais l'identification auprès du serveur a échoué</font>)<br />\n");
            } else if ($ds=="error_4") {
               echo encode_message_utf8($failed)."</h3>";
               echo encode_message_utf8("(<font color=\"red\">Impossible d'établir la connexion</font>)<br />\n");
            } else if (!$ds) {
                echo encode_message_utf8($failed)."</h3>";;
            } else {
                echo encode_message_utf8($ok)."</h3>";;
                echo "<H3>Test de recherche sur l'annuaire avec le chemin spécifié : ";
                $result = "";
                $result = grr_ldap_search_user($ds, $ldap_base, "objectClass", "*",$ldap_filter,"y");
                if ($result=="error_1") {
                    $test_chemin = 'failed';
                    echo encode_message_utf8($failed)."</h3>";
                    if ($ldap_filter == "") {
                        echo encode_message_utf8("(<font color=\"red\"><b>Problème</b> : Le chemin que vous avez choisi <b>ne semble pas valide</b>.</font>)<br /><br />");
                    } else {
                        echo encode_message_utf8("(<font color=\"red\"><b>Problème</b> : Le chemin et/ou le filtre additionnel que vous avez choisi <b>ne semblent pas valides</b>.</font>)<br /><br />");
                    }
                } else if ($result == "error_2") {
                    $test_chemin = 'failed';
                    echo encode_message_utf8($failed)."</h3>";
                    if ($ldap_filter == "") {
                        echo encode_message_utf8("(<font color=\"red\"><b>Problème</b> : Le chemin que vous avez choisi semble valide mais la recherche sur ce chemin ne renvoie aucun résultat.</font>)<br /><br />");
                    } else {
                        echo encode_message_utf8("(<font color=\"red\"><b>Problème</b> : Le chemin et le filtre additionnel que vous avez choisi semblent valides  mais la recherche sur ce chemin ne renvoie aucun résultat.</font>)<br /><br />");
                    }
                } else {
                    echo encode_message_utf8($ok)."</h3>";;
                }
            }
        }
    }
    echo "<hr />";

    if (@file_exists("/config/config_ldap.inc.php")) {

        echo encode_message_utf8("<H3>Configuration actuelle</H3> (Informations contenues dans le fichier \"config_ldap.inc.php\") :<br /><ul>");
        echo encode_message_utf8("<li>Adresse de l'annuaire LDAP <b>: ".$ldap_adresse."</b></li>");
        echo encode_message_utf8("<li>Port utilisé : <b>".$ldap_port."</b></li>");
        if ($test_chemin == 'failed')
            echo encode_message_utf8("<li><font color=\"red\">Chemin d'accès dans l'annuaire : <b>&nbsp;".$ldap_base."</b></font></li>");
        else
            echo encode_message_utf8("<li>Chemin d'accès dans l'annuaire : <b>&nbsp;".$ldap_base."</b></li>");
        if ($ldap_filter!="") $ldap_filter_text = $ldap_filter; else $ldap_filter_text = "non";
        if (($test_chemin == 'failed') and ($ldap_filter!=""))
            echo encode_message_utf8("<li><font color=\"red\">Filtre LDAP supplémentaire : <b>&nbsp;".$ldap_filter_text."</b></font></li>");
        else
            echo encode_message_utf8("<li>Filtre LDAP supplémentaire : <b>&nbsp;".$ldap_filter_text."</b></li>");
        if ($ldap_login) {
            echo encode_message_utf8("<li>Compte pour l'accès : <br />");
            echo "Identifiant : <b>".$ldap_login."</b><br />";
            $ldap_pwd_hide = "";
            for ($i=0;$i<strlen($ldap_pwd);$i++) $ldap_pwd_hide .= "*";
            echo "Mot de passe : <b>".$ldap_pwd_hide."</b></li>";
        } else {
            echo encode_message_utf8("<li>Accès anonyme.</li>");
        }
        if ($use_tls) $use_tls_text = "oui"; else $use_tls_text = "non";
        echo encode_message_utf8("<li>Utiliser TLS : <b>".$use_tls_text."</b></li>");
        echo encode_message_utf8("</ul>Vous pouvez procéder à une nouvelle configuration LDAP.<br />");
    } else {
        echo encode_message_utf8("<H3>L'accès à l'annuaire LDAP n'est pas configuré.</H3><b>L'authentification LDAP est donc pour le moment impossible.</b>");
    }
    echo "<form action=\"admin_config_ldap.php\" method=\"post\">";
    echo "<INPUT TYPE=\"hidden\" NAME=\"etape\" VALUE=\"1\" />";
    echo "<INPUT TYPE=\"hidden\" name=\"valid\" value=\"$valid\" />";
    echo "<center><input type=submit value=\"Configurer LDAP\" /></center></form>";
}





// fin de l'affichage de la colonne de droite
if ($valid == 'no') echo "</td></tr></table>";

?>
</body>
</html>
