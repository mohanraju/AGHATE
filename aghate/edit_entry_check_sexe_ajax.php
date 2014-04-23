<?php
//========================================================================
// cette ajax récupare le area, room et le date pour une  resevation  
//	et retourne le sexe compatible dans le chambre
// si l'égalité reourne "I"
// Par mohanraju /DBIM/MSI le 12/02/2009
//========================================================================
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);
	$retval = "I" ;
   $param=$_GET['param']; // The parameter passed to us
 
	//echo $param ;
	
	list($date,$area,$room,) = split('[|]', $param);// découpte lit et date 
	
	// get rrom name
	$sql="select room_name from agt_room where id=".$room;
	$res=$db->select($sql);
	$room=$res[0]['room_name'];
	$room=substr($room,0,2);// prémière deuc cahr pour tester le CHAMBRE	
	// prepare querry 
	
	$query="select sum(M) as M ,sum(F) as F from
			(
			select count(type) as M, 0 as F from agt_room,agt_loc 
			where agt_room.id =agt_loc.room_id
			and left(room_name,2)='$room'
			AND  from_unixtime( start_time, '%d-%m-%Y' )='$date' 
			and agt_room.service_id='$area'			
			and type='M'
			group by type
		union 
			select 0 as M ,count(type) as F from agt_room,agt_loc 
			where agt_room.id =agt_loc.room_id
			and left(room_name,2)='$room'
			and agt_room.service_id='$area'			
			AND   from_unixtime( start_time, '%d-%m-%Y' )='$date' 
			and type='F'
			group by type
			)
		as dfg ";
//echo 		$query;
  	$result = $db->select($query);
  	$count = count($result);
  	if ($count > 0) {

  		$male= (int)($result[0]['M']);
  		$female= (int)$result[0]['F'];
  		
  		if ($male==$female)$retval="I";
  		if ($male < $female)$retval="F";  		
  		if ($male > $female)$retval="M";  		  		

  	}
  	echo $retval;
 ?>
