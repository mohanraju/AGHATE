<?php

//===============================================
//Database settings
//===============================================
	
include "./config/config.php";
//===============================================
// recuparation GET , POST et Session Variables to direct valiabels
//===============================================
	if (count($_POST)) {
		while (list($key, $val) = each($_POST)) {
			$$key = $val;
		}
	}
	
	if (count($_GET)) {
		while (list($key, $val) = each($_GET)) {
			$$key = $val;
		}
	}
	
	if (count($_SESSION)) {
		while (list($key, $val) = each($_SESSION)) {
			$$key = $val;
		}
	}
	
	if (empty($PHP_SELF)) {
	$PHP_SELF = $_SERVER['PHP_SELF'];
	}
	


//===============================================
// recuparation GET et POST et Session Variables
//===============================================

	Class CustomMySQL  
	{
		var $Status;
	   var $Erreur="";  
	   Var $CONN;
	  function CustomMySQL($DATABASE="")
	   {     
    			global $DBHost,$DBUser,$DBPassword,$DBName;	   

		      if ($conn=mysql_connect($DBHost,$DBUser,$DBPassword))
		      {

				  	if(mysql_select_db($DBName,$conn))
				  	{
					  	$this->CONN = $conn;
		      		return true;				  	
					}
				  	else
				  	{
				  		echo "Erreur connextion Mysql <br />".mysql_error($conn);
				  		exit;
				  		return false;
				  	}
			  	
	
	   	}else{
	   	 	echo "<br />Impossible de connecter le base :".mysql_error($conn);	   	
				$this->Erreur=mysql_error($conn);
	   	 	$this->Status="Impossible de connecter le base ";
	   	 	exit;
	   	 	return false;
	   	 	
	   	}
	   }
	   function select($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	      $conn = $this->CONN;
	      $results = mysql_query($sql,$conn) or die("Erreur Sql :$sql .<br />".mysql_error($conn));
	      if ((!$results) or (empty($results)))
	      {       
	         return false;
	      }
	      $count = 0;
	      $data = array();
	      while ($row = mysql_fetch_array($results)) {
	         $data[$count] = $row;
	         $count++;
	      }
	      mysql_free_result($results);
	      return $data;
	   }
	
	   function execute($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	      $conn = $this->CONN;
	      $results = mysql_query($sql,$conn) or die("Erreur Sql :$sql .<br />".mysql_error($conn));
	      if ((!$results) or (empty($results)))
	      {       
	         return false;
	      }
	      return $results;
	      
	   }
	
	   
	   function insert($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	
	      $conn = $this->CONN;	
	      $results = mysql_query($sql,$conn) or die("Erreur Sql :$sql .<br />".mysql_error($conn));
	      if (!$results) return false;
	      $results = mysql_insert_id();
	      return $results;
	   }
	
	   
	   function update($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	
	      $result = mysql_query($sql,$conn) or die("Erreur Sql update:$sql .<br />".mysql_error($conn));;
	      return $result;
	   }
	
	   
	   function delete($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	      $result = mysql_query($sql,$conn) or die("Erreur Sql :$sql .<br />".mysql_error($conn));;
	      return $result;
	   }
	   
	   
	   function ViderTable($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	      $result = mysql_query($sql,$conn);
	      return $result;
	   }
	   
	//=================================================================
	// funcition spécifique du projet AGHATE
   //==============================================================
	function get_protocole($service_id,$overload_desc){
		$sql="Select id from agt_overload where fieldname='Protocole' and id_area='".$service_id."'";
		$res=$this->select($sql);
		$id=$res[0]['id'];
      $begin_string = "@".$res[0]['id']."@";
      $end_string = "@/".$res[0]['id']."@";
      $data = "";
      $begin_pos = strpos($overload_desc,$begin_string);
      $end_pos = strpos($overload_desc,$end_string);		
 		$first = $begin_pos + strlen($begin_string);
      $data = substr($overload_desc,$first,$end_pos-$first);
		$data=   urldecode($data);
      return $data;
	}
	

	function get_overload_cahmp($service_id,$overload_desc){
		$sql="Select id,fieldname from agt_overload where fieldname not in ('Protocole') and id_area='".$service_id."'";
		$res=$this->select($sql);
		$retval="";
		for ($c=0;$c < count($res) ;$c++){
			$id=$res[0]['id'];
	      $begin_string = "@".$res[0]['id']."@";
	      $end_string = "@/".$res[0]['id']."@";
	      $data = "";
	      $begin_pos = strpos($overload_desc,$begin_string);
	      $end_pos = strpos($overload_desc,$end_string);		
	 		$first = $begin_pos + strlen($begin_string);
	      $data = substr($overload_desc,$first,$end_pos-$first);
			$data=   urldecode($data);
			$retval.=$res[0]['fieldname'].":".$data ." ";
		}
      return $retval;
      
	}
	
	
	//=================================================================
   // Function by mohanraju
   //==============================================================
   // returns des areas allowed pour un utilisateur donnee   
   function get_areas_allowed($user,$statut){
   	$list_areas="";
		if ($statut=="administrateur"){
			$sql="SELECT id  from agt_service ";
		   $res = $this->select($sql);
			$list_areas="";
			for ($i = 0; $i < count($res); $i++){ 
				if (strlen($list_areas)>2){
					$list_areas.=",'".$res[$i]['id']."'";			
				}else{	
					$list_areas.="'".$res[$i]['id']."'";
				}   
			}			
		}else{
			$sql="SELECT id_area FROM agt_j_user_area WHERE login = '".$user."'";
		   $res = $this->select($sql);
			$list_areas="";
			for ($i = 0; $i < count($res); $i++){ 
				if (strlen($list_areas)>2){
					$list_areas.=",'".$res[$i]['id_area']."'";			
				}else{	
					$list_areas.="'".$res[$i]['id_area']."'";
				}   
			}			
		}
		

		return $list_areas;

	}
 
		
	
	}
	


//=======================================================================
//  common functions
//=======================================================================
   function date_Mysql2Normal($StrDate)
   {
		list($dt,$time)=explode(" ",$StrDate);
     	list($year,$month,$day)=explode("-", $dt);
		return "$day/$month/$year";
   }
   function date_Normal2Mysql($StrDate)
   {
   	if(strlen($StrDate)< 1) return "0000-00-00";
		list($dt,$time)=explode(" ",$StrDate);
     	list($day,$month,$year)=explode("/", $dt);
		return "$year-$month-$day";

   }
   
   function IsEmpty($tmpstr){
		if(strlen($tmpstr)<1){
			return "&nbsp;";
		}
		return $tmpstr;
	}

	//10-12-2010 => 2010-12-10
   function date_Oracl2Mysql($StrDate)
   {
		list($dt,$time)=explode(" ",$StrDate);
     	list($date,$month,$year)=explode("-", $dt);
		return "$year-$month-$date";
   }
 


function GetFirstAndLastDaysOfWeek($week,$year)
{
	if(strftime("%W",mktime(0,0,0,01,01,$year))==1)
	  $mon_mktime = mktime(0,0,0,01,(01+(($week-1)*7)),$year);
	else
	  $mon_mktime = mktime(0,0,0,01,(01+(($week)*7)),$year);
	 
	if(date("w",$mon_mktime)>1)
	  $decalage = ((date("w",$mon_mktime)-1)*60*60*24);
	 
	$lundi = $mon_mktime - $decalage;
   $dimanche = $lundi + (6*60*60*24);
	return array(date("d/m/Y",$lundi),date("d/m/Y",$dimanche));
}
 

	//==============================================================
	// check valid date formate dd/mm/YYYY
	//==============================================================	 

  function isDate($i_sDate) {
		$blnValid = true;
   	if(!ereg ("^[0-9]{2}/[0-9]{2}/[0-9]{4}$", $i_sDate)) {
			$blnValid = false;
   	}else{
	     	$arrDate = explode("/", $i_sDate); // break up date BY slash
	      $intDay = $arrDate[0];
	      $intMonth = $arrDate[1];
	      $intYear = $arrDate[2];
	      $intIsDate = checkdate($intMonth, $intDay, $intYear);
	      if(!$intIsDate){
	        	$blnValid = false;
	     	}
		}

   	return ($blnValid);
	} 
	//==============================================================
	// check valid date formate dd/mm/YYYY
	//==============================================================	 

  function isDateNormal($i_sDate) {
		$blnValid = true;
		$chars = array("/", "0", "-"," ");
		if(strlen(str_replace($chars,"",$i_sDate)==0)){
			  	$blnValid = false;
		}else{
	     	$arrDate = explode("/", $i_sDate); // break up date BY slash
	      $intDay = $arrDate[0];
	      $intMonth = $arrDate[1];
	      $intYear = $arrDate[2];
	      $intIsDate = checkdate($intMonth, $intDay, $intYear);
			//echo "<br /> date $i_sDate, d :$intDay m : $intMonth y: $intYear final :$intIsDate	      ";
	      if(!$intIsDate){
	        	$blnValid = false;
	     	}
		}
	
	if(strlen($i_sDate)< 8){
			  	$blnValid = false;
			}

   	return $blnValid;
	}	
	//==============================================================
	// check valid date formate YYYY-MM-DD
	//==============================================================
  function isDateMysql($i_sDate) {
		$blnValid = true;
   	if(!ereg ("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $i_sDate)) {
			$blnValid = false;
   	}else{
	     	$arrDate = explode("-", $i_sDate); // break up date BY slash
	      $intYear = $arrDate[0];
	      $intMonth = $arrDate[1];	      
	      $intDay = $arrDate[2];	      
	      $intIsDate = checkdate($intMonth, $intDay, $intYear);
	      if(!$intIsDate){
	        	$blnValid = false;
	     	}
		}

   	return ($blnValid);
	} 
	//==============================================================
	// check valid date formate YYYY-MM-DD
	//==============================================================

	function DatePlus($date,$nbrjours=0,$nbrmois=0,$nbrannee=0){
		if (substr($date,2,1)=="/"){
			$sep="/";
		}elseif(substr($date,4,1)=="-"){
			$sep="-";	
		}else{
			return "Date format inconu($date)";
		}
		if ($sep=="/"){
			list($day,$month,$year)=explode("/", $date);
			$retval = date("d/m/Y", mktime(0, 0, 0, $month+$nbrmois, $day+$nbrjours ,  $year+$nbrannee));
			return $retval;
		}	
		if ($sep=="-"){
			list($year,$month,$day)=explode("-", $date);
			$retval = date("Y-m-d", mktime(0, 0, 0, $month+$nbrmois, $day+$nbrjours ,  $year+$nbrannee));
			return $retval;
		}	

	}		 
	//==============================================================
	// Function Encode pour passer les variables cripté en http projet sarcobase
	//==============================================================

	function encode($nip,$dt,$med){
		$nip=floor($nip);
		$dt=str_replace("-","OF",$dt);
		$nip= $nip * 2;
		$nip=substr($nip,0,2)."OC".substr($nip,2,2)."OE".substr($nip,4,2)."OD".substr($nip,6,2)."OB".substr($nip,8,5);
		$res=$dt."OA".$nip."OA".$med;
		return $res;
	}

	//==============================================================
	// Function decode pour recuperer les variables cripté en http projet sarcobase
	//==============================================================
	function decode($donnee){
		list($dt,$nip,$med)=explode("OA",$donnee);
		$nip=str_replace("OC","",$nip);	
		$nip=str_replace("OE","",$nip);	
		$nip=str_replace("OD","",$nip);	
		$nip=str_replace("OB","",$nip);	
		
		$dt=str_replace("OF","-",$dt);
		$nip=floor($nip);
		$nip=$nip / 2;
		$res= $dt ." ". $nip ." " .$med;
		return $res;
	}
			
?>
