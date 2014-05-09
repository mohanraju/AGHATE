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

$db=new MYSQL();

$tb=$_GET['tb'];

//echo $tb;

// escape your parameters to prevent sql injection
$param   = utf8_decode($_GET['term']);
$ret_vals = array();

//prepare les donnes avec les velauer pass
$sql_ajax="SELECT *   from ".$tb."
			WHERE nom LIKE '%".$param."%'
			OR prenom LIKE '%".$param."%'
			ORDER by nom";

//echo "sql ajax".$sql_ajax;
$ret_vals = array();

//execute SQL
$res=$db->select($sql_ajax);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i]['id_medecin'];
	$lib=$res[$i]['nom']." ".$res[$i]['prenom'];
	
	//prepare Tableau	
	$ret_vals[] = array(
		'value' => utf8_encode($lib),
        'id'    => $id
    	);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
