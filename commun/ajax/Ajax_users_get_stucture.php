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
$sql = "SELECT 
				concat(hopital,' ',hopital_lib) as hopital,
				concat(pole,' ',pole_lib ) as pole,
				service_lib as service,
				concat(urm,' ',	urm_lib) urm,
				concat(uh,' ',uh_lib) uh	
 				FROM structure_gh
 				where urm !='999'
    ORDER BY service_lib  ";    
$result=$db->select($sql);    

// nombre de row dans le résultat
$total_records=count($result); 

//print_r($result);
for($i=0;$i < $total_records; $i++)
{
	unset($row);
	$row[]=utf8_encode($result[$i]['hopital']);	
	$row[]=utf8_encode($result[$i]['pole']);
	$row[]=utf8_encode($result[$i]['service']);
	$row[]=utf8_encode($result[$i]['urm']);
	$row[]=utf8_encode($result[$i]['uh']);
	$data['aaData'][]=$row;
}
 echo json_encode($data);
 
  
?>
