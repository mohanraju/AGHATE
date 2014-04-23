<?php
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

$Aghate = new Aghate();
$TableLoc =  $_GET['table_loc'];
$id = $_GET['id'];
$newcolor = $_GET['newcolor'];
if ((strlen($id) > 0) && (strlen($newcolor) > 0) ){
	$sql ="UPDATE ".$TableLoc." set type='".$newcolor."' WHERE id ='".$id."'";
	$Aghate->update($sql);
	echo "Couleur mise a jour avec  succès";
}else{
	echo "Echec une erreur a eu lieu ";
	}
