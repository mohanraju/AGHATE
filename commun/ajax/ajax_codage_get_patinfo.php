<?php
/*
##########################################################################################
	Projet CODAGE
	Get patient information par NIP ou NDA
	Script appeler dans la page "fonctions_codage.js"
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
	NIP ou NDA 
*/
require("../../user/session_check.php");
//-------------------------------------------------------------------------
// 		Verification du site declarer dans la session 
//		par rapport la connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur acces au module, Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
	//echo $_SESSION["site"];
}
include("../../config/config_".$site.".php");
require_once('../../commun/include/ClassGilda.php');
$Gilda =new Gilda($ConnexionStringGILDA);



// reucupare les parametres
$NIP=$_GET['NIP'];
$NDA=$_GET['NDA'];

// si nip on cherche par nip si non par NDA
if (strlen($NIP)==10)
	$res=$Gilda->GetPatInfoParNIP($NIP);
elseif (strlen($NDA)==9)
	$res=$Gilda->GetPatInfoParNDA($NDA);

//Prepare XML si patient trouver
if (count($res) > 0)
{
	$retval="<patient>";

	foreach ($res[0] as $key => $val)
	{
		$retval.="<".strtolower($key).">".$val."</".strtolower($key).">";			
	}
	
	$retval.="</patient>";		
	print $retval;
	
}
else
	print "<ERROR>Pas de patient </ERROR>";	
?>
