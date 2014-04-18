<?php
/*
#########################################################################################
		ProjetMSI
		Module Resa
		Recherche Patient
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation 
		Date derni? modif : 15/01/2014
*/
 
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------
header('Content-Type: text/html; charset=utf-8');
header('Content-Type: application/json');
include("../../config/config.php");
include("../../config/config_".$site.".php"); 
require("../include/CommonFonctions.php");
include("../include/ClassGilda.php");

//Objet init
$Functions = new CommonFunctions(true);
$Gilda=new Gilda($ConnexionStringGILDA);

// 	preparation de requettes
//	===================================================================
$nbr_rec=0;
$val_rech=strtoupper(trim($val_rech));
$LESCHAMPS="PAT.NOIP as NIP,PAT.NMMAL as NOM,PAT.NMPMAL as PRENOM,to_char(PAT.DANAIS,'DD/MM/YYYY') as DANAIS,PAT.CDSEXM as SEXE,NOTLDO";

// 	si au moins 3 char de nom renseingn?
if(strlen($val_rech) > 2)

{
	$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".$val_rech."%') OR PAT.NOIP LIKE('".$val_rech."%') ORDER BY NMMAL,NMPMAL";	
	$Result=$Gilda->OraSelect($SQL);
	$nbr_rec=count($Result);
	//echo $SQL;
}
if ($nbr_rec > 0){
	echo json_encode($Result);	
}
else{
	echo "[{'ERR':'Aucune donnee trouvee'}]";
}
