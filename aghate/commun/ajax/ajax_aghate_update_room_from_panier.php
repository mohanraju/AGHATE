<?php
include "../../config/config.php";
include "../../config/config.inc.php";
include "../include/ClassMysql.php";
include "../include/ClassAghate.php";
include "../include/CommonFonctions.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$res=$Aghate->GetInfoReservation ($id_resa);

if ( (count($res)> 0) && (strlen($new_room)>0))
{
	$res=$Aghate->CheckRoomDispo($new_room,$res[0]['start_time'],$res['end_time'],0,$id_resa);
	if (strlen($res) == 0){
		$req_maj = "UPDATE agt_loc SET room_id = '".$new_room."' WHERE id='".$id_resa."'";
		$where[0] = "id=".$id_resa.""; 
		$mysql->update($req_maj);
		echo "|OK|";
	}else{
		echo $res;
	}
}else{
	echo "|ERR|Donnee obligaire manques, Nouveau lit: [".$new_room."] reservation id: [".$id_resa."]";
}
?>
