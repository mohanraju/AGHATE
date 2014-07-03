<?php
/*
#########################################################################################
		ProjetMSI
		Module Resa
		Recherche Patient
		Auther Celeste Thierry @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 15/01/2014
*/
//commun include pour les modules outil MSI
 
//-------------------------------------------------------------------------
// 		Vérifiction du site declared dans le session 
//		par prapport le connexion utilisateur
//-------------------------------------------------------------------------
 
//=================================================================================--------
// script s d'inclusion
//=================================================================================--------

include("../../config/config.php");
  
require("../include/CommonFonctions.php");
include("../include/ClassGilda.php");

//Objet init
$Functions = new CommonFunctions(true);
$Gilda=new Gilda($ConnexionStringGILDA);

// 	preparation de requettes
//	===================================================================

$val_rech=strtoupper(trim($val_rech));
$LESCHAMPS="PAT.NOIP as NIP,PAT.NMMAL as NOM,PAT.NMPMAL as PRENOM,to_char(PAT.DANAIS,'DD/MM/YYYY') as DANAIS,PAT.CDSEXM as SEXE,NOTLDO";
// 	si au moins 3 char de nom renseingné
if(strlen($val_rech) > 3)
{
	if($Functions->IsNumber($val_rech))
	{
		//Taille 9 numeric=NDA
		if(strlen($val_rech)==9){
			$SQL= " SELECT $LESCHAMPS FROM PAT,DOS WHERE DOS.NOIP=PAT.NOIP AND DOS.NODA ='".trim($val_rech)."' ";	
			$desc_rech="NDA";
		//Taille 10 numeric=NIP
		}else if(strlen($val_rech)==10)
		{
			$desc_rech="NIP";
			$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NOIP =('".trim($val_rech)."') ";	
		}else{ 
			$desc_rech="NOM";
			$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".strtoupper(trim($val_rech))."%') ORDER BY NMMAL,NMPMAL";	
		}
	}
	else
	{
		// non numeric est NOM
		$desc_rech="NOM";
		$SQL=" SELECT $LESCHAMPS FROM PAT WHERE PAT.NMMAL LIKE('".strtoupper(trim($val_rech))."%') ORDER BY NMMAL,NMPMAL";	
	}
}

if (strlen($SQL) > 1)
{
	$Result=$Gilda->OraSelect($SQL);
}

$nbr_rec=count($Result);

echo '<table id="TblPat" width="100%" border="1" cellspacing="0" cellpadding="0" align="left" class="table table-condensed Patients" >';
if ($nbr_rec > 0){

?> 
	  <tr>
	    <th width='100px'>NIP</th>
	    <th width='100px'>NOM</th>
	    <th width='100px'>PRENOM</th>
	    <th width='100px'>DDN</th>
	    <th width='100px'>SEXE</th>    
	  </tr> 
<tbody>
<?php 
	for($n=0; $n < $nbr_rec;$n++)
	{
		$MyVar=$Result[$n]["NOM"]." ".$Result[$n]["PRENOM"]." (".$Result[$n]["NIP"].") (".$Result[$n]["DANAIS"].") (" .$Result[$n]["SEXE"].") (tel:".$Result[$n]["NOTLDO"].")";
		// print table Header
	  Print '	
		  <tr id="ROWID"	name="ROWID'.$i.'"  MyVar="'.$MyVar.'"	>
		    <td>'.$Result[$n]["NIP"].'</td>
		    <td>'.$Result[$n]["NOM"].'</td>
		    <td>'.$Result[$n]["PRENOM"].'</td>
		    <td>'.$Result[$n]["DANAIS"].'</td>
		    <td>'.($Result[$n]["SEXE"]=="M"?"Male":"Female").'</td>
		  </tr>';
	} 
} 
else
{
	echo "<tr><td colspan=5>[".$desc_rech." = ". $val_rech."] Aucun patient n'a trouvé </td></tr>";	
}

?>
</tbody>
</table>
</div>
</body>
</html>
