<?php
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);


	$sql="SELECT agt_loc.id,name 
						FROM agt_loc 
						WHERE  agt_loc.nda is null ;



	$actes=array();

	// recherche by nip trop long donc on r袵pare le nda d'abord!!!
	$sql_nip="select rsm.noip,rsm.noda,D8EEUE,D8SOUE  from rsm where rsm.noip='$noip' and rsm.cdurm='$service' order by D8SOUE DESC";
	require("./config/connexion_simpa.php");		
	$result = ociparse($ConnSimpa, $sql_nip);
		$nda="";
		ociexecute($result);
		$rw=0;
		while(ocifetch($result)){
			$nda.=ociresult($result, 2).",";
			if ($rw > 4 ) break;
			$rw++;
		}
	   if (strlen($nda) < 1){
	   	echo "Aucun donnée  trouvée pour ce patient !!!";
	   	Exit;
	   }else{
		   $nda=substr($nda,0,strlen($nda)-1);	
   	}
		//REQUETE
		
		$sql="select NIP, NDA,kyres,DE, DF, DP, DR, GHM, LBDP, dusej,LBGHM FROM
				(
				select distinct(rsm.noda) as nda,rsm.noip as NIP, rsm.kyres as kyres, 
						TO_char(d8deb,'DD/MM/YYYY') as DE,TO_char(d8fin,'DD/MM/YYYY') as DF, 
						rsm.CDDNC9 as DP, rsm.CDRELIE as DR, '' as DAS, 
						rss.CDGHM as GHM, LBCIM as LBDP,dusej as dusej, ghm.lbghm as LBGHM
				from RUM,RSS,RSM, CIM,GHM
					where RUM.NORES = RSM.KYRES
					AND RUM.KYRSS = RSS.KYRSS
					AND CIM.KYCIM = rsm.CDDNC9
					AND GHM.KYGHM= rss.CDGHM
					and rsm.cdurm in ('$service')
					and rsm.noda in(".$nda.") 
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
			$respmsi[$r]['nip']		= ociresult($result, 1);
			$respmsi[$r]['nda']		= ociresult($result, 2);
			$respmsi[$r]['de']		= ociresult($result, 4);
			$respmsi[$r]['df']		= ociresult($result, 5);
			$respmsi[$r]['dp']		= ociresult($result, 6) . " " .$respmsi[$r]['lbdp']		= ociresult($result, 9);
			$respmsi[$r]['dr']		= ociresult($result, 7);
			$respmsi[$r]['ghm']		= ociresult($result, 8);
			$respmsi[$r]['dusej']	= ociresult($result, 10);
			$respmsi[$r]['libghm']	= ociresult($result, 11);
		
			$nores=ociresult($result, 3);
			//---------------------------
			/* Lib DR*/
			//---------------------------
			$sqlDr="select LBCIM from cim   where CIM.KYCIM ='".$respmsi[$r]['dr']."' AND cim.tycim='10'";
			$resultDr = ociparse($ConnSimpa, $sqlDr);
			ociexecute($resultDr);
			while(ocifetch($resultDr)){
				$lbdr = ociresult($resultDr, 1);
			}
			$respmsi[$r]['dr'] .= " " .$lbdr;
			
			
			//---------------------------
			/*pour actes*/
			//---------------------------
			$sqlActes="select distinct rac.kycdac,cda.lbcda from rac,cda 
			where rac.kycdac=cda.kycda and verclas='2' and rac.kyres='$nores'";
			$resultActes = ociparse($ConnSimpa, $sqlActes);
			ociexecute($resultActes);
			while(ocifetch($resultActes))
			{
				$respmsi[$r]['actes']	.= ociresult($resultActes, 1)." : ".ociresult($resultActes, 2)."@";			
			}
			//---------------------------
			/*pour DAS*/
			//---------------------------
			$sqlDas="select dgn.kycddg, cim.lbcim from dgn,cim
			where dgn.kycddg=cim.kycim and kytydg='C' and dgn.kyres='$nores' and cim.tycim='10'";
			$resultDas = ociparse($ConnSimpa, $sqlDas);
			ociexecute($resultDas);
			while(ocifetch($resultDas))
			{
				$respmsi[$r]['das']	.= ociresult($resultDas, 1)." : ".ociresult($resultDas, 2)."@";							
			}
			$r++;
		}

 ocilogoff($ConnSimpa);
?> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="fr"> 
  <head> 
    <meta http-equiv="content-type" content="text/xhtml; charset=utf-8" /> 
    <title>donnees PMSI</title> 
<script type="text/javascript">
 var Datalist = new Array(); 
 <?php
  for($i=0 ;$i < count($respmsi) ;$i++){
  		$res="\"".htmlspecialchars($respmsi[$i]['dp'])."|";
 		$res.=htmlspecialchars($respmsi[$i]['dr'])."|";
 		$res.=htmlspecialchars($respmsi[$i]['das'])."|";
 		$res.=htmlspecialchars($respmsi[$i]['actes'])."\""; 		  		
 		
 	echo "Datalist[\"".$respmsi[$i]['nda']."\"] = [".($res)."];";
 	}
 ?> 	  

   
 function GetDataNDA(selectObj) { 
  
 		var idx = selectObj.selectedIndex; 
 		var which = selectObj.options[idx].value; 
  	cList = Datalist[which]; 
 		var res="";
 		for (var i=0; i<cList.length; i++) { 
 			res+=cList[i] ; 
 		}
 		
		exp= /@/g // régular expressions
 		res=res.replace(exp, "\n"); 
 		all_res=res.split("|");
 		document.getElementById("dp").innerHTML=all_res[0]; 
 		document.getElementById("dr").innerHTML=all_res[1]; 
 		document.getElementById("das").value=all_res[2]; 
 		document.getElementById("actes").value=all_res[3]; 
 		
 	
 
 } 
</script>
<style type="text/css">
<!--
.Styletitre {
	color: #0000CC;
	font-weight: bold;
	background-color:#FFFFCC;
	border:thin

}
.Styledata {
	background-color:#FFFFCC;
	border:thin;
	border-bottom-color:#FF0000	
	
}


-->
</style>
</head>

<body bgcolor="#E0F8E0">
  
  <h1>Codage des anciennes hospitalisations</h1>
	<h3>NIP : <?php print $noip;?></h3> 
	<h3>Les séjours</h3> 	
  <select id="datapmsi" onChange="GetDataNDA(this);" size="5">
    <?php
    for($i=0 ;$i < count($respmsi) ;$i++){
		Print "<option value=".$respmsi[$i]['nda']."> NDA : ".$respmsi[$i]['nda']. " (Du ".$respmsi[$i]['de']. " au ".$respmsi[$i]['df'] .")</option>";
 	}
  ?>
  </select>
    
  <br />
    
    
</p>

  <div style="width:725; height:325; font-size:9px;  ">
<table border="0" cellspacing="0" cellpadding="0">
    <tr >
      <td  class="Styletitre"><span >Diagnostique Principale</span></td>
    </tr>
    <tr>
       <td  class="Styledata">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="dp"></span> </td>
    </tr>
    <tr>
      <td class="Styletitre">Diagnostique relié</td>
    </tr>
    <tr>
      <td  class="Styledata">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="dr"></span>         </td>
    </tr>
    <tr>
      <td class="Styletitre">Diagnostique assiciaée</td>
    </tr>
    <tr>
      <td  class="Styledata"><textarea name="das" cols="80" rows="3" id="das"></textarea></td>
    </tr>
    <tr>
      <td class="Styletitre">Actes</td>
    </tr>
    <tr>
      <td  class="Styledata"><textarea name="actes" cols="80" rows="5" id="actes"></textarea></td>
    </tr>

  </table>
  <input type="button" value="close" onclick="window.close()">
  
 </div>  
  <p><br />
    </p>
</BODY>


</HTML>



