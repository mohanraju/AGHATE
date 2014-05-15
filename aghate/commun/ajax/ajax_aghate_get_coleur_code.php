<?Php  
/*
* PROJET AGHATE
* Ajax get patients pour les recherche par nom/noip
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
* 
*/
// script s d'inclusion
include "../../resume_session.php";
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

$Aghate = new Aghate();

$specialite = $_GET['specialite'];
if (strlen($specialite) > 0){
	$res = $Aghate->GetColorCodeByDescription($specialite);
}
echo $res;
