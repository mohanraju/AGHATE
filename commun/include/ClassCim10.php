<?php
	// Class Cim10 inclure ClassMysql avant
	class Cim10 extends MySQL
	{
		/*
	 	==============================================================================
	 	Constructeur 
	 	function nestro($site)
	 	==============================================================================
	 	*/
		function Cim10()
		{
			parent::MySQL(); //initlaise connexion Mysql
		}
 	
	  function GetListeDiagbyCodOrLib($Cod,$Statut){
			$sql = "SELECT * FROM CIM10SPEC WHERE ( CODE1 like '%".$Cod."%'  or LIB like '%".$Cod."%' ) AND STATUT!=$Statut order by FREQ DESC";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function CheckCodageBeforePdf($NDA,$UH,$NOHJO){
	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
	  	
	  	$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D'".$sql_uh." AND diag = ''".$sql_nohjo;
			//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }
	  
	  function GetInfoCodageMsi($NDA,$UH,$NOHJO){
	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
				
			$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D'".$sql_uh.$sql_nohjo;
			//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }		  
	  
	  function GetCodageMsiDp($NDA,$UH,$NOHJO){

	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
			
			$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D' AND type='DP'".$sql_uh.$sql_nohjo;
			//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetCodageMsiDr($NDA,$UH,$NOHJO){
	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
			
			$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D' AND type='DR'".$sql_uh.$sql_nohjo;
			//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetCodageMsiDas($NDA,$UH,$NOHJO){
	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
			
			$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D' AND type='DAS'".$sql_uh.$sql_nohjo;
			//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }
	  
	  function GetCodageMsiActes($NDA,$DATEINTERVENTION,$NOHJO){
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
			
			if($DATEINTERVENTION!="")
			{
				$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D' AND type='ACTES'".$sql_nohjo." AND datrea='".$DATEINTERVENTION."'";
				//echo $sql."<br>";
			}
			else
			{
				$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D'".$sql_nohjo." AND type='ACTES'";
				//echo $sql."<br>";
			}
			$result = $this->select($sql);
      return $result;
	  }
	  
	  function GetCodageMsiActesForPrint($NDA,$UH,$NOHJO){
	  	if($UH!="") $sql_uh = " AND uhdem='".$UH."'"; else $sql_uh = "";
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
	  	
			$sql = "SELECT * FROM codage_msi WHERE nda='".$NDA."' AND valid!='D' AND type='ACTES'".$sql_uh.$sql_nohjo;
				//echo $sql."<br>";
			$result = $this->select($sql);
      return $result;
	  }
	  
	  function GetListeThesaurusByUser($User){
			$sql = "SELECT droit_value FROM user_droits WHERE login=$User order by id";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetListeThesaurusByAdmin($Site){
			$sql = "SELECT CONCAT( service_lib, '(', hopital, ')' ) AS Result FROM structure_gh WHERE hopital=$Site group by Result";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetServiceLib($Uh,$Dteent,$Nda){
	  	$Site=substr(trim($Nda),0,2);
			$sql = "SELECT  service_lib FROM structure_gh WHERE hopital='$Site' AND uh='$Uh' AND date_deb <= '$Dteent' AND date_fin >= '$Dteent'";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetAllServiceLib(){
			$sql = "SELECT  service_lib FROM structure_gh GROUP BY service_lib ";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }
	  
	  /*
	 	==============================================================================
	 	function Pour GESTION ACTES
	 	==============================================================================
	 	*/
	 	
	  function GetListeInterventionIpop($Nda,$Dteent){
			$sql = "SELECT * FROM ipop WHERE nda=$Nda AND Date_intervention >= '$Dteent'";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }	
	  
	  function GetListeInterventionCodage($Nda,$Dteent){
			$sql = "SELECT * FROM ipop, codage_msi WHERE ipop.nda=codage_msi.nda and codage_msi.nda=$Nda AND datrea >= '$Dteent' AND valid!='D' GROUP BY `datrea`";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }
	  
	  function GetListeIntervention($Nip,$Dteent,$NOHJO){
	  	if($NOHJO!="" && $NOHJO!="0") $sql_nohjo = " AND nohjo='".$NOHJO."'"; else $sql_nohjo = "";
	  	
			$sql = "SELECT * 
							FROM codage_msi 
							LEFT JOIN ipop ON codage_msi.nip=ipop.nip
							WHERE codage_msi.nip=$Nip 
							AND datrea >= '$Dteent' 
							AND valid!='D' 
							".$sql_nohjo."
							GROUP BY `datrea`";
							
			$sql = "SELECT * FROM(
								SELECT `id` as id_ipop,`nip`,`nda`,`Date_intervention` as datrea,`Type_inter` FROM `ipop`
								WHERE `nip`='$Nip'
								AND `Date_intervention` >= '$Dteent'
								UNION
								SELECT `id_ipop`,`nip`,`nda`,`datrea`,'' as Type_inter FROM `codage_msi`
								WHERE `nip`='$Nip'
								AND `datrea` >= '$Dteent'
								AND valid!='D'
								".$sql_nohjo."
								) AS temp
							GROUP BY `datrea`
							";
			//echo $sql;
			$result = $this->select($sql);
      return $result;
	  }
	}
?>
