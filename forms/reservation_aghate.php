<?php
include("../aghate/commun/include/settings.inc.php");
session_name('GRR');
session_start();
 
include("../config/config.php");
require("../commun/include/ClassMysql.php");
include("../commun/include/ClassForms.php");
include("../commun/include/CommonFonctions.php");
include("../commun/include/ClassHtml.php");
include("../commun/include/ClassSimpa.php"); 
include("../commun/include/ClassGilda.php"); 
include("../aghate/commun/include/ClassAghate.php");


//Preparation Page Variable
$page_vars="t=t";
foreach ($_GET as $key=> $val)
{
	$page_vars .= "&" . $key ."=" .$val;
}

//AfficheResaInfo;
if ($mode=="MODIFY" || strlen($id) < 1) {
	header("location:../aghate/reservation.php?".$page_vars);
	exit;	
	//header("location:../aghate/reservation.php?id=$id&area=$area&room=$room&hour=$hour&minute=$minute&year=$year&month=$month&day=$day&page=day");
	//exit;
}

$com=new CommonFunctions(true);
$Html=new Html();
$Forms=new Forms($site);
$Forms->Html=$Html;
include("../commun/layout/header.php");
echo  ' <link href="../commun/styles/bootstrap_form.css" rel="stylesheet">  ';
$path="aghate/commun/ajax/ajax_aghate_get_resa_info_from_id.php";
//if($type_reservation=="Demande")
//	$path="aghate/commun/ajax/ajax_aghate_get_demande_info_from_id.php";
if(strlen($jsonpat) < 1){
	//echo "First";
		$url= "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];		
		$url= str_replace(strrchr($url,"/"),"/",$url);		
		$url= str_replace("forms/","",$url);		
	$jsonpat = file_get_contents($url.$path."?entry_id=".$id."&mode=".$mode."&table_loc=".$table_loc."&login=".$login."&type_reservation=".$type_reservation);
}else
{
	//echo "Second";
	//echo stripcslashes($jsonpat);
	$jsonpat= get_magic_quotes_gpc() ? stripslashes($jsonpat): $jsonpat ;	

	//echo $jsonpat;
	//exit;
}
/*echo '<pre>';
//print_r($_POST);
//print_r($_SESSION);
echo $jsonpat;
echo "<br><br>";
print_r(json_decode($jsonpat,true));*/
$result=json_decode($jsonpat,true);
$result['filename']=$filename;

foreach ($result as $key => $value) {
	//echo "clé : ".$key." valeur : ".$value."<br>";
    ${$key} = $value;
}
$nip=$noip;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Feuille Codage</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="../commun/js/jquery-1.9.1.js"></script>
	<script type="text/javascript" src="../commun/js/jquery.autosize.js"></script>
	<script type="text/javascript" src="../commun/js/jquery-migrate-1.2.1.js"></script>
	<script type="text/javascript" src="../commun/js/jquery_ui.js"></script>
	<script type="text/javascript" src="../commun/js/fonctions_link_aghate.js" charset="utf-8"></script>
	<link rel="stylesheet" href="../commun/styles/jquery-ui.css" />
</head>
<body style="">
	
<form name="resa" id="resa" action=<?php print $PHP_SELF;?> method=POST>
<?php
	print $Html->InputHiddenBox(id,$id);
	print $Html->InputHiddenBox(table_loc,$table_loc);		
	print $Html->InputHiddenBox(jsonpat,htmlspecialchars($jsonpat),100,100);
	print $Html->InputHiddenBox(nip,$nip);
	print $Html->InputHiddenBox(nda,$nda);
	print $Html->InputHiddenBox(area,$area);
	print $Html->InputHiddenBox(room,$room);
	print $Html->InputHiddenBox(type_reservation,$type_reservation);

	print $Html->InputHiddenBox(prenom,$prenom);
	print $Html->InputHiddenBox(nomjf,$nomjf);
	print $Html->InputHiddenBox(ddn,$ddn);
	print $Html->InputHiddenBox(sexe,$sexe);
	print $Html->InputHiddenBox(age,$age);
	print $Html->InputHiddenBox(mode,$mode);
	print $Html->InputHiddenBox(service_name,$service_name);
	print $Html->InputHiddenBox(TypeCodage,'ID_DAS');
	print $Html->InputHiddenBox(RefData,'');
		//$nom = $_SERVER["SCRIPT_NAME"]; 
		//echo $nom;
		$FichierProjet =(strlen($nom_formulaire)>5)?$nom_formulaire:"DescriptifProjet";
		$DP=$Forms->GetDescriptifProjet($FichierProjet);
		echo '<div class="row-fluid">';
		echo '<div class="span1 "></div>';
		echo '<div class="span7">';
		/* par mohan
		* si nip est vide on affiche le variable patiant si
		* sinon  nom + prenom + ddn...
		*/
		if(strlen($noip)< 1)
			$patient= "<span color='red'>".$patient."</span>";
		else
			$patient= $nom.' '.$prenom.' N&eacute;e le '.$naissance.'</b><br><b>NIP</b> '.$noip.' <b>NDA</b> '.$nda.'<br>';
		
		// check commentaire est un array ou non 
		$commentaire=is_array($description['DESC___COMPL'])?$description['DESC___COMPL']['Valeur']:$description['DESC___COMPL'];	
	 		
 		echo '<div id="view_resa">
 		<a href="../aghate/reservation.php?id='.$id.'&table_loc='.$table_loc.'&type_reservation='.$type_reservation.'&mode=MODIFY">Modifier cette r&eacute;servation</a> <br>
		<table width="700">
		<td id="Patient"><b>'.$patient.' <br>
		<b>Motif</b> : '.$protocole.'<br>
		<b>Medecin responsable</b> : '.$nom_medecin.' '.$prenom_medecin.'<br><br>
		<b>Sp&eacute;cialit&eacute;</b> : '.$specialite.'<br><br>
		'.$service_name.' du '.$entry.' au '.$end.'<br>
		</td></tr></table>
		<br><b>Commentaire : </b>'.$commentaire.'
 		</div>';
 		
 		echo '<br>';
 		//echo '<script> LoadResaInfo();</script>';
 		if ($mode=="")
 		{
 			$consulter='disabled="disabled"';
 		}
 		else
 		{
 			$consulter='';

 		}
 		/*if (strlen($NDA) < 1 ) {
			echo "<script>top.document.location='".$nom."?NDA='+obj.nda+'&UH='+obj.uh+'&".$page_vars."';</script>";
		}*/
 		//echo "nda : ".$nda;
 		//exit;
 		echo '<div id="modif_form">';
 		$Forms->PrintForm($DP,$result,$consulter);
 		if ($mode=="")
 			echo "<script>disableModifier();</script>";
 			
 		echo '</div>';

	$style=(strlen($log)>0)?"display:black;":"display:none;"; 
	echo '<div id="LOG" style="'.$style.'">Les traces de la page sont disponible ici<br></div>';
	
	
	include("../commun/layout/footer.php");	
?>
 
