<?php
/*
 * ====================================================================================
 * Version 20140428
 * Mettre a jour les annulation et les sorties dans agt_loc pour les 60 dernier jours
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
$Revision="20140428";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	// mettre a jour les annulations
	$sql ="SELECT * from MVT
			WHERE TYMAJ='D' 
 			AND DADEMJ > (sysdate -60)";
 				
	$AnnlGilda= $Gilda->OraSelect($sql);
	$nb = count($AnnlGilda);
	for($i=0;$i< $nb;$i++)
	{
		$sql_update="UPDATE loc_backup set
					tymaj='D' WHERE NOIDMV = '".$AnnlGilda[$i]['NOIDMV']."'"; 
		$NbrRecords=$Aghate->update($sql_update);
	
	}

 
	// 	mettre a jour les SH 
  	$_sql="SELECT  DOS.NOIP,
				DOS.NODA  	as NODA,
				TO_CHAR(mvt.damvad,'YYYY-MM-DD') as DTSOR,
				MVT.HHMVAD as HHSOR,
				mvt.nouf 		as UHSOR,
				DOS.TYDOS 	as TYSEJ,
				MVT.tymvad 	as TYMVT,
				MVT.NOIDMV as NOIDMV
			FROM   dos, mvt 
			WHERE mvt.noda = dos.noda
				AND dos.tydos = 'A'
				AND mvt.cddemv = 'O'
				AND mvt.tymaj != 'D'
 				AND mvt.tymvad IN ('SH')
 				and DADEMJ > (sysdate -60)"; 

	$DonneeGilda= $Gilda->OraSelect($_sql);
	$Aghate->BackupSortie($DonneeGilda);
	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$Revision."' where NAME='versionRC'";
	$Aghate->update($sql);
	echo "  ... succès";
}else{
	echo "  ... déja faites.";
	}	

?>