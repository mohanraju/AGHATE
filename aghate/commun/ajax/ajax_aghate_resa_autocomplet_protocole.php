<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
############################################################################################
*/
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
 
//==========================================================================================
// script s d'inclusion
//==========================================================================================
include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassAghate.php");

$db=new MYSQL();
$Aghate = new Aghate();

$tb=$_GET['tb'];

// escape your parameters to prevent sql injection
$param   = utf8_decode($_GET['term']);
$ret_vals = array();

$ret_vals = array();

//execute SQL
$res=$Aghate->GetProtocoleInfoByName($param,$tb);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i][0];
	$lib=$res[$i][1];
	$duree=$res[$i][2];
	
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => utf8_encode($lib),
        				'id'    => $id,
        				'duree'    => $duree,
    						);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
