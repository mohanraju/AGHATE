<?php
/*#########################################################
	
	Affiche_service_detail.php
	* Détail de chaque service
	* Affichage des lits et des patients
  Fichier appeler par AJAX
#########################################################*/
?>
<!--
on recupere les width car la page force les width
-->
<script>
var $tdwidth = $( ".service" ).innerWidth();
$( ".horaire" ).width($tdwidth);
$( ".date" ).width($tdwidth);

var $table_width = $( ".table_main" ).innerWidth();
$('.soustable').width($table_width);
</script>
<?php 



include "./commun/include/admin.inc.php";
include "./commun/include/mrbs_sql.inc.php";

ini_set("display_errors","1");
ini_set ('session.bug_compat_warn', 0); 
error_reporting(E_ALL);


$service_id=$_GET['AreaId'];

 
// gstion des services ouvert dans situation lits
if(isset($_SESSION['AreaOuvert']))
{
	$key=array_key_exists($service_id, $_SESSION['AreaOuvert']);
	if(!$key)
	{
		$_SESSION['AreaOuvert'][$service_id]['TrID']=$_GET['TrId'];
		$_SESSION['AreaOuvert'][$service_id]['TableId']=$_GET['TableId'];		
	}
	else
	{
		if($_GET['mode'] !='AUTO')
			unset($_SESSION['AreaOuvert'][$service_id]);
	}
}else
{
	$_SESSION['AreaOuvert'][$service_id]['TrID']=$_GET['TrId'];
	$_SESSION['AreaOuvert'][$service_id]['TableId']=$_GET['TableId'];	
}
 

$debug=false;

if ($_SESSION["today"] == "")
	$_SESSION["today"] = date('d/m/Y');
# forcer le jour d'aujourdhui si date est vide 

$today = $_SESSION["today"];
$vue = $_SESSION["vue"];

if (!isset($vue) || $vue=="accueil"){
	include "./config/config_val_journaliere.php";
	$vue = 'journaliere';
}
else
{
	if ($vue=='journaliere')
		include "./config/config_val_journaliere.php";
	if ($vue=='hebdomadaire')
		include "./config/config_val_hebdomadaire.php";
	if ($vue=='mensuelle')
		include "./config/config_val_mensuelle.php";	
	if ($vue=='accueil')
		include "./config/config_val.php";	
}								

$NbPx = $NombreJoursDansTableau * $NbTranches;
$NbPxComplet = ($NombreJoursDansTableau * ($NbTranches))*2;

$TdColour ="";
$TdAjoutResa = "";

/*if($debug)
{
	echo "<pre>";
	print_r($data);
	//exit;
}*/

for ($i=0;$i<$NbTranches;$i++)
{
	if ($hr_tranches[$i]==24)
		$heures_ut[$i] = 23;
	else
		$heures_ut[$i] = $hr_tranches[$i];
}

include "get_data.php";

$debug=false;

if ($debug){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	exit;
}

$RoomInfo=$Aghate->GetServiceInfoByServiceId($service_id);

echo "<br><h4>".$RoomInfo[0]['service_name']."</h4>";

echo "<table class='soustable' border='0' width='1200' cellspacing='0' cellpadding='0'>";

##################################################### Debut tbody #######################################################

echo "<tbody>";
foreach ($data as $key_lit => $_lit)
{

	if ($key_lit == 'Panier')
	{
		$TdColour.= '<tr><td>'.$key_lit .'</td>';
		$TdAjoutResa.= '<tr><td>'.$key_lit .'</td>';
		
		for ($dt = 0; $dt<count($dates); $dt++)
		{
			for ($h=0; $h < count($heures_ut)-1; $h++)
			{
				$PanierId=$Aghate->GetPanierIdByServiceId($service_id);
				$Pat=$Aghate->GetPatParLit($PanierId,$dates[$dt],$heures_ut[$h],$heures_ut[$h+1]);
				$NombrePat=count($Pat);
				list($ResaDay,$ResaMonth,$ResaYear)=explode("/",$dates[$dt]);

				$TdColour .=  "<td><a href=# onclick=\"Affiche_Panier('".$PanierId."','".$dates[$dt]."','".$heures_ut[$h]."','".$heures_ut[$h+1]."','".$service_id."'); return false;\">".$NombrePat."</a></td>";
				//	pour ajouter un resa
				$EditLink=$ModuleReservationEdit."?area=".$service_id."&room=".$PanierId."&year=".$ResaYear."&month=".$ResaMonth."&day=".$ResaDay."&hour=".$heures_ut[$h]."&page=day&table_loc=agt_loc&type_reservation=";	
				$TdAjoutResa .="<td><a href='#?'  onClick=\"OpenPopupResa('".$EditLink."')\"><img src='commun/images/new.png' border='0'></a></td>";
			}
		}
		$TdColour .= "</tr>";
		$TdAjoutResa .= "</tr>";

	}
	else
	{
		echo '<tr><td>'.$key_lit .'</td><td colspan='.$TotalColspan.'>';
		$nbr_lit=0;	
		$last_pat_fin_heure=0;
		
		//echo $date_deb;
		//every day init deb jour
		$C_DebTime=$date_deb;
		
		// nombres d'heures d'affichage 4 jours et 4 tranches : 96heures ...
		// en fonction de la vue selectionné
		$NombreDeHeuresPourAffichage = $NombreJoursDansTableau* $NbHeureDay;	
		foreach ($_lit as $key_jour => $_jour)
		{
			$cpt = 0;
			//echo "lit";
			foreach ($_jour as $key_pat => $_pat)
			{
				//echo "jour";
				//check des lits sans patient
				if(isset($_pat['noip']))
				{	
					$duree=$_pat['duree'];
					
					//======================================================================	
					// LIBRE si la date courrante est inférieur a la date début du patient
					// et que la date courrante correspond a la date du début d'affichage
					//======================================================================		
					if(($C_DebTime < $_pat['deb'] || $C_DebTime < $_pat['start_idp']) &&  $C_DebTime==$date_deb)
					{
						if ($_pat['start_idp']){
							$duree=($_pat['start_idp']-$C_DebTime)/3600;
							if($duree < 0.05) $duree = 0;
							$cur_duree=($duree * $PixelParHeure);
							// si ',' ça ne fonctionne pas 
							$cur_duree = str_replace(",",".",$cur_duree);
							if ($cur_duree >100) $cur_duree = 100; 
							$title="Libre pour une 1 duree de ".$duree." hrs" ;
							echo "<div id='libre' style='width: ".$cur_duree."%' title='$title'>Libre</div>";
							$C_DebTime = $_pat['start_idp'];	
							$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree;
							//apres duree inital du pat 
							$duree=$_pat['duree_idp'];
						}
						else{
							$duree=($_pat['deb']-$C_DebTime)/3600;
							if($duree < 0.05) $duree = 0;
							$cur_duree=($duree * $PixelParHeure);
							// si ',' ça ne fonctionne pas 
							$cur_duree = str_replace(",",".",$cur_duree);
							if ($cur_duree >100) $cur_duree = 100; 
							$title="Libre pour une 2  duree de ".$duree." hrs" ;
							echo "<div id='libre' style='width: ".$cur_duree."%' title='$title'>Libre</div>";
							$C_DebTime = $_pat['deb'];	
							$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree;
							//apres  duree inital du pat 
							$duree=$_pat['duree'];	
						}		
					}
										
					//======================================================================	
					// LIBRE si la date courrante est inférieur a la date début du patient
					// et que la date courrante n'est pas égale a la date du début d'affichage
					//======================================================================
					if(($C_DebTime < $_pat['deb'] || $C_DebTime < $_pat['start_idp']) &&  $C_DebTime!=$date_deb)
					{
						if ($_pat['start_idp']){
							$duree=($_pat['start_idp']-$C_DebTime)/3600;
							if($duree < 0.05) $duree = 0;
							$cur_duree=($duree * $PixelParHeure);
							// si ',' ça ne fonctionne pas 
							$cur_duree = str_replace(",",".",$cur_duree);
							if ($cur_duree >100) $cur_duree = 100; 
							$title="Libre pour une 3  duree de ".$duree." hrs" ;
							echo "<div id='libre' style='width: ".$cur_duree."%' title='$title'>Libre</div>";
							$C_DebTime = $_pat['start_idp'];	
							$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree;
							//apres duree inital du pat 
							$duree=$_pat['duree_idp'];
						}
						else{
							$duree=($_pat['deb']-$C_DebTime)/3600;
							if($duree < 0.05) $duree = 0;
							$cur_duree=($duree * $PixelParHeure);
							// si ',' ça ne fonctionne pas 
							$cur_duree = str_replace(",",".",$cur_duree);
							if ($cur_duree >100) $cur_duree = 100; 
							$title="Libre pour une 4  duree de ".$duree." hrs" ;
							echo "<div id='libre' style='width: ".$cur_duree."%' title='$title'>Libre</div>";
							$C_DebTime = $_pat['deb'];	
							$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree;
							//apres duree inital du pat 
							$duree=$_pat['duree'];
						}			
					}
					
					/*====================================
					 * INDISPONIBILITE
					=======================================*/
					//echo $C_DebTime;
					//echo date('d/m/y H:i:s',$C_DebTime).">=".date('d/m/y H:i:s',$_pat['start_idp']);
					if($_pat['start_idp'] && $C_DebTime >= $_pat['start_idp'])
					{
						if ($date_fin <= $_pat['end_idp']){
							$duree_idp = ($date_fin-$_pat['start_idp'])/3600;
							if($duree_idp < 0.05) $duree_idp = 0;
							$px_idp = ($duree_idp * $PixelParHeure);
							$px_idp = str_replace(",",".",$px_idp);
							if ($px_idp >100) $px_idp = 100;
							$title_idp="Indisponible 1 du ".date('d/m/y H:i:s',$_pat['start_idp'])." au ".date('d/m/y H:i:s',$_pat['end_idp']);
							if (strlen($_pat['motif_idp'])>0) $title_idp.="<br />Motif : ".$_pat['motif_idp'];
							echo "<div id='idp' style='width: ".$px_idp."%' title='$title_idp'>Indisponible</div>";
							$C_DebTime = $_pat['end_idp'];	
							//apres  duree inital du pat 
							$duree=$_pat['duree_idp'];		 
						}
						else{
							$duree_idp = ($_pat['end_idp']-$C_DebTime)/3600;
							if($duree_idp < 0.05) $duree_idp = 0;
							$px_idp = ($duree_idp * $PixelParHeure);
							$px_idp = str_replace(",",".",$px_idp);
							if ($px_idp >100) $px_idp = 100;
							$title_idp="Indisponible 2 du ".date('d/m/y H:i:s',$_pat['start_idp'])." au ".date('d/m/y H:i:s',$_pat['end_idp']);
							if (strlen($_pat['motif_idp'])>0) $title_idp.="<br />Motif : ".$_pat['motif_idp'];
							echo "<div id='idp' style='width: ".$px_idp."%' title='$title_idp'>Indisponible</div>";
							$C_DebTime = $_pat['end_idp'];	
							//$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree_idp;
							//apres  duree inital du pat 
							$duree=$_pat['duree_idp'];		 
						}
					}
					
					/*====================================
					 * INDISPONIBILITE
					=======================================*/
					elseif($_pat['start_idp'] && $date_fin <= $_pat['end_idp'])
					{
						$duree_idp = ($date_fin-$_pat['start_idp'])/3600;
						if($duree_idp < 0.05) $duree_idp = 0;
						$px_idp = ($duree_idp * $PixelParHeure);
						$px_idp = str_replace(",",".",$px_idp);
						if ($px_idp >100) $px_idp = 100;
						$title_idp="Indisponible 3 du ".date('d/m/y H:i:s',$_pat['start_idp'])." au ".date('d/m/y H:i:s',$_pat['end_idp']);
						if (strlen($_pat['motif_idp'])>0) $title_idp.="<br />Motif : ".$_pat['motif_idp'];
						echo "<div id='idp' style='width: ".$px_idp."%' title='$title_idp'>Indisponible</div>";
						$C_DebTime = $_pat['end_idp'];	
						//apres  duree inital du pat 
						$duree=$_pat['duree_idp'];		 
					}
					
					//========================================================
					//	current patient start time of today
					//	calcul la duree restante de pat
					//  check si la date courrante est supérieur a date deb patient 
					//  c'est a dire que le patient a déja commencé avant il calcule reste 
					//========================================================	
					if($C_DebTime > $_pat['deb'])
					{
						$duree=($_pat['fin'] - $C_DebTime )/3600;
						if($duree < 0.05) $duree = 0;
						$C_DebTime=$_pat['fin'];	
					}

					//========================================================			
					//	verif  dernier jour dans le tableau 
					// check si la date fin du tableau est inférieur a date fin du patient
					//========================================================
					if($date_fin < $_pat['fin'])
					{
						$duree=($date_fin - $_pat['deb'] )/3600;
						if($duree < 0.05) $duree = 0;
						$C_DebTime=$_pat['fin'];	
					}
					
					//==========================================================================================
					// check si la durée de ce pat est superieur a NombreDeHeuresPourAffichage dans le Tableau
					//==========================================================================================
					if ($duree > $NombreDeHeuresPourAffichage)
					{
						$duree=$NombreDeHeuresPourAffichage;
						if($duree < 0.05) $duree = 0;
					}
					
					$C_DebTime=$_pat['fin']; // Met le current time a la date fin du patient
					
					if($_pat['start_idp']){
						$C_DebTime = $_pat['end_idp'];
						$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree_idp;
					}
					
					if($duree < 0.05) $duree = 0;	
					$cur_duree=($duree * $PixelParHeure);			
					// si ',' ça ne fonctionne pas 
					$cur_duree = str_replace(",",".",$cur_duree);
					if ($cur_duree > 100) $cur_duree = 100;
					
					if($_pat['deb']){
						$title=$_pat['pat']." <br />Du ".date('d/m/Y H:i:s',$_pat['deb']).
								" Au ".date('d/m/Y H:i:s',$_pat['fin'])."<br />Duree : ".$_pat['duree']." Hrs";
					}
					//================================
					// affichage du patient
					//================================
					//icone type
					$ImageLink = "";
					$UrlView=$ModuleReservationView."?id=".$_pat['entry_id']."&table_loc=agt_loc&type_reservation=";
					$lien = "<a href='#?'  onClick=\"OpenPopupResa('".$UrlView."')\"   title=\"".$title."\" >";
					
					echo "<div id='pat' style='width: ".$cur_duree."%' title='".$title."'>".$lien.$ImageLink.$_pat['pat']."</a></div>";	
					
				
					//===========================================================
					// recalcul le duree restante a afficher pour le prochain patient
					//===========================================================
					if(!$_pat['start_idp'])
						$NombreDeHeuresPourAffichage = $NombreDeHeuresPourAffichage - $duree;
				}
			}
		}
		//###############
		// debug
		//###############
		if($debug)
		{	
			echo "<br />last: ".$C_DebTime ."<". $NombreDeHeuresPourAffichage;
		}
		
		
		//=============================================
		// Verifie si  Lit libre 
		//=============================================	
		if( $NombreDeHeuresPourAffichage > 0 && $NombreDeHeuresPourAffichage < $NbHeuresTotal)
		{
			//echo "affiche";
			$duree=$NombreDeHeuresPourAffichage;
			if($duree < 0.05) $duree = 0;
			$cur_duree=($duree * $PixelParHeure) ;
			// si ',' ça ne fonctionne pas 
			$cur_duree = str_replace(",",".",$cur_duree);
			if ($cur_duree >100) $cur_duree = 100; 
			$title="Libre pour 5  une duree de ".$duree." hrs" ;
			echo "<div id='libre' style='width: ".$cur_duree."%' title='.$title.'>Libre</div>";
		}
		
			
		//=============================================
		// Lit libre sur toute la durée du tableau 
		//=============================================
		if ($NombreDeHeuresPourAffichage == $NbHeuresTotal)
		{
			$duree=$NombreDeHeuresPourAffichage;
			if($duree < 0.05) $duree = 0;
			$title="Libre pour 6 une duree de ".$duree." hrs" ;
			echo "<div id='libre' style='width: 100%' title='.$title.'>Libre</div>";
		}
		
		if ($debug)
			echo "<br />".$NombreDeHeuresPourAffichage;
		echo "</td></tr>";
		
	}
}
// tr pour ajouter les reservation avec les croix verts
echo $TdAjoutResa	;	

echo "</tbody>";


//##################################################### FIN TBODY #####################################################

//##################################################### DEBUT  THEAD #####################################################
echo " 
<thead>";
if ($vue=='mensuelle')
	echo "
	<div><tr><th class='date'>Dates</th>".$ColDates."</tr></div>";
else
	echo "
	<div><tr><th class='date'>Dates</th>".$ColDates."</tr>
	<div><tr><th class='horaire'>Horaires</th>".$ColTrancheHoraires."</tr></div>";
echo $TdColour;	
echo "
</thead>";
//##################################################### FIN  THEAD #####################################################

echo "</table>";
echo "<br>"
?>

