<?php

//======================================================
// Fichier config avec valeur en commun
// Nombre de jour dans le tableau, tranches horaires ...
// Fichier Config base accueil
// =====================================================

list ($day,$month,$year) = explode ("/",$today);

$NombreJoursDansTableau=4;

$NbHeureDay = 24;

$hr_tranches=array(0,8,12,18,24);  

// morning_start and evening_end declaraions
$jour_starts=0;
$jour_ends=24;

//	Calcul pixels
// 30 min = 6 pixel
// 1 heure = 12 pixel
// par jour 24 * 12 = 288
$NbTranches = count ($hr_tranches);

$calc = $NbHeureDay * $NombreJoursDansTableau;

$Pourcentage = number_format((100/$calc),2);

//$PixelParHeure=240/($jour_ends-$jour_starts);
$PixelParHeure=$Pourcentage;

$PixelHead = 240/($jour_ends-$jour_starts);
$PixelHead = 10;

$NbHeuresTotal = $NombreJoursDansTableau * 24;

$TodayDate = date("d/m/Y");
//==============================================
//	prepare dates array
// 	heure tranches
//	preparation Header cols
//==============================================
$ColDates = "";
$ColTrancheHoraires = "";

for($_dt=0; $_dt  < $NombreJoursDansTableau ; $_dt++)
{
	$_jr=intval($day)+$_dt;
	$dates[]=date("d/m/Y",mktime(0,0,0,$month,$_jr,$year));
	// prepare Header with dates
	$TrancheParJour= count($hr_tranches) -1;
	if (($dates[$_dt])==$TodayDate)
		$ColDates .="<th colspan='".$TrancheParJour."' class='today'>".$dates[$_dt]."</th>";
	else
		$ColDates .="<th colspan='".$TrancheParJour."'>".$dates[$_dt]."</th>";
		// afichage des heures 
	for ($_trnch=0; $_trnch < $TrancheParJour; $_trnch++)
	{	$titre=($hr_tranches<10?'0'.$hr_tranches[$_trnch]:$hr_tranches[$_trnch])."-".($hr_tranches<10?'0'.$hr_tranches[$_trnch+1]:$hr_tranches[$_trnch+1]);
		$nbr_heure=$hr_tranches[$_trnch+1]- $hr_tranches[$_trnch];
		$cur_duree=floor($nbr_heure * $PixelHead);
		$ColTrancheHoraires.="<th><div style='width:".$cur_duree."px'>$titre</div></th>";
	}	
}
// calcul nombre de colspan
$TotalColspan= $NombreJoursDansTableau * ($NbTranches-1);

//declaration des périodes
$date_deb = mktime(0,0,0,$month,$day,$year);
$date_fin = mktime(0, 0, 0, $month, $day+$NombreJoursDansTableau, $year);



?>
