<?php
/*
#########################################################################
#                  ajax_edit_entry_handler.php                         #

#                                                                       #
#            Dernière modification : 20/03/2008                         #
#                                                                       #
#########################################################################
modifie par mohanraju le 13/01/2014

 
 */

include "../../config/config.php";
include "../../config/config.inc.php";
include "../include/ClassMysql.php";

$mysql = new MySQL();

$id = $_GET['id'];
if ($id > 0){
	$sql = "UPDATE agt_protocole SET actif = 'n'  WHERE id_protocole = ".$id."";
	    
	$mysql->update($sql);
	
	echo "|OK|$id";
}else{
	echo "|ERR|Protocole non supprim&eacute;";
}
?>
