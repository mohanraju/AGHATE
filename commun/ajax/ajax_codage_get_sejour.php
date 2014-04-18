<?php
/*
##########################################################################################
	Projet CODAGE
	Get séjours par NIP ou NDA
	Script appelé dans la page "ajax_codage_get_sejour.js"
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
		$nip=$_GET['NIP'];
		$nda=$_GET['NDA'];
		$DT_NAIS=$_GET['DDN'];	Pour calculer l'age du patient		
		$uh=$_GET['UH'];
		$nohjo=$_GET['NOHJO'];
*/

require("../../user/session_check.php");
//-------------------------------------------------------------------------
// 		Vérifiaction du site déclared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur accès, Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
	//echo $_SESSION["site"];
}

$nip=$_GET['NIP'];
$nda=$_GET['NDA'];
$DT_NAIS=$_GET['DDN'];			
$uh=$_GET['UH'];
$nohjo=$_GET['NOHJO'];
include("../../config/config_".$site.".php");
require_once("../include/CommonFonctions.php");
require_once('../include/ClassGilda.php');
$ComFunc =new CommonFunctions();
$Gilda =new Gilda($ConnexionStringGILDA);
 
$nbr_sej=0; //initialisation var. nombre de résultat retournées 
$condition_applique=0; // pour vérifier le qry apppliqé
/*############################################################################
// PAR NOHJO, on récupare le séjour par cet unique controle
// CAS uniquement EN HDJ
##############################################################################*/ 
if(strlen($nohjo) > 1)
{
	$condition_applique=1;
	$sql="select NIP, NDA, TYPE,to_char(de,'DD/MM/YYYY') as DAMVAD, ue as UH ,
 			libuh as LIB_UH, me as MODE_ENT, to_char(df,'DD/MM/YYYY') as DATE_SOR , mf as MODE_SOR ,TYPMAJ,DTMAJ, NOHJO, de as DTEENT
 			FROM
        (
            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE,
                   hjo.NOUFEX as UE,UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF ,HJO.tymaj as TYPMAJ,     HJO.DADEMJ as DTMAJ, NOHJO
            FROM DOS,HJO,UFM
            WHERE DOS.NODA = HJO.NODA
            AND nohjo  ='$nohjo'
            AND tydos <>'A'
            AND tydos <>'N'
            AND HJO.tymaj <> 'D'
            AND ufm.nouf = hjo.noufex
            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
        )
    	ORDER BY DTEENT DESC";
  //echo $sql;  	
	$data=$Gilda->OraSelect($sql);
	$nbr_sej=count($data);	
	
	
	
}
/*############################################################################
// pas de resultat  + NIP + NDA + UH 
// SI  ($nbr_sej< 1) + NIP + NDA + UH 
// control sur HDJ et HC
##############################################################################*/ 
/*if( ($nbr_sej < 1) &&(strlen($nip)==10) && (strlen($nda)==9) && (strlen($uh) > 2)  )
{
	$condition_applique=2;
					
	$sql="select NIP, NDA, TYPE,to_char(de,'DD/MM/YYYY') as DAMVAD, ue as UH ,
	 			libuh as LIB_UH, me as MODE_ENT, to_char(df,'DD/MM/YYYY') as DATE_SOR , mf as MODE_SOR ,TYPMAJ,DTMAJ, NOHJO, de as DTEENT
	 			FROM
	        (
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, mvt.DAMVAD as DE,mvt.NOUF as UE,
	                        UFM.LBUF as libUH,TYMVAD as ME,dos.DASOR as DF,dos.MDSOR as MF ,tymaj as TYPMAJ  ,    MVT.DADEMJ as DTMAJ, 0 as NOHJO
	            FROM DOS,MVT,UFM
	            WHERE MVT.NODA = DOS.NODA
	            AND DOS.NOIP  ='$nip'
	            AND DOS.NODA  	='$nda'
							AND MVT.NOUF  ='$uh'	            
	            AND TYMVAD 		<> 'DP'
	            AND TYMVAD 		<> 'RP'
			 			  AND (tydos 		='A' or tydos ='N')
	            AND tymaj 		<> 'D'
	            AND ufm.nouf 	= mvt.nouf
	            AND mvt.DAMVAD between UFM.DDVALI and UFM.DFVALI+1
	            UNION
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE,
	                        hjo.NOUFEX as UE,UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF ,HJO.tymaj as TYPMAJ,     HJO.DADEMJ as DTMAJ, NOHJO
	            FROM DOS,HJO,UFM
	            WHERE DOS.NODA 	= HJO.NODA
	            AND DOS.NOIP  	='$nip'
	            AND DOS.NODA  		='$nda'
							AND HJO.NOUFEX		='$uh'	            
	            AND tydos 			<>'A'
	            AND tydos 			<>'N'
	            AND HJO.tymaj 	<> 'D'
	            AND ufm.nouf 		= hjo.noufex
	            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
	        )
	    	ORDER BY NDA DESC";
	    	echo $sql;    	
	$data=$Gilda->OraSelect($sql);
	$nbr_sej=count($data);
	
}*/

/*############################################################################
// pas de resultat  + NIP + NDA  
// SI  ($nbr_sej< 1) + NIP + NDA  
##############################################################################*/ 
if( ($nbr_sej < 1) &&(strlen($nip)==10) && (strlen($nda)==9)  )
{
	$condition_applique=3;
	$sql="select NIP, NDA, TYPE,to_char(de,'DD/MM/YYYY') as DAMVAD, ue as UH , 'toto' as TOTO, HHENT,
	 			libuh as LIB_UH, me as MODE_ENT, to_char(df,'DD/MM/YYYY') as DATE_SOR , mf as MODE_SOR ,TYPMAJ,DTMAJ, NOHJO, de as DTEENT
	 			FROM
	        (
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, mvt.DAMVAD as DE,mvt.NOUF as UE, HHMVAD as HHENT,
	                        UFM.LBUF as libUH,TYMVAD as ME,dos.DASOR as DF,dos.MDSOR as MF ,tymaj as TYPMAJ  ,    MVT.DADEMJ as DTMAJ, 0 as NOHJO
	            FROM DOS,MVT,UFM
	            WHERE MVT.NODA = DOS.NODA
	            AND DOS.NOIP  ='$nip'
	            AND DOS.NODA  	='$nda'
	            AND TYMVAD 		<> 'DP'
	            AND TYMVAD 		<> 'RP'
			 			  AND (tydos 		='A' or tydos ='N')
	            AND tymaj 		<> 'D'
	            AND ufm.nouf 	= mvt.nouf
	            AND mvt.DAMVAD between UFM.DDVALI and UFM.DFVALI+1
	            UNION
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE, hjo.NOUFEX as UE, '0000' as HHENT,
	                        UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF ,HJO.tymaj as TYPMAJ,     HJO.DADEMJ as DTMAJ, NOHJO
	            FROM DOS,HJO,UFM
	            WHERE DOS.NODA 	= HJO.NODA
	            AND DOS.NOIP  	='$nip'
	            AND DOS.NODA  		='$nda'
	            AND tydos 			<>'A'
	            AND tydos 			<>'N'
	            AND HJO.tymaj 	<> 'D'
	            AND ufm.nouf 		= hjo.noufex
	            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
	        )
	    	ORDER BY DTEENT, HHENT ASC";
	    	//echo $sql;    	
	$data=$Gilda->OraSelect($sql);
	$nbr_sej=count($data);
	
	
}

/*############################################################################
// pas de resultat  + NIP  
// SI  ($nbr_sej< 1) + NIP  
##############################################################################*/ 
if( ($nbr_sej < 1) && (strlen($nip)==10)  )
{
	$condition_applique=4;
	$sql="select NIP, NDA, TYPE,to_char(de,'DD/MM/YYYY')  as DAMVAD, ue as UH ,HHENT,
	 			libuh as LIB_UH, me as MODE_ENT, to_char(df,'DD/MM/YYYY') as DATE_SOR , mf as MODE_SOR ,TYPMAJ,DTMAJ, NOHJO, de as DTEENT
	 			FROM
	        (
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, mvt.DAMVAD as DE,mvt.NOUF as UE, HHMVAD as HHENT,
	                        UFM.LBUF as libUH,TYMVAD as ME,dos.DASOR as DF,dos.MDSOR as MF ,tymaj as TYPMAJ  ,    MVT.DADEMJ as DTMAJ, 0 as NOHJO
	            FROM DOS,MVT,UFM
	            WHERE MVT.NODA = DOS.NODA
	            AND DOS.NOIP  ='$nip'
	            AND TYMVAD 		<> 'DP'
	            AND TYMVAD 		<> 'RP'
			 			  AND (tydos 		='A' or tydos ='N')
	            AND tymaj 		<> 'D'
	            AND ufm.nouf 	= mvt.nouf
	            AND mvt.DAMVAD between UFM.DDVALI and UFM.DFVALI+1
	            UNION
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE, hjo.NOUFEX as UE, '0000' as HHENT,
	                       UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF ,HJO.tymaj as TYPMAJ,     HJO.DADEMJ as DTMAJ, NOHJO
	            FROM DOS,HJO,UFM
	            WHERE DOS.NODA 	= HJO.NODA
	            AND DOS.NOIP  	='$nip'
	            AND tydos 			<>'A'
	            AND tydos 			<>'N'
	            AND HJO.tymaj 	<> 'D'
	            AND ufm.nouf 		= hjo.noufex
	            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
	        )
	    	ORDER BY DTEENT, HHENT ASC";
				//echo $sql;    	
	$data=$Gilda->OraSelect($sql);
	$nbr_sej=count($data);	
	
}

/*############################################################################
// pas de resultat  + NDA  
// SI  ($nbr_sej< 1) + NDA  
##############################################################################*/ 
if( ($nbr_sej < 1) && (strlen($nda)==9) && ($nip =="" )   )
{

	$condition_applique=5;
	$sql="select NIP, NDA, TYPE,to_char(de,'DD/MM/YYYY') as DAMVAD, ue as UH ,HHENT,
	 			libuh as LIB_UH, me as MODE_ENT, to_char(df,'DD/MM/YYYY') as DATE_SOR , mf as MODE_SOR ,TYPMAJ,DTMAJ, NOHJO, de as DTEENT
	 			FROM
	        (
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, mvt.DAMVAD as DE,mvt.NOUF as UE, HHMVAD as HHENT,
	                        UFM.LBUF as libUH,TYMVAD as ME,dos.DASOR as DF,dos.MDSOR as MF ,tymaj as TYPMAJ  ,    MVT.DADEMJ as DTMAJ, 0 as NOHJO
	            FROM DOS,MVT,UFM
	            WHERE MVT.NODA = DOS.NODA
	            AND DOS.NODA  		='$nda'
	            AND TYMVAD 		<> 'DP'
	            AND TYMVAD 		<> 'RP'
			 			  AND (tydos 		='A' or tydos ='N')
	            AND tymaj 		<> 'D'
	            AND ufm.nouf 	= mvt.nouf
	            AND mvt.DAMVAD between UFM.DDVALI and UFM.DFVALI+1
	            UNION
	            SELECT distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE, hjo.NOUFEX as UE, '0000' as HHENT,
	                       UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF ,HJO.tymaj as TYPMAJ,     HJO.DADEMJ as DTMAJ, NOHJO
	            FROM DOS,HJO,UFM
	            WHERE DOS.NODA 	= HJO.NODA
	            AND DOS.NODA  		='$nda'
	            AND tydos 			<>'A'
	            AND tydos 			<>'N'
	            AND HJO.tymaj 	<> 'D'
	            AND ufm.nouf 		= hjo.noufex
	            AND hjo.DAEXEC between UFM.DDVALI and UFM.DFVALI+1
	        )
	    	ORDER BY DTEENT, HHENT ASC";
				//echo $sql;
	$data=$Gilda->OraSelect($sql);
	$nbr_sej=count($data);	
	
}
/*############################################################################
// SI  ($nbr_sej> 1) les resultat seront envoyé sous forme de tableau
##############################################################################*/
//echo "<br>Cond:".$condition_applique;
//echo "Date entre ".$data[0]['DAMVAD'];
echo '<table id="TblSejours" width="100%" border="0" cellspacing="0" cellpadding="0" align="left">';
// ajouté par mohan le 19/12/2013 pour gerer les "SH" dans le compteur de $nbr_sej
$nbr_sh=0;
if ($nbr_sej > 1){
  for($i=0;$i < $nbr_sej;$i++){
		$NIP=$data[$i]['NIP'];
  	$NDA=$data[$i]['NDA'];
		$DAMVAD=$data[$i]['DAMVAD'];
		$MODE_ENT=$data[$i]['MODE_ENT'];
		$TYDOS=$data[$i]['TYPE'];
		$UH=$data[$i]['UH'];
		$LBUF=$data[$i]['LIB_UH'];
		$AGE=$ComFunc->CalculAge($DT_NAIS,$DAMVAD);
		$NOHJO=$data[$i]['NOHJO'];
		if($TYDOS == "A" || $TYDOS == "N")
			$DASOR=$data[$i+1]['DAMVAD'];
		else
			$DASOR=$DAMVAD;
		
		$Jours=$ComFunc->JoursBetween2Dates($DASOR,$DAMVAD);
		
		if($MODE_ENT != "SH")
		{
			//include("../commun/ajax/ajax_codage_getdatesortie.php?nda=$NDA&uh=$UH&dtent=$DAMVAD&tydos=$TYDOS");
			//echo "toto ".$DASOR;
			//exit;
	
			$MyVar=$NDA."|".$AGE."|".$DAMVAD."|".$UH."|".$LBUF."|".$DASOR."|".$Jours."|".$NOHJO;
			
			$toto="&nbsp;";
			$corps.= '
			<tr id="ROWID"	name="ROWID'.$i.'"  MyVar="'.$MyVar.'"	>
		    <td>'.$NDA.'</td>
		    <td>'.$DAMVAD.' au '.$DASOR.'</td>
		    <td>'.$UH.':'.$LBUF.'</td>
		  </tr>
		 ';
		 
			if(($nip==$NIP) && ($nda==$NDA) && ($uh==$UH))
			{
			?>
				<script type="text/javascript">
					CheckSelection('<?php	print $MyVar;?>');
					//$("#TblSejours").find("name:first").after(ligne);
					//$("tr[name=ROWID<?php	print $i;?>]").css({"background-color":"#D0D8EB","font-weight":"bold"});
					$("tr[name=ROWID<?php	print $i;?>]").css({"background-color":"#D0D8EB","font-weight":"bold"});
					//$("#ROWID").css({"background-color":"#D0D8EB","font-weight":"bold"});
				</script>
			<?php
			}
		}else
		{
			// ajouté par mohan le 19/12/2013 pour gerer les "SH" dans le compteur de $nbr_sej
			$nbr_sh++;
		}
  } 
  echo $corps;
}
elseif($nbr_sej == 1){
?>
           
<?php		 	
	$i=0;

	$NDA=$data[$i]['NDA'];
	$DAMVAD=$data[$i]['DAMVAD'];
	$MODE_ENT=$data[$i]['MODE_ENT'];
	$TYDOS=$data[$i]['TYPE'];
	$UH=$data[$i]['UH'];
	$LBUF=$data[$i]['LIB_UH'];
	$AGE=$ComFunc->CalculAge($DT_NAIS,$DAMVAD);
	$NOHJO=$data[$i]['NOHJO'];
	if($TYDOS == "A" || $TYDOS == "N")
			$DASOR=$data[$i+1]['DAMVAD'];
		else
			$DASOR=$DAMVAD;

	$Jours=$ComFunc->JoursBetween2Dates($DASOR,$DAMVAD);

	if($MODE_ENT != "SH")
	{
		$MyVar=$NDA."|".$AGE."|".$DAMVAD."|".$UH."|".$LBUF."|".$DASOR."|".$Jours."|".$NOHJO;

	?>
			<tr id="ROWID"  MyVar="<?php print $MyVar?>" >
		    <td><?Php  Print $DAMVAD.' au '.$DASOR;?></td>
		    <td><?Php  Print $UH.':'.$LBUF;?></td>
		  </tr>
	<?php
	}else
	{
		// ajouté par mohan le 19/12/2013 pour gerer les "SH" dans le compteur de $nbr_sej
		$nbr_sh++;
	}
}
else{
	echo '<tr><td colspan="4">Aucun séjour trouvé </td></tr>';
}
// ajouté par mohan le 19/12/2013 pour gerer les "SH" dans le compteur de $nbr_sej
$t_nbr_sej =$nbr_sej - $nbr_sh; // forcer sur un variable temp 
echo '<input type="hidden" id="nbr_sej" name="nbr_sej" value="'.$t_nbr_sej.'" />';
echo '<input type="hidden" id="MyVar" name="MyVar" value="'.$MyVar.'" />';
echo '</table>';
?>
