<?php 
header('Content-Type: text/html; charset=utf-8');

//  initialise variables 
$amt=20000; //Nombre enregistrements a afficher
$start=0; 

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");

// init les objets
$db=new Mysql();
$TableUser="utilisateur";

// get users from the base
$result=$db->select("SELECT * from ".$TableUser. " Order by nom,prenom");

// nombre de row dans le résultat
$total_records=count($result); 

// prepare la tableu structuré avec les donnnés a retournes
$res1= '{  "aaData": ['; 

for($i=0;$i < $total_records; $i++)
{
	if ($i > 0) $res1.=','; 
	
	$voirlink=addslashes('<a href="#"  onClick="GetUserInfo('.$result[$i]['login'].')"><img src="../images/voir.jpg" border="0" height="15" width="15"></a>');
	$editlink=addslashes('<a href="#"  onClick="EditUserInfo('.$result[$i]['login'].')"><img src="../images/edit.jpg" border="0" height="15" width="15"></a>');	
	$res1.= ' ["'.$result[$i]['login'].'",
	
					 "'.addslashes(trim($result[$i]['nom'])).'",
					 "'.addslashes(trim($result[$i]['prenom'])).'",					 
					 "'.$voirlink.'&nbsp;&nbsp;'.$editlink.'"]';				 
}
 $res1.= ']}'; 
 echo $res1;
 
  
?>
