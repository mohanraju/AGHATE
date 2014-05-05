<?php
/*
#########################################################################
#                            admin_maj.php                              #
#                                                                       #
#            interface permettant la mise à jour de la base de données  #
#               Dernière modification : 28/04/2014                      #
#                                                                       #
#                                                                       #
#########################################################################
*/
//$CurrentRevision est definit dans misc.inc.php

header('Content-Type: text/html; charset=utf-8');
set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);

include "./config/config.php";
include "./config/config_".$site.".php";
$grr_script_name = "admin_maj.php";

// Session related functions
include "./resume_session.php";
include "./commun/include/misc.inc.php";// pour version info
echo "<br>1".$version_grr_RC;
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassGilda.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Gilda= new Gilda($ConnexionStringGILDA);

	echo "<br>1".$version_grr_RC;
// check dorit execution  !!!!
//------------------------------------------------
echo "<br>User:".$_SESSION['login'];
if($_SESSION['login']!='ADMIN' )
{
	echo "<br>Access denied pour cette utilisateur!<br>seul le compte admin est autorisé  ";
	exit;
} 

// get $RevisionBase
$RevisionBase=$Aghate->GetRevision();

include("admin_maj_20140303.php");
include("admin_maj_20140420.php");
include("admin_maj_20140422.php");
include("admin_maj_20140428.php");
//$RevisionBase="000";
//include("admin_maj_20140430.php");

//mettre a jour la dernière version
echo "<br>1".$version_grr_RC;
$sql="update agt_config set VALUE='".$version_grr_RC."' where NAME='versionRC'";
$Aghate->update($sql);
?>
</body>
</html>
