<?php
#########################################################################
#                         Config_???.php                                #
#                                                                       #
#                 configuration spéfique du chaque site     	          #
#                 Dernière modification : 10/07/2006                    #
#									MOHANRAJU Sp APHP/SaintLouis                          #
#########################################################################
/*
 * Copyright 20011 MOHANRAJU Sp APHP/SaintLouis
 *
*/

/*
#############################################################################
					Common CONFIGURATIONS
#############################################################################
*/

// connextion string Simpa (il faut bien respecter les espace / retour chariot ..)

//====================GILDA
$ConnexionStringGILDA="(DESCRIPTION =
       		(ADDRESS_LIST =
        			(ADDRESS =
           			(COMMUNITY = sls.ap-hop-paris.fr)
           			(PROTOCOL = TCP)
           			(HOST = o-gilda-b1)
           			(PORT = 10501)
        			)
       		)
       		(CONNECT_DATA =
        			(SID = GIP1SLS)
       		)
    		) "; 
    		
//====================SAG					  	

$ConnexionStringSAG="(DESCRIPTION =
       		(ADDRESS_LIST =
        			(ADDRESS =
           			(COMMUNITY = sls.ap-hop-paris.fr)
           			(PROTOCOL = TCP)
           			(HOST = o-ccam-b1)
           			(PORT = 10518)
        			)
       		)
       		(CONNECT_DATA =
        			(SID = CCP1SLS)
       		)
    		) ";   
    		

//====================SIMPA	 

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
	

//====================SUSIE
$ConnexionStringSUSIE="(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = sls-ora01.sls.aphp.fr)(PORT = 10325))
    )
    (CONNECT_DATA =
      (SID = SUP1SLS)
    )
  )";
  
//====================APPlication Compte Rendu (MidlleCare/CRWeb)
$AppCR_Objet="../commun/include/ClassMiddleCare.php";
$AppCR_ConnString="(DESCRIPTION =
						(ADDRESS_LIST =
						(ADDRESS = (PROTOCOL = TCP)(HOST = sls-ora04.sls.aphp.fr)(PORT = 10326))
						)
						(CONNECT_DATA =
						(SID = MIP1SLS)
						(SERVER = DEDICATED)
						)
						)";
$AppCR_User="consult";  
$AppCR_MotDePasse="consult076";  
  
/*
###########################################################################################################################
					NESTOR CONFIGURATIONS
###########################################################################################################################
*/
// mysql table nestor permet de gérer diffrent sites dans la même base

$FicherNestor="./files/sls_nestor.fic"; 										// fichier nestor sortie par l'outil NESTOR , renomme le pour gérer plusiers sites
$TableNestor ="erreur"; 																		// Table Nestor dans Mysql 
$FichierCodeErrorsNestor="./files/NestorCodeErreurs.fic"; 	// Tous les code d'erreurs possible dans nestor
$FichierSQLControl=("./files/sql_qualite_inc.php"); 							// fichier optionnel si on traites les erreurs via SQL;
$VersionRegle="201302";

/*
###########################################################################################################################
					CODAGE CONFIGURATIONS
###########################################################################################################################
*/
// mysql table nestor permet de gérer diffrent sites dans la même base

//$FicherCodage="codage_076.xls"; 										// fichier Menu Codage
//$FicherCodageListe="liste_codage_076.xls"; 										// fichier Menu Codage
$CheminCodage="./docs/"; //Local
$CheminCodageListe="../../codage/docs/"; //Local

//$CheminCodage="/data/www/intra_PMSI/intranet/codage/"; //serveur
$TableCodage ="erreur"; 																		// Table Codage dans Mysql 
$FichierCodeErrorsCodage="./files/CodageCodeErreurs.fic"; 	// Tous les code d'erreurs possible dans nestor
$FichierSQLControl=("./files/sql_qualite_inc.php"); 							// fichier optionnel si on traites les erreurs via SQL;


/*
###########################################################################################################################
					IPOP CONFIGURATIONS
########################################################################################################################
*/
$TableIpop="ipop";
$TableIpop_bloc_structure="ipop_bloc_struc";
$chemin					="/data/www/intra_PMSI/intranet/ipop/"; //serveur
$chemin_input			="/data/www/intra_PMSI/depot01/"; //serveur
//$chemin						="D:/www/intranet/ipop/"; //local
//$chemin_input			="D:/www/intranet/ipop/input_sls/"; //local
$chemin_bkup			=$chemin."bkup/";
$chemin_err				=$chemin."erreur/";
$fic_trace				="trace.txt";		
$trigramme				="SLS";
$ListeUhExecutant	="'824','818','815','816','819','820','823','354'";
	
?>
