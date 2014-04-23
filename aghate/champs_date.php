<?php
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include ("./commun/include/ClassAghate.php");

$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";


$room_id= $_GET['room'];
$res = $Aghate->GetAllIdp($room_id);
echo "<table>";
echo "<tr><td>DU</td><td>AU</td><td>Motif</td></tr>";
for($i=0;$i<count($res);$i++)
{
	if (strlen($res[$i]['start_time_idp']) > 1)
	{
		echo "<tr><td>".date("d/m/y",$res[$i]['start_time_idp'])."</td><td>".date("d/m/y",$res[$i]['end_time_idp'])."</td><td>".date("d/m/y",$res[$i]['motif'])."</td></tr>";
	}
}

echo "<tr>
				<td>
           <input  style='width:80px' type='text' name='start_time_idp' value=\"".$row["start_time_idp"]."\" />
        </td>
        <td>
					<input style='width:80px' type='text' name='end_time_idp' value=\"".$row["end_time_idp"]."\" />
				</td>
				<td>
					<input type='text' name='motif' value=\"".$row["motif"]."\" />
				</td>
			</tr>
</table>	";		
?>
