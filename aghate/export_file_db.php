<?php

/*
 * export_file_db.php 
 * Reproduit la table LOC en local
 * (LOC+PAT)
 * Supprimer et recrée a chaque fois
*/

include "./commun/include/ClassCsv2Mysql.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";

$DBName = "aghate_test";


$mysql= new MySQL();
$export = new Csv2Mysql();

$debut = time();

//$TableauIndex[0] = 'NOIP';

$Tablename='agt_room_exp';
//$mysql->drop_table($Tablename,$DBName);
$currentfile = "./LitsNecker_ok.csv";		
$export->ExportFile($currentfile,$Tablename,$mysql);
//$mysql->add_index($Tablename,$TableauIndex);

/*$Tablename='mvt';
$mysql->drop_table($Tablename,$DBName);
$currentfile = "gilda/".$Tablename.".csv";		
$export->ExportFile($currentfile,$Tablename,$mysql);
$mysql->add_index($Tablename,$TableauIndex);*/

/*$DBTablename='loc_historique';
$FileName = 'loc';
$currentfile = "gilda/".$FileName.".csv";		
$export->ExportFile($currentfile,$DBTablename,$mysql);
*/

$fin = time();
$result = $fin - $debut;

echo "<br />".gmdate("H:i:s", $result);


?>
