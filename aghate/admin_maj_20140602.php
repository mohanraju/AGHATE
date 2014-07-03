<?php
/*
 * ====================================================================================
 * Version 20140602
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP


$Revision="20140602";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//===============================================================================
	// maj Forms
	//===============================================================================
	//intranetmsi
	$sql_modif[] ="ALTER TABLE  `codage_msi` CHANGE  `nip`  `nip` VARCHAR( 10 ) NOT NULL";

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