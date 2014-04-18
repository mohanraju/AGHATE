<?php
session_name('GRR');
session_start();
header('Content-type: text/html; charset=utf-8');
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");
include("./commun/include/ClassGilda.php");
include("./config/config_".$site.".php");
include("../commun/layout/header.php");
 
$com=new CommonFunctions(true);
$Html=new Html();
$Aghate = new Aghate();
$Gilda = new Gilda($ConnexionStringGILDA);

$Aghate->NomTableLoc = $table_loc;
$NomTableLoc = $table_loc;


$UserInfo = $Aghate->GetUserInfo($_SESSION['login']);
for($i=0; $i < 1000; $i++)
{
	$start_time=mktime(10,10,0,01,01,2010);
	$end_time =mktime(10,10,0,02,01,2010);

	$sql="INSERT INTO `agt_loc` (  `id_prog`, `noip`, `nda`, `start_time`, `end_time`, `room_id`, `timestamp`, `create_by`, `name`, `type`, `protocole`, `description`, `statut_entry`, `medecin`, `uh`, `gilda_id`, `de_source`, `ds_source`, `tydos`, `plage_pos`) VALUES
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0'),
	( NULL, '2094008917', '471416172', '".$start_time."', '".$end_time."', 767, '2014-04-14 18:05:26', 'Automate', '', 'M', 'Protocole Automate', NULL, 'HospitalisÃ©', NULL, '353', '471400034113', 'Gilda', 'Automate', 'A', '0')

	";
	$Aghate->insert($sql);
echo "<br> linge inseré".$i;	
}
?>