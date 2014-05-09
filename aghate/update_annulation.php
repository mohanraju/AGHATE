<?php
/*
############################################################################################
#	update_annulation.php                                                                  #
#   mettre a jour les annulations                                                          #
#                                                                                          #
#                                                                                          #
#	Date creation 06/02/2014                                                               #
#                                                                                          #
############################################################################################
*/

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
ini_set ('max_execution_time', 0); // pas de limitation
ini_set('memory_limit','256M');


//------------------------------------------------
// script s d'inclusion
//------------------------------------------------
include "./resume_session.php";
require_once("./config/config.php");
require_once("./config/config_".strtolower($site).".php"); 
require_once("./commun/include/ClassMysql.php");
require_once("./commun/include/ClassGilda.php");
require_once("./commun/include/ClassAghate.php");


//Objet init
$db=new MYSQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Gilda=new Gilda($ConnexionStringGILDA);

$date_maj=date("d/m/Y");

echo "<br><hr>Mise à jour annulations du site $site  entre  ".$date_maj." au -5 jours <hr><br>";

$nbr_annul=0;

//recupares les annulation de GILDA.MVT avec le typaj D et date misea jour=$date
$sql="SELECT dos.noip,mvt.NODA as NDA,MVT.NOUF,MVT.TYMVAD,MVT.DAMVAD,MVT.HHMVAD,MVT.TYMAJ,MVT.DADEMJ,MVT.NOIDMV
		FROM mvt,dos
		WHERE  dos.noda=mvt.noda 
		AND DADEMJ between sysdate-1 and sysdate+1
		AND tymaj='D' order by mvt.NODA";

$res=$Gilda->OraSelect($sql);
//print_r($res); 
 
$nbr_resume=count($res);
$nbr_annul=0;
$last_nda="";

for($i=0; $i < $nbr_resume ;$i++)
{
	if($last_nda != $res[$i]['NDA'])
	{
		// mise a jour TEMPS NDA dans forms/coadge a faire ici
		$url= "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];		
		$url= str_replace(strrchr($url,"/"),"/",$url);			
		$result = file_get_contents($url."commun/ajax/ajax_aghate_remttre_ajour_gilda_par_nda.php?nda=".$res[$i]['NDA']."&table_loc=agt_loc");
		//echo "<br>".$result;
	}	
	$last_nda = $res[$i]['NDA'];
}
?>

