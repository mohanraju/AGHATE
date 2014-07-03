<?Php  
/*
* PROJET AGHATE
* Ajax update plage pos  dans le reservation /examen complementaire
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

$Aghate = new Aghate();
$TableLoc =  $_GET['table_loc'];
$id = $_GET['id'];
$newpos = $_GET['newpos'];

 
 

if ((strlen($id) > 0) && (strlen($newpos) > 0) ){
	$sql ="UPDATE ".$TableLoc." set plage_pos='".$newpos."' WHERE id ='".$id."'";
echo "<br>".$sql;	
	$Aghate->update($sql);
	echo "|OK|Deplacement mise a jour avec  succès";
}else{
	echo "|ERR|Echec une erreur a eu lieu ";
	}
?>
