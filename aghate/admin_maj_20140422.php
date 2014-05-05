<?php
/*
 * ====================================================================================
 * Version 20140422
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
 

$Revision="20140422";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//version 02/04/2014
	$sql_modif[] ="ALTER TABLE `agt_loc` ADD `plage_pos` VARCHAR( 10 ) NULL ";

	//version 08/04/2014
	$sql_modif[] ="ALTER TABLE `agt_prog` CHANGE `start_time_prog` `start_time` INT( 11 ) NOT NULL ";
	$sql_modif[] ="ALTER TABLE `agt_prog` CHANGE `end_time_prog` `end_time` INT( 11 ) NOT NULL ";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `end_time` ";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `type` VARCHAR( 2 ) NOT NULL DEFAULT 'A'";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `description` text";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `statut_entry` varchar(25) NOT NULL DEFAULT '-'";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `motif` varchar(100) DEFAULT NULL";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `statut_consult` varchar(25) DEFAULT NULL";
	$sql_modif[] ="ALTER TABLE `agt_prog` CHANGE `room_id` `room_id` INT( 4 ) NULL ";
	$sql_modif[] ="ALTER TABLE `agt_medecin` CHANGE `area_id` `service_id` INT( 11 ) NULL DEFAULT NULL ";
	$sql_modif[] ="ALTER TABLE `agt_loc_parametres` CHANGE `service_fermee` `service_fermee` VARCHAR( 1 ) NULL DEFAULT '0' ";	
	$sql_modif[] ="ALTER TABLE `agt_protocole` ADD `actif` VARCHAR( 1 ) NULL ";
	
	//version 28/04/2014
	$sql_modif[] ="ALTER TABLE  `loc_backup` ADD  `TYMAJ` VARCHAR( 1 ) NOT NULL DEFAULT  'A'";


	for($c=0; $c < count($sql_modif); $c++)
	{
		echo "<br>"	.$sql_modif[$c];
		$Aghate->insert($sql_modif[$c]);
	}

	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$Revision."' where NAME='versionRC'";
	$Aghate->update($sql);
	echo "  ... succès";
}else{
	echo "  ... déja faites.";
	}
?>