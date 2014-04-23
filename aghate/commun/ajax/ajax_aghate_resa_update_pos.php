<?php
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

$Aghate = new Aghate();
$TableLoc =  $_GET['table_loc'];
$id = $_GET['id'];
$newpos = $_GET['newpos'];
if ((strlen($id) > 0) && (strlen($newpos) > 0) ){
	$sql ="UPDATE ".$TableLoc." set plage_pos='".$newpos."' WHERE id ='".$id."'";
	$Aghate->update($sql);
	echo "Deplacement mise a jour avec  succès";
}else{
	echo "Echec une erreur a eu lieu ";
	}
?>
