<?Php  
/*
* PROJET AGHATE
* Ajax get rooms d'in service
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
* 
*/
include "../../resume_session.php";
include "../../config/config.php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassHtml.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/CommonFonctions.php";

$CommonFonction = new CommonFunctions(true);
$mysql = new MySQL();
$Aghate = new Aghate();
$Html = new Html();
 
$row = $Aghate->GetAllRooms($service_id,true);
$ListRoom[0]="";
unset($data);
//premier pos pour Panier
$data[0]="Panier";
foreach($row as $key )
{
	unset($row);
	$row['id']	 =	$key['id'];
	$row['value']=	$key['room_name'];
	if($key['room_name']=="Panier")
		$data[0]=$row;		
	else
		$data[]=$row;		
}

//$retval= $Html->InputSelect($ListRoom,'room_id',$default_room,"class='input-small'");
echo json_encode($data);
?>