<?php
/* $return = '<?xml version="1.0" standalone="yes"?><result>';
 $return .= '<name>'.mohan|mohan.'</name>';
 $return .= '<name>'.mohanraju.'</name>';
 $return .= '<name>'.mohankumar.'</name>';
 $return .= '<name>'.mohanaraman.'</name>';
 $return .= '</result>';
 header('Content-Type: text/xml');
 */
// $return ="mohan|mohanraju|mohankumar|mohanraman";
 //echo $return;
 
	$input = ( $_GET['abr'] );
	if (strlen($input) < 2) exit;
	
	mysql_connect('localhost', 'root', '');
	mysql_select_db('hemato');
	$sql = "SELECT `nom`, `prenom`, `noip`  FROM `pat` WHERE `nom` LIKE '".$input."%' limit 10";
	$req = mysql_query($sql);
	$res="";
	$rw=0;
	while($autoCompletion = mysql_fetch_assoc($req)){
		$noip=$autoCompletion['nom'];
		$rw++;
		$res.=$autoCompletion['nom']." ".$autoCompletion['prenom']."|";
	}
	if (strlen($res) >1){
		echo $res;
	}

 
 
 ?>
