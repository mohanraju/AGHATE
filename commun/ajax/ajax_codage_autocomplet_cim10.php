<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
############################################################################################
*/

//==========================================================================================
// script s d'inclusion
//==========================================================================================
include("../../config/config.php");
include("../../config/config_".$site.".php"); 
require("../../commun/include/ClassMysql.php");
header('Content-Type: text/html; charset=utf-8'); 
$db=new MYSQL();

$tb=$_GET['tb'];

// escape your parameters to prevent sql injection
$param   = mysql_real_escape_string($_GET['term']);
$ret_vals = array();

//prepare les donnes avec les velauer pass
$sql="SELECT * from ".$tb."
			WHERE LIB LIKE '%".$param."%'				
			OR  CODE1 LIKE '%".$param."%'
			order by FREQ DESC				";

//execute SQL
$res=$db->select($sql);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$codes=$res[$i]['CODE2'];
	$cma=$res[$i]['NIVEAU'];
	$theme="Cma".trim($cma);
	if(strlen($cma)>0){
		$cma_val='<span class="badge '.$theme.'">'.$cma.'</span>';
	}
	//Force Masquer pour V2 à voir dans V3
	$cma_val='';
	//GESTION DES CAS 1 LIBELLÉ ET PLUSIEURS CODES :
	//Affichage MultiCode:
	if (strlen($codes) > 0) {
		$formated_lib=$res[$i]['LIB']." [(".$res[$i]['CODE1']."|".$codes.")] ".$cma_val;
	}
	//Affichage MonoCode:
	else
	{
		// format result avec code
		$formated_lib=$res[$i]['LIB']." [(".$res[$i]['CODE1'].")] ".$cma_val;
	}
	
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => $formated_lib,
        				'id'    => $i,
    						);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
