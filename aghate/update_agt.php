<?Php  
/*
* PROJET AGHATE
* Module Automate permet de syncroniser avec GILDA
* 
* @Mohanraju SBIM/SAINT LOUIS/APHP /Paris
* 
* date dernière modififation 14/05/2014
* 
*/

header('Content-Type: text/html; charset=utf-8');
set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);



// Include
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/ClassFileHandle.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/CommonFonctions.php";

 

$debut = time();
echo "<pre>";
// Initialisation
$Mysql = new MySQL();
$Aghate= new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Aghate->AffcheTraceSurEcran=true;
$Gilda= new Gilda($ConnexionStringGILDA);
$FileHandle= new FileHandle();
echo "<hr> Syncronisation gilda \n==>Lancé à ". date('d/m/Y H:i:s'). " \n <hr>";

/*=======================================================================
 * Etape 1 
 * Mettre a jour les annulations adns la journée  reconstrure les sejours 
 * ======================================================================
*/
$Aghate->AddTrace("\n ####  MAJ annulations ####\n"); 		
//recupares les annulation de GILDA.MVT avec le typaj D et date misea jour=$date
$sql="SELECT dos.noip,mvt.NODA as NDA,MVT.NOUF,MVT.TYMVAD,MVT.DAMVAD,MVT.HHMVAD,MVT.TYMAJ,MVT.DADEMJ,MVT.NOIDMV
		FROM mvt,dos
		WHERE  dos.noda=mvt.noda 
		AND DADEMJ between sysdate-1 and sysdate+1
		AND tymaj='D' order by mvt.NODA";

$AnnlGilda=$Gilda->OraSelect($sql);
 
$nbr_resume=count($AnnlGilda);
$last_nda="";

for($i=0; $i < $nbr_resume ;$i++)
{
	// verify e sejour est deja reconstrute
	$Chk=$Aghate->select("select * from  loc_backup  WHERE tymaj='D' AND NOIDMV = '".$AnnlGilda[$i]['NOIDMV']."'"); 
	if (count($Chk) < 0)
	{

		if($last_nda != $AnnlGilda[$i]['NDA'])
		{
			$Aghate->AddTrace("\nNDA:".$AnnlGilda[$i]['NDA']." séjour reconstruite");		
			// url
			$url= "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];		
			$url= str_replace(strrchr($url,"/"),"/",$url);			
			$result = file_get_contents($url."commun/ajax/ajax_aghate_remettre_ajour_gilda_par_nda.php?nda=".$AnnlGilda[$i]['NDA']."&table_loc=agt_loc");
			//echo "<br>".$result;
		}	
		$last_nda = $AnnlGilda[$i]['NDA'];
		
		//mettre a jour dans loc_bacup les annularions
		$sql_update="UPDATE loc_backup set
					tymaj='D' WHERE NOIDMV = '".$AnnlGilda[$i]['NOIDMV']."'"; 
		$NbrRecords=$Aghate->update($sql_update);	
	}else{
		$Aghate->AddTrace("\nNDA:".$AnnlGilda[$i]['NDA']." déja traité");				
	}
}

 
/*=======================================================================
 * Etape 2 
 * 	mettre a jour les SH du Gilda.MVT
 * 
 * ======================================================================
*/
 // Initialise le fichier de trace
$Aghate->init_trace_file ();

$DonneeGilda= $Gilda->GetSortiesParDate(date("d/m/Y"));
$FileHandle->Array2csv($DonneeGilda,"./trace/Sorie.".date("Y.m.d.h.i.s").".csv");

$Aghate->AddTrace("\n ####  Procedure mettre a jour DateFin ####\n==>Lancé à ". date('d/m/Y H:i:s'). " \n" ); 		

// les cols retourne :NOIP	NDA	DTSOR	HHSOR	UHSOR	TYSEJ	NOIDMV
$cpt_maj = 0;
$cpt_wait = 0;

$TableName = 'agt_loc';
$nb_i = count($DonneeGilda);
$Aghate->BackupSortie($DonneeGilda);

for($i=0;$i< $nb_i;$i++)
{
	$noip 		= $DonneeGilda[$i]['NOIP'];
	$uh   		= $DonneeGilda[$i]['UHSOR'];
	$nda  		= $DonneeGilda[$i]['NODA'];
	$end_time	=	$Aghate->MakeDate($DonneeGilda[$i]['DTSOR'],$DonneeGilda[$i]['HHSOR']);
	$Aghate->AddTrace("\nNIP:".$noip."| NDA:".$nda." | UH: ".$uh." | end_time :". $DonneeGilda[$i]['DTSOR']." ".$DonneeGilda[$i]['HHSOR']);
	// mettre a jour date fin sur tous les sejours par NDA et date_fin
	// modifié par mohan le 08/04/2014
	$sql_update="UPDATE ".$Aghate->NomTableLoc ." set
								end_time='".$end_time."',	
								ds_source='GILDA',
								statut_entry='Sortie'
								WHERE NDA='".$DonneeGilda[$i]['NODA']."' 
								AND end_time > '".$end_time."'"; 

	$NbrRecords=$Aghate->update($sql_update);

	$Aghate->AddTrace("|statut 'sorite' et end_time  MAJ on ".$NbrRecords." enregistrement(s)");	
 
	
}

$Aghate->AddTrace("\n ==> Fin à ". date('d/m/Y H:i:s'). " \n"); 		

 /*
 * ======================================================================
 * Etape 3
 * insert/maj LOC
 * Syncronisation with Gilda LOC+MVT vers Agt.loc
 * paramètre : $GildaLoc : tableau contenant les données de LOC
 * Insère les patients de LOC vers Aghate (agt_loc)
 * Met a jour les dates d'entrées et/ou les uh/nda
 * ======================================================================
*/ 

// Récupère les données de LOC  dans un tableau
$GildaLoc = $Gilda ->GetLocTab ();

//ecrire la tableau dans un fichier 
$FileHandle->Array2csv($GildaLoc,"./trace/LOC.".date("Y.m.d.h.i.s").".csv");
	
$Aghate->AddTrace("\n\n ##### Procedure InsertConvocation (Gilda=>Aghate) ####\n ==>Lance à ". date('d-m-Y H:i:s')."\n");

//backup loc, check presence si non insert par mohan le 31/03/2014
$Aghate->BackupLoc($GildaLoc);

$nb_loc = count ($GildaLoc);
$no_place = 0;
$cpt_updt = 0;
$cpt_in = 0;
$cpt_dbl = 0;
$cpt_forced = 0;
$nb_lit_abs = 0;
$pat_pres = 0;
$TableName = 'agt_loc';

// boucle sur les patients présent dans GILDA.LOC + MVT
for ($i=0; $i< $nb_loc ;$i++)
{
	$date_deb = "";
	$date_fin = "";
	$date_loc="";
	$heure_loc = "";
	$Err=false;
	$ErrMsg="";
	$type_mv = "";
	$MsgAlert="";
	$Aghate->AddTrace("\nNIP :".$GildaLoc[$i]['NOIP']. "| NDA:".$GildaLoc[$i]['NDA']);
	

	
	
	// definition ROOM_name et SERVICE NAME	
	// On verifie si lit existe (loc) si pas de lit on force Couloir avec l'uh 
	
	if( (strlen(trim($GildaLoc[$i]['NOLIT'])) < 1) ) {
		$room_name 		= $Aghate->NomCouloir; 	//courloir ou panier à delcater sur setting et recupare de settings 
		$nopost 			= ""; 	//------------------------------------------------------->
		$service_info = $Aghate->GetServiceInfoByUh(trim($GildaLoc[$i]['UH']));
		$MsgAlert			=	"Pas de lit physique dans Gilda";
		$date_loc 		= $GildaLoc[$i]['DTENT']; //de MVT
		$heure_loc 		= $GildaLoc[$i]['HHENT']; //de MVT		
	}
	else
	{
		$room_name 		= $GildaLoc[$i]['NOLIT'];
		$nopost 		= $GildaLoc[$i]['NOPOST'];
		$date_loc 		= $GildaLoc[$i]['DDLOPT']; //de LOC
		$heure_loc 		= $GildaLoc[$i]['HHLOPT']; //de LOC		
		//localisation des service par LIT ou NOposte
		//is Neckar on localise par Nom du lit 
		if($site=='001')
			$service_info = $Aghate->GetServiceInfoByRoomName(trim($GildaLoc[$i]['NOLIT']));
		else	
			$service_info 	= $Aghate->GetServiceInfoByNoPost($nopost);

	}	

	$type_mv 		= $GildaLoc[$i]['TYMVT'];
	$uh				= $GildaLoc[$i]['UH'];
	
	$Aghate->AddTrace("| uh : ".$uh."| tymvt : ".$type_mv."| lit :".$room_name . "| post_trt :".$nopost ."| date_mvt :".$date_loc." à ".$heure_loc);

	// check and insert le pat info dans le table grr.PAT
	$Aghate->InsertPatient($GildaLoc[$i]); 
	
	// controles sur les champs 
	// service obligatoire
	$service_id = $service_info['id']; 
	if (strlen($service_id) < 1)
	{
		$Aghate->AddTrace("ERR => Post Traitement =>".$GildaLoc[$i]['NOPOST']." NOLIT =>".$GildaLoc[$i]['NOLIT']." Service introuvable , maj jour structure neccessaire" );
		$Alerts[]="ERR => Post Traitement =>".$GildaLoc[$i]['NOPOST']." NOLIT =>".$GildaLoc[$i]['NOLIT']." Service introuvable , maj jour structure neccessaire" ;
		continue;
	}
				
	
	$room_info = $Aghate->GetRoomInfo($room_name,$service_id);// modif endroit de cette fonction 

	// on doit jamais room_id vide, au moins il doit trouve le couloir, sinon on sors
	if (strlen($room_info['id']) < 1 )
	{
		$Aghate->AddTrace("ERR => Le lit ".$room_name." n'existe pas dans la table agt_room, maj de la structure necessaire\n");
		$Alerts[]="ERR => Le lit ".$room_name." n'existe pas dans la table agt_room, maj de la structure necessaire\n";
		continue;
	}
	
	// On met le duree du service par défaut si le patient est entrée par l'automate
	$duree_previsionnel = $service_info['duree_previsionnel'];
	if(strlen($duree_previsionnel) < 1 ){
		$duree_previsionnel=$Aghate->DureePrevisionel;
	}
	
	
	$room_info 	= $Aghate->GetRoomInfo($room_name,$service_id);// modif endroit de cette fonction 	
	$room_id 	= $room_info['id'];
	$nip 		= $GildaLoc[$i]['NOIP'];
	$nda 		=  $GildaLoc[$i]['NDA'];
	$date_deb 	= $Aghate->MakeDate($date_loc,$heure_loc);
	$date_fin		=  time()+ ($duree_previsionnel*60*60*24);
	$ServicePanierID=$Aghate->GetPanierIdByServiceId ($service_id); // sera utilisé si besion
	
	
	//-----------------------------------------------------------
	//  Si mouvement est PI il faut fermer le passage précédent
	//  avec date mouvement comme date fin  de son precedent passage
	//-----------------------------------------------------------	
	
	//recupere dernier entry associé au nip et au nda
	$info_entry = $Aghate->GetEntryInfoByNipNda($date_deb,$nip,$nda);

	// Si info entry existe et si le type mvt est PI mettre date_fin de son dernière seéjour
	if($info_entry)
	{

		//-----------------------------------------------------------
		// Check si nouveau PI , GEstion intra mouvments par GILDA
		// pour n'est ecracé le current sej on controle le date deb $date_deb != $info_entry['start_time']
		//-----------------------------------------------------------	
		if ($type_mv=='PI')
		{
			// on verifie d'abord si start_time n'est pas le même et que ds source est différent de gilda
			if($date_deb != $info_entry['start_time']  && $info_entry['uh']!=$uh)
			{				
				// prepare tableau ,mise a jour de la date de sortie du passage précédent
				$TableauUp ['end_time'] = $date_deb;
				$TableauUp ['ds_source'] = 'Gilda';
				$TableauCondUp['id'] = $info_entry['id'];
				$Aghate->update_($TableName,$TableauUp,$TableauCondUp);
				$Aghate->AddTrace(", |Passage Interne MVT son dernière sejour maj id:".$info_entry['id'].' date_fin :'.date('d/m/Y h:i:s',$date_fin));	
				$cpt_pi++;

			}		
		}
	
		//-----------------------------------------------------------
		// Gestion intra mouvments dans LOC
		// NIP=NIP, NDA=NDA,UH=UH  DE < DS
		// ici ajouter une verif && $date_deb > $info_entry['start_time']
		//-----------------------------------------------------------		

		// print_r($info_entry);							
		// echo "<br>".$info_entry['room_id']	."!= ".$room_id ."&&". $date_deb ."< ".$info_entry['end_time'] ."&&". $info_entry['ds_source']."!='Gilda' &&". $info_entry['uh']."==".$uh 		;
		if($info_entry['room_id']	!= $room_id && $date_deb < $info_entry['end_time'] && $info_entry['uh']==$uh  )
		{				
				// prepare tableau
				$TableauUp ['end_time'] = $date_deb;
				$TableauUp ['ds_source'] = 'Gilda';
				$TableauCondUp['id'] = $info_entry['id'];
	
				$Aghate->update_($TableName,$TableauUp,$TableauCondUp);
				$Aghate->AddTrace(", |Passage Interne LOC son dernière sejour maj id:".$info_entry['id'].' date_fin :'.date('d/m/Y h:i:s',$date_fin));	
				$cpt_pi++;
			
		}		
							
	}			
		
	
	//-----------------------------------------------------------
	// Check Sejour Present déjà dans le AGT.loc
	// DateDeb= pour les patient dans la journées
	//-----------------------------------------------------------			

	$sejours= $Aghate->CheckSejourPresent($nip,$date_deb,$service_id); 
	for($s=0 ; $s < count($sejours); $s++)
	{
		// print_r($sejours);
		// echo "<hr>".$sejours[$s]['start_time']."==".$date_deb ."&&". $sejours[$s]['room_id']."==".	$room_id;

		//--------------------------------------------------------------------------------------------
		// si start_time et room_id sont même le pat present dans le bon lit  de_source ='automate'
		// mettre a jour le end_time
		//--------------------------------------------------------------------------------------------
		if ( ($sejours[$s]['start_time']	== $date_deb) && ($sejours[$s]['room_id']	== $room_id) && ($sejours[$s]['ds_source']=="Automate"))
		{
			// check lit occupé par autre patient, donv envoi le NIP a exclure
			$date_fin=  time();
			$ChkPlaceLibre=$Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin,$nip);
			// si place libre
			if(count($ChkPlaceLibre) > 0)
			{
				//deplace patient qui occupe au couloir
				$sql="UPDATE agt_loc set room_id='".$ServicePanierID."' WHERE id='".$ChkPlaceLibre[0]['id']."'";
				$Aghate->update($sql);	
				$alert="Conflit 6-> Patient(".$nip.") du lit ".$sejours[$s]['room_name']." non sortie de Gilda donc patient deplacer dans le pannier";		
				$Aghate->AddTrace(" |".$alert);
				$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");					
			} 				
				
			
			$sql="UPDATE agt_loc set end_time='".$date_fin."' 
						WHERE id='".$sejours[$s]['id']."'";
			//echo "<br>".
			$Aghate->update($sql);			
			$Aghate->AddTrace(", |end_date maj ".date('d/m/Y h:i:s',$date_fin));	
			$cpt_updt++;
		}

		//--------------------------------------------------------------------------------------------
		// si end_date programé par utilisateur est inférieur de cur_time on force cur_time
		// ici probleme car on verifie pas si ds_source == programmé mais seulement dssource différent d'automate
		// les problemes de panier viennent d'ici car ils ne rentrent pas dans la condition suivant
		//$sejours[$s]['ds_source']!="Automate"
		//--------------------------------------------------------------------------------------------				
		elseif(($sejours[$s]['start_time']	== $date_deb) && ($sejours[$s]['room_id']	== $room_id) && ($sejours[$s]['ds_source']=="Programme"))		
		{
			if ( $sejours[$s]['end_time'] < time())
			{
 				
				// check lit occupé par autre patient, donv envoi le NIP a exclure
				$ChkPlaceLibre=$Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin,$nip);
				// si place libre
				if(count($ChkPlaceLibre) > 0)
				{
					//deplace patient qui occupe au couloir
					$sql="UPDATE agt_loc set room_id='".$ServicePanierID."' WHERE id='".$ChkPlaceLibre[0]['id']."'";
					$Aghate->update($sql);	
					$alert="Conflit 5-> Patient(".$nip.") du lit ".$sejours[$s]['room_name']." non sortie de Gilda donc patient deplacer dans le pannier";		
					$Aghate->AddTrace(" |".$alert);
					$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");					
				} 				
				$sql="UPDATE agt_loc set end_time='".time()."' 
							WHERE id='".$sejours[$s]['id']."'";
 							
				$Aghate->update($sql);			
				$Aghate->AddTrace(" | end_date definis par utilisateur est inférier de now() , donc,maj ".date('d/m/Y H:i',time()));			
				$cpt_updt++;
			}
		}
		//--------------------------------------------------------------------------------------------				
		// si start_time diffrent dans gilda et agt	 sans regarder le source
		//--------------------------------------------------------------------------------------------				
		elseif(($sejours[$s]['start_time']	!= $date_deb) && ($sejours[$s]['room_id']	== $room_id) )	
		{	
			// check lit occupé par autre patient, donv envoi le NIP a exclure
			$ChkPlaceLibre=$Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin,$nip);
			// si place libre
			if(count($ChkPlaceLibre) > 0)
			{
				//deplace patient qui occupe au couloir
				$sql="UPDATE agt_loc set room_id='".$ServicePanierID."' WHERE id='".$ChkPlaceLibre[0]['id']."'";
				$Aghate->update($sql);
				$alert="Conflit 1-> Patient(".$nip.") du lit ".$sejours[$s]['room_name']." occupe le lit  donc patient deplacer dans le pannier";		
				$Aghate->AddTrace(" |".$alert);
				$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");
			} 
			//update start_time pour ce patient
			$sql="UPDATE agt_loc set start_time='".$date_deb."' 
						WHERE id='".$sejours[$s]['id']."'";
			$Aghate->update($sql);			
			$Aghate->AddTrace(" | start_time modifée par gilda, ".date('d/m/Y h:i:s',$date_deb));										
		}

		//--------------------------------------------------------------------------------------------				
		// si Gilda.LIT  <> AGT.LIT , 
		// deplace Panier vers LIT les Programations
		//--------------------------------------------------------------------------------------------				
		elseif(($sejours[$s]['start_time']	== $date_deb) && ($sejours[$s]['room_id']	!= $room_id) )	
		{	
			// check lit occupé par autre patient, donv envoi le NIP a exclure
			$ChkPlaceLibre=$Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin);
			// si place libre
			if(count($ChkPlaceLibre) > 0)
			{
				//deplace patient qui occupe au couloir
				$sql="UPDATE agt_loc set room_id='".$ServicePanierID."' WHERE id='".$ChkPlaceLibre[0]['id']."'";
				$Aghate->update($sql);	
				$alert="Conflit 2-> Patient(".$nip.") du lit ".$sejours[$s]['room_name']." occupe le lit  donc patient deplacer dans le pannier";		
				$Aghate->AddTrace(" |".$alert);
				$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");
			} 
			//update start_time pour ce patient
			$sql="UPDATE agt_loc set room_id='".$room_id."',
							de_source='Gilda',
							statut_entry='Hospitalisé'
			 WHERE id='".$sejours[$s]['id']."'";
			$Aghate->update($sql);		
			

			$Aghate->AddTrace(" | room_id modifée par gilda, new room_id".$room_id);										
		}

		//--------------------------------------------------------------------------------------------				
		// si Gilda.LIT  <> AGT.LIT and gilda.start_time <> agt.start_time  
		// deplace Panier vers LIT les Programations
		//--------------------------------------------------------------------------- -----------------				
		 elseif(($sejours[$s]['start_time']	!= $date_deb) && ($sejours[$s]['room_id']	!= $room_id) )	
		{	
			// check lit occupé par autre patient, donc envoi le NIP a exclure
			$ChkPlaceLibre=$Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin,$nip);
			// si place libre
			if(count($ChkPlaceLibre) > 0)
			{
				//deplace patient dans le couloir
				$sql="UPDATE agt_loc set 
							room_id='".$ServicePanierID."'
							WHERE id='".$ChkPlaceLibre[0]['id']."'";
				$Aghate->update($sql);			
				$alert="Conflit 3-> Patient(".$nip.") du lit ".$sejours[$s]['room_name']." occupe le lit  donc patient deplacer dans le pannier";		
				$Aghate->AddTrace(" |".$alert);
				$Aghate->UpdateDescriptionFromId($ChkPlaceLibre[0]['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");
	
				
			} 
			//update start_time pour ce patient
									$sql="UPDATE agt_loc set 
							room_id='".$room_id."',
							start_time='".$date_deb."',
							de_source='Gilda',
							statut_entry='Hospitalisé'
							WHERE id='".$sejours[$s]['id']."'";
			$Aghate->update($sql);	


			$Aghate->AddTrace(" | room_id modifée par gilda, new room_id".$room_id);										
			
		}

		//-------------------------------------------
		// mise a jour les NDA, si programmé,puis CODAGE et FORMS	
		//-------------------------------------------
		if($nda != $sejours[$s]['nda']){
			$Aghate->updateNda($sejours[$s]['id'],$GildaLoc[$i]);

			// mise a jour TEMPS NDA dans forms/coadge a faire ici
			$url= "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];		
			$url= str_replace(strrchr($url,"/"),"/",$url);			
			$res = file_get_contents($url."../commun/ajax/ajax_forms_update_temp_nda.php?temp_nda=".$sejours[$s]['nda']."&nda=".$GildaLoc[$i]['NDA']."&uh=".$GildaLoc[$i]['UH']);

		}		

		//--------------------------------------------------------------------------------------------
		// si end_date programé par utilisateur est inférieur de cur_time on force cur_time
		//--------------------------------------------------------------------------------------------			
		if ( $sejours[$s]['end_time'] < time())
		{
				$sql="UPDATE agt_loc set end_time='".time()."' 
							WHERE id='".$sejours[$s]['id']."'";
				$Aghate->update($sql);			
				$Aghate->AddTrace(" | end_date definis par utilisateur est inférier de now() , donc,maj ".date('d/m/Y h:i',time()));			
				$cpt_updt++;
		}
		
	}
	
	// controle a faire si le patient present dans le panier
	
	if(count($sejours) > 0)
	{
		continue; // pas besion de verifier les restes patient deja present dans la base et les donnes sont maj
	}
	else
	{
		//Preparation du tableau pour l'insertion
		$TableauInsertDonnee['start_time']	=$date_deb;
		$TableauInsertDonnee['end_time']	=$date_fin;
		$TableauInsertDonnee['room_id']		= $room_id;
		$TableauInsertDonnee['create_by']	='Automate';
		$TableauInsertDonnee['type']		=$GildaLoc[$i]['CDSEXM']; // colour code a definir--------------->
		$TableauInsertDonnee['noip']		=$nip;
		$TableauInsertDonnee['nda']			=$GildaLoc[$i]['NDA'];
		$TableauInsertDonnee['uh']			=$GildaLoc[$i]['UH'];				 
		$TableauInsertDonnee['protocole']	='Protocole Automate';
		$TableauInsertDonnee['de_source'] 	= 'Gilda'; // Gilda car la date d'entrée
		$TableauInsertDonnee['ds_source'] 	= 'Automate';
		$TableauInsertDonnee['gilda_id'] 	= $GildaLoc[$i]['NOIDMV'];
		$TableauInsertDonnee['statut_entry'] = 'Hospitalisé';
		
		// On vérifie si il y a de la place dans le lit
		// false si la place est libre sauf panier
		if ($ServicePanierID != $room_id)
		{
			$ChkPlaceLibre = $Aghate->IsPlaceLibre($room_id,$date_deb,$date_fin);
			
			if (count($ChkPlaceLibre) > 0)
			{
				foreach($ChkPlaceLibre as $key )
				{
					//deplace patient qui occupe au couloir
					$sql="UPDATE agt_loc set 
								room_id='".$ServicePanierID."'
								WHERE id='".$key['id']."'";
					$Aghate->update($sql);	
					$alert="Conflit 4-> Patient(".$nip.") du lit ".$room_name." occupe le lit  donc patient deplacer dans le pannier";		
					$Aghate->AddTrace(" |".$alert);
					$Aghate->UpdateDescriptionFromId($key['id'],"TRACE_AUTOMATE",$alert,"TRACE_AUTOMATE");
				}
			}
		}					
		// Insertion de la reservation
		$id = $Aghate->InsertConvocation($TableName,$TableauInsertDonnee);
		$Aghate->AddTrace("||Convocation inseré,  sans programation") ;

		// ajoute msg alert dans le reservatiosn s'il existe
		if (strlen($MsgAlert) > 1)
		{
				$msg_trace=$MsgAlert;
				$Aghate->UpdateDescriptionFromId($id,"TRACE_AUTOMATE",$msg_trace,"TRACE_AUTOMATE");		
		}
		
		$cpt_in++;
	}

}
$Aghate->AddTrace("\n\nNombre séjours traité : ".$nb_loc);
$Aghate->AddTrace("\nNombre patient insere : ".$cpt_in);
$Aghate->AddTrace("\nNombre maj : ".$cpt_updt."\n");
$Aghate->AddTrace("Nombre lit absent | pat non insere : ".$nb_lit_abs."\n");
$Aghate->AddTrace("Nombre pat non insere faute de place : ".$no_place."\n");
$Aghate->AddTrace("Nombre pat dejà present agt_loc : ".$pat_pres."\n");
$Aghate->AddTrace("Nombre date de sortie forcé pour insertion : ".$cpt_forced."\n");
 

//Ecrit dans le fichier de trace
$Aghate->write_trace_file();

// affiche les alerts
foreach ($Alerts as $val)
{
	echo "<br>".$val;
}
$fin = time();

$result = $fin - $debut;
echo "<br/>Temps de traitement : ";
echo gmdate("H:i:s", $result); // convertit $result en heure, min et sec

?>
