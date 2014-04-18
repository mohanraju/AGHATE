<?php
//========================================================================
// cette ajax récupare le area, room et le date pour une  resevation  
//	et retourne le sexe compatible dans le chambre
// si l'égalité reourne "I"
// Par mohanraju /DBIM/MSI le 12/02/2009
//========================================================================
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);
	$retval = "0" ;
   $param=$_GET['param']; // The parameter passed to us
 
	//echo $param ;
	
	list($date,$area,$room,) = split('[|]', $param);// découpte lit et date 
	
	// get rrom name
	$sql="select room_name from agt_room where id=".$room;
	$res=$db->select($sql);
	$room=$res[0]['room_name'];
	$room=substr($room,0,2);// prémière deuc cahr pour tester le CHAMBRE	
	// prepare querry 
	
	$query="select type,count(type) as tot from agt_room,agt_loc 
			where agt_room.id =agt_loc.room_id
			AND  from_unixtime( start_time, '%d-%m-%Y' )='$date' 
			and agt_room.service_id='$area'			
			group by type ORDER BY tot DESC";
//echo 		$query;
  	$result = $db->select($query);
  	$count = count($result);
  	if ($count > 0) {
  		$retval=$result[0]['type'];
  	}
  	echo $retval;
 ?>
