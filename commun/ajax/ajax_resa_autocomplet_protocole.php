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
$sql_ajax="SELECT id_protocole, protocole, duree from ".$tb."
			WHERE protocole LIKE '%".$param."%'			
			ORDER by protocole";

$ret_vals = array();

//execute SQL
$res=$db->select($sql_ajax);
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
