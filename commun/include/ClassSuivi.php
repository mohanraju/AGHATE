<?php
/*
###################################################################################################
#
#											OBJET Suivi
#											
#
#							By Mohanraju @ APHP
###################################################################################################
*/
class Suivi extends MySQL
{
	var $uh_liste;
	var $Site;
	
 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Suivi($Site)
	{
		$this->Site=$Site;
 		//initialise connexion Mysql
		parent::MySQL(); 
		
	
	}
 	/*
 	==============================================================================
 	PrepareFiltres($uh_liste,$select_grp,$Site)
 	preparation desf filtres par rapport UH ou POLe/URM
 	
 	$uh_liste = listes des uh separer par vircule ex 157,569
 	$select_grp = choix de selection soit URM or POLE, ex 060@DERMATO ou 100@ nomdu pole
 	==============================================================================
 	*/	
	function PrepareFiltres($uh_liste,$select_grp,$Site)
	{
		$_tmpsite=substr($Site,1,2);		
		$sql_uh=" AND structure_gh.uh in('indefinis') "; // default sql
				
		// get the UH du selected pole ou URM
		if (strlen($uh_liste) > 2)
		{
			$uh	="'".str_replace(",","','",$uh_liste)."' ";		
			$sql_uh=" AND structure_gh.uh in(".$uh.")  AND nda like('".$_tmpsite."%') ";
		}
		// get the URM du selected pole ou URM
 		elseif (strlen($select_grp) > 2)
		{
 	
			//recupare les uh  par selection pole/service
			list($pole,$lib_pole)=explode("@",$select_grp);		
			if ($pole=="POLE")
				$sql_uh=" AND structure_gh.pole='".$lib_pole."' ";
			else{
				$sql_uh=" AND concat(hopital,'-', service_lib)='".$lib_pole."' " ;
			}
		}
	
		//Filtre patr site
		if($FiltreParSite){
			$filtre_site=" AND nda like('".$_tmpnda."%') " 	;
		}
		
		return $sql_uh.$filtre_site;
		
	}


	/*
 	==============================================================================
	PrepareHC($uh_liste,$select_grp,$Site)
 	preparation de tableau HC
 	
 	==============================================================================
 	*/	
	function PrepareHC($uh_liste,$select_grp,$Site,$date_deb,$date_fin)
	{
		$filtres=$this->PrepareFiltres($uh_liste,$select_grp,$Site);
		// SQL HC
		$sqlhc="SELECT suivi.id as cle , suivi.*,structure_gh.* from suivi,structure_gh 
						where ds between '".$date_deb."' 
						and '".$date_fin."'  
						and suivi.uh=structure_gh.uh 
						and suivi.site=structure_gh.hopital 	
						and tymaj <>'D'"					
						.$filtres
						." AND type_sej ='HC'" 
						." GROUP BY  suivi.id  ORDER BY suivi.nom,suivi.prenom,suivi.uh,suivi.de"
						;
 
		$data=parent::select($sqlhc);
		return $data;						
	 	
	}
	/*
 	==============================================================================
	PrepareHC_codage($uh_liste,$select_grp,$Site)
 	preparation de tableau HC
 	ajouté le 17/12/2013 pour récuparer les codage manquantes d'année 
 	date de debut est forcé aà 01 janvier de l'année
 	et uniquement les séjours en codage manquantes.
 	==============================================================================
 	*/	
	function PrepareHC_codage($uh_liste,$select_grp,$Site,$date_deb,$date_fin)
	{

		// date_fin = date_deb -1
		$date_fin = date('Y-m-d', strtotime($date_deb .' -1 day'));
		// date de debut est forcé aà 01 janvier de l'année
		$date_deb=substr($date_deb,0,4)."-01-01"; 		
		
		$filtres=$this->PrepareFiltres($uh_liste,$select_grp,$Site);
		// SQL HC
		$sqlhc="SELECT suivi.id as cle , suivi.*,structure_gh.* from suivi,structure_gh 
						where ds between '".$date_deb."' 
						and '".$date_fin."'  
						and suivi.uh=structure_gh.uh 
						and suivi.site=structure_gh.hopital 		
						and tymaj <>'D'															
						and suivi.codage<>'1'"												
						.$filtres
						." AND type_sej ='HC'" 
						." GROUP BY  suivi.id  ORDER BY suivi.nom,suivi.prenom,suivi.uh,suivi.de"
						;
						
		$data=parent::select($sqlhc);
		return $data;						
	 	
	}
	/*
 	==============================================================================
	PrepareHDJ($uh_liste,$select_grp,$Site)
 	preparation de tableau HDJ
 	
 	==============================================================================
 	*/	
	function PrepareHDJ($uh_liste,$select_grp,$Site,$date_deb,$date_fin)
	{
		$filtres=$this->PrepareFiltres($uh_liste,$select_grp,$Site);
		// SQL hors HC  	
		$sqlhdj="SELECT   suivi.id as cle , suivi.*,structure_gh.* from suivi,structure_gh 
						where ds between '".$date_deb."' 
						and '".$date_fin."' 
						and suivi.uh=structure_gh.uh  
						and suivi.site=structure_gh.hopital  		
						and tymaj <>'D'	"																				
						.$filtres
						." AND type_sej <>'HC'" 
						." GROUP BY  suivi.id  ORDER BY suivi.nom,suivi.prenom,suivi.uh,suivi.de"
						;					 
		$data=parent::select($sqlhdj);
		return $data;						
	}

	/*
 	==============================================================================
	PrepareHDJ_codage($uh_liste,$select_grp,$Site)
 	preparation de tableau HDJ
 	ajouté le 17/12/2013 pourrécuparer les codage manquantes d'année 
 	date de debut est forcé aà 01 janvier de l'année
 	et uniquement les séjours en codage manquantes. 	
 	==============================================================================
 	*/	
	function PrepareHDJ_codage($uh_liste,$select_grp,$Site,$date_deb,$date_fin)
	{


		// date_fin = date_deb -1
		$date_fin = date('Y-m-d', strtotime($date_deb .' -1 day'));
		// date de debut est forcé aà 01 janvier de l'année
		$date_deb=substr($date_deb,0,4)."-01-01"; 		
		
		$filtres=$this->PrepareFiltres($uh_liste,$select_grp,$Site);
		// SQL hors HC  	
		$sqlhdj="SELECT   suivi.id as cle , suivi.*,structure_gh.* from suivi,structure_gh 
						where ds between '".$date_deb."' 
						and '".$date_fin."' 
						and suivi.uh=structure_gh.uh  
						and suivi.site=structure_gh.hopital 	
						and tymaj <>'D'											
						and suivi.codage<>'1'"																	
						.$filtres
						." AND type_sej <>'HC'" 
						." GROUP BY  suivi.id  ORDER BY suivi.nom,suivi.prenom,suivi.uh,suivi.de"
						;					 
		$data=parent::select($sqlhdj);
		return $data;						
	}

	/*
 	==============================================================================
	MajTableau($res_suivi)

 	
 	==============================================================================
 	*/	
	function 	MajTableau($res_suivi)
	{
		$nbs_suivi=count($res_suivi);
	 	// ON MAJ les donnée dans la base  
 	 	
		for ($i=0; $i < $nbs_suivi;$i++)
		{
			// si ou moins un modif dans la linge on force le MAJ
			if($res_suivi[$i]['modif'])
			{
				$sql_update="UPDATE suivi set 
											crh				='".$res_suivi[$i]['crh']."',
											cro				='".$res_suivi[$i]['cro']."',
											ipop			='".$res_suivi[$i]['ipop']."',
											sag   		='".$res_suivi[$i]['sag']."',
											nestor		='".$res_suivi[$i]['nestor']."',
											codage		='".$res_suivi[$i]['codage']."',
											date_maj	='".date('Y-m-d')."' 
										WHERE id='".$res_suivi[$i]['cle']."'";	
				parent::update($sql_update);	
			}
		}
	
	}
	/*
 	==============================================================================
	function IsSejourCoded(NDA,date_sortie,$Uh,Type_sej);
	date_sortie: format JJ/MM/YYYY
	Type_sej : HC / HDJ pas de control pour l'instant
 		vérife DP present et activé pour ce nda 
 	==============================================================================
 	*/
	function IsSejourCoded($Nda,$DateSortie,$Uh,$TypeSej='HC')
	{

		//pour les sejours en HC pas de contro date UH+NDA uniquement
		if ($TypeSej=='HC')
			$sql_date=" ";
		else
			$sql_date="and date_format(datsor,'%d/%m/%Y')='$DateSortie'";			
			
		// SQL check DP
		$SqlCodage="SELECT etat from codage_msi
								where type in('DP','DR','DAS')
								and valid='A'
								and ( diag <>'' or libdiag <>'')
								and nda='$Nda'
								and uhdem='$Uh'
								$sql_date 
								";

		$data=parent::select($SqlCodage);
		// si un dp trouvé on envoi EC:(En cours) 
		$retval=($data[0]['etat']=='DP')?'EC':'';
		return $data[0]['etat'];		
		
	}

	/*
 	==============================================================================
	function IsActesCoded(NDA );
 	A définir
 	==============================================================================
 	*/
	function GetActesCoded($Nda )
	{
		// SQL check DP
		$SqlCodage="SELECT concat(diag,' ',libdiag) as actes  from codage_msi
								where type ='ACTES'
								and valid='A'
								and nda='$Nda'
								";
		$res=parent::select($SqlCodage);
		for($i=0;$i < count($res) ;$i++){
			$retval.=$res[$i]['actes']."<br>";
		}

		//echo $retval;
		return $retval."&nbsp;";		

		
	}
	
	/*
 	==============================================================================
	function GetServiceUh($Service);
 	$Service : Hopital - Libelle Service
 	les UH sont retourné soud forma d'un tableau
 	==============================================================================
 	*/
	function GetServiceUh($Service)
	{
		$uh_liste[]="XXX"; //init list 
		
		//decoupr le service et lib _service
		list($service,$lib_service)=explode("@",$Service);		
		$sql="SELECT uh from structure_gh where concat(hopital,'-', service_lib)='".$lib_service."' " ;
 
		$res=parent::select($sql);
		for($i=0;$i < count($res);$i++){
				$uh_liste[] =$res[$i]['uh'];
		}	 

		return $uh_liste;		
		
	}	
	
	/*
 	==============================================================================
	function GetAllServices();
 	lilst retourn tous les services 
 	fromat HopCode - Service libelle ex (076 - AJA HEmato)
 	==============================================================================
 	*/
	function GetAllServices()
	{
		$sql="SELECT concat(hopital,'-', service_lib) as urm,concat(service_lib, '-(',hopital,')') as service_lib from structure_gh where service_lib is not null and service_lib<>''  
					group by hopital,service_lib order by service_lib"; 
		return parent::select($sql);
		
	}	
		
	
}// fin class
?>
