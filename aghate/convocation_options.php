<?php
include "./commun/include/admin.inc.php";
include_once("./commun/include/functions.inc.php");
/*
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/
	$grr_script_name = "titres.php";
	$back = '';
	require("adresse_patient.php");	
	include "./commun/include/CustomSql.inc.php";
	include "./commun/include/code_barre.php";	
	
	$db = new CustomSQL($DBName);

if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((authGetUserLevel(getUserName(),-1) < 3) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    //showAccessDenied($day, $month, $year, $area,$back);

}

/*
#############################################################
#
#	GET reservations infrormation  by entry_id
#
#############################################################
*/

    /**
     * Convert number of seconds into hours, minutes and seconds
     * and return an array containing those values
     *
     * @param integer $seconds Number of seconds to parse
     * @return array
     */
    function secondsToTime($seconds)
    {
        // extract hours
        $hours = floor($seconds / (60 * 60));
     
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
     
        // extract the remaining seconds
        $divisor_for_seconds = $divisor_for_minutes % 60;
        $seconds = ceil($divisor_for_seconds);
     
        // return the final array
        $obj = array(
            "h" => (int) $hours,
            "m" => (int) $minutes,
            "s" => (int) $seconds,
        );
        return $obj;
    }


/*
#############################################################
#
#	GET reservations infrormation  by entry_id
#
#############################################################
*/
$res=$db->select("select * from agt_loc where id='".$_GET["entry_id"]."'");
 
if ( count($res) > 0)
{
	$nip=$res[0]["noip"];
	$pat=$res[0]["name"];
	$date_entree=time_date_string($res[0]["start_time"],$dformat);

	$duree=($res[0]["end_time"] - $res[0]["start_time"])  ;
	toTimeString($duree,$units);
	$duree.= " ".$units;
	$med=$res[0]["medecin"];

	// patients info cummulé 
	$pats = explode("(", $pat);
	$nom=$pats[0];
	$nip=substr($pats[1],0,10); // à voir
	$date_nais=substr($pats[2],0,10); // à voir 
	$sexe=substr($pats[3],0,1); // à voir 
	$overlaod_desc	=$res[0]["overload_desc"];
	// récuparation du addresse du patient
	
	$adresse=GetAdresse($nip);
	$addr=explode("|",$adresse);
	
	// code barre generation
	GenerateCodeBarre($nip);	

	// get  area 	
	$area = grr_sql_query1("select agt_service.id  from agt_service,agt_room where agt_room.service_id=agt_service.id and agt_room.id='".$res[0]["room_id"]."'");	

}
else
{
	echo "<div align='center'><br /><br />Probleme technique veuillez recommencer SVP</div>";
	exit;	
}

if (strlen($area) <1)
{
	echo "<div align='center'><br /><br />Probleme technique veuillez recommencer SVP</div>";
	exit;	
}

# print the page header
simple_header("","","","",$type="with_session", $page="admin");
$this_service_name = grr_sql_query1("select service_name from agt_service where id=$area");
// Affichage de la colonne de gauche
?>
	<style type="text/css">
	<!--
	.Style1 {color: #CCCCCC}
	.textbox1 {
		border: 0px dashed #D1C7AC;
		width: 230px;
	
	}
	.textbox2 {	
		width: 230px;
		border: 1px solid #3366FF;
		border-left: 4px solid #3366FF;
	}
input.btn { 
	  color:#050; 
	  font: bold 84%'trebuchet ms',helvetica,sans-serif; 
	  font: 12px ; 
	  //background-color: #fed; 
  	background: #9cf;	  
    //background:  url(./commun/images/imprimer.gif) no-repeat center top;  	
		outline: 2px solid blue;	  
	} 	
	-->
   </style>

<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<script type="text/JavaScript">
	
	
	function Affiche(control_id,disp_id){
		
		var choix=document.getElementById(control_id).checked;
		var Obj = document.getElementById(disp_id);
		if (choix){
			Obj.style.display = "";    		
		}else{
	  		Obj.style.display = "none";	
		}
	}
	
	function Startup(){
		Affiche('Prescription','presc_detail');
		Affiche('presc_autre','med_liste')	
	}	
		
	function Validate(){
		document.option_convocation.submit();	 	
		return false;
		if( (document.getElementById('convocation').checked) ||
			 (document.getElementById('Prescription').checked) ||
			 (document.getElementById('notice_PKP').checked) || 
			 (document.getElementById('presc_kleen').checked) || 
			 (document.getElementById('notice_COL').checked) || 
			 (document.getElementById('notice_ANSHDJ').checked) || 
			 (document.getElementById('notice_ANSHDS').checked) ){
			 	
			 	if ( (document.getElementById('Prescription').checked) && 
			 		  (!document.getElementById('presc_kleen').checked) && 
			 		  (!document.getElementById('presc_sang').checked) &&
			 		  (!document.getElementById('presc_autre').checked)  ){
			 		  	
			 		  	alert(" Aucun prescription choisi pour imprimer !!!");
						return false;		 		  	
			 	}
				document.option_convocation.submit();	 	
		}else{
			alert("Aucun option choisi pour imprimer !!! ");
			return false;
		}
	
	}	
	
	function count( form_object,checkbox_name ) {
		var cpt=0;
    	var total=0;

    	for(var i=0; i < form_object[checkbox_name].length; i++){
        	if(form_object[checkbox_name][i].checked){
				cpt++;
        }
		}
		if (cpt > 8){
			alert("Vous ne pouvez pas imprimer plus d'huit convocation en même temps!!!!") ;    
		}
	}	

	
		
</script>
<?php

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if (isset($_GET["action_moderate"])) {
    // on modère
    moderate_entry_do($id,$_GET["moderate"],$_GET["description"]);
};
$urm=$_SESSION['URM'];

// si Hépato Gastro entrologie
if ($urm=="470"){
		// GET UH
	$uh=$db->GetAdditionalParametre($area,"UH",$overlaod_desc)		;	
		?>
		<body onLoad="Startup()">
		<form name="option_convocation"   action="generate_rdv.php" method="get">
		
		<table width="600" border="1" cellspacing="1" cellpadding="1" align="center">
		  <tr>
		    <th height="40"   align="center"  ><span class="Style1">Option d'impression des convocations</span></th>
		  </tr>
		  <tr>
		    <td>
					<table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
					  <tr>
					  <td align=center><b> Adresse Postal </b>	</td>
					  </tr>
					
					  <tr>
					  <td align=center><input class="textbox1" type="text" name="nom" size="35" value="<?php print $nom?>">	</td>
					  </tr>
					  <tr>
					  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[0]?>">	</td>
					  </tr>
					  <tr>
					  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[1]?>">	</td>
					  </tr>
					  <tr>
					  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[2]?>">	</td>
					  </tr>
					  <tr>
					  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[3]." - " .$addr[4]?>">	</td>
					  </tr>
					  <tr>
					  <td align=center> <font color='red'>Veuillez vérifier et modifier l'adresse si besoin avant l'imprimer	</font></td>
					  </tr>					  
					 </table>		    
		    </td>
		  </tr>
			<tr>
				<td>
				<!--separaion en deux collons -->
				<table border='0'>
					<tr>
						<!------------------------------------------
						colonne Gauche 
						----------------------------------------->
						<td>
							<table border='1' width="250">
								<tr><td align='center'> <b>Convocation et Prescriptions </b></td>		  </tr>
								<tr><td><input type="checkbox" name="convocation" id="convocation" />		Convocation</td>		  </tr>
				 			 	<tr><td><input type="checkbox" name="endoscopie_digestive"  />					Prescription d'endoscopie digestive</td></tr>   
							 	<tr><td><input type="checkbox" name="Douleur_Soins_Palliatifs"   />			Douleur Soins Palliatifs Algoplus EMSP</td></tr>   
							 	<tr><td><input type="checkbox" name="Etiquettes_autocol_perfusion"   />	Etiquettes autocol perfusion</td></tr>   
							 	<tr><td><input type="checkbox" name="Examen_avant_DIVLD"   />						Examen avant DIVLD</td></tr>   
							 	<tr><td><input type="checkbox" name="Prescription_Normacol"   />				Prescription Normacol</td></tr>   
							 	<tr><td><input type="checkbox" name="Prescription_PBH"   />							Prescription PBH</td></tr>   
							 	<tr><td><input type="checkbox" name="Prescription_Suivi_Remicade" />		Prescription Suivi Remicade</td></tr>   
							 	<tr><td><input type="checkbox" name="Surveillance_transfusionnelle" />	Surveillance transfusionnelle</td></tr>   
							 	<tr><td><input type="checkbox" name="_350se_hospit"   />								350se d'hospitalisation</td></tr>   
							 	<tr><td><input type="checkbox" name="_350se_sortie_ide"   />						350se_Sortie_IDE</td></tr>  
						 	</table>
						</td>
						<!------------------------------------------
						colonne Droite 
						<!------------------------------------------>
						<td>
							<table border='1' width="350">
								<tr><td align='center'> <b>Notices et documents </b></td>	  </tr>
					     	<tr><td><input type="checkbox" name="presc_kleen"   />							KLEAN PREP : Prepation colique 4 sachets</td></tr>
								<tr><td><input type="checkbox" name="presc_sang"    />							Prise de sang avant une coloscopie </td><tr>
						  	<tr><td><input type="checkbox" name="notice_PKP"   />								Notice de Preparion du KLEAN-PREP</td></tr>
					 	  	<tr><td><input type="checkbox" name="notice_COL"   />								Information médicales avant la realisation d'une COLOSCOPIE</td>			</tr>   
							 	<tr><td><input type="checkbox" name="notice_ANSHDJ"  />							Notice d'Anesthesie HDJ</td>			</tr>
							 	<tr><td><input type="checkbox" name="notice_ANSHDS"   />						Notice d'Anesthesie HDS</td>			</tr>   
							 	<tr><td><input type="checkbox" name="notice_CATHETERISME"   />			CATHETERISME ENDOSCOPIQUE BILIO PANCREATIQUE</td>			</tr>   
							 	<tr><td><input type="checkbox" name="Infos_coloscopie"  />					Infos coloscopie</td>			</tr>   
							 	<tr><td><input type="checkbox" name="Infos_echo_endoscopie"  />			Info Echo-Endoscopie</td>			</tr> 
								<tr><td><input type="checkbox" name="Infos_enteroscopie"  />				Infos Enteroscopie</td></tr> 
								<tr><td><input type="checkbox" name="Infos_gastrocopie" />					Infos GASTROSCOPIE DIAGNOSTIQUE et THERAPEUTIQUE </td></tr>   
						 	</table>
							</td>
				
				</table>
				
				
				
				</td>
			</tr>
					


	 	  <tr>
				<td  align="center"> <input type="button" name="button" id="button" value="Imprimer" onClick="Validate();"/></td>
		  </tr> 
		</table>
		<input type="hidden" name="champ_du_owner" value="<?php print $champ; ?>">                 
		<input type="hidden" name="nip" value="<?php print $nip?>">
		<input type="hidden" name="nom" value="<?php print $nom?>">
		<input type="hidden" name="sexe" value="<?php print $sexe?>">
		<input type="hidden" name="pat" value="<?php print $pat?>">
		<input type="hidden" name="date_entree" value="<?php print $date_entree?>">
		<input type="hidden" name="duree" value="<?php print $duree?>">
		<input type="hidden" name="med" value="<?php print $med?>">
		<input type="hidden" name="date_nais" value="<?php print $date_nais?>">
		<input type="hidden" name="pat" value="<?php print $pat?>">
		<input type="hidden" name="uh" value="<?php print $uh?>">
		
		  
		</form>
		</body>
<?php } 

// si  Hématologie POLICLINIQUE
if ($urm=="560"){
		echo "<body>";
		echo "<form name=\"option_convocation\"   action=\"generate_rdv.php\" method=\"POST\">";
				
		$mon=date("m");
		$day=date("d");
		$year=date("Y");
		$deb_time=mktime(0,0,0,$mon,$day-1,$year);
		$fin_time=mktime(24,0,0,$mon,$day,$year+1);			
 		$sql_ctrl=" AND  (start_time >=".$deb_time." AND  start_time <".$fin_time .") ";
		$sql_ctrl.=" AND  noip= '".$nip."'";
		  
 
		//recherce les patients dans le même service 
			$session_user = $_SESSION['login'];
		  $session_statut = $_SESSION['statut'];
		  $list_areas =get_areas_allowed($session_user,$session_statut);
		   
		  
		// Recherche les  réservation pour ce patient dan sle peroide 
			$sql = "SELECT agt_loc.name,
				       agt_loc.description,
				       agt_loc.beneficiaire,
				       agt_room.room_name,
				       agt_service.service_name,
				       agt_loc.type,
				       agt_loc.room_id,
				       agt_loc.repeat_id,
				             ".grr_sql_syntax_timestamp_to_unix('agt_loc.timestamp').",
				       (agt_loc.end_time - agt_loc.start_time),
				       agt_loc.start_time,
				       agt_loc.end_time,
				       agt_service.id,
				       agt_loc.statut_entry,
				       agt_room.delais_option_reservation,
				       agt_loc.option_reservation, " .
				       "agt_loc.moderate,
				       agt_loc.beneficiaire_ext,
				       agt_loc.create_by,
				       agt_loc.jours,
				       agt_room.active_ressource_empruntee,
				       agt_loc.medecin,
				       overload_desc,
				       agt_loc.id
				FROM agt_loc, agt_room, agt_service
				WHERE agt_loc.room_id = agt_room.id
				  AND agt_room.service_id = agt_service.id
				  AND agt_service.id in (".$list_areas.")" ;

		$sql.=$sql_ctrl. "ORDER BY agt_loc.start_time";  
		
		/*echo $sql;
		exit;
		*/

		
		?>

		<table width="450" border="0" cellspacing="0" cellpadding="0" align="center">
		  <tr>
		  <td align=center><b> Adresse Postal </b>	</td>
		  </tr>
		
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="nom" size="35" value="<?php print $nom?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[0]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[1]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[2]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[3]." - " .$addr[4]?>">	</td>
		  </tr>


		 </table> 
		<br />
		<table width="450" border="1" cellspacing="1" cellpadding="1" align="center">
		  <tr>
		    <th align="center">Choix/Print</th>		  
		    <th align="center">Date - heure</th>
		    <th align="center">Dur&eacute;e</th>
		    <th align="center">Chambre</th>
		    <th align="center">Protocole</th>    
		  </tr>
		 
		  
		  <?php 
		  $res = grr_sql_query($sql);
		  $optvals="";
		  if ($res) {
			  for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
			  	$duree_min= $row[9]/60;// en minutes
			  	$duree_hrs=$duree_min/60;
			  	if ($duree_hrs > 24){
			  		$c_duree=$duree_hrs/24;
			  		$mod=$duree_hrs % 24;
			  		$c_duree= intval($c_duree) ." Jour(s) et " .$mod."  heure(s)";
			  	 }else{
			  	 	$mod=$duree_min % 60 ;
			  		$c_duree= intval($duree_hrs) .":" .$mod ." heure(s)";	  	 
			  	 }
			  	$protocole=$db->get_protocole($area,$row[22]);
			  	/*$protocole=urldecode($row[22]); 
			  	$protocole=substr($protocole,2,strlen($protocole));
			  	$protocole=substr($protocole,1,strlen($protocole)-5);
			  	*/
			  	if(($i%2)==0)$bgcolor="";else $bgcolor="#EED7D2";
			  	$optvals .= date('d/m/Y à H:i',$row[10])."|";
			  	
			  	if ($i < 8)$chk="checked value='".date('d/m/Y à H:i',$row[10])."'"; else $chk=" value=''";
			?>
			  <tr  bgcolor="<?php print $bgcolor?>">
				 <td><input type="checkbox" name="opt_print[]" id="opt_print[]" <?php print $chk;?> onclick="count(this.form,'opt_print[]')" /></td>			  
				 <td><?php print  date('d/m/Y à H:i',$row[10]) ;?></td>			  
			    <td><?php print $c_duree ?></td>
				 <td><?php print $row[3]?></td>			    				 
			    <td><?php print $protocole ?></td>
		      </tr>
			  <?php 
			}
			}
		    ?>
		    
	<!-- L'autre convocations -->
	</table><br />
	<table width="450" border="1" cellspacing="1" cellpadding="1" align="center">
			  <tr>
				 <td><input type="checkbox" name="pose_kt" id="pose_kt" <?php if($pose_kt=="1")print "Checked";?>  /></td>			  
				 <td> POSE CATHETER</td>			  
		    </tr>	
		    
			  <tr>
				 <td><input type="checkbox" name="abl_kt" id="abl_kt" <?php if($abl_kt=="1")print "Checked";?>    /></td>			  
				 <td>ABLATION DE CATHETER</td>			  
		    </tr>	

		
			  <tr>
				 <td><input type="checkbox" name="ordonnance_tagamet_atarax" id="ordonnance_tagamet_atarax" <?php if($ordonnance_tagamet_atarax=="1")print "Checked";?>    /></td>			  
				 <td>Ordonnance Ranitidine Atarax </td>			  
		    </tr>	
			  <tr>
				 <td><input type="checkbox" name="ordonnance_tagamet_xyzall" id="ordonnance_tagamet_xyzall" <?php if($ordonnance_tagamet_xyzall=="1")print "Checked";?>    /></td>			  
				 <td>Ordonnance Ranitidine xyzall </td>			  
		    </tr>	
			  <tr>
				 <td><input type="checkbox" name="ordonnance_emla" id="ordonnance_emla" <?php if($ordonnance_emla=="1")print "Checked";?>    /></td>			  
				 <td>Ordonnance EMLA patch </td>			  
		    </tr>	
		    
			  <tr>
				 <td><input type="checkbox" name="numeration_formule_sanguine_M3" id="numeration_formule_sanguine_M3" <?php if($numeration_formule_sanguine_M3=="1")print "Checked";?>    /></td>			  
				 <td>Numeration Formule Sanguine M3 </td>			  
		    </tr>	
			  <tr>
				 <td><input type="checkbox" name="numeration_formule_sanguine_C6" id="numeration_formule_sanguine_C6" <?php if($numeration_formule_sanguine_C6=="1")print "Checked";?>    /></td>			  
				 <td>Numeration Formule Sanguine C6</td>			  
		    </tr>	
			  <tr>
				 <td><input type="checkbox" name="bilan_pose_KT" id="bilan_pose_KT" <?php if($bilan_pose_KT=="1")print "Checked";?>    /></td>			  
				 <td>Bilan Pose KT</td>			  
		    </tr>	
			  <tr>
				 <td><input type="checkbox" name="bilan_ablation_KT" id="bilan_ablation_KT" <?php if($bilan_ablation_KT=="1")print "Checked";?>    /></td>			  
				 <td>Bilan Ablation KT</td>			  
		    </tr>	
		    
		    
			  <tr  >
				 <td>BILAN GREFFE </td>			  
				 <td >
				    <a href="./images/courriers/bilan_greffe_2011.pdf"  ><img src="./commun/images/pdf.jpg" border="0" height="20" width="20"/></a>
				 		<a href="./images/courriers/bilan_greffe_2011.doc"  ><img src="./commun/images/msword.jpg" border="0" height="20" width="20"/></a>
				 </td>			  
		      </tr>	
			  <tr>
				 <td>BILAN SUIVI</td>			  
				 <td>
				      <a href="./others/doc/bilan_suivi.pdf"  ><img src="./commun/images/pdf.jpg" border="0" height="20" width="20"/></a>
				 		<a href="./images/courriers/bilan_suivi.doc"  ><img src="./commun/images/msword.jpg" border="0" height="20" width="20"/></a>
				 </td>			  
				 
		      </tr>	


			    
		</table>		
			<input type="submit" name="Imprimer" id="Imprimer" value="Imprimer" />		
			<input type="hidden" name="optvals" id="optvals" value="<?php print $optvals;?>" />					
	 
				<input type="hidden" name="nip" value="<?php print $nip?>">
		<input type="hidden" name="nom" value="<?php print $nom?>">
		<input type="hidden" name="sexe" value="<?php print $sexe?>">
		<input type="hidden" name="pat" value="<?php print $pat?>">
		<input type="hidden" name="med" value="<?php print $med?>">
		<input type="hidden" name="date_nais" value="<?php print $date_nais?>">
		<input type="hidden" name="service" value="<?php print $this_service_name ?>">

		<input type="hidden" name="date_entree" value="<?php print $date_entree?>">
		<input type="hidden" name="duree" value="<?php print $duree?>">
 		  			
		</form>
		</body>
		
<?php 

}
//##################################################################
// URM 010 ENDOCRINOLOGIE 
//##################################################################

// si  Hématologie POLICLINIQUE
if ($urm=="010"){
		echo "<body>";
		echo "<form name=\"option_convocation\"   action=\"generate_rdv_010.php\" method=\"get\">";
				
		$mon=date("m");
		$day=date("d");
		$year=date("Y");
		$deb_time=mktime(0,0,0,$mon,$day-1,$year);
		$fin_time=mktime(24,0,0,$mon,$day,$year+1);			
 		$sql_ctrl=" AND  (start_time >=".$deb_time." AND  start_time <".$fin_time .") ";
		$sql_ctrl.=" AND  noip= '".$nip."'";
		  
 
		//recherce les patients dans le même service 
			$session_user = $_SESSION['login'];
		   $session_statut = $_SESSION['statut'];
		   $list_areas =get_areas_allowed($session_user,$session_statut);
		   
		  
		// Recherche les  réservation pour ce patient dan sle peroide 
			$sql = "SELECT agt_loc.name,
				       agt_loc.description,
				       agt_loc.beneficiaire,
				       agt_room.room_name,
				       agt_service.service_name,
				       agt_loc.type,
				       agt_loc.room_id,
				       agt_loc.repeat_id,
				             ".grr_sql_syntax_timestamp_to_unix('agt_loc.timestamp').",
				       (agt_loc.end_time - agt_loc.start_time),
				       agt_loc.start_time,
				       agt_loc.end_time,
				       agt_service.id,
				       agt_loc.statut_entry,
				       agt_room.delais_option_reservation,
				       agt_loc.option_reservation, " .
				       "agt_loc.moderate,
				       agt_loc.beneficiaire_ext,
				       agt_loc.create_by,
				       agt_loc.jours,
				       agt_room.active_ressource_empruntee,
				       agt_loc.medecin,
				       overload_desc,
				       agt_loc.id
				FROM agt_loc, agt_room, agt_service
				WHERE agt_loc.room_id = agt_room.id
				  AND agt_room.service_id = agt_service.id
				  AND agt_service.id in (".$list_areas.")" ;

		$sql.=$sql_ctrl. "ORDER BY agt_loc.start_time";  
		
		/*echo $sql;
		exit;
		*/
		// récuparation du addresse du patient
			$addr=explode("|",$adresse);
		
		?>

		<table width="450" border="0" cellspacing="0" cellpadding="0" align="center">
		  <tr>
		  <td align=center><b> Adresse Postal </b>	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="nom" size="35" value="<?php print $nom?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $adresse0?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[0]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[1]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[2]." - " .$addr[3]?>">	</td>
		  </tr>


		 </table> 
		<br />
		<input type="hidden" name="convocation" value="convocation" />
		<input type="submit" name="Imprimer" id="Imprimer" value="Imprimer" />		
		<input type="hidden" name="optvals" id="optvals" value="<?php print $optvals;?>" />					
	 
		<input type="hidden" name="nip" value="<?php print $nip?>">
		<input type="hidden" name="nom" value="<?php print $nom?>">
		<input type="hidden" name="sexe" value="<?php print $sexe?>">
		<input type="hidden" name="pat" value="<?php print $pat?>">
		<input type="hidden" name="med" value="<?php print $med?>">
		<input type="hidden" name="date_nais" value="<?php print $date_nais?>">
		<input type="hidden" name="service" value="<?php print $this_service_name ?>">

		<input type="hidden" name="date_entree" value="<?php print $date_entree?>">
		<input type="hidden" name="duree" value="<?php print $duree?>">
 		  			
		</form>
		</body>
		
<?php 

}

//##################################################################
// URM 070 Maladies de sien
//##################################################################


if ($urm=="070"){
		echo "<body>";
		echo "<form name=\"option_convocation\"   action=\"generate_rdv_070.php\" method=\"get\">";
				
		$mon=date("m");
		$day=date("d");
		$year=date("Y");
		$deb_time=mktime(0,0,0,$mon,$day-1,$year);
		$fin_time=mktime(24,0,0,$mon,$day,$year+1);			
 		$sql_ctrl=" AND  (start_time >=".$deb_time." AND  start_time <".$fin_time .") ";
		$sql_ctrl.=" AND  noip= '".$nip."'";
		  
 
		//recherce les patients dans le même service 
			$session_user = $_SESSION['login'];
		   $session_statut = $_SESSION['statut'];
		   $list_areas =get_areas_allowed($session_user,$session_statut);
		   
		  
		// Recherche les  réservation pour ce patient dans le peroide 
			$sql = "SELECT agt_loc.name,
				       agt_loc.description,
				       agt_loc.beneficiaire,
				       agt_room.room_name,
				       agt_service.service_name,
				       agt_loc.type,
				       agt_loc.room_id,
				       agt_loc.repeat_id,
				             ".grr_sql_syntax_timestamp_to_unix('agt_loc.timestamp').",
				       (agt_loc.end_time - agt_loc.start_time),
				       agt_loc.start_time,
				       agt_loc.end_time,
				       agt_service.id,
				       agt_loc.statut_entry,
				       agt_room.delais_option_reservation,
				       agt_loc.option_reservation, " .
				       "agt_loc.moderate,
				       agt_loc.beneficiaire_ext,
				       agt_loc.create_by,
				       agt_loc.jours,
				       agt_room.active_ressource_empruntee,
				       agt_loc.medecin,
				       overload_desc,
				       agt_loc.id
				FROM agt_loc, agt_room, agt_service
				WHERE agt_loc.room_id = agt_room.id
				  AND agt_room.service_id = agt_service.id
				  AND agt_service.id in (".$list_areas.")" ;

		$sql.=$sql_ctrl. "ORDER BY agt_loc.start_time";  
		
		/*echo $sql;
		exit;
		*/
		// récuparation du addresse du patient
			$addr=explode("|",$adresse);
		
		?>

		<table width="450" border="0" cellspacing="0" cellpadding="0" align="center">
		  <tr>
		  <td align=center><b> Adresse Postal </b>	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="nom" size="35" value="<?php print $nom?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[0]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[1]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[2]?>">	</td>
		  </tr>
		  <tr>
		  <td align=center><input class="textbox1" type="text" name="adresse[]" size="35" value="<?php print $addr[3]." - " .$addr[4]?>">	</td>
		  </tr>

		 </table> 


	

		<table width="450" border="1" cellspacing="0" cellpadding="0" align="center">
			<tr >
		    <td colspan="3" align="center" >	<input type="submit" name="Imprimer" id="Imprimer" value="Imprimer"  class="btn"/>		</td>
	    </tr>			  
			<tr>
				<td width="14" rowspan="2" >&nbsp;</td>
		  	<td width="14" ><input  type="checkbox" name="bondadmission"   value="1" /></td>
		  	<td width="416"> Convocation Bon d'admission	</td>  
		  </tr>
			<tr>
				<td ><input  type="checkbox" name="convocation"     value="1" /></td>
			  <td>Convocation	</td>  
			</tr>
			      
			<tr>
				<td rowspan="6" bgcolor="#CCCCFF" align="center" ><b>Pose KT </b></td>
				<td bgcolor="#CCCCFF" ><input  type="checkbox" name="bilansanguin"   value="1" /></td>
			  <td bgcolor="#CCCCFF">Bilan sanguin </td>  
			</tr>
			<tr>
				<td bgcolor="#CCCCFF" ><input  type="checkbox" name="convocationpmc"   value="1" /></td>
			  <td bgcolor="#CCCCFF"> Convocation PMC </td>  </tr>		  
			<tr>
		    <td bgcolor="#CCCCFF" ><input  type="checkbox" name="ordonnancesavonantiseptique"   value="1" /></td>
	      <td bgcolor="#CCCCFF"> Ordonnance Savon antiseptique </td>  </tr>		  

			<tr>
		    <td bgcolor="#CCCCFF" ><input  type="checkbox" name="ordonnance_radiographie_pulmonaire"   value="1" /></td>
	      <td bgcolor="#CCCCFF"> Ordonnance Radiographie Pulmonaire Face + Profil </td>  </tr>		  
			<tr>
		    <td bgcolor="#CCCCFF" ><input  type="checkbox" name="ordonnance_echographie_doppler"   value="1" /></td>
	      <td bgcolor="#CCCCFF"> Ordonnance Echographie doppler des vaisseaux du cou </td>  </tr>		  

		  <tr>
		    <td bgcolor="#CCCCFF" ><input  type="checkbox" name="plan"   value="1" /></td>
	      <td bgcolor="#CCCCFF"> Plan </td>  </tr>		  		  
	      
	      
		  <tr><td rowspan="4" bgcolor="#FFFFCC"  align="center"><b>Ablation KT</b> </td>
		    <td bgcolor="#FFFFCC" ><input  type="checkbox" name="akt_bilansanguin"   value="1" /></td>
	      <td bgcolor="#FFFFCC">Bilan sanguin </td>  </tr>		  
		  <tr>
		    <td bgcolor="#FFFFCC" ><input  type="checkbox" name="akt_convocationpmc"   value="1" /></td>
	      <td bgcolor="#FFFFCC"> Convocation PMC	</td>  </tr>		  		  
		  <tr>
		    <td bgcolor="#FFFFCC" ><input  type="checkbox" name="ordonnancesavonantiseptique"   value="1" /></td>
	      <td bgcolor="#FFFFCC"> Ordonnance Savon antiseptique </td>  </tr>		  
		  <tr>
		    <td bgcolor="#FFFFCC" ><input  type="checkbox" name="plan"   value="1" /></td>
	      <td bgcolor="#FFFFCC"> Plan </td>  </tr>		  		  
		  <tr>
		  	<td rowspan="5" bgcolor="#99FFFF"  align="center"><b>Bilan sanguin et urinaire</b></td>
		    <td bgcolor="#99FFFF" ><input  type="checkbox" name="bsu_bilansanguin"   value="1" /></td>
	      <td bgcolor="#99FFFF"> Bilan sanguin (consultation)</td>  </tr>		  		  
		  <tr>
		    <td bgcolor="#99FFFF" ><input  type="checkbox" name="bsu_bilansanguin_fec75"   value="1" /></td>
	      <td bgcolor="#99FFFF">   Ordonnance bilan sanguin FEC 75 </td>  </tr>		  		  		  
			<tr>
			  <td bgcolor="#99FFFF" ><input  type="checkbox" name="bsu_Ordonnance_bs_surveillance_avastin"   value="1" /></td>
		    <td bgcolor="#99FFFF"> Ordonnance bilan sanguin surveillance Avastin et chimio</td> 
		  </tr>		  		  		  
			<tr>
			  <td bgcolor="#99FFFF" ><input  type="checkbox" name="bsu_Ordonnance_bs_surveillance_chimio"   value="1" /></td>
		    <td bgcolor="#99FFFF"> Ordonnance bilan sanguin surveillance Chimio</td> 
		  </tr>		  
			<tr>
			  <td bgcolor="#99FFFF" ><input  type="checkbox" name="bsu_Ordonnance_proteinurie"   value="1" /></td>
		    <td bgcolor="#99FFFF"> Ordonnance proteinurie</td> 
		  </tr>		  
 			<tr>
		  	<td bgcolor="#99FF0F"  rowspan="2"  align="center"><b>Elma Patch</b></td>
		    <td bgcolor="#99FF0F" ><input  type="checkbox" name="elma_patch"   value="1" /></td>
	      <td bgcolor="#99FF0F"> Ordonnance Emla patch et notice  d'application Elma</td>  </tr>		  		  

 			<tr bgcolor="#99FF0F">
		    <td><input  type="checkbox" name="ordonnance_prothese_capillaire"   value="1" /></td>
	      <td > Ordonnance Prothèse CAPILLAIRE</td>  </tr>		  		  

 			<tr bgcolor="#BBFFFF">
		  	<td  rowspan="13" align="center"><b>Ordonnances de medicament</b></td>
		    <td  ><input  type="checkbox" name="ordonnance_xeloda"   value="1" /></td>
	      <td >Ordonnance  Xeloda </td>  </tr>		  		  
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_tyverbt_xeloda"   value="1" /></td>
	      <td > Ordonnance Tyverb Xeloda </td>  </tr>	
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_motilium"   value="1" /></td>
	      <td > Ordonnance de MOTILIUM </td>  </tr>		  		  
	      
 			<tr bgcolor="#BBFFFF">
		    <td><input  type="checkbox" name="ordonnance_bain_de_bouche"   value="1" /></td>
	      <td > Ordonnance Bain de bouche</td>  </tr>		  		  

 			<tr bgcolor="#BBFFFF">
		    <td  ><input  type="checkbox" name="ordonnance_forlax"   value="1" /></td>
	      <td > Ordonnance Forlax  </td>  </tr>		  		  
 			<tr bgcolor="#BBFFFF">
		    <td  ><input  type="checkbox" name="ordonnance_premedication_taxol"   value="1" /></td>
	      <td > Prémédication TAXOL (xyzall)</td>  </tr>		  		  

	      
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_premedication_taxol_hebdo_34"   value="1" /></td>
	      <td> Ordonnance Premédication Taxol Hebdo (3 semaines sur 4)  </td>  </tr>		  		  
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_premedication_taxol_hebdo_12"   value="1" /></td>
	      <td > Ordonnance Premédication Taxol Hebdo (12 semaines) </td>  </tr>		  		  

 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_premedication_taxol_hebdo_j1j8"   value="1" /></td>
	      <td > Ordonnance Premédication Taxol J1 J8</td>  </tr>		  		  
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_premedication_taxotere"   value="1" /></td>
	      <td > Ordonnance Premédication Taxotere (medrol) </td>  </tr>		  		  
	  		  
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_traitement_preventif_des_nausees_1"   value="1" /></td>
	      <td > Ordonnance Traitement préventif des nausées n°1 (emend)</td>  </tr>		  		  
 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_traitement_preventif_des_nausees_2"   value="1" /></td>
	      <td > Ordonnance Traitement préventif des nausées n°2 (zophren)</td>  </tr>		  		  

 			<tr bgcolor="#BBFFFF">
		    <td ><input  type="checkbox" name="ordonnance_traitement_preventif_des_nausees_3"   value="1" /></td>
	      <td > Ordonnance Traitement préventif des nausées n°3 (taxol) a faire</td>  </tr>		  		  

			<tr bgcolor="#BBBBFF">
		  	<td  rowspan="2" align="center"><b>Ordonances prescription examen </b></td>
		    <td  ><input  type="checkbox" name="Ordonnance_eco_cardiaque_surveillance_anthracyclines"   value="1" /></td>
	      <td >Ordonnance échographie cardiaque Surveillance Anthracyclines </td>  </tr>		  		  
			<tr bgcolor="#BBBBFF">
		    <td><input  type="checkbox" name="Ordonnance_eco_cardiaque_surveillance_herceptin"   value="1" /></td>
	      <td > Ordonnance échographie cardiaque Surveillance Herceptin </td>  </tr>		  		  


			<tr bgcolor="#BBDDFF">
		  	<td  rowspan="2" align="center"><b>Ordonnances si fièvre</b></td>
		    <td  ><input  type="checkbox" name="Ordonnance_bilan_sanguin_si_fievre"   value="1" /></td>
	      <td >Ordonnance bilan sanguin si fièvre</td>  </tr>		  		  
			<tr bgcolor="#BBDDFF">
		    <td><input  type="checkbox" name="Ordonnance_OROKEN_si_fievre"   value="1" /></td>
	      <td >Ordonnance OROKEN  si fièvre </td>  </tr>		  		  

			<tr >
		    <td colspan="3" align="center" >	<input type="submit" name="Imprimer" id="Imprimer" value="Imprimer"  class="btn"/>		</td>
	    </tr>		  		  


		 </table>  
		 
		 
		<br />

	
		<input type="hidden" name="optvals" id="optvals" value="<?php print $optvals;?>" />					
	 
		<input type="hidden" name="nip" value="<?php print $nip?>">
		<input type="hidden" name="nom" value="<?php print $nom?>">
		<input type="hidden" name="sexe" value="<?php print $sexe?>">
		<input type="hidden" name="pat" value="<?php print $pat?>">
		<input type="hidden" name="med" value="<?php print $med?>">
		<input type="hidden" name="date_nais" value="<?php print $date_nais?>">
		<input type="hidden" name="service" value="<?php print $this_service_name ?>">

		<input type="hidden" name="date_entree" value="<?php print $date_entree?>">
		<input type="hidden" name="duree" value="<?php print $duree?>">
 		  			
		</form>
		</body>
		
<?php 

}








if (($urm!="560") &&($urm!="470") &&($urm!="010") &&($urm!="070")){
	echo $urm.": URM inconnu pour l'option d'impression!!!";
	}

?>

</html>
