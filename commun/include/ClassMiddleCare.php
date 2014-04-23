<?php
class AppCR
{
	var $Site	;								// Code du Hopital ex 076 pour sls, 047 pour lrb ....	
 	var $ConnStringMiddleCare; 		// connexion String MiddleCare
 	var $ConnMiddleCare;					 	// connexion MiddleCare 	
 	var $Err;									// Erreurs de ce traitements sont dans un array  
 	var $warn;								// Warning de ce traitements sont dans un array  
 	var $Trace;								// Trace de ce traitements sont dans un array  

 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function AppCR($ConnStringMiddleCare,$User,$Mdp)
	{
		if (strlen($ConnStringMiddleCare) < 1)
		{
			echo "MiddleCare:: Connexion String est Vide!<br> Impossible de se connecter";
			exit;
		}
		if( (strlen($User) < 1)  or (strlen($Mdp) < 1)) 
		{
			echo "MiddleCare:: Login ou Mot de passe Vide!<br> Impossible de se connecter";
			exit;
		}
		
		$this->SetConnString($ConnStringMiddleCare);
 		$this->ConnectOracle($User,$Mdp);

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
		if (!$this->ConnMiddleCare)
		{
			// vérify le connexion string à ete initilisé
			if(strlen($this->ConnString)> 0){
				$this->ConnMiddleCare = oci_connect($User,$Mdp,$this->ConnString);
			}
			// recheck le connexion est initialisé
			if (!$this->ConnMiddleCare)
      {
       	echo  "<br>MiddleCare::Erreur connection Oracle";
       	echo "<br>".oci_error();
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
   		echo  "<br>MiddleCare::Erreur connection String Oracle est VIDE!!!";		
   		return false;
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
		if ($this->ConnMiddleCare)
		{
			oci_close($this->ConnMiddleCare);
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

	
		// vérify  connexion MiddleCare
		if (!isset($this->ConnMiddleCare))
		{
			echo  "<br>MiddleCare::Erreur connexion Oracle !!!";
			echo 	$this->Err[count($this->Err)-1];
			return false;
		}
		// executte qry
		$result = oci_parse($this->ConnMiddleCare, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br>MiddleCare::SQL Fetch Erreur :".$oerr["message"];
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
			echo  "<br>MiddleCare::Erreur Executing qry :".$sql;
		}
	
	}		
	

	/*
	=================================================================
	function CheckCR($NIP,$NDA,$UH,$TypeCr="")
	modifiée lz 03/09/2013 
		retourne DATEPUB|CR_PRVISOIRE
	=================================================================	
	*/
	function CheckCR($NIP,$NDA,$UH="",$TypeCr="")
	{
		$site="0".substr($NDA,0,2);
		//----------------------------------------------------------------------------------------------------
		// check Type compte rendu demandée
		//----------------------------------------------------------------------------------------------------
		switch($TypeCr)
		{
			case "CRH":
				$req_cr=" AND CATEG='120' ";
				  break;
			case "CRO":
				$req_cr=" AND CATEG='402' ";
				  break;
			case "CS": // consult
				$req_cr=" AND CATEG='201' ";
				  break;
			default :
				$req_cr=" Inconnu"; //typeCR est obligatoire si type inconnu on envoi rien
				//return "Type CR inconnu";
				  break;
		}

		//----------------------------------------------------------------------------------------------------
		// check UH demandée
		//----------------------------------------------------------------------------------------------------
		if (strlen($UH) < 1)
		{
			$req_uh="";
		}else{
			$req_uh=" AND dos.cd_uf like '%$UH%' ";		
		}
		//----------------------------------------------------------------------------------------------------
		// req spl pour SLS
		// demandé par Christophe d'ajouter les uh de URGENCE pour que gerer les hebergement de urgence,
		// car l'urgence fait son compte rendu dans leur uh (376 et 374)
		//----------------------------------------------------------------------------------------------------
		if ($site=="076")
		{
			$req_uh=" AND 
								 (
									dos.cd_uf like '%$UH%'  	
			 						OR dos.cd_uf like '%376%' 
			  					OR dos.cd_uf like '%374%'
			  					) ";		
		}		
		
			
		$sql="SELECT to_char(DATEPUB,'DD/MM/YYYY') as DATEPUB, AUTEUR ,CR_PROVISOIRE
					FROM MIDDLECARE.consultation cons,
  					 	MIDDLECARE.dossier dos
					WHERE  cons.num_venu = '$NDA'
					$req_cr
					$req_uh
					AND cons.cdprod= dos.cd_dossier
			 		";
		$res=$this->OraSelect($sql); 		
		return $res[0]['DATEPUB']."|".$res[0]['CR_PROVISOIRE'];
	}
	/*
	=================================================================
	function CheckCR($NIP,$NDA,$UH,$TypeCr="")
	=================================================================	
	*/
	function GetAuther($NIP,$NDA,$UH="",$TypeCr="")
	{

		// check Type compte rendu demandée
		switch($TypeCr)
		{
			case "CRH":
				$req_cr=" AND CATEG='120' ";
				  break;
			case "CRO":
				$req_cr=" AND CATEG='402' ";
				  break;
			case "CS": // consult
				$req_cr=" AND CATEG='201' ";
				  break;
			default :
				$req_cr=" Inconnu"; //typeCR est obligatoire si type inconnu on envoi rien
				return "Type Cr inconnu";
				  break;
		}
		// check UH demandée
		if (strlen($UH) < 1)
		{
			$req_uh="";
		}else{
			$req_uh=" AND dos.cd_uf like '%$UH%' ";		
		}
		//----------------------------------------------------------------------------------------------------
		// req spl pour SLS
		// demandé par Christophe d'ajouter les uh de URGENCE pour que gerer les hebergement de urgence,
		// car l'urgence fait son compte rendu dans leur uh (376 et 374)
		//----------------------------------------------------------------------------------------------------
		if ($site=="076")
		{
			$req_uh=" AND 
								 (
									dos.cd_uf like '%$UH%'  
			 						OR dos.cd_uf like '%376%' 
			  					OR dos.cd_uf like '%374%'
			  					) ";		
		}		
				
		$sql="SELECT u_nom,u_pnom,titre_nom,to_char(DATEPUB,'DD/MM/YYYY') as DATEPUB,to_char(DATEXAM,'DD/MM/YYYY') as DATEXAM, AUTEUR 
					FROM MIDDLECARE.consultation cons, 
					MIDDLECARE.dossier dos, 
					MIDDLECARE.info_user users 
				WHERE users.code_user(+)=cons.AUTEUR				
				AND cons.num_venu = '$NDA'
				$req_cr
				$req_uh
				AND cons.cdprod= dos.cd_dossier
			 ";
		$res=$this->OraSelect($sql); 		
		return $res;		
	}
			
				
				
}
//FIN CLASS

?>
