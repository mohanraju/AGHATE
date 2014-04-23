<?php
/*
#########################################################################################
		ProjetMSI
		Module Resa
		Recherche Patient
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 23/01/2014
*/
//commun include pour les modules outil MSI
 
 
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------

include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassAghate.php");
require("../../commun/include/CommonFonctions.php");

//Objet init
$Aghate=new Aghate();
$Aghate->NomTableLoc=$table_loc;
$res=$Aghate->UpdateDescriptionFromId ($FormUpdate_VID,$FormUpdate_Var,$FormUpdate_Val,$FormUpdate_Libelle);

?>
