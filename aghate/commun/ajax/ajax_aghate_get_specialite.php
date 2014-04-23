<?php
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
