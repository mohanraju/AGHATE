<?php
/*
 * ====================================================================================
 * Version 20140420
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP


$Revision="20140420";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//===============================================================================
	// maj Forms
	//===============================================================================
	//intranetmsi
	$forms_sql[] =" ALTER TABLE `forms` CHANGE `idref` `idref` VARCHAR( 11 ) NOT NULL ";
	$forms_sql[] =" ALTER TABLE `forms` CHANGE `nda` `idref` VARCHAR( 9 ) NOT NULL ";
	$forms_sql[] =" ALTER TABLE `forms` CHANGE `type` `type` VARCHAR( 25 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";

	include "../config/config.php";
	$FormsDb =  new MySQL();
	echo "<hr> Forms ::Mettre a jour les modifications du structure<hr>";
	for($c=0; $c < count($forms_sql); $c++)
	{
		echo "<br>"	.$forms_sql[$c];
		$FormsDb->insert($forms_sql[$c]);
	}

	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$CurrentRevision."' where NAME='versionRC'";
	$Aghate->update($sql);

	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$Revision."' where NAME='versionRC'";
	$Aghate->update($sql);
	echo "  ... succès";
}else{
	echo "  ... déja faites.";
	}
?>