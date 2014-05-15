<?Php  
/*
* PROJET AGHATE
* Ajax Save reservation
* 
* @Celeste Thierry SBIM/SAINT LOUIS/APHP /Paris
* 
* date dernière modififation 14/05/2014
* 
*/
include "../../resume_session.php";
header('Content-type: text/html; charset=utf-8'); 
include "../../config/config.php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/ClassGilda.php";
include "../../commun/include/CommonFonctions.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);
$Gilda= new Gilda($ConnexionStringGILDA);

if (strlen($table_loc)< 1){
    echo "|ERR|Erreur declaration Table LOC ";
	exit;
}else
	$Aghate->NomTableLoc =$table_loc;


//-----------------------------------------------------------------------------
//Check Session
//-----------------------------------------------------------------------------
if (strlen($login) < 0 ) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};



//----------------------------------------------------------------------------- 
// check patient present dans base local si non insert le
//-----------------------------------------------------------------------------
if (strlen($noip) > 0){
	$PatInfo=$Gilda->GetPatInfoByNip($noip);
	// err nip no patient trouvé
	if(count($PatInfo) < 1)
	{
		echo "|ERR| Invalide NIP =>$noip, Nip non trouvé dans la base Administrative";
		exit;
	}
	$PatInfo[0]['DANAIS']=$CommonFonctions->Normal2Mysql($PatInfo[0]['DTNAIS']); //attn pourrecumarer datenaisse au format dd/mm/YYYY
	$Aghate->InsertPatient ($PatInfo[0]); 

}else{
	echo "|ERR| NIP vide, Patient obligatoire ";
	exit;
}
 
 
//----------------------------------------------------------------------------- 
// check reservation present plus au moins 5 jours dans n'importe quel service
//-----------------------------------------------------------------------------
 
// A FAIRE 

//----------------------------------------------------------------------------- 
// check SEXE dans le cas HDJ 
//-----------------------------------------------------------------------------
 
// A FAIRE 
 
 
//----------------------------------------------------------------------------- 
// check medecin present dans la table agt_medecin
//-----------------------------------------------------------------------------
 
if (strlen($id_medecin) < 1)
{
	echo "|ERR| Invalide Medecin => $medecin, medecin non trouvé dans la base locale";
		exit;

}
//-----------------------------------------------------------------------------
// check service_id ou room_id
//-----------------------------------------------------------------------------
 
if ((strlen($id_service) < 1) && (strlen($room_id) < 1) && (intval($id_service) < 1 ) )
{
	echo "|ERR|Service ou Room obligaire !";
	exit;
}
$ServiceInfo=$Aghate->GetServiceInfoByServiceId ($id_service);

 
//-----------------------------------------------------------------------------
//si le room non declaré on force Coulour/Panier
if (strlen($room_id) < 1){
	$room_id=$Aghate->GetPanierIdByServiceId($id_service);
}
  
//-----------------------------------------------------------------
// vérify le droit de reservation 
//-----------------------------------------------------------------	

if($Aghate->GetUserLevel($_SESSION['login'],$room_id ) < 3){
	echo "|ERR|Access denied pour cette utilisateur!";
	exit;
}
 

//-----------------------------------------------------------------------------
// check start time et durée
//-----------------------------------------------------------------------------
list($dt,$time)=explode(" ",$start_time);
if((strlen($dt) != 10) || (strlen($time) != 5))
{
	echo "|ERR|Invalide start time($start_time), format JJ/MM/AAAA HH:mm attendu!";	
	exit;
}
list($day,$month,$year)=explode("/",$dt);
list($hour,$minute)=explode(":",$time);

//exit;
 
//-----------------------------------------------------------------------------
// check end time 
//-----------------------------------------------------------------------------
list($df,$timef)=explode(" ",$end_time);
if((strlen($df) != 10) || (strlen($timef) != 5))
{
	echo "|ERR|Invalide end time($end_time), format JJ/MM/AAAA HH:mm attendu!";	
	exit;
}
list($day_f,$month_f,$year_f)=explode("/",$df);
list($hour_f,$minute_f)=explode(":",$timef);

// les controls sur datetime a faire **************************

if (strlen($duree)< 1){
	echo "|ERR|Duree de convocation est inconnu!";
	exit;	
}

 

$starttime = mktime($hour, $minute, 0, $month, $day, $year);
$endtime   = mktime($hour_f, $minute_f, 0, $month_f, $day_f, $year_f);

 
/*if ($starttime < time()){
	echo "|ERR| Warning : La réservation est effectué avant la date courrante";
	exit;
}*/


if ($endtime <= $starttime)
{
	echo "|ERR|probléme de calcul start_time et end_time endtime <=  starttime ($endtime <=  $starttime)"	;
	exit;
}



//-----------------------------------------------------------------------------
// Check Hors reservation ou Service ferme 
//-----------------------------------------------------------------------------
$day_temp   = date("d",$starttime);
$month_temp = date("m",$starttime);
$year_temp  = date("Y",$starttime);
$starttime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
$day_temp   = date("d",$endtime);
$month_temp = date("m",$endtime);
$year_temp  = date("Y",$endtime);
$endtime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);

// A faire ree hosrs reservation 
 
//----------------------------------------------------------------------------- 
# Acquire mutex to lock out others trying to book the same slot(s).
//-----------------------------------------------------------------------------

// a fiare

//-----------------------------------------------------------------------------
//check droit d'utlisateur sur la room
//-----------------------------------------------------------------------------
$info_room = $Aghate->GetRoomInfoByRoomId($room_id);
$statut_room = $info_room['statut_room'];
// on vérifie qu\'un utilisateur non autorisé ne tente pas de réserver une ressource non disponible
if (($statut_room == "0") and authGetUserLevel(getUserName(),$room_id) < 3)
{
	echo "|ERR| Salle est indisponible ou l'utilisateur n'a pas le droit de réserver dans cette salle";
	exit;
}

//-----------------------------------------------------------------------------
// check room dispo ou non 
// sauf Panier et reservattion par Plages
//--------------------------------------------------------------------------

if (($room_name != 'Panier') && ($ServiceInfo[0]['enable_periods']!='y') )  {
 
	$tmp = $Aghate->CheckRoomDispo($room_id, $starttime, $endtime,"",$id );
 
	if (strlen($tmp) > 0)
	{
		echo "|ERR|Pas de place disopnible ";
		exit;
	}
}
// force end time pour les plages
if($ServiceInfo[0]['enable_periods']=='y')
{
	$endtime = $starttime + 30;
}

$id_service = $Aghate->GetServiceIdByRoomId($room_id);



// tableau RESERVATION info
$TableauData = array();
$TableauData['start_time'] 	= $starttime;
$TableauData['end_time'] 	= $endtime;
$TableauData['room_id'] 	= $room_id;
$TableauData['create_by'] 	= $_SESSION['login'];
$TableauData['type'] 		= $type;
$TableauData['noip'] 		= $noip;
$TableauData['plage_pos'] 	= $plage_pos;
$TableauData['medecin'] 	= $id_medecin;
$TableauData['uh'] 			= $uh;
$TableauData['ds_source'] = "Programme";
$TableauData['protocole'] = $protocole;
$TableauDataCd['id'] 			= $id;

// tableau PROGRAMATION info
$TableauProg['noip'] 			= $noip;
$TableauProg['start_time'] = $starttime;
$TableauProg['end_time'] 	= $endtime; 
$TableauProg['service_id'] 		= $id_service;	
$TableauProg['room_id'] 		= $room_id;
$TableauProg['medecin'] 		= $id_medecin;
$TableauProg['protocole'] 		= $protocole;
$TableauProg['create_by'] 		= $_SESSION['login'];
$TableauProgCd['id'] 			= $id_prog;

//PROGRAMATION 
if (strlen($id_prog) > 0 ){
	$id_prog = $Aghate->UpdateEntry('agt_prog',$TableauProg,$TableauProgCd);
}
else{	
	// Create the entry:
	// generate temp NDA uninquement pour les insertion
	$TempNda			=	$Aghate->GetLastTempNda(); 
	$TableauProg['nda'] = $TempNda;
	$TableauData['nda']	= $TempNda;
	$id_prog = $Aghate->CreateSingleEntry('agt_prog',$TableauProg);

}
$TableauData['id_prog'] = strlen($id_prog)>0?$id_prog:0;

// RESERVATION (LOC)
if(strlen($id)>0){

	 $Aghate->UpdateEntry($Aghate->NomTableLoc,$TableauData,$TableauDataCd);
	 if (strlen($_description) > 1)
		$Aghate->UpdateDescriptionFromId ($id,"DESC___COMPL",$_description,"DESC___COMPL");
	
}
else
{
	$TableauData['de_source'] = "Programme";				
	$TableauData['statut_entry'] = "Programmé";
	$id = $Aghate->CreateSingleEntry($Aghate->NomTableLoc,$TableauData);
	if (strlen($_description) > 1)
		$Aghate->UpdateDescriptionFromId ($id,"DESC___COMPL",$_description,"DESC___COMPL");
}

//----------------------------------------------------------------------------- 
# Acquire mutex un lock 
//-----------------------------------------------------------------------------
// a fiare   


echo "|OK|$id|"; 
?>
