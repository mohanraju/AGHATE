<?php 
	include "./config/config.php";
	include "./config/config.inc.php";
	include "./commun/include/functions.inc.php";
	include "./commun/include/$dbsys.inc.php";
	include "./commun/include/mrbs_sql.inc.php";
	include "./commun/include/misc.inc.php";
	include "./commun/include/funtions.inc2.php";	
	if (count($_POST)) {
		while (list($key, $val) = each($_POST)) {
			$$key = $val;
		}
	}
	if (count($_GET)) {
		while (list($key, $val) = each($_GET)) {
			$$key = $val;
		}
	}
	
	
	$grr_script_name = "edit_rdv.php";
	// Settings
	$err=false;
	require_once("./commun/include/settings.inc.php");
	//Chargement des valeurs de la table settingS
	if (!loadSettings())
	    die("Erreur chargement settings");
	
	// Session related functions
	require_once("./commun/include/session.inc.php");
	
	if (!grr_resumeSession()) {
	    header("Location: ./logout.php?auto=1");
	    die();
	};
	

	include "./commun/include/language.inc.php";
	//=====================================
	// mode operation Ajoute ou modify
	//=====================================
	if ($id==0){
		// Mode Adjoute  ***********************
	    $entry_mode = get_vocab("addentry");
		//Area
		if(empty($area))  $area = get_default_area();
		$sql = "select id, service_name from agt_service where id='".$area."' order by service_name";
		$res = grr_sql_query($sql);
		$row = grr_sql_row($res, 0);
		$service_name=	$row[1];
		$service_id=$row[0];
		get_planning_area_values($area);
	
		 if(authGetUserLevel(getUserName(),-1) < 1)
		{
		    echo "<H2>".get_vocab("accessdenied")."</H2>";
		    exit();
		}
		
		// rooms
		$sql = "select id, room_name, description from agt_room where id=$room";		
		$res = grr_sql_query($sql);
		$row = grr_sql_row($res, 0);
		$room_name=$row[1];
		$room_id=$row[0];
		//utilisateur
	    $beneficiaire   = getUserName();
	    $sql = "SELECT DISTINCT login, nom, prenom FROM agt_utilisateurs WHERE  login='".$beneficiaire."'";
	    $res = grr_sql_query($sql);
	    $row = grr_sql_row($res, $i);
	    //patient (sexe=type)
		$type=isset($type) ? $type : "M";
		
		//If we dont know the right date then make it up
		if(!isset($day) or !isset($month) or !isset($year))
		{
		    $day   = date("d");
		    $month = date("m");
		    $year  = date("Y");
		}
	
	        
	    //date monthselector
	    $start_day   = $day;
	    $start_month = $month;
	    $start_year  = $year;
	    $start_hour  = $hour;
	    (isset($minute)) ? $start_min = $minute : $start_min ='00';
	    //$duration = $duree_par_defaut_reservation_area ;    
	    $dtm_selector = genDateSelectorForm("", $start_day, $start_month, $start_year,"");    
	   $option_reservation = -1;     	
			    
	    
	}else{
		// Mode Modify  ***********************
       $entry_mode = get_vocab("editentry");
   }
	//------------------------------------
	//ENREGISTERER
	//------------------------------------
	if (isset($ENREG)){	
		$errmsg="";
		if (isset($id)) {
		    settype($id,"integer");
		} else{
		    $id = NULL;
		}
		if (isset($hour)) {
		    settype($hour,"integer");
		    if ($hour > 23) $hour = 23;
		}else{
			$errmsg .="Heur invalide<br />";
		}
		if (isset($minute)) {
		    settype($minute,"integer");
		    if ($minute > 59) $hour = 59;
		}else{
			$errmsg .="Minute invalide<br />";
		}

		//utilisateur
		if (($beneficiaire) == "") {
		        $errmsg .="Utlisateur : obligatoire <br />";
		        $err=true;
		 }
		//patient
		if (($patient) == "") {
		        $errmsg .="Patient obligatoire <br />";
		        $err=true;
		 }
		//Protocole
		if (($protocole) == "") {
		        $errmsg .="Protocole obligatoire <br />";
		        $err=true;
		 }

		if (!isset($room_id)) {
		       $errmsg .="Salle/lit obligatoire <br />";
		}
	
		// Récupération des données concernant l'affichage du planning du domaine
		if (check_begin_end_bookings($day, $month, $year))
		{
		    if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) $type_session = "no_session";
		    else $type_session = "with_session";
		     echo "<H2>NoBookings</H2>";
		    exit();
		}
	
		if ($type_affichage_reser == 0) {
		    // La fin de réservation est calculée à partir d'une durée
		
		    // Units start in seconds
		    $units = 1.0;
			//convert durée unites ves secondes ??
		    switch($dur_units)
		    {
		        case "years":
		            $units *= 52;
		        case "weeks":
		            $units *= 7;
		        case "days":
		            $units *= 24;
		        case "hours":
		            $units *= 60;
		        case "periods":
		        case "minutes":
		            $units *= 60;
		        case "seconds":
		           break;
		    }
		    // Units are now in "$dur_units" numbers of seconds
		   if(isset($all_day) && ($all_day == "yes") && ($dur_units!="days")) {
		        if($enable_periods=='y') {
		            $starttime = mktime(12, 0, 0, $month, $day, $year);
		            $endtime   = mktime(12, $max_periods, 0, $month, $day, $year);
		        } else {
		      		$starttime = mktime($morningstarts, 0, 0, $month, $day  , $year);
		      		$endtime   = mktime($eveningends, 0, $resolution, $month, $day, $year);
		        }
		    } else {
				if (!$twentyfourhour_format){
		      	if (isset($ampm) && ($ampm == "pm")){
		         	$hour += 12;
		         }
				}
		      $starttime = mktime($hour, $minute, 0, $month, $day, $year);
		      $endtime   = mktime($hour, $minute, 0, $month, $day, $year) + ($units * $duration);
		       if ($endtime <= $starttime)
		           $errmsg .="Erreur durée <br />";
		
		       # Round up the duration to the next whole resolution unit.
		       # If they asked for 0 minutes, push that up to 1 resolution unit.
		       $diff = $endtime - $starttime;
		
				if (!isset($resolution))$resolution=1;
		        if (($tmp = $diff % $resolution) != 0 || $diff == 0)
		            $endtime += $resolution - $tmp;
		
		   }
		}

		$rep_type = 0; //périodecité à garder
		$rep_day = "";
	
		// Dans le cas d'une réservation sans périodicité, on teste si la résa tombe un jour "hors réservation"
		// On définit les jours temps "minuit" de début et fin
		$day_temp   = date("d",$starttime);
		$month_temp = date("m",$starttime);
		$year_temp  = date("Y",$starttime);
		$starttime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
		$day_temp   = date("d",$endtime);
		$month_temp = date("m",$endtime);
		$year_temp  = date("Y",$endtime);
		$endtime_midnight = mktime(0, 0, 0, $month_temp, $day_temp, $year_temp);
	
	
		// Si tous les tests précédents sont passés avec succès :
		if (strlen($errmsg)< 1){
	    	$entry_type = 0;
			$repeat_id=0;
   		$sql = "INSERT INTO agt_loc (start_time,   end_time,   	entry_type,    						repeat_id,   room_id, 	create_by, 										beneficiaire, 									beneficiaire_ext, 								name, 										type, 													description, 			statut_entry,option_reservation,								overload_desc,protocole)
	                            VALUES ($starttime, 	$endtime, '".protect_data_sql($entry_type)."', $repeat_id, '$room_id','".protect_data_sql($beneficiaire)."', '".protect_data_sql($beneficiaire)."', '".protect_data_sql($beneficiaire_ext)."', '".protect_data_sql($patient)."', '".protect_data_sql($type)."', '".protect_data_sql($description)."', '-',		 '".$option_reservation."','".protect_data_sql($overload_data_string)."','".protect_data_sql($protocole)."')";
echo $sql;	                            
	    //if (grr_sql_command($sql) < 0) return 0;
		//$new_id = grr_sql_insert_id("agt_loc", "id");
		
		}
	

	}// fin if enreg=y
?>
	 <script type="text/javascript" language="JavaScript">
	function popup_patient(number) {
		 whichOne = number;

		 champ_du_owner="name_pat";
		 pat=document.getElementById(champ_du_owner).value;
	    mywindow=open('recherche_pat.php?champ_du_owner='+champ_du_owner+'&nom='+pat,'myname','resizable=yes,width=780,height=450,status=yes,scrollbars=yes');
	    mywindow.location.href = 'recherche_pat.php?champ_du_owner='+champ_du_owner+'&nom='+pat;
	    if (mywindow.opener == null) mywindow.opener = self;
	}
	</script>
	<style type="text/css">
<!--
.Style1 {color: #FF0000}
-->
    </style>
		
<h2><?php print $entry_mode;?></H2>    

<table width="100%" border="1" cellspacing="1" cellpadding="1">
  
  <tr>
    <td> <form name="main">
    <table width="52%" border="0" cellspacing="1" cellpadding="1" class="EditEntryTable">
      <tr>
        <td colspan="2" class="E Style1"> <?php print $errmsg;?></td>
        </tr>
      <tr>
        <td class="E"><strong>Reservation par</strong></td>
        <td><input type="text" name="beneficiaire"   value="<?php print $beneficiaire;?>" readonly ></td>
      </tr>
      <tr>
        <td><strong>Service</strong></td>
        <td><input type="text" name="service_name"   value="<?php print $service_name;?>"  readonly ></td>
        <input type="hidden" name="area"   value="<?php print $area;?>">
      </tr>
      <tr>
        <td><strong>Salle/Lit</strong></td>
        <td><input type="text" name="room_name"   value="<?php print $room_name;?>"  readonly></td>
        <input type="hidden" name="room"   value="<?php print $room;?>">        
      </tr>
      <tr>
        <td><strong>Patient</strong></td>
        <td><input type="text" name="patient" value="<?php print $patient;?>" >
          <img src="./commun/images/details.png" width="20" height="19">
          <input type="hidden" name="type" value="<?php print $type;?>" /></td>
      </tr>
      <tr>
        <td><strong>Protocole</strong></td>
        <td><input type="text" name="protocole" value="<?php print $protocole;?>">
          <img src="./commun/images/details.png" alt="" width="20" height="19"></td>
      </tr>
      <tr>
        <td colspan="2"> <strong>D&eacute;but de la r&eacute;servation&nbsp;: 
        <?php print $dtm_selector; ?>
        &agrave;&nbsp;</strong> 

	  <?php
	  echo "<INPUT NAME=\"hour\" SIZE=2 VALUE=\"";
	  if (!$twentyfourhour_format && ($start_hour > 12)) echo ($start_hour - 12);
	  else echo $start_hour;
	
	  echo "\" MAXLENGTH=2 />:<INPUT NAME=\"minute\" SIZE=2 VALUE=\"".$start_min."\" MAXLENGTH=2 />";
	   ?>               
      <tr>
        <td colspan="2"><strong>Dur&eacute;e:</strong>

	 <INPUT NAME="duration" SIZE="3" VALUE="<?php print $duration;?>" /> 
	 <Input type="hidden" name="dur_units" VALUE="minutes">minute(s)          </td>
        </tr>
      <tr>
        <td colspan="2"><strong>Description compl&egrave;te (facultative) :<br />
            <textarea name="description" cols="60"><?php print $description;?></textarea>
        </strong>
        <INPUT TYPE="hidden" NAME="option_reservation" VALUE="<?php print $option_reservation;?>" />        </td>
        </tr>
      <tr><td colspan="2" align="center"><input type="submit" name="ENREG"  value="Energisterer" /> 

      
      </td></tr>
    </table>
    </form>
    
    </td>
    <td>
    <?php 
    //===========================================
    // les patients récherche dans base local
    //=============================================
   echo "<div id ='patients_local'>";
   include("recherche_pat.php");
   echo " </div>";
    
    
    
    ?></td>
  </tr>
</table>
