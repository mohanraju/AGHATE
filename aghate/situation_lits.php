<?php 

#########################################################################
#                         Situation lits.php                       		#
#				Tableau de situation des lits 						    #
#                 									                    #
#																        #
#########################################################################

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
include "./config/config.php";
#Paramètres de connection
require_once("./commun/include/settings.inc.php");

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
if (empty($area)) $area = get_default_area();
$_SESSION["area"]=$this_area_urm;



// Paramètres langage
include "./commun/include/language.inc.php";

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

// On vérifie une fois par jour si le délai de confirmation des réservations est dépassé
// Si oui, les réservations concernées sont supprimées et un mail automatique est envoyé.
// On vérifie une fois par jour que les ressources ont été rendue en fin de réservation
// Si non, une notification email est envoyée
if (getSettingValue("verif_reservation_auto")==0) {
    verify_confirm_reservation();
    verify_retard_reservation();
}

# print the page header
print_header($day, $month, $year, $area, $type_session);

?>

	  <meta charset="utf-8"/>
	  <title>Situation des lits</title>
	  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	  <meta name="description" content=""/>
	  <meta name="author" content=""/>
	
		<link href="./commun/style/situation_lits.css" rel="stylesheet" type="text/css" media="all" />
		<link rel="stylesheet" href="./commun/style/jquery-ui.css" />
		<script type="text/javascript" src="./commun/js/jquery-1.9.1.js"></script>
		<script type="text/javascript" src="./commun/js/datepicker.js"></script>
		<script type="text/javascript" src="./commun/js/info_bulle.js"></script>
	  <script type="text/javascript" src="./commun/js/fonctions_aghate.js"  ></script>	
		<script language="javascript" type="text/javascript" src="./commun/js/Communfonctions.js"></script>		
		<script type="text/javascript" src="./commun/js/jquery-ui-1.10.3.custom.js"></script>			  
		<link rel="stylesheet" href="./commun/style/redmond/jquery-ui-1.10.3.custom.css" />	  
		
  <?php 
  
  //============================================
  // Récupération de la date
  //============================================
	$_SESSION["today"] = $_GET["today"];
	$today = $_GET["today"];
	if ($today == "")
		$today = date('d/m/Y');
	
	$_SESSION["vue"] = $_GET["vue"];
	$vue = $_GET["vue"];
	/* FICHIER A MODIFIER : STYLE.CSS DANS THEME DEFAULT CSS */
	
	?> 
	<form action="situation_lits.php">
		<div style='text-align:center'>															
	 		Date: <input type="text"  id="date_deb" name="today"  value="<?php print $today?>" onchange="refresh_page('<?php print $vue?>')":/>
	 		<br />
	 		<input type="button" name="accueil" 		value="Vue par defaut" 		onclick="refresh_page('accueil')" 		class="btn" > 
			<input type="button" name="journaliere" 	value="Vue journaliere" 	onclick="refresh_page('journaliere')" 	class="btn" > 
			<input type="button" name="hebdomadaire" 	value="Vue hebdomadaire"  onclick="refresh_page('hebdomadaire')" 	class="btn" > 
			<input type="button" name="mensuelle" 		value="Vue mensuelle" 		onclick="refresh_page('mensuelle')" 	class="btn" > 
		</div>				
	</form>
	

<?php

ini_set("display_errors","1");

ini_set('session.bug_compat_warn', 0);
ini_set('session.bug_compat_42', 0);



include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";

//##################################
// Check include
//##################################

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

$debut = time();

$mysql= new MySQL();
$Aghate=  new Aghate();
$Aghate->NomTableLoc = "agt_loc";

//recupère session user
$user = getUserName();
 
$area_res=$Aghate->GetAllServiceAuthoriser($user,$_SESSION['statut'],$enable_peroides=false);


$heures_ut = array();

//===========================================
// prépare tableau pour calcul du nb des lits
//===========================================

for ($i=0;$i<$NbTranches;$i++)
{
	if ($hr_tranches[$i]==24)
		$heures_ut[$i] = 23;
	else
		$heures_ut[$i] = $hr_tranches[$i];
}

//===========================================
// lien jour moins un et jour plus un 
//===========================================
list ($day,$month,$year) = explode ("/",$today);
$todayplusun = date("d/m/Y", mktime(0, 0, 0,$month, $day+1, $year));
$todaymoinsun = date("d/m/Y", mktime(0, 0, 0,$month, $day-1, $year));


$colspan = $NombreJoursDansTableau * ($NbTranches);

?>

<div id="form">
<table align="center" style="border:0;width : 1200px; text-align : center;">
	<tr>
		<td style="border:0;text-align:left;"> <a href="situation_lits.php?today=<?php print $todaymoinsun ?>&amp;vue=<?php print $vue ?>"> << Voir jour precedent </a> </td>
		<td style="border:0;text-align:center;">  <b>Situation des lits </b></td>
		<td style="border:0;Text-align:right;"> <a href="situation_lits.php?today=<?php print $todayplusun ?>&amp;vue=<?php print $vue ?>"> Voir jour suivant >> </a> </td>
	</tr>
</table>
</div>


<table class = "table_main">

<tfoot>
	<?php
		$TotalLit = 0;
		$TTotalLitOcc = array();
		$TotalLitOcc = array();
		$nombre_service=count($area_res);
	
//################### Appel des fonction de classe Aghate permettant l'affichage du nombre de lit total et du nombre de lit occupé  ###################
// boucle sur les services
	for($i=0;$i < $nombre_service; $i++) 
	{
		
	//============================================
	// Affichage des noms de services 	
	//============================================
			$LitDispo = $Aghate->CompteLit($area_res[$i]['id']);
			$litall = intval($LitDispo);
			$TotalLit +=$LitDispo;

		echo '
		<tr> 
			<td id="id_'.$i.'"  class="nomservice" > <a href="javascript:void(0)" onclick="AfficheDetail(\'trId_'.$i.'\',\'tableId_'.$i.'\',\''.$area_res[$i]['id'].'\',\'MANUEL\')">'.$area_res[$i]['service_name'].' : '.$LitDispo.' lits</a></td>';		
			

			// boucle sur les dates
			for ($s=0; $s < count($dates); $s++) {
				
			//============================================
			// Affichage des lits dispo dans les services
			// et calcul des lits tot dispo pour affichage hopital
			//============================================
					for ($h=0; $h < count($heures_ut)-1; $h++) {
						
						$var = $Aghate->CompteLitOccupe($area_res[$i]['id'],$dates[$s],$heures_ut[$h],$heures_ut[$h+1]);
						if (!isset($TTotalLitOcc[$dates[$s]][$heures_ut[$h]]))
						{
							$TTotalLitOcc[$dates[$s]][$heures_ut[$h]] = 0;
							$TTotalLitOcc[$dates[$s]][$heures_ut[$h]] = intval($TTotalLitOcc[$dates[$s]][$heures_ut[$h]]) + $var;
						}
						else
							$TTotalLitOcc[$dates[$s]][$heures_ut[$h]] = intval($TTotalLitOcc[$dates[$s]][$heures_ut[$h]]) + $var;
						$litocc = intval($var);
						$litdiff = $litall-$litocc;
						if (($litocc >0)  && ($litall > 0))
							$pct=intval( ($litocc/$litall) *100);
						else
							$pct=0;	
						switch ($pct){
							case 0:
								$couleur="dispo";								// CODE COULEUR
								break;
						
							case (($pct > 0) && ($pct < 25)):
								$couleur="dispo100";							// CODE COULEUR
								break;
							case (($pct > 25) && ($pct < 51) ):
								$couleur="dispo75";								// CODE COULEUR
								break;
							case (($pct > 50) && ($pct < 76) ):
								$couleur="dispo50";								// CODE COULEUR
								break;
							case (($pct > 75) && ($pct < 100) ):
								$couleur="dispo25";								// CODE COULEUR
								break;
							case ($pct > 99):
								$couleur="nondispo";							// CODE COULEUR
								break;
						}
							echo '<td class = "'.$couleur.'" colspan = "1">'.$litdiff.  '</td>';
					}
				}
				echo '	
					</tr>
					<tr style="display:none" ID="trId_'.$i.'"><td colspan="'.$colspan.'"><table ID="tableId_'.$i.'"><tr><td></td></tr></table></td></tr>';			 	
	}
	
	?>
</tfoot>

<tbody>
	<?php
		
	//============================================
	// Affichage pour l'hoôpital 
	//============================================
	echo '<tr> <td class="hopital">Hôpital : '.$TotalLit.' lits </td>';
	for ($s=0; $s < count($dates); $s++) 
	{
		for ($h=0; $h < count($heures_ut)-1; $h++) 
		{
			$litocc = intval($TTotalLitOcc[$dates[$s]][$heures_ut[$h]]);
			$litTotal = intval($TotalLit);
			$litdiff = $litTotal-$litocc;
			$pct=intval( ($litocc/$litTotal) *100);
			
			if( ($litocc >0 ) &&  ($litTotal > 0) ) 
				$pct=intval( ($litocc/$litTotal) *100);
			else
				$pct=0;	
							
			switch ($pct){
				case 0:
					$couleur="dispo";								// CODE COULEUR
					break;
			
				case (($pct > 0) && ($pct < 25)):
					$couleur="dispo100";							// CODE COULEUR
					break;
				case (($pct > 25) && ($pct < 51) ):
					$couleur="dispo75";								// CODE COULEUR
					break;
				case (($pct > 50) && ($pct < 76) ):
					$couleur="dispo50";								// CODE COULEUR
					break;
				case (($pct > 75) && ($pct < 100) ):
					$couleur="dispo25";								// CODE COULEUR
					break;
				case 100:
					$couleur="nondispo";							// CODE COULEUR
					break;
			}
			echo '<td class = "'.$couleur.'" colspan = "1">'.$litdiff. '</td>';	
		}
	}
	?>	
	
</tbody>


<div  class="header">
<thead>
	<tr>
		<TH class="service">SERVICE</TH>
		<?php
			echo $ColDates;
		?>	
	</tr>
	<?php
		if($vue!= 'mensuelle')
			echo '
				<tr>
					<td >Horaires</td>'.$ColTrancheHoraires.'</tr>';
	?>
	</thead>
</div>	

</table>	
<!--
===============================================================================
PopUP list des patients dans le couloir
===============================================================================
-->
<div id="div_patient_panier" title="Patients dans le panier">
  <div id="PanierList"></div>
</div>

 
<?php
$fin = time();


//gestion d'affichage des services deja ouvert

// gstion des services ouvert dans situation lits
 
if(isset($_SESSION['AreaOuvert']))
{
	foreach($_SESSION['AreaOuvert'] as $key => $val)
	{
		echo "<script type='text/javascript'>
				AfficheDetail('".$val['TrID']."','".$val['TableId']."','".$key."','AUTO')
		</script>
		";
	}
}
 
?>

  </body>
  
</html>
