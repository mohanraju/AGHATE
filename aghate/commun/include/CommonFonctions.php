<?php
//===============================================
// recuparation GET , POST et Session Variables to normal valiabels
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
	
 //echo "COUNTGET:".count($_GET)."<br />";
 //echo count($_POST)."post";
	
	if (empty($PHP_SELF)) {
	$PHP_SELF = $_SERVER['PHP_SELF'];
	}
	
//===============================================
// Class common functions used in PHP
//===============================================
Class CommonFunctions  
{
	/*
	--------------------------------------------------------------------------
	constructeur CALSS
	function ModeDev($ModeDev=false)
	// si mode dev active affiche les errers 
	--------------------------------------------------------------------------
	*/ 
	function CommonFunctions($ModeDev=false)
	{
		if ($ModeDev){		
			//ini_set('error_reporting', E_ALL );
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set("display_errors", 1);
		}else{
			ini_set("display_errors", 0);//deactive les erreur affiche sur l'ecran
		}
		set_time_limit(0);// pour éviter le max time 30 sec pour l'execution des requettes
	}  	

	/*
	--------------------------------------------------------------------------
	function Mysql2Normal($StrDate)
	Function convert date Mysql format to normal
	YYYY-MM-JJ => JJ/MM/YYYY
	--------------------------------------------------------------------------
	*/ 
	function Mysql2Normal($StrDate)
  {
  	if (strlen($StrDate) > 9){

  		// split date and time
			list($dt,$time)=explode(" ",$StrDate);
			// split date month year
     	list($year,$month,$day)=explode("-", $dt);
     	// return date
     	if (strlen($time) > 1)
				return "$day/$month/$year $time";
			else
				return "$day/$month/$year";
		}else{
			return "00/00/0000";
		}
	}
	
	/*
	--------------------------------------------------------------------------
	function Normal2Mysql($StrDate)
	Function convert date Mysql format to normal
	YYYY-MM-JJ => JJ/MM/YYYY
	--------------------------------------------------------------------------
	*/ 
	function Normal2Mysql($StrDate)
   {
		if (strlen($StrDate) > 9){   	
			list($dt,$time)=explode(" ",$StrDate);
	    list($day,$month,$year)=explode("/", $dt);
	    // si time ajoute le 
	    if (strlen($time) > 1)
				return "$year-$month-$day $time";
			else
				return "$year-$month-$day";
		}else{
			return "0000-00-00";
		}
   }

	/*
	--------------------------------------------------------------------------
	function IsEmpty($tmpstr)
	Function verifys le string est empty si oui renvoi une espace html
	pour éviter le tableau html vide
	--------------------------------------------------------------------------
	*/ 
	function IsEmpty($tmpstr){
		if(strlen($tmpstr)<1){
			return "&nbsp;";
		}
		return $tmpstr;
	}
	/*
	--------------------------------------------------------------------------
	function NombreJours($date1,$date2)
	Function retourne sour format tableau le premier et le dernièrejour du semaine 
	
	--------------------------------------------------------------------------
	*/
	
function NombreJours($date1,$date2)
{
 $s = strtotime($date2)-strtotime($date1);
 $d = intval($s/86400)+1;  
 return "$d";
} 
	
	/*
	--------------------------------------------------------------------------
	function GetFirstAndLastDaysOfWeek($week,$year)
	Function retourne sour format tableau le premier et le dernièrejour du semaine 
	
	--------------------------------------------------------------------------
	*/
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
 
	/*
	--------------------------------------------------------------------------
	function isDate($week,$format="dd/mm/YYYY")
	Function retourne oui ou non
	
	--------------------------------------------------------------------------
	*/
  function isDate($StrDate,$format="dd/mm/yyyy") {
		$DtValid = true;
		$format=strtoupper($format);
		// check normal foramt
		if ( ($format=="DD/MM/YYYY") or ($format=="JJ/MM/YYYY") )
		{
			$DtValid=ereg ("^[0-9]{2}/[0-9]{2}/[0-9]{4}$", $StrDate);
   		$arrDate 	= explode("/", $StrDate); // break up date BY slash	
	    $intDay 	= $arrDate[0];
	    $intMonth = $arrDate[1];
	    $intYear 	= $arrDate[2];   				
		}else 
		// check Mysql Format
		if(($format=="YYYY-MM-DD")  or ($format=="YYYY-MM-DD") )
		{
			$DtValid=ereg ("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $StrDate);
			$arrDate 	= explode("-", $StrDate); // break up date BY slash
	    $intYear 	= $arrDate[0];   						
	    $intMonth = $arrDate[1];
	    $intDay 	= $arrDate[2];	    
		}
		else
		{
			return false ; // format inconnu
		}
		
  	if($DtValid)
  	{
      $intIsDate = checkdate($intMonth, $intDay, $intYear);
      if(!$intIsDate)
      {
				$DtValid = false;
	    }
		}
   	return ($blnValid);
	} 
	/*
	--------------------------------------------------------------------------
	function DatePlus($StrDate,$nbrjours=0,$nbrmois=0,$nbrannee=0)
	add jour ou mois ou annee dans ue date
	fromat du date obligatoirement dd/mm/yyyy ou yyyy-mm-dd
	--------------------------------------------------------------------------
	*/
	function DatePlus($StrDate,$nbrjours=0,$nbrmois=0,$nbrannee=0){
		
		if ($nbrjours==0 and $nbrmois==0 and $nbrannee==0){
			return $StrDate;	
		}
		//format dd/mm/yyyy
		if(preg_match("/^([0-9]{2}\/[0-9]{2}\/[0-9]{2})/", $StrDate))
		{
	 		$arrDate 	= explode("/", $StrDate); // break up date BY slash	
			$intDay 	= $arrDate[0];
			$intMonth = $arrDate[1];
			$intYear 	= $arrDate[2];   				
			$retval = date("d/m/Y", mktime(0, 0, 0, $intMonth+$nbrmois, $intDay+$nbrjours ,  $intYear+$nbrannee));
			return $retval;
		}
		
		if(preg_match("/^([0-9]{4}\/[0-9]{2}\/[0-9]{2})/", $StrDate))
		{
	 		$arrDate 	= explode("/", $StrDate); // break up date BY slash	
			$intDay 	= $arrDate[0];
			$intMonth = $arrDate[1];
			$intYear 	= $arrDate[2];   				
			$retval = date("d/m/Y", mktime(0, 0, 0, $intMonth+$nbrmois, $intDay+$nbrjours ,  $intYear+$nbrannee));
			return $retval;
		}
		
		if(preg_match ("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $StrDate))
		{
	 		$arrDate 	= explode("-", $StrDate); // break up date BY slash	
			$intYear 	= $arrDate[0];   					 		
			$intMonth = $arrDate[1];
			$intDay 	= $arrDate[2];	    
			$retval = date("d/m/Y", mktime(0, 0, 0, $month+$nbrmois, $day+$nbrjours ,  $year+$nbrannee));
			return $retval;
		}
		// si on arrive ici le date format sont pas bonne donc on retourne le date recu
		return $StrDate;
	}		 
	
		/*
	============================================================	
	//Permet de convertur une chaine en date
	//chaine passe à  YYYY-MM-DD HH:II:SS
	=================================================================	
	*/
	
	function mettreDate($date){
		$new_date='';
		for($compte=0;$compte<strlen($date);$compte ++){
			switch($compte){
				case($compte == 4 or $compte == 6):
					$new_date=$new_date."-".$date[$compte];
				break;
				
				case($compte == 8):
					$new_date=$new_date." ".$date[$compte];
				break;
				
				case($compte == 10 or $compte == 12):
					$new_date=$new_date.":".$date[$compte];
				break;
				
				default:
					$new_date=$new_date.$date[$compte];
				break;
			}
		}
		$new_date=substr($new_date,1,-1);
		return $new_date;
	}
	
	
		/*
	============================================================	
	//Permet de convertir une date YYYY-MM-DD HH:II:SS
	//en format pour timelineJS YYYY,MM,DD,HH,II,SS
	=================================================================	
	*/
	function changeDateTimelineJs($date)
	{	
		$replace=array ('-',' ',':');
		$date=str_replace($replace,',',$date);
		return $date;
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
	/*
	----------------------------------------------------------------------
	function is_num($var);
	checks given variable est number (no sign) only 0-9
	assci 48=0 et 57=9
	----------------------------------------------------------------------
	*/
	function IsNumber($var)
	{
		$retval=false ;// init
		for ($i=0;$i< strlen($var);$i++)
	  {
	  	$ascii_code=ord($var[$i]);
 			if((48 <= $ascii_code) && ($ascii_code <= 57))	  	
			{
				$retval=true;
			}else{
	    	return false;
	    }
		}
		return $retval;
	}	
}//end class			



//======================================================================================
// functions hors class
//======================================================================================

//--------------------------------------------------------------------------
// function JsonEncode($array,$default=true)
//--------------------------------------------------------------------------

function JsonEncode($array,$default=true)
{
	$nbElement = count($array);
	$compteur = 0;
	$json='{';

	foreach($array as $cle => $valeur)
	{
		if(is_array($valeur))
			$json.='"'.$cle.'":'.JsonEncode($valeur,false);
		else
			$json.='"'.$cle.'":"'.$valeur.'"';
			
		if(++$compteur != $nbElement)
			$json.=',';
	}
	$json.='}';
		
	return addslashes($json);
}
//--------------------------------------------------------------------------
// function JsonDecode($json)
//--------------------------------------------------------------------------

function JsonDecode($json)
{
	$json=stripslashes($json);
	$json=str_replace('"','',$json);
	if($json[0] == "[")
		$json=substr($json,2,-2);
	else
		$json=substr($json,1,-1);

	$json=explode(",",$json);
	$tmp=explode(":",$json[$i]);
	for($i=0;$i<count($json);$i++)
	{
		$tmp=explode(":",$json[$i]);
		$j=0;

			if(($tmp[1][0] != "{") && ($tmp[1][0] != "[") )
				$array[$tmp[$j]]=$tmp[$j+1];
			else
			{
				$compteurOuvert=0;
				$compteurFermee=0;
				$first=false;
				$newJson="";
				$last=$tmp[0];
				$k=1;
				$l=$i;
				do
				{	
					if($k == count($tmp))
					{ 
						$l++;
						$k=0;
						$tmp=explode(":",$json[$l]);
						
					}
					
					if($tmp[$k][0] == "{")
					{
						$compteurOuvert++;
						$newJson.=$tmp[$k];
							$newJson.=":";
					}
					elseif($tmp[$k][strlen($tmp[$k])-1] == "}")
					{
						$nbOccu=substr_count($tmp[$k],"}");
						$compteurFermee+=$nbOccu;
						$newJson.=$tmp[$k];
						if($compteurFermee != $compteurOuvert)
							$newJson.=",";
					}
					else
					{
						if($newJson[strlen($newJson)-1] == ",")
							$newJson.=$tmp[$k].":";
						elseif($newJson[strlen($newJson)-1] == ":" && ($compteurFermee != $compteurOuvert))
							$newJson.=$tmp[$k].",";
					}
					$k++;
				}while($compteurFermee != $compteurOuvert);
				//echo $last."<br>".$newJson;
				$array[$last]=JsonDecode($newJson);
				$j=$k;
				$i=$l;
			}
		
	}
	
	return $array;
	
}

?>
