<?php
/*
Auteur MOHANRAJU@SLS.APHP.FR
Class Parseur RAP
Date creation 12/06/2012
**************************************************************************************
*                                                                                    * 
*       PARSEUR RAP                                                                  *
*       pour tous les version RAP
*                                                                                    * 
**************************************************************************************


*/
Class RapSimpa
{
	var $Structure; 				// les structure défnit dans le fichier CSV sont recuparée par cette ARRAY
	var $Data;							// les données du formulaire ou les variables recuparé par GET/POST sont mis dans cette ARRAY
	var $StructureTitre; 		// le structure du ficheir CSV définit dans cette variable, ATTN le CSV est lis par Position
	var $CharactarSpl="#"; 	// caratar utilisable dans la range pour éviter le prob de Excel qui converti en date
	var $RapHeader; 				// Rap header du fichier rap


	//----------------------------------------
	// Class Constructeur
	//------------------------------------------
	function  RapSimpa(){
		// ne change pas le position ou le description du chaque collonne
 		$this->StructureTitre = array(
 										"Varibale",
								 		"Pos_DEB",
								 		"Pos_END",
								 		"Libellé",
								 		"Remarque",
								 		"Taille",
								 		"Obligatoire"
								);
		$this->RapHeader="*+*RAP*+*vv.vv00020120627140925_001.r00001\n";
 	}
	

	/*
	-------------------------------------------------------------------
	function LireStructure($fichierStructure,$Seperateur=";")
	Lire le fichier structure 
	ATTN: format de collone obligatoire
	-------------------------------------------------------------------
	*/
	function LireStructure($fichierStructure,$Seperateur=";") 
	{
		// vérify le presence du fichier structure
		if (strlen($fichierStructure)< 1)
		{
			echo "<br>Erreur LireStructure :: Fichier strcuture RAP introuvable : ".$fichierStructure;
			exit;

		}
		

		
		// lire le fihcier et mttre dans un array	   
		if($CsvFile = file($fichierStructure))
		{
			// get nombre de cols dans le fichier structure
	    $keydata = explode($Seperateur,$CsvFile[0]);
	    $keynumb = count($keydata);
		
			// check nombre de colonnes de la fichier structure vs heading declaration
	  	$Heading= $this->StructureTitre; 
	  	if(count($Heading) !=$keynumb )
	  	{
	  		echo " Erreur LireStructure :: Invalide Structure file ".$fichierStructure ."Attendu est ";
	  		echo "<pre>";
	  		echo "==========  Structure définition ================";
	  		print_r($this->StructureTitre);
	  		echo "==========  Structure File ================";
	  		print_r($keydata );
	  		echo"</pre>";	  		
	  		return false;
	  	}	   
	  	
			
	    $NbrLignes = count($CsvFile);
	    $Name_col=0; //collone corresponds le nom du variable !!!!!
			
	    //intiallement on devalare un zone vide
	    $ZONE="VIDE";
	    $CurStruc=array();
	    
	    // boucle sur le fichier 
	    for($n=1; $n< $NbrLignes; $n++)
	    {
	    	$CsvFile[$n] = chop($CsvFile[$n]);
	      $CsvFile[$n] = str_replace($encl,'',$CsvFile[$n]);
	      $CurrentLigne = explode($Seperateur,$CsvFile[$n]);
	    	// Chaque changement de ZONE on palce les resutat dans un nouveau array avec l'ancienne ZONE
	      if ($CurrentLigne[0]=="ZONE"){
	      	$Retval[$ZONE]=$CurStruc;	      	
	      	$ZONE= $CurrentLigne[3]; // nom du zone
	      	$CurStruc=array();
	      }
	      $i=0;
	      foreach($Heading as $key) 
	      {
	      	$CurStruc[$CurrentLigne[$Name_col]][$key] = $CurrentLigne[$i];
        	$i++;
        }   
	    }
  
		$Retval[$ZONE]=$CurStruc;
 
 
		}
		else
		{
			Print "<br>Erreur LireStructure :: Erreur reading Strcuture file ".$fichierStructure;
			exit;
			return false;
		}
 
		return $Retval; 		

	}	//fin structure



	/*
	-------------------------------------------------------------------
	function PreapareRap($RapData,$Version)
	Lire le fichier structure 
	puis preapre fichier RAP
	-------------------------------------------------------------------
	*/
	function  PrepareRap($RapData,$VersionRap)
	{
		// Check $RapData est un array 
		$NbrRap=count($RapData);		
		if(count($NbrRap) < 1)
		{
			echo "<br>Erreur PreapareRap :: Aucun donnée trouvé dans la objet RapData, pour construre la RAP!!!";
			return false;	
		}
		
		// Check le version
		if (strlen($VersionRap)==5)
		{
			$RapStructure=$this->LireStructure("../commun/include/StructureRAP".$VersionRap.".csv");
		}
		else
		{
			echo "Erreur PreapareRap :: Version RAP".$VersionRap." inconnu ou pas de structure définis !!!";
			exit;
		}			

		// Vérify les erreurs dans les RapData 
		// Les donnée obligatoires
		// les data types
		// longeur des donnée
		/*
		$Erreurs=$this->ValidateData($RapData,$RapStructure);
		if (strlen($Erreurs) > 1){
			echo 	"<br> Unable to prépare RAP<br>".$Erreurs;
			exit;	
		}
		*/
		//Prépare le header avac la version
    $RetVal=str_replace("vv.vv",$VersionRap,$this->RapHeader);

		
		//Découpe RAP Strructure file			
  	//---------------------------------------
		$RapStructureDebut	=	$RapStructure['DEBUT'];
		$RapStructureDas		=	$RapStructure['DIAG'];
		$RapStructureActes	=	$RapStructure['CCAM'];			
		$RapStructureFin		=	$RapStructure['FIN'];		
		
		$RetVal=$this->RapHeader;
		//---------------------------------------------
		// Boucle sur RAP data Initial, 
		// donée dans le zone Debut
		//---------------------------------------------
		$NbrDas=0;
		$NbrActes=0;
		//---------------------------------------------
		// Boucle sur RAP data Initial, 
		// donée dans le zone Debut
		//---------------------------------------------

		for($R=0;$R < $NbrRap;$R++)
		{
			echo "<br> Boucle : ".$R;			
			// recupare current ligne
			$CurRapData=$RapData[$R];

			// decoupe chaque partie de la ligne
 			$RapDebut	=	$CurRapData['DEBUT'];
			$RapDas		=	$CurRapData['DIAG'];
			$RapActes	=	$CurRapData['CCAM'];			
			$RapFin		=	$CurRapData['FIN'];					
			//---------------------------------------------
			// boucle sur RAP Debut							
			//---------------------------------------------			
			$RetVal="";
			foreach ($RapStructureDebut as $key=>$value)
			{
				$RetVal.= str_pad($RapDebut[$key],$RapStructureDebut[$key]['Taille']);

				// nombre diagnistic associées				
				if ($key=="nombre_das")
					$NbrDas= intval($RapDebut[$key]);

				// nombre Actes cccam
				if ($key=="nombre_actes")					
					$NbrActes=intval($RapDebut[$key]);	
			}

			//---------------------------------------------
			// Boucle sur DAS ZONE, 
			//---------------------------------------------

			for($CptDas=0;$CptDas < $NbrDas;$CptDas++)
			{
				//seconde boucle pour la strucure 
				foreach ($RapStructureDas as $key=>$value)
				{
					$RetVal.= str_pad($RapDas[$CptDas][$key],$RapStructureDas[$key]['Taille']);
				}				
			}
			//---------------------------------------------
			// Boucle sur ACTES ZONE, 
			//---------------------------------------------
			for($CptAct=0;$CptAct < $NbrActes;$CptAct++)
			{
				//seconde boucle pour nombre cols
				foreach ($RapStructureActes as $key=>$value)
				{
					$RetVal.= str_pad($RapActes[$CptAct][$key],$RapStructureActes[$key]['Taille']);					
				}				
			}
			//---------------------------------------------
			//  ZONE FIN, 
			//---------------------------------------------
			foreach ($RapStructureFin as $key=>$value)
			{
				$RetVal.= str_pad($RapFin[$key],$RapStructureFin[$key]['Taille']);					
			}				
			$FinalRetVal .="".$RetVal;
		}

 	 	return $this->RapHeader.$FinalRetVal;
	}



	/*
	-------------------------------------------------------------------
	function LireRap($FichierRap)
	lire le fichier rap et retourne array
	array cols
	 1: DEBUT : debut zone cols dans array
	 2. DAS 	:	zone cols dans un array repetetif
	 3: CCAM 	:	zone cols dans un array repetetif
	 4: FIN 	: fin zone cols dans larray
	
	le fichier structure va prends automatiquement 
	-------------------------------------------------------------------
	*/
	function LireRap($FichierRap)
	{
 	
		//-------------------------------------------------------
		// Lire la ficheir RAP
		//-------------------------------------------------------
		// vérify le fichier prensent
		if (strlen($FichierRap)< 1)
		{
			echo "<br>Fichier RAP introuvable : ".$FichierRap;
			return false;
		}
		
		//-----------------------------------------
		// lire le fichier et mettre dans un array	
		//------------------------------------------   
		if($RapFile = file($FichierRap))
		{
			// Check version
			//---------------------------------------
			$RapHead=$RapFile[0];
			$VersionRap=substr($RapFile[0],9,5);

			// initialise Structure RAP??
			//---------------------------------------
			if (strlen($VersionRap)==5)
			{
				$RapStructure=$this->LireStructure("../commun/include/StructureRAP".$VersionRap.".csv");
			}
			else
			{
				echo "Erreur LireRap :: Version RAP".$VersionRap." inconnu ou le fichier structure indéfinis poour cette version ".$VersionRap."!!!";
				exit;
			} 
			
			/*
			//Découpe RAP Structure file			
			//---------------------------------------

			*/
 			$RapStructureDebut	=	$RapStructure['DEBUT'];
			$RapStructureDas		=	$RapStructure['DIAG'];
			$RapStructureActes	=	$RapStructure['CCAM'];			
			$RapStructureFin		=	$RapStructure['FIN'];					
 	
			//---------------------------------------------
			// Variables

			$NbrRap=count($RapFile);
			$RetVal=array();
			$NbrDas=0;
			$NbrActes=0;
			//---------------------------------------------
			// Boucle sur RAP data Initial, 
			// on ignore le premier ligne( voir $R=1) car le header dan sle premier linge
			//---------------------------------------------

			for($R=1;$R < $NbrRap;$R++)
			{
				$FilePos=1;				
				$CurrentRap=$RapFile[$R];
				$Debut=Array();
				//---------------------------------------------
				// ZONE DEBUT
				//---------------------------------------------				
				foreach ($RapStructureDebut as $key=>$value)
				{
					$Debut[$key]=substr($CurrentRap,$FilePos-1,intval($RapStructureDebut[$key]['Taille']));
					$FilePos += intval($RapStructureDebut[$key]['Taille']);					
					if ($key=="nombre_das")
						$NbrDas= intval($Debut[$key]);
					if ($key=="nombre_actes")					
						$NbrActes=intval($Debut[$key]);	
				}
				$RetVal['DEBUT']=$Debut;
				//---------------------------------------------
				// ZONE DAS +boucle 
				//---------------------------------------------
				$DAS=array(); //intialise l'array Das 
				for($CptDas=0;$CptDas < $NbrDas;$CptDas++)
				{
					//seconde boucle pour nombre cols
					foreach ($RapStructureDas as $key=>$value)
					{

						$DAS[$CptDas][$key]=substr($CurrentRap,$FilePos-1,intval($RapStructureDas[$key]['Taille']));
						$FilePos +=intval($RapStructureDas[$key]['Taille']);
					}				
				}
				$RetVal['DIAG']=$DAS;				
				//---------------------------------------------
				// ZONE ACTES  +boucle 
				//---------------------------------------------
				$ACTES=array(); //intialise l'array Actes
				//premier boucle pour nombre Actes
				for($CptAct=0;$CptAct < $NbrActes;$CptAct++)
				{
					//seconde boucle pour nombre cols
					foreach ($RapStructureActes as $key=>$value)
					{
						$ACTES[$CptAct][$key]=substr($CurrentRap,$FilePos-1,intval($RapStructureActes[$key]['Taille']));
						$FilePos += intval($RapStructureActes[$key]['Taille']);
					}				
				}		
				$RetVal['CCAM']=$ACTES;
				//---------------------------------------------
				// ZONE  FIN
				//---------------------------------------------				
				$FIN=Array();
				foreach ($RapStructureFin as $key=>$value)
				{
					$FIN[$key]=substr($CurrentRap,$FilePos-1,intval($RapStructureFin[$key]['Taille']));
					$FilePos += intval($RapStructureFin[$key]['Taille']);					
 				}
				$RetVal['FIN']=$FIN; 		
 				$FinalRetrunData[$R-1]=$RetVal;	// array [0] est ignoré pour titre, donc on utilise l'array des début donc $R-1
 			}

 		}
		else
		{
			Print "<br>Erreur LireRap :: Erreur reading rap file ".$FichierRap;
			return false;
		}
			return $FinalRetrunData;	
	}	
 	
	
 	


	/*
	-------------------------------------------------------------------
	function ValideData($varName="")
	si un variable dans la prametre on vérify que celle ci 
	si non ensemble du structure
	-------------------------------------------------------------------
	*/
	function ValidateData($Data,$RapStructure)
	{
		$Erreurs="";
		$RapStructure=$RapStructure[0];
		foreach ($RapStructure as $key=>$value)
		{
			$Obligatoire=$RapStructure[$key]['Obligatoire'];
			if (strtoupper($Obligatoire)=="O")
			{
				if (strlen(trim($Data[$key])) < 1)
				{
					$Erreurs.="<br>Champs obligatoire manquant :".$RapStructure[$key]['Varibale'];
				}
				
			}
			$RetVal.= str_pad($Data[$key],$RapStructure[$key]['Taille']);
		}
		return $Erreurs;
	}

	
 

}// FIN CLASS
?>
