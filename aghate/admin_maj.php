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

$grr_script_name = "admin_maj.php";

// Session related functions
include "./resume_session.php";
include "./commun/include/misc.inc.php";// pour version info
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassGilda.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Gilda= new Gilda($ConnexionStringGILDA);

echo "<br>Version Scripts : ".$version_grr_RC;
// check dorit execution  !!!!
//------------------------------------------------
echo "<br>User:".$_SESSION['login'];
if(($_SESSION['login']!='ADMIN' ) || (strlen($_SESSION['login']) < 1))
{
	echo "<br>Access denied pour cet utilisateur!<br>seul le compte admin est autorisé  ";
	exit;
} 

// get $RevisionBase
$RevisionBase=$Aghate->GetRevision();
 
echo "<br>Revision BaseMysql avant maj : ".$RevisionBase;
if (strlen($RevisionBase) < 0)
{
	$RevisionBase="20140101";
	$Aghate->delete("delete from agt_config where NAME='versionRC' or NAME='version'");
	$Aghate->insert("insert into agt_config set NAME='versionRC',VALUE='$RevisionBase'");
	$Aghate->insert("insert into agt_config set NAME='version',VALUE='2.0'");
}

$LastVersion[]="20140303";
$LastVersion[]="20140420";
$LastVersion[]="20140422";
$LastVersion[]="20140428";
$LastVersion[]="20140528";
$LastVersion[]="20140602";
$LastVersion[]="20140603";
$LastVersion[]="20140702";

for($i=0; $i < count($LastVersion); $i++)
{
	echo "<br>".intval($RevisionBase)," ",intval($LastVersion[$i]);
	if( $LastVersion[$i] > $RevisionBase)
		include("admin_maj_".$LastVersion[$i].".php");

	
}

// get $RevisionBase
$RevisionBase=$Aghate->GetRevision();
 
echo "<br>Revision BaseMysql après maj : ".$RevisionBase;
?>
<br>
<a href="./">re-login Aghate en cliquant ICI</a>
</body>
</html>
