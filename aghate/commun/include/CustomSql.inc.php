<?php

// set the level of error reporting  
//ini_set("error_reporting", E_ALL);
//ini_set("display_errors","0"); // masque ou afficahe les  erreurs
//ini_set("ignore_repeated_errors","1"); // evite la repetition des mm erreurs dans les logs
//ini_set("log_errors", "1" ); // inscrit les erreurs dans un fichier log
//ini_set("error_log",  "/home/virtual/domaine/files/errors.log" ); // le chemin de ce fichier
	

require("./commun/include/DbSql.inc.php");
//require("./commun/include/DbSql.inc.php");
	$user_add=$_SESSION["user_id"];
	$date_add =date("Y-m-d H:m:s");
	$user_modif=$_SESSION["user_id"];
	$date_modif=date("Y-m-d H:m:s");


Class CustomSQL extends DBSQL
{
   // the constructor
   function CustomSQL($DBName = "")
   {
      $this->DBSQL($DBName);
   }
	//========================================
	// Table Patients
	//========================================
	function GetPatInfo($id_pat){
    return false;
	}
   
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
		$sql="Select id,fieldname from agt_overload where id_area='".$service_id."'";
 
		$res=$this->select($sql);
		$retval="";
		for ($c=0;$c < count($res) ;$c++){
			$id=$res[0]['id'];
			$begin_string = "@".$res[$c]['id']."@";
	    $end_string = "@/".$res[$c]['id']."@";
	    $data = "";
	    
	    $begin_pos = strpos($overload_desc,$begin_string);
	    $end_pos = strpos($overload_desc,$end_string);		
	 		$first = $begin_pos + strlen($begin_string);
	 		
	    $data = substr($overload_desc,$first,$end_pos-$first);
			$data=   urldecode($data);
			$retval.=$res[$c]['fieldname'].":".$data. "<br />";
		}
      return $retval;
	}
	
	function get_overload_cahmp_xls($service_id,$overload_desc){
		$sql="Select id,fieldname from agt_overload where fieldname not in ('Protocole') and id_area='".$service_id."'";
		$res=$this->select($sql);
		$retval="";
 
		for ($c=0;$c < count($res) ;$c++){
			$id=$res[$c]['id'];
	    $begin_string = "@".$res[$c]['id']."@";
	    $end_string = "@/".$res[$c]['id']."@";
	    $data = "";
 
	    $begin_pos = strpos($overload_desc,$begin_string);
	    $end_pos = strpos($overload_desc,$end_string);		
	 		$first = $begin_pos + strlen($begin_string);
	    $data = substr($overload_desc,$first,$end_pos-$first);
			$data=   urldecode($data);
			$retval.=$data ."\t";
		}
      return $retval;
      
	}

/*--------------------------------------------------------------------

Funtion GetAdditionalParametre(Current Area, NomduParametreDecalrerDansLeArea,AdditionalDescriptionComplet)

--------------------------------------------------------------------
*/
	function GetAdditionalParametre($service_id,$NomDuChamp,$overload_desc){
		$sql="Select id,fieldname from agt_overload where fieldname  in ('$NomDuChamp') and id_area='".$service_id."'";
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
	
	

   // function by mohanraju
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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//=======================================================================
//  common functions hors data base
//=======================================================================
	// funtion permet de convertir le date du MySQL ver date normal

   function date_Mysql2Normal($StrDate,$YearFull=true,$time=true)
   {
   	$retval="";
		list($dt,$time)=explode(" ",$StrDate);
     	list($year,$month,$day)=explode("-", $dt);
     	if($YearFull){
			$retval="$day/$month/$year"; 
		}else{	
			$retval= "$day/$month/". substr($year,2); 
		}	
		// vérify valide date to check 00/00/0000
		if (isDate($retval)	){
			if($time){
				$retval.= " ".$time;
			}
			return $retval;
		}else{
			return "";
		
		}		
		
			 	
   }
   
   function date_Normal2Mysql($StrDate)
   {
   	if (empty($StrDate)) return "0000-00-00";
		list($dt,$time)=explode(" ",$StrDate);
     	list($day,$month,$year)=explode("/", $dt);
		return "$year-$month-$day $time";
   }
   
   
   function IsEmpty($tmpstr){
		if(strlen($tmpstr)<1){
			return "&nbsp;";
		}
		return $tmpstr;
	}
  function isDate($i_sDate) {
		$blnValid = true;
	preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/",  $i_sDate, $matches);
	if (!checkdate($matches[2], $matches[1], $matches[3])) {
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
	
	function GetHHMM ($time) {
		list($hh, $mm, $ss) = split(':', $time);
		return $hh.':'.$mm;
 	}
 
 	function MonthsBetween2Dates($Date_Fin,$Date_Deb,$format="YYYY-MM-DD" ) {
	  if ( (strlen($Date_Fin) < 10 ) || strlen($Date_Deb) < 10  ) return 0;
	  
	  if($format=="YYYY-MM-DD"){
		  list($year1,$month1,$day1)= explode("-", $Date_Fin);
		  list($year2,$month2,$day2) = explode("-", $Date_Deb);
	  }
	  if($format=="DD/MM/YYYY"){
		  list($day1,$month1,$year1)= explode("/", $Date_Fin);
		  list($day2,$month2,$year2) = explode("/", $Date_Deb);
	  }

	  $diff = mktime(0, 0, 0, $month1, $day1, $year1) -   mktime(0, 0, 0,  $month2, $day2, $year2);

	  $ret_val=$diff / 86400;
	  $ret_val=round($ret_val / 30.5,1);
	  if($ret_val < 0) return 0; else return $ret_val;
	}

	// calcul nbr des mois entre deux dates ATTN date FORMAT "YYYY-MM-DD"
 	function CalcNombreMois($fin,$debut ) {

	  if ( (strlen($fin) < 10 ) || strlen($debut) < 10  ) return "NSP";
	  if ( ($fin=="0000-00-00" ) || ($debut=="0000-00-00")  ) return "NSP";

	  $tDeb = explode("-", $debut);
	  $tFin = explode("-", $fin);
	  $diff = mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]) -   mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);

	  $ret_val=$diff / 86400;
	  $ret_val=round($ret_val / 30.5,1);
	  if($ret_val < 0) return 0; else return $ret_val;
	  
	  //return(($diff / 86400));
	}

?>
