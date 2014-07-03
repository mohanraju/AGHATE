<?php
#########################################################################
#                         day.php                                       #
#                                                                       #
#    Permet l'affichage de la page d'accueil lorsque l'on est en mode   #
#    d'affichage "jour".                                                #
#                                                                       #
#                  Dernière modification : 20/03/2008                   #
#                                                                       #
#########################################################################
/*
*/

$deb = time();

include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassHtml.php";
include "./commun/include/CommonFonctions.php";


$Mysql  = new MySQL();
$Aghate = new Aghate();
$Html   = new Html();

include "./commun/include/AghateFunctions.php";
include "./resume_session.php";
include "./commun/include/language.inc.php";

 

$ServiceInfo= $Aghate->GetServiceInfoByServiceId($area);
 

// initialisation Table LOC
////----------------------------------------------------------------------
if($ServiceInfo[0]['enable_periodes']=='y')
	$TableLoc='agt_ipop';
else
	$TableLoc='agt_loc';

$TableLoc='agt_loc';
$Aghate->NomTableLoc=$TableLoc;

// Check session
if (strlen($_SESSION['login'])< 1) {
    header("Location: ./logout.php?auto=1");
    die();
};

// model d'affichage CORO
if($ServiceInfo[0]['enable_periods']=='y')
{
 
	include "./day_cons.php";	
	exit;
}
 


//Si nous ne savons pas la date, nous devons la créer
//----------------------------------------------------------------------
$date_now = time();
if (!isset($day) or !isset($month) or !isset($year))
{
    $date_ = $date_now;
    $day   = date("d",$date_);
    $month = date("m",$date_);
    $year  = date("Y",$date_);
} else
{
    // Vérification des dates
    settype($month,"integer");
    settype($day,"integer");
    settype($year,"integer");
    $minyear = strftime("%Y", getSettingValue("begin_bookings"));
    $maxyear = strftime("%Y", getSettingValue("end_bookings"));
    if ($day < 1) $day = 1;
    if ($day > 31) $day = 31;
    if ($month < 1) $month = 1;
    if ($month > 12) $month = 12;
    if ($year < $minyear) $year = $minyear;
    if ($year > $maxyear) $year = $maxyear;

    #Si la date n'est pas valide, ils faut la modifier (Si le nombre de jours est suppérieur au nombre de jours dans un mois)
    while (!checkdate($month, $day, $year))
        $day--;
}

// Check Premier connexion 
//----------------------------------------------------------------------
$UserInfo=$Aghate->GetUserInfo ($_SESSION['login']);
if(count($Aghate->GetServiceInfoByServiceId($UserInfo[0]['default_area'])) < 1 )
{
	echo "<br><br><br><br><div style=\"text-align : center; font-size : 15px \">
			
			Veuillez declarer le <b>Service par défaut</b>  
			si cela votre premier connexion veuillez contacter votre cadre ou support infromatique </br>
			pour activer votre compte, sinon votre compte informations sont  <a href='my_account.php'>  ICI</a> </div>";
	exit;
	
}

//====================================================================
//Premier fois demande de syncroniser sur Gilda
//====================================================================
$res=$Aghate->GetAllArea();
if (count($res) < 1)
{
		echo "<h1 align = 'center' > Premier utilisation Aghate  </h1>";
		echo "<h2 align = 'center' > Une synchronisation est nécessaire avec la structure GILDA  </h2>";
		echo "<H1 style=\"text-align : center;  \"  ><a href='update_structure.php'> Synchroniser maintenant </a> </H1>";
		echo "<br><br>";
		include "./commun/include/trailer.inc.php";
		exit;
	
}

// inclide page header et menus 
//----------------------------------------------------------------------
include("header.php");


$session_login = $_SESSION['login'];
$session_statut = $_SESSION['statut'];
$type_session = "with_session";




// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);//------------------------------------------------------------------------<<<<<<<

// Si aucun service par default n'est défini
//----------------------------------------------------------------------
if ($area == 0) {
	echo "<br><br><br><br><div style=\"text-align : center; font-size : 15px \">
			Veuillez declarer le service par défaut</br>   <a href='my_account.php'>  Compte Information</a> </div>";
	exit;
}

// check droit d'acces sur la page
//----------------------------------------------------------------------
$level=$Aghate->GetUserLevel($_SESSION['login'],$area,'area') ;
if($level < 1 )
{
	echo "<br>".$level ." > 1 prob a voir";
	showAccessDenied($day, $month, $year, $area,$back);
	exit;
}

?>
<script type="text/javascript" src="./commun/js/functions.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-1.10.2.js" charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/jquery-ui-1.10.4.custom.js"  charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/info_bulle.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="./commun/js/jquery.contextMenu.js"></script>

<link href="./commun/style/smoothness/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css" media="all"  charset="utf-8"/>
<link href="./commun/style/day.css" rel="stylesheet" type="text/css" media="all" />
<link href="./commun/style/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
<script type='text/javascript'>


function OpenPopup(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=750,height=250,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
function OpenPopupResa(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=850,height=670,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}


//======================================================================
//		Ducuement ready funtions
//======================================================================

jQuery(function($){
	// SET REGIONAL SETTINGS to FR
   $.datepicker.regional['fr'] = {
	  closeText: 'Fermer',
	  prevText: 'Préc',
	  nextText: 'Suiv',
	  currentText: 'Courant',
	  monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
	  'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
	  monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
	  'Jul','Aoû','Sep','Oct','Nov','Déc'],
	  dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
	  dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
	  dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
	  weekHeader: 'Sm',
	  //dateFormat: 'dd/mm/yy',
				dateFormat: 'dd/mm/yy',
	  firstDay: 1,
	  isRTL: false,
	  showMonthAfterYear: false,
	  yearSuffix: ''};
   $.datepicker.setDefaults($.datepicker.regional['fr']);

	//----------------------------------------------------------------------
	//			Jquery Calandar en 3 mois
	//----------------------------------------------------------------------	
	$('#cur_date').datepicker({
		showWeek : true,
		showOn: "button",
		buttonImage: "./commun/images/calendar.gif",
 		buttonImageOnly: true,		
		numberOfMonths: 1, 
		showCurrentAtPos: 0,
		changeMonth: true,
		changeYear: true,	
		showAnim: "drop",		
		dateFormat: 'dd/mm/yy',
		onSelect: function(dateText, inst) {
        dt= dateText.split("/");
					var url ="./day.php?year="+dt[2]+"&month="+dt[1]+"&day="+dt[0]+"&area="+<?php print $area?>;
          location.href=url
      }			
	});			


	//----------------------------------------------------------------------
	//onclick de semaine => envoyer vers week_all
	//----------------------------------------------------------------------
	$("td.ui-datepicker-week-col").live("click", function() {         
		$(this).next().click();
		var cpt = 0;
		var bool = true;
		var currentDay;
		var currentMonth ;
		var currentYear  ;
		$(this).parent().children().each(function(){
			if(cpt>=1 && bool==true){
				if($(this).children().length > 0){
					currentDay	 = ($(this).children()[0].innerHTML);
					currentMonth = parseInt($(this).attr("data-month"))+1;
					currentYear  = 	parseInt($(this).attr("data-year"));
					bool=false;
				}
			}
			cpt++;
		});
		var fromDate = $("#cur_date").datepicker("getDate");
		var url ="./week_all.php?year="+currentYear+"&month="+currentMonth+"&day="+currentDay+"&area="+<?php print $area?>;
		location.href=url       
	 });
 }); // fin doc ready
</script>

 
<?php

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {



echo "<table border='1' width=\"100%\" cellspacing=2><tr>\n<td width='20%'>";
$cur_date=date( "d/m/Y", strtotime("$year-$month-$day" )); // pour concateiner les zeros


if (isset($_SESSION['default_list_type']) or (getSettingValue("authentification_obli")==1)) {
    $area_list_format = $_SESSION['default_list_type'];
} else {
    $area_list_format = getSettingValue("area_list_format");
}

 

#Show all avaliable areas
# need to show either a select box or a normal html list,
if ($area_list_format != "list") {
  echo make_area_select_html('day.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
} else {
	echo "\n<table cellspacing=15><tr><td>\n";
  echo make_area_list_html('day.php', $area, $year, $month, $day, $session_login); # from functions.inc.php
	echo "</td><td>";
	make_room_list_html('week.php', $area, "", $year, $month, $day);
	echo "</td></tr>";
	echo "</table>";
}
echo "</td>\n";


}

echo "<td>"; // ouverture pour afficher le date et nombre de pats
#y? are year, month and day of yesterday
#t? are year, month and day of tomorrow
$ind = 1;
$test = 0;
while (($test == 0) and ($ind < 7)) {
    $i= mktime(0,0,0,$month,$day-$ind,$year);
    $test =$display_day[date("w",$i)];
    $ind++;
}
$yy = date("Y",$i);
$ym = date("m",$i);
$yd = date("d",$i);

$i= mktime(0,0,0,$month,$day,$year);
$jour_cycle = $Aghate->GetJour($i);
$ind = 1;
$test = 0;
while (($test == 0) and ($ind < 7)) {
    $i= mktime(0,0,0,$month,$day+$ind,$year);
    $test =$display_day[date("w",$i)];
    $ind++;
}
$ty = date("Y",$i);
$tm = date("m",$i);
$td = date("d",$i);

# Define the start and end of the day.
$am7=mktime($morningstarts,0,0,$month,$day,$year);
$pm7=mktime($eveningends,$eveningends_minutes,0,$month,$day,$year);


// Ligne modifier pour effectué qu'une seule requête avec classAghate
// row_name
$res_area = $Aghate->GetServiceInfoByServiceId($area);
$this_service_name = $res_area[0]['service_name'];


#Show current date
echo "<h2 align=center>";
echo '  <input type="hidden"  id="cur_date" name="cur_date"  value="'.$cur_date.'" title="change dates" /> ';
echo ucfirst(utf8_strftime($dformat, $am7)) . " - ";

$c_jour=ucfirst(utf8_strftime($dformat, $am7));
$for_print=ucfirst(utf8_strftime($dformat, $am7))."|";

if (getSettingValue("jours_cycles_actif") == "Oui" and intval($jour_cycle)>-1)
    if (intval($jour_cycle)>0)
	      echo  get_vocab("rep_type_6")." ".$jour_cycle."<br />";
	  else
	      echo  $jour_cycle."<br />";
echo ucfirst($_this_service_name);//." - ";//.get_vocab("all_areas");

 

// count nombre de patient dans le journée
$compare_to_end_time = $am7;
$compare_to_start_time = $pm7 + $resolution;


$res_nbr_pat =$Aghate->GetNbPatDay($area,$compare_to_start_time,$compare_to_end_time);
$nbr_pats=count($res_nbr_pat); 
for ($i=0;$i<$nbr_pats;$i++)
{
	if ($res_nbr_pat[$i]['room_name']=='Panier')
	{$cpt_coul++;}
	else
	{	$cpt_room++;}
}

if ($cpt_coul==0)
	echo $nbr_pats." patient(s) </h2>\n";
else
	echo $nbr_pats." patient(s) dont ".$cpt_coul." panier(s)";
	
//Possibilité d'ajouter d'un reservation dans le panier	du service
echo  "</h2>\n";	
//------------------------------------------------------------
// ajote additional description journalier à affichier ICI
// ajouté par mohan le 21/02/2011
//------------------------------------------------------------
$c_date=$year."-".$month."-".$day;
$sql_="select agt_medecin.titre,agt_medecin.nom,details,service_fermee  FROM agt_loc_parametres
			LEFT JOIN agt_medecin ON agt_medecin.id_medecin = agt_loc_parametres.medecin_id
			where date ='$c_date' and agt_loc_parametres.service_id='".protect_data_sql($area)."'";

	$res_ = $Mysql->select($sql_);
	$msg="";
	if((strlen($res_[0]['nom']) > 1)){
		$msg="Medecin : ".$res_[0]['titre']." " .$res_[0]['nom'] ." , ";
		}

	if ($row[3]=='1'){
		$msg.=" SERVICE FERMEE ";		
		}
		
	
	$msg.=nl2br($res_[0]['details']);
	if (strlen($msg)< 3)$msg="<img src='./commun/images/post_it.jpg' border='0' height='20'>";
if ((authGetUserLevel($_SESSION['login'],$room_id) >= 2))
{
	$link="<H2 align='center'><a href='#'  onClick=\"OpenPopup('additional_info_journalier.php?date=$c_date&service_id=".$area."')\" >".$msg."</a></H2>";
}else{
	$link="<H2 align='center'> ".$msg." </H2>";
}	
echo $link;


echo "</td>";
echo "<td width='20%'>&nbsp;</td>";
echo "</tr></table>";
// fin de la condition "Si format imprimable"


$res = $Aghate->GetInfoAffichage($area,$compare_to_start_time,$compare_to_end_time);
$nb_res = count($res);
$cpt = 0;
$lien = "";

 
 
if (authGetUserLevel($_SESSION['login'],$room_id) >= 2)
{
	$UrlEdit=$ModuleReservationView."?area=".$area."&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day&table_loc=".$TableLoc."&type_reservation=Programmation";
	$LinkResa= "<a href=\"#?\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" onClick=\"OpenPopupResa('".$UrlEdit."')\"><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" class=\"print_image\" /></a>";

	$TableProg = 'agt_prog';
	$UrlEditDemande=$ModuleReservationView."?area=".$area."&amp;year=$year&amp;month=$month&amp;day=$day&amp;page=day&table_loc=".$TableProg."&type_reservation=Demande";
	$LinkResaDemande= "<a href=\"#?\" title=\"".get_vocab("cliquez_pour_effectuer_une_reservation")."\" onClick=\"OpenPopupResa('".$UrlEditDemande."')\"><img src=\"./commun/images/new.png\" border=\"0\" alt=\"".get_vocab("add")."\" class=\"print_image\" /></a>";
	
}else{
	$LinkResa="";
	$LinkResaDemande="";	
}


//===============================================
// panier
//===============================================
echo "<table style='float:left' id='TablePanier' cellspacing='0' cellpadding='0' border='1'>";
echo "<tr><th colspan='5'>Couloir / Panier ".$LinkResa."</th>		</tr>";	
echo "<tr>
			<th>Nip</th>
			<th>Nda</th>			
			<th>Patient</th>
			<th>Sejour</th>			
			<th>Statut</th>
		</tr>";		
for($i=0;$i< $nb_res;$i++)
{
	if ( $res[$i]['room_name']=='Panier' )
	{
		$UrlView=$ModuleReservationView."?id=".$res[$i]['entry_id']."&table_loc=".$TableLoc;

		$title=($Aghate->GetDescComplementaire($res[$i]['description']));

		$lien = "<a href='#?'  onClick=\"OpenPopupResa('".$UrlView."')\"   title=\"".$title."\" >";
		$imgag_link="";
		if ($res[$i]['sex']=="M")
			$imgag_link="<img src='./commun/images/homme.png' width='20' height='20' border='0'>";
		if ($res[$i]['sex']=="F")
			$imgag_link="<img src='./commun/images/femme.png' width='20' height='20' border='0'>";
		
		echo "<tr>
						<td>".$res[$i]['noip']."</td>
					<td>".$res[$i]['nda']."</td>						
					<td>"	.$lien
								.$imgag_link. " "
								.$res[$i]['nom']." "
								.$res[$i]['prenom']
								." Né(e) le ".date('d/m/Y',strtotime($res[$i]['ddn']))
								."(".$res[$i]['sex'].")</a>
					</td>
					<td> du ".date('d/m/Y H:i',$res[$i]['start_time'])." au ".date('d/m/Y H:i',$res[$i]['end_time'])."</td>
					<td>".$res[$i]['statut_entry'].",  ".str_replace("TRACE_AUTOMATE","",$title)."</td>
				  </tr>";	
	}

}
	echo '</table>';

//===============================================
// Liste demande
//===============================================

$Aghate->NomTableLoc = 'agt_prog';
/*
$demande = $Aghate->GetInfoDemande($area,$compare_to_start_time,$compare_to_end_time);
$nb_demande = count($demande);
echo "<table style='float:right' id='TablePanier' cellspacing='0' cellpadding='0' border='1'>";
echo "<tr><th colspan='6'>Demande réservation ".$LinkResaDemande."</th>		</tr>";	
echo "<tr>
			<th>Nip</th>
			<th>Patient</th>
			<th>Sejour</th>			
			<th>Statut</th>
			<th>Demandeur</th>
			<th>Date demande</th>
		</tr>";		
for($i=0;$i< $nb_demande;$i++)
{
	
	//$title=($Aghate->GetDescComplementaire($res[$i]['description']));
	$UrlView=$ModuleReservationView."?id=".$demande[$i]['entry_id']."&table_loc=".$Aghate->NomTableLoc."&type_reservation=Demande";
	$lien = "<a href='#?'  onClick=\"OpenPopupResa('".$UrlView."')\"   title=\"".$title."\" >";
	$imgag_link="";
	if ($demande[$i]['sex']=="M")
		$imgag_link="<img src='./commun/images/homme.png' width='20' height='20' border='0'>";
	if ($demande[$i]['sex']=="F")
		$imgag_link="<img src='./commun/images/femme.png' width='20' height='20' border='0'>";
	
	echo "<tr>
					<td>".$demande[$i]['noip']."</td>
				<td>"	.$lien
							.$imgag_link. " "
							.$demande[$i]['nom']." "
							.$demande[$i]['prenom']
							." Né(e) le ".date('d/m/Y',strtotime($demande[$i]['ddn']))
							."(".$demande[$i]['sex'].")</a>
				</td>
				<td> du ".date('d/m/Y H:i',$demande[$i]['start_time'])." au ".date('d/m/Y H:i',$demande[$i]['end_time'])."</td>
				<td>".$demande[$i]['statut_entry']."</td>
				<td>".$demande[$i]['create_by']."</td>
				<td>".$demande[$i]['timestamp']."</td>
			  </tr>";

}
	echo '</table>';

*/
	$Aghate->NomTableLoc = $TableLoc;
if ($cpt!=0)
	echo "Réservation programmées (lit à attribuer) :".$lien;
echo "<br />";
$row=$res;

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
 
    #Show Go to day before and after links
    echo "<table width=\"100%\"><tr>\n<td>\n<a href=\"day.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area\">&lt;&lt; ".get_vocab('daybefore')."</a></td>\n<td align=right><a href=\"day.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area\">".get_vocab('dayafter')." &gt;&gt;</a></td>\n</tr></table>\n";

for ($r=0; $r<$nb_res;$r++) {

    $start_t = max(round_t_down($row[$r]['start_time'], $resolution, $am7), $am7);
    $end_t = min(round_t_up($row[$r]['end_time'], $resolution, $am7) - $resolution, $pm7);

 		
	
    // Calcul du nombre de créneaux qu'occupe la réservation
    $cellules[$row[$r]['entry_id']]= array();
    $cellules[$row[$r]['entry_id']]['value']=($end_t-$start_t)/$resolution+1;
    
    
    /* RETARD
     * if($row[$r]['de_source']=="Programme")
    {
		$retard = $Aghate->EstEnRetard($row[$r]['start_time'],$row[$r]['end_time'],$px_config);
		if ($retard)
			$cellules[$row[$r]['entry_id']]['retard'] = $retard ;
		else
		{}
			// pas de retard signalé encore
	}
    else
		//rien => car c'est gilda qui a inséré dans l'autre cas
    */
    /*
    echo "<pre>";
	print_r($cellules);
	* */
    // Initialisation du compteur
    $compteur[$row[$r]['entry_id']]=0;

    for ($t = $start_t; $t <= $end_t; $t += $resolution)
    {
        $today[$row[$r]['room_id']][$t]["id"]    = $row[$r]['entry_id'];
        $today[$row[$r]['room_id']][$t]["color"] = $row[$r]['type'];
        $today[$row[$r]['room_id']][$t]["data"]  = "";
        $today[$row[$r]['room_id']][$t]["who"] = "";
        $today[$row[$r]['room_id']][$t]["statut"] = $row[$r]['statut_entry'];
        // Construction des infos à afficher sur le planning
        $today[$row[$r]['room_id']][$t]["description"] = affichage_resa_planning($row[$r]['description'],$row[$r]['entry_id']); 
        $today[$row[$r]['room_id']][$t]["nda"] = $row[$r]['nda'];
        $today[$row[$r]['room_id']][$t]["noip"] = $row[$r]['noip']; 
        $today[$row[$r]['room_id']][$t]["nom"] = $row[$r]['nom'];
        $today[$row[$r]['room_id']][$t]["prenom"] = $row[$r]['prenom'];
        $today[$row[$r]['room_id']][$t]["ddn"] = $row[$r]['ddn'];
        $today[$row[$r]['room_id']][$t]["sexe"] = $row[$r]['sex'];
        $today[$row[$r]['room_id']][$t]["start_time"] = $row[$r]['start_time'];                                
		  $today[$row[$r]['room_id']][$t]["duree"] = ($row[$r]['end_time']* 1) - ($row[$r]['start_time'] *1);                
		  $today[$row[$r]['room_id']][$t]["heure"] =time_date_string($row[$r]['start_time'],""); 
		 
    }

    # Show the name of the booker in the first segment that the booking
    # happens in, or at the start of the day if it started before today.
    $PatInfo="NIP:".$row[$r]['noip']."\n".$row[$r]['nom']."\n".$row[$r]['prenom']."\n".date('d/m/Y',strtotime($row[$r]['ddn']))."\n (".$row[$r]['sex'].")\n
    					Tel:".$row[$r]['tel']."\nNDA:".$row[$r]['nda']."\nUH:".$row[$r]['uh']."\n
    					Medecin:".	$medecin=$Aghate->GetMedecinById($row[$r]['medecin'])."\n
    					Specialité:".	$medecin=$Aghate->GetspecialiteByMedecin($row[$r]['medecin'])."\n
    					Protocole:".$row[$r]['protocole'];
    if ($row[$r]['start_time'] < $am7) 
    {
    	$today[$row[$r]['room_id']][$am7]["data"].=  $PatInfo;
			//$today[$row[$r]['room_id']][$am7]["data"].= $row[$r]['protocole']." \n";
      // Info-bulle
      if (getSettingValue("display_info_bulle") == 1)
				$today[$row['room_id']][$am7]["who"] = get_vocab("reservation au nom de").affiche_nom_prenom_email($row[6],$row[11],"nomail");
      else if (getSettingValue("display_info_bulle") == 2)
      	$today[$row[$r]['room_id']][$am7]["who"] = $row[$r]['description'];
      else
      	$today[$row[$r]['room_id']][$am7]["who"] = "";
    } else {
    	$today[$row[$r]['room_id']][$start_t]["data"].=  $PatInfo;
        // Info-bulle
        if (getSettingValue("display_info_bulle") == 1)
           $today[$row[$r]['room_id']][$start_t]["who"] = get_vocab("reservation au nom de").affiche_nom_prenom_email($row[$r][6],$row[$r][11]);
        else if (getSettingValue("display_info_bulle") == 2)
            $today[$row[$r]['room_id']][$start_t]["who"] = $row[$r]['description'];
        else
            $today[$row[$r]['room_id']][$start_t]["who"] = "";
    }
}
# We need to know what all the rooms area called, so we can show them all
# pull the data from the db and store it. Convienently we can print the room
# headings and capacities at the same time

$res = $Aghate->GetRoomsByServiceId($area);
$nb_info = count($res);
$row = $res;
# It might be that there are no rooms defined for this area.
# If there are none then show an error and dont bother doing anything
# else

if ($nb_info == 0)
{
	$areas = $Aghate->GetAllArea();
	$nb_area = count($areas);
	if($nb_area==0){
		echo "<h1 align = 'center'>".get_vocab('no_rooms_for_area')."</h1>";
		echo "<h2 align = 'center' > Une synchronisation est nécessaire avec la structure GILDA  </h2>";
		echo "<div style=\"text-align : center; font-size : 15px \"  ><a href='update_structure.php'> Synchroniser maintenant </a> </div>";
		echo "<br><br>";
	}
	else{
		echo "<div style=\"text-align : center; font-size : 15px \"  ><a href='my_account.php'> Modifier le service par defaut </a> </div>";
	}
}
else
{
    echo "<table cellspacing=0 border=1 width=\"100%\">";

    // Première ligne du tableau
    echo "<tr>\n<th width=\"5%\">&nbsp;</th>";
    
    $start_day = mktime(0,0,0,$month,$day,$year);
	
    $room_column_width = (int)(90 / $nb_info);
    $nbcol = 0;
    // -1 pour ne pas afficher le panier
    for ($i = 0; $i<$nb_info; $i++)
    {
    	$temp_rooms[$row[$i]['id']]=$row[$i]['room_name'];
    	$room_alias[$i] = $row[$i]['room_alias'];
        $room_name[$i] = $row[$i]['room_name'];
        $id_room[$i] =  $row[$i]['id'];
        $statut_room[$id_room[$i]] =  $row[$i]['statut_room'];
        $statut_moderate[$id_room[$i]] =  $row[$i]['moderate'];
        if (strlen($row[$i]['picture_room']) > 4) 
        	$picture_room="images/".$row[$i]['picture_room'];
        else{
					$picture_room="./commun/images/editor.png";    }
        $nbcol++;

            $temp="";
        // Enlever commentaire ici si problème
        if ($row[$i]['room_name'] != 'Panier')
        {
			echo "<th width=\"$room_column_width%\"";
			// Si la ressource est temporairement indisponible, on le signale
			echo ">" . htmlspecialchars($row[$i]['room_alias'])."\n";
		}
		else {}
        if (htmlspecialchars($row[$i]['description']. $temp != '')) {
            if (htmlspecialchars($row[$i]['description'] != '')) $saut = "<br />"; else $saut = "";
            echo $saut."<i><span class =\"small\">". htmlspecialchars($row[$i]['description']) . $temp."\n</span></i>";
        }
        echo "<br />";
        //=======================================================================================
        // verif_display_fiche_ressource controle les droits pour l'accès a la visualisation de la fiche d'une ressource
        // affichage image entête du lit
        //=======================================================================================
        if (verif_display_fiche_ressource(getUserName(), $id_room[$i]) and $_GET['pview'] != 1)
        {
            echo "<A href='javascript:centrerpopup(\"view_room.php?id_room=$id_room[$i]\",600,480,
            \"scrollbars=yes,statusbar=no,resizable=yes\")' title=\"".get_vocab("fiche_ressource")."\">
            <img src=\"./commun/images/details.png\" alt=\"d&eacute;tails\" border=\"0\" class=\"print_image\"  /></a>";
        }
        if (authGetUserLevel(getUserName(),$id_room[$i]) > 2 and $_GET['pview'] != 1)
        {
            echo "<a href='admin_edit_room.php?room=$id_room[$i]'><img src=\"$picture_room\" alt=\"configuration\" 
            border=\"0\" title=\"".get_vocab("Configurer la ressource")."\" width=\"30\" height=\"30\" class=\"print_image\"  /></a>";
        }
        echo "</th>";
        $rooms[] = $row[$i]['id'];
    }
    echo "<th width=\"5%\">&nbsp;</th></tr>\n";

    // Deuxième ligne et lignes suivantes du tableau
    echo "<tr>\n";
    tdcell("cell_hours");
    if ($enable_periods == 'y')
        echo get_vocab('period');
    else
        echo get_vocab('time');
    echo "</td>\n";
    //+1 pour eviter de sauter une cellule
    for ($i = 0; $i < $nbcol+1; $i++)
    {
		// ICI AFFICHAGE SEMAINE ET MOIS
        // Si la ressource est temporairement indisponible, on le signale, sinon, couleur normale
        if ($Aghate->CheckIndispo($id_room[$i],$start_day)) tdcell("avertissement"); else tdcell("cell_hours");
        echo "</td>\n";
    }
    echo "</td>\n</tr>\n";
    // Début première boucle sur le temps
    for ($t = $am7; $t <= $pm7; $t += $resolution)
    {
        # Show the time linked to the URL for highlighting that time
        echo "<tr>\n";
        tdcell("cell_hours");
        if( $enable_periods == 'y' ){
            $time_t = date("i", $t);
            $time_t_stripped = preg_replace( "/^0/", "", $time_t );
            echo $periods_name[$time_t_stripped] . "</td>\n";
                    } else {
            echo date(hour_min_format(),$t) . "</td>\n";
                    }
        // Début Deuxième boucle sur la liste des ressources du domaine
        while (list($key, $room) = each($rooms))
        {

            if(isset($today[$room][$t]["id"])) // il y a une réservation sur le créneau
            {
                $id    = $today[$room][$t]["id"];
                $color = $today[$room][$t]["color"];
                $descr = htmlspecialchars($today[$room][$t]["data"]);
            }
            else
                unset($id);  // $id non défini signifie donc qu'il n'y a pas de résa sur le créneau

            // Définition des couleurs de fond de cellule
            if  ((isset($id)))   // 1er cas : il y a une réservation sur le créneau
            {
                $c = $color;
            } 
            else if ($Aghate->CheckIndispo($room,$start_day)) // 2ème cas : ou bien la ressource est temporairement indisponible
                $c = "avertissement"; // on le signale par une couleur spécifique
            else  // 3ème cas : sinon, il s'agit d'un créneau libre
                $c = "empty_cell";

            // S'il s'agit d'un créneau avec une resa :
            // s'il s'agit du premier passage ($compteur[$id]=0), on fait un tdcell_rowspan
            // Sinon, pas de <td>
            if  ((isset($id)))// and (!est_hors_reservation(mktime(0,0,0,$month,$day,$year)))) 
            {
                if( $compteur[$id] == 0 ) {
                    // Y-a-il chevauchement de deux blocs dans le cas où la hauteur du bloc est supérieure à 1 ?
                    if ($cellules[$id]['value'] != 1) {
                       // Dans ce cas, on s'intéresse à la dernière ligne du bloc
                       if(isset($today[$room][$t+($cellules[$id]['value']-1)*$resolution]["id"])) {
                         // Il y a chevaussement seulement si l'id correspondant est différent de l'id actuel
                         $id_derniere_ligne_du_bloc = $today[$room][$t+($cellules[$id]['value']-1)*$resolution]["id"];
                         // Dan ce cas, on réduit la taille du bloc pour éviter le chevaussement
                         if ($id_derniere_ligne_du_bloc != $id) $cellules[$id]['value'] = $cellules[$id]['value']-1;
                       }
                    }
                   
                    tdcell_rowspan ($c, $cellules[$id]['value']);
                    if ($cellules[$id]['retard'])
						echo "<div id='retard' style='height:".$cellules[$id]['retard']."px;'>"; //  1 row = 18px
                }
                $compteur[$id] = 1; // on incrémente le compteur initialement à zéro
            } else
                tdcell ($c); // il s'agit d'un créneau libre  -> <td> normal
            						$c_date="$year-$month-$day";
            if ((!isset($id)) or  (est_fermee($c_date,$area)) ) // Le créneau est libre
            {
                $hour = date("H",$t);
                $minute  = date("i",$t);
                $date_booking = mktime($hour, $minute, 0, $month, $day, $year);
                echo "<center>";
				if ((est_fermee($c_date,$area))) 
				{
                   echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
                } else
					$UrlEdit=$ModuleReservationEdit."?area=".$area."&room=".$room."&hour=".$hour."&minute=".$minute."&year=".$year."&month=".$month."&day=".$day."&page=day&table_loc=".$TableLoc;
                if ((authGetUserLevel(getUserName(),-1) > 1) or (auth_visiteur(getUserName(),$room) == 1)  )
                {
					if ($c == "avertissement")
					{
						echo "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
					}
		            else 
		            {
						// Check user à droit ajouter une modification
						if (authGetUserLevel(getUserName(),$room) > 2)
							echo "<a href='#?'  onClick=\"OpenPopupResa('".$UrlEdit."')\"><img src='./commun/images/new.png' border='0' alt='".get_vocab("add")."' alt='".get_vocab("add")."' class='print_image' /></a>";                        
						else
							echo "&nbsp;";
		            }
				} else 
				{
					echo "&nbsp;";
                }
                echo "</center>";
                echo "</td>\n";
            }
            elseif ($descr != "")
            {
                 // si la réservation est à confirmer, on le signale
                if (($delais_option_reservation[$room] > 0) and (isset($today[$room][$t]["option_reser"])) and ($today[$room][$t]["option_reser"]!=-1)) {
                    echo "&nbsp;<img src=\"./commun/images/small_flag.png\" alt=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."\" title=\"".get_vocab("reservation_a_confirmer_au_plus_tard_le")."&nbsp;".time_date_string_jma($today[$room][$t]["option_reser"],$dformat)."\" width=\"20\" height=\"20\" border=\"0\" />&nbsp;\n";
                        }
                // si la réservation est à modérer, on le signale
                if ((isset($today[$room][$t]["moderation"])) and ($today[$room][$t]["moderation"]=='1')) {
                    echo "&nbsp;<img src=\"./commun/images/flag_moderation.png\" alt=\"".get_vocab("en_attente_moderation")."\" title=\"".get_vocab("en_attente_moderation")."\" border=\"0\" />&nbsp;\n";
                        }

                #if it is booked then show
                if (($statut_room[$room] == "1") or
                (($statut_room[$room] == "0") and (authGetUserLevel(getUserName(),$room) > 2) )) {
                	//overloaddataNEW
					$entry_id = $today[$room][$t]["id"];
					$start_time = $today[$room][$t]["start_time"];
					$nda = $today[$room][$t]["nda"];
					//echo $start_time;
					$all_entry_nda = $Aghate->GetEntriesByNda($nda) ;
					$prev_link =$Aghate->HasPreviousSejour ($all_entry_nda,$entry_id,$start_time);
					$AreaName = $Aghate->GetServiceInfoByUh($prev_link["uh"]);
					if ($prev_link) 
					{
						$link = "day.php?year=".$prev_link['year']."&month=".$prev_link['month']."&day=".$prev_link['day']."&area=".$prev_link['area'];
						echo '<a href='.$link.' title='.$AreaName["service_name"].'><= UH :'.$prev_link["uh"].' </a>';
					}
					else echo '';
						$overload_data=$Aghate->GetOverloadData($id);
                		$duree= $duration .' '. $dur_units;
						$date_rdv	=date("d/m/Y-H:i",$date_rdv);
						$link="convocation_options.php?pat=$descr&uh=$uh&med=$med&duree=$duree&date_nais=$p_ddn&date_entree=$date_rdv&area=$area";
						
						/*$day ="0".$day; 
						$month="0".$month;
						$day = =substr($day,strlen($day)-2,2);
						$month = =substr($month,strlen($month)-2,2);			
						*/
						// icon SEXE 
						$sexe = $today[$room][$t]["sexe"];
						if($sexe=="M"){
							echo "<img src='./commun/images/homme.png' alt='Homme' border='0' class='print_image' width='20px' height='20px'/><br />";
						}
						if($sexe=="F"){
							echo "<img src='./commun/images/femme.png' alt='Femme' border='0' class='print_image' width='20px' height='20px'/><br />";									
						}
            
						$UrlView=$ModuleReservationView."?id=".$id."&area=".$area."&room=".$room."&year=".$year."&month=".$month."&day=".$day."&page=day&table_loc=".$TableLoc;
						echo "<a href='#?'  onClick=\"OpenPopupResa('".$UrlView."')\"   title='' />$descr</a>";                        
            
						echo "<br />".$overload_data;
                    if ($today[$room][$t]["description"]!= "") {
                        echo "<br /><i>".
                        $Aghate->GetDescComplementaire($today[$room][$t]["description"])
                        ."</i>";
                    }
                    
                    $next_link =$Aghate->HasNextSejour($all_entry_nda,$entry_id,$start_time);
                    $AreaName = $Aghate->GetServiceInfoByUh($next_link["uh"]);
					if ($next_link)
					{
						$link = "day.php?year=".$next_link['year']."&month=".$next_link['month']."&day=".$next_link['day']."&area=".$next_link['area'];
						echo '<a href='.$link.' title='.$AreaName["service_name"].'>UH : '.$next_link["uh"].' => </a>';
					}
					else echo "";
					
					
                } else {
                    echo "$descr";
                }
                echo "</td>\n";
            }
        } // Fin Deuxième boucle sur la liste des ressources du domaine

        // Répétition de la première colonne
        // Si la ressource est temporairement indisponible, on le signale, sinon, couleur normale
        if (isset($cellules[$id]['retard']))
			echo "</div>";
        tdcell("cell_hours");
        if( $enable_periods == 'y' ){
            $time_t = date("i", $t);
            $time_t_stripped = preg_replace( "/^0/", "", $time_t );
            echo $periods_name[$time_t_stripped] . "</td>\n";

        } else {
            echo date(hour_min_format(),$t) . "</td>\n";
        }

        echo "</tr>\n";
        reset($rooms);
    }
    // répétition de la ligne d'en-tête
    echo "<tr>\n<th>&nbsp;</th>";
    for ($i = 0; $i < $nbcol; $i++)
    {
        echo "<th";
        if ($Aghate->CheckIndispo($id_room[$i],$start_day)) echo " class='avertissement' ";
        echo ">" . htmlspecialchars($room_alias[$i])."</th>";
    }
    echo "<th>&nbsp;</th></tr>\n";

    echo "</table> ";
    show_colour_key($area);
}


if ($_SESSION['alert_consult_prog']!="NO"){
	//traitement pour les médecins
	if($UserInfo[0]['medecin']=="1" && $_SESSION['statut'] != 'administrateur'){
		$session_user = $_SESSION['login'];
		$consult_prog = $Aghate->GetConsultProg($session_user);
		if (count($consult_prog) > 0)	{
			echo "<script type=\"text/javascript\" language=\"javascript\">";
			echo "OpenPopup('consult_prog.php')\n";
			echo "</script>";
			$_SESSION['alert_consult_prog']	="NO";	
		}
	}
	//traiter pour les gestionnaires en affichant les nouvelles demandes pour leurs services	
}


$fin = time();

$_SESSION['c_day'] = $_GET['day'];
$_SESSION['c_month'] = $_GET['month'];
$_SESSION['c_year'] = $_GET['year'];
$link="print_day_A4.php?area=".$area."&am7=".$am7."&pm7=".$pm7."&this_area_name=".$this_area_name."&jour=".$c_jour."&urm=".$_SESSION["URM"];
 
echo "<table align='center'><tr><td align='leftr'><a href='#'  onClick=\"OpenPopup('".mysql_real_escape_string($link)."')\" > Format imprimable en A4 </a></td></tr></table>";
 
include ("reprogram_div_top.php");
include "./commun/include/trailer.inc.php";
?>
