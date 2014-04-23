<A HREF="javascript:self.print()"><IMG SRC="./commun/images/print.gif" BORDER="0"  title="Print this page"></A>
<?Php 
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set("display_errors", 1);
			
	//parameters Ó modifie
	$nbr_col=3; //nombre des cols per ligne
	$nbr_row=9; // nombre des row per page
	$page=1; // page number
	$nbr_row=$nbr_row *$nbr_col;
	
	
	# Define the start and end of the day.
  include "./commun/include/code_barre.php";	
	include "./commun/include/CustomSql.inc.php";
  include "./commun/include/language.inc.php";	
	$db = New CustomSQL($DBName);
	$sql = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, beneficiaire, statut_entry, agt_loc.description, agt_loc.option_reservation, agt_loc.moderate, beneficiaire_ext,pmsi,hds,room_name,overload_desc,
					agt_type_area.type_name
			   FROM agt_loc, agt_room,agt_type_area
			   WHERE agt_loc.room_id = agt_room.id
			   AND agt_type_area.type_letter=agt_loc.type
			   AND service_id = '$area'
			   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 
			   ORDER BY start_time,name";
				$res = $db->select($sql);

	$col=1;
	$row=1;
	$last_room="";
	$printable= print_header($nbr_col,$page, $jour,$this_service_name);
?>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>Convocations</title>
</head>
</head>
<STYLE TYPE='text/css'>
P.pagebreakhere {page-break-before: always}
.stl_font {font-size: 10px}
</STYLE>
<body>

<?php	
	//generate les code barres before printing
	for ($i = 0; $i <count($res);$i++) {
		$cur_val=explode("(",$res[$i]['name']);
		$nip=substr($cur_val[1],0,10);		
		GenerateCodeBarre($nip);		
	}

	$tete_xls="NIP\tPatient\tDate de naissance\tSexe\tPéroide\t";
	for ($i = 0; $i <count($res);$i++) {
		
		// preapre additionnal filds uniquement le premier fois
    if ($i==0){
			$sql="Select id,fieldname from agt_overload where fieldname not in ('Protocole') and id_area='".$area."'";
			$addn_datas=$db->select($sql);
			for ($c=0;$c < count($addn_datas) ;$c++){
    		$tete_xls.=	$addn_datas[$c]['fieldname']."\t";				
			}
			$tete_xls.="\n";
    } 
    
		$duree= ($res[$i]['end_time']* 1) - ($res[$i]['start_time']* 1);     
		$duree=$duree/3600;           
		$st_time= utf8_strftime("%H:%M",$res[$i]['start_time']);
		$en_time= utf8_strftime("%H:%M",$res[$i]['end_time']);		

		//date("H:m",$res[$i]['start_time']-4);
		//$en_time=date("H:m",$res[$i]['end_time']+30);
		$room_name=$res[$i]['room_name'];
		$cur_val=explode("(",$res[$i]['name']);		
		$nip=substr($cur_val[1],0,10);

		//prepare l'affichage
		$desc="<img src=./commun/images/codes/".$nip.".gif height='25px' width='130px'><br /> ";
		$data_xls.=$nip."\t". $cur_val[0]."\t".substr($cur_val[2],0,10)."\t".substr($cur_val[3],0,1)."\t".$st_time." à ".$en_time."\t".$db->get_overload_cahmp_xls($area,$res[$i]['overload_desc'])."\n";
		
		
		$desc.=$st_time." à ".$en_time." - NIP:".$nip."<br />";		
		$desc.="<b>". $cur_val[0]."</b><br />";
		$desc.="Ne(e): ".substr($cur_val[2],0,10)."(".substr($cur_val[3],0,1).")<br />";
		$desc.="Description : ". $res[$i]['description']."<br />";
		$others=$db->get_overload_cahmp($area,$res[$i]['overload_desc']);
		$desc.=$others ." " ;			
		
 	 	//prepare l'affichage du UH/type reservation
		$desc.= "Salle : ".$res[$i]['room_name']."<br />UH/TYPE/SEXE =".$res[$i]['type_name'];
 	 
		//nbr des colennes dans une row
		if ( (($i%$nbr_col)==0) and ($i >0) ){
 
			$last_room=$room_name;
			$printable.="</tr><tr>";
			$row++;
			$col=0;
		}


		$printable.="<td class='stl_font'>".$desc."</td>";

		$col++;

	}
	$printable.="</tr></table><br />";	
	
	Print $printable;
	/*
	===============================================================================
	PRint into Excel et téléchargement
	===============================================================================
	*/
	$dossier="./";
	$FicXls="Convocations_lrb.xls";
	if (!$FileXls = fopen($dossier.$FicXls, 'w')) {
		echo "Impossible d'ouvrir le fichier csv ($FicXls)";
	}else{
		if (fwrite($FileXls, $tete_xls.	$data_xls) === FALSE) {
			echo  "Impossible d'écrire dans le fichier Xls ($FicXls)";
		}
		fclose($FileXls);
	}
	echo "<br /><br /><div align='center'>
				<a href='./download.php?file=".$FicXls."&dir=".$dossier."' target='_blank'>Télécharger ce tableau au format Excel</a>
				</div>";

 	/*
	===============================================================================
	function print_header($nbr_col,$page, $jour,$service_name)
	===============================================================================
	*/
 	function print_header($nbr_col,$page, $jour,$service_name){
		$tete="<h3 align='center'>".$service_name."  <br />  Les convocations  du ".$jour."</h3>
		<table width='90%' border='1' align='center'>		<tr>";
		return $tete; 	
 	} 	

?>
</body> 	
</html>
