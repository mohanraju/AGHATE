<?php
/*
##########################################################################################
	Projet CODAGE
	Affiche Liste Intervention
	Script appelé dans la page "index.php" && au Onchange Thésaurus Service
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
	$thesaurus 
*/
require("../../user/session_check.php");
//-------------------------------------------------------------------------
// 		Vérifiaction du site déclared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
if (strlen($_SESSION["site"]) < 1)
{
	$retval .= "<br> :Erreur accès au IPOP, Site inconnu ou non declared pour l'utilisateur!!!"	;
	exit;
}
else
{
	$site=$_SESSION["site_patient"];

}

// inclusion des objets

require_once("../../config/config.php");
require_once("../../config/config_".strtolower($site).".php"); 
require_once("../../commun/include/ClassMysql.php");
require_once("../../commun/include/ClassCim10.php");

$db=new MySQL();
$Cim10=new Cim10();

$nip=$_GET['NIP'];

$retval = "<table width='100%' border='1' align='center' id='navigation' cellspacing='5'>
	<tr>
		<td align='center' colspan='3' style='font-size:14px'>
			<b>Historique patient</b>
		</td>
	</tr>";


//-------------------------------------------------
// Si NDA est Vide demande de saisir un NDA
// permet d'utiliser le module toute seul
//-------------------------------------------------
if ((strlen($nip) > 7 ))
{
	$diag=$Simpa->GetDiagnosticsParNIP($nip);
	$totrecords=count($diag) ;
	$compt=0;
	
	for ($i=0 ; $i < $totrecords ; $i++)
	{
		$cma=$Nestor->Checkcma($diag[$i]['CODE']);
		$theme="Cma".trim($cma);
		if ($cma > 0)
			$niveau="";
			//$niveau="<span class=\"badge $theme\"> ".$cma."</span>";
			//$niveau="<span class=\"badge badge-info\">".$cma ."</span>";
		else
			$niveau="";
		$compt++;
		//echo $compt;
		$retres="'".addslashes($diag[$i]['LIBELLE'])."|".$diag[$i]['CODE']."'";
		if ($compt == 1){
			$retval .= "<tr>";
		}
		$retval .= "
					<td width='50%' align='left' class=\"initial\"
					onMouseOver=\"this.className='highlight'\"
					onMouseOut=\"this.className='normal'\"
					onClick=\"GetCodage($retres)\" style='font-size:11px'>
						<b>".stripslashes($diag[$i]['LIBELLE'])."</b>
					</td>
		";
		if ($compt == 2){
			$retval .= "
				</tr>";
			$compt=0;
		}
	}
}
$retval .= 		"</table>";
$Simpa->Close();
echo $retval
?>
