<?Php  
/*
* PROJET AGHATE
* Ajax check plage pos libre  dans le reservation /examen complementaire
* 
* @Mohanraju Sp SBIM/SAINT LOUIS/APHP /Paris
* 
* date dernière modififation 14/05/2014
* 
*/
include "../../resume_session.php";
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

header('Content-Type: text/html; charset=utf-8');
$Aghate = new Aghate();
$TableLoc =  $_GET['table_loc'];
$LocId = $_GET['id'];
$NowPos = $_GET['newpos'];
if( (strlen($TableLoc) < 1) || (strlen($LocId) < 1) || (strlen($NowPos) < 1) )
{
	echo "|ERR|Invalide parametres pour vérifier plage libre";
	exit;
}
//check Plage libre ou un autre utilisateur a mis le patients
$SqlChk="SELECT * FROM  ".$TableLoc."  
			WHERE  plage_pos='".$NowPos."'
			AND FROM_unixtime( `start_time` , '%Y%m%d' )
			IN (
				SELECT FROM_unixtime( `start_time` , '%Y%m%d' ) AS date
				FROM ".$TableLoc."
				WHERE id = '".$LocId."'
			 ) ";


$ResChk=$Aghate->select($SqlChk);
/*echo $SqlChk;
echo "<pre>";
print_r($ResChk);
*/
if(count($ResChk) > 0)
{
	echo "|ERR|Patient ".$ResChk[0]["noip"].$ResChk[0]["patient"]." occupe le plage ".$ResChk[0]['plage_pos'];
}else
{
	echo "|OK|Plage pos Libre";
}

?>