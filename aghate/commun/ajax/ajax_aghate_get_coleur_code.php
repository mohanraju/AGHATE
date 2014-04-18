<?php
include "../../config/config.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";

$Aghate = new Aghate();

$specialite = $_GET['specialite'];
if (strlen($specialite) > 0){
	$res = $Aghate->GetColorCodeByDescription($specialite);
}
echo $res;
