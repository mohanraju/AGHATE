<?php
//========================================================================
// cette ajax vérify les RDV +-5 jours du current RDV
// Par mohanraju /DBIM/MSI le 12/02/2009
//========================================================================
	include "./commun/include/CustomSql.inc.php";
	$db = new CustomSQL($DBName);
   $param=$_GET['param']; // The parameter passed to us
	
	//echo $param ;
	//$param="20-02-2010|1240898400";
	list($date,$noip,$id,$session_user,$session_statut) = explode('|', $param);// découpte nip et date
	 
	list($day,$mois,$annee)=explode("-",$date);
	$date=mktime(0,0,0,$mois,$day,$annee);	
	//$session_user = $_SESSION['login'];
   //$session_statut = $_SESSION['statut'];
   $list_areas =$db->get_areas_allowed($session_user,$session_statut);
	
	$cing_jour=60*60*24*5;
	// vérifiy id 
	$chk_id=$id + 1;
	if ($chk_id==1)$id=0;
	$date_deb =$date - $cing_jour;
	$date_fin =$date + $cing_jour;
   $sql = "select agt_room.room_name, name, agt_loc.description, start_time, end_time,type, room_id,noip,medecin,agt_service.service_name,agt_room.service_id 
    			from agt_loc,agt_room ,agt_service
    			where agt_room.id=agt_loc.room_id
    			and agt_room.service_id=agt_service.id
    			and noip='$noip'
    			and agt_loc.id not in($id)
    			and start_time between $date_deb and $date_fin
    			and agt_service.id in ($list_areas )
    			order by start_time
    			";
 
	$res=$db->select($sql);
	$retval="";
	for($i=0;$i<count($res);$i++){
		$area=$res[$i]['service_id'];
		$Protocole=$db->get_protocole($area,$over);
		$retval.="Le ".date("d/m/Y à H:i",$res[$i]['start_time'])."- ".$Protocole."-[Salle/".$res[$i]['room_name']."]\n";;
		}

  	echo $retval;
  		
 ?>
