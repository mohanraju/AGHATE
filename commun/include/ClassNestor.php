<?php
/*
###################################################################################################
#
#											OBJET NESTOR
#											
#
#							By Mohanraju @ APHP
# last modified le 21/05/2012
###################################################################################################
*/
class Nestor extends MySQL
{
	var $DateDeb;
	var $DateFin;
 	var $FichierNestor; 			// nestor.rum sortie par l'outil nestor
 	var $FichierErreurCodes; 	// fichier code erreurs 
 	var $ErreurCodesNestor; 	// Possible error codes dans le fichier nestor ARRAY
 	var $Err;									// Erreurs de ce traitements sont dans un array  
 	var $warn;								// Warning de ce traitements sont dans un array  
 	var $Trace;								// Trace de ce traitements sont dans un array  
 	var $Structure; 					// nestor file structure
 	var $Site	;								// Code du Hopital ex 076 pour sls, 047 pour lrb ....
 	var $TableNestor ;         // nom de Table nestor dans mysql ::permet de defrentiser si plusiers sites...
	var $VersionRegle;

 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Nestor($Site)
	{
		$this->Site=$Site;
		parent::MySQL(); //initlaise connexion Mysql
		$this->VersionRegle="Inconnu";
	}

 	/*
 	==============================================================================
 	function SetTableNestor($NomduTable)
 	==============================================================================
 	*/
	function SetTableNestor($NomduTable)
	{
		$this->TableNestor=$NomduTable;
	}
 	/*
 	==============================================================================
 	function SetTableNestor($NomduTable)
 	==============================================================================
 	*/
	function SetVersionRegle($Regle)
	{
		$this->VersionRegle=$Regle;
	}
 	/*
 	==============================================================================
 	funtion SetPeroide (date_deb et date_fin )
 	initalise les peroides dans nestor;
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
 	funtion SetFicNestor (Nom de fichier nestor)
 	$nom de fichier peut être chemin relatif
 	vérify presence du ficheir dans le dossier si non le traitement sera annulé
 	==============================================================================
 	*/
	function SetFicNestor($fichier)
	{
		if(!is_file($fichier))
		{
			$this->Err[]= "Le fichier Nestor (".$fichier.")introuvable ou unable to lire ";
		}else
		{
			$this->FichierNestor=$fichier;
		}
	}

	/*
	==============================================================================
	Function Init()
	on consider tous les erreurs sont corrigé,
	si l'erreur reaparrais remis le corrige tag=non	 donc corrigé ='1'
	initialise les code d'erreurs = oui  donc corrigé ='1'
	pendant l'anayse du fichier nestor on remis corrig"='0' si l'erreur persiste encore
	==============================================================================
	*/
	
	function UpdateStatus()
	{
		// on bascule tous les status corrige=1 , donc tous corrigé
		$sql_update="UPDATE  ".$this->TableNestor." set corrige='1' WHERE site='".$this->Site."' and corrige='0'";
		echo "<br>Nestor:: mettre a jour status corrige <br>";
		$nbr_rows=$this->update($sql_update);
		echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre rows affecté =".$nbr_rows;		
	}
	
	
	
 	/*
 	==============================================================================
 	funtion SetCodeErreurNestor (Nom de fichier code etteur)
 	$nom de fichier peut être chemin relatif
 	vérify presence du ficheir dans le dossier si non le traitement sera annulé
 	retourne form de tableux les code erreurs
 	==============================================================================
 	*/
	function SetCodeErreurNestor($fichier)
	{
		$code_file;
		$retval=array();
		if(!is_file($fichier))
		{
			$this->Err[]= "<br>Nestor::Le fichier code erreurs Nestor (".$fichier.")introuvable ou unable to lire ";
		}else
		{
			$this->FichierErreurCodes=$fichier;
			//Lire le libelle et les codes et mettre dans un tableau
			$code_file=	file($fichier);
			$arr_size=count($code_file);
			for($i=0;$i < $arr_size;$i++){
				list($err,$err_lib)=explode(";",$code_file[$i]);
				$retval[$err]=$err_lib;	
			}
		}
		$this->ErreurCodesNestor= $retval;			
	}
	
 

	
 	/*
 	==============================================================================
 	 funtion TraiteSQL ($FicSQL)
 	 les requette spl pour sortir les erreurs de SIMPA
 	 les sqls sont mis dans uu fichier text, il faut respecter certains conditions  (attn voir mohan )
 	 le collonne selecté doit structuré sans doublons!!
 	 NODA,DATE_DEB,DATE_FIN,DUSEJ,CDGHM,'CODE_ERR',URM, GHM, LBGHM, DESC_ERR,TYPE_ERR
 	 vérify presence du ficheir dans le dossier si non aucune traitement sera fait
 	 execute les sql et retourne les resultats dans un format tableau 
 	==============================================================================
 	*/
	function AddControlViaSQL($FicSQL,$ObjetSimpa)
	{
		$code_file;
		$retval=array();
		if(!is_file($FicSQL))
		{
			$this->Err[]= "Le fichier SQL (".$FicSQL.")introuvable ou unable to lire ";
		}else
		{
			// vérify les connexion Oracle sinon  initialise le
			//if ($this->ConnectOracle("consult","consult" )){
				$date_deb=$this->DateDeb;
				$date_fin=$this->DateFin;
				include($FicSQL); // dans ce fichier les query sont declarées dans un tableau $sql[]		

				// just boucle sur l'array $sql		
				$arr_size=count($sql);
				for($n=0;$n < $arr_size;$n++){
					$res =$ObjetSimpa->OraSelect($sql[$n]);
					foreach($res as $val){
						$result[]=$val;
					}
				}
				
				return $result;	
		}	
	}
	
 	/*==============================================================================
 	 funtion LireFichierNestor ($Option="ERREUR")
 	 Lire el fichier nestor et retourne les resultat 
 	 opition 
 	 				ERREUR	=	retourne les lignes avec erreur
 	 				CTRLOK	= retorune uniquelens les lignes avec CTRL OK
 	 				TOUS 		= retourne tous les lignes 
 	 
 	==============================================================================*/
	function LireFichierNestor($option="ERREUR")
	{
		/* Sructure nestor
			[0] => BN 
			[1] => BN 
			[2] => RetFg_CODE 
			[3] => 761007018 
			[4] => 00000000000106931812 
			[5] => 20100610 
			[6] => 0581 
			[7] => 
			[8] => 20100218 
			[9] => 20100331 
			[10] => 90Z00Z 
			[11] => 
			[12] => CTRLAPHP0709.dat 
			[13] => 121 
			[14] => RetFg_CODE  		
		*/
		
 		// vérify les fichier nestor est declaré
		if (	strlen($this->FichierNestor)< 1){
			$this->Err[]="Nestor :: Fichier nestor_???.fic non declaré dans  config !!!";	
			return false;
		}
		// lire le ficheir et mettre dans un array
		$lines = file($this->FichierNestor);
		
		// compteur pour la resultat de retour
		$new_compteur=0;
		
		// boucle par chaque linge 
		$arr_size=count($lines);
		for($i=0;$i < $arr_size;$i++)
		{
			// explode le ligne dans un tableu
			$data=explode(";",$lines[$i]); 
			$check_col=trim($data[14]);
			
			$chk=false;  //initialise check

			// lire tous les CTRLOK only
			if ( ($check_col == 'CTRLOK' ) and  ($option == 'CTRLOK') )
			{
				$chk=true;	
			}
			
			// lire tous les Erreurs
			if ( ($check_col != 'CTRLOK' ) and  ($option != 'CTRLOK') )
			{
				$chk=true;	
			}
			
 
			// prepare un nouveau tableau par rapport l'option
			// on traite que le BN et AN
 
			if($chk){
				$urm=$data[6];// format urm dans le nestor XXXX
				$_urm=$urm; // garde l'urm PMSI ?????????????????????????????????? A Voir celle-ci est un bon methode ?
				if ((substr($urm,3,1)=="J") or (substr($urm,3,1)=="C") or (substr($urm,3,1)=="D") ){
						$urm	=substr($urm,0,3);
				}else{
						$urm	=substr($urm,1,3);
				}
 
				
				// remettre dans un nouveau tableau
				$res[$new_compteur]['NDA']=substr($data[3],0,9);
				$res[$new_compteur]['NAS']=$data[3];
				$res[$new_compteur]['URM']=$urm;	
				//le dates sont au format YYYYMMDD convert it to format Mysql
				$res[$new_compteur]['DATE_DEB'] =substr($data[8],0,4)."-".substr($data[8],4,2)."-".substr($data[8],6,2);
				$res[$new_compteur]['DATE_FIN'] =substr($data[9],0,4)."-".substr($data[9],4,2)."-".substr($data[9],6,2);
				$res[$new_compteur]['GHM']=$data[10];
				$res[$new_compteur]['CDERR']=$data[13];
				$res[$new_compteur]['DUSEJ']='';
				$res[$new_compteur]['LBGHM']='';
				$res[$new_compteur]['LBERR']=$this->ErreurCodesNestor[$data[13]]; //remttre le liblle du code correponds
				$res[$new_compteur]['TYPE_ERR']='NESTOR';
				$res[$new_compteur]['CTRL_NESTOR']=$data[0];
				$res[$new_compteur]['CTRLOK']=$data[14];				
				$new_compteur++;				
			}
		}
	 	return $res;
	}
	/*
 
	
	/*
	=================================================================
	MAJErreurs($res,$ObjetSimpa)
	function mettre a jour les erreurs dans le table  nestor
	si l'erreur persiste on change le tag 
	si non  on ajoute un ligne 
	=================================================================
	*/

	function MAJErreurs($res,$ObjetSimpa)
	{
		// si les resulat on maj dans le table
		if (!is_array($res)) return;
		
		//on boucle sur l'array
		$arr_size=count($res); 		
		for($c=0; $c < $arr_size;$c++)
		{
			$lbghm 		= $res[$c]['LBGHM'];
			$lberr		= $res[$c]['LBERR'];

			$res[$c]['CTRLOK']=trim($res[$c]['CTRLOK']); //surime trailing spaces
		
			// is this ligne avec erreur ou non
			if (trim($res[$c]['CTRLOK'])== "CTRLOK")
				$ResumeEnErreur=1; // erreur corrigé
			else
				$ResumeEnErreur=0; // erreur persiste

 								

			$trc=$res[$c]['NDA'].", urm:".$res[$c]['URM'].", Sejour du ".$res[$c]['DATE_DEB']." au " .$res[$c]['DATE_FIN'] . ", Err code :".$res[$c]['CDERR'];				
			echo "<br>Résumé :".$trc;
 				
 			/*===================================================================
  		 check erreur duplication 
  		 vérify si le résumé+err existe dajé dans la table 
  		===================================================================*/
			$sql_duplicate="SELECT id,nda,urm from ".$this->TableNestor." 
												where nda   		='".$res[$c]['NDA']."' 
													and urm     	='".$res[$c]['URM']."' 
													and ds      	='".$res[$c]['DATE_FIN']."' 					
													and code_err 	='".$res[$c]['CDERR']."'   
											    and version_regle='".$this->VersionRegle."'" ;													
			echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Check duplicate SQL : ".$sql_duplicate;	
			$chk_duplicate=parent::select($sql_duplicate);
			
			//==============================================================================
			// si le resume Existe dans la table  on force le resume en erreur
			// on force le GHM_last et GHS_last en NULL
			//==============================================================================
			$resume_updated=false;	
			if( (count($chk_duplicate) > 0))
			{
				echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Duplicate oui, on maj statut='0' et GHS=''";
				$sql_update="UPDATE  ".$this->TableNestor." set 
														ghm_last		=NULL, 
														ghs_last		=NULL,
														corrige='0' 
														where id='".$chk_duplicate[0]['id']."'";
				$nbr_rows=$this->update($sql_update);
				echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre rows affecté =".$nbr_rows;
				$resume_updated=true;	
			}
			else
			{
				echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nouvel erreur, inseré dans la table nestor";	
				// récupare le GHS initial
				$GhmGhs=$this->GetGhmGhs($ObjetSimpa,$res[$c]['NDA'],$res[$c]['DATE_FIN']);
				$ghm=$GhmGhs[0];
				$ghs=$GhmGhs[1];
				
				// si non nouvelle erreur insert into table
				$sql_insert="INSERT into ".$this->TableNestor." set 
											date_init		='".date("Y-m-d")."',
											date_last		='".date("Y-m-d")."',
											nda     		='".$res[$c]['NDA']."', 
											nas     		='".$res[$c]['NAS']."', 											
											urm     		='".$res[$c]['URM']."', 
											de      		='".$res[$c]['DATE_DEB']."', 
											ds      		='".$res[$c]['DATE_FIN']."', 
											dusej   		='".$res[$c]['DUSEJ']."', 
											ghm_init		=$ghm, 
											ghm_last		=$ghm, 
											ghs_init		=$ghs, 
											ghs_last		=NULL, 
											code_err		='".$res[$c]['CDERR']."', 
											lib_err 		='".mysql_real_escape_string($lberr)."',
											type_err		='".$res[$c]['TYPE_ERR']."', 
											site    		='".$this->Site."', 
											ctrl_nestor ='".$res[$c]['CTRL_NESTOR']."',											
											version_regle='".$this->VersionRegle."',
											corrige			='".$ResumeEnErreur."' ";								

				$this->insert($sql_insert);
				$resume_updated=true;	

			}			
 		}

	}// fin function MAJ Erreur

	/*
	================================================================
 	Get GHM et GHS from Simpa avant insert ou update 
 	==================================================================	
 	*/
 	
	function GetGhmGhs($ObjetSimpa,$Nda,$Date_Sortie,$TypeSej="HDJ"){

		$sql_ghm="SELECT CDGHM as GHM,NUMGHS as GHS  
							FROM RSS 
					 		WHERE RSS.noda='".$Nda."' 
							AND to_char(RSS.D8FIN,'YYYY-MM-DD') = '".$Date_Sortie."'";
		$res_ghm=$ObjetSimpa->OraSelect($sql_ghm);									

		// on essai sans date 
		if ((count($res_ghm) < 1)){
		$sql_ghm="SELECT CDGHM as GHM,NUMGHS as GHS  
							FROM RSS 
					 		WHERE RSS.noda='".$Nda."'"; 
			$res_ghm=$ObjetSimpa->OraSelect($sql_ghm);									

		}

/*
//req remi a faire
$NAS=trim($NAS)

$sql='select NODA,CDGHM,CDGHS from RSS where TYPSEJ='HC' and NODA='.$NAS.' 
union 
select NODA,CDGHM,CDGHS from RSS where TYPSEJ<>'HC' and NODA||' '||to_char(D8FIN,"YYYYMMDD")='.$NAS
*/
 		if ((count($res_ghm) > 0) and (is_array($res_ghm)))
 		{
 			$ghm="'".$res_ghm[0][GHM]."'";
 			$ghs="'".$res_ghm[0][GHS]."'";
 		}	
 		else
 		{
 			$ghm="NULL";
 			$ghs="NULL";
 		}
 		return(array($ghm,$ghs));
	}

	/*
	=================================================================
	function MajStatut(ObjetSimpa )
	recupare tous les errreurs dans Mysql donc corrige='0' (non)
	si on trouve les statut "TD","TR","VD", "CN" dans ctrl_medical bascule corrige='1' (oui)
	=================================================================	
	*/
	function MajStatut($ObjetSimpa){
		// les status medical dans SIMPA à vérifier
		$statuts = array("TD","TR","VD", "CN");

		// recupares tous les erreurs =oui se trouve dans le table MYSQL
		$sql="select nda,de,ds,urm from ".$this->TableNestor." where corrige='0'";
		$result=parent::select($sql);

		// boucle su les resultat
		$nbr_resume=count($result);
		echo "<br> Nombre resumé a contolé :".$nbr_resume;
		for($i=0;$i < $nbr_resume;$i++){
			$sql= "Select NAS,CDSTATUT from ctrl_medical  WHERE  nas like('".$result[$i]['nda']."%') ";				
			echo "<br>Résume: NDA:".$result[$i]['nda']. ", URM:".$result[$i]['urm']. " SQL :".$sql;

			// verify le statut dans simpa pour chaque NAS
			$data=$ObjetSimpa->OraSelect($sql);	

			$nbr_trouve=count($data);
			for($k=0;$k < $nbr_trouve;$k++){
				if(array_search($data[0]['CDSTATUT'],$statuts) > -1 ){
					
					// NDA unique sans le format nas (donc pas de date de sortie dans le nas)
					if (trim(strlen($data[0]['NAS']))< 11)	{
						$sqlupdate="update ".$this->TableNestor." set corrige='1' where nda='".substr($data[0]['NAS'],0,9)."' AND (  type_err ='NESTOR' ) ";
					}else{
						//recupare les date_sortie de NAS et lance l'update pour ce nas
						$ds=substr($data[0]['NAS'],10,4)."-".substr($data[0]['NAS'],14,2)."-".	substr($data[0]['NAS'],16,2);
						$sqlupdate="update ".$this->TableNestor." set corrige='1' where nda='".substr($data[0]['NAS'],0,9)."' AND ds='".$ds."' AND ( type_err ='NESTOR' ) ";		
					}
					$nbr_rows=$this->update($sqlupdate);
					echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UPDATE SQL :".$sqlupdate;					
					echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre rows affecté =".$nbr_rows;							
				}
			}
		}		
	}

	/*
	=================================================================
	function MajStatut(ObjetSimpa )
	recupare tous les errreurs dans Mysql donc corrige='0' (non)
	si on trouve les statut "TD","TR","VD", "CN" dans ctrl_medical bascule corrige='1' (oui)
	=================================================================	
	*/
	function MajGHS($ObjetSimpa){

		// recupares tous les resume corrige et GHS_LAST vide
		$sql="select id,nda,de,ds,urm from ".$this->TableNestor." where corrige='1' AND (ghs_last is null or ghs_last='')";
		$result=parent::select($sql);

		// boucle su les resultat
		$nbr_resume=count($result);
		echo "<br> Nombre des resumés lesquelle GHS à mettre a jour : ".$nbr_resume;
		for($i=0;$i < $nbr_resume;$i++)
		{
			$GhmGhs=$this->GetGhmGhs($ObjetSimpa,$result[$i]['nda'],$result[$i]['ds']);
			$ghm=$GhmGhs[0];
			$ghs=$GhmGhs[1];
			
			echo "<br>Résume: NDA:".$result[$i]['nda']. ", URM:".$result[$i]['urm']. ", GHS :".$ghs;
			if ($ghs <> "NULL")
			{
				$sql_upt= "UPDATE ".$this->TableNestor." set ghs_last=$ghs, ghm_last=$ghm, date_last='".date("Y-m-d")."' where id='".$result[$i]['id']."'";				
				$nbr_rows=$this->update($sql_upt);
				echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UPDATE SQL :".$sql_upt;					
				echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nombre rows affecté =".$nbr_rows;
			}
			else
			{
				echo "<br> !!!! Erreur !!!!!!!! GHS non trouvé ";	
			}
		}
		
	}

	/*
	=================================================================
	function TableDetail($FiltreUrm,$FitreErreur,$TrierPar,$AfficheValider)
	// par defualt
	$FiltreUrm=""
	$FitreErreur=""
	$TrierPar="URM"
	AFFICHE TABLEAU NESTOR en Detail Format
	=================================================================	
	*/
	function TableDetail($FiltreUrm="",$FitreErreur="",$TrierPar="urm",$AfficheValider){
		// query 
		$sql = "SELECT  nda, urm, de, ds, dusej, ghm_init,  code_err, lib_err, type_err,ctrl_tim from ".$this->TableNestor." 
						WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
						AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
						AND corrige='0'";

		// filtre par urm 						
		if ($FiltreUrm !="")					
			$sql .= "AND urm='".$FiltreUrm."'";					

		// filtre par Code erreur	
		if ($FitreErreur !="")					
			$sql .= "AND code_err='".$FitreErreur."'";					

		// filtre par valider	
		if ($AfficheValider !=1)					
			$sql .= " AND (ctrl_tim != 'VD' or ctrl_tim is null) " ;	

		// count nombre de NDA dans le requette
		$count_sql=$sql." Group by nda";	

		//ajoute l'order by 
		$sql .= "		order by ".$TrierPar .", ds";		
//echo $sql;
		$data=parent::select($sql);
		$count_res=parent::select($count_sql);
		$nbr_nda=count($count_res);
	 
 	
		return $data;
	}//fin table detail
	/*
	=================================================================
	function TableDetailCountNda($FiltreUrm,$FitreErreur,$TrierPar)
	// par defualt
	$FiltreUrm=""
	$FitreErreur=""
	$TrierPar="URM"
	AFFICHE TABLEAU NESTOR en Detail Format
	=================================================================	
	*/
	function TableDetailCountNda($FiltreUrm="",$FitreErreur="",$TrierPar="urm",$AfficheValider){
		// query 
		$sql = "SELECT  nda, urm, de, ds, dusej, ghm_init,  code_err, lib_err, type_err,ctrl_tim from ".$this->TableNestor." 
						WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
						AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
						AND corrige='0'";

		// filtre par urm 						
		if ($FiltreUrm !="")					
			$sql .= "AND urm='".$FiltreUrm."'";					

		// filtre par Code erreur	
		if ($FitreErreur !="")					
			$sql .= "AND code_err='".$FitreErreur."'";					
		// filtre par validation local
		if ($AfficheValider !=1)					
			$sql .= " AND (ctrl_tim != 'VD' or ctrl_tim is null) ";	

		// count nombre de NDA dans le requette
		$count_sql=$sql." Group by nda";	

		$count_res=parent::select($count_sql);
		$nbr_nda=count($count_res);
	 
 	
		return $nbr_nda;
	}//fin table detail

	/*
	============================================================
	function TableGlobal($Liste)
	soit le $Liste par URM ou Par CodeErr
	// par defualt
  $Liste=ERREUR
	AFFICHE TABLEAU NESTOR en Detail Format
	=================================================================	
	*/
	function TableGlobal($Liste="ERREUR",$AfficheValider)
	{

		if ($AfficheValider !=1)					
			$sql_valid = " AND (ctrl_tim != 'VD' or ctrl_tim is null) ";	

		// prepare SQL par rappoer le choix
		// si par URM
		if($Liste=="URM")
		{
			$sql = "SELECT  urm, count(nda) as nbr from ".$this->TableNestor." 
							WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
							AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
							AND corrige='0'
							$sql_valid
							group by urm order by urm";			
 
		}
		else
		{
			// si par Code Erreur
			$sql = "SELECT  code_err,lib_err, count(nda) as nbr from ".$this->TableNestor." 
								WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
								AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
								AND corrige='0'
								$sql_valid
								group by code_err order by code_err ";			
 			

		}
 
		// count nombre des NDA
		$data			=	parent::select($sql);

 	
		return $data;
	}//fin table detail

	/*
	============================================================
	function TableGlobalCountNda($Liste)
	soit le $Liste par URM ou Par CodeErr
	// par defualt
  $Liste=ERREUR
	=================================================================	
	*/
	function TableGlobalCountNda($Liste="ERREUR",$AfficheValider){
			// filtre par valider	
 
		if ($AfficheValider !=1)					
			$sql_valid = " AND (ctrl_tim != 'VD' or ctrl_tim is null) ";	

		if($Liste=="URM")
		{
			$sql_count = "SELECT  distinct(nda) as nbr from ".$this->TableNestor." 
										WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
										AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
										AND corrige='0'
										$sql_valid
										group by nda";	
		}
		else
		{
			// si par Code Erreur
			$sql_count = "SELECT  distinct(nda) as nbr from ".$this->TableNestor." 
								WHERE ds between '". CommonFunctions::Normal2Mysql($this->DateDeb)."' 
								AND '". CommonFunctions::Normal2Mysql($this->DateFin)."' 
								AND corrige='0'
								$sql_valid
								group by nda ";				

		}
		
 
		// count nombre des NDA
		$nbr_sql	=	parent::select($sql_count);
		$nbr_nda	=	count($nbr_sql);
		return $nbr_nda;
	}//fin table detail

	/*
	============================================================
	function CheckInderdit($code,$diag)
	$code= code cim
	$diag= DP, DR, DAS
	vérify le code diag interdit ou non 
	=================================================================	
	*/
	function CheckInderdit($code,$diag){
		if($diag=='DP')
			$sql_diag= " and ctrl_dp='DP' ";
		else if($diag=='DR')
			$sql_diag= " and ctrl_dr='DR' ";
		else if($diag=='DAS')
			$sql_diag= " and ctrl_das	='DAS' ";
		else	
			$sql_diag=" ";
			
		$sql="SELECT	code_cim from cim10 where code_cim='$code' ".$sql_diag;

		$res=parent::select($sql);
		if (count($res) >0){
			if (strlen($res[0]['code_cim'])> 0){
				return true;
			}else{
				return false;				
			}
		}else{
				return false;
		}
	}
	/*
	============================================================
	function Checkcma($diag)
	$diag= code cim
	Vérifie niveau sevrité d'un CODE CIM
	=================================================================	
	*/	
	function Checkcma($diag){
		$sql="SELECT	cma from cim10 where code_cim='$diag'";
		$res=parent::select($sql);
		if (count($res) >0){
			if (strlen($res[0]['cma'])> 0){
				return $res[0]['cma'];
			}else{
				return "";				
			}
		}else{
				return "";
		}
		
		
	}
	/*
	============================================================
	function GetErrorsByNda($Nda,$date_sortie="")
	=================================================================	
	*/
	function GetErrorsByNda($nda,$date_sortie="")
	{
		if ($date_sortie=="")
		{
			$sql="SELECT * from ".$this->TableNestor." where nda='$nda'  order by date_init ";
		}
		else
		{
			$sql="SELECT * from ".$this->TableNestor." where nda='$nda' and ds='".CommonFunctions::Normal2Mysql($date_sortie)."' order by date_init ";	
		}	
		$res=parent::select($sql);
		return $res;
	}

	/*
	============================================================
	function GetErrorsByNda()
	=================================================================	
	*/
	function GetUrmList()
	{
		$sql="select urm from ".$this->TableNestor." group by urm order by urm";
		$res=parent::select($sql);
		return $res;
	}
	/*
	============================================================
	function GetErrCodeList()
	=================================================================	
	*/
	function GetErrCodeList()
	{
		$sql="SELECT code_err from ".$this->TableNestor." group by code_err order by code_err";
		$res=parent::select($sql);
		return $res;
	}

	/*
	============================================================
	function SetUpdateCommentaires($nda,$dt_sor,$user,$ctrl,$commentaire)
	$nda et $dt_sor pour construre le NAS
	=================================================================	
	*/
	function UpdateNestorCommentaires($nda,$dt_sor,$user,$ctrl,$commentaire)
	{
		$sql="INSERT INTO nestor_ctrl set 
									date_maj		='".date("Y-m-d")."',	
									nas					='".$nda.$dt_sor."',
									user				='".$user. "',
									ctrl				='".$ctrl ."',
									commentaire	='".$commentaire."'";
		$res=parent::insert($sql);					
																			
	}	

	/*
	============================================================
	function SetUpdateCommentaires($nda,$dt_sor,$user,$ctrl,$commentaire)
	$nda et $dt_sor pour construre le NAS
	=================================================================	
	*/
	function UpdateNestorVd($nda,$dt_sor,$user,$ctrl,$commentaire)
	{
		// update table commentaires
		$sql="INSERT INTO nestor_ctrl set 
									date_maj		='".date("Y-m-d")."',	
									nas					='".$nda.$dt_sor."',
									user				='".$user. "',
									ctrl				='".$ctrl ."',
									commentaire	='".$commentaire."'";
		$res=parent::insert($sql);					

		// update table nestor
		$sql="UPDATE ".$this->TableNestor." set
					ctrl_tim	='".$ctrl ."'
					WHERE	nda	='".$nda."' 		
					AND ds		='".CommonFunctions::Normal2Mysql($dt_sor)."'";
		$res=parent::update($sql);
				
																			
	}	

	/*
	============================================================
	function GetCommentaires($nda,$dt_sor)
	return en form de tableau
	=================================================================	
	*/
	function GetCommentaires($nda,$dt_sor)
	{
		$sql="SELECT * from  nestor_ctrl where   nas like('%".$nda.$dt_sor."%') order by date_maj" ;

		$res=parent::select($sql);	
		return $res;				
																			
	}	
	/*
	============================================================
	function SejourEnError($nda)
	return true  or false 
	=================================================================	
	*/
	function SejourEnError($nda){
		if (strlen($nda) !=9){
			echo "Controle Nestor :: Invalide NDA :".$nda;
			return false;
		}
		if (!$this->TableNestor)
		 $this->SetTableNestor();
		// check les erreur dans nestor
		$sql="select corrige from ".$this->TableNestor." where nda='".$nda."' and  corrige='0' ";			
		$nbr_res=$this->select($sql);
		// si on a trouvé au moins un ligne, il y ades erreurs dans cette sejours 
 
		if (count($nbr_res) > 0)
			return true; // erreur = YES
		else
			return false; // erreur = YES
 
	}	
	/*
	============================================================
	function GetCommentaires($nda,$dt_sor)
	return en form de tableau
	=================================================================	
	*/
	function ValideResume($id,$ctrl)
	{
		$sql="UPDATE ".$this->TableNestor ." set ctrl_tim='".$ctrl."' where id='".$id."'";
 
		$res=parent::update($sql);	
		return $res;				
																			
	}	
 

	/*
	============================================================
	function Erreurs($Liste)
	Print all erreur de cette CALSS
	si aucune parametre recu, le denireer erreur sera affiche 
	si non all erreur seront affichées
	=================================================================	
	*/
	function Erreurs($pos="LAST"){
		$tbl=$this->Err;
		if($pos=="LAST"){
			echo $this->Err[count($this->Err)-1];
		}else{
			if (count($tbl) >0){
				foreach ($tbl as $value) {
			    echo "<br> ".$value;
			  }
			}else{
				echo $tbl;
			}
		}
	}

	/*
	============================================================
	function Traces($Liste)
	Print all Traces de cette CALSS
	si aucune parametre recu, le denireer Traces sera affiche 
	si non all Traces seront affichées
	=================================================================	
	*/
	function Traces($pos="LAST"){
		$tbl=$this->Trace;
		if($pos=="LAST"){
			echo $this->Trace[count($this->Trace)-1];
		}else{
			if (count($tbl) >0){		
				foreach ($tbl as $key => $value) {
			    echo "<br> ".$value;
				}
			}
		}
	}

}//FIN CLASS
?>
