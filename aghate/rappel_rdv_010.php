<?php
	ini_set("memory_limit","128M");
	require("fpdf16/fpdf.php");
	include ("./commun/include/CustomSql.inc.php");
	include "./commun/include/language.inc.php";	
	require("adresse_patient.php");		
	$db = New CustomSQL($DBName);
	//---------------------------------------------------
	// CLASS PDF creator
	//---------------------------------------------------
	class PDF extends FPDF
	{
	
		
		function TextSimple($height,$text)
		{
		  $this->write($height,$text);   
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

	
	//----------------------------------------
	// input section
	//---------------------------------------
	if (strlen($date_conv) > 1 && strlen($date_conv) < 10){
		echo "Invalide date format ".$date_conv;
		$date_conv="";
		}
	if (strlen($date_conv) == 10){
		list($day,$month,$year)=explode("/",$date_conv);
		$d1=mktime(8,0,0,$month,$day,$year);
		list($day,$month,$year)=explode("/",date('d/m/Y'));
		$d2=mktime(8,0,0,$month,$day,$year);
		if ($d1 <= $d2){
			echo "Impossible d'envoyer les convocations pour les dates antérieure à la date jour ".$date_conv;
			$date_conv="";
			}
		
		}
		
	if (strlen($date_conv)< 10){

		$date_conv= date('d/m/Y', strtotime('+10 day')); 
		?>
		<style type="text/css">
		.bold {
			font-size: 24px;
			color: #06C;
		}
		</style>		
		<body>
		<table width="73%" border="0" align="cneter">
		  <tr>
		    <td bgcolor="#FFFFFF"  class="bold" align="center">Rapel des convocations</td>
		  </tr>
		  <tr>
		    <td>
		    	<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
		      <label for="textfield">Rappel à faire pour les convocations du</label>
		      <input type="text" name="date_conv" id="date_conv" value="<?php print $date_conv;?>"/> (JJ/MM/YYYY)
		     	<input type="hidden" name="area" id="area" value="<?php print $area;?>"/>  
		   </td>
		  </tr>
		  <tr>
		    <td align="center"> 
		      <input type="submit" name="button" id="button" value="Generer" />
		    </form></td>
		  </tr>
		</table>
		</body>
	<?php
	 exit;
	}

	
	list($day,$month,$year)=explode("/",$date_conv);
	$jour =$date_conv;
	$StDate =mktime(8,0,0,$month,$day,$year)+14400;
	$FnDate =mktime(17,59,0,$month,$day,$year);

	$sql = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, beneficiaire, statut_entry, agt_loc.description, agt_loc.option_reservation, agt_loc.moderate, beneficiaire_ext,pmsi,hds,room_name,overload_desc
	   FROM agt_loc, agt_room
	   WHERE agt_loc.room_id = agt_room.id
	   AND service_id = '$area'
	   AND start_time <= $FnDate AND start_time >= $StDate ORDER BY room_name,start_time";
    
		$res = $db->select($sql);

	//====================================================================================
	// on commance le stuff here to prepare the PDF
	//====================================================================================
	if (count($res)> 0){
		$pdf=new PDF();		
		for ($i = 0; $i <count($res);$i++) {
			$name_complet=$res[$i]['name'];
			$cur_val=explode("(",$name_complet);
			
			$nom=$cur_val[0];
			$nip=substr($cur_val[1],0,10);
			$date_nais=substr($cur_val[2],0,10);
			$adresse=GetAdresse($nip);
			$adresse=explode('|',$adresse);
			//echo $name_complet,"<br />",$nip,"<br />",$date_nais,"<br />",$date_entree,"<br />";
			
			
			$date_entree=date('d/m/Y',$res[$i]['start_time']);
			$pdf= Print_rdv($pdf,$date_entree,$area,$nip,$nom,$adresse,$date_nais);			
			}
		
		
		
		
	}else{
		echo "Aucun convocations trouvé le :" .$jour;
		exit;
	}
	
	



	$pdf->SetDisplayMode('fullwidth','single');
	$pdf->Output();
	//$pdf->Output('d:/convocation.pdf','F');
//====================================================================================
// Function Print Convocation
//====================================================================================
function Print_rdv($pdf,$date_entree,$area,$nip,$pat,$adresse,$date_nais){

	$c_line=50;
	$date=date("d/m/Y");

	$pdf->SetLeftMargin(35);
	$pdf->SetLeftMargin(500);
	$pdf->SetTopMargin($c_line);
	$pdf->SetFont('Times','',12);
	$pdf->AddPage();

	if ($area==10){
		$pdf->Image('images/courriers/tete_010_hdj.gif',3,2,200);
		$pos_img=15;
	}else{
		$pdf->Image('images/courriers/tete_010_diabete.gif',3,2,200);
		$pos_img=15;
	}
	$pdf->SetFont('','B',14);	
	$c_line=30;
	$pdf->SetXY(100, $c_line);
	$pdf->TextSimple(5,"RAPPEL DE RDV");


	$c_line=60;
	$pdf->SetFont('','B',12);	
	$c_line=50;
	$pdf->SetXY(100, $c_line);
	$pdf->TextSimple(5,$pat);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->TextSimple(5, $adresse[0]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->TextSimple(5, $adresse[1]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->TextSimple(5, $adresse[2]);

	$c_line=$c_line+5;
	$pdf->SetXY(100, $c_line);			
	$pdf->TextSimple(5, $adresse[3]);
	$pdf->SetFont('','',12);	
	

	$c_line=$c_line+15;
	$pdf->SetXY(140, $c_line);
	$pdf->TextSimple(5,"PARIS, le ");
	$pdf->TextSimple(5," $date");
	$c_line=$c_line+6;

	
	$pdf->SetXY(133,$c_line);
	$pdf->SetFont('Times','BI');
	$pdf->TextSimple(10,$date_entree);



	return $pdf;

}

	
?>
