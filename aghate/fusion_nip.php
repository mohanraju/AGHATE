<?php
	session_start();
	
	if (!(session_is_registered("CID"))){
		include("relogin.php");
		exit;
	}
	?>
<html>
<head>
<title>FUSION DES NIPS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="style/style.css" type="text/css">
<script language="javascript" type="text/javascript" src="./commun/js/validations.js"></script>
<style type="text/css">
input.radio {
background-color: lightblue;
color: green;
}
</style>
<style type="text/css">
<!--
.Style1 {
	color: #0000CC;
	font-weight: bold;
	font-size: 14px;
}
-->
</style>
</head>

<body bgcolor="#FFFFFF" text="#000000">
<?php 
	include("head.php"); 
 include("menu_admin.php");

?>

<form>

<p>&nbsp;</p>
<table width="60%" border="0" cellspacing="1" cellpadding="1" align="center" >
  <tr>
    <td align="center" bgcolor="#99CCFF"><span class="Style1"> Fusion des NIPs </span></td>
  </tr>
  <tr>
    <td align="center"><p><br />Cette outil permet de v&eacute;rifier les NIPs fusionnes dans GILDA  et mis a jour dans la base REGA</p>
      <p>L&rsquo;ancienne NIP sont remplace par &nbsp;&laquo;FUSIONNEE&nbsp;&raquo; dans la base REGA </p>
    <p> clique &laquo;&nbsp;Continuer&nbsp;&raquo; pour lancer le fusion.</p>
    <br /><br /></td>
  </tr>
  <tr>
    <td align="center"><input type="submit" name="Continuer" id="button2" value="Continuer" /></td>
    </tr>
</table>

</form>


<?php
/*
ini_set("display_errors" , "E_ALL");
ini_set('error_reporting', E_ALL);
error_reporting(E_ALL);
ini_set("display_errors", 1);
*/

	include ("./commun/include/CustomSql.inc.php");
   $db = new CustomSQL($DBName);
   
if($Continuer=="Continuer"){   
	require("./config/connexion_gilda.php");		   
	$sql_fusion="SELECT NOIP,NOM, PRENOM,DDN,SEX,count(nom) as NBR FROM pat where noip not like('FUSIONNEE')
					group by nom, prenom,ddn,sex
					order by count(nom) desc
					limit 5";
				
	$tbls_update= array("clinique","conj_cytatheque","conj_lcrtheque","conj_serotheque","toxicite");

	$res=$db->select($sql_fusion);
	$count=0;
	$noip_list="'VIDE'";
	

	for($i=0;$i < count($res);$i++){
		if($res[$i]['NBR'] > 1){
			$count++;
			$noip_list.=",'".$res[$i]['NOIP']."'";			
		}
	}	

	if ($count < 1){
		echo "<p align='center' class='ColorRed'> <b>Aucun nip à fusionner</b></p>";
		exit;
		}
	

  			//CHECK FUSION NIP dans GILDA
	  		$sql_fus="select nipfus,nipact,nomfus,prefus,sexfus,danfus from fus where nipact in ($noip_list) or nipfus in ($noip_list)";

	  		$res_fus = ociparse($ConnGilda, $sql_fus);
			ociexecute($res_fus);

			while(ocifetch($res_fus)){	 
				$nip_fus=ociresult($res_fus, 1);
				$nip_act=ociresult($res_fus, 2);
				echo "<hr>";
			 	echo "NIP fusionné   :". $nip_fus ." ,  NIP Actuel :".$nip_act. ", Nom : ".ociresult($res_fus, 3).", Prénom  : ".ociresult($res_fus, 4).", Sexe  : ".ociresult($res_fus, 5).", date nias  : ".ociresult($res_fus, 6)."<br />  ";
			 	
			 	$res_nipfus =$db->select("select id_pat from pat where noip='".$nip_fus."'"); // nip fusionneé
			 	$id_pat_old=$res_nipfus[0]['id_pat'];
			 	
			 	$res_nipNEW =$db->select("select id_pat from pat where noip='".$nip_act."'"); // nip actuelle
			 	$id_pat_new=$res_nipNEW[0]['id_pat'];
			 	// Maj dans les tables corresponds 
			 	for($t=0;$t < count($tbls_update);$t++){	
				 	$sql="UPDATE ".$tbls_update[$t]." set id_pat='".$id_pat_new."' where  id_pat ='".$id_pat_old."'"; 
				 	$db->update($sql);
				 	echo "<br />".$sql;
				}
				// maj dans table pat inverse le nip fusioné 
				 	$sql="UPDATE pat set noip='FUSIONNEE' where  noip ='".$nip_fus."'"; 
				   $db->update($sql);
				 	echo "<br />".$sql;
				 		echo "<br />";
			}
			   
		//fin vérification fusion	
	require("./commun/include/_~conn.php");	
}

?>
