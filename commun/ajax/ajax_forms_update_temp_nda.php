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

include_once("../../config/config.php");
require_once("../../commun/include/ClassMysql.php");
require_once("../../commun/include/ClassForms.php");
require_once("../../commun/include/CommonFonctions.php");

//$InfoMVT['NDA']='761401224';
//$InfoMVT['UH']='114';
echo "<pre>";
echo $TempNDA;
print_r($InfoMVT);
//Objet init
if(strlen($TempNDA) > 0)
{
	$com=new CommonFunctions(true);
	$Forms=new Forms($site);
	$res=$Forms->updateNda($TempNDA,$InfoMVT);
}
?>