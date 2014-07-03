<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
#		Query Extracteur				                                                               #
#                                                                                          #
#		Date dernière modification le 18/01/2012, 																						 #
#   le 23/05/2012  mis a jour les CSS	
#		le 17/11/2012 adoptaion avec le nouveau structure dossier                              #
#		le 05/02/2012 adoptation avec deux sites      sls/lrb                                  #
############################################################################################
*/
include("./config/config.php");
echo "Site en cours : ".$site;
$dossier_export="./trace/";

	if (count($_POST)) {
		while (list($key, $val) = each($_POST)) {
			$$key = $val;
		}
	}
	
$_SQL=str_replace("\\","",$_SQL);


/*
==============================================================================
funtion ConnectOracle($ConnString,$User,$Mdp,$VariableConnexion)
return le connextion 
==============================================================================
*/
function ConnectOracle($_Conn,$User,$Mdp)
{
 
	// vérify le connexion string à ete initilisé

		$ConnOracle = ocilogon($User,$Mdp,$_Conn);

	// recheck le connexion est initialisé
	if (!$ConnOracle)
  {
   	echo  "<br>Outil::Erreur connection Oracle";
   	exit;
  }
  return $ConnOracle; // connexion ouvert now
 
}
//=====================================================
//	Vérification du SITE / hopital
//=====================================================

if(strlen($site) < 1 )
{
	echo "<br><br><br><br><div align='center'>Veuillez spécifez votre site SVP  </div>";
	exit;
}else



$hopital =$site;
?>
<!--
----------------------------------------------------------
Partie  HTML
----------------------------------------------------------
-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iutf-8">
<TITLE>OUTIL SQL MSI <?php print $hopital ?></TITLE>
<link rel="stylesheet" type="text/css" href="./style_table.css" />
<script language="javascript" type="text/javascript" src="./style_table.js"></script>
<body>
<br>
<form method="POST" >
<h2 align='center'>OUTIL SQL MSI (<?php print $hopital ?>)</h2>
<table width="1200px"   border="1" align="center" cellpadding="0" cellspacing="3"  class="Tableau">
	<tr class="table_titre">
  	<td  align="center"> 	
		<textarea name="_SQL" cols="250" rows="6"><?php print $_SQL?></textarea>
		</td>  	  			
	</tr>	

	<tr class="table_titre">
  	<td  align="center"> 	
			<input type="radio" name="_select" value ="SIMPA" <?php  if($_select=="SIMPA") echo "checked"; ?> >SIMPA
			<input type="radio" name="_select" value ="SAG"   <?php  if($_select=="SAG") echo "checked"; ?> >SAG
			<input type="radio" name="_select" value ="SUSIE" <?php  if($_select=="SUSIE") echo "checked"; ?> >SUSIE
			<input type="radio" name="_select" value ="GILDA" <?php  if($_select=="GILDA") echo "checked"; ?> >GILDA
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="submit" name="_submit" value ="EXECUTER">
		</td>  	  			
	</tr>	
	</table>
</form >
<?php

//si  submit et et le sql 
if ( ($_submit=="EXECUTER" )and (strlen($_SQL) > 5)){
	switch ($_select)
	{
		case  "SIMPA" :
			$Conn=ConnectOracle($ConnexionStringSIMPA,"consult","consult");
			break;
		case "GILDA" :
			$Conn=ConnectOracle($ConnexionStringGILDA,"consult","consult");
			break;
		case  "SAG" :
			$Conn=ConnectOracle($ConnexionStringSAG,"arccam_v1","arccam_v1");			
			break;
		case  "SUSIE" :
			$Conn=ConnectOracle($ConnexionStringSUSIE,"susie","grutil");						
			break;
		default :
			echo "<div align='center' color='red'>Veuillez choisir une base PMSI SVP!!</div>";;
			exit;
			break;	
	}
	//execute uniquement SELECT
	if  (substr(trim(strtoupper($_SQL)),0,6) !="SELECT") 
	{
		echo "<div align='center' color='red'>Use Only select commands</div>";;
		exit;	
	}


	// onexecute le réqutette
	// vérify  connexion base
	if (!isset($Conn))
	{
		echo  "<br>Erreur connextion Oracle !!!";
		exit;
	}
	// executte qry
	$result = ociparse($Conn, $_SQL);
	
	// vérify les resulat d'exec
	if (!$result){
 		$oerr = OCIError($result);
		echo "<div align='center' color='red'>SQL Fetch Erreur :".$oerr["message"]."</div>";; 		
		exit;
	}
	//-------------------------------------------------------------
	// tous va bien  prepare le tablue ou on affiche errer d'exec
	//prepate tableau
	//-------------------------------------------------------------
	
	if (ociexecute($result)){
		$row=0;	
		//prepare fichier de sortie
		$__FicExcel=$user."_".date("His").".csv";
	  if (!$FileXls = fopen($dossier_export.$__FicExcel, 'w')) {
			echo "Impossible d'ouvrir le fichier csv ($FicXls)";
			//exit;
		}
		
		// on boucle sur le résultat
		while(ocifetch($result))
		{
			$ncols = ocinumcols($result);
			// Print Header
			if ($row==0)
			{
				echo  "<table border='1' align='center' id='myTable'  class='table' width='800px'>" ;
				echo  "<tr>" ;
				for ($i = 1; $i <= $ncols; $i++) 
				{
					$fic_csv .=ocicolumnname ($result, $i).";";
					echo "<th>".ocicolumnname ($result, $i)."</th>";
		 		}
		 		$fic_csv .="\n";
				echo  "</tr>";	
			}
			// pour tous les résulatat			
			for ($i = 1; $i <= $ncols; $i++) 
			{
				$fic_csv .=  oci_result($result, $i).";";
				echo "<td>". oci_result($result, $i)."&nbsp;</td>";	     	
	 		}
	 		$fic_csv .="\n";
	 		echo  "</tr>";
			$row++;
		}
		echo  "</table>";
		// close CSV file
		if (fwrite($FileXls, $fic_csv) === FALSE) {
			echo  "Impossible d'écrire dans le fichier csv ($FicXls)";
		}
		fclose($FileXls);

	}
	else
	{
		echo  "<br>Erreur Executing qry :".$sql;
	}

}		
// on ferme le connextion
if (isset($Conn))
{
		oci_close($Conn);
}		
if ($row > 0){
	echo "<br>";
	//remmonte un niveau pour le telechargement
	$DossierExport="../../trace/";
	// propose le telechargemzent de fichier
	echo "<br><br><div align='center'>
				<a href='./commun/include/download.php?file=".$__FicExcel."&dir=".$DossierExport."' target='_blank'>Télécharger ce tableau au format Excel</a>
				</div>";



}else{
echo "<div align='center'> Aucun donne trouvé!!! 	</div>";


}
 
?>	 
 
</body>

</html>
