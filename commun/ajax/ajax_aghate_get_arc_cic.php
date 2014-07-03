<?php
/*
 * Projet Aghate 
 * Ajax GetArcNoms 
 * Table aghate.agt_listes
 * 
 */ 

//==========================================================================================
// script s d'inclusion
//==========================================================================================
include("../../aghate/config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/CommonFonctions.php");

$db=new MYSQL();
$ComFunc=new CommonFunctions(true);
header('Content-Type: text/html; charset=utf-8');

// escape your parameters to prevent sql injection
$param   = $_GET['term'];
$ret_vals = array();

//prepare les donnes avec les velauer pass


$sql="SELECT lib_value as code,libelle as lib FROM agt_listes where grp='ARC' AND libelle like('%".$param."%') ORDER BY libelle ";
 
$res=$db->select($sql);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	//prepare Tableau	
	$ret_vals[] = array(
        				'id'    => $res[$i]['code'],	
        				'value' => $res[$i]['lib']
    						);  
}
 
echo JsonEncode($ret_vals);
?>
