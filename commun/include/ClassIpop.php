<?php
/*
###################################################################################################
#
#											OBJET Ipo
#											
#
#							By Mohanraju @ APHP
###################################################################################################
*/
class Ipop extends MySQL
{
	var $DateDeb;
	var $DateFin;
 	var $FichierIpop; 			// Ipop.rum sortie par l'outil Ipop
 	var $FichierErreurCodes; 	// fichier code erreurs 
 	var $ErreurCodesIpop; 		// Possible error codes dans le fichier Ipop ARRAY
 	var $Err;					// Erreurs de ce traitements sont dans un array  
 	var $warn;					// Warning de ce traitements sont dans un array  
 	var $Trace;					// Trace de ce traitements sont dans un array  
 	var $Structure; 			// Ipop file structure
 	var $Site	;				// Code du Hopital ex 076 pour sls, 047 pour lrb ....
 	var $TableIpop ;         	// nom de Table Ipop dans mysql ::permet de defrentiser si plusiers sites...
  var $Table_bloc_structure ;         	// nom de Table Ipop dans mysql ::permet de defrentiser si plusiers sites...
 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Ipop($Site)
	{
		$this->Site=$Site;
		
		//initialise connexion Mysql
		parent::MySQL(); 
		
		//initialise file structure
		$this->Structure=array(
					"IPOP_ID",
					"BLOC",
					"UM_DE_TRAVAIL",
					"DATE_PREVUE",
					"DATE_INTERVENTION",
					"HEURE_ENTREE_SALLE",
					"HEURE_SORTIE_SALLE",
					"DUREE_INTERVENTION",
					"SALLE",
					"TYPE_INTER",
					"PROGRAMMATION",
					"CHIRURGIEN",
					"ANESTHESISTE",
					"NOM_INTERNE",
					"NIP"
					);		
		
	}

 	/*
 	==============================================================================
 	function SetTableIpop($NomduTable)
 	==============================================================================
 	*/
	function SetTableIpop($NomduTable)
	{
		if (strlen($NomduTable) < 1)
		{
			echo "<br>Ipop:: Nom du Table_ipop est vide ou non declaré<br> Operation abondonnée !!!, consult config/config_[site].php";
			exit;
		}
		else	
		{
			$this->TableIpop=$NomduTable;
		}
	}

 	/*
 	==============================================================================
 	function SetTablebloc_structure($NomduTable)
 	==============================================================================
 	*/
	function SetTablebloc_structure($NomduTable)
	{
		if (strlen($NomduTable) < 1)
		{
			echo "<br>Ipop:: Nom du Table_bloc_structure est vide ou non declaré<br> operation abondonnée !!!, consult config/config_[site].php";
			exit;
		}
		else	
		{
			$this->Table_bloc_structure=$NomduTable;
		}
	}

 	/*
 	==============================================================================
 	funtion SetPeroide (date_deb et date_fin )
 	initalise les peroides dans Ipop;
 	vérify le format de dates 
 	si les dates sont vide le peroide sera le 1er jour de l'anne à aujourdhui
 	==============================================================================
 	*/
	function SetPeroide($DateDeb="",$DateFin="")
	{
		if ((strlen($DateDeb) < 8)||(strlen($DateFin) < 8))
		{
			// pour janvier et fevrier traites les année d'avant aussi
			if( (date('m')=='01') || (date('m')=='02')){
					$year=date("Y");
					$year--;
					$this->DateDeb="01/01/".$year;
	 		}else{
				$this->DateDeb=date("01/01/Y");
			}
			$this->DateFin=date("d/m/Y");
		}else{
			$this->DateDeb=$DateDeb;
			$this->DateFin=$DateFin;
		}
		
	}

 	/*
 	==============================================================================
 	funtion GetUhExecutant ()
 	Recupare les uh declaré dans table stucture bloc
 	retourn dans le format string formaté (ex 'uh1','uh2','uh3'.....)
 	si rien trouvé retourne null
 	==============================================================================
 	*/
	function GetUhExecutant()
	{
		$sql_uh="SELECT uh from ".$this->Table_bloc_structure." where site = '".$this->Site."' and uh <>'' and uh is not null";
		$res =parent::select($sql_uh);
	
		$retval="UH";
		for ($i=0;$i < count($res);$i++){
			$retval.=",".trim($res[$i]['uh']);
		}
		//mettre lss single quotes dans les UH avant d'envoyer
		return "'".str_replace(",","','",$retval)."'";
	} 	
 	/*
 	==============================================================================
 	funtion SetFicIpop (Nom de fichier Ipop)
 	$nom de fichier peut être chemin relatif
 	vérify presence du ficheir dans le dossier si non le traitement sera annulé
 	==============================================================================
 	*/
	function SetFicIpop($fichier)
	{
		if(!is_file($fichier))
		{
			$this->Err[]= "Le fichier Ipop (".$fichier.")introuvable ou unable to lire ";
		}else
		{
			$this->FichierIpop=$fichier;
		}
	}

 	/*==============================================================================
 	 funtion LireFichierIpop ($FichierIpop)
 	 Lire el fichier Ipop et mettre dans un array
 	 
 	==============================================================================*/
	function LireFichierIpop($FichierIpop)
	{
 		// vérify les fichier Ipop est declaré
		if (	strlen($FichierIpop)< 1){
			echo "<br>Ipop :: Fichier Ipop  non declaré ou vide !!!";
			return false;
		}
		// lire le ficheir et mettre dans un array
		$lines = file($FichierIpop);
		$arr_size=count($lines);
		if ($arr_size < 2)
		{	
			echo "<br>Ipop :: aucun donneé dans le Fichier Ipop :".$FichierIpop;	
			return false;		
		}
		// model du ligne dans Ipop
		$new_compteur=0;
		$TailleStrcuture=count($this->Structure);
	
		// boucle par chaque linge 

		for($i=0;$i < $arr_size;$i++)
		{
			// explode le ligne dans un tableu
			$data=explode(";",$lines[$i]); 
			
			//vérify le structure corresponds nombre de collone 
			//uniquement au premier fois
			if($i==0)
			{
				if(count($data)!= $TailleStrcuture)
				{
					echo "<br>Ipop:: Le fichier Ipop ne correponds pas au Struture  ".count($data) ." != ".$TailleStrcuture."<br>Dans le Structurte:-<br>";
					print_r($this->Structure);
					echo "<br>Dans le fichier :-<br>";
					print_r($data);	
					exit;
				}
			
			}
			
			for ($c=0; $c < $TailleStrcuture; $c++)
			{
				$res[$i][$this->Structure[$c]]=$data[$c];
			}
		}
	 	return $res;
	}

	/*
	=================================================================
	CheckDonnees($TableauIpop)
	function vérify les donnes de la IPOP avec mettre a jour dans la table
	=================================================================
	*/
	function CheckDonnees($TableauIpop)
	{
		// si les resulta on maj dans le table
		if (!is_array($TableauIpop)) return;
		$arr_size=count($TableauIpop); 		
		for($c=0; $c < $arr_size;$c++)
		{
			if(($TableauIpop[$c]['BLOC']== 'NULL') or (strlen($TableauIpop[$c]['BLOC']) < 1) )
				{
					$TableauIpop[$c]['ERR'] .="<br> -Bloc'] non rensiegnée ";
				}
			if(($TableauIpop[$c]['UM_DE_TRAVAILE'] == 'NULL') or (strlen($TableauIpop[$c]['UM_DE_TRAVAIL'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -UM de Travail Non rensignée";
				}
			if(($TableauIpop[$c]['DATE_PREVUE'] == 'NULL') or (strlen($TableauIpop[$c]['DATE_PREVUE'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Date prevue Non rensignée";
				}
			if(($TableauIpop[$c]['DATE_INTERVENTION'] == 'NULL') or (strlen($TableauIpop[$c]['DATE_INTERVENTION'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Date intervention Non rensignée";
				}
			if(($TableauIpop[$c]['HEURE_ENTREE_SALLE'] == 'NULL') or (strlen($TableauIpop[$c]['HEURE_ENTREE_SALLE'] )< 1)) 
				{
					$TableauIpop[$c]['HEURE_ENTREE_SALLE']="00:00:00";
					$TableauIpop[$c]['Err'] .= "<br> -Heure entree_salle Non rensignée";
				}
			if(($TableauIpop[$c]['HEURE_SORTIE_SALLE'] == 'NULL') or (strlen($TableauIpop[$c]['HEURE_SORTIE_SALLE'] )< 1))
				{
					$TableauIpop[$c]['HEURE_SORTIE_SALLE']="00:00:00";
					$TableauIpop[$c]['Err'] .= "<br> -Heure sortie_salle Non rensignée";
				}
			if(($TableauIpop[$c]['DUREE_INTERVENTION'] == 'NULL') or (strlen($TableauIpop[$c]['DUREE_INTERVENTION'] )< 1)) 
				{
					$TableauIpop[$c]['DUREE_INTERVENTION']="00:00:00";
					$TableauIpop[$c]['Err'] .= "<br> -Duree intervention Non rensignée";
				}
			if(($TableauIpop[$c]['SALLE'] == 'NULL') or (strlen($TableauIpop[$c]['SALLE'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Salle Non rensignée";
				}
			if(($TableauIpop[$c]['TYPE_INTER'] == 'NULL') or (strlen($TableauIpop[$c]['TYPE_INTER'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -type intervention Non rensignée";
				}
			if(($TableauIpop[$c]['PROGRAMMATION'] == 'NULL') or (strlen($TableauIpop[$c]['PROGRAMMATION'] )< 1))  
				{
					$TableauIpop[$c]['Err'] .= "<br> -Programmation Non rensignée";}
			if(($TableauIpop[$c]['CHIRURGIEN'] == 'NULL') or (strlen($TableauIpop[$c]['CHIRURGIEN'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Chirurgien Non rensignée";
				}
			if(($TableauIpop[$c]['ANESTHESISTE'] == 'NULL') or (strlen($TableauIpop[$c]['ANESTHESISTE'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Anesthesiste Non rensignée";
				}
			if(($TableauIpop[$c]['NOM_INTERNE'] == 'NULL') or (strlen($TableauIpop[$c]['NOM_INTERNE'] )< 1)) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -Nom_interne Non rensignée";
				}
			if(($TableauIpop[$c]['NIP'] == 'NULL') or (strlen($TableauIpop[$c]['NIP'] )< 1)  ) 
				{
					$TableauIpop[$c]['Err'] .= "<br> -NIP Non rensignée ou invalide  !!";
					$TableauIpop[$c]['NIP']=''; 
				}
			if(($TableauIpop[$c]['DATE_NAIS'] == 'NULL') or (strlen($TableauIpop[$c]['DATE_NAIS'] )< 1)  ) 
				{
					$TableauIpop[$c]['DATE_NAIS']=''; 
				}				
			// surime les dernière char spl dans nip	
			$TableauIpop[$c]['NIP']=trim($TableauIpop[$c]['NIP']);
				
		}		
		return $TableauIpop;

	}
	/*
	=================================================================
	UpdateBase($TableauIpop)
	function mettre a jour les erreurs dans le table Errurs de Ipop
	(resultat de query en form de array , connexion Mysql )
	=================================================================
	*/
	function UpdateBase($TableauIpop)
	{
		// si les resulta on maj dans le table
		if (!is_array($TableauIpop)) return;
		$arr_size=count($TableauIpop); 		
		for($c=0; $c < $arr_size;$c++)
		{
			
			//duplicate cheking dans le base mysql before insering a nouvelle ligne
			$SqlChkDupl="SELECT * FROM ".$this->TableIpop." WHERE nip='".(addslashes($TableauIpop[$c]['NIP']))."' 
						AND date_intervention='".CommonFunctions::Normal2Mysql($TableauIpop[$c]["DATE_INTERVENTION"])."'";
			$chk_dupl=parent::select($SqlChkDupl);
			$err=" ";
			// force default value pour les champs si vide , pour éviter l'erreur SQL
			//***** contole a faire
 		
			// PREPARE SQL
			// SQL common pour update ou insert
			$sql_temp=" 
			
					base_ipop_id				= '".(addslashes($TableauIpop[$c]['IPOP_ID']))."',
					bloc								= '".(addslashes($TableauIpop[$c]['BLOC']))."',
					site	       				= '".(addslashes($this->Site))."',
					um_de_travail      	= '".(addslashes($TableauIpop[$c]['UM_DE_TRAVAIL']))."',
					date_prevue        	= '".(addslashes($TableauIpop[$c]['DATE_PREVUE']))."',
					date_intervention  	= '".(addslashes(CommonFunctions::Normal2Mysql($TableauIpop[$c]['DATE_INTERVENTION'])))."',
					heure_entree_salle 	= '".(addslashes($TableauIpop[$c]['HEURE_ENTREE_SALLE']))."',
					heure_sortie_salle 	= '".(addslashes($TableauIpop[$c]['HEURE_SORTIE_SALLE']))."',
					salle              	= '".(addslashes($TableauIpop[$c]['SALLE']))."',
					type_inter         	= '".(addslashes($TableauIpop[$c]['TYPE_INTER']))."',				
					programmation    		= '".(addslashes($TableauIpop[$c]['PROGRAMMATION']))."',
					chirurgien         	= '".(addslashes($TableauIpop[$c]['CHIRURGIEN']))."',
					anesthesiste       	= '".(addslashes($TableauIpop[$c]['ANESTHESISTE']))."',
					nom_interne        	= '".(addslashes($TableauIpop[$c]['NOM_INTERNE']))."',
					uh        				= '".(addslashes($TableauIpop[$c]['UH']))."',					
					nda         				= '".(addslashes($TableauIpop[$c]['NDA']))."',					
					nip         				= '".(addslashes($TableauIpop[$c]['NIP']))."',
					nom         				= '".(addslashes($TableauIpop[$c]['NOM']))."',
					prenom         			= '".(addslashes($TableauIpop[$c]['PRENOM']))."',								
					dt_nais         		= '".(addslashes(CommonFunctions::Normal2Mysql($TableauIpop[$c]['DATE_NAIS'])))."',
					err         				= '".(addslashes($err))."'";				
			if(count($chk_dupl) > 0){
				$sql= "UPDATE ".$this->TableIpop." set ".$sql_temp. " WHERE 
						 date_intervention  = '".CommonFunctions::Normal2Mysql($TableauIpop[$c]['DATE_INTERVENTION'])."' AND
						 nip = '".$TableauIpop[$c]['NIP']."'";
				parent::update($sql);
				print "<br> !!! Duplicate entry NIP: ".$TableauIpop[$c]['NIP']." Date_intervention  = '".CommonFunctions::Normal2Mysql($TableauIpop[$c]['DATE_INTERVENTION']);

			}else	{
				$sql= "INSERT INTO ".$this->TableIpop." set 
							nip_ok       				= '".(addslashes($TableauIpop[$c]['NIP']))."',".$sql_temp;
				parent::insert($sql);
				print "<br> Insertion  NIP: ".$TableauIpop[$c]['NIP']." Date_intervention  = '".CommonFunctions::Normal2Mysql($TableauIpop[$c]['DATE_INTERVENTION']);							
			}

		}
	}// fin function MAJ 

	//----------------------------------------------------------
	// mettre a jour  patient identification dans la tableau IPOP
	//----------------------------------------------------------	
	function GetPatInfo($ConnSimpa,$TableauIpop){
		$Taille=Count($TableauIpop);
		for($i=0;$i < $Taille; $i++)
		{
			//----------------------------------
			//	patient identités 
			//	pourquoi dans PAT ? pourquoi pas SDO ?
			//---------------------------------
			$nip=substr($TableauIpop[$i]['NIP'],0,10);
			$sql="Select NOIP,NMMAL as nom,
									NMPMAL as prenom,
									TO_CHAR(DANAIS,'DD/MM/YYYY') dt_nais
						FROM PAT 
						WHERE noip='".substr($TableauIpop[$i]['NIP'],0,10)."'";
			$res=$ConnSimpa->OraSelect($sql);
 			if( count($res) > 0)
			{
				$TableauIpop[$i]['NOM']			=$res[0]['NOM'];
				$TableauIpop[$i]['PRENOM']		=$res[0]['PRENOM'];
				if (strlen($res[0]['DT_NAIS']) < 1)
					$TableauIpop[$i]['DATE_NAIS']	="//";
				else
					$TableauIpop[$i]['DATE_NAIS']	=$res[0]['DT_NAIS'];			
			}
			
		//--------------------------------
		//	sejours info from sdo et epi
		//-----------------------------------
		$sql ="SELECT epi.kynoda as NDA, UE as UH from epi,sdo 
					WHERE  sdo.kynoda= epi.kynoda 
					AND sdo.kynoip='".trim($TableauIpop[$i]['NIP'])."'
					AND to_date('".$TableauIpop[$i]['DATE_INTERVENTION']."','DD/MM/YYYY') between D8EEUE and D8SOUE";		
		$res=$ConnSimpa->OraSelect($sql);
 		if( count($res) > 0){
			$TableauIpop[$i]['NDA']			=$res[0]['NDA'];
			$TableauIpop[$i]['UH']			=$res[0]['UH'];
		}
		}
		return $TableauIpop;	
	}   
 
	/*
	============================================================
	function SejourEnError($nda)
	return ipop ID si non false
	=================================================================	
	*/
	function IsSejourCoded($nip,$DtDebSejour,$DtFinSejour)
	{
		if (strlen($nip) !=10){
			echo "Controle Ipop :: Invalide NIP :".$nda;
			return false;
		}
		
		// check les erreur dans Ipop   //='".$nip."'
		$sql=" SELECT id,nip
 						FROM `ipop` 
						WHERE nip like '$nip%'  
						AND Date_intervention   >= '".$DtDebSejour."'			
						AND Date_intervention   <= '".$DtFinSejour	."'";			
		$res_ipop=$this->select($sql);

		// si on a trouvé au moins un ligne, il y a des erreurs dans cette sejours
		
	
		 
		if (count($res_ipop) > 0)
			// on va cherches les actes coded dans SAG si non on retourne vide
			
			return $res_ipop[0]['id']; // IsSejourCoded = YES
		else
			return false; // IsSejourCoded = Non
	
	}

	/*
	============================================================
	function SejourEnError($nda)
	return ipop ID si non false
	=================================================================	
	*/
	function IsSejourBloc($nip,$DtDebSejour,$DtFinSejour)
	{
		if (strlen($nip) !=10){
			echo "Controle Ipop :: Invalide NIP :".$nda;
			return "-";
		}
		
		// check les erreur dans Ipop   //='".$nip."'
		$sql=" SELECT id,nip
 						FROM `ipop` 
						WHERE nip like '$nip%'  
						AND Date_intervention   >= '".$DtDebSejour."'			
						AND Date_intervention   <= '".$DtFinSejour	."'";			
		$res_ipop=$this->select($sql);

		// si on a trouvé au moins un ligne, il y a des erreurs dans cette sejours
		
	
		 
		if (count($res_ipop) > 0)
			// on va cherches les actes coded dans SAG si non on retourne vide
			
			return $res_ipop[0]['id']; // IsSejourCoded = YES
		else
			return "-"; // IsSejourCoded = Non
	
	}

 
	/*
	============================================================
	function GetSejours($Service="",$Anesthesiste="",$Chirurgien="");
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetSejours($Service="",$Anesthesiste="",$Chirurgien="")
	{
		$filtre_sql="";

		//Service
		if ((strlen($Service) > 0) and ($Service !="TOUS"))
		{
			$filtre_sql.= " AND ".$this->Table_bloc_structure.".lib_service='".$Service."' ";					
		}

		//Chirurgien
		if ((strlen($Chirurgien) > 0) and ($Chirurgien !="TOUS"))
		{
			$filtre_sql.= " AND ".$this->TableIpop.".Chirurgien='".$Chirurgien."' ";					
		}
		
		//Anesthesiste
		if ((strlen($Anesthesiste) > 0) and ($Anesthesiste !="TOUS"))
		{
			$filtre_sql.= " AND ".$this->TableIpop.".Anesthesiste='".$Anesthesiste."' ";					
		}
		$sql = "SELECT * from ".$this->TableIpop." LEFT JOIN ".$this->Table_bloc_structure." ON ".$this->Table_bloc_structure.".ug = ".$this->TableIpop.".um_de_travail
					WHERE date_intervention   between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
					AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
					AND ".$this->TableIpop.".site='".$this->Site."' 
					$filtre_sql
		    	ORDER BY  date_intervention,um_de_travail";					

		$data=parent::select($sql);
		return $data;
	}
 
	/*
	============================================================
	function GetAllServices();
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetAllServices()
	{

	  $sql = "SELECT lib_service from ".$this->Table_bloc_structure." where site='".$_SESSION['site']."' group by lib_service order by lib_service";
		$data=parent::select($sql);
		return $data;
	}

	/*
	============================================================
	function GetAllServices();
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetAllAnesthesiste()
	{

	  $sql = "SELECT Anesthesiste from ".$this->TableIpop." where site='".$_SESSION['site']."' and Anesthesiste is not null and Anesthesiste<>'' group by Anesthesiste order by Anesthesiste";
		$data=parent::select($sql);
		return $data;
	}


	/*
	============================================================
	function GetAllServices();
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetAllChirurgien()
	{

	  $sql = "SELECT Chirurgien from ".$this->TableIpop." where site='".$_SESSION['site']."' and Chirurgien is not null and Chirurgien<>'' group by Chirurgien order by Chirurgien";
		$data=parent::select($sql);
		return $data;
	}
	/*
	============================================================
	function GetDateIntervention($id)
	return date intervention de id(utilisé pour recupare les acted coded dans sag
	=================================================================	
	*/
	function GetDateIntervention($id)
	{

	  $sql = "SELECT Date_intervention from ".$this->TableIpop." where id='".$id."'";
		$data=parent::select($sql);
		return $data[0]['Date_intervention'];
	}


	/*
	============================================================
	GetIpopDataParId($id)
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetIpopDataParId($id)
	{

	$sql="SELECT * FROM ".$this->TableIpop." 
	 		LEFT JOIN ".$this->Table_bloc_structure." ON ".$this->Table_bloc_structure.".ug = ".$this->TableIpop.".um_de_travail
			where ".$this->TableIpop.".id='".$id."'";

		$data=parent::select($sql);
		return $data;
	}


	/*
	============================================================
	Intranet_V2
	UpdateActes($ipop_id,$actes)
	mettre a jours les avecs dans le table pour éviter le temps d'attends
	=================================================================	
	*/
	function UpdateActes($ipop_id,$actes)
	{
	if ($actes=="&nbsp;")
		$actes="NULL";
	else
		$actes="'$actes'";
		
	$sql_update="UPDATE ".$this->TableIpop." set actes = ".$actes."
			where ".$this->TableIpop.".id='".$ipop_id."'";

		$data=parent::update($sql_update);
		return $data;
	}


}//FIN CLASS
?>
