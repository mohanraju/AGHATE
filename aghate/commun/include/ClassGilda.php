<?php
class Gilda
{
	var $Site	;				// Code du Hopital ex 076 pour sls, 047 pour lrb ....	
 	var $ConnStringGilda; 		// connexion String Gilda
 	var $ConnGilda;				// connexion Gilda 	
 	var $ConnMysql;				// connexion Mysql 	 	
 	var $Err;					// Erreurs de ce traitements sont dans un array  
 	var $warn;					// Warning de ce traitements sont dans un array  
 	var $Trace;					// Trace de ce traitements sont dans un array  
	var $DateDeb;
	var $DateFin; 	
	var $NN ;                 // Nouveau Né true/false       
 	/*
 	==============================================================================
 	Constructeur 
 	function Gilda($site)
 	==============================================================================
 	*/
	function Gilda($ConnStringGilda)
	{
		$this->SetConnString($ConnStringGilda);
 		$this->ConnectOracle("consult","consult");
 		$this->NN=false;
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
		if (!$this->ConnGilda)
		{
			// vérify le connexion string à ete initilisé
			if(strlen($this->ConnString)> 0){
				$this->ConnGilda = oci_connect($User,$Mdp,$this->ConnString);
			}
			// recheck le connexion est initialisé
			if (!$this->ConnGilda)
      {
				echo  "<br />GILDA::Connexion =>Impossible de se connecter a la base gilda. Veuillez réessayer ultérieurement.";
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
   		echo  "<br />Gilda::Erreur connection String Oracle est VIDE!!!";		
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
		if ($this->ConnGilda)
		{
			oci_close($this->ConnGilda);
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

	
		// vérify  connexion Gilda
		if (!isset($this->ConnGilda))
		{
			echo  "<br />Gilda::Erreur connexion Oracle !!!";
			echo 	$this->Err[count($this->Err)-1];
			return false;
		}
		// executte qry
		$result = oci_parse($this->ConnGilda, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br />Gilda::SQL Fetch Erreur :".$oerr["message"];
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
			echo  "<br />Gilda::Erreur Executing qry :".$sql;
		}
	
	}		
	
		
	
	/*========================================================================
	// function PatInfoByNip($NIP)
	// recupere patients info by NIP
	// NOTE : date de naissance au format  "DD/MM/YYYY sur la variable DTNAIS
	========================================================================*/
	function GetPatInfoByNip($NIP)
	{
		$sql = "SELECT pat.*,to_char(DANAIS,'DD/MM/YYYY') as DTNAIS from pat where NOIP='$NIP'";
		$res = $this->OraSelect($sql);
		return $res;
	}
	

	
	
	/*========================================================================
	// function GetLitPost()
	// recupere NOLIT et NOPOST de lli
	========================================================================*/
	function GetLitPost ()
	{
		$sql = "SELECT NOLIT,NOPOST,ser.LBSERV, to_char(ser.DFVALI,'YYYY-MM-DD') as DFVALI
				FROM lli,ser
				WHERE lli.NOSERV=ser.NOSERV
				order by LBSERV";
		$res = $this->OraSelect($sql);
		return $res;
	}
	
 
	/*========================================================================
	// function GetLocTab()
	// renvoie toutes les infos de loc
	========================================================================*/
	
	function GetLocTab ()
	{
		// ListeNoPostAExclure spécifique de NCK pour exclure les post taitements dans dans l'automate
		// pour ce liste le nom de lits sont concatiner de fasion suivant
		// NOLIT-NOPOST, et si ils sont declaré dans le strcutures les patients sont couché sur ces lits
		// si non l'automate affiche un warning "mise a jour stucture necessiare" et continue le traitement
		
		$ListeNoPostAExclure="'LEM','INP'";
		
		//Modfié le 10/01/2014 LOC+ MVT
		$sql ="SELECT 	
					PAT.NOIP,
		  			PAT.NMMAL,
		  			PAT.NMPMAL,
		  			to_char(PAT.DANAIS,'YYYY-MM-DD') as DANAIS,
		  			PAT.NOTLDO,
		  			LOC.NOLIT,
		  			LOC.NOCHAM,
		  			LOC.NOPOST,
		  			LOC.NOSERV,
		  			to_char(LOC.DDLOPT,'YYYY-MM-DD') as DDLOPT,
		  			PAT.CDSEXM,
		  			LOC.HHLOPT,
					DOS.NODA  	as NDA,
					TO_CHAR(mvt.damvad,'YYYY-MM-DD') as DTENT,
					MVT.HHMVAD as HHENT,
					mvt.nouf 		as UH,
					DOS.TYDOS 	as TYSEJ,
					MVT.tymvad 	as TYMVT,
					MVT.NOIDMV as NOIDMV
			FROM   dos, mvt,pat,loc
			WHERE mvt.noda = dos.noda
			and pat.noip =loc.noip(+)
				AND pat.noip=dos.noip
				AND dos.tydos = 'A'
				AND mvt.cddemv = 'O'
				AND mvt.tymaj != 'D'
 				AND mvt.tymvad IN ('EN', 'PI')
 				AND loc.nopost NOT IN(".$ListeNoPostAExclure.")
 				and DADEMJ > (sysdate -700)
 	UNION
			SELECT 	
					PAT.NOIP,
		  			PAT.NMMAL,
		  			PAT.NMPMAL,
		  			to_char(PAT.DANAIS,'YYYY-MM-DD') as DANAIS,
		  			PAT.NOTLDO,
		  			LOC.NOPOST||'-'||LOC.NOLIT as NOLIT,
		  			LOC.NOCHAM,
		  			LOC.NOPOST,
		  			LOC.NOSERV,
		  			to_char(LOC.DDLOPT,'YYYY-MM-DD') as DDLOPT,
		  			PAT.CDSEXM,
		  			LOC.HHLOPT,
					DOS.NODA  	as NDA,
					TO_CHAR(mvt.damvad,'YYYY-MM-DD') as DTENT,
					MVT.HHMVAD as HHENT,
					mvt.nouf 		as UH,
					DOS.TYDOS 	as TYSEJ,
					MVT.tymvad 	as TYMVT,
					MVT.NOIDMV as NOIDMV
			FROM   dos, mvt,pat,loc
			WHERE mvt.noda = dos.noda
			and pat.noip =loc.noip(+)
				AND pat.noip=dos.noip
				AND dos.tydos = 'A'
				AND mvt.cddemv = 'O'
				AND mvt.tymaj != 'D'
 				AND mvt.tymvad IN ('EN', 'PI')
 				AND loc.nopost IN(".$ListeNoPostAExclure.")
 				and DADEMJ > (sysdate -700)		
 				";
		  return 	$this->OraSelect($sql);		


				// uh pneumo
 				//AND mvt.nouf ='114'		

		
	 
	}
	/*========================================================================
	// function GetSortiesParDate($date)
	$date DD/MM/YYYY
	========================================================================*/

	function GetSortiesParDate($date)
	{
		// prepare n-1
		list($d,$m,$y)=explode("/",$date);
		//date vide ou invalide format date 
		if (strlen($d) < 1)
		{
			$date=date("d/m/Y");
			list($d,$m,$y)=explode("/",$date);
		}
		// date n-1
		$HierDate = date('d/m/Y', strtotime("$Y-$m-$d 0:0:0 - 1 day"));		
		
 		$sql="SELECT 	
						DOS.NOIP,
						DOS.NODA  	as NODA,
						TO_CHAR(mvt.damvad,'YYYY-MM-DD') as DTSOR,
						MVT.HHMVAD as HHSOR,
						mvt.nouf 		as UHSOR,
						DOS.TYDOS 	as TYSEJ,
						MVT.tymvad 	as TYMVT,
						MVT.NOIDMV as NOIDMV
			FROM   dos, mvt 
			WHERE mvt.noda = dos.noda
				AND dos.tydos = 'A'
				AND mvt.cddemv = 'O'
				AND mvt.tymaj != 'D'
 				AND mvt.tymvad IN ('SH')
 				and DADEMJ > (sysdate -5)

 				"; 
 				
 
		return 	$this->OraSelect($sql);							

		/* OLD
		$sql ="SELECT 
						DOS.NOIP,
						DOS.NODA  	as NDA,
						TO_CHAR(DASOR,'DD/MM/YYYY') as DTSOR,
						HHSOR as HHSOR,
						NOUFSO as UHSOR,
						DOS.TYDOS 	as TYSEJ
						
					FROM dos  
					WHERE to_char(dasor,'DD/MM/YYYY')='$date'  
					and  tydos='A'";
  		return 	$this->OraSelect($sql);						
		*/
	}
	
	/*========================================================================
	// function GetUPF()
	// renvoie un tableau du type array(numerodeposte => uh(s))
	========================================================================*/
	function GetUPF ()
	{
		$sql = "SELECT * FROM UFP ORDER BY NOPOST";
		$res = $this->OraSelect($sql);
		$nb = count($res);
		for ($i=0;$i < $nb;$i++)
		{
			if (count($uh[$res[$i]["NOPOST"]])==0)
				$uh[$res[$i]["NOPOST"]].= $res[$i]["NOUF"];
			else
				$uh[$res[$i]["NOPOST"]].= "|".$res[$i]["NOUF"];
		}
		return $uh;
	}

	/*========================================================================
	// function GetAllMedecins()
	// renvoie un tableau du type array()
	// CDPHOS	NMPHOS	NMPPHS	NOMAPH	LBTITR	NOTLPP	DDVALI	DFVALI	CDVALI	CMPHO	HHRVPH	DUMORV	CDUSER
	========================================================================*/
	function GetAllMedecins ()
	{
		$sql = "SELECT * from pho where DFVALI>sysdate  ";
		$res = $this->OraSelect($sql);
		return $res;
	}
	/*========================================================================
	// function GetSejourInfoParNda($nda)
	// renvoie un tableau tous les sejours coresponds ce sejour
	========================================================================*/
		
	function GetSejourInfoParNda($nda)
	{
		$sql="SELECT DOS.NOIP,NOUF,MVT.NODA,to_char(DAMVAD,'DD/MM/YYYY') as DTMVAD,  SUBSTR(HHMVAD,1,2)||':'||SUBSTR(HHMVAD,3,2) as HHMVAD,TYMVAD,MVT.NOIDMV
		 FROM MVT,DOS 
		 WHERE DOS.NODA=MVT.NODA AND DOS.NODA='".$nda."' AND TYMAJ !='D' AND TYMVAD in ('EN','PI','SH')ORDER BY DAMVAD,HHMVAD";
		$res = $this->OraSelect($sql);
		return $res;		
	}
	
	
	

} //FIN CLASS

?>
