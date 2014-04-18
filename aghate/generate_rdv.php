<?php
ini_set("memory_limit","12M");
require("fpdf16/fpdf.php");
require_once('fpdi/fpdf_tpl.php');
require_once('fpdi/fpdi_pdf_parser.php');
require_once('fpdi/fpdi.php');

class PDF extends FPDI
{

	
	function AjouterText($posx,$text)
	{
	  $this->write($posx,$text);   
	}
	
	function BasicTable($data)
	{
	    $this->Cell(120,5,$data,0,0,'C');
	    $this->Ln();

	}
	function BasicTable2($data)
	{
	    $this->MultiCell(120,5,$data,1);
	    $this->Ln();
	}	
	function BasicTable1($data)
	{
	    $this->MultiCell(120,5,$data,0);
	    $this->Ln();
	}

	function Header()
	{

	}	
	

}




function convert( $lesMinutes )
{
	$heures = floor( $lesMinutes / 60 );
	$minutes = $lesMinutes % 60 ;
	$heures = sprintf( "%02s" , $heures );
	$minutes = sprintf( "%02s" , $minutes );
	return( $heures . ":" . $minutes );
}


// récuparation des vals posté 
if (count($_POST)) {
	while (list($key, $val) = each($_POST)) {
		$$key = $val;
	}
}

// récuparation des vals posté 
if (count($_GET)) {
	while (list($key, $val) = each($_GET)) {
		$$key = $val;
	}
}



	$pats = explode("(", $pat);

	$pat=$pats[0];
	$nip=substr($pats[1],0,10); // à voir
	$date_nais=substr($pats[2],0,10); // à voir 
	$sexe=substr($pats[3],0,1); // à voir 



list($time,$date)=explode("-",$date_entree);
$date_entree= $date." à ". $time ;
 
if (strcmp($sexe,"M")== 0) $titre="Monsieur";
if (strcmp($sexe,"F")== 0) $titre="Madame";

//====================================================================================
// on commance le stuff here to prepare the PDF
//====================================================================================
	$pdf=new PDF();

	/*##########################################"
	// Printing optins de GASTRO
	/*##########################################"*/
	if ($convocation){
		$pdf= Print_rdv($pdf,$uh,$nip,$date_entree,$med,$titre,$pat,$duree,$date_nais,$sexe,$adresse);
	}	
	if ($presc_kleen){
			$pdf= Print_prescription_klean($pdf,$med,$pat,$date_nais);
	}
	if ($presc_sang){
		$pdf= Print_prescription_sang($pdf,$med,$pat,$date_nais);
	}
	if ( ($presc_autre) && ($med_liste)){
		$pdf= Print_prescription_autre($pdf,$med,$pat,$date_nais,$med_liste);
	}
	if ($notice_PKP){
		Print_notice_PKP($pdf);
	}	
	if ($notice_COL){
		$pdf= Print_notice_coloscopie($pdf);
	}
	if ($notice_ANSHDJ){
		$pdf= Print_notice_ANSHDJ($pdf);
	}
	if ($notice_ANSHDS){
		$pdf= Print_notice_ANSHDS($pdf);
	}

	if ($endoscopie_digestive){
		$pdf=Print_Notices($pdf,"images/courriers/prescription_endoscopie_digestive.pdf");
	}	
	if ($Douleur_Soins_Palliatifs)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/Douleur_Soins_Palliatifs_Algoplus_EMSP.pdf");
	}
	if ($Etiquettes_autocol_perfusion){
		$pdf=Print_Notices($pdf,"images/courriers/etiquettes_autocol_perfusion.pdf");
	}	
	if ($Examen_avant_DIVLD){
		$pdf=Print_Notices($pdf,"images/courriers/Examen_avant_DIVLD.pdf");
	}	
	if ($Prescription_Normacol){
		$pdf=Print_Notices($pdf,"images/courriers/Prescription_Normacol.pdf");
	}	
	if ($Prescription_PBH){
		$pdf=Print_Notices($pdf,"images/courriers/prescription_PBH.pdf");
	}		
	if ($Prescription_Suivi_Remicade){
		$pdf=Print_Notices($pdf,"images/courriers/Prescription_Suivi_Remicade.pdf");
	}				
	if ($Surveillance_transfusionnelle){
		$pdf=Print_Notices($pdf,"images/courriers/surveillance_transfusionnelle.pdf");
	}				
	if ($_350se_hospit){
		$pdf=Print_Notices($pdf,"images/courriers/350se_hospitalisation.pdf");
	}				
	if ($_350se_sortie_ide){
		$pdf=Print_Notices($pdf,"images/courriers/350se_Sortie_IDE.pdf");
	}				

	if ($notice_CATHETERISME)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/notice_catheterisme.pdf");
	}
	if ($Infos_coloscopie)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/Infos_coloscopie.pdf");
	}
	if ($Infos_echo_endoscopie)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/Infos_echo_endoscopie.pdf");
	}
	if ($Infos_enteroscopie)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/Infos_enteroscopie.pdf");
	}
	if ($Infos_gastrocopie)
	{
 		$pdf=Print_Notices($pdf,"images/courriers/Infos_gastorscopie.pdf");
	}

	
	
	/*##########################################"
	// printing optins de HEMATOLOGIE
	/*##########################################"*/
	if($opt_print){
		Print_rdv_hematologie($pdf,$nip,$pat,$adresse,$date_nais,$service,$opt_print,$optvals);
	}
	
	if($abl_kt){
		Print_rdv_ablation_cat($pdf,$nip,$pat,$date_entree);
	}
	
	if($pose_kt){
		Print_rdv_pose_kt($pdf,$nip,$pat,$date_entree);
		//Print_rdv_pose_kt($pdf,$nip,$pat,$date_entree);	
	}
	
	//print options added le 17/03/2011 by mohanraju
	
	if($rdv_hdg_greffe){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/rdv_HDJ_greffe.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		
		
		$adr_print="$pat \n$adresse[0]\n$adresse[1]\n$adresse[2]\n$adresse[3]";
		$pdf->SetFont('Times','B',12);

		$c_line=50;
		$pdf->SetXY(125, $c_line);
		$pdf->MultiCell(110,5,$adr_print,0);
		$c_line=$c_line+49;
		$pdf->SetXY(100, $c_line);
		$pdf->AjouterText(5, $nip);
		$pdf->SetXY(172, $c_line+1);
		$pdf->AjouterText(5,date("d/m/Y"));
		$c_line=$c_line+5;
		$pdf->SetXY(108, $c_line);
		$pdf->AjouterText(5,$date_nais." (".$sexe.")");
	}
	
 
	if($rdv_HDJ_C6){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/rdv_HDJ_greffe.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		
		
		$adr_print="$pat \n$adresse[0]\n$adresse[1]\n$adresse[2]\n$adresse[3]";
		$pdf->SetFont('Times','B',12);

		$c_line=50;
		$pdf->SetXY(125, $c_line);
		$pdf->MultiCell(110,5,$adr_print,0);
		$c_line=$c_line+49;
		$pdf->SetXY(100, $c_line);
		$pdf->AjouterText(5, $nip);
		$pdf->SetXY(172, $c_line+1);
		$pdf->AjouterText(5,date("d/m/Y"));
		$c_line=$c_line+5;
		$pdf->SetXY(108, $c_line);
		$pdf->AjouterText(5,$date_nais." (".$sexe.")");
		
	}
	if($ordonnance_tagamet_atarax){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/ordonnance_tagamet_atarax.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 139);
		$pdf->AjouterText(5,date("d/m/Y"));		

	}
	if($ordonnance_tagamet_xyzall){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/ordonnance_tagamet_xyzall.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 139);
		$pdf->AjouterText(5,date("d/m/Y"));		

	}
	if($ordonnance_emla){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/ordonnance_emla.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 139);
		$pdf->AjouterText(5,date("d/m/Y"));		

	}
	
	if($numeration_formule_sanguine_M3){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/numeration_formule_sanguine_M3.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 129);
		$pdf->AjouterText(5,date("d/m/Y"));
	}
	if($numeration_formule_sanguine_C6){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/numeration_formule_sanguine_C6.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 134);
		$pdf->AjouterText(5,date("d/m/Y"));
	}
	if($bilan_pose_KT){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/bilan_pose_KT.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 144);
		$pdf->AjouterText(5,date("d/m/Y"));
	}
	if($bilan_ablation_KT){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/bilan_ablation_KT.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 		

		$pdf->Image('images/courriers/etiquette.jpg',130,40,50,20);
		$c_line=35;
		$pdf->SetFont('Times','B',12);
		$pdf->SetXY(130, $c_line);
		$msg=" NIP :  $nip \n $nom \n Ne(e) le  $date_nais ($sexe)";
	  $pdf->MultiCell(80,6,$msg,0,'L');
		
		$pdf->SetFont('','B',10);		
		$pdf->SetXY(57, 144);
		$pdf->AjouterText(5,date("d/m/Y"));
	}
	
		/*$pdf->AddPage(); 
		// set the sourcefile 
		$pdf->setSourceFile('images/courriers/QuestActiviPhys_1.pdf'); 
		// import page 1 
		$tplIdx = $pdf->importPage(1); 
		// use the imported page and place it at point 10,10 with a width of 100 mm 
		$pdf->useTemplate($tplIdx, 10, 10, 200); 
	*/	

	$pdf->SetDisplayMode('fullwidth','single');
	$pdf->Output();
	//$pdf->Output('d:/convocation.pdf','F');
//====================================================================================
// Function Print Convocation
//====================================================================================
function Print_rdv($pdf,$uh,$nip,$date_entree,$med,$titre,$pat,$duree,$date_nais,$sexe,$adresse){
	$c_line=50;
	$date=date("d/m/Y");
	$pdf->AddPage(); 
	$pdf->SetLeftMargin(70);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->setSourceFile('images/courriers/tete_f5.pdf'); 
	$tplIdx = $pdf->importPage(1); 
	$pdf->useTemplate($tplIdx, 2, 2, 200); 	
	
 	// identitié et UH du patient
	$c_line=25;
	
	$pdf->SetXY(60, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,"NIP : $nip");
	$pdf->SetFont('','');
	$c_line=$c_line+5;
	$pdf->Image('./commun/images/codes/'.$nip.'.gif',62,$c_line,30,6);			
	$c_line=$c_line+8;
	$pdf->SetXY(60, $c_line);
	$pdf->AjouterText(5,"$uh");
		
	
	//adresse du patient
	$pdf->SetFont('Times','B',12);	
	$c_line=50;
	$pdf->SetXY(100, $c_line);
	$pdf->AjouterText(5,$pat);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[0]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[1]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[2]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[3]);
	$pdf->SetFont('','',12);	
	
	$c_line=$c_line+20;
	$pdf->SetXY(150, $c_line);
	$pdf->AjouterText(5,"PARIS, le ");
	$pdf->AjouterText(5," $date");
	$c_line=$c_line+10;		

	// contenu du document
	$c_line=110;
	$pdf->SetXY(70, $c_line);
	$ligne1="Le Docteur  $med, après vous avoir examiné(e) vous a conseillé de vous faire hospitaliser dans notre service.";
	$pdf->BasicTable1($ligne1);
	
	$c_line=$c_line+15;
	$pdf->SetXY(70, $c_line);
	$ligne2="Nous vous réservons donc un lit.";
	$pdf->BasicTable1($ligne2);
	
	$c_line=$c_line+7;
	$pdf->SetXY(70, $c_line);
	$ligne3_row1="DATE DENTREE : $date_entree";
	$ligne3_row2="DUREE DHOSPITALISATION : environ   $duree ";
	$ligne3_row3="Prière de vous présenter à jeun.";
	$ligne3=$ligne3_row1."\n".$ligne3_row2."\n".$ligne3_row3;
	$pdf->BasicTable2($ligne3);
	
	$c_line=$c_line+25;
	$pdf->SetXY(70, $c_line);
	$ligne4="Si un examen sous anesthésie a eu lieu le jour de la sortie, vous nêtes pas autorisé(e) à repartir seul(e). Prévoyez une personne qui vous raccompagnera et restera auprès de vous  durant la nuit.";
	$pdf->BasicTable2($ligne4);
	
	$c_line=$c_line+25;
	$pdf->SetXY(70, $c_line);
	$ligne5="Le jour de votre hospitalisation, vous voudrez bien vous présenter au bureau des admissions de lhôpital, muni dune pièce didentité, de votre carte vitale, de votre attestation de mutuelle et de la présente convection.";
	$pdf->BasicTable1($ligne5);
	
	$c_line=$c_line+20;
	$pdf->SetXY(70, $c_line);
	$ligne6_row1="Pour votre confort, durant lhospitalisation quelle soit dune ou plusieurs journées, veuillez prévoir :";
	$ligne6_row2="	-	Nécessaire de toilette : savon, serviettes, brosse à dents";
	$ligne6_row3="	-	Robe de chambre";
	$ligne6_row4="	-	Chemise de nuit ou pyjama, chaussons.      ";
	$ligne6=$ligne6_row1."\n".$ligne6_row2."\n".$ligne6_row3."\n".$ligne6_row4;
	$pdf->BasicTable2($ligne6);
	
	
	$c_line=$c_line+30;
	$pdf->SetXY(70, $c_line);
	$ligne7="Dans lattente de vous accueillir dans les meilleures conditions, je vous prie de croire, $titre $pat, à lassurance de mon dévouement.";
	$pdf->BasicTable1($ligne7);
	
	$c_line=$c_line+34;
	$pdf->SetXY(140, $c_line);
	$ligne8="Cadre infirmière du service \n Ou Secrétaire Hospitalier ";
	$pdf->BasicTable1($ligne8);
	return $pdf;

}

//====================================================================================
// Function Print Print_ Prescription_klean
//====================================================================================
function Print_prescription_klean($pdf,$med,$pat,$date_nais){

	$c_line=50;
	$date=date("d/m/Y");

	$pdf->SetLeftMargin(70);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();
	$pdf->setSourceFile('images/courriers/tete_f5.pdf'); 
	$tplIdx = $pdf->importPage(1); 
	$pdf->useTemplate($tplIdx, 2, 2, 200); 

	$pdf->SetXY(150, 50);
	$pdf->AjouterText(5,"PARIS, le ");
	$pdf->AjouterText(5," $date");
	$c_line=$c_line+10;
	
	$pdf->SetXY(70, $c_line);
	$pdf->AjouterText(5,$pat);
	$c_line=$c_line+5;
	



	$c_line=$c_line+45;
	$pdf->SetXY(70, $c_line);
	$pdf->SetFont('','B',12);
	$ligne2="KLEAN PREP : ";
	$pdf->AjouterText(5,"KLEAN PREP : ");
	$pdf->SetFont('','',12);
	$pdf->AjouterText(5,"préparation colique  4 sachets");
	
	
	$c_line=$c_line+140;
	$pdf->SetXY(140, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,"Pr.".$med);	
	return $pdf;	
}

//====================================================================================
// Function Print SANG
//====================================================================================
function Print_prescription_sang($pdf,$med,$pat,$date_nais){

	$c_line=50;
	$date=date("d/m/Y");

	$pdf->SetLeftMargin(70);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();
	$pdf->setSourceFile('images/courriers/tete_f5.pdf'); 
	$tplIdx = $pdf->importPage(1); 
	$pdf->useTemplate($tplIdx, 2, 2, 200); 

	$pdf->SetXY(150, 50);
	$pdf->AjouterText(5,"PARIS, le ");
	$pdf->AjouterText(5," $date");
	$c_line=$c_line+10;
	
	$pdf->SetXY(70, $c_line);
	$pdf->AjouterText(5,$pat);
	$c_line=$c_line+5;
	
	$c_line=$c_line+25;
	$pdf->SetXY(70, $c_line);
	$ligne3_row1="Prise de sang à faire avant une coloscopie sous anesthésie générale ";
	$ligne3_row2="A faire dans les 8 jours précédant lexamen ";
	$ligne3=$ligne3_row1."\n".$ligne3_row2;
	$pdf->SetFont('','B',11);	
	$pdf->BasicTable2($ligne3);


	$c_line=$c_line+25;
	$pdf->SetXY(70, $c_line);	
	$pdf->SetFont('','',12);	
	$pdf->AjouterText(15,"- NFS, Plaquettes");
	$c_line=$c_line+8;
	$pdf->SetXY(70, $c_line);	
	
	$pdf->AjouterText(15,"- TP,TCA");

	
	$c_line=$c_line+100;
	$pdf->SetXY(140, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,"Pr.".$med);	
	return $pdf;	
}

//====================================================================================
// Function Print Prescription autre
//====================================================================================
function Print_prescription_autre($pdf,$med,$pat,$date_nais, $med_liste){

	$c_line=50;
	$date=date("d/m/Y");

	$pdf->SetLeftMargin(70);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();
	$pdf->setSourceFile('images/courriers/tete_f5.pdf'); 
	$tplIdx = $pdf->importPage(1); 
	$pdf->useTemplate($tplIdx, 2, 2, 200); 

	$pdf->SetXY(150, 50);
	$pdf->AjouterText(5,"PARIS, le ");
	$pdf->AjouterText(5," $date");
	$c_line=$c_line+10;
	
	$pdf->SetXY(70, $c_line);
	$pdf->AjouterText(5,$pat);
	$c_line=$c_line+5;

	$c_line=$c_line+45;
	$pdf->SetXY(70, $c_line);
	$pdf->AjouterText(5,$med_liste);
	
	
	$c_line=$c_line+140;
	$pdf->SetXY(140, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,"Pr.".$med);	
	return $pdf;	
}


//====================================================================================
// Function Print notice  Preparation du klean-prep
//====================================================================================
function Print_notice_PKP($pdf){


	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_pkp.jpeg',10,8,200);
	return $pdf;
}

//====================================================================================
// Function Print NOTICE coloscopie
//====================================================================================
function Print_notice_coloscopie($pdf){


	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_col_front.jpeg',10,8,200);
	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_col_back.jpeg',10,8,200);
	return $pdf;
}


//====================================================================================
// Function Print NOTICE coloscopie
//====================================================================================
function Print_notice_ANSHDJ($pdf){


	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_anesth_hdj.jpeg',0,0,200);
	return $pdf;
}


//====================================================================================
// Function Print NOTICE coloscopie
//====================================================================================
function Print_notice_ANSHDS($pdf){


	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_anesth_hds_front.jpeg',0,0,200);
	$pdf->AddPage();
	$pdf->Image('images/courriers/notice_anesth_hds_back.jpeg',0,0,200);
	return $pdf;
}
//====================================================================================
// Function Print any NOTICE to pdf (all pages)
//====================================================================================
function Print_Notices($pdf,$file){
	$nbPage = $pdf->setSourceFile($file); 
	for ($i = 1; $i <= $nbPage; $i++) {
		$tplidx = $pdf->ImportPage($i);
		$size = $pdf->getTemplatesize($tplidx);
		$pdf->AddPage('P', array($size['w'], $size['h']));
		$pdf->useTemplate($tplidx);
	}				
	return $pdf;
}

//====================================================================================
// Function Print Convocation HEMATOLOGIE
//====================================================================================
function Print_rdv_hematologie($pdf,$nip,$pat,$adresse,$date_nais,$service,$opt_print,$optvals){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/hemato_letter_2011.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 2, 2, 200); 	
			
	$c_line=60;
	$date=date("d/m/Y");

	
	$pdf->SetFont('Times','B',12);	
	$c_line=50;
	$pdf->SetXY(100, $c_line);
	$pdf->AjouterText(5,$pat);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[0]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[1]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[2]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->AjouterText(5, $adresse[3]);
	$pdf->SetFont('','',12);	
	

	$c_line=$c_line+15;
	$pdf->SetXY(140, $c_line);
	$pdf->AjouterText(5,"PARIS, le ");
	$pdf->AjouterText(5," $date");
	$c_line=$c_line+10;
	
	$pdf->SetXY(85, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,"NIP : $nip");
	$pdf->SetFont('','');
	$c_line=$c_line+5;


	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"Ne(e) le ".$date_nais);
	$c_line=$c_line+15;

	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"Veuillez prendre note de vos RDV en ");
	$c_line=$c_line+5;	
	$pdf->SetXY(85, $c_line);
	$pdf->SetFont('','B',12);
	$pdf->AjouterText(5,$service);
	$pdf->SetFont('','');
	$c_line=$c_line+8;	
	
	$pdf->SetFont('','B',12);	
	$optvals=explode("|",$optvals);
	for($c=0;$c < count($opt_print);$c++){
		if ($opt_print[$c]){
			$c_line=$c_line+8;
			$pdf->SetXY(85, $c_line);
			$pdf->AjouterText(5,"- Le ".$opt_print[$c]);
		}
		
	}
	$pdf->SetFont('','',12);	
	
	$msg0="    Afin douvrir votre dossier administratif HDJ/USE, veuillez vous présenter, lors de votre 1er rendez-vous, au bureau des admissions muni de votre pièce didentité, de la carte vitale, de lattestation de mutuelle et du papillon dadmission ci-joint; cette admission est valide sur lannée civile.";
	$msg1="  Nous vous rappelons quune numération formule sanguine entre 2 traitements  est indispensable à votre prise en charge. Elle doit être effectuée et faxée 48h avant votre rendez-vous.";
	$msg2="    Si problème majeur concernant cette programmation, merci de contacter la secrétaire hospitalière.";
	
	$c_line=$c_line+12;
	$pdf->SetXY(85, $c_line);
	//$pdf->AjouterText(5,$msg0);	
	$pdf->MultiCell(110,5,$msg0,0);

	$c_line=$c_line+30;
	$pdf->SetXY(85, $c_line);
	$pdf->SetFont('','B',12);	
	$pdf->MultiCell(110,5,$msg1,0);	
	$pdf->SetFont('','',12);
	$c_line=$c_line+25	;
	$pdf->SetXY(85, $c_line);
	$pdf->MultiCell(110,5,$msg2,0);	
	
	return $pdf;

}

function Print_rdv_ablation_cat($pdf,$nip,$pat,$date_entree){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/ablation_kt2011.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 2, 2, 200); 	
		$pdf->SetFont('Times','B',12);			
		$pdf->SetXY(85, 60);
		$pdf->AjouterText(5,$pat);
	
		$pdf->SetXY(75, 110);
		$pdf->AjouterText(5,"Le ".$date_entree);
		$pdf->SetFont('','');
		return $pdf;
}

function Print_rdv_pose_kt($pdf,$nip,$pat,$date_entree){
		$pdf->AddPage(); 
		$pdf->setSourceFile('images/courriers/pose_KT2011.pdf'); 
		$tplIdx = $pdf->importPage(1); 
		$pdf->useTemplate($tplIdx, 2, 2, 200); 	
		$pdf->SetFont('Times','B',12);			
		$pdf->SetXY(85, 60);
		$pdf->AjouterText(5,$pat);
	
		$pdf->SetXY(75, 110);
		$pdf->AjouterText(5,"Le ".$date_entree);
		$pdf->SetFont('','');
		return $pdf;
}


function Print_rdv_pose_kt_old($pdf,$nip,$pat,$date_entree){
	$c_line=60;
	$date=date("d/m/Y");

	$pdf->SetLeftMargin(85);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();
	$pdf->Image('images/courriers/tete_hemato_top.jpg',3,8,200);
	$pdf->Image('images/courriers/tete_hemato_bot.jpg',3,140,200);	

	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,$pat);
	$pdf->SetFont('','');
	$c_line=$c_line+20;	
   $pdf->Ln();
   $pdf->Ln();
	$pdf->SetFont('','B',14);
	$msg="                                                                
	           VOUS AVEZ RENDEZ-VOUS          
	  POUR UNE POSE DE CATHETER
	                                         ";
	$pdf->MultiCell(120,5,$msg,1,'C');
	$pdf->SetFont('','' ,12);
	$c_line=$c_line+30;

	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"Le ".$date_entree);
	$pdf->SetFont('','');
	$c_line=$c_line+20;	
	

	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"Veuillez vous présenter en Hôpital de jour polyvalent situé à la policlinique Médico-chirurgicale (à droite avant les caisses).");
	$c_line=$c_line+25;	
	$pdf->SetFont('','B','14');
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"A JEUN  (Voir livret pour recommandations)");
	$c_line=$c_line+15;	



	$pdf->SetFont('','u','14');
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"Il faut vous munir : " );
	$c_line=$c_line+10;
	
	$pdf->SetFont('','','12');
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"	des résultats du bilan sanguin (fait 48 h avant la date de pose du cathéter) " );
	$c_line=$c_line+12;	
		
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"	De votre carte nationale didentité");
	$c_line=$c_line+8;	
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"	De votre attestation de sécurité sociale et/ou mutuelle" );
	$c_line=$c_line+20;	
		$pdf->SetFont('','B','14');
	$pdf->SetXY(85, $c_line);
	$pdf->AjouterText(5,"En cas de problème, vous pouvez appeler au 01 42 49 94 99" );
	$c_line=$c_line+8;	

	
	return $pdf;
}

?>
