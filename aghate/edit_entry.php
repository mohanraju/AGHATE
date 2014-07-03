<?php
#########################################################################
#                         edit_entry.php                                #
#                                                                       #
#                  Interface d'édition d'une réservation                #
#                                                                       #
#                  Dernière modification : 20/07/2006                   #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
 * D'après http://mrbs.sourceforge.net/
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
include "./commun/include/admin.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
error_reporting(E_ALL^E_NOTICE);

$grr_script_name = "edit_entry.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";



// Initialisation
if (isset($_GET["id"]))
{
  $id = $_GET["id"];
  settype($id,"integer");
}
else $id = NULL;

$period = isset($_GET["period"]) ? $_GET["period"] : NULL;
if (isset($period)) settype($period,"integer");
if (isset($period)) $end_period = $period;

$edit_type = isset($_GET["edit_type"]) ? $_GET["edit_type"] : NULL;
if(!isset($edit_type)) $edit_type = "";

// si $edit_type = "series", cela signifie qu'on édite une "périodicité"
$page = verif_page();
if (isset($_GET["hour"]))
{
  $hour = $_GET["hour"];
  settype($hour,"integer");
  if ($hour < 10) $hour = "0".$hour;
}
else $hour = NULL;

if (isset($_GET["minute"]))
{
  $minute = $_GET["minute"];
  settype($minute,"integer");
  if ($minute < 10) $minute = "0".$minute;
}
else $minute = NULL;

$rep_num_weeks='';

global $twentyfourhour_format;
//Si nous ne savons pas la date, nous devons la créer

if(!isset($day) or !isset($month) or !isset($year))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
}

// s'il s'agit d'une modification, on récupère l'id de l'area et l'id de la room
if (isset($id))
{
  if ($info = mrbsGetEntryInfo($id))
    {
      $area  = mrbsGetServiceIdByRoomId($info["room_id"]);
      $room = $info["room_id"];
    }
  else
    {
      $area = "";
      $room = "";
    }
}

if(empty($area))  $area = get_default_area();
$urm=$_SESSION["URM"];


// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Récupération d'info sur la rerssource

$info_agt_room = $Aghate->GetRoomInfoByRoomId($room);

$type_affichage_reser = $info_agt_room['type_affichage_reser'];
$delais_option_reservation  = $info_agt_room['delais_option_reservation'];
$qui_peut_reserver_pour  = $info_agt_room['qui_peut_reserver_pour'];

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars( $_SERVER['HTTP_REFERER']);

//Vérification de la présence de réservations
if (check_begin_end_bookings($day, $month, $year))
{
    if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) $type_session = "no_session";
    else $type_session = "with_session";
    showNoBookings($day, $month, $year, $area,$back,$type_session);
    exit();
}

//Vérification des droits d''accès


if ((authGetUserLevel(getUserName(),-1) < 2) and (auth_visiteur(getUserName(),$room) == 0))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

if(authUserAccesArea($_SESSION['login'], $area)==0)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}


if(authUserReserveRoom($_SESSION['login'], $room)==0)
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}


if(UserRoomMaxBooking(getUserName(), $room, 1) == 0)
{
    showAccessDeniedMaxBookings($day, $month, $year, $area, $room, $back);
    exit();
}

//Vérification si l'on édite une périodicité ($edit_type = "series") ou bien une réservation simple


/*
* Cette page peut ajouter ou modifier une réservation
* Nous devons savoir:
*  - Le nom de la personne qui a réservé
*  - La description de la réservation
*  - La Date (option de sélection pour le jour, mois, année)
*  - L'heure
*  - La durée
*  - Le statut de la réservation en cours
* Premièrement nous devons savoir si c'est une nouvelle réservation ou bien une modification
* Si c'est une modification, nous devons reprendre toute les informations de cette réservation
* Si l'ID est présente, c'est une modification
*/

if (isset($id))
{
    $res = $Aghate->GetInfoEntry($id);
    $row = $res;
    $breve_description        = $row['nom']." ".$row['prenom']." (".$row['noip']
													.") ".$row['ddn']." (".$row['sex'].")";
    $create_by    = $row['create_by'];
    $description = $row['description'];

    $start_day   = strftime('%d', $row['start_time']);
    $start_month = strftime('%m', $row['start_time']);
    $start_year  = strftime('%Y', $row['start_time']);
    $start_hour  = strftime('%H', $row['start_time']);
    $start_min   = strftime('%M', $row['start_time']);

    $end_day   = strftime('%d', $row['end_time']);
    $end_month = strftime('%m', $row['end_time']);
    $end_year  = strftime('%Y', $row['end_time']);
    $end_hour  = strftime('%H', $row['end_time']);
    $end_min   = strftime('%M', $row['end_time']);

	$protocole = $row['protocole'];
	$uh = $row['uh'];
	
    $duration    = $row['end_time']-$row['start_time'];
    $type        = $row['type'];
    $room_id     = $row['room_id'];
    $noip=$row['noip'];
    $med=$row['medecin'];
    
    $id_prog = $row['id_prog'];

    //forcé par mohan , en cas de modification le benificier sera le utilisateur courante 
    $beneficiaire   = getUserName();
    
    $modif_option_reservation = 'n';
    /*if($entry_type >= 1)
    // il s'agit d'une réservation à laquelle est associée une périodicité
    {
        $sql = "SELECT rep_type, start_time, end_date, rep_opt, rep_num_weeks, end_time, type, name, beneficiaire, description
                FROM agt_repeat WHERE id='".protect_data_sql($rep_id)."'";

        $res = grr_sql_query($sql);
        if (! $res) fatal_error(1, grr_sql_error());
        if (grr_sql_count($res) != 1) fatal_error(1, get_vocab('repeat_id') . $rep_id . get_vocab('not_found'));

        $row = grr_sql_row($res, 0);
        grr_sql_free($res);

        $rep_type = $row[0];
        if ($rep_type == 2)
            $rep_num_weeks = $row[4];
        if($edit_type == "series")
        // on edite la périodicité associée à la réservation et non la réservation elle-même
        {
            $start_day   = (int)strftime('%d', $row[1]);
            $start_month = (int)strftime('%m', $row[1]);
            $start_year  = (int)strftime('%Y', $row[1]);
            $start_hour  = (int)strftime('%H', $row[1]);
            $start_min   = (int)strftime('%M', $row[1]);
            $duration    = $row[5]-$row[1];

            $end_day   = (int)strftime('%d', $row[5]);
            $end_month = (int)strftime('%m', $row[5]);
            $end_year  = (int)strftime('%Y', $row[5]);
            $end_hour  = (int)strftime('%H', $row[5]);
            $end_min   = (int)strftime('%M', $row[5]);

            $rep_end_day   = (int)strftime('%d', $row[2]);
            $rep_end_month = (int)strftime('%m', $row[2]);
            $rep_end_year  = (int)strftime('%Y', $row[2]);

            $type = $row[6];
            $breve_description = $row[7];
            $beneficiaire = $row[8];
            $description = $row[9];

            if ($rep_type==2)
            {
              // Toutes les n-semaines
              $rep_day[0] = $row[3][0] != '0';
              $rep_day[1] = $row[3][1] != '0';
              $rep_day[2] = $row[3][2] != '0';
              $rep_day[3] = $row[3][3] != '0';
              $rep_day[4] = $row[3][4] != '0';
              $rep_day[5] = $row[3][5] != '0';
              $rep_day[6] = $row[3][6] != '0';
            } else {
               $rep_day = array(0, 0, 0, 0, 0, 0, 0);
            }
        }
        else
        // on edite la réservation elle-même et non pas de périodicité associée
        {
            $rep_end_date = strftime($dformat,$row[2]);
            $rep_opt      = $row[3];
            // On récupère les dates de début et de fin pour l'affichage des infos de périodicité
            $start_time = $row[1];
            $end_time = $row[5];
        }


    }*/
}
 
else
{
  //Ici, c'est une nouvelle réservation, les donnée arrivent quelque soit le boutton selectionné.
    if ($enable_periods == 'y')
        $duration    = 60;
    else {
		$duree_area =$Aghate->GetServiceInfoByServiceId($area);
        $duree_par_defaut_reservation_area =$duree_area[0]['duree_par_defaut_reservation_area'] ;
        if ($duree_par_defaut_reservation_area == 0) $duree_par_defaut_reservation_area = $resolution;
        $duration = $duree_par_defaut_reservation_area ;
    }
    $edit_type   = "series";
    if (getSettingValue("remplissage_description_breve")=='2')
		    $breve_description = $_SESSION['prenom']." ".$_SESSION['nom'];
	  else
        $breve_description = "";
    $beneficiaire   = getUserName();
    $tab_benef["nom"] = "";
    $tab_benef["email"] = "";
    $create_by    = getUserName();
    $description = "";
    $start_day   = $day;
    $start_month = $month;
    $start_year  = $year;
    $start_hour  = $hour;
    (isset($minute)) ? $start_min = $minute : $start_min ='00';

    if ($enable_periods=='y') {
        $end_day   = $day;
        $end_month = $month;
        $end_year  = $year;
        $end_hour  = $hour;
        (isset($minute)) ? $end_min = $minute : $end_min ='00';
    } else {
        // On fabrique un timestamp
        $now = mktime($hour, $minute, 0, $month, $day, $year);
        $fin = $now + $resolution;
        $end_day   = date("d",$fin);
        $end_month = date("m",$fin);
        $end_year  = date("Y",$fin);
        $end_hour  = date("H",$fin);
        $end_min = date("i",$fin);
    }

    $type        = "";
    $room_id     = $room;
    $id = 0;
    $rep_id        = 0;
    $rep_type      = 0;
    $rep_end_day   = $day;
    $rep_end_month = $month;
    $rep_end_year  = $year;
    $rep_day       = array(0, 0, 0, 0, 0, 0, 0);
    $rep_jour      = 0;  // pour les Jours/Cycle
//    $option_reservation = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $option_reservation = -1;
    $modif_option_reservation = 'y';

}
//=====================================================
// ajouté par mohan pour gerer les reprogramation
// si une reprogramation on consier une nouvelle reservation en recuparent les values d'non venue
//=====================================================================
/*if (strlen($_SESSION['REPROGMATION']) >10){
	list($id_non_venue,$noip,$nom_pat)=explode("|",$_SESSION['REPROGMATION']);
	$res = $Aghate->GetInfoNonVenu($id_non_venue);
	$nb_non_venue = count($res);
   if (! $res) fatal_error(1, grr_sql_error());
   if ($nb_non_venue!= 1) 
	fatal_error(1, get_vocab('repeat_id') . $rep_id . get_vocab('not_found'));
   $row = $res;
            $type = $row['type'];
            $breve_description = $row['name'];
            $description = $row['description'];

}*/
// fin d'ajout par mohan pour reprogramation


// Si Err=yes il faudra recharger la saisie
if ( isset($_GET["Err"]))
{
	$Err = $_GET["Err"];
}
//Transforme $duration en un nombre entier
if ($enable_periods=='y')
    toPeriodString($start_min, $duration, $dur_units);
else
    toTimeString($duration, $dur_units);
//Maintenant nous connaissons tous les champs
if(!getWritable($beneficiaire, getUserName(),$id))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit;
}

// On cherche s'il y a d'autres domaines auxquels l'utilisateur a accès
$nb_areas = 0;
$res = $Aghate->GetAllArea();
$row = $res;
$nb_info_area = count($res);
$allareas_id = array();
if ($res) for ($i = 0; $i<$nb_info_area; $i++)
{
  array_push($allareas_id,$row[$i]['id']);
  if (authUserAccesArea(getUserName(),$row[$i]['id'])==1)
    {

      $nb_areas++;
    }
}

// Utilisation de la bibliothèqye prototype dans ce script
$use_prototype = 'y';
print_header($day, $month, $year, $area);
$ajax_vals=	$session_user = $_SESSION['login']."|".$session_statut = $_SESSION['statut'];	


?>

<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>
<script type="text/javascript" src="./commun/js/jquery-1.9.1.js"></script>

<script type="text/javascript" src="./commun/js/edit_entry.js" language="javascript"></script>
<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
//Vérification de la forme
// lors d'un clic dans une option
function check_1 ()
{
    isIE = (document.all)
    isNN6 = (!isIE) && (document.getElementById)
    if (isIE) menu = document.all['menu2'];
    if (isNN6) menu = document.getElementById('menu2');
    if (menu) {
    if (!document.forms["main"].rep_type[2].checked)
    {
      document.forms["main"].elements['rep_day[0]'].checked=false;
      document.forms["main"].elements['rep_day[1]'].checked=false;
      document.forms["main"].elements['rep_day[2]'].checked=false;
      document.forms["main"].elements['rep_day[3]'].checked=false;
      document.forms["main"].elements['rep_day[4]'].checked=false;
      document.forms["main"].elements['rep_day[5]'].checked=false;
      document.forms["main"].elements['rep_day[6]'].checked=false;
      menu.style.display = "none";

   } else {
      menu.style.display = "";
   }
   }
    // Pour les checkboxes des Jours/Cycles
<?php
if (getSettingValue("jours_cycles_actif") == "Oui") {
?>
    if (isIE) menu = document.all['menuP'];
    if (isNN6) menu = document.getElementById('menuP');
    if (menu) {
    if (!document.forms["main"].rep_type[5].checked)
    {
      menu.style.display = "none";
    } else {
      menu.style.display = "";
    }
    }
<?php
	}
?>
}
// lors d'un clic dans la liste des semaines
function check_2 ()
{
   document.forms["main"].rep_type[2].checked=true;
   check_1 ();
}
// lors d'un clic dans la liste des mois
function check_3 ()
{
   document.forms["main"].rep_type[3].checked=true;
}
// lors d'un clic dans la liste des bénéficiaires
function check_4 ()
{
    isIE = (document.all)
    isNN6 = (!isIE) && (document.getElementById)
    if (isIE) menu = document.all['menu4'];
    if (isNN6) menu = document.getElementById('menu4');
    if (menu) {
    if (!document.forms["main"].beneficiaire.options[0].selected) {
      menu.style.display = "none";
    } else {
      menu.style.display = "";
    }
    }
}
// lors de l'ouverture et la fermeture de la périodicité
function check_5 ()
{
	var menu; var menup; var menu2;
	isIE = (document.all)
	isNN6 = (!isIE) && (document.getElementById)
	if (isIE) {
		menu = document.all['menu1'];
		menup = document.all['menuP'];
		menu2 = document.all['menu2'];
		}
	else if (isNN6) {
		menu = document.getElementById('menu1');
		menup = document.getElementById('menuP');
		menu2 = document.getElementById('menu2');
		}

	if ((menu)&&(menu.style.display == "none")) {
		menup.style.display = "none";
		menu2.style.display = "none";
	}
	else
		check_1();
}

function Load_entry ()
{
	recoverInputs(document.forms["main"],retrieveCookie('agt_loc'),true);
<?php
if (!$id <> "") {
?>
	if (!document.forms["main"].rep_type[0].checked)
	clicMenu('1');
<?php
	}
?>
}

function Save_entry ()
{
setCookie('agt_loc',getFormString(document.forms["main"],true));
}


function validate_and_submit ()
{
	/*PageData = $("form").serialize();
	alert(PageData);
	res = LanceAjax('./commun/ajax/ajax_edit_entry.php',PageData);
	$("#div_erreurs").html(res);
	//alert(res);
	return false;*/
	var day = parseInt(document.getElementById('c_day').selectedIndex)+1;
	var jour =parseInt(document.forms["main"].day.selectedIndex)+1;
	var mois =parseInt(document.forms["main"].month.selectedIndex)+1;
	var annee=document.forms["main"].year.options[document.forms["main"].year.selectedIndex].value;

	service_id=document.forms["main"].areas.value
	room= document.getElementById("c_rooms");
	room_id=room.options[room.selectedIndex].value; 	
	c_id=document.forms["main"].id.value;
	
	if (jour < 10) jour='0'+jour;
	if (mois < 10) mois='0'+mois;
   val= jour +'-'+mois+'-'+annee+'|'+service_id+'|'+room_id;
	//------------------------------
	//Check non venu plus de Trois fois	
	//------------------------------
	noip=document.forms["main"].noip.value
	param=noip+'|'+val;
	retval = file('./edit_entry_check_nonvenu_ajax.php?param='+escape(param))

	if (retval > 2){
		msg="!!! ALERTE !!! \nCe patient n'est pas venu "+ retval +" fois au cours de ses dernières hospitalisations \nVoulez vous continuer ce convocation ?"
		if (confirm(msg)==false) return false;
	} 

   //alert(val);
   //------------------------------
	//ajax to check the sexe/chambre
	//------------------------------   
	sexe_attendu = file('./edit_entry_check_sexe_ajax.php?param='+escape(val))
	//alert(sexe_attendu);
   //------------------------------
	//ajax to check même type de réservation pour groupe d'éducation
	//------------------------------   
	type_attendu = file('./edit_entry_check_groupe_ajax.php?param='+escape(val))
		
	//------------------------------
	//ajax to check BMR	
	//------------------------------
	noip=document.forms["main"].noip.value
	/*retval = file('./edit_entry_check_bmr_ajax.php?param='+escape(noip))
	retval=retval.split("|");


	if (retval[0]=="O"){
		msg="Alert BMR !!! \nCe patient à contracté une BMR au cours de sa dernière hospitalisation \n"+retval[1] +"\n Voulez vous passer outre ?"
		if (confirm(msg)==false) return false;
	} */  
	//--------------------------------------------
	//ajax to check mutiple RDV dans +- 5 jours
	//--------------------------------------------
	val= jour +'-'+mois+'-'+annee+'|'+noip+'|'+parseInt(c_id)+'|<?php print $ajax_vals;?>';   

	retval = file('./edit_entry_check_double_rdv_ajax.php?param='+escape(val))
	if (retval!=""){
		msg="  ALERTE!!!\nLe(s) réservation existe dans +-5 jours pour ce patient\n"+retval +"\n Voulez vous passer outre ?"
		if (confirm(msg)==false) return false;
	} 
	
	
	function file(fichier)
	{
		if(window.XMLHttpRequest) // FIREFOX
		xhr_object = new XMLHttpRequest();
		else if(window.ActiveXObject) // IE
		xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
		else
		return(false);
		xhr_object.open("GET", fichier, false);
		xhr_object.send(null);
		if(xhr_object.readyState == 4) return(xhr_object.responseText);
		else return(false);
	}
	
	

	//===================================================	
	// AJAX ENDS HERE	
	//===================================================			
	
	
	
  if (document.forms["main"].benef_ext_nom) {
	  if ((document.forms["main"].beneficiaire.options[0].selected) &&(document.forms["main"].benef_ext_nom.value == ""))
	  {
	    alert ( "<?php echo get_vocab('you_have_not_entered').":" . '\n' . strtolower(get_vocab('nom beneficiaire')) ?>");
	    return false;
	  }
	}
	<?php if (getSettingValue("remplissage_description_breve")=='1') { ?>
	  if(document.forms["main"].name.value == "")
	  {
	    alert ( "<?php echo get_vocab('you_have_not_entered') . '\n' . get_vocab('brief_description') ?>");
	    return false;
	  }
	  <?php }
	  // On teste si les champs additionnels obligatoires sont bien remplis
	  // Boucle sur tous les areas
	  foreach ($allareas_id as $idtmp) {
	       // On récupère les infos sur le champ add
	      $overload_fields = mrbsOverloadGetFieldslist($idtmp);
	      // Boucle sur tous les champs additionnels de l'area
	      foreach ($overload_fields as $fieldname=>$fieldtype) {
	        if ($overload_fields[$fieldname]["obligatoire"] == 'y') {
	        // Le champ est obligatoire : si le tableau est affiché (area sélectionné) et que le champ est vide alors on affiche un message d'avertissement
	          if ($overload_fields[$fieldname]["type"] != "list") {
	              echo "if((document.getElementById('id_".$idtmp."_".$overload_fields[$fieldname]["id"]."')) && (document.forms[\"main\"].addon_".$overload_fields[$fieldname]["id"].".value == \"\")) {\n";
	          } else {
	              echo "if((document.getElementById('id_".$idtmp."_".$overload_fields[$fieldname]["id"]."')) && (document.forms[\"main\"].addon_".$overload_fields[$fieldname]["id"].".options[0].selected == true)) {\n";
	          }
	          echo "alert (\"".$vocab["required"]."\");\n";
	          echo "return false\n}\n";
	        }
	      }
	  }
	  
	  if($enable_periods!='y') { ?>
	    h = parseInt(document.forms["main"].hour.value);
	    m = parseInt(document.forms["main"].minute.value);
	    if(h > 23 || m > 59)
	    {
	      alert ("<?php echo get_vocab('you_have_not_entered') . '\n' . get_vocab('valid_time_of_day') ?>");
	      return false;
	    }
	  <?php } ?>
	  // Partie pour rendre obligatoire certains champs
		/*
		if (document.forms["main"].protocole.value == "")
		{
			alert ("<?php echo "Vous n'avez pas saisi le protocole ";?>");
	      return false;
	    }
	    
	    if (document.forms["main"].uh.value == "")
		{
			alert ("<?php echo "Vous n'avez pas saisi l'uh ";?>");
	      return false;
	    }*/
		//GESTION type selection SEXE /UH
	  if  (document.forms["main"].type.value=='0')
	  {
	  	urm="<?php print $urm?>";
	  	if (urm=="470"){
	     	alert("<?php echo get_vocab("choose_a_type"); ?>");
	   }  	
		  	else if (urm=="560"){
		     	alert("Vous devez choisir une UH!!!");
		   }else{
		     	alert("Vous devez choisir une Type!!!");
		   } 	
	   
	     return false;
	  }
	
	    <?php
	    if($edit_type == "series")
	    {     ?>
			  i1 = parseInt(document.forms["main"].id.value);
			  i2 = parseInt(document.forms["main"].rep_id.value);
			  n = parseInt(document.forms["main"].rep_num_weeks.value);
			  if ((document.forms["main"].elements['rep_day[0]'].checked || document.forms["main"].elements['rep_day[1]'].checked || document.forms["main"].elements['rep_day[2]'].checked || document.forms["main"].elements['rep_day[3]'].checked || document.forms["main"].elements['rep_day[4]'].checked || document.forms["main"].elements['rep_day[5]'].checked || document.forms["main"].elements['rep_day[6]'].checked) && (!document.forms["main"].rep_type[2].checked))
			  {
			    alert("<?php echo get_vocab('no_compatibility_with_repeat_type'); ?>");
			    return false;
			  }
			  if ((!document.forms["main"].elements['rep_day[0]'].checked && !document.forms["main"].elements['rep_day[1]'].checked && !document.forms["main"].elements['rep_day[2]'].checked && !document.forms["main"].elements['rep_day[3]'].checked && !document.forms["main"].elements['rep_day[4]'].checked && !document.forms["main"].elements['rep_day[5]'].checked && !document.forms["main"].elements['rep_day[6]'].checked) && (document.forms["main"].rep_type[2].checked))
			  {
			    alert("<?php echo get_vocab('choose_a_day'); ?>");
			    return false;
			  }
			<?php
		}
	?>
	//---------------------------------------------------
	// par mohan , vérification de sexe dans même salles
	//-----------------------------------------------------
	urm="<?php print $urm?>";
	if (urm=='010' && service_id=='11'){
		type_resa=document.forms["main"].type.value;
		// force le fermuture toute la journé groupe d'education
		types_attendu=type_attendu.split('|');
		if (type_attendu != "0"){
			if(type_resa != types_attendu[0]){
				
				var chk_val=(types_attendu[1]);
				alert("Vous pouvez reservez que le type ["+chk_val+"] dans le journée !!")
				return false;
			}
		}
	}	
	// par mohan , vérification de sexe dans même salles
	//sexe=document.forms["main"].type.value;
	/*sexe_attendu=document.forms["main"].sexe_attend.value;
		if(sexe != sexe_attendu){
			if (confirm("Incompatibilité SEXE / CHAMBRE \n Voulez vous passer outre ?")== false)
				return false;
		}*/
	// would be nice to also check date to not allow Feb 31, etc...
	  if (confirm("Voulez-vous vraiment enregistrer ?")== false)
				return false;	
	  else{			
		document.forms["main"].submit();
		return true;
	}
}
</SCRIPT>

<?php
if ($id==0)
    $A = get_vocab("addentry");
else
    if($edit_type == "series")
        $A = get_vocab("editseries").grr_help("aide_grr_periodicite");
    else
        $A = get_vocab("editentry");
$B = get_vocab("namebooker");
if (getSettingValue("remplissage_description_breve")=='1') $B .= " *";
$B .= get_vocab("deux_points");
$C = htmlspecialchars($breve_description);
$D = get_vocab("fulldescription");
$E = htmlspecialchars ( $description );
$F = get_vocab("date").get_vocab("deux_points");
$G = genDateSelectorForm("", $start_day, $start_month, $start_year,"");

//Determine l'ID de "area" de la "room"
$row = $Aghate->GetAreaIdByRoomId($room_id);
$service_id = $row['service_id'];

// Détermine si la ressource est moderée
$moderate = $info_agt_room['moderate']; // if 0 c'est -1
echo "<h2>$A</H2>\n";
if ($moderate) echo "<span class='avertissement'>".$vocab["reservations_moderees"]."</span>\n";
echo "<FORM name=\"main\" action=\"edit_entry_handler.php\" method=\"get\">\n";
?>
 <script type="text/javascript" language="JavaScript">
    <!--
function changeRooms( formObj )
{
    areasObj = eval( "formObj.areas" );
    area = areasObj[areasObj.selectedIndex].value
    roomsObj = eval( "formObj.elements['rooms[]']" )
    // remove all entries
    for (i=0; i < (roomsObj.length); i++) {
      roomsObj.options[i] = null
    }
    // add entries based on area selected
    switch (area){
<?php
    // get the area id for case statement
    if ($enable_periods == 'y')
    {
        $res = $Aghate->GetServiceInfoByServiceId($area);
	}
    else
    {
        $res = $Aghate->GetAreaInfoByServiceIdEnable();
	}
	$nb_info = count($res);
	$row = $res;
    if ($res)
    for ($i = 0; $i<$nb_info; $i++)
    {
    if (authUserAccesArea(getUserName(),$row[$i]['id'])==1)
      {
        print "      case \"".$row[$i]['id']."\":\n";
        // get rooms for this area
        $res2=$Aghate->GetRoomsByServiceId($row[$i]['id']);
        $nb_info_room = count($res2);
        $row2 = $res2;
        if ($res2) for ($j = 0; $j<$nb_info_room; $j++)
        print "        roomsObj.options[$j] = new Option(\"".str_replace('"','\\"',$row2[$j]['room_alias'])."\",".$row2[$j]['id'] .")\n";
        // select the first entry by default to ensure
        // that one room is selected to begin with
        print "        roomsObj.options[0].selected = true\n";
        // Affichage des champs additionnels
        print "        break\n";
      }
    }
?>
    } //switch
}
function popup_patient(number) {
	 whichOne = number;
	 pat="";	 
    champ_du_owner="name";
    mywindow=open('patients_ajoute.php?champ_du_owner='+champ_du_owner+'&nom='+pat,'mypat','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'patient_affiche.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
    if (mywindow.opener == null) mywindow.opener = self;
     if(mywindow.window.focus){mywindow.window.focus();}        
 
    
}
function popup_gilda(number) {
	 whichOne = number;
	 pat="";	 
    champ_du_owner="name";
    mywindow=open('patients_ajoute.php?champ_du_owner='+champ_du_owner+'&nom='+pat,'mypat','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'patients_ajoute.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
    if (mywindow.opener == null) mywindow.opener = self;
     if(mywindow.window.focus){mywindow.window.focus();}        
 
    
}

function popup_protocol(number) {
	 pat="";
    champ_du_owner="name";
    mywindow=open('recherche_protocole.php?champ_du_owner='+champ_du_owner+'&nom='+pat,'myname','resizable=yes,width=620,height=250,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'protocoles_affiche.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
    if (mywindow.opener == null) mywindow.opener = self;
     if(mywindow.window.focus){mywindow.window.focus();}        

     
}
// -->
</script>

<?php
// On construit un tableau pour afficher la partie réservation hors périodicité à gauche et la partie périodicité à droite
echo "<table width=\"100%\" border=\"1\"><tr>\n";
// Première colonne (sans périodicité)
echo "<td valign=\"top\" width=\"50%\">\n";
// Début du tableau de la colonne de gayche
echo "<TABLE width=\"100%\" border=\"0\" class=\"EditEntryTable\">\n";

// Pour pouvoir réserver au nom d'un autre utilisateur il faut :
// - avoir le droit spécifique sur cette ressource ET
// - dans le cas d'une réservation existante, il faut être propriétaire de la réservation
if(((authGetUserLevel(getUserName(),-1,"room") >= $qui_peut_reserver_pour) or (authGetUserLevel(getUserName(),$area,"area") >= $qui_peut_reserver_pour))
 and (($id == 0) or (($id!=0) and ($create_by==getUserName()) )))
 {
    $flag_qui_peut_reserver_pour = "yes";
    echo "<TR><TD class=\"E\"><B>".ucfirst(trim(get_vocab("reservation au nom de"))).get_vocab("deux_points").grr_help("aide_grr_effectuer_reservation","modifier_reservant")."</B></TD></TR>";
    echo "<TR><TD class=\"CL\"><select size=1 name=beneficiaire onClick=\"check_4();\" readonly>\n";
    //echo "<option value=\"\" >".get_vocab("personne exterieure")."</option>\n";
    $row = $Aghate->GetInfoLog();
    $nb_info = count($row);
    if ($row) for ($i = 0; $i<$nb_info; $i++) {
        echo "<option value=\"".$row[$i]['login']."\" ";
        if (strtolower($beneficiaire) == strtolower($row[$i]['login']))  echo " selected";
        echo ">".$row[$i]['nom']." ".$row[$i]['prenom']."</option>";
    }
    echo "</select>";
    echo "</TD></TR>\n";
    /*if ($tab_benef["nom"] != "")
        echo "<tr id=\"menu4\"><td>";
    else
        echo "<tr style=\"display:none\" id=\"menu4\"><td>";
    echo get_vocab("nom beneficiaire")." *".get_vocab("deux_points")."<input type=\"text\" name=\"benef_ext_nom\" value=\"".htmlspecialchars($tab_benef["nom"])."\" size=\"20\" />";*/
    if (getSettingValue("automatic_mail") == 'yes') {
        echo "&nbsp;".get_vocab("email beneficiaire").get_vocab("deux_points")."<input type=\"text\" name=\"benef_ext_email\" value=\"".htmlspecialchars($tab_benef["email"])."\" size=\"20\" />";
    }
    echo "</TD></TR>\n";
} else     $flag_qui_peut_reserver_pour = "no";
echo "<TR><TD class=\"E\"><B>$B</B></TD></TR>
<TR><TD class=\"CL\"><INPUT NAME=\"name\" SIZE=\"80\" VALUE=\"$C\" READONLY/>
<a href=\"#\"  onClick=\"popup_gilda(1)\" ><img src=\"./commun/images/find.png\" width=\"15\" height=\"15\" border=\"0\" /></a>


<INPUT type=\"hidden\" NAME=\"pat_id\"  VALUE=\"$pat_id\" />
</TD></TR>
<TR><TD class=\"E\"><B>$D</B></TD></TR>
<TR><TD class=\"TL\"><TEXTAREA name=\"description\" rows=\"2\" cols=\"80\">$E</TEXTAREA>";
echo "</TD></TR>\n";

echo "<TR><TD class=\"E\"><B>UH: </B></TD></TR>\n";
echo "<TR><TD>";
echo "<div id = 'div_champs_uh'>";
// Insert uh
echo "</div>";
echo "</TD></TR>\n";
echo "<TR><TD class=\"E\"><B>Protocole  : </B></TD></TR>\n";
echo "<TR><TD>";
echo "<INPUT size=\"80\" type=\"text\"
		name=\"protocole\" value=\"$protocole\"/>
                 <a href=\"#\"  onClick=\"popup_protocol(1)\" >
                 <img src=\"./commun/images/find.png\" width=\"15\" height=\"15\" border=\"0\" /></a>
            </TD></TR>\n";
echo "</TD></TR>\n";

// Début réservation

echo "<TR><TD class=\"E\"><B>$F</B></TD></TR>\n";
echo "<TR><TD class=\"CL\">";
echo "<table border = 0><tr><td>".$G;
echo "</TD><TD CLASS=E><B>";
//echo $enable_periods;
// Heure ou créneau de début de réservation
if ($enable_periods=='y')
{
  /*echo get_vocab("period")."</B>\n";
  echo "<SELECT NAME=\"period\">";
  foreach ($periods_name as $p_num => $p_val)
    {
      echo "<option VALUE=$p_num";
      if( ( isset( $period ) && $period == $p_num ) || $p_num == $start_min)
    echo " SELECTED";
      echo ">$p_val";
    }
  echo "</SELECT>\n";*/
}
else
{
  echo get_vocab("time")."</B></TD>\n";
  echo "<TD><INPUT NAME=\"hour\" SIZE=2 VALUE=\"";
  if (!$twentyfourhour_format && ($start_hour > 12))
	echo ($start_hour - 12);
  else
	echo $start_hour;
  echo "\" MAXLENGTH=2 /></TD><TD>:</TD><TD><INPUT NAME=\"minute\" SIZE=2 VALUE=\"".$start_min."\" MAXLENGTH=2 />";
  if (!$twentyfourhour_format)
    {
      $checked = ($start_hour < 12) ? "checked" : "";
      echo "<INPUT NAME=\"ampm\" type=\"radio\" value=\"am\" $checked />".date("a",mktime(1,0,0,1,1,1970));
      $checked = ($start_hour >= 12) ? "checked" : "";
      echo "<INPUT NAME=\"ampm\" type=\"radio\" value=\"pm\" $checked />".date("a",mktime(13,0,0,1,1,1970));
    }
}

echo "</td></tr></table>\n";
echo "</TD></TR>";	


if ($type_affichage_reser == 0)
{
  // Durée
  echo "<TR><TD class=\"E\"><B>".get_vocab("duration")."</B></TD></TR>\n";
  if (($urm=="010") and ($service_id=="11")){
		$readonly="disabled";
  		echo "<TR><TD class=\"CL\"><INPUT NAME=\"duration\" SIZE=\"7\" VALUE=\"".$duration."\" READONLY />";
  	}else{
  		echo "<TR><TD class=\"CL\"><INPUT NAME=\"duration\" SIZE=\"7\" VALUE=\"".$duration."\" />";
  		$readonly="";
  	}
  
  echo "<SELECT name=\"dur_units\" size=\"1\" $readonly>\n";
  echo "ok";
  if($enable_periods == 'y') {}
	//$units = array("periods", "days");
  else {
	  echo "dureemax : ".$duree_area[0]['duree_max_resa_area'];
      $duree_max_resa_area = $duree_area[0]['duree_max_resa_area'];
     /* if ($duree_max_resa_area < 0)
          $units = array("minutes", "hours", "days", "weeks");
      else if ($duree_max_resa_area < 60)
          $units = array("minutes");
      else if ($duree_max_resa_area < 60*24)
          $units = array("minutes", "hours");
      else if ($duree_max_resa_area < 60*24*7)
          $units = array("minutes", "hours", "days");
      else*/
          $units = array("minutes", "hours", "days", "weeks");
  }
  while (list(,$unit) = each($units))
    {
      echo "<option VALUE=$unit";
      if ($dur_units ==  get_vocab($unit)) echo " SELECTED";
      echo ">".get_vocab($unit)."</option>\n";
    }
  echo "</SELECT>\n";
  
  // Affichage du créneau "journée entière"
  // Il reste un bug lorsque l'heure finale dépasse 24 h
  $fin_jour = $eveningends;
  $minute = $resolution/60;
  $minute_restante = $minute % 60;
  $heure_ajout = ($minute - $minute_restante)/60;
  if ($minute_restante < 10) $minute_restante = "0".$minute_restante;
  $heure_finale = round($fin_jour+$heure_ajout,0);
  if ($heure_finale > 24) {
      $heure_finale_restante = $heure_finale % 24;
      $nb_jour = ($heure_finale - $heure_finale_restante)/24;
      $heure_finale = $nb_jour. " ". $vocab["days"]. " + ". $heure_finale_restante;
  }
  $af_fin_jour = $heure_finale." H ".$minute_restante;

  echo "<INPUT name=\"all_day\" TYPE=\"checkbox\" value=\"yes\" $readonly/>".get_vocab("all_day");
  if ($enable_periods!='y') echo " (".$morningstarts." H - ".$af_fin_jour.")";
  echo "</TD></TR>\n";

}
else
{
  // Date de fin de réservation
  echo "<TR><TD class=\"E\"><B>".get_vocab("fin_reservation").get_vocab("deux_points")."</B></TD></TR>\n";
  echo "<TR><TD class=\"CL\" >";
  echo "<table border = 0><tr><td>\n";
  genDateSelector("end_", $end_day, $end_month, $end_year,"");
  echo "</TD>";
  // Heure ou créneau de fin de réservation
  if ($enable_periods=='y')
    {
      echo "<TD class=\"E\"><B>".get_vocab("period")."</B></TD>\n";
      echo "<TD class=\"CL\">\n";
      echo "<SELECT NAME=\"end_period\">";
      foreach ($periods_name as $p_num => $p_val)
    {
      echo "<option VALUE=$p_num";
      if( ( isset( $end_period ) && $end_period == $p_num ) || ($p_num+1) == $end_min)
        echo " SELECTED";
      echo ">$p_val";
    }
      echo "</SELECT>\n</TD>\n";
    }
  else
    {
      echo "<TD CLASS=E><B>".get_vocab("time")."</B></TD>\n";
      echo "<TD CLASS=CL><INPUT NAME=\"end_hour\" SIZE=2 VALUE=\"";

      if (!$twentyfourhour_format && ($end_hour > 12))  echo ($end_hour - 12);
      else echo $end_hour;

      echo "\" MAXLENGTH=2 /></td><td>:</td><td><INPUT NAME=\"end_minute\" SIZE=2 VALUE=\"".$end_min."\" MAXLENGTH=2 />";
      if (!$twentyfourhour_format)
    {
      $checked = ($end_hour < 12) ? "checked" : "";
      echo "<INPUT NAME=\"ampm\" type=\"radio\" value=\"am\" $checked />".date("a",mktime(1,0,0,1,1,1970));
      $checked = ($end_hour >= 12) ? "checked" : "";
      echo "<INPUT NAME=\"ampm\" type=\"radio\" value=\"pm\" $checked />".date("a",mktime(13,0,0,1,1,1970));
    }
      echo "</TD>";
    }
  echo "</TR></table>\n</td></tr>";

}

// Option de réservation
if (($delais_option_reservation > 0)
    and (($modif_option_reservation == 'y')
     or ((($modif_option_reservation == 'n')
          and ($option_reservation!=-1)) ) ))
{
  $day   = date("d");
  $month = date("m");
  $year  = date("Y");
  echo "<TR bgcolor=\"#FF6955\"><TD class=\"E\"><B>".get_vocab("reservation_a_confirmer_au_plus_tard_le");

  if ($modif_option_reservation == 'y')
    {
      echo "<SELECT name=\"option_reservation\" size=\"1\">\n";
      $k = 0;
      $selected = 'n';
      $aff_options = "";
      while ($k < $delais_option_reservation+1)
    {
      $day_courant = $day+$k;
      $date_courante = mktime(0,0,0,$month,$day_courant,$year);
      $aff_date_courante = time_date_string_jma($date_courante,$dformat);
      $aff_options .= "<option value = \"".$date_courante."\" ";
      if ($option_reservation == $date_courante)
        {
          $aff_options .= " selected ";
          $selected = 'y';
        }
      $aff_options .= ">".$aff_date_courante."</option>\n";
      $k++;
    }
      echo "<option value = \"-1\">".get_vocab("Reservation confirmee")."</option>\n";
      if (($selected == 'n') and ($option_reservation != -1))
    {
      echo "<option value = \"".$option_reservation."\" selected>".time_date_string_jma($option_reservation,$dformat)."</option>\n";
    }
      echo $aff_options;
      echo "</select>";
    }
  else
    {
      echo "<input type=\"hidden\" name=\"option_reservation\" value=\"".$option_reservation."\" />&nbsp;<b>".
        time_date_string_jma($option_reservation,$dformat)."</b>\n";
      echo "<br /><input type=\"checkbox\" name=\"confirm_reservation\" value=\"y\" />".get_vocab("confirmer reservation")."\n";
    }
  echo "<br />".get_vocab("avertissement_reservation_a_confirmer")."</B>\n";
  echo "</TD></TR>\n";

}

// create area selector if javascript is enabled as this is required
// if the room selector is to be updated.

echo "<tr ";
if ($nb_areas == 1) echo "style=\"display:none\" ";
echo "><td class=E><b>".get_vocab("match_area").get_vocab("deux_points")."</b></td></TR>\n";
echo "<tr ";
if ($nb_areas == 1) echo "style=\"display:none\" ";
echo "><td class=\"CL\" valign=\"top\" >\n";
  echo "<select id=\"areas\" name=\"areas\" onChange=\"
		InsertChampsRoom('div_room','areas',".$room.",".$id.");
		InsertChampsTypes('div_types','areas',".$room.",'".$type."'); 
		InsertChampsAdd('div_champs_add','areas',".$id.",".$room.");
		InsertChampsUh('div_champs_uh','areas','".$uh."');\" readonly>";

    // get list of areas

   if ($enable_periods == 'y')
        $res = $Aghate->GetServiceInfoByServiceId($area);
    else
        $res = $Aghate->GetAreaInfoByServiceIdEnable();
	
	$nb_info = count($res);
	$row = $res;
    if ($res)
    for ($i = 0; $i<$nb_info; $i++)
   {
     if (authUserAccesArea(getUserName(),$row[$i]['id'])==1) {

       $selected = "";
       if ($row[$i]['id'] == $area) $selected = "SELECTED";
       print "<option ".$selected." value=\"".$row[$i]['id']."\">".$row[$i]['service_name']."</option>\n";
     }
   }
echo "</select>";
?>

<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
var i = 0;
</SCRIPT>

<?php

/*echo "<span onClick=\"
		InsertNewSej('test_'+i);
		i++;\"
		> test </span>\n";
echo "</td></tr>\n";
*/
?>
<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
InsertChampsUh('div_champs_uh','areas','<?php print $uh; ?>');
</SCRIPT>
<?php

/*=====================================
 * TEST
 * ==============================*/
/*echo "<tr><td class=\"E\"><b>test : </b></td></TR>\n";
echo "<TR><td class=\"CL\" valign=\"top\"><table border=0><tr><td>

<div> test </div>

</td></tr></table>\n";
echo "</td></tr>\n";*/


// *****************************************
// Edition de la partie ressources
// *****************************************

echo "\n<!-- ************* Ressources edition ***************** -->\n";

echo "<tr><td class=\"E\"><b>".get_vocab("rooms").get_vocab("deux_points")."</b></td></TR>\n";
echo "<TR><td class=\"CL\" valign=\"top\"><table border=0><tr><td>

<div id=\"div_room\"></div>

</td></tr></table>\n";
echo "</td></tr>\n";
echo "<tr><TD class=\"E\"><div id=\"div_types\">";
// Ici, on insère tous ce qui concerne les types avec de l'ajax !
echo "</div></td></tr>";
	echo "<tr><td class='E'><b> Médecin".get_vocab('deux_points')."</b><select name=\"medecin\" id=\"medecin\">";
	$res = $Aghate->GetMedecinInfoByUrm($urm);
	$nb_info_med = count($res);
	$row = $res;
	if ($res) for ($i = 0; $i<$nb_info_med; $i++)
	{
	  $selected = "";
		$c_med=$row[$i]['nom']." " .$row[$i]['prenom'];
	  if ($c_med == $med) $selected = "SELECTED";
	  echo "<option $selected value=\"".$c_med."\">".$c_med;
	}
	
	echo "</TD></TR>\n";
	
	echo "<TR><TD>".get_vocab("required");
/*}else{
	echo "<input type=\"hidden\" name=\"medecin\" value=\"ND\">";
}*/
// au chargement de la page, on affiche les champs additionnels et les types après que l'id 'areas' ait été définie.
?>
<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
InsertChampsRoom("div_room",'areas','<?php print $room; ?>','<?php print $id; ?>');	
InsertChampsTypes("div_types",'areas',<?php print $room; ?>,'<?php print $type; ?>');
</SCRIPT>
<?php

echo "</TD></TR>\n";

// Fin du tableau de la page de gauche
echo "</table>\n";
// Fin de la colonne de gauche
echo "</td>\n";
// Début colonne de droite
echo "<td valign=\"top\">\n";
// Début tableau de la colonne de droite
echo "<table width=\"100%\">";
echo "<div id=\"div_erreurs\"></div>";
echo "<div id=\"div_champs_add\">";

?>
<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
InsertChampsAdd('div_champs_add','areas',<?php print $id; ?>,<?php print $room; ?>);
</SCRIPT>
<?php

echo "</div>";
// on récupère la liste des domaines et on génère tous les formulaires.
//$sql = "select id from agt_service;";
//$res = grr_sql_query($sql);

// Dans le cas d'une nouvelle réservation, ou bien si on édite une réservation existante

// *****************************************
// Edition de la partie périodique
//
// *****************************************
echo "\n<!-- ************* Periodic edition ***************** -->\n";
// Tableau des "une semaine sur n"
$weeklist = array("unused","every week","week 1/2","week 1/3","week 1/4","week 1/5");
/*
Explications sur les différents cas de périodicité:
$rep_type = 0 -> Aucune périodicité
$rep_type = 1 -> Chaque jour (sélectionné)
$rep_type = 2 -> "Une semaine sur n". La valeur "n" est alors enregistrée dans $rep_num_weeks
$rep_type = 3 -> Chaque mois, la même date
$rep_type = 5 -> Chaque mois, même jour de la semaine
Attention : dans le formualaire de réservation, les deux cas $rep_type = 3 et $rep_type = 5
sont regroupés dans une liste déroulante correspondant au cas $i = 3 ci-dessous
$rep_type = 4 -> Chaque année, même date
$rep_type = 6 -> Jours cycle
*/

if($edit_type == "series")
{
	
	// multiple réservation option bloqued by mohan 
	/*
  echo "
    <TR>
       <TD id=\"ouvrir\" style=\"cursor: inherit\" onClick=\"clicMenu('1');check_5()\" align=center class=\"fontcolor4\">
       <span class=\"bground\"><B><a href='#'>".get_vocab("click_here_for_series_open")."</a></B></span>".grr_help("aide_grr_periodicite")."
       </TD>
       </TR>
       <TR>
       <TD style=\"display:none; cursor: inherit\" id=\"fermer\" onClick=\"clicMenu('1');check_5()\" align=center class=\"fontcolor4\">
       <span class=\"bground\"><B><a href='#'>".get_vocab("click_here_for_series_close")."</a></B></span>".grr_help("aide_grr_periodicite")."
       </TD>
    </TR>
    ";
    */

  echo "<TR><TD><TABLE border=0 width=100%>\n ";

  echo "<TR><TD><TABLE border=0 style=\"display:none\" id=\"menu1\" width=100%>\n ";
  echo "<TR><TD CLASS=F><B>".get_vocab("rep_type")."</B></TD></TR><TR><TD CLASS=CL>\n";


  echo "<table border=0  width=100% >\n";
  //Vérifie si le jour cycle est activé ou non
  if (getSettingValue("jours_cycles_actif") == "Oui") $max = 7; //$max = 7 Pour afficher l'option Jour cycle dans les péridocidités
  else $max = 6;                                                //$max = 6 Pour ne pas afficher l'option Jour cycle dans les péridocidités
  for($i = 0; $i<$max ; $i++)
    {
      if ($i != 5) // Le cas rep_type = 5 (chaque mois, même jour de la semaine)  est traité plus bas comme un sous cas de $i = 3
    {
      echo "<TR><TD><INPUT NAME=\"rep_type\" TYPE=\"radio\" VALUE=\"" . $i . "\"";
      if($i == $rep_type) echo " CHECKED";
      // si rep_type = 5 (chaque mois, même jour de la semaine), on sélectionne l'option 3
      if(($i == 3) and ($rep_type==5)) echo " CHECKED";
      echo " ONCLICK=\"check_1()\" /></td><td>";
      // Dans le cas des semaines et des mois, on affichera plutôt un menu déroulant
      if (($i != 2) and ($i != 3))  echo get_vocab("rep_type_$i");
      echo "\n";
      // Dans le cas d'une périodicité semaine, on précise toutes les n-semaines
      if ($i == '2')
        {
          echo "<select name=\"rep_num_weeks\" size=\"1\" onfocus=\"check_2()\" onclick=\"check_2()\">\n";
          echo "<option value=1 >".get_vocab("every week")."</option>\n";
          for ( $weekit=2 ; $weekit<6 ; $weekit++ )
        {
          echo "<option value=$weekit ";
          if ($rep_num_weeks == $weekit) echo " selected";
          echo ">".get_vocab($weeklist[$weekit])."</option>\n";
        }
          echo "</select></td></tr>\n";

        }
      if ($i == '3')
        {
          $monthrep3 = "";
          $monthrep5 = "";
          if ($rep_type == 3) $monthrep3 = " selected ";
          if ($rep_type == 5) $monthrep5 = " selected ";

          echo "<select name=\"rep_month\" size=\"1\" onfocus=\"check_3()\" onclick=\"check_3()\">\n";
          echo "<option value=3 $monthrep3>".get_vocab("rep_type_3")."</option>\n";
          echo "<option value=5 $monthrep5>".get_vocab("rep_type_5")."</option>\n";
          echo "</select></td></tr>\n";
        }
    }

    }

  echo "</td></tr></table>\n\n";
  echo "<!-- ***** Fin de périodidité ***** -->\n";

  echo "</TD></TR>";
  echo "\n<TR><TD>\n";

  echo "<TR><TD CLASS=F><B>".get_vocab("rep_end_date")."</B></TD></TR>\n";

  echo "<TR><TD CLASS=CL>";
  genDateSelector("rep_end_", $rep_end_day, $rep_end_month, $rep_end_year,"");
  echo "</TD></TR></table>\n";

  // Tableau des jours de la semaine à cocher si on choisit une périodicité "une semaine sur n"
  echo "<TABLE style=\"display:none\" id=\"menu2\" width=100%>\n";
  echo "<TR><TD CLASS=F><B>".get_vocab("rep_rep_day")."</B></TD></TR>\n";
  echo "<TR><TD CLASS=CL>";
  //Affiche les checkboxes du jour en fonction de la date de début de semaine.
  for ($i = 0; $i < 7; $i++)
    {
      $wday = ($i + $weekstarts) % 7;
      echo "<INPUT NAME=\"rep_day[$wday]\" TYPE=checkbox";
      if ($rep_day[$wday]) echo " CHECKED";
      echo " ONCLICK=\"check_1()\" />" . day_name($wday) . "\n";
    }
  echo "</TD></TR>\n</TABLE>\n";

  // Tableau des jours cycle à cocher si on choisit une périodicité "Jours Cycle"
  echo "<TABLE style=\"display:none\" id=\"menuP\" width=100%>\n";
  echo "<TR><TD CLASS=F><B>Jours/Cycle</B></TD></TR>\n";
  echo "<TR><TD CLASS=CL>";
  // Affiche les checkboxes du jour en fonction du nombre de jour par jours/cycles
  for ($i = 1; $i < (getSettingValue("nombre_jours_Jours/Cycles")+1); $i++) {
      $wday = $i;
      echo "<input type=\"radio\" name=\"rep_jour_\" value=\"$wday\"";
      if(isset($jours_c)) { if ($i == $jours_c) echo " CHECKED"; } //$jours_c supprimé
      echo " ONCLICK=\"check_1()\" />".get_vocab("rep_type_6")." ".$wday. "\n";
  }
  echo "</TD></TR>\n</TABLE>\n";
} else {
// On affiche les informations liées à la périodicité
  if (isset($rep_type)) {
    echo '<tr><td class="E"><b>'.get_vocab('periodicite_associe').get_vocab('deux_points').'</b></td></tr>';
    if ($rep_type == 2)
        $affiche_period = get_vocab($weeklist[$rep_num_weeks]);
    else
        $affiche_period = get_vocab('rep_type_'.$rep_type);

    echo '<tr><td class="E"><b>'.get_vocab('rep_type').'</b> '.$affiche_period.'</td></tr>';
    if($rep_type != 0) {
        $opt = '';
        if ($rep_type == 2) {
            $nb = 0;
            //Affiche les checkboxes du jour en fonction de la date de début de semaine.
            for ($i = 0; $i < 7; $i++) {
                $wday = ($i + $weekstarts) % 7;
                if ($rep_opt[$wday]) {
                    if ($opt != '') $opt .=', ';
                    $opt .= day_name($wday);
                    $nb++;
                 }
            }
        }
        if ($rep_type == 6) {
            $nb = 1;
            //Affiche le jour cycle.
      			$opt .= get_vocab('jour_cycle').' '.$jours_c;
        }
        if($opt)
            if ($nb == 1)
                echo '<tr><td class="E"><b>'.get_vocab('rep_rep_day').'</b> '.$opt.'</td></tr>';
            else
                echo '<tr><td class="E"><b>'.get_vocab('rep_rep_days').'</b> '.$opt.'</td></tr>';
        if($enable_periods=='y') list( $start_period, $start_date) =  period_date_string($start_time);
        else $start_date = time_date_string($start_time,$dformat);
        $duration = $end_time - $start_time;
        if ($enable_periods=='y') toPeriodString($start_period, $duration, $dur_units);
        else toTimeString($duration, $dur_units);

        echo '<tr><td class="E"><b>'.get_vocab("date").get_vocab("deux_points").'</b> '.$start_date.'</td></tr>';
        echo '<tr><td class="E"><b>'.get_vocab("duration").'</b> '.$duration .' '. $dur_units.'</td></tr>';

        echo '<tr><td class="E"><b>'.get_vocab('rep_end_date').'</b> '.$rep_end_date.'</td></tr>';
    }
  } else {
   // echo '<tr><td class="E"><b>'.get_vocab('aucune_periodicite_associe').'</b></td></tr>';
  }
}
// Fin du tableau de la colonne de droite
echo "</TABLE>\n";
//$sexe_attend="M";
include ("reprogram_div_top.php");
?>
<center>
<div id="fixe">
<INPUT TYPE="button" VALUE="<?php echo get_vocab("cancel")?>" ONCLICK="window.location.href='<?php echo $page.".php?year=".$year."&amp;month=".$month."&amp;day=".$day."&amp;area=".$area."&amp;room=".$room; ?>'" />
<INPUT TYPE="button" VALUE="<?php echo get_vocab("save")?>" ONCLICK="Save_entry();validate_and_submit()" />
</div>
</center>
<INPUT TYPE="hidden" NAME="sexe_attend"    VALUE="<?php echo $sexe_attend?>" />
<INPUT TYPE="hidden" NAME="rep_id"    VALUE="<?php echo $rep_id?>" />
<INPUT TYPE="hidden" NAME="edit_type" VALUE="<?php echo $edit_type?>" />
<INPUT TYPE="hidden" NAME="page" VALUE="<?php echo $page?>" />
<INPUT TYPE="hidden" NAME="room_back" VALUE="<?php echo $room_id?>" />
<INPUT TYPE="hidden" NAME="noip"    VALUE="<?php echo $noip?>" />
<INPUT TYPE="hidden" NAME="nom" VALUE="<?php echo $nom?>" />
<INPUT TYPE="hidden" NAME="prenom" VALUE="<?php echo $prenom?>" />
<INPUT TYPE="hidden" NAME="ddn" VALUE="<?php echo $ddn?>" />
<INPUT TYPE="hidden" NAME="sexe" VALUE="<?php echo $sexe?>" />
<INPUT TYPE="hidden" NAME="njf" VALUE="<?php echo $njf?>" />
<INPUT TYPE="hidden" NAME="tel" VALUE="<?php echo $njf?>" />
<INPUT TYPE="hidden" NAME="id_prog" VALUE="<?php echo $id_prog?>" />

<?php
if ($flag_qui_peut_reserver_pour == "no") {
    echo "<input type=\"hidden\" name=\"beneficiaire\" value=\"$beneficiaire\" />";
}
echo "<input type=\"hidden\" name=\"create_by\" value=\"".$create_by."\" />";
if ($id!=0) echo "<INPUT TYPE=hidden NAME=\"id\" VALUE=\"$id\" />\n";
echo "<INPUT TYPE=hidden NAME=\"type_affichage_reser\" VALUE=\"$type_affichage_reser\" />\n"; ?>
</FORM>

<script type="text/javascript" language="JavaScript">
document.main.name.focus();
<?php
if ($id <> "") echo "clicMenu('1'); check_5();\n";
// Si Err=yes il faut recharger la saisie après 1/2 seconde d'attente
if (isset($Err) and $Err=="yes") echo "timeoutID = window.setTimeout(\"Load_entry();check_5();\",500);\n";
?>
</script>

<?php //include "./commun/include/trailer.inc.php" ?>
