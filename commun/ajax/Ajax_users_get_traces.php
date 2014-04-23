<?php 
header('Content-Type: text/html; charset=utf-8');

//  initialise variables 
$amt=20000; //Nombre enregistrements a afficher
$start=0; 

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/CommonFonctions.php");

// init les objets
$db=new Mysql();
$TableUser="utilisateur";
$functions= new CommonFunctions(false);



// get users from the base
$sql = "SELECT * FROM log , utilisateur
    WHERE log.user = utilisateur.login
    ORDER BY time DESC   ";    
$result=$db->select($sql);    

// nombre de row dans le résultat
$total_records=count($result); 

//print_r($result);
for($i=0;$i < $total_records; $i++)
{
	unset($row);
	$row[]=utf8_encode($result[$i]['login']);	
	$row[]=utf8_encode($result[$i]['nom']);
	$row[]=utf8_encode($result[$i]['prenom']);
	$row[]=utf8_encode($functions->Mysql2Normal($result[$i]['time']));
	$row[]=utf8_encode($result[$i]['tache']);
	$data['aaData'][]=$row;
}
 echo json_encode($data);
 
  
?>
