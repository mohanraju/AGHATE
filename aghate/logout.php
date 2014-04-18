<?php
#########################################################################
#                            logout.php                                 #
#                                                                       #
#                      script de deconnexion                            #
#                                                                       #
#            Dernière modification : 10/04/2008                         #
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

require_once("./config/config.php");
require_once("./config/config.inc.php");
include "./commun/include/functions.inc.php";
require_once("./commun/include/$dbsys.inc.php");
// Settings
require_once("./commun/include/settings.inc.php");
//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");

// Paramètres langage
include "./commun/include/language.inc.php";

require_once("./commun/include/session.inc.php");


if ((getSettingValue('sso_statut') == 'lasso_visiteur') or (getSettingValue('sso_statut') == 'lasso_utilisateur')) {
  require_once(SPKITLASSO.'/lassospkit_public_api.inc.php');
  session_name(SESSION_NAME);
  @session_start();
  if (@$_SESSION['lasso_nameid'] != NULL)
    {
      // Nous sommes authentifiés: on se déconnecte, puis on revient
      lassospkit_set_userid($_SESSION['login']); // work-around
      lassospkit_set_nameid($_SESSION['lasso_nameid']);
      lassospkit_soap_logout();
      lassospkit_clean();
    }
}


grr_closeSession($_GET['auto']);

//foced to goto login page by mohanraju
   header("Location: ./login.php");
   exit;

//redirection vers l'url de déconnexion
$url = getSettingValue("url_disconnect");
if ($url != '') {
  header("Location: $url");
  exit;
}


if (isset($_GET['authentif_obli']) and ($_GET['authentif_obli'] == 'no')) {
   header("Location: ./".page_accueil()."");
   exit;
}
echo begin_page(get_vocab("mrbs"),"no_session");
?>
<div class="center">
<h1>
<?php
 if (!$_GET['auto']) {
     echo (get_vocab("msg_logout1")."<br/>");
 } else {
     echo (get_vocab("msg_logout2")."<br/>");
 }
?>
</h1><a href="login.php"><?php echo (get_vocab("msg_logout3")."<br/>"); ?></a>
</p>
</div>
</body>
</html>
