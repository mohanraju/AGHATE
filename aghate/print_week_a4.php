<?php
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/CommonFonctions.php";
header('Content-Type: text/html; charset=UTF-8');
#Paramètres de connection
require_once("./commun/include/settings.inc.php");

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$com=new CommonFunctions(true);
 

 
// count nombre de patient dans le journée
$compare_to_end_time = $am7;
$compare_to_start_time = $pm7;


$service=$Aghate->GetServiceInfoByServiceId($area);
$res = $Aghate->GetWeekInfo($area,$am7,$pm7);
 
$nb_res = count($res);
$nbr_res=count($res);

echo '<A HREF="javascript:self.print()"><IMG SRC="./commun/images/print.gif" BORDER="0"  title="Print this page"></A>';
echo "<h3 align='center'>".$service[0]['service_name']."</h3>";
echo "<h4 align='center'> Patients admis ou programmé du ".date("d/m/Y" ,$am7)." au ".date("d/m/Y" ,$pm7)." </h4>";
echo "<table align='center' border='1' width='800px'>
		<tbody>
			<tr>
				<th>Lit</th> 
				<th>Patient</th>
				<th>Séjour</th>
				<th>Medecin</th>
			</tr>
		</tbody>
	";
for($i=0; $i < $nbr_res; $i++)
{
	$res[$i]['nda'] =(strlen($res[$i]['nda'])< 1)?"<b>Programmé</b>":$res[$i]['nda'];
	// medecin + specialité
	$info_med	=$Aghate->GetInfoMedecinById($res[$i]['medecin']);
	$id_medecin = $res['medecin'];
	$medecin	=$info_med['nom']." ".$info_med['prenom'];
	$specialite	=$info_med['specialite'];
	if(strlen(trim($specialite)) < 1)
		$specialite="Non declaré";
	if(strlen($res[$i]['noip'])>3)
		$patient="NIP : ".$res[$i]['noip']."<br>".$res[$i]['nom']." ".$res[$i]['prenom']."<br>Ne(é) ".$com->Mysql2Normal($res[$i]['ddn'])." (".$res[$i]['sex'].")";
	else
		$patient=$res[$i]['patient'];
		
	echo "<tr>
			<td>".$res[$i]['room_name']."</td>
			<td>".$patient."</td>
			<td> NDA : ".$res[$i]['nda']."<br> du:".date("d/m/Y à H:i",$res[$i]['start_time'])."<br> au:".date("d/m/Y à H:i",$res[$i]['end_time'])."</td>
			<td>
				Dr. ".	$medecin."<br>
    			Specialité:" .$specialite."<br>
    			Protocole:".$res[$i]['protocole']."
			</td>
		</tr>";
}

echo "</table>";


 




