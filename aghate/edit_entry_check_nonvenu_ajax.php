<?php
//========================================================================
// cette ajax récupare le area, room ,nip et le date pour une  resevation  
//	et retourne si il a non venue plus de 3 fois 
// Par mohanraju /DBIM/MSI le 10/09/2010
//========================================================================
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);
	$retval = "0" ;
   $param=$_GET['param']; // The parameter passed to us
 
	//echo $param ;
	
	list($noip,$date,$area,$room,) = split('[|]', $param);// découpe 
	
	// get rrom name
	$sql="select name from grr_nonvenu,agt_room  
			where agt_room.id= grr_nonvenu.room_id
			AND agt_room.service_id='$area'
			AND name like ('%$noip%')  ";
	$res=$db->select($sql);
	if (count($res) > 0 )
		echo count($res);
	else
  		echo $retval;
 ?>
