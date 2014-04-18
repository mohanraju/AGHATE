<?php  
/*ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/
	include "./commun/include/CustomSql.inc.php";
	$db = new CustomSQL($DBName);

	$urm='470';
	//=============================================================
	// recupares les liste des patients d'aujourd'hui 
	///=============================================================
    $report_start = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
    $report_end = mktime(0, 0, 0, date('m'), date('d')+2, date('Y'));
	 $sql="select noip from agt_loc WHERE agt_loc.start_time between $report_start and $report_end";
	//echo $sql;
	$nip_list=$db->select($sql);
	//echo "<br />".print_r($nip_list)."<br />";
	require("./config/connexion_simpa.php");	

	$r=0;				
	for($m=0;$m < count($nip_list) ;$m++){
		$nip=$nip_list[$m]['noip'];
		
		// vérifie si les donnée sont déja present dans la base POUR éviteR duplication
		//lance oracle pour récuparer les donnée Simpa
		$sql="select NIP, NDA,kyres,DE, DF, DP, DR, GHM, LBDP, dusej,LBGHM,UH ,URM FROM
				(
					select distinct(rsm.noda) as nda,rsm.noip as NIP, rsm.kyres as kyres, 
							TO_char(d8deb,'DD/MM/YYYY') as DE,TO_char(d8fin,'DD/MM/YYYY') as DF, 
							rsm.CDDNC9 as DP, rsm.CDRELIE as DR, '' as DAS, 
							rss.CDGHM as GHM, LBCIM as LBDP,dusej as dusej, ghm.lbghm as LBGHM, RSM.UE as UH ,rsm.cdurm as URM,
							rank() over (ORDER BY d8fin DESC) iRow 
					from RUM,RSS,RSM, CIM,GHM
						where RUM.NORES = RSM.KYRES
						AND RUM.KYRSS = RSS.KYRSS
						AND CIM.KYCIM = rsm.CDDNC9
						AND GHM.KYGHM= rss.CDGHM
						and rsm.noip='".$nip."'
						and (rsm.cdurm='470'
							or RSM.UE in ('566','567','230','220','552','501') )						
				) 
				where iRow < 2 
				order by kyres desc";

		//echo "<br />".$sql;				
				
		$result = ociparse($ConnSimpa, $sql);
		ociexecute($result);

		while(ocifetch($result))
		{
			$actes='';
			$das='';
			$nores=ociresult($result, 3);			
			$respmsi[$r]['nip']		= ociresult($result, 1);
			$respmsi[$r]['nda']		= ociresult($result, 2);
			$respmsi[$r]['de']		= ociresult($result, 4);
			$respmsi[$r]['df']		= ociresult($result, 5);
			$respmsi[$r]['dp']		= ociresult($result, 6) . " " .ociresult($result, 9);
			$dr= ociresult($result, 7);
			$respmsi[$r]['ghm']		= ociresult($result, 8). " ". ociresult($result, 11);
			$respmsi[$r]['dusej']	= ociresult($result, 10);
			$respmsi[$r]['urm']	= ociresult($result, 13);
			$respmsi[$r]['uh']	= ociresult($result, 12);					

			//---------------------------
			/* Lib DR*/
			//---------------------------
			$sqlDr="select LBCIM from cim   where CIM.KYCIM ='".$dr."' AND cim.tycim='10'";
			$resultDr = ociparse($ConnSimpa, $sqlDr);
			ociexecute($resultDr);
			while(ocifetch($resultDr)){
				$lbdr = ociresult($resultDr, 1);
			}
			$respmsi[$r]['dr'] .= $dr." " .$lbdr;
			
			
			//---------------------------
			/*pour actes*/
			//---------------------------
			$sqlActes="select distinct rac.kycdac,cda.lbcda from rac,cda 
			where rac.kycdac=cda.kycda and verclas='2' and rac.kyres='$nores'";
			$resultActes = ociparse($ConnSimpa, $sqlActes);
			ociexecute($resultActes);
			while(ocifetch($resultActes))
			{
				$respmsi[$r]['actes']	.= ociresult($resultActes, 1)." : ".ociresult($resultActes, 2)."@";			
			}
			//---------------------------
			/*pour DAS*/
			//---------------------------
			$sqlDas="select dgn.kycddg, cim.lbcim from dgn,cim
			where dgn.kycddg=cim.kycim and kytydg='C' and dgn.kyres='$nores' and cim.tycim='10'";
			$resultDas = ociparse($ConnSimpa, $sqlDas);
			ociexecute($resultDas);
			while(ocifetch($resultDas))
			{
				$respmsi[$r]['das']	.= ociresult($resultDas, 1)." : ".ociresult($resultDas, 2)."@";							
			}
			$r++;
		}
		

	}
	
	//on rebascules les donnée dans le table grr_pmsi
	for ($c=0;$c<count($respmsi);$c++){
		$chk_sql="select id from grr_pmsi where 
								nda='".$respmsi[$c]['nda']."' and 
								de='".Date_Normal2MySQL($respmsi[$c]['de'])."' and 
								uh='".$respmsi[$c]['uh']."'";
		$chk_res=$db->select($chk_sql);

			$insert_sql="insert into grr_pmsi (nip,nda ,de ,df,urm,uh,dp,dr,das,actes,ghm) 
								values ('".$respmsi[$c]['nip']."',
										'".$respmsi[$c]['nda']."',  
										'".Date_Normal2MySQL($respmsi[$c]['de'])."',   
										'".Date_Normal2MySQL($respmsi[$c]['df'])."',  
										'".$respmsi[$c]['urm']."',  
										'".$respmsi[$c]['uh']."',  
										'".mysql_real_escape_string($respmsi[$c]['dp'])."',  
										'".mysql_real_escape_string($respmsi[$c]['dr'])."',  
										'".mysql_real_escape_string($respmsi[$c]['das'])."',  
										'".mysql_real_escape_string($respmsi[$c]['actes'])."',  
										'".mysql_real_escape_string($respmsi[$c]['ghm'])."') ";
								
		if (count($chk_res) < 1){			
			$db->insert($insert_sql);
			echo "\nInsertion OK : ".$respmsi[$c]['nip']." ". $respmsi[$c]['nda'];			
		}else{
			echo "\nDonné existe déja : ".$respmsi[$c]['nip']." ". $respmsi[$c]['nda'];
		
		}
		
	}
?>
