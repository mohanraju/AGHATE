<?php
/*
 * update_structure.php
 * Fichier qui apelle les fonctions de la classe GildaToAghate
 * Synchronise la structure de Gilda avec Aghate
*/

set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassGildaToAghate.php";

#Paramètres de connection
require_once("./commun/include/settings.inc.php");
#Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");
#Fonction relative à la session
require_once("./commun/include/session.inc.php");

// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};
include "./commun/include/language.inc.php";

 
// Initialisation
$Aghate= new Aghate();
$Aghate->AffcheTraceSurEcran=true;
$Gilda= new Gilda($ConnexionStringGILDA);

$debut = time();

$user_level = authGetUserLevel(getUserName(),-1);
$login= $_SESSION['login'];

if(($user_level < 5 )){
	echo "<h1> Vous n'avez pas les droits pour lancer la mise à jour de la structure </h1>";
	header('Location : ./day.php');
	exit;
}

// Initialise le fichier de trace
$Aghate->init_trace_file ();

/*
 ==================================================================================================
 *    UPDATE medecins
  * 
 *=================================================================================================
 */
// Import Medecin
$ListMed=$Gilda->GetAllMedecins();
$ListExcure[]="INTERNE";///// a faire
		
$Aghate->AddTrace(" #####=> Mise a jour Medecins \n");				
$TotalMed=count($ListMed);
for($i=0; $i < $TotalMed; $i++)
{
	$CheckPresentSql="SELECT * from agt_medecin where nom='".addslashes($ListMed[$i]['NMPHOS']) ."' AND prenom='" .addslashes($ListMed[$i]['NMPPHS'])."'";
	if(count($Aghate->select($CheckPresentSql)) < 1 )
	{
		$TableauInsertDonnee['titre'] 	= 	$ListMed[$i]['LBTITR'];
		$TableauInsertDonnee['nom'] 	=	$ListMed[$i]['NMPHOS'];
		$TableauInsertDonnee['prenom'] 	=	$ListMed[$i]['NMPPHS'];	
					
		$Aghate->insertion('agt_medecin',$TableauInsertDonnee);				
		$Aghate->AddTrace("\nMedecin ".$ListMed[$i]['NMPHOS']."=> Inseré");
	}else{
		$Aghate->AddTrace("\nMedecin ".$ListMed[$i]['NMPHOS']." Déja present");
	}
} 


// Ecrit dans le fichier de script
$Aghate->write_trace_file();


$fin = time();

$result = $fin - $debut;
echo "<br />Temps du traitement : ";
echo gmdate("H:i:s", $result); 

?>
