<?php
/*
##########################################################################################
	Projet CODAGE
	Get Codage + Libelle depuis la liste des chapitres et sous chapitres des fichier .XLS
	Script appelé dans la page "index.php"
	Auteur Thierry CELESTE SLS APHP
	Maj le 28/05/2013
##########################################################################################
	Parametres de page 
	$chap=$_GET["CHAP"];
	$FicherCodageListe=$_GET["LISTE"];
*/
require("../../user/session_check.php");
//-------------------------------------------------------------------------
// 		Vérifiaction du site déclared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	echo "<br> :Erreur accès, Site inconnu ou non declarer pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];
	//echo $_SESSION["site"];
}
require_once("../../config/config.php");
include("../../config/config_".$site.".php");
require_once("../include/ClassMysql.php");
require_once("../../commun/include/ClassNestor.php");
require_once('../include/ClassCim10.php');

$Nestor =new Nestor($site); // declaraion site

//Declarer dans ./inc/config.php

$chap=$_GET["CHAP"];
$FicherCodageListe=$_GET["LISTE"];
	
$fichier=$CheminCodageListe.$FicherCodageListe;
	
$fp = fopen($fichier, 'rb');
$aLines = file($fichier);
?>
<table width="100%" border='1' align="center" id="navigation" cellspacing="5">
	<tr>
		<td align="center" colspan="3" style='font-size:14px'>
			<b><?php echo stripslashes($chap); ?></b>
		</td>
	</tr>
	<?php
		$compt=0;
		$lastSsChap="";
		foreach($aLines as $sLine) {
		 	$sLine=str_replace("'","\'",$sLine);
			$aCols = explode("\t", $sLine);
			
			//$cma=$Nestor->Checkcma($aCols[3]);
			$theme="Cma".trim($cma);
			if ($cma > 0)
				$niveau="";
				//$niveau="<span class=\"badge $theme\"> ".$cma."</span>";
				//$niveau="<span class=\"badge badge-info\">".$cma ."</span>";
			else
			$niveau="";
			
			if ($aCols[0] == $chap){
				if ($aCols[1] != $lastSsChap) {
					$compt=0;
					print "
						<tr>
							<td align='center' colspan='3' style='font-size:12px'>
								<b>".stripslashes($aCols[1])."</b>
							</td>
						</tr>
					";
					$lastSsChap = $aCols[1];
				}
				$compt++;
				//echo $compt;
				$retval="'".$aCols[2]."|".$aCols[3]."'";
				if ($compt == 1){
					print "
						<tr>";
				}
				print "
							<td width='50%' align='left' class=\"initial\"
							onMouseOver=\"this.className='highlight'\"
							onMouseOut=\"this.className='normal'\"
							onClick=\"GetCodage($retval)\" style='font-size:11px'>
								<b>".stripslashes($aCols[2])."</b>
							</td>
					";
				if ($compt == 2){
					print "
						</tr>";
					$compt=0;
				}
			}
		} 		
	?>
</table>
