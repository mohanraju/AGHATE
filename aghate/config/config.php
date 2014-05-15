<?Php  
/*
* PROJET AGHATE
* Fichier de configuration Base de donnes 
* Mysql et Oracle
* @Mohanraju SBIM/SAINT LOUIS/APHP /Paris
* date dernière modififation 14/05/2014
* 
*/

$site='001';
$CheminConfig="d:/www/config/";

// ne modife pas les lignes ci-dessous
$ConfigAghate=$CheminConfig."config_agt.php";
$ConfigSite=$CheminConfig."config_".$site.".php";

if(file_exists($ConfigAghate))
{
	include $ConfigAghate;
}	
else
{
	echo "<h1>Config du base Mysql::Erreur de chargement fichier config ou fichier introuvable : ".$ConfigAghate."</h1>";
	exit;
}


if(file_exists($ConfigSite))
{
	include $ConfigSite;
}	
else
{
	echo "<h1>Config du base SIH::Erreur de chargement fichier configuration du site  ou fichier introuvable : ".$ConfigSite."</h1>";
	exit;
}
	
?>
