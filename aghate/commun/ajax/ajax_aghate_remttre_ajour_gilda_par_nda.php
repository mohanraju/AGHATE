<?php
/*
 * ajax_aghate_remttre_ajour_gilda_par_nda.php
 * 
 * Gestion des Annulations GLIDA
 * Réverification les séjours de données administratif 
 * puis corrigé dans AGHATE
 * 
 * modifie par mohanraju le 07/04/2014
 * 
*/

include "../../resume_session.php";
include "../../config/config.php";
include "../../config/config_".$site.".php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/ClassGilda.php";
include "../../commun/include/CommonFonctions.php";

$mysql 		= new MySQL();
$Aghate 	= new Aghate($table_loc);
$Gilda		= new Gilda($ConnexionStringGILDA);
$CommonFonctions = new CommonFunctions(true);


//-----------------------------------------------------------------------------
// Session related functions
//-----------------------------------------------------------------------------
if (strlen($_SESSION['login']) < 1) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};

//echo "<pre>";
$sej_aghate	=	$Aghate->GetSejoursParNda($nda);
$sej_gilda	=	$Gilda->GetSejourInfoParNda($nda);
// print_r($sej_aghate);
//print_r($sej_gilda);
 
$NbrAghate 	=count($sej_aghate);
$NbrGilda	=count($sej_gilda);
//=============================================================
// boucle sur les sej GILDA mettre a jour fin_date
//=============================================================
for($g=0; $g < $NbrGilda; $g++)
{
	$EntGilda =$sej_gilda[$g]['NOUF'].$sej_gilda[$g]['DTMVAD']." ".$sej_gilda[$g]['HHMVAD'];
	$SorGilda =$sej_gilda[$g+1]['DTMVAD']." ".$sej_gilda[$g+1]['HHMVAD'];
 

	// prepare unix strt_time et unix end_time à partir de GILDA
	$_time=str_replace(":","",$sej_gilda[$g]['HHMVAD']) ;		
	$gilda_start_time	= $Aghate->MakeDate($sej_gilda[$g]['DTMVAD'],$_time);	
	
	// make gilda end_time s'exists
	if(strlen($sej_gilda[$g+1]['DTMVAD']) > 1){
		$_time=str_replace(":","",$sej_gilda[$g+1]['HHMVAD']) ;		
		$gilda_end_time	= $Aghate->MakeDate($sej_gilda[$g+1]['DTMVAD'],$_time);	
	}else{
		continue;	
	}
	
	echo "<br>   NDA:".$sej_gilda[$g]['NODA']." | UH: ".$sej_gilda[$g]['NOUF']." | DU :". $sej_gilda[$g]['DTMVAD']." ".$sej_gilda[$g]['HHMVAD']." AU ". $sej_gilda[$g]['DTMVAD']." ".$sej_gilda[$g]['HHMVAD'];
	// boucle sur les sej AGHATE pour mettre a jour fin_date 
	$Entry_trouve=false;
	for($a=0; $a < $NbrAghate; $a++)
	{
		$EntAghate =$sej_aghate[$a]['uh'].date("d/m/Y H:i",$sej_aghate[$a]['start_time']);
		$SorAghate =date("d/m/Y H:i",$sej_aghate[$a]['start_time']);
		echo "<br>  =>".$EntGilda ."==".$EntAghate  ."&& ".  $SorGilda ."!= ".$SorAghate;

		// UH+Start_time égal et edn_time is diffèrent, maj END_time
		//-------------------------------------------------------------------
		if(($EntGilda ==$EntAghate) && ($SorGilda != $SorAghate))
		{
			echo " || Mise a jour date_fin :".date('d/m/Y H:i',$gilda_end_time);
			// check lit occupé par autre patient, donc envoi le NIP a exclure
			$ChkPlaceLibre=$Aghate->IsPlaceLibre($sej_aghate[$a]['room_id'],$sej_aghate[$a]['start_time'],$gilda_end_time,$sej_aghate[$a]['noip']);

			// Check place libre
			if(count($ChkPlaceLibre) > 0)
			{
				// get coulior ID
				$RoomInfo=$Aghate->GetRoomInfoByRoomId ($sej_aghate[$a]['room_id']);
				$ServicePanierID=$Aghate->GetPanierIdByServiceId ($RoomInfo[0]['service_id']); 
				
				//deplace patient qui occupe le lit vers  couloir				
				$sql = "UPDATE ".$table_loc." set room_id='".$ServicePanierID."' WHERE id='".$ChkPlaceLibre[0]['id']."'";
				$Aghate->update($sql);
				$Aghate->AddTrace(" |(cdn:A1) Remttre a jour par Gilda est deplacé ce patient ");
				$msg_trace="Conflit 1-> Room->".$room_name." Patient -> ".$ChkPlaceLibre[0]['noip'];
				//$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$msg_trace,"TRACE_AUTOMATE");					
			} 
			//update start_time pour ce patient
			$sql="UPDATE ".$table_loc." set end_time='".$gilda_end_time."' WHERE id='".$sej_aghate[$a]['id']."'";
			$Aghate->update($sql);			
			$Aghate->AddTrace(" | end_time modifée par gilda, ".date('d/m/Y h:i:s',$date_deb));	
			$Entry_trouve=true;			
		}
		// UH+Start_time égal et edn_time is égal, entry ok
		//-------------------------------------------------------------------
		if(($EntGilda ==$EntAghate) && ($SorGilda == $SorAghate))
		{
			$Entry_trouve=true;
						
		}			
	}
	
	// fin de boucle et pas de sejours dans aghate on insert le ligne
	//---------------------------------------------------------------
	if(!$Entry_trouve)
	{
		echo " |Entry a inseré : ".$EntAghate. " ".$SorAghate;

		// get coulior ID
		$ServiceInfo = $Aghate->GetServiceInfoByUh(trim($sej_gilda[$g]['UH']));
 	
		$ServicePanierID=$Aghate->GetPanierIdByServiceId ($ServiceInfo['id']); 
						
		//Preparation du tableau pour l'insertion
		$Data['start_time']	=	$gilda_start_time;
		$Data['end_time']	=	$gilda_end_time;
		$Data['room_id']	= 	$ServicePanierID;
		$Data['create_by']	=	'Automate';
		$Data['noip']		=	$sej_gilda[$g]['NOIP'];
		$Data['nda']		=	$sej_gilda[$g]['NODA'];
		$Data['uh']			=	$sej_gilda[$g]['NOUF'];				 
		$Data['protocole']	=	'Protocole Automate';
		$Data['de_source'] 	= 	'Gilda'; // Gilda car la date d'entrée
		$Data['ds_source'] 	= 	'Gilda';
		$Data['gilda_id'] 	= 	$sej_gilda[$g]['NOIDMV'];
		$Data['statut_entry'] = 'Hospitalisé';
		
		// Pas de verification du place libre car on place dans  Insertion de la reservation
		$id = $Aghate->InsertConvocation($table_loc,$Data);

		
	}
}

// Check nombre  de sejours Gilda corespond s sejours Aghate
$sej_aghate	=	$Aghate->GetSejoursParNda($nda);
$NbrAghate 	=count($sej_aghate);
if($NbrAghate > $NbrGilda)
{
	// boucle sur res aghate et suprimme les doublons qui ne present pas dans gilda
	for ($a=0; $a < $NbrAghate; $a++)
	{
		if ( $sej_aghate[$a]['start_time']==$sej_aghate[$a+1]['start_time'] )
		{
			echo "Ligne a suprimmer row_id :".$sej_aghate[$a]['id'];
			$sql = "UPDATE ".$table_loc." set statut_entry='SUPPRIMER',create_by='".$_SESSION['login']."' WHERE id='".$sej_aghate[$a]['id']."'";
			$Aghate->update($sql);			
		}
	}
}
echo "|OK|"; 
?>
