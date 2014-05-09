<?php
/*
#########################################################################################
		ProjetMSI
		Module Forms
		Update NDA
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation : 11/04/2014
		Date dernière modif 
*/
//commun include pour les modules outil MSI
 
 
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------

include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassForms.php");
require("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassHtml.php");

//$InfoMVT['NDA']='761401224';
//$InfoMVT['UH']='114';

//Objet init
$com=new CommonFunctions(true);
$Html=new Html();
$Forms=new Forms($site);
$Forms->Html=$Html;
$res=$Forms->updateNda($TempNDA,$InfoMVT);

?>
