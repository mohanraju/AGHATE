<?php
//========================================================================
// partie de la projet gestion salles
// accessible par PMSI
// ce script maj le date de confirmation par TIM
// Par mohanraju /DBIM/MSI le 28/04/2010
//========================================================================
	include ("./commun/include/CustomSql.inc.php");
	$db = new DBSQL($DBName);
	$id=$_GET['param']; 
	$date_maj=date("d/m/Y");
	$query="update agt_loc set tim='".$date_maj."' where id='".$id."'" ;
  	$result = $db->update($query);
  	echo $result ;
 ?>
