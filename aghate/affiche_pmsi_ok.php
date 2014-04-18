<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html> <head>
<title>Détails séjours patients</title>
<LINK REL="StyleSheet" HREF="file:///C|/DOCUME~1/3217662/LOCALS~1/Temp/style2.css">
</head>
<table width="50%" border="0" cellspacing="1" cellpadding="1">
  <tr>
    <td>Nom</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Prenom</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>NIP</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Diagnostique Princiapale</td>
    <td><input name="textfield" type="text" id="textfield" size="6" maxlength="6">
    <label> libelle</label></td>
  </tr>
  <tr>
    <td>Diganostique R&eacute;li&eacute;e</td>
    <td><input name="textfield2" type="text" id="textfield2" size="6" maxlength="6">
<label> libelle</label></td>
  </tr>
  <tr>
    <td>Diganostique associ&eacute;e</td>
    <td><textarea name="textarea3" id="textarea3" cols="60" rows="5"></textarea></td>
  </tr>
  <tr>
    <td>Actes</td>
    <td><textarea name="textarea2" id="textarea2" cols="60" rows="5"></textarea></td>
  </tr>
</table>
<input name="affiche_codage" type="submit" id="affiche_codage"  value="Afficher les derni&egrave;re codage">

<?php
if (isset($_GET["affiche_codage"])){
	$count=0;
	require("./config/connexion_simpa.php");

	$noip = $_GET["noip"];	
	echo $noip;	
	$actes=array();


		//REQUETE
		
		$sql="select NIP, NDA,kyres,DE, DF, DP, DR, GHM, LBDP, dusej,LBGHM FROM
				(
				select distinct(rsm.noda) as nda,rsm.noip as NIP, rsm.kyres as kyres, TO_char(d8deb,'DD/MM/YYYY') as DE,TO_char(d8fin,'DD/MM/YYYY') as DF, rsm.CDDNC9 as DP, rsm.CDRELIE as DR, '' as DAS, rss.CDGHM as GHM, LBCIM as LBDP,dusej as dusej, ghm.lbghm as LBGHM
				from RUM,RSS,RSM, CIM,GHM
					where  rss.D8fin >= to_date('01/01/2008','DD/MM/YYYY')
					AND rss.D8fin < to_date('31/12/2008','DD/MM/YYYY') + 1
					and RUM.NORES = RSM.KYRES
					AND RUM.KYRSS = RSS.KYRSS
					AND CIM.KYCIM = rsm.CDDNC9
					AND GHM.KYGHM= rss.CDGHM
					and rsm.cdurm in ('470')
					and rsm.noip =".$noip." 
				)
				order by kyres desc";
		
		//FIN REQUETE
		//echo $sql;
		
		$result = ociparse($ConnSimpa, $sql);
		ociexecute($result);
		$r=0;			
		while(ocifetch($result))
		{
	
			$actes='';
			$das='';
	
			$lesactes[$r]['noip']	= ociresult($result, 1);
			$lesactes[$r]['noda']	= ociresult($result, 2);
			$lesactes[$r]['nores']	= ociresult($result, 3);
			$lesactes[$r]['de']		= ociresult($result, 4);
			$lesactes[$r]['df']		= ociresult($result, 5);
			$lesactes[$r]['dp']		= ociresult($result, 6);
			$lesactes[$r]['dr']		= ociresult($result, 7);
			$lesactes[$r]['ghm']		= ociresult($result, 8);
			$lesactes[$r]['lbdp']	= ociresult($result, 9);
			$lesactes[$r]['dusej']	= ociresult($result, 10);	
			$lesactes[$r]['libghm']	= ociresult($result, 11);	
		
			$nores=ociresult($result, 3);
			/*pour actes*/
			$sqlSuite="select distinct rac.kycdac,cda.lbcda from rac,cda 
			where rac.kycdac=cda.kycda and verclas='2' and rac.kyres='$nores'";
			
		
			$resultSuite = ociparse($ConnSimpa, $sqlSuite);
			ociexecute($resultSuite);
				
			while(ocifetch($resultSuite))
			{
				$acte = ociresult($resultSuite, 1);
				$lbacte = ociresult($resultSuite, 2);
				$actes=$actes.$acte." : ".$lbacte.";<BR> ";
			}
			$lesactes[$r]['actes']	= $actes;		
		
			/*pour DAS*/
			$sqlSuiteDas="select dgn.kycddg, cim.lbcim from dgn,cim
			where dgn.kycddg=cim.kycim and kytydg='C' and dgn.kyres='$nores'";
			
			$resultSuiteDas = ociparse($ConnSimpa, $sqlSuiteDas);
			ociexecute($resultSuiteDas);
				
			while(ocifetch($resultSuiteDas))
			{
				$diag = ociresult($resultSuiteDas, 1);
				$lbdiag = ociresult($resultSuiteDas, 2);
				$das=$das.$diag." : ".$lbdiag.";<BR> ";
			}
			$lesactes[$r]['das']	= $das;				
			$r++;
		}
		echo count($lesactes);
		echo "<table border='1'><tr> <td>NDA/resumé</td>   <td>date</td> <td>durée resumé</td> <td>DP</td>  <td>DR</td> <td>GHM</td>  <td>actes</td> <td>das</td></tr> ";
		// tableau affichage par mohan
		for ($i=0;$i<count($lesactes);$i++)
		{
			echo "<tr>";
			echo "<td>".$lesactes[$i]['noda']."<br />/" .$lesactes[$i]['nores']."</td>";
			echo "<td>".$lesactes[$i]['de']."<br />".$lesactes[$i]['df'] ."</td>";
			echo "<td>".$lesactes[$i]['dusej'] ."</td>";		
			echo "<td>".$lesactes[$i]['dp'] .":".$lesactes[$i]['lbdp']."</td>";
			echo "<td>".$lesactes[$i]['dr'] ."</td>";
			echo "<td>".$lesactes[$i]['ghm'].":".$lesactes[$i]['libghm'] ."</td>";
			echo "<td>".$lesactes[$i]['actes'] ."</td>";
			echo "<td>".$lesactes[$i]['das'] ."</td>";
			echo "</tr>";		
			
		}
		echo "</table>";
	}
	?>
	
	</body>
	</html>
<? ocilogoff($c1);?>


