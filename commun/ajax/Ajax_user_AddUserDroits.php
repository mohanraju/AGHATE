<?php 
header('Content-Type: text/html; charset=utf-8');

//  initialise variables 
$amt=20000; //Nombre enregistrements a afficher
$start=0; 

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");

$login=$_GET['login'];
$dtoits=$_GET['droits'];
echo $dtoits;
list($droit_type,$droit_value)=explode("|",$dtoits);

// init les objets
$db=new Mysql();
$TableDroits="user_droits";

// check exists
$sql="SELECT login  FROM $TableDroits 
				WHERE login='$login' AND
				droit_type='$droit_type' AND 
			  droit_value='$droit_value'";

$res= $db->select($sql);
if(count($res) < 1)
{
// insert valuse into table
$insert_sql="INSERT  into $TableDroits	set
 							login='$login',  
							droit_type='$droit_type', 
							droit_value='$droit_value'
							";
$result=$db->insert($insert_sql);
return 'oui';
}
return 'non';
  
?>
