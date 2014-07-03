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
class IpopAghate extends MySQL
{
 	var $Structure; 			// Ipop file structure

 	//Constructeur 
 	//-----------------------------------------------------------------
	function IpopAghate()
	{
		//initialise connexion Mysql
		parent::MySQL(); 
		
		//initialise file structure
		$this->Structure=array(
					"ipop_id",
					"bloc",
					"um_de_travail",
					"date_prevue",
					"date_intervention",
					"heure_entree_salle",
					"heure_sortie_salle",
					"duree_intervention",
					"salle",
					"type_inter",
					"programmation",
					"chirurgien_responsable",
					"chirurgien",
					"anesthesiste",
					"nom_interne",
					"nip",
					"commentaire",
					"ambulatoire",
					"commentaire2",
					"etat",
					"service"
					);		
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
 	/*==============================================================================
 	 funtion LireFichierIpop ($FichierIpop)
 	 Lire el fichier Ipop et mettre dans un array
 	==============================================================================*/
	function PrepareMappingArray($FicherMapping){
		// vérify les fichier Ipop est declaré
		if (strlen($FicherMapping)< 1){
			echo "<br>Fichier Ipop config non declaré";
			exit;
		}
		// vérify les fichier Ipop est declaré
		if (!is_file($FicherMapping)){
			echo "<h2>Fichier introuvable :".$FicherMapping. "</h2>";
			exit;
		}		
		// lit fichier
		$FileContent = file($FicherMapping);
		$arr_size=count($FileContent);
		$new_compteur=0;
		$MappingRes = array();
		for($i=0;$i < $arr_size;$i++)
		{
			// explode ligne dans deux array
			list($NomSrvExcel,$NomAgt)=explode(";",$FileContent[$i]); 
			//si autre key a revoir
			$dataExcel	= $NomSrvExcel;
			$dataAgt 	= $NomAgt;
			$MappingRes[$dataExcel] = $dataAgt;
		}
	 	return $MappingRes;
	}

 
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
									TO_CHAR(DANAIS,'DD/MM/YYYY') dt_nais, CDSEXM
						FROM PAT 
						WHERE noip='".substr($TableauIpop[$i]['NIP'],0,10)."'";
			$res=$ConnSimpa->OraSelect($sql);
 			if( count($res) > 0)
			{
				$TableauIpop[$i]['NOM']			=$res[0]['NOM'];
				$TableauIpop[$i]['PRENOM']		=$res[0]['PRENOM'];
				$TableauIpop[$i]['SEX']			=$res[0]['CDSEXM'];
				if (strlen($res[0]['DT_NAIS']) < 1)
					$TableauIpop[$i]['DATE_NAIS']	="//";
				else
					$TableauIpop[$i]['DATE_NAIS']	=$res[0]['DT_NAIS'];			
			}
			
			//--------------------------------
			//	sejours info from sdo et epi
			//-----------------------------------
			/*$sql ="SELECT epi.kynoda as NDA, UE as UH from epi,sdo 
						WHERE  sdo.kynoda= epi.kynoda 
						AND sdo.kynoip='".trim($TableauIpop[$i]['NIP'])."'
						AND to_date('".$TableauIpop[$i]['DATE_INTERVENTION']."','DD/MM/YYYY') between D8EEUE and D8SOUE";		
			$res=$ConnSimpa->OraSelect($sql);
			if( count($res) > 0){
				$TableauIpop[$i]['NDA']			=$res[0]['NDA'];
				$TableauIpop[$i]['UH']			=$res[0]['UH'];
			}*/
		}
		return $TableauIpop;	
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

 	
	/*==========================================================================
	function BackupIpop($Tableau)
	* Recupere les donnes du fichier d'extraction
	* Controle de la ligne :
	* 	si pas de différence => deuxieme controle  
	* 	si difference => desactive la ligne courant dans la base puis 
	* 	insertion d'une nouvelle ligne
	==========================================================================
	*/
	function  BackupIpop($DataIpop)
	{
		//ctrl+alt+u pour minuscule
		//$sql create table if not exist
		//---------------------------------
		$Sql_create="CREATE TABLE IF NOT EXISTS ipop_backup (
					ipop_id		 			int(11) NOT NULL,
					bloc 					varchar(50) NOT NULL DEFAULT '',
					um_de_travail			varchar(50) NOT NULL DEFAULT '',
					date_prevue				VARCHAR(50) NOT NULL DEFAULT '',
					date_intervention		VARCHAR(50) NOT NULL DEFAULT '',
					heure_entree_salle		VARCHAR(50) NOT NULL DEFAULT '',
					heure_sortie_salle		VARCHAR(50) NOT NULL DEFAULT '',
					duree_intervention		VARCHAR (50) NOT NULL DEFAULT '',
					salle					VARCHAR (50) NOT NULL DEFAULT '',
					type_inter				varchar(255) NOT NULL DEFAULT '',
					programmation			varchar(5) NOT NULL, 
					chirurgien_responsable  VARCHAR( 255 ) NOT NULL DEFAULT '',
					chirurgien				varchar(50) NOT NULL DEFAULT '',
					anesthesiste			varchar(50) NOT NULL DEFAULT '',
					nom_interne				varchar(50) NOT NULL DEFAULT '',
					nip						varchar(50) NOT NULL,
					commentaire				varchar(255) NOT NULL,
					ambulatoire				varchar(5) NOT NULL,
					commentaire2			varchar(255) NOT NULL,
					etat					text,
					service					varchar(255) NOT NULL,
					date_maj				timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					tymaj 					VARCHAR (1) NOT NULL DEFAULT 'A'
					)ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";	
 		$this->create($Sql_create);
		$nbr_rows=count($DataIpop);
		for($t=0; $t < $nbr_rows; $t++)
		{
			$sql_data_ins ="";
			$sql_data_chk ="";
			$sql_key		="";
			$sql_cond="";
			//---------------------------------
			//$sql Check deja present
			//---------------------------------
			foreach($DataIpop[$t] as $key => $val){
				if($key == 'IPOP_ID') {
					$Ipop_Id = $val;
				}
				$sql_data_ins.= $key."='".addslashes($val)."',";
				$sql_data_chk.= $key."='".addslashes($val)."' AND ";
				$sql_key	  .= $key.","; 
			}		
			
			// suprime last virgule
			$sql_data_ins = substr($sql_data_ins,0, -1);
			$sql_data_chk = substr($sql_data_chk,0, -4);
			$sql_key	  = substr($sql_key,0, -1);
			
			// premier controle sans ipop_id
			$sql_check="select ".$sql_key." from ipop_backup where " .$sql_data_chk. " 
						AND TYMAJ <>'D'";
			$res=$this->select($sql_check);
			$sql_cond = $sql_data_chk;
			//si pas de resultat=> faire 2eme controle avec id			 
			if (count($res)< 1 || empty($res) || !(is_array($res))) 
			{
				$sql_cond	= "IPOP_ID = '".$Ipop_Id."' 
									AND TYMAJ <>'D'";
				//deuxieme controle avec l id
				$sql_check2	= "SELECT ".$sql_key."  FROM ipop_backup WHERE ".$sql_cond;
				$res2=$this->select($sql_check2);
				//array check
				if(count($res2) > 0 && is_array($res2)){
					$result = array_diff($res2[0], $DataIpop[$t]); // retourne les differences
				}
				//si pas de res => on insere la ligne
				else{		
					$sql_insert="INSERT INTO ipop_backup set " .$sql_data_ins;
					$this->insert($sql_insert);			
					//echo "<br>ligne inserer";		
				}
			}
			// il y a un resultat donc controler les champs
			else{
				$result = array_diff($res[0], $DataIpop[$t]); // retourne les differences
			}
			
			//si resultat est vide => pas de diff
			if(count($result)< 1 || empty($result) || !(is_array($result))){
				//echo "Acune difference <br>";
				//exit;
			} 
			//il y a des differences => desactiver la ligne, creer une nouvelle
			else{
				//echo "<br>difference maj et insertion de la nouvelle ligne";
				//Maj => desactiver la ligne
				$sql_desactive = "UPDATE ipop_backup SET TYMAJ = 'D' 
									WHERE $sql_cond ";
				$res	= $this->update($sql_desactive);
				//Insert nouvelle ligne					
				$sql_ins	 = "INSERT INTO ipop_backup SET ".$sql_data_ins;
				$res	= $this->insert($sql_ins);
			}
		}
	}
	
	/*==========================================================================
	function GetModifFromDate($date)
	* retourne l'ensemble des lignes modifiés a partir de la date 
	* de mise a jour $date
	==========================================================================
	*/
	function GetModifFromDate($date){
		$sql = "SELECT * FROM ipop_backup where 
					DATE_MAJ >= '".$date."' AND TYMAJ <> 'D'";
		return $this->select($sql);
	}	



}//FIN CLASS
?>
