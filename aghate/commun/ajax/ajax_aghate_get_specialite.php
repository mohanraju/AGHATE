<?Php  
/*
* PROJET AGHATE
* Ajax get specialité
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
* 
*/

include "../../resume_session.php";
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

$Aghate = new Aghate();

$NomMedcin = $_GET['medecin'];
$id_medecin= $_GET['id_medecin'];
if (strlen($id_medecin) > 0){
	$res = $Aghate->GetInfoMedecinById($id_medecin);
}
echo $res['specialite'];	
?>
