<?php
#########################################################################
#                         Config_047.php                                #
#                                                                       #
#                 configuration spéfique du chaque site     	          #
#                 Dernière modification : 10/07/2006                    #
#									MOHANRAJU Sp APHP/SaintLouis                          #
#########################################################################
/*
 * Copyright 20011 MOHANRAJU Sp APHP/SaintLouis
 *
*/
$Hopital="Lariboisière";
//nom debut de fichier déposé par siège pour identiifer le site 
$FileIdentificationSite="EXTRACTION_AGATHE_".date("Y"); // exemple LRB Extraction_Agathe_2010_12_15.csv

/*###############################################################################
#                                                                               #
# SEVICE AGHATE                                                                 #
# Nom de service Agahte dans le quelle les plannings IPOP à inserée             #
# le libelle sans Espace en respectent le case                                  #
#                                                                               #
################################################################################*/
$ServiceAgahte="CHIRURGIE AMBULATOIRE";
		
	
/*###############################################################################
#                                                                               #
# SERVICE FILTRE IPOP                                                           #
# L'extraction du siège envoi tous les blocs dnas l'extraction mais le CHIR ne  #
# pas besoin tous dnas l'aghate, dans ce mettre ici les services dont ils ont   #
# besoin dans un array                                                          #
# si pas de filtre laisser le variable avec ""                                  #
################################################################################*/
$ServiceFiltre=array('BLOCS CENTRAUX','ENDOSCOPIE'); // dans une array obligaroire ...

/*
########################################
# Check sexs dans les chambre double   #
########################################
*/

$CheckSexeCompatibility=false;


/*
########################################
#             GILDA                    #
########################################
*/
$ConnexionStringGILDA="(DESCRIPTION =
									    (ADDRESS_LIST =
									        (ADDRESS =
									          (COMMUNITY = lrb.ap-hop-paris.fr)
									          (PROTOCOL = TCP)
									          (HOST = o-gilda-b1.lrb.aphp.fr)
									          (PORT = 10401)
									        )
									    )
									    (CONNECT_DATA =
									      (SID = GIP1LRB)
									    )
									  )";

/*
########################################
#             SIMAP                    #
########################################
*/
$ConnexionStringSIMPA ="(DESCRIPTION =
										    (ADDRESS_LIST =
										        (ADDRESS =
										          (COMMUNITY = lrb.ap-hop-paris.fr)
										          (PROTOCOL = TCP)
										          (HOST = o-simpa-b1)
										          (PORT = 10405)
										        )
										    )
										    (CONNECT_DATA =
										      (SID = SIP1LRB)
										    )
										  )";

 
?>
	
