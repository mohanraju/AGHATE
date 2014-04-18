<?php
require("./config/config.php");
require("./commun/include/CommonFonctions.php");
require("./commun/include/ClassMysql.php");
require("./commun/include/ClassAghate.php");
include("./commun/include/ClassHtml.php");
include("../commun/layout/header.php");
// init les objets
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$html= new Html($db);

$sql="SELECT * from agt_room order by service_id,room_name"	;

$res=$Aghate->select($sql);
for($i=0; $i < count($res);$i++)
{
	$sql="update agt_room set order_display='".$i."' where id='".$res[$i]['id']."'";
	$Aghate->update($sql);
	echo "<br>".$sql;
	
}

?>
