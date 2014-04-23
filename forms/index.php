<?php
session_start();
include("../config/config.php");
include("../config/config_".$site.".php"); 
require("../commun/include/ClassMysql.php");
include("../commun/include/ClassHtml.php");
include("../commun/include/ClassForms.php");
include("../commun/include/CommonFonctions.php");
include("../commun/include/ClassSimpa.php"); 
include("../commun/include/ClassGilda.php"); 




$com=new CommonFunctions();
$Html=new Html();
$Forms=new Forms($site);
$Forms->Html=$Html;
$Simpa = new Simpa($ConnexionStringSIMPA);
$Forms->Simpa=$Simpa;
include("../commun/layout/header.php");
include("../commun/layout/menu.php");
echo  ' <link href="../../commun/styles/bootstrap_form.css" rel="stylesheet">  ';
$FichierProjet = "DescriptifProjet";
$DP=$Forms->GetDescriptifProjet($FichierProjet);

if (isset($_GET['HtmlTest']))$Forms->HtmlTest($DP);
if (isset($_GET['CreateDB']))$Forms->CreateTable($DP);
if (isset($_GET['PrintForm'])){
	//if(isset($_GET['vid']))$Forms->PrintForm($DP,_GET['vid']);
	//if(!isset($_GET['vid']))$Forms->GetFormId($DP);
	$Forms->PrintForm($DP,$VID=1);
}
if (isset($_GET['InsertTable']))$Forms->InsertTable($DP);


include("../commun/menu/footer.php");
?>
