<?php
class Simpa
{
	var $Site	;								// Code du Hopital ex 076 pour sls, 047 pour lrb ....	
 	var $ConnStringSimpa; 		// connexion String Simpa
 	var $ConnSimpa;					 	// connexion Simpa 	
 	var $ConnMysql;					 	// connexion Mysql 	 	
 	var $Err;									// Erreurs de ce traitements sont dans un array  
 	var $warn;								// Warning de ce traitements sont dans un array  
 	var $Trace;								// Trace de ce traitements sont dans un array  
	var $DateDeb;
  var $DateFin; 	

 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Simpa($ConnStringSimpa)
	{
		$this->SetConnString($ConnStringSimpa);
 		$this->ConnectOracle("consult","consult");

	}

 	/*
 	==============================================================================
 	funtion SetPeroide (date_deb et date_fin )
 	initalise les peroides dans Simpa;
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
 	funtion ConnectOracle($ConnString,$User,$Mdp,$VariableConnexion)
 	return le connextion 
 	==============================================================================
 	*/
	function ConnectOracle($User,$Mdp)
	{
		// check le connexion est initialisé si oui envoi le conn exixte
		if (!$this->ConnSimpa)
		{
			// vérify le connexion string à ete initilisé
			if(strlen($this->ConnString)> 0){
				$this->ConnSimpa = oci_connect($User,$Mdp,$this->ConnString);
			}
			// recheck le connexion est initialisé
			if (!$this->ConnSimpa)
      {
       	echo  "<br>Simpa::Connexion => Impossible de se connecter a la base SIMPA. Veuillez réessayer ultérieurement.";
       	echo "<br><pre>";
       	print_r(oci_error());
       	exit;
      }
      return true; // connexion ouvert now
		}
		return true; //connexion existe deja
	}
 	/*
 	==============================================================================
 	funtion SetConnString($ConnString)
 	return true ou false
 	==============================================================================
 	*/
	function SetConnString($ConnString)
	{
		// check le connexion est initialisé si oui envoi le conn exixte
		if (strlen($ConnString) > 1 )
		{
			$this->ConnString=$ConnString;
			return true;
		}else{
   		echo  "<br>Simpa::Erreur connection String Oracle est VIDE!!!";		
   		exit;
		}	
	}

 	/*
 	==============================================================================
 	 funtion CloseOracle($VariableConnexion)
 	==============================================================================
 	*/
	function Close()
	{
		// check le connexion est initialisé
		if ($this->ConnSimpa)
		{
			oci_close($this->ConnSimpa);
		}
	}	
	/*
	==============================================================================
	Function OraSelect(ConnextionOracleDejaOvert , Requeete) 
	retourn les resulata sous form de tableau
	==============================================================================
	*/
	function OraSelect($sql){
		if ($sql=="")
		{
			return false;
		}

	
		// vérify  connexion Simpa
		if (!isset($this->ConnSimpa))
		{
			echo  "<br>Simpa::Erreur connexion Oracle !!!";
			echo 	$this->Err[count($this->Err)-1];
			return false;
		}
		// executte qry
		$result = oci_parse($this->ConnSimpa, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br>Simpa::SQL Fetch Erreur :".$oerr["message"];
   		return false;
		}
		
		if (oci_execute($result)){
			$row=0;	
			$data=array();
			while(oci_fetch($result))
			{
				$ncols = oci_num_fields($result);
				$c_line=array();
				for ($i = 1; $i <= $ncols; $i++) 
				{
					$column_name  = oci_field_name ($result, $i);
		      $column_value = oci_result($result, $i);
	       	$c_line[$column_name]=$column_value;
	   		}
				$data[$row]=$c_line;
				$row++;
			}
		

			return $data;	

		}
		else
		{
			echo  "<br>Simpa::Erreur Executing qry :".$sql;
		}
	
	}		
	

	/*
	=================================================================
	function PatientsParUH($uh,$date_deb,$date_fin)
	uh a separer par vircule
	modifié le 30/04/2013, gestion nouveu née(AND SDO.TYDOS !='N')
	=================================================================	
	*/
	function PatientsParUH($uh)
	{
	$sql="  SELECT SDO.kyNOIP as NIP, 
					EPI.KYNODA as NDA,
				  NMMAL as NOM,
				  NMPMAL as PRENOM,
				  TO_CHAR(D8EEUE,'DD/MM/YYYY') as DATE_ENTREE,
				  TO_CHAR(D8SOUE,'DD/MM/YYYY') as DATE_SORTIE,
				  TO_CHAR(DANAIS,'DD/MM/YYYY') as DANAIS,  
				  UE as UH,
				  tyepi as TYPE_EPI,
				  NOHJO as  NOHJO 
				 from epi,SDO  
				 WHERE EPI.KYNODA=SDO.KYNODA
				 AND SDO.TYDOS != 'N'
				 AND D8SOUE BETWEEN TO_DATE('".$this->DateDeb."','DD/MM/YYYY') AND TO_DATE('".$this->DateFin."','DD/MM/YYYY')+1 
				 AND UE in (".$uh.") 	 ORDER BY NMMAL,NMPMAL,D8SOUE
				 ";
 
				return $this->OraSelect($sql); 			
		/* old
		$sql="  SELECT NOIP as NIP, NODA as NDA,
					  NMMAL as NOM,
					  NMPMAL as PRENOM,
					  TO_CHAR(D8EEUE,'DD/MM/YYYY') as DATE_ENTREE,
					  TO_CHAR(D8SOUE,'DD/MM/YYYY') as DATE_SORTIE,
					  TO_CHAR(DANAIS,'DD/MM/YYYY') as DANAIS,  
					  UE as UH
					 from rsm,SDO  
					 WHERE RSM.NODA=SDO.KYNODA
					 AND D8SOUE BETWEEN TO_DATE('".$this->DateDeb."','DD/MM/YYYY') AND TO_DATE('".$this->DateFin."','DD/MM/YYYY')+1 
					 AND UE in (".$uh.") 	 ORDER BY NMMAL,NMPMAL,D8SOUE
					 ";
					 
					return $this->OraSelect($sql); 			
		*/
	}
	/*
	=================================================================
	function GetPatients($date_deb="",$date_fin="")
	=================================================================	
	*/
	function GetPatients($date_deb="",$date_fin="")
	{
		
	$sql="  SELECT SDO.kyNOIP as NIP, 
					EPI.KYNODA as NDA,
				  NMMAL as NOM,
				  NMPMAL as PRENOM,
				  TO_CHAR(D8EEUE,'DD/MM/YYYY') as DATE_ENTREE,
				  TO_CHAR(D8SOUE,'DD/MM/YYYY') as DATE_SORTIE,
				  TO_CHAR(DANAIS,'DD/MM/YYYY') as DANAIS,  
				  UE as UH,
				  tyepi as TYPE_EPI,
				  NOHJO as  NOHJO,
				  NOMVTDEB as NOIDMV 
				 from epi,SDO  
				 WHERE EPI.KYNODA=SDO.KYNODA
				 AND SDO.TYDOS != 'N'
				 AND D8SOUE BETWEEN TO_DATE('".$this->DateDeb."','DD/MM/YYYY') AND TO_DATE('".$this->DateFin."','DD/MM/YYYY')+1 
 				 ORDER BY NMMAL,NMPMAL,D8SOUE
				 ";
		//echo "<br>" .$sql; 
				return $this->OraSelect($sql); 			
		/* old
		$sql="  SELECT NOIP as NIP, NODA as NDA,
					  NMMAL as NOM,
					  NMPMAL as PRENOM,
					  TO_CHAR(D8EEUE,'DD/MM/YYYY') as DATE_ENTREE,
					  TO_CHAR(D8SOUE,'DD/MM/YYYY') as DATE_SORTIE,
					  TO_CHAR(DANAIS,'DD/MM/YYYY') as DANAIS,  
					  UE as UH
					 from rsm,SDO  
					 WHERE RSM.NODA=SDO.KYNODA
					 AND D8SOUE BETWEEN TO_DATE('".$this->DateDeb."','DD/MM/YYYY') AND TO_DATE('".$this->DateFin."','DD/MM/YYYY')+1 
					 AND UE in (".$uh.") 	 ORDER BY NMMAL,NMPMAL,D8SOUE
					 ";
					 
					return $this->OraSelect($sql); 			
		*/
	}
	
	/*
	=================================================================
	function IsSejourCoded($nda,$uh,$date_sortie,$type_sej,$nohjo){
	permet de savoir un séjour est codé dans un service
	Ajouté le 17/04/13 
	au lieu de chercher un serjour groupé un cherche un résumé codé 

	=================================================================	
	*/
	function IsSejourCoded($nda,$uh,$date_sortie,$type_sej="S",$nohjo="")
	{
		// en cas d'hospit complet
		if ($type_sej=="HC")
		{
			$sql="SELECT *	FROM  EPI 
						WHERE EPI.KYNODA='$nda'
						AND EPI.UE='$uh'
						and epi.nores is not null";
		}
		else
		{
			$sql="SELECT *	FROM  EPI 
						WHERE EPI.KYNODA='$nda'
						AND EPI.UE='$uh'
						AND epi.nores is not null
						AND ( (TO_CHAR(EPI.D8SOUE,'DD/MM/YYYY') = '$date_sortie') OR (EPI.NOHJO='$nohjo') ) ";
		}
 
		$res=$this->OraSelect($sql);
		if (count($res) < 1)
			return '0'; //aucun ligne trouvé donc pas de codage
		else	
			return '1'; // codage  ok pour ce résumé
		
	}
		
 
	/*
	=================================================================
	function IsSejourGrpouped($nda,$date_sortie){
	permet de savoir un séjour est groupé ou non
	control sur CMD80 ou l'évitement 80 
	=================================================================	
	*/

	function IsSejourGrpouped($nda,$date_sortie,$type_sej="S"){
		if ($type_sej=="HC")
		{
			$sql="SELECT CDGHM 
							FROM RSS 
							WHERE NODA='$nda'" ;
		}
		else
		{
			$sql="SELECT CDGHM 
							FROM RSS 
							WHERE NODA='$nda' 
							AND TO_CHAR(rss.D8FIN,'DD/MM/YYYY') = '$date_sortie' ";
		}
 
		$res=$this->OraSelect($sql);
		if (count($res) < 1)
			return '0'; //aucun ligne trouvé donc pas groupé
		if (substr($res[0]['CDGHM'],0,2)=="90"){
			return '0';	 //CMD90  donc pas groupé
		}
		return '1';   // si non ok
		
	}
	
	/*
	=================================================================
	function GetSejourInfo($nda_list,$date_sortie=""){
	permet de recuparer les sejour info d'un ou plusieurs NDA
	si le date_sotie est non vide on ajoute dans le filtre
	=================================================================	
	*/
	function GetSejourInfo($nda_list,$date_sortie="")
	{
		if (strlen($date_sortie)==10)
			$sql_date=" AND TO_CHAR(rss.d8fin,'DD/MM/YYYY')='$date_sortie' "	;
		else
			$sql_date="   ";	
		//on compte pas le date de sortie	
		$sql_date="   ";
			
		$sql="select NIP, NDA,nores,DER, DSR, DP, DR, GHM, LBDP, duree_rum,LBGHM,nom,prenom,danais,nbhjo,nbsean,urm,igs,sexe,UH,MER,MSR,dusej,DES,DSS,GHS,CDERG,D8TRI  FROM
				(
				select distinct(rsm.noda) as nda,
							rsm.noip as NIP,
							rsm.kyres as kyres,
							SDO.nmmal as nom,
							SDO.nmpmal as prenom, 
							TO_char(danais,'DD/MM/YYYY') as danais, 
							TO_char(rum.d8eeue,'DD/MM/YYYY') as DER,
							TO_char(rum.d8soue,'DD/MM/YYYY') as DSR, 
							rsm.CDDNC9 as DP, 
							rsm.CDRELIE as DR, 
							'' as DAS, 
							rss.CDGHM as GHM, 
							LBCIM as LBDP,
							nbjrum as duree_rum, 
							ghm.lbghm as LBGHM , 
							rsm.NBHJO as nbhjo , 
							rsm.NBSEAN as nbsean ,
							rsm.cdurm as urm, 
							rsm.igs as igs ,
							sxpmsi as sexe ,
							rsm.ue as UH,
							rsm.mdeeue as MER,
							rsm.mdsoue as MSR,
							dusej, 
							TO_CHAR(rss.d8deb,'DD/MM/YYYY') as DES,
							TO_CHAR(rss.d8fin,'DD/MM/YYYY') as DSS,
							rum.nores	 as nores,							
							rss.NUMGHS as GHS,
							rss.CDERG as 	CDERG,
							rum.d8eeue	as D8TRI						
				from RUM,RSS,RSM, CIM,GHM,SDO
					WHERE RUM.NORES = RSM.KYRES
					AND cim.tycim='10'
					AND RUM.KYRSS = RSS.KYRSS
					AND CIM.KYCIM = rsm.CDDNC9
					AND GHM.KYGHM= rss.CDGHM
					AND SDO.KYNODA = rsm.noda 
					AND rsm.NODA in (".$nda_list." )
					$sql_date
				)
 		
			order by NIP, NDA,D8TRI";
		$res=$this->OraSelect($sql);
		return $res;
	}
	/*
	=================================================================
	function GetSejourInfo($nda_list,$date_sortie=""){
	permet de recuparer les sejour info d'un ou plusieurs NDA
	si le date_sotie est non vide on ajoute dans le filtre
	=================================================================	
	*/
	function GetSejourInfo_test($nda_list,$date_sortie="")
	{
		if (strlen($date_sortie)==10)
			$sql_date=" AND TO_CHAR(rss.d8fin,'DD/MM/YYYY')='$date_sortie' "	;
		else
			$sql_date="   ";	
		//on compte pas le date de sortie	
		$sql_date="   ";
			
		$sql="SELECT SDO.kynoda as nda, 
						SDO.kynoip	as NIP, 
					 	SDO.nmmal as nom, 
						SDO.nmpmal as prenom, 
						TO_char(danais,'DD/MM/YYYY') as danais, 
						sxpmsi as sexe , 
						TO_CHAR(SDO.DAENTR,'DD/MM/YYYY') as DES, 
						TO_CHAR(SDO.DASOR,'DD/MM/YYYY') as DSS
				FROM SDO
				WHERE  SDO.KYNODA in (".$nda_list." )";


		$res=$this->OraSelect($sql);
		return $res;
	}		
	/*
	=================================================================
	function GetLibelleCim($code,$version=10){
	permet de recuparer les sejour info d'un ou plusieurs NDA
	si le date_sotie est non vide on ajoute dans le filtre
	=================================================================	
	*/
	function GetLibelleCim($code,$version=10)
	{
		$sql="select LBCIM from cim   where CIM.KYCIM ='".$code."' AND cim.tycim='$version'";	
		$res=$this->OraSelect($sql);
		if (count($res) >0)
			return $res[0]['LBCIM'];		
		else
			return "Libelle Cim Inconnu !!!";		
	}
	
	
	/*
	=================================================================
	function GetDasEtLibelle($NoRes,Libelle=False){
	permet de recuparer les das d'un resumé 
	=================================================================	
	*/	
	function GetDasEtLibelle($NoRes,$version=10){
		$sql="SELECT dgn.kycddg as code, cim.lbcim as libelle 
					FROM dgn,cim
					WHERE dgn.kycddg=cim.kycim 
					and kytydg='C' 
					and dgn.kyres='$NoRes'
					AND cim.tycim='".$version."'";
		$res=$this->OraSelect($sql);
		return $res;			
	}
	/*
	=================================================================
	function GetActes($NoRes){
	permet de recuparer les actes d'une résumé
	=================================================================	
	*/	
	function GetActes($NoRes,$version=10){
		$sql="SELECT distinct rac.kycdac as acte ,cda.lbcda as libelle 
					FROM rac,cda
					where rac.kycdac=cda.kycda 
					and verclas='".$version."'  
					and rac.kyres='".$NoRes."'";
								
		$res=$this->OraSelect($sql);
		return $res;			
	}

 	/*
	=================================================================
	function GetLibelleGHM($CodeGHM){
	permet de recuparer le libelle d'GHM 
	=================================================================	
	*/	
	function GetLibelleGHM($CodeGHM,$version=10){
		$sql="SELECT ghm.lbghm as Libelle from GHM WHERE ghm.kyghm='".$CodeGHM."' ";
		$res=$this->OraSelect($sql);
		return $res[0]['LIBELLE'];			
	}

 	/*
	=================================================================
	function GetLibelleURM($CodeURM){
	permet de recuparer le libelle d'URM 
	=================================================================	
	*/	
	function GetLibelleURM($CodeURM,$version=10){
		$sql="SELECT lburm as libelle from urmsim where urmsim.cdurm='".$CodeURM."' ";
		$res=$this->OraSelect($sql);
		return $res[0]['LIBELLE'];			
	}
 	/*
	=================================================================
	function GetLibelleUH($CodeUH){
	permet de recuparer le libelle d'UH 
	=================================================================	
	*/	
	function GetLibelleUH($CodeUH,$version=10){
		$sql="SELECT ufmsim.lbuf as libelle from ufmsim where  ufmsim.nouf='".$CodeUH."' ";
		$res=$this->OraSelect($sql);
		return $res[0]['LIBELLE'];			
	}

 	/*
	=================================================================
	function GetLibelleUH($CodeUH){
	permet de recuparer le libelle d'UH 
	=================================================================	
	*/	
	function GetDiagnosticsParNIP($NIP,$version=10){
		
		$sql="SELECT distinct(code),Libelle  from(
					SELECT distinct(CDDNC9) as code ,LBCIM as libelle,'DP' as DIAG
						from rsm,SDO, cim 
						where SDO.kynoda= rsm.noda 
						AND cim.tycim='$version'
						AND cim.KYCIM = rsm.CDDNC9
						and SDO.kynoip='$NIP'
					union
					SELECT distinct(CDRELIE) as code  ,LBCIM as libelle,'DR' as DIAG
						from rsm,SDO, cim 
						where SDO.kynoda= rsm.noda 
						AND cim.tycim='$version'
						AND cim.KYCIM = rsm.CDRELIE
						and SDO.kynoip='$NIP'
					union
					select distinct(dgn.kycddg) as code, cim.lbcim as libelle, 'DAS' as DIAG from dgn,cim
						where dgn.kycddg=cim.kycim and kytydg='C' 
						AND cim.tycim='$version'
						AND dgn.kyres  in (
						 	SELECT rsm.kyres
							from rsm,SDO where SDO.kynoda= rsm.noda and SDO.kynoip='$NIP')	
				)
				  "; 
 			
		$res=$this->OraSelect($sql);
		return $res;
	}
				
 	/*
	=================================================================
	function GetManquantSimpa($ListUrm,$date_deb,$date_fin){
	permet de recuparer les manquantes SIMPA
	=================================================================	
	*/	
	function GetManquantSimpa($ListUrm,$date_deb="",$date_fin=""){
		if (strlen($ListUrm) < 1 )
			$qry_urm	= " ";
		else
			$qry_urm=" AND RTRIM(EPI.CDURM) in ('".str_replace(",","','",$ListUrm)."') ";
	
		$sql =  "SELECT DISTINCT CDURM AS URM, SDO.KYNOIP AS NIP, SDO.KYNODA AS NDA, SDO.NMMAL AS NOM, 
	    				SDO.NMPMAL AS PRENOM, EPI.UE AS UH,  
	    				SUBSTR(TO_CHAR(D8EEUE , 'DD/MM/YYYY'),1,10) AS DATE_DEB,  
	    				SUBSTR(TO_CHAR(D8SOUE , 'DD/MM/YYYY'),1,10) AS DATE_FIN  
	    				FROM SDO, EPI  
	    				WHERE NORES IS NULL  
	    				AND EPI.KYNODA = SDO.KYNODA  
	    				AND D8SOUE  >= to_date('$date_deb','DD/MM/YYYY')  
	    				AND ((TYEPI = 'HC' AND DASOR   < to_date('$date_fin','DD/MM/YYYY') + 1)    
	    				 OR (TYEPI <> 'HC' AND D8SOUE < to_date('$date_fin','DD/MM/YYYY') + 1))   
	    				 $qry_urm
	    				ORDER BY EPI.CDURM,SDO.NMMAL, SDO.NMPMAL, DATE_DEB  "	;		

		$res=$this->OraSelect($sql);
		return $res;
	}
						
				

 	/*
	=================================================================
	function GetManquantSimpa($ListUrm,$date_deb,$date_fin) 
	permet de recuparer les manquantes SIMPA
	=================================================================	
	*/	
	function GetPatInfoByNip($Nip){
		if (strlen($Nip) < 1 )
			return array();
		else
		$sql =  "SELECT * from pat 	WHERE NOIP='".$Nip."'";
		$res=$this->OraSelect($sql);
		return $res;
	}
						
				


}//FIN CLASS

?>
