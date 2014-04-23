<?php
//========================================================================
// cette ajax récupare le area, room et le date pour une  resevation  
//	et retourne le groupe attendu dans le journée et dan le salle 
// si l'égalité reourne "0"
// Par mohanraju /DBIM/MSI le 28/03/2010
//========================================================================
	include "./commun/include/CustomSql.inc.php";
	$db = new CustomSQL($DBName);
	$retval = "0" ;
   $param=$_GET['param']; // The parameter passed to us
 
	//echo $param ;
	list($date,$area,$room) = split('[|]', $param);// découpte lit et date 
	// prepare querry 
	$query="
			select type, count(type) as tot from agt_loc 
			where from_unixtime( start_time, '%d-%m-%Y' )='$date' 
			and agt_loc.room_id='$room'			
			group by type
		 ";
  	$result = $db->select($query);
  	$count = count($result);
  	if ($count > 0) {
  		$type=($result[0]['type']);
  		$res=$db->select("select type_name from agt_type_area where type_letter='".$type."'");
  		$lib=$res[0]['type_name'];
  		$retval=  utf8_encode($type."|".$lib) ;
  	}
  	echo $retval;
 ?>
