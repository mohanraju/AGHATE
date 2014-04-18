<?php
/*
#########################################################################################
		ProjetMSI
		Module Outil MSI
		Recherche sejours par NIP
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/
//commun include pour les modules outil MSI
require("../../user/session_check.php");

//-------------------------------------------------------------------------
// 		Vérifiction du site declared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> OUTILMSI::Erreur acces , Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site"];
}
//=================================================================================
// script s d'inclusion
//=================================================================================

include("../../config/config.php");
include("../../config/config_".strtolower($site).".php"); 
require("../../commun/include/CommonFonctions.php");
include("../../commun/include/ClassGilda.php");


$nip=trim($_GET['NIP']);
 
$ComFunc =new CommonFunctions();
$Gilda =new Gilda($ConnexionStringGILDA);
 

/*############################################################################
// pas de resultat  + NIP  
// SI  ($nbr_sej< 1) + NIP  
##############################################################################*/ 
if(strlen($nip)==10)  
{
	$condition_applique=4;
	$sql="select NIP, NDA, TYPE,to_char(DE,'DD/MM/YYYY') as DE,   UH ,libuh as LIB_UH, ME as MODE_ENT, TYPMAJ,DTMAJ, NOHJO, DE as DTTRI,HH_ENT
	 			FROM
	        (
	            SELECT distinct(dos.noda) as nda,dos.noip as NIP, dos.tydos as TYPE, mvt.DAMVAD as DE, HHMVAD as HH_ENT,mvt.NOUF as UH,
	                        UFM.LBUF as libUH,TYMVAD as ME,tymaj as TYPMAJ, MVT.DADEMJ as DTMAJ, 0 as NOHJO
	            FROM DOS,MVT,UFM
	            WHERE MVT.NODA = DOS.NODA
	            AND DOS.NOIP  ='$nip'
			 			  AND (tydos 		='A' or tydos ='N')
	            AND tymaj 		<> 'D'
	            AND ufm.nouf 	= mvt.nouf
	            AND mvt.DAMVAD between UFM.DDVALI and UFM.DFVALI+1
	            UNION
	            SELECT distinct(dos.noda) as nda,dos.noip as NIP, dos.tydos as TYPE, hjo.DAEXEC as DE,'0000' as HH_ENT,
	                        hjo.NOUFEX as UH,UFM.LBUF as libUH,dos.MDENTR as ME,HJO.tymaj as TYPMAJ,HJO.DADEMJ as DTMAJ, NOHJO
	            FROM DOS,HJO,UFM
	            WHERE DOS.NODA 	= HJO.NODA
	            AND DOS.NOIP  	='$nip'
	            AND tydos 			<>'A'
	            AND tydos 			<>'N'
	            AND HJO.tymaj 	<> 'D'
	            AND ufm.nouf 		= hjo.noufex
	            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
	        )
	    	ORDER BY NDA,DTTRI,HH_ENT,MODE_ENT ";
				//echo $sql;    	
	$result=$Gilda->OraSelect($sql);
	$nbr_sej=count($result);	
	
}
 
/*############################################################################
// 
##############################################################################*/
//echo "<pre>";
//print_r($result);

$last_nda=""; 
for($i=0;$i < $nbr_sej; $i++)
{
	//row color handling
	if($last_nda !=$result[$i]['NDA']){
		$bgcolor =($bgcolor=='info'?'success':'info');
		$last_nda =$result[$i]['NDA'];
		if($i > 0)
				$row.="<tr><td colspan='6'  style='height:5px;text-align:center'>  &nbsp;</td></tr>";
	}
	
	$dasor=$result[$i+1]['DE'];
	if($result[$i]['TYPE']=='S')
		$dasor=$result[$i]['DE'];
  if ($result[$i]['MODE_ENT'] != 'SH')
	{
		$row .= "<tr class='".$bgcolor."'>";
		$row .= "<td>".$result[$i]['NDA']."</td>";
		$row .= "<td>".$result[$i]['MODE_ENT']."</td>";
		$row .= "<td>".$result[$i]['TYPE']."</td>";
		$row .= "<td>".$result[$i]['UH']." - ".$result[$i]['LIB_UH']."</td>";
		$row .= "<td>".$result[$i]['DE']."</td>";
		$row .= "<td>".$dasor."</td>";	
		$row .= "</tr>";
	}
}
if ($nbr_sej < 1)
	$row="<tr><td colspan='6' align='center'>( NIP : ".$nip." )  Aucun sejour trouvé</td></tr>";

echo $row; 	
?>
