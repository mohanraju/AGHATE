<?Php  
/*
#########################################################################
	PROJET MSI                       	  																	
 	config.php 
	MOHANRAJU Sp APHP/SaintLouis                          
#########################################################################
	configuration du base Mysql
	Dernière modification : 10/05/2013                    
	
*/

//===============================================
//MYSQL Database settings
//===============================================
$serveur=true; // facilite la basculemnt de script vers server/local
date_default_timezone_set("Europe/Paris");
$site='001';
if ($serveur){
	//server settings
	$DBName = "intramsi";
	$DBUser = "intramsi";//"root"
	$DBPassword = "intramsi";//"-/Peldt!";
	$DBHost = "nck-mysql3.nck.aphp.fr";//"localhost";
	$ExportDir  = "//http7/ulysse/aghate/forms/export/" ;	//utilise pour toute export csv		
	$TBBDir  = "//http7/ulysse/aghate/forms/tbb/files/" ;		//répertoire des fichier TBB
}else{
	//local settings
	$DBName = "intramsi";
	$DBUser = "root";
	$DBPassword = "";
	$DBHost = "localhost";
	$ExportDir  = "D:/www/INTRANET/export/" ;	//utilise pour toute export csv
	$TBBDir  = "D:/www/INTRANET/tbb/files/" ;	//répertoire des fichier TBB				
}


//Variables
$charset="ISO-5589-1";					// Charset du Projet
$sites=array("001");			// les sites a traité (codes hopitaux)
$AfficheMenu=true; 							//affiche les menu de tous les modules
$ModuleAccuil="suivi"; 					// nom de dossier pour le page d'accuil



date_default_timezone_set('Europe/Moscow');
	
?>
