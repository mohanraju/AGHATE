<?php
ini_set('display_errors',1);
include("../../config/config.php");
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
if (strlen($_GET['FormUpdate_VID']) >0){
	foreach($_GET as $key=>$value)
	{
		//if(!$_GET['FormUpdate_Nhjo'] ) $_GET['FormUpdate_Nhjo']=""
		$DetailSource['UPDATE']=str_replace($key,$value,$DetailSource['UPDATE']);
		$DetailSource['INACTIVE']=str_replace($key,$value,$DetailSource['INACTIVE']);
	}
	$DetailSource['UPDATE']=str_replace('undefined','',$DetailSource['UPDATE']);
	$DetailSource['INACTIVE']=str_replace('undefined','',$DetailSource['INACTIVE']);
	//echo $DetailSource['UPDATE'];

	$res=$db->update($DetailSource['INACTIVE']);
	
	$res=$db->insert($DetailSource['UPDATE']);
	echo $res;
}
else
{
	echo "KO";	
}	
?>
