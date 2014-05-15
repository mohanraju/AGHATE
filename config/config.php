<?Php  
/*
* PROJET AGHATE
* Fichier de configuration Base de donnes 
* Mysql et Oracle
* @Mohanraju SBIM/SAINT LOUIS/APHP /Paris
* date dernière modififation 14/05/2014
* 
* MODULE FORMS
*/

$site='001';
$CheminConfig="d:/www/config/";

// ne modife pas les lignes ci-dessous
$ConfigAghate=$CheminConfig."config_msi.php";
$ConfigSite=$CheminConfig."config_".$site.".php";

/*
 *  chargement du fichier config_msi.php
 */ 
if(file_exists($ConfigAghate))
{
	include $ConfigAghate;
}	
else
{
	echo "<h1>Config Base Mysql::Erreur de chargement fichier config ou fichier introuvable :-".$ConfigAghate."</h1>";
	exit;
}

/*
 *  chargement du fichier site config_???.php
 * */
if(file_exists($ConfigSite))
{
	include $ConfigSite;
}	
else
{
	echo "<h1>Config Base SIH::Erreur de chargement fichier configuration du site  ou fichier introuvable :-".$ConfigSite."</h1>";
	exit;
}

?>
