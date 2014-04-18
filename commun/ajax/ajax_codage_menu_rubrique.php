<?php
/*
##########################################################################################
	Projet CODAGE
	Affiche Menu Rubrique
	Script appelé dans la page "index.php" && au Onchange Thésaurus Service
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
	$thesaurus 
*/
require("../../user/session_check.php");

if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur accès, Site inconnu ou non declarer pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
}
include("../../config/config_".$site.".php");

$thesaurus=$_GET['thesaurus'];
//Vérification si thesaurus deja au bon format pour eviter les erreurs
if(substr($thesaurus, -4) != ".xls")
	$FicherCodage="Chap_codage_".str_replace(' ','',$thesaurus).".xls";
else
	$FicherCodage=$thesaurus;
	
$fichier=$CheminCodageListe.$FicherCodage;

//Vérification du fichier thésaurus
if (is_File($fichier))
{
	$fp = fopen($fichier, 'rb');

	//Si le thésaurus service n'existe pas dans le répertoire /docs alors on charge le thésaurus site par default
	if ($fp==false)
	{
		$FicherCodage="Chap_codage_".$_SESSION['site_patient'].".xls";
		$fichier=$CheminCodage.$FicherCodage;
		$fp = fopen($fichier, 'rb');
	}
	$aLines = file($fichier);
	?>
	<table width="100%" border='0' align="center" class="table table-condensed">
		<tr>
			<td align='left' class='navigation' >
				<a href='#?' onclick="javascript:getPage('../commun/ajax/ajax_codage_get_historique_cma.php?NIP='+$('#NIP').val(),'boitecodage');">Historique patient</a>
			</td>
		</tr>
			<?php
			//initialisation var lastChap qui sera égale au dernier chapitre rencontré, ce qui permet de ne pas répéter plusieur fois un meme chapitre
			$lastChap="";
			 foreach($aLines as $sLine) {
				 	$sLine=addslashes($sLine);
					$aCols = explode("\t", $sLine);
					//On affiche le Chapitre seulement s'il est different du précédent
					if ($aCols[0] != $lastChap) {
						print "<tr><td align='left' class='navigation' ><a href='#?' onclick=\"javascript:getPage('../commun/ajax/ajax_codage.php?CHAP=$aCols[0]&LISTE=$FicherCodage','boitecodage');\">".stripslashes($aCols[0])."</a></td></tr>";
						$lastChap = $aCols[0];
					}
			 	}
			 	fclose($fp);
			?>
	</table>
<?php
}else{
	$retval='';
	echo $retval;
}
?>
