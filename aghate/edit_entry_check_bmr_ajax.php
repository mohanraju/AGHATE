<?php
//========================================================================
// cette ajax récupare le BMR du patient 
//	et retourne le sexe compatible dans le chambre
// si l'égalité reourne "I"
// Par mohanraju /DBIM/MSI le 12/02/2009
//========================================================================
	include "./commun/include/base_bmr.inc.php";
	$db = new DBSQL($DBName);
	$noip=$_GET['param']; 
	//$noip="1186041866";
	$res="";	
	$bmr="N";
	$query="SELECT prelevement.nip,bmr,nda, date_entree,date_sortie, date_prelevement
				FROM prelevement, sejour, bacterie
				WHERE prelevement.num_prelevement = bacterie.num_prelevement
				AND sejour.nip = prelevement.nip
				and sejour.nip='$noip'
				order by sejour.nip,date_prelevement desc";
				
				//and bacterie.bmr not in('aucun','SUPRIMEE')
  	$result = $db->select($query);
  	$count = count($result);
  	if ($count > 0) {
		for ($i=0;$i<count($result);$i++){
			if ($result[$i]['bmr'] == "SUPRIMEE")
				$res.="le ".date_Mysql2Normal($result[$i]['date_prelevement'])." - aucun \n";
			else
				$res.="le ".date_Mysql2Normal($result[$i]['date_prelevement'])." - ".$result[$i]['bmr']."\n";
			if ((strcmp($result[$i]['bmr'], "aucun") != 0) && (strcmp($result[$i]['bmr'], "SUPRIMEE") != 0)	)$bmr="O";
			if ($i==4)break;
		}
  	}
  	echo $bmr."|".$res;
 ?>
