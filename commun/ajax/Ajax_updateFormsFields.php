<?php
ini_set('display_errors',1);
include("../../config/config.php");
include("../../config/config_".$site.".php"); 
include("../../commun/include/ClassMysql.php");
$FichierRef = '../../commun/sources/'.$_GET['Source'].'.source';
$fp = fopen($FichierRef, 'rb');
$aLines = file($FichierRef);
$count=0;
$DetailSource=array();
foreach($aLines as $sLine) {
	if(substr($sLine,0,1)!='#'){
		$aCols = explode(":", $sLine);
		$DetailSource[$aCols[0]]=$aCols[1];
	}
}
$db=new MySQL();
if (!$_GET['FormUpdate_Champs']){$_GET['FormUpdate_Champs']='valid';$_GET['FormUpdate_DataChamps']='D';}
	
foreach($_GET as $key=>$value)
{
	//if(!$_GET['FormUpdate_Nhjo'] ) $_GET['FormUpdate_Nhjo']=""
	$DetailSource['UPDATECHAMPS']=str_replace($key,$value,$DetailSource['UPDATECHAMPS']);
}

$DetailSource['UPDATECHAMPS']=str_replace('undefined',"''",$DetailSource['UPDATECHAMPS']);

	//echo $DetailSource['UPDATE'];
	//echo $DetailSource['TYPEDATA'];
	if($DetailSource['TYPEDATA']=='colonne')echo "toto";
	$res=$db->update($DetailSource['UPDATECHAMPS']);
	echo "<br>".$DetailSource['UPDATECHAMPS'];
	echo $res;

?>
