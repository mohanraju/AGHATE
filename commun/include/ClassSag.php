<?php
class Sag
{
	var $Site	;							// Code du Hopital ex 076 pour sls, 047 pour lrb ....	
 	var $ConnStringSag; 		// connexion String Sag
 	var $ConnString; 		// connexion String Sag
 	var $ConnexionSAG;			// Active connexion Sag ouvert
 	var $ConnMysql;					// connexion Mysql 	 	
 	var $Err;								// Erreurs de ce traitements sont dans un array  
 	var $warn;							// Warning de ce traitements sont dans un array  
 	var $Trace;							// Trace de ce traitements sont dans un array  

 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Sag($ConnStringSag="")
	{
		if (strlen($ConnStringSag) < 1)
		{
			$this->Err="SAG::Connexion String SAG Vide !!!";
			echo $this->Err;
			Exit;
		}
		$this->SetConnString($ConnStringSag);
 		$this->ConnectOracle("arccam_v1","arccam_v1");

	}

 	/*
 	==============================================================================
 	funtion SetPeroide (date_deb et date_fin )
 	initalise les peroides dans Sag;
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
		if (!$this->ConnexionSAG)
		{
			// vérify le connexion string à ete initilisé
			if(strlen($this->ConnString)> 0){
				$this->ConnexionSAG = oci_connect	($User,$Mdp,$this->ConnString);
			}
			// recheck le connexion est initialisé
			if (!$this->ConnexionSAG)
      		{
						echo  "<br>SAG::Connexion =>Impossible de se connecter a la base SAG. Veuillez réessayer ultérieurement.";
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
   		echo  "<br>Erreur connection String Oracle est VIDE!!!";		
   		return false;
		}	
	}

 	/*
 	==============================================================================
 	 funtion CloseOracle($VariableConnexion)
 	==============================================================================
 	*/
	function CloseOracle( )
	{
		// check le connexion est initialisé
		if ($ConnexionSAG)
		{
			ora_close($ConnexionSAG);
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
		
		// vérify  connexion Sag
		if (!isset($this->ConnexionSAG))
		{
			echo  "<br>Erreur connextion Oracle !!!";
			echo 	$this->Err[count($this->Err)-1];
			return false;
		}
		// executte qry
		$result = oci_parse($this->ConnexionSAG, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_Error($result);
   		echo  "<br>SQL Fetch Erreur :".$oerr["message"];
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
			echo  "<br>Erreur Executing qry :".$sql;
		}
	
	}		
	

	/*
	=================================================================
	function GetActes($nip,$DateIntervention,)
	select les actes coded pour ce patient dans +ou- de  150 jours de date intervension
	pour tous les séjours, celle-ci permet d'identifier les erreurs de date d'actes
	
	=================================================================	
	*/
	function GetActes($Nip,$DateIntervention,$UhExecutant)
	{
		$sql_Actes="select CODE_ACTE_CCAM AS ACTE,NDA,to_char(DATE_REALISATION,'DD/MM/YYYY HH24:MM:SS')AS DATE_EXEC,DATE_REALISATION,
					LIBELLE_ACTE_CCAM AS LIB_ACT,S_APHP_CODE_UH_LOC AS UH,S_APHP_LIBELLE_UH_LOC AS LIBUH
					FROM ACTE_CCAM ,
					     PHASE_ACTIVITE ,
					     STRUCTURE_FONCTIONNELLE uh_exc,
					     patient
					WHERE  
						     ACTE_CCAM.identifiant=PHASE_ACTIVITE.acte_ccam_identifiant
						    and acte_ccam.PAT_IDENTIFIANT=patient.IDENTIFIANT
						    and patient.nip='".trim($Nip)."'
						    and PHASE_ACTIVITE.str_id_execution=uh_exc.id  	
						    and uh_exc.code in ($UhExecutant) 
					ORDER BY DATE_REALISATION desc 
					";	

		return $this->OraSelect($sql_Actes); 			

	}
	/*
	=================================================================
	function GetActesCoded($Nip,$DateIntervention,$ListeUhExecutant)
	select les actes coded pour ce patient
	pour tous les séjours, celle-ci permet d'identifier les erreurs de date d'actes
	=================================================================	
	*/	
	function GetActesCoded($Nip,$DateIntervention,$ListeUhExecutant)
	{
			
		$sql="SELECT NDA,ACTE_CCAM.CODE_ACTE_CCAM as ACTE,  LIBELLE_ACTE_CCAM as LIB
					FROM ACTE_CCAM ,
					     PHASE_ACTIVITE ,
					     STRUCTURE_FONCTIONNELLE uh_exc,
					     patient
					WHERE ACTE_CCAM.date_realisation >= to_date('$DateIntervention','DD/MM/YYYY')
						    and ACTE_CCAM.date_realisation < to_date('$DateIntervention','DD/MM/YYYY') + 1
						    and ACTE_CCAM.identifiant=PHASE_ACTIVITE.acte_ccam_identifiant
						    and acte_ccam.PAT_IDENTIFIANT=patient.IDENTIFIANT
						    and patient.nip='".trim($Nip)."'
						    and PHASE_ACTIVITE.str_id_execution=uh_exc.id  	
						    and uh_exc.code in ($ListeUhExecutant)";
		$res=$this->OraSelect($sql);	
		for($i=0;$i < count($res) ;$i++){
			$retval.=$res[$i]['ACTE']. " ".$res[$i]['LIB']."<br>";
		}
		//echo $retval;
		return $retval."&nbsp;";
	}

 
	/*
	=================================================================
	function ActeCoded($nip,$date_intervention)
		return true or false 
	=================================================================	
	*/
	function ActeCoded($nip,$date_intervention)
	{
		$sql_Actes="select CODE_ACTE_CCAM AS ACTE,NDA,to_char(DATE_REALISATION,'DD/MM/YYYY HH24:MM:SS')AS DATE_EXEC,DATE_REALISATION,
					LIBELLE_ACTE_CCAM AS LIB_ACT,S_APHP_CODE_UH_LOC AS UH,S_APHP_LIBELLE_UH_LOC AS LIBUH
					FROM  ACTE_CCAM ,patient
					where patient.nip='$nip'
					and  acte_ccam.PAT_IDENTIFIANT=patient.IDENTIFIANT
					ORDER BY DATE_REALISATION desc ";	
 				
		return $this->OraSelect($sql_Actes); 			
		
	}
	/*
	=================================================================
	function GetActesParNda($nda)
	return les actes 
	=================================================================	
	*/	
	function GetActesParNda($nda){
		$sql_Actes="select CODE_ACTE_CCAM AS ACTE,NDA,to_char(DATE_REALISATION,'DD/MM/YYYY HH24:MM:SS')AS DATE_EXEC,DATE_REALISATION,
					LIBELLE_ACTE_CCAM AS LIB_ACT,S_APHP_CODE_UH_LOC AS UH,S_APHP_LIBELLE_UH_LOC AS LIBUH
					FROM ACTE_CCAM ,
					     PHASE_ACTIVITE ,
					     STRUCTURE_FONCTIONNELLE uh_exc,
					     patient
					WHERE  
						     ACTE_CCAM.identifiant=PHASE_ACTIVITE.acte_ccam_identifiant
						    and acte_ccam.PAT_IDENTIFIANT=patient.IDENTIFIANT
						    and PHASE_ACTIVITE.str_id_execution=uh_exc.id  	
						    and NDA='".$nda."'
					ORDER BY DATE_REALISATION desc 
					";	

		return $this->OraSelect($sql_Actes); 			
		
	} 

}
//FIN CLASS

?>
