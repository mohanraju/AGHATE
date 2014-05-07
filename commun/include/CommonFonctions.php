<?php
//===============================================
// recuparation GET , POST et Session Variables to normal valiabels
//===============================================
	if (count($_SESSION)) {
		while (list($key, $val) = each($_SESSION)) {
			$$key = $val;
		}
	}
	
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
   	$time="";
		if(strlen($StrDate) > 9){   	
			if (strlen(trim($StrDate))==10)
				$dt=$StrDate;
			else
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
  function isDate($StrDate,$format="DD/MM/YYYY") {
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
		if(ereg ("^[0-9]{2}/[0-9]{2}/[0-9]{4}$", $StrDate))
		{
	 		$arrDate 	= explode("/", $StrDate); // break up date BY slash	
	    $intDay 	= $arrDate[0];
	    $intMonth = $arrDate[1];
	    $intYear 	= $arrDate[2];   				
			$retval = date("d/m/Y", mktime(0, 0, 0, $month+$nbrmois, $day+$nbrjours ,  $year+$nbrannee));
			return $retval;
		}
		
		if(ereg ("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $StrDate))
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

	function CalculAge($ddn,$daent)
	{
	 //$d2 = date("d/m/Y");;

   	 $j2=substr($daent,0,2);
   	 $m2=substr($daent,3,2);
   	 $a2=substr($daent,6);

	  $age=-1;
	  if ( $ddn != "" ) {
   	 	$j1=substr($ddn,0,2);
   	 	$m1=substr($ddn,3,2);
   	 	$a1=substr($ddn,6);
 
  		$age=$a2-$a1;

  		if ( $m2 < $m1 ) --$age;
  		else if ( $m2 == $m1 && $j2 < $j1 ) --$age;

  		if ( $age < 0 ) $age = 0;
		if ( $age > 1 ) $str=$age." ans";
		else $str=$age." an";
  		return($str);
	  }
	}

	//==============================================================
	// Function Récupère le résultat d'une page via l'url
	/*
	//set data (in this example from post)
	
	//sample data
	$postdata = array(
	    'Dp' => 'z511',
	    'Age' => $_POST['age'],
	    'Sexe' => $_POST['sex']
	);
	$postdata =$_POST;
	
	$res=do_post_request("http://www.google.fr", $postdata);
	echo $res;
	*/
	//==============================================================	
	function GetPostRequest($url, $postdata )
	{
	    $data = "";
	    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
	      
	    //Collect Postdata
	    foreach($postdata as $key => $val)
	    {
	        $data .= "--$boundary\n";
	        $data .= "Content-Disposition: form-data; name=\"".$key."\"\n\n".$val."\n";
	    }
	    
	    $data .= "--$boundary\n";
	   
	 
	 
	    $params = array('http' => array(
	           'method' => 'POST',
	           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
	           'content' => $data
	        ));
	
	   $ctx = stream_context_create($params);
	   $fp = fopen($url, 'rb', false, $ctx);
	  
	   if (!$fp) {
	      throw new Exception("Problem with $url, $php_errormsg");
	   }
	 
	   $response = @stream_get_contents($fp);
	   if ($response === false) {
	      throw new Exception("Problem reading data from $url, $php_errormsg");
	   }
	   return $response;
	}

	/*
	----------------------------------------------------------------------
	function DateCheck($date1,$date2);
	format attendu est dd/mm/YYY
	return true or false
	----------------------------------------------------------------------
	*/
	function JoursBetween2Dates($Date1,$Date2,$format="dd/mm/yyyy"){
	
	   $Date1 =substr($Date1,0,10); //suprimer l'heure
	   $Date2 =substr($Date2,0,10); //suprimer l'heure
	
	 if($format=="YYYY-MM-DD"){
	     list($year1,$month1,$day1) = explode("-", $Date1);
	     list($year2,$month2,$day2) = explode("-", $Date2);
	 }
	 if($format=="dd/mm/yyyy"){
	     list($day1,$month1,$year1) = explode("/", $Date1);
	     list($day2,$month2,$year2) = explode("/", $Date2);
	 }
	 $dt1=mktime(0, 0, 0, $month1, $day1, $year1);
	 $dt2=mktime(0, 0, 0,  $month2, $day2, $year2);
	
	 $diff = $dt1 -   $dt2;
	 $ret_val=$diff / 86400;
	 $ret_val=round($ret_val,1);
	 return $ret_val;
	}
	
	/*
	----------------------------------------------------------------------
	function GetNomFichier($_service);
	return NomFichier propre
	----------------------------------------------------------------------
	*/
	function ConvertNomService($_service){
		//$_service_titre= (str_replace("@"," ",$_service));
		$search  = array('(', '-', '&',')',' ','@');
		$_service=strtoupper(str_replace($search,"",$_service));
		return($_service);
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
	
	/*
	----------------------------------------------------------------------
	function GetAbsoluteUrl($path,$level);
	Retourne  URL complet en ajoutant le repertoire du level choisi
	$level = 1 ; $path = /commun/rest/rest_aghate_get_resa_info_from_id.php
	----------------------------------------------------------------------
	*/
	function GetAbsoluteUrl($path,$level){
		$url='http://';
		$tabloc=explode('/',$_SERVER['PHP_SELF']);$tabloc[0]=$_SERVER['HTTP_HOST'];
		for($i=0;$i<($level+1);$i++){
			$url.=$tabloc[$i].'/';
		}
		$url.=$path;
		return $url;
	}
	
	/*
	----------------------------------------------------------------------
	function GetRestUrl($source);
	Retourne  URL complet en ajoutant les repertoires avant le fichier source
	$source = rest_aghate_get_resa_info_from_id.php
	----------------------------------------------------------------------
	*/
	function GetRestUrl($source){
		$tabloc=explode('/',$_SERVER['PHP_SELF']);
		$url='http://'.$_SERVER['HTTP_HOST'].'/'.$tabloc[1].'/commun/rest/'.$source;
		return $url;
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
		
	return $json;
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
