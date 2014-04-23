<?php
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
// Settings
require_once("./commun/include/settings.inc.php");
//Chargement des valeurs de la table settingS
if (!loadSettings())
    die("Erreur chargement settings");
// Session related functions
require_once("./commun/include/session.inc.php");
// Resume session
if (!grr_resumeSession()) {
    header("Location: ./logout.php?auto=1");
    die();
};
// Paramètres langage
include "./commun/include/language.inc.php";
?>
