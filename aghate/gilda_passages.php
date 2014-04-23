<?php
	//======================================================
	// Program Par MOHANRAJU le 12/03/2009
	// cette program appelé par L'AJAX
	// affichee les passages d'une patient
	//======================================================
	$sql_ora="";
	require("./config/connexion_gilda.php");		
	if(isset($Annuler)) {
		   	$noip="";
		   	$t_nom="";
		   	$t_prenom="";
	}	

	$noip=$_GET['param']; 
	//$noip="2608030084";
	$sql_passages = "
		select nip as nip, nda as nda, type as type,to_char(de,'DD/MM/YYYY') as de, ue as ue , libuh as libuh, me as me, to_char(df,'DD/MM/YYYY') as df , mf as mf FROM
		(
		select nip as nip, nda as nda, type as type,de as de, ue as ue , libuh as libuh, me as me, df as df , mf as mf FROM
		(
		select distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, dos.DAENTR as DE,mvt.NOUF as UE,UFM.LBUF as libUH,MDENTR as ME,dos.DASOR as DF,dos.MDSOR as MF   from dos,mvt,ufm
		where dos.noip  in ('$noip')
		and mvt.noda = dos.noda
		and tydos <> 'S'
		and mvt.tymaj <> 'D'
		and ufm.nouf = mvt.nouf
		and DFVALI >= to_date('01/01/3000','DD/MM/YYYY')
		union all
		select distinct(dos.noda) as nda,dos.noip as nip, dos.tydos as type, hjo.DAEXEC as DE,mvt.NOUF as UE,UFM.LBUF as libUH,dos.MDENTR as ME,hjo.DAEXEC as DF,'1' as MF from dos,hjo,mvt,ufm
		where dos.noip  in ('$noip')
		and mvt.noda = hjo.noda
		and tydos = 'S'
		and mvt.tymaj <> 'D'
		and ufm.nouf = mvt.nouf
		and dos.noda=hjo.noda
		and DFVALI >= to_date('01/01/3000','DD/MM/YYYY')
		)
		order by df desc
		)

	";

	$result = ociparse($ConnGilda, $sql_passages);
	ociexecute($result);
		$retval="<table width='680' border='1' cellspacing='0' cellpadding='0' bgcolor='#FFFFFF'>";
		$compteur=0;
			while(ocifetch($result))	{		  	
				$nda = ociresult($result, 2);
				$a_type = ociresult($result, 3);
				$a_daentr = ociresult($result, 4);
				$a_ue = ociresult($result, 5);
				$a_lbuh = ociresult($result, 6);
				$a_mdentr = ociresult($result, 7);
				$a_dasor = ociresult($result, 8);
				$a_mdsor = ociresult($result, 9);

				$retval.="<tr>";
				$retval.="<td width='60'>".$nda  ."</td>";				
				$retval.="<td width='150'>".$a_ue  ." - (".$a_lbuh.")</td>";
				$retval.="<td width='60'>".$a_daentr ."</td>";
				$retval.="<td width='60'>".$a_dasor  ."</td>";  	
				$retval.=" </tr>";
				$compteur++;

			}			
			if ($compteur==0){ 
					$retval.="<tr><td colspan='6'> Aucun passages trouvee !!!</td></tr>";			
				}
				$retval.="</table>";			
	echo $retval;
	?>
