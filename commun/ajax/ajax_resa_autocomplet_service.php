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
include("../../aghate/config/config.php");
require("../../commun/include/ClassMysql.php");

$db=new MYSQL();

$tb=$_GET['tb'];

// escape your parameters to prevent sql injection
$param   = utf8_decode(mysql_real_escape_string($_GET['term']));
$ret_vals = array();

//prepare les donnes avec les velauer pass
$sql_ajax="SELECT id, service_name from ".$tb."
			WHERE service_name LIKE '%".$param."%'				
			AND enable_periods != 'y' 
			AND etat='n' 
			ORDER by service_name";
echo $sql;
$ret_vals = array();

//execute SQL
$res=$db->select($sql_ajax);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i][0];
	$lib=$res[$i][1];
	
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => utf8_encode($lib),
        				'id'    => $id,
    						);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
