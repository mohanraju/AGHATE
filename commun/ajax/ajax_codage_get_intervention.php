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
require_once("../../commun/include/ClassHtml.php");
require_once("../../commun/include/CommonFonctions.php");

$db=new MySQL();
$Cim10=new Cim10();
$html = new Html($LoadListe=True);
$ComFunc =new CommonFunctions();

$NIP=$_GET['NIP'];
$NDA=$_GET['NDA'];
$DTEENT=$ComFunc->Normal2Mysql($_GET['DTEENT']);
$NOHJO=$_GET['NOHJO'];

//-------------------------------------------------
// Si NDA est Vide demande de saisir un NDA
// permet d'utiliser le module toute seul
//-------------------------------------------------
if ((strlen($NIP) > 9 ))
{
	$ROWACTID="0";
	$retval.='<table id="TblActes" width="100%" border="0" cellspacing="0" cellpadding="0" align="left">';
	// Préparation de la liste des Actes IPOP
	$ListeIntervention=$Cim10->GetListeIntervention($NIP,$DTEENT,$NOHJO);
	for($i=0;$i< count($ListeIntervention);++$i) 
	{
		$IDIPOP=$ListeIntervention[$i]['id_ipop'];
		$DATEINTERVENTION=$ComFunc->Mysql2Normal($ListeIntervention[$i]['datrea']);
		$CHIRURGIEN=$ListeIntervention[$i]['Chirurgien'];
		$ANESTHESISTE=$ListeIntervention[$i]['Anesthesiste'];
		$UMDETRAVAIL=$ListeIntervention[$i]['um_de_travail'];
		$TYPEINTER=html_entity_decode($ListeIntervention[$i]['Type_inter']);
		if($i==0) $MyFirstIntervention=$DATEINTERVENTION."|".$IDIPOP."|||||".$NDA."|".$NOHJO;
		//$MyInterventionIpop=$DATEINTERVENTION."|".$IDIPOP."|".$CHIRURGIEN."|".$ANESTHESISTE."|".$UMDETRAVAIL."|".$TYPEINTER."|".$NDA;
		//$ListeInterventionUser[]=$MyInterventionIpop."|".$TYPEINTER." (".$DATEINTERVENTION.")";
		$Date[]=$DATEINTERVENTION;
		
		$MyIntervention=$DATEINTERVENTION."|".$IDIPOP."|||||".$NDA."|".$NOHJO;
	 	$retval.= '
		<tr id="ROWACTID"	name="ROWACTID'.$ROWACTID.'"  MyIntervention="'.$MyIntervention.'"	>
	    <td>'.$TYPEINTER.' - '.$DATEINTERVENTION.'</td>
	  </tr>
	 	';
		
		$ROWACTID=$ROWACTID+1;
	}
	
	//Tri sur le tableau
	//sort($ListeInterventionUser);
	
	/*echo "<pre>";
	print_r($Date);
	exit;*/
	//Vérification de la présence de la date du jour dans la liste des dates
	if (!in_array(date('d/m/Y'), $Date))
	{
		//Si non présent dans la liste on Force la date du jour en fin du tableau
		//$MyInterventionJour=date('d/m/Y')."||||||".$NDA;
		//$ListeInterventionUser[]=$MyInterventionJour."|Intervention du jour (".date('d/m/Y').")";
		$TYPEINTER="Intervention du jour";
		$MyIntervention=date('d/m/Y')."||||||".$NDA."|".$NOHJO;
		$retval.= '
			<tr id="ROWACTID"	name="ROWACTID'.$ROWACTID.'"  MyIntervention="'.$MyIntervention.'"	>
		    <td>'.$TYPEINTER.'</td>
		  </tr>
		 ';
		if($MyFirstIntervention=="") $MyFirstIntervention=$MyIntervention;
	}
	//Si les dates intervention IPOP et les dates realisation CODAGE ne sont pas egale a la date du jour alors on renseigne les informations
	//necessaires pour forcer la selection dans la page "ajax_codage_get_sejour.js" a la date du jour
	echo '<input type="hidden" id="MyIntervention" name="MyIntervention" value="'.$MyFirstIntervention.'" />';
	echo '<input type="hidden" id="MyROWACTID" name="MyROWACTID" value="ROWACTID0" />';
	//$retval.=$html->InputSelect($ListeInterventionUser,lst_intervention,$lst_intervention,'250','size="5" onChange="RefreshInfoIntervention(this.value)"');
}
echo $retval."</table>";
?>
