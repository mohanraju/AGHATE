<?php
/*
//Sycronisation avec GILDA pour récuparèrer le NDA
*/
//require_once("./commun/include/admin.inc.php");
//require_once("./commun/include/functions.inc.php");
require_once("./commun/include/CustomSql.inc.php");
require_once("./config/connexion_gilda.php");
require_once("./commun/include/progressbar.php");

$db = new CustomSQL($DBName);
ini_set("display_errors","1");
$grr_script_name = "update_nda.php";
$back = '';

if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

// preparation progress bar
InitProgressBar(50,0,200,20,'#000000','#FF0000','#5098F1'); // initialisation de la barre de progression 			
ProgressBar("Preparation");	
ProgressBar("Syncronisation ");	
 

/*
#############################################################################################
#
#	functions  
#
#############################################################################################
#
*/
/*
#############################################################
#
#	GET NDA for the given patient nip+date_entreee+date_sortie
#
#############################################################
*/
function GetNda($NIP,$DateEntree,$DateSortie,$ConnGilda)
{
	$sql="SELECT distinct(dos.noda) as NDA,
							dos.DAENTR as DATE_ENTREE,
							dos.DASOR as DATE_SORTIE,
							NOUF as UH,
							TYDOS
			FROM DOS,MVT
			WHERE MVT.NODA = DOS.NODA
			AND dos.noip  in ('".trim($NIP)."') 
			AND tydos ='A' 
			AND MVT.TYMAJ='A'
			AND (
						(to_date('$DateEntree','DD/MM/YYYY') >= dos.DAENTR
							AND to_date('$DateEntree','DD/MM/YYYY') <= dos.DASOR+1)
						OR
						(to_date('$DateSortie','DD/MM/YYYY') >= dos.DAENTR
							AND to_date('$DateSortie','DD/MM/YYYY') <= dos.DASOR+1)
						OR(to_date('$DateEntree','DD/MM/YYYY') >= dos.DAENTR
							AND  dos.DASOR is null)
					)
			UNION
			SELECT dos.noda as NDA,
						 hjo.DAEXEC as DATE_ENTREE,
						 hjo.DAEXEC as DATE_SORTIE,
						 NOUFEX as UH,
						 TYDOS
			FROM DOS,HJO,MVT
			WHERE MVT.NODA = HJO.NODA
			AND dos.noip  in ('".trim($NIP)."') 
			AND tydos <> 'A'
			AND HJO.TYMAJ <> 'D'			
			AND dos.noda=hjo.noda		
			AND (
					(hjo.DAEXEC =to_date('$DateEntree','DD/MM/YYYY'))
					OR
					(hjo.DAEXEC =to_date('$DateSortie','DD/MM/YYYY'))
					)
			";

	//echo "<br />".$sql."<hr>";
	$Res=OraSelect($sql,$ConnGilda);
	return $Res;

}

/*
#############################################################
#
#	functions   END
#
#############################################################


#############################################################
#
#	GET all reservations par jour where NDA is vide or null
# partie affichage html
#
#############################################################
*/

// Recuaprations des variabeles envoyée par updata_nda.php
$period = isset($_GET["period"]) ? $_GET["period"] : NULL;
$month=isset($_GET['month']) ?$_GET['month']:date('m');
$day	=isset($_GET['day'])		?	$_GET['day']:date('d');
$year	=isset($_GET['year'])	?	$_GET['year']:date('Y');

//if(isset($_GET['area'])) $area = get_default_area();


//si aucun area(service) on select tous les services
if(isset($_GET['area'])< 1)
	$sql_area = " ";
else
	$sql_area = "AND service_id='".$_GET['area']."'";	


# Define the start and end of the day.
$am7=mktime(0,0,0,$month,$day,$year);
$pm7=mktime(23,59,0,$month,$day,$year);


// prepare requere pour la journée
$sql_nbr_pat = "SELECT agt_loc.id,room_id,noip, start_time, end_time, name
							   FROM agt_loc,agt_room 
							   WHERE start_time < $pm7
							   AND end_time > $am7 
							   and length(noip)=10
								 AND 	agt_room.id=room_id
								 $sql_area 		
							   ORDER BY service_id,start_time";

//echo $sql_nbr_pat ;
$res=$db->select($sql_nbr_pat);


// boucle sur les résultat 
$nbr_rows=count($res); 
echo "<div align='center'><h2>Vérification d'inscription des patients dans Gilda</h2></div>";
echo "<table border=1 width=500px align='center'><tr align='center'>
			<th>NIP</th>
			<th>Patient</th>
			<th>UH</th>
			<th>NDA</th>
			</tr>";

// variables compteur nombre ok ou ko
$countko=0;
$countok=0;			
for($i=0;$i < $nbr_rows;$i++)
{
	//Gestion progress bar
	$pourcentage=intval(($i * 100) / $nbr_rows);
	ProgressBar("syncronisation ".$pourcentage);	
	
	$nip=$res[$i]["noip"];
	list($patient,$niipp,$dt,$sex,$tel)=explode("(",$res[$i]["name"]);
	$date_entree = strftime("%d/%m/%Y",$res[$i]["start_time"]);
	$date_sortie = strftime("%d/%m/%Y",$res[$i]["end_time"]);
	$duree=($res[0]["end_time"] - $res[$i]["start_time"])  ;

	
	// suprime l'heure dans les dates
	//list($time,$date_entree)=explode('-',$date_entree );
	//list($time,$date_sortie)=explode('-',$date_sortie );
	
	// get  area 	
	$area_res = $db->select("select agt_service.service_name  
					from agt_service,agt_room
					 where agt_room.service_id=agt_service.id
					 and agt_room.id='".$res[$i]["room_id"]."'");	
					$area=$area_res[0]['service_name'];
	// get DNA
	$GildaRes=GetNda(trim($nip),trim($date_entree),trim($date_sortie),$ConnGilda);
  if (count($GildaRes) > 0)
  {
  	$nda=$GildaRes[0]['NDA'];
  	$uh=$GildaRes[0]['UH'];
 		$countok++; 	
 	}else{
 		$nda="Null";
 		$uh="Null"; 		
 		$countko++; 	
 	}
	$sql_update="update agt_loc set nda=".$nda." where id=".$res[$i]["id"];
	$db->update($sql_update);

  echo "<tr><td>".$nip."</td>
            <td>".$patient."</td>  
            <td>".str_replace("Null","&nbsp;",$uh)."</td>
            <td>".str_replace("Null","&nbsp;",$nda)."</td>
        </tr>";
            
}
echo "</table>";
echo "<div align='center'>"; 
echo "Dans ce tableau il y a $nbr_rows Patient(s)" ;
if($countko >0)
	echo "<br /><b>Dans laquelle $countko patients sont pas inscrit dans gilda !<b>";
elseif(($countko==$countko) and 	($countko >0) )
	echo "<br /><b>Tous les patients sont enregistrés dans Gilda !<b>";
echo "<div>"; 

//close progress bar
ProgressBar(100);		
ProgressBarHide();	

/*
<script language="JavaScript">

window.opener.location.href = window.opener.location.href;
window.opener.location.reload(true);
</script>
*/
?>
