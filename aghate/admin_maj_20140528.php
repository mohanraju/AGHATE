<?php
/*
 * ====================================================================================
 * Version 20140528
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
 

$Revision="20140528";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//Modif pour remplir le champs patient dans formulaire de reservation
	$sql_modif[] ="ALTER TABLE `agt_utilisateurs`  ADD `droit_demande` VARCHAR(10) NOT NULL DEFAULT '0'";
	$sql_modif[] ="ALTER TABLE `agt_prog` CHANGE `statut_consult` `statut_demande` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci 
					NULL DEFAULT NULL" ;
	$sql_modif[] ="ALTER TABLE `agt_loc` ADD `patient` VARCHAR( 50 ) NOT NULL DEFAULT '' AFTER `noip` ; ";
	$sql_modif[] ="ALTER TABLE `agt_prog` ADD `patient` VARCHAR( 50 ) NOT NULL DEFAULT '' AFTER `noip` ;";	
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
