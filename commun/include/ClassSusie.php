<?php
class Susie
{
	var $Site	;								// Code du Hopital ex 076 pour sls, 047 pour lrb ....	
 	var $ConnStringSusie; 		// connexion String Susie
 	var $ConnSusie;					 	// connexion Susie 	
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
	function Susie($ConnStringSusie,$User="",$Mdp="")
	{
		$this->SetConnString($ConnStringSusie);
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
		if (!$this->ConnSusie)
		{
			// vérify le connexion string à ete initilisé
			if(strlen($this->ConnString)> 0){
				$this->ConnSusie = oci_connect($User,$Mdp,$this->ConnString);
			}
			// recheck le connexion est initialisé
			if (!$this->ConnSusie)
      {
       	echo  "<br>Susie::Erreur connection Oracle";
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
   		echo  "<br>Susie::Erreur déclaraion connection string Oracle est VIDE!!!";		
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
		if ($this->ConnSusie)
		{
			oci_close($this->ConnSusie);
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

	
		// vérify  connexion Susie
		if (!isset($this->ConnSusie))
		{
			echo  "<br>Susie::Erreur connexion Oracle !!!";
			echo 	$this->Err[count($this->Err)-1];
			return false;
		}
		// executte qry
		$result = oci_parse($this->ConnSusie, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br>Susie::SQL Fetch Erreur :".$oerr["message"];
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
			echo  "<br>Susie::Erreur Executing qry :".$sql;
		}
	
	}		
	
 
	

}
//FIN CLASS

?>
