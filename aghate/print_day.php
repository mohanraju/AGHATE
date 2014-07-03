<A HREF="javascript:self.print()"><IMG SRC="./commun/images/print.gif" BORDER="0"  title="Print this page"></A>
<?Php 

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
	$sql = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, beneficiaire, statut_entry, agt_loc.description, agt_loc.option_reservation, agt_loc.moderate, beneficiaire_ext,pmsi,hds,room_name,overload_desc
	   FROM agt_loc, agt_room
	   WHERE agt_loc.room_id = agt_room.id
	   AND service_id = '$area'
	   AND start_time < ".($pm7+$resolution)." AND end_time > $am7 ORDER BY room_name,start_time";
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

	for ($i = 0; $i <count($res);$i++) {
		if ($res[$i]["hds"]=="HDS") $hds="Oui"; else $hds="Non";
		$duree= ($res[$i]['end_time']* 1) - ($res[$i]['start_time']* 1);     
		$duree=$duree/3600;           
		$st_time= utf8_strftime("%H:%M",$res[$i]['start_time']);
		$en_time= utf8_strftime("%H:%M",$res[$i]['end_time']);		
		//date("H:m",$res[$i]['start_time']-4);
		//$en_time=date("H:m",$res[$i]['end_time']+30);
		$room_name=$res[$i]['room_name'];
		$cur_val=explode("(",$res[$i]['name']);		
		$nip=substr($cur_val[1],0,10);
		$desc="<img src=./commun/images/codes/".$nip.".gif height='25px' width='130px'><br /> ";
		$desc.=$st_time." à ".$en_time." - NIP:".$nip."<br />";		
		$desc.=$cur_val[0]."<br />";
		$desc.="Ne(e): ".substr($cur_val[2],0,10)."(".substr($cur_val[3],0,1).")<br />";
		//$desc.="HDS : ".$hds ."<br />";
		$desc.="Description : ". $res[$i]['description']."<br />";		
		$others=$db->get_overload_cahmp($area,$res[$i]['overload_desc']);
		$desc.=$others ." / " ;			
		if($urm=="560"){
				$sql_type="select type_name from agt_type_area where type_letter='".$res[$i]['type']."' limit 1";
				$type_res=$db->select($sql_type);
				if (count($type_res) >0)
					$desc.="UH:".$type_res[0]['type_name'];			
			}

		//nbr des colennes dans une row
		if ($room_name <> $last_room){
			$last_room=$room_name;
			$printable.="</tr><tr><td>".$room_name."</td>";
			$row++;
			$col=0;
		}


		$printable.="<td class='stl_font'>".$desc."</td>";

		$col++;

	}
	$printable.="</tr></table><br />";	
	
	Print $printable;
 	
 function print_header($nbr_col,$page, $jour,$service_name){
 	
	$tete="<h3 align='center'>Les convocations  du ".$jour." - ".$service_name."</h3>
	
	<table width='90%' border='1' align='center'>		<tr>";
	
	return $tete; 	
 	
 	} 	

?>
</body> 	
</html>
