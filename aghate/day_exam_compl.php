<?php
#########################################################################
#                         day_exam_compl.php                            #
#                                                                       #
 
#                                                                       #
#########################################################################
// le page day.php doit être inclure avant

// declaration du TAble LOc
$TableLoc="agt_exam_compl";
$Aghate->NomTableLoc=$TableLoc;
 
#Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");

#Fonction relative à la session
require_once("./commun/include/session.inc.php");

#Si nous ne savons pas la date, nous devons la créer
$date_now = time();
if (!isset($day) or !isset($month) or !isset($year))
{
    if ($date_now < getSettingValue("begin_bookings"))
        $date_ = getSettingValue("begin_bookings");
    else if ($date_now > getSettingValue("end_bookings"))
        $date_ = getSettingValue("end_bookings");
    else
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


// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
	
    die();
};
// Paramètres langage
include "./commun/include/language.inc.php";

if (empty($area))
{
	$res=$Aghate->GetUserInfo ($_SESSION['login']);
	if(count($Aghate->GetServiceInfoByServiceId($res[0]['default_area']) < 1 ))
	{
   	print_header($day, $month, $year, $area,$type_session);
	  echo "<br><br><br><br><div style=\"text-align : center; font-size : 15px \"  >Veuillez declarer le <b>Service par défaut</b>  a afficher sur le page d'accuil en cliquant<a href='my_account.php'>  ICI</a> </div>";
		exit;
		
	}
}
 

// On affiche le lien "format imprimable" en bas de la page
$affiche_pview = '1';
if (!isset($_GET['pview'])) $_GET['pview'] = 0; else $_GET['pview'] = 1;

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
} else {
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Si aucun domaine n'est défini
if ($area == 0) {
   print_header($day, $month, $year, $area,$type_session);
   header("Content-type:text/html; charset: utf-8");
   echo "<H1>".get_vocab("noareas")."</H1>";
   echo "<A HREF='admin_accueil.php'>".get_vocab("admin")."</A>\n
   </BODY>
   </HTML>";
   exit();
}

if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
if(authUserAccesArea($session_login, $area)==0)
{
	//echo "-",authUserAccesArea($session_login, $area),$session_login, $area,"-";
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

if (check_begin_end_bookings($day, $month, $year))
{
    showNoBookings($day, $month, $year, $area,$back,$type_session);
    exit();
}



# print the page header
print_header($day, $month, $year, $area, $type_session);

?>
<script type="text/javascript" src="./commun/js/functions.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-1.11.0.js" charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/jquery-ui-1.10.4.custom.js"  charset="utf-8"></script>
<script type="text/javascript" src="./commun/js/info_bulle.js"  charset="utf-8" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="./commun/js/fonctions_aghate.js"    language="javascript"></script>

<link href="./commun/style/smoothness/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css" media="all"  charset="utf-8"/>
<link href="./commun/style/day.css" rel="stylesheet" type="text/css" media="all" />


<script type='text/javascript'>
var CurrentPosition; // variable utilisé pour detecter le current posssion n'est pas surimmmer
var CurrentLocId; // variable utilisé pour detecter le current posssion n'est pas surimmmer
var startPos;
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
/*
===================================================================
	DROG AND DROP TBODY  Update times on change
===================================================================
*/
function UpdateTimes(obj_id,startPos,CurrentPosition,CurrentLocId)
{
	var compteur = 1 ;// new compteuur pos
	var	TableLoc =$("#TableLoc").val();	
	var res;
	var post_val="";

	$('#'+obj_id+' tr').each(function() {	
		//recupère LOCID
		LocId =$(this).attr( "LOC_ID" ); 		
		//Remettre le compteur inceremental pour numerique values
		if(jQuery.isNumeric($(this).find('td').eq(0).html()))
		{
			$(this).find('td').eq(0).html(compteur);
			
			if(compteur==11){
				compteur++; // skip pour le medecin apres midi
			}
			// update pos dans la base si pat present dans le td
			if (LocId){
				//alert("./commun/ajax/ajax_aghate_resa_update_pos.php?id="+LocId+"&newpos="+test+"&table_loc="+TableLoc);
				res =LanceAjax("./commun/ajax/ajax_aghate_resa_update_pos.php","id="+LocId+"&newpos="+compteur+"&table_loc="+TableLoc);		 
				//alert(res);
			}
			//alert(compteur);
 
			compteur++;
		}
	});
 
}



/*
#####################################################
		Ducuement ready funtions
#######################################################	
*/

// SET REGIONAL SETTINGS to FR
jQuery(function($){
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
});

$(function() {
	/*
	===================================================================
			Jquery Calandar en 3 mois
	===================================================================
	*/	
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

  
	/*
	===================================================================
	Change color
	===================================================================
	*/
	$('.color').change(function() 
	{
		var TableLoc = $("#TableLoc").val();
		 SelectedVal=$(this).val();
		 LocId =$(this).attr( "LOC_ID" ) 
		 tmp = SelectedVal.split(":");
		 $(this).closest('tr').css("background-color", "#"+tmp[1]);
		 LanceAjax("./commun/ajax/ajax_aghate_change_coleur.php","id="+LocId+"&newcolor="+tmp[0]+"&table_loc="+TableLoc);		 

	});
	

		
}); // fin document ready

//===================================================
//onclick de semaine => envoyer vers week_all
//===================================================
$(function() {
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
		var url ="./week.php?year="+currentYear+"&month="+currentMonth+"&day="+currentDay+"&area="+<?php print $area?>;
		location.href=url       
	 });
 });
</script>

 
<?php

// Si format imprimable ($_GET['pview'] = 1), on n'affiche pas cette partie
if ($_GET['pview'] != 1) {

/*
====================================================================
Premier fois demande de syncroniser sur Gilda
====================================================================
*/
$sql ="Select * from agt_service";
$res=$mysql->select($sql);
if (count($res) < 1)
{
		echo "<h1 align = 'center'>".get_vocab('no_rooms_for_area')."</h1>";
		echo "<h2 align = 'center' > Une synchronisation est nécessaire avec la structure GILDA  </h2>";
		echo "<H1 style=\"text-align : center;  \"  ><a href='update_structure.php'> Synchroniser maintenant </a> </H1>";
		echo "<br><br>";
		include "./commun/include/trailer.inc.php";
		exit;
	
}
/*
====================================================================
Premier fois demande de declarer le service par default
====================================================================
*/
 
	$areas = $Aghate->GetAllArea();
	if (count($areas)< 1) 
	if($nb_area==0){
		echo "<h1 align = 'center'>".get_vocab('no_rooms_for_area')."</h1>";
		echo "<h2 align = 'center' > Une synchronisation est nécessaire avec la structure GILDA  </h2>";
		echo "<div style=\"text-align : center; font-size : 15px \"  ><a href='update_structure.php'> Synchroniser maintenant </a> </div>";
		echo "<br><br>";
	}
	else{
		echo "<div style=\"text-align : center; font-size : 15px \"  ><a href='my_account.php'> Modifier le service par defaut </a> </div>";
	}
 

 
// Affichage d'un message pop-up
affiche_pop_up(get_vocab("message_records"),"user");

echo "<table border='0' width=\"100%\" cellspacing=2><tr>\n<td width='20%'>";
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
$am7=mktime(0,0,0,$month,$day,$year);
$pm7=mktime(23,59,0,$month,$day,$year);
 
#Show current date
// Ligne modifier pour effectué qu'une seule requête avec classAghate
// row_name
$res_area = $Aghate->GetServiceInfoByServiceId($area);
$this_service_name = $res_area[0]['service_name'];
$this_area_urm = $res_area[0]['urm'];

$_SESSION["URM"]=$this_area_urm;

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



#We want to build an array containing all the data we want to show
#and then spit it out.

#Get all appointments for today in the area that we care about
#Note: The predicate clause 'start_time <= ...' is an equivalent but simpler
#form of the original which had 3 BETWEEN parts. It selects all entries which
#occur on or cross the current day.

// count nombre de patient dans le journée
$compare_to_end_time = $am7;
$compare_to_start_time = $pm7 + $resolution;


$TableauData['compare_to_start']	=	$compare_to_start_time;
$TableauData['compare_to_end']		=	$compare_to_end_time;
$TableauData['service_id']			=	$area;
$TableauData['medecin_pos_matin']	=	0;
$TableauData['medecin_pos_apm']		=	11;

$res_nbr_pat =$Aghate->GetNbPatDayExamCompl($TableauData);
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

	$res_ = grr_sql_query($sql_);
	$row = grr_sql_row($res_, 0);	
	$msg="";
	if((strlen($row[1]) > 1)){
		$msg="Medecin : ".$row[0]." " .$row[1] ." , ";
		}

	if ($row[3]=='1'){
		$msg.=" SERVICE FERMEE ";		
		}
		
 
	$msg.=nl2br($row[2]);
 
	if (strlen($msg)< 3)$msg="<img src='./commun/images/post_it.jpg' border='0' height='20' />";
if ((authGetUserLevel($_SESSION['login'],$room_id) >= 2))
{
	$link="<H2 align='center'><a href='#'  onClick=\"OpenPopup('additional_info_journalier.php?date=$c_date&service_id=".$area."')\" >".$msg."</a></H2>";
}else{
	$link="<H2 align='center'> ".$msg." </H2>";
}	
echo $link;

//minicals($year, $month, $day, $area, -1, 'day');
echo "</td>";
echo "<td width='20%'>&nbsp;</td>";
echo "</tr></table>";
// fin de la condition "Si format imprimable"


$res = $Aghate->GetInfoAffichage($area,$compare_to_start_time,$compare_to_end_time);
$nb_res = count($res);
$cpt = 0;
$lien = "";
 
//echo "<pre>";
//print_r($res);

// On Load page charger les TypeResa(coleur)
$AllTypes = $Aghate->GetResrvationTypes($area);

 
foreach($AllTypes as $key )
{
	// attn colorcode est ajouté devant separateur
	$ListeTypeResa[]=$key['type_letter'].":".$tab_couleur[$key['color']]."|". $key['type_name'];
}

//print_r($ListeTypeResa);
 
$plages=$Aghate->GetServicePlages($area);
$NbrPlages=count($plages);
// pour gestion de colors
global $tab_couleur;
static $ecolors;

	//------------------------------------------------------				    
	// Get all room list
	//------------------------------------------------------
	$Rooms= $Aghate->GetAllRooms($area);
 	echo "<table width=\"80%\" align='center'><tr>\n<td>\n<a href=\"day.php?year=$yy&amp;month=$ym&amp;day=$yd&amp;area=$area\">
			&lt;&lt; ".get_vocab('daybefore')."</a></td>\n<td align=right><a href=\"day.php?year=$ty&amp;month=$tm&amp;day=$td&amp;area=$area\">"
				.get_vocab('dayafter')." &gt;&gt;</a></td>\n</tr></table>\n";
 	// Tableau principal

	echo "<table cellspacing='3' cellpadding='3' border='1' align='center'  >";	
	$JS_data ="";	
	for($r = 0; $r < count($Rooms); $r++)
	{
		//------------------------------------------------------
		//preparation du Tableau Creanaux par heure ou demi heure
		//-------------------------------------------------------

		for($plage=0; $plage < $NbrPlages; $plage++)
		{
			$TableauHeure[$plage]['plage']=$plages[$plage]['nom_periode'];	
			$TableauHeure[$plage]['pos']=$plages[$plage]['num_periode'];								
			
		
			$TableauHeure[$plage]['heure']=date("Y-m-d-H-i");	
			//possitionne le malade dans le plage		
			for($p=0; $p < $nb_res; $p++)
			{
				if (($res[$p]['plage_pos']==$plages[$plage]['num_periode']) &&  $res[$p]['room_id']==$Rooms[$r]['id'] )
				{
					// sur les plages pas de controle sur check_libre 
					// donc force sur le prochainge... a voir
					if (!is_array($TableauHeure[$plage]['data']))
						$TableauHeure[$plage]['data']=$res[$p];					
					else
					{
						$plage++;
						$TableauHeure[$plage]['data']=$res[$p];											
					}
					
					break;
				}
			}
		}		
		// prepare javascript dragdrop 
		
		$JS_data .= '$( "#'.$Rooms[$r]['room_alias'].'" ).sortable({
						connectWith: "#'.$Rooms[$r]['room_alias'].'",
						items: " tr:not(:first)",
						helper:"clone",
						zIndex: 999990,
						start : function(event, ui) {
							//get initail pos 
							ui.item.data("startPos", ui.item.index());
						},
						change: function(event, ui) {
							// get last pos
							CurrentPosition = ui.placeholder.index();
							CurrentLocId = ui.item.attr("LOC_ID");
						},		
						revert: 5,			  
						update: function( event, ui ) {
							//$("#log").append(" | "+ui.item.data("startPos")+"-"+CurrentPosition+"-"+CurrentLocId);
							//$(this).sortable("cancel");
							//alert(CurrentPosition);
							UpdateTimes(\''.$Rooms[$r]['room_alias'].'\',startPos,CurrentPosition,CurrentLocId);
						},
					  });';
		
		echo "<tr>";
		echo "<td align='center'> <H4>".$Rooms[$r]['room_alias']."</H4>";
		echo "<table id='TBL_".$Rooms[$r]['room_alias']."' cellspacing='0' cellpadding='0' border='1' style='min-width:600px'>";
		echo "<tbody class='DrogDropTBody1' id='".$Rooms[$r]['room_alias']."'>  ";
				



		
		//print_r($TableauHeure);
		//exit;
		/*
		------------------------------------------------------
		Affichaage le tableau 
		-------------------------------------------------------
	 	*/			
		for($p=0;$p < count($TableauHeure);$p++)
		{
			if (!is_array($TableauHeure[$p]['data']))
			{
				// pas de reservation place libre 
				$CurColeur="FFFFFF";
				list($__year,$__month,$__jr,$hour,$minute)=explode("-",date("Y-m-d-H-i"));
				
				if ((est_fermee($c_date,$area))) 
				{
                   $lien= "<center><img src=\"./commun/images/stop.png\" border=\"0\" alt=\"".get_vocab("reservation_impossible")."\"  title=\"".get_vocab("reservation_impossible")."\" width=\"16\" height=\"16\" class=\"print_image\"  /></center>";
                }
				else
				{
					$UrlEdit="reservation_exam_compl.php"."?area=".$area."&room=".$Rooms[$r]['id']."&hour=".$hour."&minute=".$minute."&year=".$year."&month=".$month."&day=".$day."&page=day&plage_pos=".$TableauHeure[$p]['pos']."&table_loc=".$TableLoc;

					$lien= "<a href='#?'  onClick=\"OpenPopupResa('".$UrlEdit."')\">
									<img src='./commun/images/new.png' 
									border='0' 
									alt='".get_vocab("add")."'/> 

						  </a>";   
				}                 
			}else
			{
				// cas reservation prensent 
				$imgag_link="";
		 		if ($TableauHeure[$p]['data']['sex']=="M")
					$imgag_link="<img src='./commun/images/homme.png' width='15' height='15' border='0'/>";
				if ($TableauHeure[$p]['data']['sex']=="F")
					$imgag_link="<img src='./commun/images/femme.png' width='15' height='15' border='0'/>";
				
				//gestion colors
				$colclass=$TableauHeure[$p]['data']['type'];
				if (($colclass >= "A") and ($colclass <= "Z")) 
				{
					$res_couleur = $mysql->select("select couleur from agt_type_area where type_letter='".$colclass."'");
					$num_couleur=$res_couleur[0]['couleur'];
					$CurColeur=$tab_couleur[$num_couleur];
				}else
				{
				  $CurColeur=$colclass;
				}
				
				$MedecinInfo = $Aghate->GetInfoMedecinById($TableauHeure[$p]['data']['medecin'],"agt_medecin_exam_compl");
				
				if(strlen($TableauHeure[$p]['data']['noip'])>0){
					$PatDescr = "NIP : ".$TableauHeure[$p]['data']['noip']." / "
		  				.$TableauHeure[$p]['data']['nom']." "
		  				.$TableauHeure[$p]['data']['prenom']
		  				." Né(e) le ".date('d/m/Y',strtotime($TableauHeure[$p]['data']['ddn']))
		  				."(".$TableauHeure[$p]['data']['sex'].")";
		  				
				}
				else{
					$PatDescr = $TableauHeure[$p]['data']['patient'];
				}
				
				
				$TableauHeure[$p]['Heure']="";
				$UrlEdit="reservation_exam_compl.php"."?id=".$TableauHeure[$p]['data']['entry_id']."&table_loc=".$TableLoc;
				$lien= "<div><a href='#?'  onClick=\"OpenPopupResa('".$UrlEdit."')\">"
						.$imgag_link. " ".$PatDescr
						."</a>";
				if(strlen($TableauHeure[$p]['plage'])< 3){
				$lien .="<div style='float : right'>".$Html->InputSelect($ListeTypeResa,'color',
																	$TableauHeure[$p]['data']['type'],
																	150,"class='color' LOC_ID='".$TableauHeure[$p]['data']['entry_id']."' 
																	COLOR_VAL='".$tab_couleur[$num_couleur]."'")."</div>";				
				}
				$lien.="</div>";
				if(strlen($TableauHeure[$p]['plage'])< 3){
					if(strlen($MedecinInfo['nom'])>0){
						$lien .= "Medecin :".$MedecinInfo['nom']." ". $MedecinInfo['prenom'];
					}
					$descr = $Aghate->GetDescComplementaire($TableauHeure[$p]['data']['description']);
					$lien .= "<br>".$Aghate->GetDescComplementaire($TableauHeure[$p]['data']['description']);	
		  		}
			}
			echo "<tr bgcolor='".$CurColeur."' align='left'  LOC_ID='".$TableauHeure[$p]['data']['entry_id']."'>
							<td width='30px'> ".$TableauHeure[$p]['plage']."</td>			
							<td>".$lien."</td>
						</tr>";				
			
		}
		unset($TableauHeure); //initilaise pour le prochane LIT
		
		echo "</tbody>";
		echo '</table>';	
		echo "</td></tr>" ; 
	}
 
	echo "</tr></table>";

	print $Html->InputHiddenBox("TableLoc",$TableLoc );	
	
	show_colour_key($area);
	//Trace Ajax
	$style=(strlen($_GET['log'])>0)?"display:black;":"display:none;";
	echo '<div id="LOG" style="'.$style.'">Les traces de la page sont disponible ici<br></div>';	
	//------------------------------------------------------------------
	//print here javascript DrogAndDrop
	//------------------------------------------------------------------
	Print "<script>\n".$JS_data ."\n</script>";
	include "./commun/include/trailer.inc.php";	
	exit;

?>
