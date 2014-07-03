<?Php  
/*
* PROJET AGHATE
* Module reservation
* 
* @Mohanraju SBIM/SAINT LOUIS/APHP /Paris
* 
* date dernière modififation 14/05/2014
* 
*/

include("./resume_session.php");
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
foreach($row as $key )
{
	if($key['room_name']=='Panier')
	{
		$default_room=$key['id'];
		$ListRoom[0]=$key['id']."|". $key['room_name'];
	}else
	{
		$ListRoom[]=$key['id']."|". $key['room_name'];
	}
}

$retval= $Html->InputSelect($ListRoom,'room_id',$default_room,"class='input-small'");
echo $retval
?>


