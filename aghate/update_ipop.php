<?php
/*
########################################################################
* 	Projet Aghate
* 	Module Ipop->Agahte
* 	Auteur Divan Kandiah
* 	date creation 02/07/2014
########################################################################
*/
//ini_set('max_execution_time', 0);
//ini_set("display_errors", 0);	
//error_reporting(E_ALL ^ E_NOTICE);


header("Content-type:text/html; charset=utf-8");
require("./config/config.php");
require("./config/config_ipop.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassFileHandle.php");
include("./commun/include/ClassMysql.php");
include("./commun/include/ClassIpopAghate.php");
include("./commun/include/ClassAghate.php");
include("./commun/include/ClassFtp.php");  
include("./commun/include/ClassGilda.php");

$debut = time();

// initalise les objets
$Ipop = new IpopAghate($site);
$mysql = new MySQL();
$Aghate = new Aghate('agt_loc');
$FileHandle = new FileHandle();
//$ftp = new ftp($IPOP_ftp_host,$IPOP_ftp_port,$IPOP_ftp_host,$IPOP_ftp_password);
$CommonFunctions = new CommonFunctions(true);
$Gilda=new Gilda($ConnexionStringGILDA); 

$MappingArray = $Ipop->PrepareMappingArray($IPOP_map_file);
print_r($MappingArray);
$compteur=0;

$TraceFile = $IPOP_chemin_trace.$IPOP_fic_trace;

$FileHandle->CreateFile($TraceFile);

$msg .= "\n========= Traitement Le ".date("d/m/Y")."==============\n ";

/*
// "ouverture du répertoire et liste les files 
$dir = @opendir($IPOP_chemin_input);
//recupère les fichier dans un array
$liste_files=array();
$nbr_files=0;
while ($file = readdir($dir)) 
{
	$liste_files[$nbr_files]=$file;
	$nbr_files++;
}
closedir($dir);	
*/

 
$file=$liste_files[0];

//force le fichier pour le test
$file="Extraction_IPOP_SLS_LRB_2014_7_3_16.csv";

//date et heure du fichier
$DateHeure = substr($file,20,-4);
list($annee,$mois,$jour,$heure) =  explode("_",$DateHeure);
$FileTime = mktime($heure,0,0,$mois,$jour,$annee);
 
// ecrire dans fichier trace 
$FileHandle->WriteFile($TraceFile,"Fichier :".$file);
$src=$IPOP_chemin_input.$file;

$date_modify	= "2014-05-16 12:00:00";
 
//--------------------------------------------------------------	
// lecture fichier ipop
// insertion des modifs
// recuperationd des modifs
//--------------------------------------------------------------
$msg.= "\n|======= > processing : ".$src." <==========| \n";
$data= $Ipop->	LireFichierIpop($src);
$Ipop->BackupIpop($data);
$IpopData	 = $Ipop->GetModifFromDate($date_modify);

$arr_size=count($IpopData);

//controle data
if($arr_size > 0)
{
	//service bloc pour agt_exam_compl
	$bloc_info 		= $Aghate->GetserviceInfoByserviceName($NomserviceAmbulatoire_EC);
	$bloc_id		= $bloc_info[0]['id'];
	
	//service bloc pour agt_loc
	$bloc_info_loc	= $Aghate->GetserviceInfoByserviceName($NomserviceAmbulatoire_LOC);
	$bloc_id_loc	= $bloc_info_loc[0]['id'];
	
	if(strlen($bloc_id)< 1 ){
		echo "<h3>Le service de bloc(".$NomserviceAmbulatoire_EC.") n'a pas été defini la base Aghate </h3>";
	}
	/*
	if(strlen($bloc_id_loc)<1  ){
		echo "<h3>Le service de bloc(".$NomserviceAmbulatoire_LOC.") n'a pas été defini la base Aghate </h3>";
		exit;
	}
	*/
	
	//Panier pour le service uca exam compl et le service uca loc
	$bloc_panier_id 	= $Aghate->GetPanierIdByserviceId($bloc_id);
	$bloc_loc_panier_id = $Aghate->GetPanierIdByserviceId($bloc_id_loc);
	
	$NbSrvNonMap	=0;
	$NbNipInvalide	=0;
	$NbMaj 			= 0;
	$NbIns 			= 0;
	$NbAnnule 		= 0;
	$NbPatIns 		= 0;
	$nb_pres		= 0 ;
	$TableInsert	="";
	$arr_size=count($IpopData);
	//====================================================================
	//boucle sur data 		
	//====================================================================
	for($c=0; $c < $arr_size;$c++)
	{
		//====================================================================
		//Preparation des valeurs et tableau
		//que faire si date_interv rempli ?
		//====================================================================
		$service = "";
		echo "<br>".$IpopData[$c]["nip"]." Date :".$IpopData[$c]["date_prevue"];
		list($date,$heure)	= explode(" ", $IpopData[$c]["date_prevue"]);
		list($hr,$min)		= explode(":",$heure);
		//date_prevu toujours remplit
		if(strrpos($date, "/"))
			list($jour,$mois,$annee)=explode("/",$date); // dd/mm/yyyy
		else
			list($annee,$mois,$jour)=explode("-",$date); // YYYY-mm-dd
		$date_prevu 		= mktime($hr,$min,0,$mois,$jour,$annee);
		$protocole 			= $IpopData[$c]["type_inter"];
		$ChkAmbulatoire 	= $IpopData[$c]["ambulatoire"];
		$Etat				= $IpopData[$c]["etat"];
		$Noip 				= (strlen(floatval($IpopData[$c]["nip"])) ==10)?floatval($IpopData[$c]["nip"]):"";
		$service 			= trim(str_replace("\n","",$IpopData[$c]["service"]));
		$service 			= (strlen($MappingArray[$service])>0)?trim($MappingArray[$service]):"";
		$Medecin_Info		= (strlen($IpopData[$c]["chirurgien"])>0)?$Aghate->GetMedecinInfoByNomPrenom($IpopData[$c]["chirurgien"]):"";
		$ipop_id			= $IpopData[$c]["ipop_id"];
		$commentaire2		= $IpopData[$c]["commentaire2"];
		
		//=============================================================
		//Controle ambulatoire
		//=============================================================
		if(strtolower($ChkAmbulatoire) == 'oui')  {
			$ChkAmbulatoire	= 'oui'; 
		}
		if( (strpos(strtoupper($protocole),'AMBU')>0 ) && (strtolower($ChkAmbulatoire)=='non')  ){
			$ChkAmbulatoire	= 'oui'; 
		}
		if( (strtoupper(substr($commentaire2,0,3))=='UCA') || (strpos(strtoupper($commentaire2),'UCA ')>0 ) || 
		(strpos(strtoupper($commentaire2),' UCA')>0 ) ){
			$ChkAmbulatoire	= 'oui';  
		}			
		
		//=====================================
		//Controle sur nip ,service et medecin
		//=====================================
		if(strlen($service)< 1){
			$msg.="\n /!\ service ".$IpopData[$c]["service"]." non mappé !!! ";
			$NbSrvNonMap++;
			continue;
		}
		
		if(strlen($Noip)<1){
			$msg.="\n /!\ Nip invalide :  ".$IpopData[$c]["nip"]."  !!! ";
			$NbNipInvalide++;
			continue;
		}
		
		if(empty($Medecin_Info) || count($Medecin_Info)<1){
			$Medecin_Info	= $Aghate->GetMedecinInfoByNomPrenom("Medecin Automate");
			$msg.="\n /!\ Medecin ".$IpopData[$c]["chirurgien"]." non trouver dans la base local => Medecin Automate  !!! ";
		}
				
		//patient controle 
		$PatInfo	= $Aghate->CheckPatientPresent($Noip);		
		if (!($PatInfo)){
			$InfoPat 						= $Gilda->GetPatInfoByNip($Noip);		
			list($j,$m,$a)					= explode("/",$InfoPat[0]["DTNAIS"]);
			
			//Tableau pat
			$TabPatient["noip"]				= $Noip;
			$TabPatient["nom"]				= $InfoPat[0]["NMMAL"];
			$TabPatient["prenom"]			= $InfoPat[0]["NMPMAL"];
			$TabPatient["ddn"]				= date("Y-m-d",mktime(0,0,0,$m,$j,$a));
			$TabPatient["sex"]				= $InfoPat[0]["CDSEXM"];
			$mysql->insertion("agt_pat",$TabPatient,"id_pat");
			$NbPatIns++;
			$Type							=$InfoPat[0]["CDSEXM"];
		}
		else{
			$Type							=$PatInfo[0]["sex"];
		}
 		
		//=====================================
		//Get service info
		//=====================================	
		$service_info = $Aghate->GetserviceInfoByserviceName($service);
		if (count($service_info) > 0)
			$service_id = $service_info[0]["id"];
		else{
			$msg.="\n /!\ service  $service introuvable dans Aghate!!! ";
			$serviceInvalide++;
			continue;				 
		}
		
		$PanierId			= $Aghate->GetPanierIdByserviceId($service_id);		
		$ChkAmbulatoire 	= strtolower($ChkAmbulatoire);
		$Medecin_id			= $Medecin_Info[0]['id_medecin'];
				
		//======================================================================================
		// DUREE 
		//======================================================================================
		//Duree du sejour
		$start_time 		= $date_prevu;
		$start_time 		= $start_time - $IPOP_duree_avant; 
		$end_time 			= $date_prevu + $IPOP_duree_apres; 
		
		//Duuree au bloc
		$bloc_start_time 	= $date_prevu - $duree_av_bloc;
		$bloc_end_time 		= $date_prevu + $duree_apr_bloc;
		
		//corriger l'automate duplicate exam_compl, mapping a revoir

		// PREPARE SQL array
		//Tableau loc
		/*$TabDonnees["start_time"] 		= $start_time;
		$TabDonnees["end_time"] 		= $end_time;
		$TabDonnees["statut_entry"] 	= "Programme";
		$TabDonnees["de_source"] 		= "IPOP";
		$TabDonnees["ds_source"] 		= "Programme";
		$TabDonnees["noip"] 			= $Noip;
		$TabDonnees["room_id"] 			= $PanierId;
		$TabDonnees["protocole"] 		= addslashes($protocole); // A voir
		$TabDonnees["medecin"]			= $Medecin_id;
		$TabDonnees["gilda_id"]			= $ipop_id;
		$TabDonnees["type"]				= $Type;*/
		
		//Tableau prog
		$TableProg["start_time"] 	    = $start_time;
		$TableProg["end_time"] 	        = $end_time;
		$TableProg["statut_entry"]      = "Demande";
		$TableProg["noip"] 		        = $Noip;
		$TableProg["room_id"] 		    = $PanierId;
		$TableProg["service_id"] 		= $service_id;
		$TableProg["protocole"] 	    = addslashes($protocole);
		$TableProg["medecin"]			= $Medecin_id;
		$TableProg["type"]				= $Type;
		$TableProg["ipop_id"]			= $ipop_id;
		$TableProg["create_by"]			= "IPOP";

		//Tableau exam compl (service uca) => affichage avec enable periods = y
		$TabExam["id"]					= $ipop_id;
		$TabExam["noip"]				= $Noip;
		$TabExam["start_time"]			= $bloc_start_time;
		$TabExam["end_time"]			= $bloc_end_time;
		$TabExam["room_id"]				= $bloc_panier_id;
		$TabExam["protocole"]			= addslashes($protocole); // A voir
		$TabExam["statut_entry"]		= "Programme";
		$TabExam["medecin"]				= $Medecin_id; 
		$TabExam["anesthesiste"]		= $IpopData[$c]["anesthesiste"];
		$TabExam["type"]				= $Type;
		
		//Insertion exam complementaire pour loc (service uca loc) => pour enable periods = n affichage classique
		/*
		$TabLocExam["noip"]				= $Noip;
		$TabLocExam["start_time"]		= $bloc_start_time;
		$TabLocExam["end_time"]			= $bloc_end_time;
		$TabLocExam["room_id"]			= $bloc_loc_panier_id;
		$TabLocExam["protocole"]		= addslashes($protocole); // A voir
		$TabLocExam["statut_entry"]		= "Programme";
		$TabLocExam["medecin"]			= $Medecin_id;
		$TabLocExam["type"]				= $Type; 
		*/
		$InsertLocInfo					= "Patient  = ".$Noip." et start_time = ".$start_time;
		$InsertExamInfo					= "Patient  = ".$Noip." et start_time = ".$bloc_start_time;	
		
		//=========================================================
		//Info ok 
		// controle si patient deja présent dans Aghate
		//=========================================================
		//controle si patient deja dans loc
		$SqlCheckLoc	= $Aghate->CheckSejourPresent($Noip,$start_time,$service_id);
		if(count($SqlCheckLoc)>0){
			$msg .= "\n Patient $Noip deja present dans agt_loc";
			$nb_pres++;
			continue;
		}
		
		//=================================================================================
		//recupere ligne si existe dans la base
		//=================================================================================
		//controle si ligne deja existant dans agt_prog
		$SqlChkDupl="SELECT * FROM agt_prog WHERE ipop_id='".$ipop_id."'";
		$chk_dupl=$mysql->select($SqlChkDupl);
		
		
		//=================================================================================
		//traitement info passé par rapport au fichier
		//traitement des annulations  => si pas annulé deja traité par gilda (automate update_agt)
		// si deja traité par Gilda => donc info passé
		//=================================================================================
		if($date_prevu <= time()){
			//Programmation annulé
			if($Etat == "Non arrivé"){
				//annule que le bloc et pas le sejour
				if(count($chk_dupl)>0 && is_array($chk_dupl)){
					$desc	 = "Programmation annule : non arrive dans IPOP";
					$Sql_Del = "UPDATE agt_prog,agt_exam_compl SET statut_entry='SUPPRIMER',description= '".$desc."'
									WHERE agt_prog.id ='".$chk_dupl[0]['id_prog']."'
									AND agt_exam_compl.id ='".$ipop_id."'";
					$mysql->update($Sql_Del);
					$msg.= "\n /!\ Demande ".$chk_dupl[0]['id_prog']." annulé";
					$NbAnnule++;
				}
			}
		} // fin traitement info passé
			
		//=================================================================================
		//traitement infos futur
		//ajouter programmation => si ambulatoire 1 jour
		//						=> autre cas 5 jours totale
		//=================================================================================
		else{
			//=================================================================================
			//controle ligne existe  => si deja existant controle si info différent
			//=================================================================================
			if(count($chk_dupl)>0 && is_array($chk_dupl)){
					//$TabCd["id"] 		= $chk_dupl[0]["id"];
					$TabCdProg["id"] 	= $chk_dupl[0]["id"];
					$TabCdExam['id']	= $ipop_id;
					
					$agt_start_time		= $chk_dupl[0]["start_time"];
					$agt_end_time		= $chk_dupl[0]["end_time"];
					$agt_protocole		= $chk_dupl[0]["protocole"];
					$agt_medecin		= $chk_dupl[0]["medecin"];
					$agt_room			= $chk_dupl[0]["room_id"];
					//|| $agt_protocole != $protocole a voir pour protocole a cause de addslashes => stripslashes dans le select
					//Check differences => si diff maj dans les trois tables
					if($start_time != $agt_start_time || $end_time != $agt_end_time  || $agt_medecin != $Medecin_id || $agt_room != $PanierId || 
							$protocole != $agt_protocole ){
						$msg.="\n". $start_time."!=".$agt_start_time."  ; ".$end_time."!=".$agt_end_time."  ; ".$agt_medecin."!=".$Medecin_Id."  ; ".
								$agt_room."!=".$PanierId."  ; ".$protocole."!=".$agt_protocole."  ; ".
						$mysql->update_("agt_prog",$TableProg,$TabCdProg);
						if($ChkAmbulatoire=='oui'){
							$mysql->update_("agt_exam_compl",$TabExam,$TabCdExam);
						}
						$msg  	.= "\n Maj des lignes deja présentes dans la base Aghate avec gilda_id=$ipop_id  , noip 
									: ".$Noip." et start time :  ".$start_time;
						$NbMaj++;
					}
					else{
						$msg  	.= "\n Aucune maj";
					}
			}
			//====================================================================
			// si pas existant => insertion
			//====================================================================
			else{
				$id_prog 	= $mysql->insertion("agt_prog",$TableProg);
				$msg 		.=(strlen($id_prog)<1)?"\n Une erreur a eu lieu lors de l'insertion de $InsertLocInfo dans agt_prog":""; 
				if($ChkAmbulatoire=='oui'){		
					$SqlChkExam="SELECT * FROM agt_exam_compl WHERE id='".$ipop_id."'";
					$chk_dupl=$mysql->select($SqlChkExam);
					if(count($chk_dupl)<1){
						$id_exam	= $mysql->insertion("agt_exam_compl",$TabExam);
						$msg 		.=(strlen($id_exam)<1)?"\n Une erreur a eu lieu lors de l'insertion de $InsertExamInfo dans exam_compl":"";
					}
				}
				$msg.= "\n Insertion de la ligne dans agt_prog  patient : ".$IpopData[$c]['NIP']." start_time : ".$start_time;							
				$NbIns++;
			}
		}//fin traitement info futur	
	} // fin boucle for
}//fin if data
else{
	$msg.= "\nPas de données dans le fichier :". $src;
}

$msg.= "\n Nb Insertion :".						$NbIns;
$msg.= "\n Nb Maj :".							$NbMaj;
$msg.= "\n Nb Nip Invalide :".					$NbNipInvalide;
$msg.= "\n Nb service non mappé  :".			$NbSrvNonMap;
$msg.= "\n Nb annulation  :".					$NbAnnule;
$msg.= "\n Nb pat inserer dans agt_pat  :".		$NbPatIns;
$msg.= "\n Nb patient deja present dans loc :".	$nb_pres;

$fin = time();
$result = $fin - $debut;
$msg.= "\n Temps de traitement : ";
$msg.= "\n".gmdate("H:i:s", $result); // convertit $result en heure, min et sec

$FileHandle->WriteFile($TraceFile,$msg);
echo str_replace("\n","<br>",$msg);

?>
