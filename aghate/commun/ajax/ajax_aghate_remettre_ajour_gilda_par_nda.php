<?Php  
/*
* PROJET AGHATE
* Ajax remettre a jour les sejours 
*
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
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
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/CommonFonctions.php";

$mysql 		= new MySQL();
$Aghate 	= new Aghate($table_loc);
$CommonFonctions = new CommonFunctions(true);
$TableName = 'agt_loc';



$sej_aghate	=	$Aghate->GetSejoursParNda($nda);

// loc backup 
$sej_backup=$Aghate->GetLocBackupParNda($nda);

$NbrAghate 	=count($sej_aghate);
$NbrBackup	=count($sej_backup);

//recupère les 	protocole et medecin avant de supprime
$protocole	=	$sej_aghate[0]['protocole'];
$medecin	=	$sej_aghate[0]['medecin'];
$id_prog	=	$sej_aghate[0]['id_prog'];
$type     	=	$sej_aghate[0]['type'];

// supprime les sejour dans aghate avant d'inserer
$Aghate->update("UPDATE agt_loc set statut_entry='SUPPRIMER' where nda='".$nda."'");

// boucle sur les sej LOC backup pour re inserer  les jour
for($g=0; $g < $NbrBackup; $g++)
{
 
	//les SH sont traité dans le boucle d'avant
	if ($sej_backup[$g]['TYMVT']=='SH') 
		continue;
	
	//get LIT et dt_entee	
	if( (strlen(trim($sej_backup[$g]['NOLIT'])) < 1) ) {
		$room_name 		= $Aghate->NomCouloir; 
		$dt_ent 		= $sej_backup[$g]['DTENT']; //de MVT
		$hh_ent 		= $sej_backup[$g]['HHENT']; //de MVT		
		$service_info = $Aghate->GetServiceInfoByUh(trim($sej_backup[$g]['UH']));		
	}
	else
	{
		$room_name 		= $sej_backup[$g]['NOLIT'];
		$dt_ent 		= $sej_backup[$g]['DDLOPT']; //de LOC
		$hh_ent 		= $sej_backup[$g]['HHLOPT']; //de LOC		
 
		//SI Neckar on localise par Nom du lit sinon par NoPoste
 	
		if($site=='001')
			$service_info = $Aghate->GetServiceInfoByRoomName(trim($sej_backup[$g]['NOLIT']));
		else	
			$service_info 	= $Aghate->GetServiceInfoByNoPost(trim($sej_backup[$g]['NOPOST']));
			
	}		
	if(count($service_info)< 1)
	{
		echo "|$nda ,Lit:".$sej_backup[$g]['NOLIT']." intouvable dans la structure|"; 	
		exit;
	}

	// prepare unix strt_time et unix end_time à partir de GILDA
	$starttime	= $Aghate->MakeDate($dt_ent,str_replace(":","",$hh_ent));	
 	
	// get date_sortie
	$temp= $g + 1;
	if($NbrBackup == $temp) // dernier boucle et pat en cours d'hospit force end_date to now()
	{
		$endtime	= time();	
		$ds_source  ='Automate';	
		
	}elseif(strlen(trim($sej_backup[$g+1]['NOLIT'])) < 1 )
	{	
	
		$dt_sor 	= $sej_backup[$g+1]['DTENT']; //de MVT
		$hh_sor 	= $sej_backup[$g+1]['HHENT']; //de MVT
		$endtime	= $Aghate->MakeDate($dt_sor,str_replace(":","",$hh_sor));
		$ds_source	='Gilda';	

	}else{

		$dt_sor 	= $sej_backup[$g+1]['DDLOPT']; //de LOC
		$hh_sor 	= $sej_backup[$g+1]['HHLOPT']; //de LOC	
		$endtime	= $Aghate->MakeDate($dt_sor,str_replace(":","",$hh_sor));
		$ds_source	='Gilda';	
	}


	$room_info 	= $Aghate->GetRoomInfo($room_name,$service_info['id']);// modif endroit de cette fonction 	
	$room_id 	= $room_info['id'];	
	
	//get panier id pour depalcer les patients vers panier en cas de manque de place
	if($room_name 	== $Aghate->NomCouloir)
		$ServicePanierID=$room_id;
	else
		$ServicePanierID=$Aghate->GetPanierIdByServiceId ($service_info['id']); 
		
	$noip		=	$sej_backup[$g]['NOIP'];
	$uh 		=	$sej_backup[$g]['UH'];
	$nda		=	$sej_backup[$g]['NDA'];

	// tableau RESERVATION info
	$TableauData = array();
	$TableauData['start_time'] 	= $starttime;
	$TableauData['end_time'] 	= $endtime;
	$TableauData['room_id'] 	= $room_id;
	$TableauData['create_by'] 	= $_SESSION['login'];
	$TableauData['type'] 		= $type;
	$TableauData['noip'] 		= $noip;
	$TableauData['uh'] 			= $uh;
	$TableauData['nda'] 		= $nda;	
	$TableauData['de_source'] 	= "Gilda";
	$TableauData['ds_source'] 	= $ds_source;
	$TableauData['medecin'] 	= $medecin;	
	$TableauData['protocole'] 	= $protocole;
	$TableauData['statut_entry'] 	= "Hospitalisé";	
	if (strlen($id_prog)>0)
		$TableauData['id_prog'] 	= $id_prog;

	// Check place libr sauf  le panier 
	if ($ServicePanierID != $room_id)
	{
		$ChkPlaceLibre = $Aghate->IsPlaceLibre($room_id,$starttime,$endtime);
		
		if (count($ChkPlaceLibre) > 0)
		{
			foreach($ChkPlaceLibre as $key )
			{
				//deplace patient qui occupe au couloir
				$sql="UPDATE agt_loc set 
							room_id='".$ServicePanierID."'
							WHERE id='".$key['id']."'";
				$Aghate->update($sql);	
		
				$alert="Conflit 4-> manque de palce,".$noip." occupe ce lit ".$room_name ;		
				$Aghate->AddTrace(" |".$alert);
				$Aghate->UpdateDescriptionFromId($key['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");
			}
		}
	}	
	// Insertion de la reservation
 	//print_r($TableauData);
	$id = $Aghate->InsertConvocation($TableName,$TableauData);

			
}
echo "|$nda :sejour a été mis à jour correctement|"; 
?>
