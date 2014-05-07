<?php
/*
 * ====================================================================================
 * Version 20140303
 * creation table temproire locbackup
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
 
$Revision="20140303";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	$sql_modif[]=	"CREATE TABLE IF NOT EXISTS loc_backup (
				ID int(11) NOT NULL auto_increment,
				NOIP 	varchar(10) default NULL,
				NMMAL 	varchar(35) default NULL,
				NMPMAL	varchar(35) default NULL,
				DANAIS	date  NOT NULL default '0000-00-00',
				NOTLDO  varchar(30) default NULL,
				NOLIT 	varchar(10) default NULL, 
				NOCHAM 	varchar(10) default NULL, 
				NOPOST 	varchar(10) default NULL, 
				NOSERV 	varchar(10) default NULL, 
				DDLOPT	date  NOT NULL default '0000-00-00',
				CDSEXM 	varchar(2) default NULL, 
				HHLOPT 	varchar(5) default NULL, 
				NDA 	varchar(10) default NULL, 
				DTENT	date  NOT NULL default '0000-00-00',
				HHENT 	varchar(5) default NULL, 
				UH 		varchar(3) default NULL, 
				TYSEJ 	varchar(3) default NULL, 
				TYMVT 	varchar(2) default NULL, 
				NOIDMV 	varchar(15) default NULL, 
				DATE_MAJ timestamp NOT NULL default CURRENT_TIMESTAMP,
				 PRIMARY KEY  (ID)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
			
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
	echo "  ... déja fait.";
	}

?>