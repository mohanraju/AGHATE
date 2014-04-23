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

utilisation 
MajAnnulations.php?site=???&date_deb=01/03/2013&date_fin=10/03/2013
*/

error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1);
ini_set ('max_execution_time', 0); // pas de limitation
ini_set('memory_limit','256M');


//------------------------------------------------
// script s d'inclusion
//------------------------------------------------
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

//##########################################################################################
// Taace Files
//##########################################################################################
function trace($fic,$msg)
{
	fwrite($fic,$msg);
}
$trace_file="./trace/TraceAnnulation.txt";
$trace=fopen($trace_file,'a+');
trace($trace," Trace du ".date("d/m/Y h:i:s"));


 

/*
//##########################################################################################
	HC=> Traitement les données 
	recupare les annulation de GILDA avec le dademj et tymaj
	Puis vérify les sejours prenset dans EPI avec NDA+UH+DAENTR
	et chager le tag  à 'D' dans suivi
//##########################################################################################
*/

trace($trace,"==========>  Hospit Complet");
$nbr_annul=0;

//recupares les annulation de GILDA.MVT avec le typaj D et date misea jour=$date
$sql="SELECT dos.noip,mvt.NODA,MVT.NOUF,MVT.TYMVAD,MVT.DAMVAD,MVT.HHMVAD,MVT.TYMAJ,MVT.DADEMJ,MVT.NOIDMV
		FROM mvt,dos
		WHERE  dos.noda=mvt.noda 
		AND DADEMJ between sysdate-5 and sysdate
		AND tymaj='D'";

$res=$Gilda->OraSelect($sql);
//print_r($res); 
echo $sql;
 
$nbr_resume=count($res);
$nbr_annul=0;

// boucle avec les  séjours selectionnées
for ($i=0; $i < $nbr_resume ;$i++)
{
	// Vérify ce sejour present dans Aghate par mvt.NOIDMV
	$sql_annul="SELECT * from agt_loc where gilda_id='".$res[$i]['NOIDMV']."'";
	//echo "<br>".$sql_annul;
	$ResAnnul=$Aghate->select($sql_annul);
	//si aucun résumé trouvé donc le résumé est annulé
	if (count($ResAnnul) > 0)
	{
		echo "<br> Annulé dans gilda NIP:".$res[$i]['NOIP'].", NDA:".$res[$i]['NODA'].", UH:".$res[$i]['NOUF'].", DT_MVT:".$res[$i]['DAMVAD'];
	}
	
}
trace($trace,$nbr_annul." résumés sont annulé sur ".$nbr_resume ." dans la du ".$date_deb." au ".$date_fin);
fclose($trace);
?>

