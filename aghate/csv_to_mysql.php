<?php
include "./commun/include/ClassCsv2Mysql.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";

$DBName = "gilda";

// tester exportfile modifier la base dans config.php 

$mysql= new MySQL();
$export = new Csv2Mysql();

$debut = time();
	
	$TableauIndex[0] = 'NOIP';
	
	$Tablename='idp';
	$mysql->drop_table($Tablename,$DBName);
	$currentfile = "gilda/idp.csv";		
	$export->ExportFile($currentfile,$Tablename,$mysql);
	$mysql->add_index($Tablename,$TableauIndex);
	
/*	$Tablename='mvt';
	$mysql->drop_table($Tablename,$DBName);
	$currentfile = "gilda/bkup/31_10_2013/mvt_14_02.csv";		
	$export->ExportFile($currentfile,$Tablename,$mysql);
	$mysql->add_index($Tablename,$TableauIndex);

	*/
	/*$Tablename='mvt';
	$date = "2013-10-16";
	$currentfile = "gilda/input/".$Tablename.".csv";		
	$export->ExportFile($currentfile,$Tablename,$mysql);*/
	
$fin = time();
$result = $fin - $debut;

echo "<br />".gmdate("H:i:s", $result);



?>
