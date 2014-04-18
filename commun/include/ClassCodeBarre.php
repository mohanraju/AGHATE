<?php
/*
##########################################################################################
	Projet CODAGE
	Class Code a barre
	Auteur Thierry CELESTE SLS APHP
	Maj le 28/05/2013
##########################################################################################
Parametres $DataCodage
*/

	//Si on appel cet page depuis le module code_liste n'include pas les fichiers car déja déclaré.

		define('FPDF_FONTPATH','../../commun/pdf/font/');
		include('../../commun/pdf/fpdf.php');

// declaration des codes binaires
// tableau Char -> CodeAbarre :Code39  ===================================
$TabCode = array(
   '0' =>  '101000111011101',
   '1' =>  '111010001010111',
   '2' =>  '101110001010111',
   '3' =>  '111011100010101',
   '4' =>  '101000111010111',
   '5' =>  '111010001110101',
   '6' =>  '101110001110101',
   '7' =>  '101000101110111',
   '8' =>  '111010001011101',
   '9' =>  '101110001011101',
   
   'A' =>  '111010100010111',
   'B' =>  '101110100010111',
   'C' =>  '111011101000101',
   'D' =>  '101011100010111',
   'E' =>  '111010111000101',
   'F' =>  '101110111000101',
   'G' =>  '101010001110111',
   'H' =>  '111010100011101',
   'I' =>  '101110100011101',
   'J' =>  '101011100011101',
   'K' =>  '111010101000111',
   'L' =>  '101110101000111',
   'M' =>  '111011101010001',
   'N' =>  '101011101000111',
   'O' =>  '111010111010001',
   'P' =>  '101110111010001',
   'Q' =>  '101010111000111',
   'R' =>  '111010101110001',
   'S' =>  '101110101110001',
   'T' =>  '101011101110001',
   'U' =>  '111000101010111',
   'V' =>  '100011101010111',
   'W' =>  '111000111010101',
   'X' =>  '100010111010111',
   'Y' =>  '111000101110101',
   'Z' =>  '100011101110101',
   
   '-' =>  '100010101110111',
   '.' =>  '111000101011101',
   ' ' =>  '100011101011101',
   '$' =>  '100010001000101',
   '/' =>  '100010001010001',
   '+' =>  '100010100010001',
   '%' =>  '101000100010001',
   '*' =>  '100010111011101'
);

/**************************************************************************
*
** PrintPresc.php : permet l'edition de la prescription
*
**************************************************************************/



define("MAXLIGNE",240);

$TAILLELIG=204;

class CODEBARRE extends FPDF
{
    var $Ligne;
    var $Colonne;
    var $Style;
    var $Taille;

	function InitVar()
	{
	
	    $this->Ligne=0;
	    $this->Colonne=0;
	
	}
	
	function Header()
	{
		$this->Rect(2,35,204,5);
 
		$this->Line(10,35,10,40);
		$this->Line(140,35,140,40);
		$this->Line(160,35,160,40);
		
		$this->SetFont('Arial','',10); 
		$this->Text(2,39,"Type",'',10);	
		$this->Text(60,39,"Diagnostics",'',10);	
		$this->Text(145,39,"Codes",'',10);	
		//$this->Text(162,39,"Niveau",'',10);	
		$this->Text(170,39,"Code à barres",'',10);	
	}
	
	function GeneCodeBarre($x,$y,$lib,$barcode,$pas,$hauteur,$title)
	{
		global $TabCode;
	
		$str=strtoupper($barcode);
		
		$this->SetLineWidth($pas);
		$this->SetDrawColor(0,0,0);
	
		$x+=3;
		$y+=4;
		if ( $title==true) {
			$this->SetFont('Arial','B',12);        
			$this->Text($x,$y+$hauteur+5,$lib.$barcode,'',3);	
			$this->SetFont('Arial','',8);        
		}
		for($j=0;$j<strlen($TabCode['*']);$j++) {
				if ( $TabCode['*'][$j] == '1' ) 
					$this->Line($x,$y,$x,$y+$hauteur);
				$x+=$pas;
		}
		$x+=$pas;
		
		for($i=0;$i<strlen($str);$i++) {
			$c=$str[$i];
	
			for($j=0;$j<strlen($TabCode[$c]);$j++) {
				if ( $TabCode[$c][$j] == '1' ) 
					$this->Line($x,$y,$x,$y+$hauteur);
				$x+=$pas;
			}
			$x+=$pas;
		}
		$x+=$pas;
		for($j=0;$j<strlen($TabCode['*']);$j++) {
				if ( $TabCode['*'][$j] == '1' ) 
					$this->Line($x,$y,$x,$y+$hauteur);
				$x+=$pas;
		}
	}
	
	function putTrace($Trace)
	{
		/*
		** DATE et HEURE A AJOUTER
		*/
		$fic="../log/CODAGEMSI.log";
		$fd=fopen($fic,"w");
		fputs($fd,$Trace);
		fclose($fd);
	}

	function PageHeader($Data)
	{
		/*
		** header
		*/
		/*
		** Cadre
		*/
		$this->Rect(2,4,204,26);
		$this->Line(102,4,102,30);
		
		$this->Rect(2,40,204,240);
 
		$this->Line(10,40,10,280);
		$this->Line(140,40,140,280);
		$this->Line(160,40,160,280);
		
		
		
		$this->GeneCodeBarre(4,3,"NIP : ",$Data['NIP'],0.2,10,true);
		$this->SetFont('Arial','',11); 
		$this->Text(49,20,"Né(e) le : ".$Data['DDN'],'',11);	
		$this->Text(49,25,"Sexe : ".$Data['SEXE'],'',11);	
		$this->Text(146,10,"Date d'entrée : ".$Data['DTEENT'],'',11);	
		$this->Text(146,15,"Date de sortie : ".$Data['DATSOR'],'',11);	
		$this->SetFont('Arial','B',8); 
		$this->Text(146,20,"UH : ",'',11);	
		$this->SetFont('Arial','',8); 
		$this->SetXY(152, 16.8);
		$this->MultiCell(55,5,$Data['UH']." - ".$Data['LIBUH'],0,'L');
		
		
		
		//$this->Text(146,25,"LIB : ".$Data['LIBUH'],'',11);	
		//$this->SetFont('Arial','',11); 
		$this->SetFont('Arial','B',11); 
		$this->Text(49,10,$Data['NOM'],'',11);	
		$this->Text(49,15,$Data['PRENOM'],'',11);	
		$this->SetFont('Arial','B',12); 
		$this->GeneCodeBarre(104,3,"NDA : ",$Data['NDA'],0.2,10,true);		
		
		
		
	}	
	function Footer()
	{
		$date=date("d/m/Y");
		$heure=date("H:i");
		$this->SetFont('Arial','',10); 
		$this->Text(170,294,"Le : ".$date." à ".$heure,'',10);	
		$this->Image("../../commun/images/logoGH.jpg",2,281,30,10);
	}
}
?>
