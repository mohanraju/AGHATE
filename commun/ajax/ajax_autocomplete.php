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
require("../../commun/include/CommonFonctions.php");

$db=new MYSQL();
$ComFunc=new CommonFunctions(true);
header('Content-Type: text/html; charset=utf-8');
/*$tb=$_GET['tb'];
$code=$_GET['code'];
$lib=$_GET['lib'];*/


// escape your parameters to prevent sql injection
$param   = $_GET['term'];
$ret_vals = array();

//prepare les donnes avec les velauer pass

$sql="SELECT ".$_GET['code'].",".$_GET['lib'];

if($_GET['tb'] == "CIM10SPEC")$sql.=",CODE2";
$sql.=" from ".$_GET['tb']."
			WHERE ".$_GET['lib']." LIKE '%".$param."%'				
			OR  ".$_GET['code']." LIKE '%".$param."%'
			order by ".$_GET['code']." ASC";
//echo $sql;
//execute SQL
$res=$db->select($sql);
$nb_rec=count($res);


//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	if($_GET['tb'] == "CIM10SPEC" && $res[$i]['CODE2'] !="") $res[$i][$_GET['code']]=$res[$i][$_GET['code']]."|".$res[$i]['CODE2'];

	$codes=$res[$i][$_GET['code']];
	$lib=$res[$i][1];
	$duree=$res[$i][2];
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => $res[$i][$_GET['lib']],
        				'id'    => $res[$i][$_GET['code']],
    						);  
}

// format json format et envoyer

/*$ret_vals=Array ( 0 => Array ( "value" => Array ( "value" => "Choléra, sans précision" ,"id" => "A009" ) ,"id" => "A010" ), 1=> "titi",3=>"totoS");
print_r($ret_vals);
echo "<br><br>";
echo json_encode($ret_vals);
echo "<br><br>";*/
echo JsonEncode($ret_vals);
?>
