<?php

ini_set('display_errors',1);
include("../../config/config.php");
include("../../config/config_".$site.".php"); 
include("../../commun/include/ClassMysql.php");
$db=new MySQL();


$table='referentiel_glims';
$fp = fopen($table.'.txt', 'rb');
$aLines = file($table.'.txt');
$count=0;
foreach($aLines as $sLine) {  
	$aCols = explode("\t", $sLine);
	if($count==0){$NomCols=$aCols;}
	if($count>0){
		for($i=0;$i<count($aCols);$i++){
			$fields.=$NomCols[$i].',';
			$val.="'".str_replace("'","''",trim($aCols[$i]))."',";
		}
	
		$sql= "insert into ".$table." (".substr($fields,0,-1).") values(".substr($val,0,-1).")";
		$res=$db->insert($sql);
		echo $res.'<br>';
		$fields='';$val='';
	}	
	$count++;
}



?>
