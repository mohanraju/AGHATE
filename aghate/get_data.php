<?php
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";


$db = new MySql();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";


//INSERER DANS AFFICHE SERVICE DETAIL
// requete pour récupérer les idp aussi
$sql = "SELECT 	start_time, end_time,de_source,ds_source,statut_entry,type,nda,agt_pat.noip,nom,prenom,sex, 
				agt_loc.id as entry_id,agt_room.room_name,agt_room.room_alias,agt_room.id,
				agt_room_idp.id as room_idp_id,agt_room_idp.room_id as room_idp_room_id ,start_time_idp,end_time_idp,motif
			FROM agt_service
			LEFT JOIN agt_room ON agt_service.id=agt_room.service_id
			LEFT JOIN agt_room_idp ON agt_room.id = agt_room_idp.room_id 
					AND (start_time_idp BETWEEN $date_deb AND $date_fin 
					OR end_time_idp BETWEEN $date_deb AND $date_fin
					OR start_time_idp < $date_deb AND end_time_idp > $date_fin)
			LEFT JOIN agt_loc ON agt_loc.room_id=agt_room.id
			LEFT JOIN agt_pat ON agt_loc.noip=agt_pat.noip  	
			AND statut_entry !='SUPPRIMER'
			AND (start_time BETWEEN $date_deb  AND $date_fin 
					OR end_time BETWEEN $date_deb AND $date_fin
					OR start_time < $date_deb AND end_time > $date_fin
				) 	
			WHERE agt_service.id='$service_id' 
			AND agt_service.etat='n'

			ORDER BY room_name,start_time";

$res= $db->select($sql);
//echo "<pre>";
//print_r($res);
//echo $sql;

for($i=0;$i < count($res);$i++)
{
	$pat=array();
	if($res[$i]['noip']){
		$pat['pat']=$res[$i]['nom']." ".$res[$i]['prenom']. "(".$res[$i]['noip'].")";
		$pat['deb']=$res[$i]['start_time'];
		$pat['de_source']=$res[$i]['de_source'];
		$pat['fin']=$res[$i]['end_time'];
		$pat['ds_source']= $res[$i]['ds_source'];
		$pat['noip'] = $res[$i]['noip'];
		$pat['nda'] = $res[$i]['nda'];
		$pat['duree']=(($res[$i]['end_time']-$res[$i]['start_time']) / 3600);
		$pat['room_id'] = $res[$i]['id'];
		$pat['entry_id'] =  $res[$i]['entry_id'];
		$pat['sex'] =  $res[$i]['sex'];	
		$pat['start_idp'] = $res[$i]['start_time_idp'];
		$pat['end_idp'] = $res[$i]['end_time_idp'];
		$pat['duree_idp']=(($res[$i]['end_time_idp']-$res[$i]['start_time_idp']) / 3600);
		$pat['motif_idp'] = (strlen($res[$i]['motif'])>0)?$res[$i]['motif']:"";
		$pat['idp'] = (strlen($pat['start_idp'])>0)?"Indispo":"";
		
		if($res[$i]['start_time']){
			if ($pat['idp']){	
				$pat['noip'] = 'idp';
				$data[$res[$i]['room_alias']][date('d/m/Y',$res[$i]['start_time_idp'])][]=$pat;
			}
			else
				$data[$res[$i]['room_alias']][date('d/m/Y',$res[$i]['start_time'])][]=$pat;
			}
			
		else{
				$data[$res[$i]['room_alias']][][]=$pat; // a corriger
		}
	}
	else{
		$pat['deb']=$res[$i]['start_time'];
		$pat['fin']=$res[$i]['end_time'];
		$pat['room_id'] = $res[$i]['id'];
		$data[$res[$i]['room_alias']][][]= $pat;
	}
}
/*	
	echo "<pre>";
	//print_r($res);
	print_r($data);
	exit;
*/
?>
