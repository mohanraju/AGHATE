<?php
session_name('GRR');
session_start();
header('Content-type: text/html; charset=utf-8');
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");
include("./commun/include/ClassGilda.php");
include("./config/config_".$site.".php");
include("../commun/layout/header.php");
 
$com=new CommonFunctions(true);
$Html=new Html();
$Aghate = new Aghate();
$Gilda = new Gilda($ConnexionStringGILDA);

$Aghate->NomTableLoc = $table_loc;
$NomTableLoc = $table_loc;

// varibale de la page de reservation
// Listeduree unites
$ListeDureeUnits[]="H|Heure(s)";
$ListeDureeUnits[]="M|Minute(s)";
$ListeDureeUnits[]="J|Jour(s)";

$date_deb = "";
$heure_deb = "";


$Display = ""; 
$TextBoxAttr =  " class='span5'";
$DeleteButton = "";


$UserInfo = $Aghate->GetUserInfo($_SESSION['login']);

//================================================================================
// Dans le cas du modification d'un reservation
//================================================================================
if($type_reservation=="Demande"){
		// si id deja existant modification d'une demande
	if(strlen($id)>0){
		$res=$Aghate->GetInfoDemandeById($id);
		$id_prog	= $id;
		$noip		=	$res['noip'];
		$type 		= 	$res['type'];	
		$patient_rech=$res['noip']. " ".$res['nom']." ". $res['prenom']. " ".$res['ddn']."(".$res['sex'].")";
		$diff = $end_time - $start_time;
		$duree=$res['end_time']-$res['start_time'];
		$tabdur = $Aghate->GetDureeUnit($duree);
		$duree =  $tabdur['duree'];
		$unit = $tabdur['units'];

		// medecin + specialité
		$info_med	=$Aghate->GetInfoMedecinById($res['medecin']);
		$id_medecin = $res['medecin'];
		if(strlen($info_med['nom'])>0)
			$medecin	=$info_med['nom']." ".$info_med['prenom'];
		else
			$medecin=""	;
		// err nip no patient trouvé	
		//decoupe recupere premier caractere
		$dur_units = strtoupper(substr($unit,0,1));
		$start_time=date("d/m/Y/H/i",$res['start_time']);
		//echo "st :".$start_time	." ".$res['start_time'];
		list($day,$month,$year,$hour,$minute)=explode("/",$start_time);
		$date_deb = $day."/".$month."/".$year;
		$heure_deb= $hour.":".$minute;

		$start_time = $date_deb." ".$heure_deb;
		$end_time=date("d/m/Y/H/i",$res['end_time']);

		list($day_f,$month_f,$year_f,$hour_f,$minute_f)=explode("/",$end_time);
		$date_fin=$day_f."/".$month_f."/".$year_f;
		$heure_fin= $hour_f.":".$minute_f;
		
		$end_time = $date_fin." ".$heure_fin;	
		
		$id_service=$res['service_id'];

		$protocole=$res['protocole'];
		$description=$Aghate->GetDescComplementaire($res['description'],true);	
		
		$DonneAghate=$Aghate->GetSejoursParNda($res['nda']);
		
		$DeleteButton = '<input type="button" id="Supprimer" name="Supprimer" value="Supprimer" 
					class="btn btn-danger" onclick="DelReservation(\''.$NomTableLoc.'\',\'Demande\');"/>'; 	
	
		// si statut medecin , il n'a pas le droit d'attribuer de lit => ne pas affiche lit
		// que modif
		if($UserInfo[0]['medecin']=="1" && $_SESSION['statut'] != 'administrateur'){
			// ne pas afficher le lit
			$Display = "style='display:none'";
			$SaveButton = '<input type="button" id="Enregistrer" name="Enregistrer" value="Enregistrer" class="btn btn-success" 
						onclick="SavePage(\''.$NomTableLoc.'\',\'Demande\');"/>';
		}
		else{
			$Display = "";
			$SaveButton = '<input type="button" id="Enregistrer" name="Valider" value="Valider" class="btn btn-success" 
						onclick="ValideDemande(\''.$NomTableLoc.'\');"/>';
			$RefuseButton = '<input type="button" id="Refuser" name="Refuser" value="Refuser" class="btn btn-warning" 
							onclick="RefuseDemande(\''.$NomTableLoc.'\',\'Refuse\');"/>';
		}		
	}	
	// nouvelle demande
	else{
		//================================================================================
		// Dans le cas de l'ajout d'une demande
		//================================================================================
		$Display = "style='display:none'";
		if (isset($hour) && isset($minute) && isset($year) && isset($month) && isset($day)){
			
			$day	= str_pad($day, 	2, "0", STR_PAD_LEFT);
			$month	= str_pad($month, 	2, "0", STR_PAD_LEFT);
			
			$hour	= str_pad($hour, 	2, '0', STR_PAD_LEFT);
			$minute	= str_pad($minute, 	2, '0', STR_PAD_LEFT);
			
					
			$date_deb=$day."/".$month."/".$year;
			$heure_deb= $hour.":".$minute;
			$start_time = $date_deb." ".$heure_deb;
			$unix_start_time =  mktime($hour, $minute, 0, $month  , $day, $year);
		}
		else
		{
			list($_day,$_month,$_year,$_hour,$_minute)=explode("/",date("d/m/Y/H/i"));
			$date_deb=$_day."/".$_month."/".$_year;
			$heure_deb= $_hour.":".$_minute;
			$start_time = $date_deb." ".$heure_deb;
			$unix_start_time =  mktime($_hour, $_minute, 0, $_month  , $_day, $_year);		
		}
		
		//$day=(strlen($day)>0)?$day;
		$dur_units="J";
		$duree="5";
		$timestamp_dur = $duree*24*60*60;
		
		list($_day_d,$_month_d,$_year_d,$_hour,$_minute)=explode("/",date("d/m/Y/H/i",time()+$timestamp_dur));
		$date_fin=$_day_d."/".$_month_d."/".$_year_d;
		$heure_fin= $_hour.":".$_minute;
		
		$end_time = $date_fin." ".$heure_fin;
		$str_duree = $duree." jours";
		$specialite="A choisir";

		
		//old varibales
		$id_service=$area;
		$Display = "style='display:none'";
		$SaveButton = '<input type="button" id="Enregistrer" name="Enregistrer" value="Enregistrer" class="btn btn-success" 
							onclick="SavePage(\''.$NomTableLoc.'\',\'Demande\');"/>';
	}

}
else{
	//================================================================================
	// Dans le cas du modification d'un reservation
	//================================================================================
	if(strlen($id)>0)
	{
		$res=$Aghate->GetInfoReservation ($id);
		$id_prog 	=	$res['id_prog'];
		$noip		=	$res['noip'];
		$type 		= 	$res['type'];	
		$patient_rech=$res['noip']. " ".$res['nom']." ". $res['prenom']. " ".$res['ddn']."(".$res['sex'].")";
		$diff = $end_time - $start_time;
		$duree=$res['end_time']-$res['start_time'];

		$plage_pos=$res['plage_pos'];
		$tabdur = $Aghate->GetDureeUnit($duree);
		$duree =  $tabdur['duree'];
		$unit = $tabdur['units'];

		// medecin + specialité
		$info_med	=$Aghate->GetInfoMedecinById($res['medecin']);
		$id_medecin = $res['medecin'];
		if(strlen($info_med['nom'])>0)
			$medecin	=$info_med['nom']." ".$info_med['prenom'];
		else
			$medecin=""	;
		$specialite	=$info_med['specialite'];
		if(strlen(trim($specialite)) < 1)
			$specialite="specialite inconnu";
			
		// err nip no patient trouvé	
		//decoupe recupere premier caractere
		$dur_units = strtoupper(substr($unit,0,1));
		$start_time=date("d/m/Y/H/i",$res['start_time']);
		//echo "st :".$start_time	." ".$res['start_time'];
		list($day,$month,$year,$hour,$minute)=explode("/",$start_time);
		$date_deb = $day."/".$month."/".$year;
		$heure_deb= $hour.":".$minute;
		
		$start_time = $date_deb." ".$heure_deb;
		
		$end_time=date("d/m/Y/H/i",$res['end_time']);
		list($day_f,$month_f,$year_f,$hour_f,$minute_f)=explode("/",$end_time);
		$date_fin=$day_f."/".$month_f."/".$year_f;
		$heure_fin= $hour_f.":".$minute_f;
		
		$end_time = $date_fin." ".$heure_fin;	
		
		$id_service=$res['service_id'];
		$room_id=$res['room_id']; 

		$protocole=$res['protocole'];
		$description=$Aghate->GetDescComplementaire($res['description'],true);	
		if ($de_source != "Programme")	
			$TextBoxAttr.="readonly=readonly";
			
		// si donné modifé par gilda force read only
		if($res['de_source']=='Gilda'){
			$AttrService="readonly=readonly";
			$AttrRoom="disabled=disabled"; 
			$AttrDateDeb="readonly=readonly";
			$SejourInfo=$Gilda->GetSejourInfoParNda($res['nda']);
		}
		
		$DonneAghate=$Aghate->GetSejoursParNda($res['nda']);
		// Verifier ici si de_source gilda on affiche le bouton ou pas
		
		$SaveButton = '<input type="button" id="Enregistrer" name="Enregistrer" value="Enregistrer" class="btn btn-success" 
						onclick="SavePage(\''.$NomTableLoc.'\',\'reservation\');"/>';
		$DeleteButton = '<input type="button" id="Supprimer" name="Supprimer" value="Supprimer" class="btn btn-danger" 
						onclick="DelReservation(\''.$NomTableLoc.'\',\'reservation\');"/>'; 	
	}
	else 
	{
		//================================================================================
		// Dans le cas de l'ajout d'une reservation
		//================================================================================
		if (isset($hour) && isset($minute) && isset($year) && isset($month) && isset($day)){
			// concatiner 0 pour single digit
			$day	= str_pad($day, 	2, "0", STR_PAD_LEFT);
			$month	= str_pad($month, 	2, "0", STR_PAD_LEFT);
			
			$hour	= str_pad($hour, 	2, '0', STR_PAD_LEFT);
			$minute	= str_pad($minute, 	2, '0', STR_PAD_LEFT);
			
					
			$date_deb=$day."/".$month."/".$year;
			$heure_deb= $hour.":".$minute;
			$start_time = $date_deb." ".$heure_deb;
			$unix_start_time =  mktime($hour, $minute, 0, $month  , $day, $year);
		}
		else
		{
			list($_day,$_month,$_year,$_hour,$_minute)=explode("/",date("d/m/Y/H/i"));
			$date_deb=$_day."/".$_month."/".$_year;
			$heure_deb= $_hour.":".$_minute;
			$start_time = $date_deb." ".$heure_deb;
			$unix_start_time =  mktime($_hour, $_minute, 0, $_month  , $_day, $_year);		
		}
		
		//$day=(strlen($day)>0)?$day;
		$dur_units="J";
		$duree="5";
		// pour plages pas de durée
		if (strlen($plage_pos) > 0)
			$timestamp_dur = 15;
		else
			$timestamp_dur = $duree*24*60*60;
		
		list($_day_d,$_month_d,$_year_d,$_hour,$_minute)=explode("/",date("d/m/Y/H/i",time()+$timestamp_dur));
		$date_fin=$_day_d."/".$_month_d."/".$_year_d;
		$heure_fin= $_hour.":".$_minute;
		
		$end_time = $date_fin." ".$heure_fin;
		$str_duree = $duree." jours";
		$specialite="A choisir";

		
		//old varibales
		$id_service=$area;

		// Gestion proposition des Room
		// si le programation en future le lit sera panier sauf les resa dans 48 hrs
		$unix_24hrs_plus =  time() + (2*60*60*24);
		if (( $unix_24hrs_plus < $unix_start_time) || (strlen($room)< 1) ){
			$room_id=$Aghate->GetPanierIdByServiceId($id_service); 	
			// si pas de panier on envoi vers el default lit choisi
			if (strlen($room_id) < 1 )
				$room_id=$room; 
		}
		else
			$room_id=$room; 
			
		
		$SaveButton = '<input type="button" id="Enregistrer" name="Enregistrer" value="Enregistrer" class="btn btn-success" 
							onclick="SavePage(\''.$NomTableLoc.'\',\'reservation\');"/>';
		
	}
}
//}


$ResService=$Aghate->GetServiceInfoByServiceId($id_service);
$service=$ResService[0]['service_name'];

//-----------------------------------------------------------------
// vérify le droit de reservation 
//-----------------------------------------------------------------	

if($type_reservation!="Demande"){
	if($Aghate->GetUserLevel($_SESSION['login'],$room_id ) < 3){
		Print "<h1> Vous n'avez pas les droits suffisants pour effectuer cette opération</h1>";
		exit;
	}
}
//-----------------------------------------------------------------
// On Load page charger les Rooms	par rapport le service
//-----------------------------------------------------------------
$AllRooms = $Aghate->GetAllRooms($id_service,true);
$ListRoom[0]="";
foreach($AllRooms as $key )
{
	if($key['room_name']=='Panier')
	{
		$default_room=$key['id'];
		$ListRoom[0]=$key['id']."|". $key['room_name'];
	}else
	{
		$ListRoom[]=$key['id']."|". $key['room_name'];
	}
}
$room =(strlen($room)< 1)?$default_room:$room_id;

//-----------------------------------------------------------------
// On Load page charger les TypeResa(coleur)
// utiliser fichier config les listes ?
//-----------------------------------------------------------------
$AllTypes = $Aghate->GetResrvationTypes($id_service);

foreach($AllTypes as $key )
{
	$ListeTypeResa[]=$key['type_letter']."|". $key['type_name'];
}

//-----------------------------------------------------------------
// On Load page charger les TypeResa(coleur)
// gestion par palages
//-----------------------------------------------------------------
	
if($ResService[0]['enable_periods']=='y')
{
	$AllPalges = $Aghate->GetServicePlages($id_service);
	foreach($AllPalges as $key )
	{
		$ListeCreneau[]=$key['num_periode']."|". $key['nom_periode'];
	}
}

?>

<link href="../commun/styles/bootstrap_form.css" rel="stylesheet"> 
<link href="./commun/style/reservation.css" rel="stylesheet"> 

<link rel="stylesheet" type="text/css" href="./commun/js/datetimepicker-master/jquery.datetimepicker.css"/ > 
<script type="text/javascript" src="./commun/js/datetimepicker-master/jquery.datetimepicker.js" charset="<?php print $charset?>"></script>
<script type="text/javascript" src="./commun/js/fonctions_aghate.js"  charset="<?php print $charset?>"></script>
</head>
<body style="">
<form name="FormResa" id="FormResa" action=<?php print $PHP_SELF;?> method="POST">
<table align="center" border="0" width="700px" cellspacing=0>
	<tr>
		<td id="SectionTitle">Reservation</td>
		<td>&nbsp;&nbsp;&nbsp;</td>
		<td>
			<table>
				<tr>
					<td>
						<span class="add-on"><b>Patient </b> </span>
						<?php Print $Html->InputTextBox("patient_rech",$patient_rech,20,10,$TextBoxAttr);?>
					</td>
				</tr>
				<tr>
					<td>
					<div class="input-prepend input-append">
						<span class="add-on"><b>Description </b> </span>
						
						<?php Print $Html->InputTextBox("description",$description,255,10,"class='span3'" );?>
					</div>	
					</td>
				</tr>
				<tr>
					<td>
						<div id="pat_info" style='background-color:#FFFFFF;display:none;padding:10px;padding-top:0px;overflow-y:auto;height:150px;width:100%;'></div>
					</td>
				</tr>
				<tr>
					<td>
						 <div class="input-prepend input-append">

							<span class="add-on"><b>Localisation</b>&nbsp;&nbsp;&nbsp;&nbsp;</span>
							<?php 
							print $Html->InputHiddenBox("id_service",$id_service );
							// gestion par plages
							if($ResService[0]['enable_periods']=='y')
							{
								$AttrService="readonly=readonly";
							}
							print $Html->InputCompletSimple(service,"",$service,"agt_service","./commun/ajax/ajax_aghate_resa_autocomplet_service.php","$AttrService");
							print "<span class='add-on' $Display ><b>Lit  </b>&nbsp;&nbsp;&nbsp;&nbsp;</span>";							
							print  $Html->InputSelect($ListRoom,"room_id",$room_id,"./commun/ajax/ajax_aghate_resa_autocomplet_service.php","class='input-small' $Display $AttrRoom" );
						 
							?>
						</div>	
					</td>
				</tr>
				 
				<tr>
					<td>
					<div class="input-prepend input-append">
					<span class="add-on"><b>Motif/Intervention  </b>&nbsp;&nbsp;</span>
						<?php 
							print $Html->InputHiddenBox("id_protocole",$id_protocole);
							print $Html->InputCompletSimple(protocole,"",$protocole,"agt_protocole","./commun/ajax/ajax_aghate_resa_autocomplet_protocole.php");
						?>
					</div>	
					</td>
				</tr>
				<tr>
					<td>
					<div class="input-prepend input-append">
					<span class="add-on"><b>Medecin  </b>&nbsp;&nbsp;</span>
						<?php 
							print $Html->InputHiddenBox("id_medecin",$id_medecin);
							print $Html->InputCompletSimple(medecin,"",$medecin,"agt_medecin","./commun/ajax/ajax_aghate_resa_autocomplet_medecin.php");
						?>
					</div>	
					</td>
				</tr>	

                <?php
				// Specialité / type reservation
				if ($site  == "001")
				{
				?>
				 		
				<tr>
					<td>
					<div class="input-prepend input-append">
					<span class="add-on"><b>Specialité  </b>&nbsp;&nbsp;</span>
						<?php 
							print $Html->InputHiddenBox("type",$type );
							Print $Html->InputTextBox("specialite",$specialite,40,40,"readonly=readonly");
						?>
					</div>	
					</td>
				</tr>					
				<?php
				}
				
				if($ResService[0]['enable_periods']=='y')
				{
					// gestion par palages
					Print '<tr><td>
					     <div class="input-prepend input-append">
					     <span class="add-on"><b>Créneau  </b>&nbsp;&nbsp;</span>'.
					     $Html->InputSelect($ListeCreneau,"plage_pos",$plage_pos,"","class='input-small' $AttrRoom" )
					     .'</td></tr>';
					print $Html->InputHiddenBox("start_time",$start_time )  ;     
					print $Html->InputHiddenBox("end_time",$end_time  );     
					print $Html->InputHiddenBox("duree",$duree );     
			  					
				}else
				{
				?>
				<tr>
					<td colspan="2">
					<div class="input-prepend input-append">
						<span class="add-on"><b>Séjour du </b>&nbsp;&nbsp;&nbsp;</span>
						<?php Print $Html->InputTextBox("start_time",$start_time,15,15,$AttrDateDeb)?>;
						<span class="add-on"><b> au </b>&nbsp;&nbsp; </span>
						<?php Print $Html->InputTextBox("end_time",$end_time,15,15)?>;
						<?php Print $Html->InputTextBox("duree",$duree,15,15,"readonly=readonly ")?>;								 
					</div>
	
					</td>
				</tr>
				<?php
				}
				
				// Specialité / type RESA
				if ($site  != "001")
				{
				?>
				
				<tr>
					<td colspan="2">
					<div class="input-prepend input-append">
						<span class="add-on"><b>Type de reservation</b>&nbsp;&nbsp;&nbsp;</span>
							<?php 
							print $Html->InputHiddenBox("specialite",$specialite );
							print  $Html->InputSelect($ListeTypeResa,"type",$type,"","class='input-medium'" );?>
					</div>	
					</td>
				</tr>	
				<?php
				}
				?>
				<tr>
					<td align="center">
						<div class="noprint">
							<?php
							print $SaveButton;
							print $RefuseButton;
							print $DeleteButton;
							print $Html->InputHiddenBox("table_loc",$table_loc );							
							?>
						</div>
					</td>
				</tr>
			
			</table>
			<div id="RefuseDiv"></div>
		</td>
	</tr>
</table>
<!--
========================================================================
 Affiche les ensemble de sejours si le patient est hospitalisé
======================================================================== 
 -->

<DIV id="sejours_gilda" style="text-align:center">
	</br>
	<?php
	// define max row in Gilda ou Agahte
	$TotRow =(count($DonneAghate) > count($SejourInfo))?count($DonneAghate):count($SejourInfo);
	if (count($SejourInfo) > 0)
		{
		echo "<table class='table' border='1' align='center'>
				<tbody>
				<tr><th colspan='3'> NDA : ".$SejourInfo[0]['NODA']." / Gilda </th>
					<th colspan='3' align='right'>  Aghate <a href='#' ID='MAJ' NDA='".$SejourInfo[0]['NODA']."' >Mettre a jour</a></th>

				</tr>
				<tr>
					<th>UH</th>
					<th>Entree </th>
					<th>Sortie</th>
					<th>UH</th>
					<th>Entree </th>
					<th>Sortie</th>								
				</tr>
				</tbody>";
				
		for($i=0;$i < $TotRow; $i++)
		{
			if($SejourInfo[$i]['TYMVAD'] != 'SH')
			{
				// Données GILDA							
				echo "
				<tr>
					<td>".$SejourInfo[$i]['NOUF']."</td>
					<td>".$SejourInfo[$i]['DTMVAD']." ".$SejourInfo[$i]['HHMVAD']."</td>
					<td>".$SejourInfo[$i+1]['DTMVAD']." ".$SejourInfo[$i+1]['HHMVAD']."</td>
				 ";	
				 
				// Données Aghate
				// prepare start end time to avoide epmty dates
				$start_time =(strlen($DonneAghate[$i]['start_time']) > 1)?date('d/m/Y H:i',$DonneAghate[$i]['start_time']):'-';
				$end_time 	=(strlen($DonneAghate[$i]['end_time'])   > 1)?date('d/m/Y H:i',$DonneAghate[$i]['end_time']):'-';
				
				$ClassUh= ($SejourInfo[$i]['NOUF'] !=$DonneAghate[$i]['uh'])?'class=ClassRed':' ';
				$ClassDtE= ($SejourInfo[$i]['DTMVAD']." ".$SejourInfo[$i]['HHMVAD']   !=$start_time)?'class=ClassRed':' ';
				$ClassDtS= ($SejourInfo[$i+1]['DTMVAD']." ".$SejourInfo[$i+1]['HHMVAD'] !=$end_time)?'class=ClassRed':' ';

				// si dt sor est vide dans  Gida
				if(strlen($SejourInfo[$i+1]['DTMVAD']) < 1 )$ClassDtS='';

				
				echo " 
					<td $ClassUh>".$DonneAghate[$i]['uh']."</td>
					<td $ClassDtE>".$start_time."</td>
					<td $ClassDtS>".$end_time."</td> 

				</tr>";								
			}
		}
		echo "</table>";
	}
	?>
	</td></tr></table>
 
</DIV>




<?php

	print $Html->InputHiddenBox(id,$id);
	print $Html->InputHiddenBox(noip,$noip);
	print $Html->InputHiddenBox(id_prog,$id_prog);	
	// reforce les read_only champs_id pour $_POST
	if($res['de_source']=='Gilda')
	{
		print $Html->InputHiddenBox(room_id,$room_id);
	}
 
?>
</form>
</body>
<script>
//=================================================================
// document ready
//=================================================================
 
$(document).ready(function() {	
	var de_source="<?php echo $res['de_source'];?>";
	//--------------------------------------------------------------------
	//patient recherche
	//--------------------------------------------------------------------
	if($("#patient_rech").length){
		$("#patient_rech").AutoSuggest("./commun/ajax/ajax_aghate_get_patinfo.php");
	}
	
	$("#patient_rech").blur(function(){
		if( $("#patient_rech").is('[readonly]') )
		{
			 // pas de changement
		}else{
	  	TmpPat=$("#patient_rech__id").val().split("|");
	  	$("#noip").val(TmpPat[0]);
	  }
	});
	
	//--------------------------------------------------------------------
	//Service on change charge ROOMS
	//--------------------------------------------------------------------
	$("#service").blur(function(){

		var DivRooms = "#DivRooms";
		//recupare page variables
		var id_service	=$("#id_service").val();
		var room_id		=$("#room").val();		
		
		//rooms recuparès de JSON
		ListRooms=res=LanceAjax("../aghate/commun/ajax/ajax_aghate_get_rooms.php","service_id="+id_service+"room_id="+room_id) ;		

		// Get the raw DOM object for the select box
		select = document.getElementById('room_id');

		// Clear the old options
		select.options.length = 0;

		// Load the new options
		ListRooms =eval(ListRooms);
		for (var i = 0; i < ListRooms.length; i++) {
		  select.options.add(new Option(ListRooms[i].value, ListRooms[i].id));
		}
		
	});
		
 	
 	//--------------------------------------------------------------------
	//Refresh specialité
	//--------------------------------------------------------------------
	$("#medecin").blur(function(){
		//recupare page variables
		var id_medecin		=$("#id_medecin").val();
		res	=LanceAjax("../aghate/commun/ajax/ajax_aghate_get_specialite.php","id_medecin="+id_medecin ) ;	
		if(res.length > 1)
		{
			$("#specialite").val(res);
			color_code	=LanceAjax("../aghate/commun/ajax/ajax_aghate_get_coleur_code.php","specialite="+res ) ;
			if(color_code.length > 0)
			{	
				$("#type").val(color_code);
			}
		}else
			$("#specialite").val("Specialité inconnu...");
	});	

	//--------------------------------------------------------------------
	//Refresh creneau date_deb date_fin
	//--------------------------------------------------------------------
	$("#plage_pos").blur(function(){
		//recupare page variables
		var plage_pos	=$("#plage_pos").val();
		var start_date	= $("#start_time").val();
		var res	=LanceAjax("../aghate/commun/ajax/ajax_aghate_calculplages.php","plage_pos="+plage_pos +"&start_date="+start_date ) ;

		res=res.split("|");
		if (res[1]){
			$("#start_time").val(res[1]);
			$("#end_time").val(res[2]);
			$("#duree").val('39');
		}else
		{
			alert("Err : invalide start time "+start_date)
			return false;
		}

	});	
	
	//--------------------------------------------------------------------
	//calcul duree
	//--------------------------------------------------------------------
	$("#protocole").blur(function(){
		res=LanceAjax("./commun/ajax/ajax_aghate_get_duree_protocole.php","id_protocole="+$("#id_protocole").val()+"&date_deb="+$("#start_time").val()+"&date_fin="+$("#end_time").val()+"&duree="+$("#duree").val());
		res = res.split("|");
		$("#end_time").val(res[1]);
		$("#duree").val(res[0]);
 	});
 	
	//=================================================================
	//on date deb change => changer durée
	//=================================================================
	//demmarage
	res=LanceAjax("./commun/ajax/ajax_aghate_get_duree.php","date_fin="+$("#end_time").val()+"&date_deb="+$("#start_time").val());
	$("#duree").val(res);
	if(de_source!='Gilda') 
		$("#start_time").datetimepicker({
		fixFocusIE: false,	
		lang:'fr',
		onClose: function(dateText) {
			res=LanceAjax("./commun/ajax/ajax_aghate_get_duree.php","date_fin="+$("#end_time").val()+"&date_deb="+$("#start_time").val());
			$("#duree").val(res);
        }
	});

	$("#start_time,#end_time").blur(function(){	
		
		res=LanceAjax("./commun/ajax/ajax_aghate_get_duree.php","date_fin="+$("#end_time").val()+"&date_deb="+$("#start_time").val());
		$("#duree").val(res);
		//diff = CalculDate($("#start_time").val(),$("#end_time").val());
		//$("#duree").val(diff.day+" jours "+diff.hour+" heures");
	});
		
	$("#end_time").datetimepicker({
		fixFocusIE: false,	
		lang:'fr',
		onClose: function(dateText) {
			res=LanceAjax("./commun/ajax/ajax_aghate_get_duree.php","date_fin="+$("#end_time").val()+"&date_deb="+$("#start_time").val());
			$("#duree").val(res);
        }
	});
	//--------------------------------------------------------------------
	//remettre a jour les sejours Gilda Vs Aghate
	//--------------------------------------------------------------------
	$("#MAJ").click(function(){
		var nda = $("#MAJ").attr( "NDA" );		
		//alert("./commun/ajax/ajax_aghate_remttre_ajour_gilda_par_nda.php?nda="+nda+"&table_loc="+$("#table_loc").val());		
		res=LanceAjax("./commun/ajax/ajax_aghate_remttre_ajour_gilda_par_nda.php","nda="+nda+"&table_loc="+$("#table_loc").val());
		res = res.split("|");
		alert(res[1]);
		//refresh windows
		window.location.href=window.location.href;
 	});


})// fin doc ready

</script> 
</html>
