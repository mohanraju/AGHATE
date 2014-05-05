<?php
#########################################################################
#                            login.php                                  #
#                                                                       #
#            interface de connexion                                     #
#               Dernière modification : 10/07/2006                      #
#                                                                       #
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

// Settings
require_once("./commun/include/settings.inc.php");

//Chargement des valeurs de la table settingS
if (!loadSettings()) die("Erreur chargement settings");

// Paramètres langage
include "./commun/include/language.inc.php";

// Session related functions
require_once("./commun/include/session.inc.php");


// User wants to be authentified
if (isset($_POST['login']) && isset($_POST['password'])) {
    // Détruit toutes les variables de session au cas où une session existait auparavant
    $_SESSION = array();
    $result = grr_opensession($_POST['login'], $_POST['password']);
    // On écrit les données de session et ferme la session
    session_write_close();
    if ($result=="2") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= " ".get_vocab("wrong_pwd");
    } else if ($result == "3") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("importation_impossible");
    } else if ($result == "4") {
        //$message = get_vocab("importation_impossible");
        $message = get_vocab("echec_connexion_GRR");
        $message .= " ".get_vocab("causes_possibles");
        $message .= "<br />- ".get_vocab("wrong_pwd");
        $message .= "<br />- ". get_vocab("echec_authentification_ldap");
     } else if ($result == "5") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("connexion_a_grr_non_autorisee");
     } else if ($result == "6") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("connexion_a_grr_non_autorisee");
        $message .= "<br />". get_vocab("format identifiant incorrect");
     } else if ($result == "7") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("connexion_a_grr_non_autorisee");
        $message .= "<br />". get_vocab("echec_authentification_ldap");
        $message .= "<br />". get_vocab("ldap_chemin_invalide");
     } else if ($result == "8") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("connexion_a_grr_non_autorisee");
        $message .= "<br />". get_vocab("echec_authentification_ldap");
        $message .= "<br />". get_vocab("ldap_recherche_identifiant_aucun_resultats");
     } else if ($result == "9") {
        $message = get_vocab("echec_connexion_GRR");
        $message .= "<br />". get_vocab("connexion_a_grr_non_autorisee");
        $message .= "<br />". get_vocab("echec_authentification_ldap");
        $message .= "<br />". get_vocab("ldap_doublon_identifiant");


     } else {
        header("Location: ./".page_accueil()."");
		  $_SESSION['alert_novenue=']="yes";		
        die();
    }
}
// Dans le cas d'une démo, on met à jour la base une fois par jour.

echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"no_session");
?>
<script type="text/javascript" language="javascript">
function encode_adresse(user,domain,label,link) {
        var address = user+'@'+domain;
        var toWrite = '';
        if (link > 0) {toWrite += '<a href="mailto:'+address+'">';}
        if (label != '') {toWrite += label;} else {toWrite += address;}
        if (link > 0) {toWrite += '<\/a>';}
        document.write(toWrite);
}
function popup(number) {
	 whichOne = number;
	 pat="";	 
    champ_du_owner="name";
    mywindow=open('patients_ajoute.php?champ_du_owner='+champ_du_owner+'&nom='+pat,'mypat','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'patients_ajoute.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
    if (mywindow.opener == null) mywindow.opener = self;
    if(mywindow.window.focus){mywindow.window.focus();}        
}
</script>
<div class="center">
<h1><?php echo getSettingValue("title_home_page"); ?></h1>
<h2><?php //echo getSettingValue("company"); ?></h2>
<div align='center'><img src="./commun/images/logo_hopital.gif" width="200" height="65" /></div>
<br />

<p>
<?php echo getSettingValue("message_home_page");
if ((getSettingValue("disable_login"))=='yes') echo "<br /><br /><font color='red'>".get_vocab("msg_login3")."</font>";
?>
</p>
<form name="main" action="login.php" method='post' style="width: 100%; margin-top: 24px; margin-bottom: 48px;">
<?php
if ((isset($message)) and (getSettingValue("disable_login"))!='yes') {
    echo("<p><font color=red>" . $message . "</font></p>");
}
if ((getSettingValue('sso_statut') == 'cas_visiteur') or (getSettingValue('sso_statut') == 'cas_utilisateur')) {
    echo "<p><font size=\"+1\"><a href=\"./index.php\">".get_vocab("authentification_CAS")."</a></font></p>";
    echo "<p><b>".get_vocab("authentification_locale")."</b></p>";
}
if ((getSettingValue('sso_statut') == 'lemon_visiteur') or (getSettingValue('sso_statut') == 'lemon_utilisateur')) {
    echo "<p><font size=\"+1\"><a href=\"./index.php\">".get_vocab("authentification_lemon")."</a></font></p>";
    echo "<p><b>".get_vocab("authentification_locale")."</b></p>";
}
if (getSettingValue('sso_statut') == 'lcs') {
    echo "<p><font size=\"+1\"><a href=\"".LCS_PAGE_AUTHENTIF."\">".get_vocab("authentification_lcs")."</a></font></p>";
    echo "<p><b>".get_vocab("authentification_locale")."</b></p>";
}
if ((getSettingValue('sso_statut') == 'lasso_visiteur') or (getSettingValue('sso_statut') == 'lasso_utilisateur')) {
    echo "<p><font size=\"+1\"><a href=\"./index.php\">".get_vocab("authentification_lasso")."</a></font></p>";
    echo "<p><b>".get_vocab("authentification_locale")."</b></p>";
}
if ((getSettingValue('sso_statut') == 'http_visiteur') or (getSettingValue('sso_statut') == 'http_utilisateur')) {
    echo "<p><font size=\"+1\"><a href=\"./index.php\">".get_vocab("authentification_http")."</a></font></p>";
    echo "<p><b>".get_vocab("authentification_locale")."</b></p>";
}


?>
<fieldset style="padding-top: 8px; padding-bottom: 8px; width: 40%; margin-left: auto; margin-right: auto;">
<legend class="fontcolor3" style="font-variant: small-caps;"><?php echo get_vocab("identification"); ?></legend>
<table style="width: 100%; border: 0;" cellpadding="5" cellspacing="0">
<tr>
<td style="text-align: right; width: 40%; font-variant: small-caps;"><?php echo get_vocab("login"); ?></td>
<td style="text-align: center; width: 60%;"><input type="text" name="login" /></td>
</tr>
<tr>
<td style="text-align: right; width: 40%; font-variant: small-caps;"><?php echo get_vocab("pwd"); ?></td>
<td style="text-align: center; width: 60%;"><input type="password" name="password" /></td>
</tr>
 
<td colspan='2' align='center'><input type="submit" name="submit" value="<?php echo get_vocab("OK"); ?>" style="font-variant: small-caps;" /></td>
</table>


<br />
</fieldset>
</form>

<script type="text/javascript" language="JavaScript">
document.main.login.focus();
</script>


</div>
	<?php include "./commun/include/trailer.inc.php"; ?>
