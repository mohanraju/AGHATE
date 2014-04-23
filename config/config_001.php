<?php
#########################################################################
#                         Config_???.php                                #
#                                                                       #
#                 configuration spéfique du chaque site     	        #
#                 Dernière modification : 10/07/2006                    #
#									MOHANRAJU Sp APHP/SaintLouis        #
#########################################################################
/*
 * Copyright 20011 MOHANRAJU Sp APHP/SaintLouis
 *
*/
$Hopital="Necker";
$serveur=true; // facilite la basculemnt de script vers server/local

//nom debut de fichier déposé par siège pour identiifer le site 
$FileIdentificationSite="EXTRACTION_AGATHE_SLS"; // exemple SLS Extraction_Agathe_SLS_2010_12_15.csv

/*###############################################################################
#                                                                               #
# SEVICE AGHATE                                                                 #
# Nom de service Agahte dans le quelle les plannings IPOP à inserée             #
# le libelle sans Espace en respectent le case                                  #
#                                                                               #
################################################################################*/
$ServiceAgahte="UNITE CHIRURGIE AMBULATOIRE";	
		
	
/*###############################################################################
#                                                                               #
# SERVICE FILTRE IPOP                                                           #
# L'extraction du siège envoi tous les blocs dnas l'extraction mais le CHIR ne  #
# pas besoin tous dnas l'aghate, dans ce mettre ici les services dont ils ont   #
# les service sont  dans un array   obligatoire                                 #
# si pas de filtre laisser le variable avec ""                                  #
################################################################################*/
$ServiceFiltre[]="Chir. Gene.";
$ServiceFiltre[]="Chir. Plastique";
$ServiceFiltre[]="CRN";
$ServiceFiltre[]="Maxillo-faciale";
$ServiceFiltre[]="Plastie Esth.";
$ServiceFiltre[]="Urologie";



/*
########################################
# Check sexs dans les chambre double   #
########################################
*/

$CheckSexeCompatibility=true;
 
 

/*
########################################
#             GILDA                    #
########################################
*/

$serveur=true;


$ConnexionStringGILDA="mysql:dbname=gilda;host=localhost"; 

if ($serveur)
{
	$ConnexionStringGILDA="(DESCRIPTION = 
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = caddy.bsr.aphp.fr)(PORT = 8401))
    )
    (CONNECT_DATA = 
      (SID = GIP1NCK)
    )
    )";

/*
########################################
#             SIMPA                    #
########################################
*/

	$ConnexionStringSIMPA="(DESCRIPTION =
		   (ADDRESS_LIST =
			(ADDRESS =
			   (COMMUNITY = sls.ap-hop-paris.fr)
			   (PROTOCOL = TCP)
			   (HOST = o-simpa-b1)
			   (PORT = 10505)
			)
		   )
		   (CONNECT_DATA =
			(SID = SIP1SLS)
		   )
		)";
	}
	


?>
