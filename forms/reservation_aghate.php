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
include("../config/config_".$site.".php");

//AfficheResaInfo;
if ($mode=="MODIFY" || strlen($id) < 1) {
		$page_vars="t=t";
	foreach ($_GET as $key=> $val)
	{
		$page_vars .= "&" . $key ."=" .$val;
	}
 	
	header("location:../aghate/reservation.php?".$page_vars);
	exit;	
}

$com=new CommonFunctions(true);
$Html=new Html();
$Forms=new Forms($site);
$Forms->Html=$Html;
$aghate = new Aghate();
include("../commun/layout/header.php");
echo  ' <link href="../commun/styles/bootstrap_form.css" rel="stylesheet">  ';


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
<form name="resa" id="resa" action=<?php print $PHP_SELF;?> method="POST">
<?php

 	
 		$FichierProjet = "DescriptifProjet";
		$DP=$Forms->GetDescriptifProjet($FichierProjet);
		echo '<div class="row-fluid">';
		echo '<div class="span1 "></div>';
		echo '<div class="span7">';
 		
 		echo '<div id="view_resa"></div>';
 		echo '<br>';
 		
 		
 		
 		if ($mode=="" /*&& authGetUserLevel(getUserName(),-1,'area') >= 2*/ )
 		{
 			$consulter='disabled="disabled"';
 		}
 		else
 		{
 			$consulter='';

 		}
 		
 		echo '<div id="modif_form">';
 		$Forms->PrintForm($DP,$id,$consulter);
 		if ($mode=="")
 			echo "<script>disableModifier();</script>";
 			
 		echo '</div>';
	
	print $Html->InputHiddenBox(id,$id);
	print $Html->InputHiddenBox(area,$area);
	print $Html->InputHiddenBox(room,$room);
	print $Html->InputHiddenBox(table_loc,$table_loc);		

	print $Html->InputHiddenBox(prenom,$prenom);
	print $Html->InputHiddenBox(nomjf,$nomjf);
	print $Html->InputHiddenBox(ddn,$ddn);
	print $Html->InputHiddenBox(sexe,$sexe);
	print $Html->InputHiddenBox(age,$age);
	print $Html->InputHiddenBox(mode,$mode);
	print $Html->InputHiddenBox(service_name,$service_name);
	print $Html->InputHiddenBox(TypeCodage,'ID_DAS');

	print $Html->InputHiddenBox(RefData,'');
	
	include("../commun/layout/footer.php");	
?>
 
