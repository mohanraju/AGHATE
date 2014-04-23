<?php
include "./commun/include/admin.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
$grr_script_name = "edit_entry.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";




// Initialisation
if (isset($_GET["id"])){
  $id = $_GET["id"];
  settype($id,"integer");
}else{
	$id = NULL;;
}

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

// Récupération des données concernant l'affichage du planning du domaine
get_planning_area_values($area);

// Récupération d'info sur la rerssource
$type_affichage_reser = grr_sql_query1("select type_affichage_reser from agt_room where id='".$room."'");
$delais_option_reservation  = grr_sql_query1("select delais_option_reservation from agt_room where id='".$room."'");
$qui_peut_reserver_pour  = grr_sql_query1("select qui_peut_reserver_pour from agt_room where id='".$room."'");

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
    $sql = "select name, beneficiaire, description, start_time, end_time,
            type, room_id, entry_type, repeat_id, option_reservation, jours, create_by, beneficiaire_ext from agt_loc where id=$id";
    $res = grr_sql_query($sql);
    if (! $res) fatal_error(1, grr_sql_error());
    if (grr_sql_count($res) != 1) fatal_error(1, get_vocab('entryid') . $id . get_vocab('not_found'));
    $row = grr_sql_row($res, 0);
    grr_sql_free($res);
    $breve_description        = $row[0];
    $beneficiaire   = $row[1];
    $beneficiaire_ext   = $row[12];
    $tab_benef = donne_nom_email($beneficiaire_ext);
    $create_by    = $row[11];
    $description = $row[2];

    $start_day   = strftime('%d', $row[3]);
    $start_month = strftime('%m', $row[3]);
    $start_year  = strftime('%Y', $row[3]);
    $start_hour  = strftime('%H', $row[3]);
    $start_min   = strftime('%M', $row[3]);

    $end_day   = strftime('%d', $row[4]);
    $end_month = strftime('%m', $row[4]);
    $end_year  = strftime('%Y', $row[4]);
    $end_hour  = strftime('%H', $row[4]);
    $end_min   = strftime('%M', $row[4]);


    $duration    = $row[4]-$row[3];
    $type        = $row[5];
    $room_id     = $row[6];
    $entry_type  = $row[7];
    $rep_id      = $row[8];
    $option_reservation  = $row[9];
    $jours_c = $row[10];
    $modif_option_reservation = 'n';
    if($entry_type >= 1)
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

    }
}
else
{
  //Ici, c'est une nouvelle réservation, les donnée arrivent quelque soit le boutton selectionné.
    if ($enable_periods == 'y')
        $duration    = 60;
    else {
        $duree_par_defaut_reservation_area = grr_sql_query1("select duree_par_defaut_reservation_area from agt_service where id='".$area."'");
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
$sql = "select id, service_name from agt_service";
$res = grr_sql_query($sql);
$allareas_id = array();
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
  array_push($allareas_id,$row[0]);
  if (authUserAccesArea(getUserName(),$row[0])==1)
    {

      $nb_areas++;
    }
}
// Utilisation de la bibliothèqye prototype dans ce script
$use_prototype = 'y';
//print_header($day, $month, $year, $area);
?>
<script type="text/javascript" src="./commun/js/functions.js" language="javascript"></script>

<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
function insertChampsAdd(){
    new Ajax.Updater($('div_champs_add'),"edit_entry_champs_add.php",{method: 'get', parameters: $('areas').serialize(true)+'&id=<?php echo $id; ?>&room=<?php echo $room; ?>'});
}
function insertTypes(){
    new Ajax.Updater($('div_types'),"edit_entry_types.php",{method: 'get', parameters: $('areas').serialize(true)+'&type=<?php echo $type; ?>&room=<?php echo $room; ?>'});
    
    
}

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
      $overload_fields = $Aghate->OverloadGetFieldslist($idtmp);
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
  if  (document.forms["main"].type.value=='0')
  {
     alert("<?php echo get_vocab("choose_a_type"); ?>");
     return false;
  }

    <?php
    if($edit_type == "series1")
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
// would be nice to also check date to not allow Feb 31, etc...
   document.forms["main"].submit();
  return true;
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
$sql = "select service_id from agt_room where id=$room_id";
$res = grr_sql_query($sql);
$row = grr_sql_row($res, 0);
$service_id = $row[0];

// Détermine si la ressource est moderée
$moderate = grr_sql_query1("select moderate from agt_room where id='".$room_id."'");
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
        $sql = "select id, service_name from agt_service where id='".$area."' order by service_name";
    else
        $sql = "select id, service_name from agt_service where enable_periods != 'y' order by service_name";
    $res = grr_sql_query($sql);

    if ($res)
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    if (authUserAccesArea(getUserName(),$row[0])==1)
      {
        print "      case \"".$row[0]."\":\n";
        // get rooms for this area
        $sql2 = "select id, room_name from agt_room where service_id='".$row[0]."' order by room_name";
            $res2 = grr_sql_query($sql2);

        if ($res2) for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++)
        print "        roomsObj.options[$j] = new Option(\"".str_replace('"','\\"',$row2[1])."\",".$row2[0] .")\n";


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
	 champ_du_owner="name_pat";
	 champ_du_owner_ok="name_pat_ok";	 
alert(champ_du_owner);	 
	 pat=document.getElementById(champ_du_owner).value;
	 pat_ok=document.getElementById(champ_du_owner_ok).value;
    mywindow=open('recherche_pat.php?champ_du_owner='+champ_du_owner+'&nom='+pat+'&pat_ok='+pat_ok,'myname','resizable=yes,width=500,height=670,status=yes,scrollbars=yes');
    mywindow.location.href = 'recherche_pat.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
    if (mywindow.opener == null) mywindow.opener = self;
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
//************* REservation par 
if(((authGetUserLevel(getUserName(),-1,"room") >= $qui_peut_reserver_pour) or (authGetUserLevel(getUserName(),$area,"area") >= $qui_peut_reserver_pour))
 and (($id == 0) or (($id!=0) and ($create_by==getUserName()) )))
 {
    $flag_qui_peut_reserver_pour = "yes";
    echo "<TR><TD class=\"E\"><B>".ucfirst(trim(get_vocab("reservation au nom de"))).get_vocab("deux_points").strtolower($beneficiaire).grr_help("aide_grr_effectuer_reservation","modifier_reservant")."</B> </TD></TR>";
    
    if ($tab_benef["nom"] != "")
        echo "<tr id=\"menu4\"><td>";
    else
        echo "<tr style=\"display:none\" id=\"menu4\"><td>";
    echo get_vocab("nom beneficiaire")." *".get_vocab("deux_points");
    echo "<input type=\"text\" name=\"benef_ext_nom\" value=\"".htmlspecialchars($tab_benef["nom"])."\" size=\"20\" />";
   
    if (getSettingValue("automatic_mail") == 'yes') {
        echo "&nbsp;".get_vocab("email beneficiaire").get_vocab("deux_points")."<input type=\"text\" name=\"benef_ext_email\" value=\"".htmlspecialchars($tab_benef["email"])."\" size=\"20\" />";
    }
    echo "</TD></TR>\n";
} else{    
	$flag_qui_peut_reserver_pour = "no";
}
echo "<TR><TD class=\"E\"><B>$B</B>

<INPUT NAME=\"name\" ID=\"name_pat\" SIZE=\"15\" VALUE=\"$C\" /> ";

echo "<a href=\"#\"  onClick=\"popup_patient(1)\" >recherche </a>  ";
echo "</TD></TR> 
<TR><TD class=\"CL\"><INPUT NAME=\"name\" ID=\"name_pat_ok\" SIZE=\"80\" VALUE=\"$C\" /> ";


echo "</TD></TR>
<TR><TD class=\"E\"><B>$D</B></TD></TR>
<TR><TD class=\"TL\"><TEXTAREA name=\"description\" rows=\"2\" cols=\"80\">$E</TEXTAREA>";
echo "<div id=\"div_champs_add\">";
// Ici, on insère tous ce qui concerne les champs additionnels avec de l'ajax !
echo "</div>";
echo "</TD></TR>\n";
// Début réservation

echo "<TR><TD class=\"E\"><B>$F</B></TD></TR>\n";
echo "<TR><TD class=\"CL\">";
echo "<table border = 0><tr><td>".$G;
echo "</TD><TD CLASS=E><B>";

// Heure ou créneau de début de réservation
if ($enable_periods=='y')
{
  echo get_vocab("period")."</B>\n";
  echo "<SELECT NAME=\"period\">";
  foreach ($periods_name as $p_num => $p_val)
    {
      echo "<OPTION VALUE=$p_num";
      if( ( isset( $period ) && $period == $p_num ) || $p_num == $start_min)
    echo " SELECTED";
      echo ">$p_val";
    }
  echo "</SELECT>\n";
}
else
{
  echo get_vocab("time")."</B></TD>\n";
  echo "<TD><INPUT NAME=\"hour\" SIZE=2 VALUE=\"";
  if (!$twentyfourhour_format && ($start_hour > 12)) echo ($start_hour - 12);
  else echo $start_hour;

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
  echo "<TR><TD class=\"CL\"><INPUT NAME=\"duration\" SIZE=\"7\" VALUE=\"".$duration."\" />";
  echo "<SELECT name=\"dur_units\" size=\"1\">\n";
  if($enable_periods == 'y') $units = array("periods", "days");
  else {
      $duree_max_resa_area = grr_sql_query1("select duree_max_resa_area from agt_service where id='".$area."'");
      if ($duree_max_resa_area < 0)
          $units = array("minutes", "hours", "days", "weeks");
      else if ($duree_max_resa_area < 60)
          $units = array("minutes");
      else if ($duree_max_resa_area < 60*24)
          $units = array("minutes", "hours");
      else if ($duree_max_resa_area < 60*24*7)
          $units = array("minutes", "hours", "days");
      else
          $units = array("minutes", "hours", "days", "weeks");
  }
  while (list(,$unit) = each($units))
    {
      echo "<OPTION VALUE=$unit";
      if ($dur_units ==  get_vocab($unit)) echo " SELECTED";
      echo ">".get_vocab($unit)."</OPTION>\n";
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

  echo "<INPUT name=\"all_day\" TYPE=\"checkbox\" value=\"yes\" />".get_vocab("all_day");
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
      echo "<OPTION VALUE=$p_num";
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
echo '><td class="CL" valign="top" >\n';
//echo "<select name=\"areas\" onChange=\"changeRooms(this.form)\" >";
  echo "<select id=\"areas\" name=\"areas\" onChange=\"changeRooms(this.form);insertChampsAdd();insertTypes()\" >";

    // get list of areas

    if ($enable_periods == 'y')
      $sql = "select id, service_name from agt_service where id='".$area."' order by service_name";
    else
      $sql = "select id, service_name from agt_service where enable_periods != 'y' order by service_name";
 $res = grr_sql_query($sql);
 if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
   {
     if (authUserAccesArea(getUserName(),$row[0])==1) {

       $selected = "";
       if ($row[0] == $area) $selected = "SELECTED";
       print "<option ".$selected." value=\"".$row[0]."\">".$row[1]."</option>\n";
     }
   }
echo "</select>\n";
echo "</td></tr>\n";

// *****************************************
// Edition de la partie ressources
// *****************************************

echo "\n<!-- ************* Ressources edition ***************** -->\n";

echo "<tr><td class=\"E\"><b>".get_vocab("rooms").get_vocab("deux_points")."</b></td></TR>\n";
echo "<TR><td class=\"CL\" valign=\"top\"><table border=0><tr><td><select name=\"rooms[]\" multiple>";
//Sélection de la "room" dans l'"area"
$sql = "select id, room_name, description from agt_room where service_id=$service_id order by order_display,room_name";
$res = grr_sql_query($sql);
if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
{
  $selected = "";
  if ($row[0] == $room_id) $selected = "SELECTED";
  echo "<option $selected value=\"".$row[0]."\">".$row[1];
}
echo "</select></td><td>".get_vocab("ctrl_click")."</td></tr></table>\n";
echo "</td></tr>\n";
echo "<tr><TD class=\"E\"><div id=\"div_types\">";
// Ici, on insère tous ce qui concerne les types avec de l'ajax !
echo "</div></td></tr>";


echo "<TR><TD>".get_vocab("required");
// au chargement de la page, on affiche les champs additionnels et les types après que l'id 'areas' ait été définie.
?>
<SCRIPT type="text/javascript" LANGUAGE="JavaScript">
insertChampsAdd();
insertTypes();
</SCRIPT>
<?php

echo "</TD></TR>\n";
// Fin du tableau de la page de gauche
echo "</table>\n";
// Fin de la colonne de gauche
		echo "</td>\n";
		// Début colonne de droite
		echo "<td valign=\"top\" id=\3PATINFO\">\n";
		// Début tableau de la colonne de droite
		echo "<table width=\"100%\">";
		echo "<TR><TD>wdfsdf</TD></TR>";
		
		echo "</TABLE>\n";
		// Fin de la colonne de droite et fin du tableau
echo "</td></tr></table>\n";
?>
<center>
<div id="fixe">
<INPUT TYPE="button" VALUE="<?php echo get_vocab("cancel")?>" ONCLICK="window.location.href='<?php echo $page.".php?year=".$year."&amp;month=".$month."&amp;day=".$day."&amp;area=".$area."&amp;room=".$room; ?>'" />
<INPUT TYPE="button" VALUE="<?php echo get_vocab("save")?>" ONCLICK="Save_entry();validate_and_submit()" />
</div>
</center>

<INPUT TYPE=hidden NAME="rep_id"    VALUE="<?php echo $rep_id?>" />
<INPUT TYPE=hidden NAME="edit_type" VALUE="<?php echo $edit_type?>" />
<INPUT TYPE=hidden NAME="page" VALUE="<?php echo $page?>" />
<INPUT TYPE=hidden NAME="room_back" VALUE="<?php echo $room_id?>" />
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

<?php include "./commun/include/trailer.inc.php" ?>
