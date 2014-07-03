<?php
/*
 * ====================================================================================
 * Version 20140603
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
 

$Revision="20140603";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//Modif pour remplir le champs patient dans formulaire de reservation
	$sql_modif[] ="CREATE TABLE IF NOT EXISTS `agt_listes` (
				  `id` int(4) NOT NULL auto_increment,
				  `tri` int(2) NOT NULL,
				  `grp` varchar(30) NOT NULL,
				  `lib_value` varchar(15) NOT NULL,
				  `libelle` text NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ";
// ipop				
 	$sql_modif[]="CREATE TABLE IF NOT EXISTS `ipop_backup` (
				  `ipop_id` int(11) NOT NULL,
				  `nip` varchar(50) NOT NULL,
				  `bloc` varchar(50) NOT NULL default '',
				  `um_de_travail` varchar(50) NOT NULL default '',
				  `date_prevue` varchar(50) NOT NULL default '',
				  `date_intervention` varchar(50) NOT NULL default '',
				  `heure_entree_salle` varchar(50) NOT NULL default '',
				  `heure_sortie_salle` varchar(50) NOT NULL default '',
				  `duree_intervention` varchar(50) NOT NULL default '',
				  `salle` varchar(50) NOT NULL default '',
				  `type_inter` varchar(255) NOT NULL default '',
				  `programmation` varchar(5) NOT NULL,
				  `chirurgien_responsable` varchar(50) NOT NULL default '',
				  `chirurgien` varchar(50) NOT NULL default '',
				  `anesthesiste` varchar(50) NOT NULL default '',
				  `nom_interne` varchar(50) NOT NULL default '',
				  `commentaire` varchar(255) NOT NULL,
				  `ambulatoire` varchar(5) NOT NULL,
				  `commentaire2` varchar(255) NOT NULL,
				  `etat` text,
				   service	varchar(255)  NULL
				  `date_maj` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
				  `tymaj` varchar(1) NOT NULL default 'a',
				  PRIMARY KEY  (`ipop_id`),
				  KEY `nip` (`nip`),
				  KEY `date_maj` (`date_maj`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$sql_modif[]="ALTER TABLE  `agt_service` ADD  `service_ipop` VARCHAR( 100 ) NOT NULL DEFAULT  ' '";
	$sql_modif[]="ALTER TABLE  `agt_service` ADD  `nom_formulaire` VARCHAR( 255 ) NOT NULL DEFAULT  ' '";	
	$sql_modif[]="CREATE TABLE IF NOT EXISTS `agt_service_periodes` (
				  `service_id` int(11) NOT NULL default '0',
				  `num_periode` smallint(6) NOT NULL default '0',
				  `nom_periode` varchar(100) NOT NULL default ''
				) ENGINE=InnoDB DEFAULT CHARSET=utf8";

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
