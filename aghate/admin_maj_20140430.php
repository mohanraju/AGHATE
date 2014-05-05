<?php
/*
 * ====================================================================================
 * Version 20140430
 * Mettre a jour les annulation  pour les 30 dernier jours
 * reconstrure les ensemble de sejours de LOC_backup
 * ====================================================================================
*/
// SCRIPT A INCLURE DANS ADMIN_MAJ.PHP
$Revision="20140430";
echo "<br> Mise a jour Révision =>".$Revision;
if ($RevisionBase < $Revision)
{
	//recupares les annulation de GILDA.MVT avec le typaj D 
 

	$sql="SELECT mvt.NODA as NDA,MVT.NOIDMV
			FROM MVT
			WHERE DADEMJ between sysdate-30 and sysdate+1
			AND tymaj='D' order by mvt.NODA";

	$res=$Gilda->OraSelect($sql);
	//print_r($res); 

	//mettre a jour dans loc_bacup les annulations
	$nbr_resume=count($res);
	for($i=0; $i < $nbr_resume ;$i++)
	{	 
		$sql_update="UPDATE loc_backup set
					tymaj='D' WHERE NOIDMV = '".$AnnlGilda[$i]['NOIDMV']."'"; 
		$NbrRecords=$Aghate->update($sql_update);		
	}


	// get les sejours de loc_backup
	$res=$Aghate->select("select nda from loc_backup where TYMVT !='SH' group by nda"); 
	$nbr_resume=count($res);
	//print_r($res); 	
	$nbr_annul=0;
	$last_nda="";
 

	// mise a jour TEMPS NDA dans forms/coadge a faire ici
	$url= "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];		
	$url= str_replace(strrchr($url,"/"),"/",$url);	
	for($i=0; $i < $nbr_resume ;$i++)
	{
		// wait deux sec tous les  100
		if($i % 100) sleep(2);
		
		if($last_nda != $res[$i]['nda'])
		{
			$result = file_get_contents($url."commun/ajax/ajax_aghate_remttre_ajour_gilda_par_nda.php?nda=".$res[$i]['nda']."&table_loc=agt_loc");
			echo "<br>".$result;
		}	
		$last_nda = $res[$i]['nda'];
	}


	//mettre a jour la dernière version
	$sql="update agt_config set VALUE='".$Revision."' where NAME='versionRC'";
	$Aghate->update($sql);
	echo "  ... succès";
}else{
	echo "  ... déja faites.";
	}	

?>