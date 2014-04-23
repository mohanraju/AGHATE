<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
############################################################################################
*/
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
 
//==========================================================================================
// scripts d'inclusion
//==========================================================================================
include("../../config/config.php");
include("../../config/config_$site.php"); 
require("../include/CommonFonctions.php");
include("../include/ClassGilda.php");

//Objet init
$Functions = new CommonFunctions(true);
$Gilda=new Gilda($ConnexionStringGILDA);

// 	preparation de requettes
//	===================================================================
//echo "toto ".$_GET['term'];
// escape your parameters to prevent sql injection
$val_rech   = utf8_decode($_GET['term']);
$LESCHAMPS="PAT.NOIP as NIP,PAT.NMMAL as NOM,PAT.NMPMAL as PRENOM,to_char(PAT.DANAIS,'DD/MM/YYYY') as DANAIS,PAT.CDSEXM as SEXE,NOTLDO";
// 	si au moins 3 char de nom renseingné
if(strlen($val_rech) > 2)
{
	if($Functions->IsNumber($val_rech))
	{
		//Taille 9 numeric=NDA
		if(strlen($val_rech)==9){
			$SQL= " SELECT $LESCHAMPS FROM PAT,DOS WHERE DOS.NOIP=PAT.NOIP AND DOS.NODA ='".trim($val_rech)."' ";	
			$desc_rech="NDA";
		//Taille 10 numeric=NIP
		}else if(strlen($val_rech)==10)
		{
			$desc_rech="NIP";
			$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NOIP =('".trim($val_rech)."') ";	
		}else{ 
			$desc_rech="NOM";
			$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".strtoupper(trim($val_rech))."%') ORDER BY NMMAL,NMPMAL";	
		}
	}
	else
	{
		// non numeric est NOM
		$desc_rech="NOM";
		$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".strtoupper(trim($val_rech))."%') ORDER BY NMMAL,NMPMAL";	
	}
}

if (strlen($SQL) > 1)
{
	//execute SQL
	$Result=$Gilda->OraSelect($SQL);
}

$nb_rec=count($Result);

$ret_vals = array();




//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$lib=$Result[$i]["NOM"]." ".$Result[$i]["PRENOM"]." (".$Result[$i]["NIP"].") (".$Result[$i]["DANAIS"].") (" .$Result[$i]["SEXE"].") (tel:".$Result[$i]["NOTLDO"].")";
	$id=$Result[$i]["NIP"];
	
	//prepare Tableau	
	$ret_vals[] = array(
        				'value' => utf8_encode($lib),
        				'id'    => $id,
    						);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>
