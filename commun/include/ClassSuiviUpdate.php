<?php
/*
###################################################################################################
#
#											OBJET SuiviUpdate
#											
#
#							By Mohanraju @ APHP
# modif le 15/10/2012
###################################################################################################
*/
class SuiviUpdate extends MySQL
{
	var $uh_liste;
	var $Site;
	
 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Suivi()
	{
	
	}
	/*
 	==============================================================================
	Init objet des deux sites
 	==============================================================================
 	*/	
	function Init()
	{
		//Init  les objets Saint Louis
		// ==== >  Ouvrir les objets 
		//SIMPA
		include("../commun/include/ClassSimpa.php");
		$Simpa=new Simpa($ConnexionStringSIMPA);
		$Simpa->SetPeroide($date_deb,$date_fin);		
		
		//SAG + IPOP
		
		include("../commun/include/ClassIpop.php");
		$Ipop= new Ipop($site);
		$Ipop->SetTableIpop($TableIpop);
		$Ipop->SetTablebloc_structure($TableIpop_bloc_structure);
		$ListeUhExecutant=$Ipop->GetUhExecutant();
		
		//SAG 
		include("../commun/include/ClassSag.php");
		$Sag  = new Sag($ConnexionStringSAG);	
		
		
		//NESTOR
		require("../commun/include/ClassNestor.php");
		$Nestor	=	new Nestor($site,"erreur");
		$Nestor->SetTableNestor($TableNestor);
		
		
		// CRO ET CRH
		require($AppCR_Objet);
		$AppCr =	new AppCR($AppCR_ConnString,$AppCR_User,$AppCR_MotDePasse);
		
		// Suivi
		require("../commun/include/ClassSuivi.php");
		$Suivi=new Suivi($Site);

	
	
	
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
											crh='".$res_suivi[$i]['crh']."',
											cro='".$res_suivi[$i]['cro']."',
											ipop='".$res_suivi[$i]['ipop']."',
											nestor='".$res_suivi[$i]['nestor']."',
											codage='".$res_suivi[$i]['codage']."',
											date_maj='".date('Y-m-d')."' 
										WHERE id='".$res_suivi[$i]['cle']."'";	
				parent::update($sql_update);	
			}
		}
	
	}
}// fin class
?>
