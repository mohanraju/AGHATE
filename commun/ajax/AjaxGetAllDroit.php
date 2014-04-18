<?php 
header('Content-Type: text/html; charset=utf-8');

//  initialise variables 
$amt=20000; //Nombre enregistrements a afficher
$start=0; 

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");

$user=$_GET['login'];
// init les objets
$db=new Mysql();
$TableUser="utilisateur";

// get users from the base
$result=$db->select("SELECT 	urm,service_lib 	from structure_gh  group by urm Order by service_lib");

// nombre de row dans le résultat
$total_records=count($result); 

// prepare la tableu structuré avec les donnnés a retournes
$res1= '{  "aaData": ['; 

for($i=0;$i < $total_records; $i++)
{
	if ($i > 0) $res1.=','; 
	
	$editlink=addslashes('<a href="#"  onClick="AddUserDroits('.$user.','.$result[$i]['urm'].')"><img src="../images/right_arrow.jpg" border="0" height="15" width="15"></a>');	
	$res1.= ' ["'.addslashes(trim($result[$i]['service_lib'])).' - '.addslashes(trim($result[$i]['urm'])).'",					 
					 "'.$voirlink.'&nbsp;&nbsp;'.$editlink.'"]';				 
}
 $res1.= ']}'; 
 echo $res1;
 
  
?>
