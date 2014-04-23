<?php
/*
############################################################################################
#	                                                                                       #
#                                                                                          #
############################################################################################
*/
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
 
include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassAghate.php");

$db=new MYSQL();
$Aghate = new Aghate();

$tb=$_GET['tb'];

//echo $tb;

// escape your parameters to prevent sql injection
$param   = utf8_decode($_GET['term']);
$ret_vals = array();

//execute SQL
$res=$Aghate->GetMedecinInfoByValRech($param,$tb);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i]['id_medecin'];
	$lib=$res[$i]['nom']." ".$res[$i]['prenom'];
	
	//prepare Tableau	
	$ret_vals[] = array(
		'value' => $lib,
        'id'    => $id
    	);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
