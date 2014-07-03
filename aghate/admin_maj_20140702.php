<?php
/*
 * ====================================================================================
 * Version 20140701
 * Revision : ipop pour gerer les id et le chirurgien responsable
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
 

$Revision="20140702";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	$sql_modif[]="ALTER TABLE `ipop_backup` ADD `CHIRURGIEN_RESPONSABLE` VARCHAR( 255 ) NOT NULL AFTER `PROGRAMMATION`";
	$sql_modif[]="ALTER TABLE `agt_prog` ADD `ipop_id` int( 9 ) NOT NULL default 0";
	$sql_modif[]="ALTER TABLE `agt_prog` CHANGE `protocole` `protocole` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL" ;
	$sql_modif[]="ALTER TABLE `ipop_backup` ADD `service` VARCHAR( 255 ) NULL DEFAULT ' ' AFTER `commentaire2` ";
	$sql_modif[]="ALTER TABLE `agt_exam_compl` ADD `anesthesiste` VARCHAR( 100 ) NULL";
	$sql_modif[]="ALTER TABLE  `ipop_backup` CHANGE  `ambulatoire`  `ambulatoire` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";


	for($c=0; $c < count($sql_modif); $c++)
	{
		echo "<br>"	.$sql_modif[$c];
		$Aghate->update($sql_modif[$c]);
	}

	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$Revision."' where NAME='versionRC'";
	$Aghate->update($sql);
	echo "  ... succès";
}else{
	echo "  ... déja faites.";
	}
?>
