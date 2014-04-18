<?php
/*
#########################################################################################
		ProjetMSI
		Module Resa
		Recherche Patient
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 23/01/2014
*/
//commun include pour les modules outil MSI
 
//-------------------------------------------------------------------------
// 		Vérifiction du site declared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
 
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------

include("../../aghate/config/config.php");
//include_once("../../aghate/commun/include/language.inc.php");
//include_once("../../aghate/commun/include/functions.inc.php");
require("../../commun/include/ClassMysql.php");
require("../../aghate/commun/include/ClassAghate.php");
require("../../aghate/commun/include/ClassReservation.php");
require("../include/CommonFonctions.php");

//Objet init
//$Functions = new CommonFunctions(false);
$aghate=new Aghate();
$reservation=new Reservation();
$com=new CommonFunctions();
// 	preparation de requettes
//	===================================================================

$Result=$aghate->GetInfoReservation ($entry_id);
$ResEnt=$aghate->GetInfoEntry ($entry_id);

$nbr_rec=count($Result);

echo '<div class="row-fluid">';
echo '<div class="span1 "></div>';
echo '<div class="span7"><form>';
echo '';
echo '<table width="700">';
if ($nbr_rec > 0){
	$start_date=date('d/m/Y H:i' ,$Result['start_time']);
	$end_date=date('d/m/Y H:i' ,$Result['end_time']);
	$duration=$Result['duration'];
	$reservation->toTimeString($duration, $dur_units);
	$ddn=$com->Mysql2Normal($Result['ddn']);
	/*list( $start_period, $start_date) =  period_date_string($Result['start_time']);
	$start_date = time_date_string($Result['start_time'],$dformat);
	list( , $end_date) =  period_date_string($Result['end_time'], -1);*/
	 
	echo '<tr>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>
						<!--input class="span10" id="NIP" type="text" value="'.$NIP.'"-->
					</td>
					<td></td>
					<td id="Patient">
						<b>'.$Result["nom"].' '.$Result["prenom"].' né(e) le '.$ddn.' ('.$Result["type"].') </b>('.$Result["noip"].')<br><br>
						'.$Result["service_name"].' - '.$Result["room_name"].' du '.$start_date.' au '.$end_date.'<br><br>
						<a href="./reservation.php?id='.$entry_id.'">Modifier cette réservation</a> <br>
					</td>
				</tr>
			</table>
		</div>';
				
	$start_date=date('d/m/Y H:i' ,$Result['start_time']);
	$end_date=date('d/m/Y H:i' ,$Result['end_time']);
	$duration=$Result['duration'];
	$reservation->toTimeString($duration, $dur_units);
	$ddn=$com->Mysql2Normal($Result['ddn']);
	
	$MyVar=$Result["nom"]." ".$Result["prenom"]." (".$Result["noip"].") (".$ddn.") (" .$Result["type"].") (tel:".$Result["tel"].")";
	$return="|_|".$MyVar."|".$Result['noip']."|".$Result['description']."|".$Result['service_id']."|".$Result['service_name']."|".$ResEnt['protocole']."|".$start_date."|".$duration."|".$dur_units;
	echo $return;
} 
else
{
	echo "<tr><td colspan=5>[".$entry_id."] Aucune reservation trouvé </td></tr></table></div>";	
}
?>
