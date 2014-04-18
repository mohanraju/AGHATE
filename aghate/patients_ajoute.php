<?php 
include "./commun/include/admin.inc.php";
echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");

	function Calcul_age($ddn){
		list($day,$month,$year_ddn)=explode("/", $ddn);
		$year_today=date("Y");
		$age = $year_today - $year_ddn;
		return $age;
  }
	include ("./commun/include/CustomSql.inc.php");
	$showtable = false;
	$sql_ora="";
	$sql_local="";
	$db = new CustomSQL($DBName);
	//***********************************
	// annuler initialise
	//***********************************
	if(isset($Annuler)) {
		   	$noip="";
		   	$t_nom="";
		   	$t_prenom="";
	}	
	//***********************************
	// recherce dans GILDA
	//***********************************
	
	if(isset($Rechercher)) {
		if (strlen($noip)< 8 && strlen($t_nom)< 3 && strlen($t_prenom)< 3) {
				$errormsg="Aucune critère de recherche rempli !!!<br />
								Nip : minimum 7 chiffres <br />
								Nom ou Prénom : minimum 3 caractère   ";

		}else{  
		
			$fields_ora="SELECT distinct  CASE WHEN (PAT.NOIPFU > 111111111) then PAT.NOIPFU else PAT.NOIP end NOIP, NMMAL, NMPMAL, NMPATR, CDSEXM, TO_CHAR(DANAIS,'DD/MM/YYYY')  ,NOCOMA,NOTLDO from pat" ;
			$fields_local="SELECT * FROM agt_pat ";
			if (isset($noip) && strlen($noip)> 7){
		   	$sql_ora=" where noip like ('$noip"."%"."') ";			
				$sql_local=" where noip like ('$noip"."%"."') ";					   	
			}
			if (isset($t_nom) && strlen($t_nom)> 2){
				if (strlen($sql_ora)> 2 ){ 
					$sql_ora.=" and nmmal like ('".strtoupper($t_nom)."%') ";			
					$sql_local.=" and nom like ('".strtoupper($t_nom)."%') ";								
				}else{
		   		$sql_ora=" where nmmal like ('".strtoupper($t_nom)."%') ";			
					$sql_local=" where nom like ('".strtoupper($t_nom)."%') ";			
				}
			}
			if (isset($t_prenom) && strlen($t_prenom)> 2){
				if (strlen($sql_ora) > 2) {
					$sql_ora.=" and nmpmal like ('".strtoupper($t_prenom)."%') ";					   		
					$sql_local.=" and prenom like ('".strtoupper($t_prenom)."%') ";			
		   	}else{
		   		$sql_ora=" where nmpmal like ('".strtoupper($t_prenom)."%') ";				
					$sql_local=" where prenom like ('".strtoupper($t_prenom)."%') ";					   		   	
		   	}
		   		
			}
			//=============================================
			// Select patients from  gilda ou local
			//==============================================
			if($opt_find=="GILDA"){
				$sql_ora=$fields_ora.$sql_ora." Order by nmmal,nmpmal";
			      if (strlen($sql_ora) > 0){			
						require("./config/connexion_gilda.php");		
					   $showtable = true;
					   $result = ociparse($ConnGilda, $sql_ora);
						ociexecute($result);
						$count=0;
					   while(ocifetch($result))	{
					    	$data[$count]['noip']=ociresult($result, 1);
					    	$data[$count]['nom']=ociresult($result, 2);
					    	$data[$count]['prenom']=ociresult($result, 3);
					    	$data[$count]['njf']=ociresult($result, 4);
					    	$data[$count]['sex']=ociresult($result, 5);		    	
					    	$data[$count]['ddn']=ociresult($result, 6);
					    	$data[$count]['codepostal']=ociresult($result, 7);
					    	$data[$count]['tel']=ociresult($result, 8);					    	
					    	//$data[$count]['Ville']=ociresult($result, 7);
					    	//$data[$count]['codepostal']=ociresult($result, 7);
						   $count++;
					    } 			

						}
			}else{
						$sql_local= $fields_local. $sql_local ." ORDER BY nom";	
						$data=$db->select($sql_local);
						$showtable=true;
			}
		}
	} 
	//***********************************
	// Ajouter dans nontre base Mysql
	//***********************************
		if(isset($Ajouter_base) ) {
			foreach ($nipliste as $noip){
				$fields_ora=" NOIP, NMMAL, NMPMAL, NMPATR, TO_CHAR(DANAIS,'DD/MM/YYYY'),CDSEXM, NOCOMA ,NOCOMA " ;
				$fields_mysql="noip , nom , prenom , nomjf , ddn , sex , add , ville , codepostal "; 		   			
				$res=$db->CheckExistNoip($noip);
	        	if   ($res) {			
					// re recupare les donnée partir de gilda e inject dans le base mysql        	   
					require("./commun/include/_conn.php");	
					$sql_ora="SELECT distinct $fields_ora from pat where noip='$noip'";				
			   	$result = ociparse($ConnGilda, $sql_ora);
					ociexecute($result);
				   while(ocifetch($result))	{
						$noip=ociresult($result, 1);
						$t_nom=ociresult($result, 2);
						$t_prenom=ociresult($result, 3);
						$njf=ociresult($result, 4);
						$ddn=ociresult($result, 5);
						$sex=ociresult($result, 6);
						$codepostal=ociresult($result, 7);
						$tel=ociresult($result, 8);						
						$ville="''";
						$adresse="''";
						$age=Calcul_age($ddn);
				   //	$res=$db->AddPatients($noip , $t_nom , $t_prenom , $t_nomjf , $ddn , $sex , $adresse , $ville , $codepostal,$age);
  				   $errormsg .= "Patient ajouté : $noip <br />";
					} 			
				} else{
					$errormsg .= " DUPL  :  Patient existe déja : $noip <br />";
				}
			}
		}


?>

<title>Recherche Patients</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="style/style.css" type="text/css">
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
</head>
<script type="text/JavaScript">
	function upperCase(Idval){
		var x=document.getElementById(Idval).value
		document.getElementById(Idval).value=x.toUpperCase()
	}
	//retuen selected value
	function CheckSelection(retval){
		  	if (retval==""){
		   	alert("Aucune valeur selectionnée!!!");
		   	return false;
		   }
		   urm=document.getElementById('URM').value
		   //|$sexe|$noip|$t_nom|$t_prenom|$ddn|$t_njf'";
			retval=retval.split("|");
			window.opener.document.main.pat_id.value=retval[0];
			window.opener.document.main.name.value=retval[1];
			window.opener.document.main.type.value=retval[2];
			window.opener.document.main.sexe.value=retval[2];																					
			window.opener.document.main.noip.value=retval[3];			
			window.opener.document.main.nom.value=retval[4];			
			window.opener.document.main.prenom.value=retval[5];			
			window.opener.document.main.ddn.value=retval[6];												
			window.opener.document.main.njf.value=retval[7];
			window.opener.document.main.tel.value=retval[8];							
			if(urm=='470'){ 
				if(retval[2]=="F")
					window.opener.document.main.type.selectedIndex=2;			
				else
					window.opener.document.main.type.selectedIndex=1;			
			}
			window.close();
		}
		function focus(){
			document.getElementById('noip').focus();
		}		
</script>
<body bgcolor="#FFFFFF" text="#000000"  onLoad="focus()">


<form>

	<div style="overflow:auto;width:700px;  ">
    <h2 align="center"> Recherche des patients</h2>
		  <table width="350" border="0" cellspacing="0" cellpadding="0" align="center">
		    <?Php  if ($errormsg){
					Print "<tr><td  colspan='2'><font color='#FF0000'> $errormsg </font></td></tr>" ;
				}
			?>
		    
		    <tr> 
		      <td width="39%" align="left"> NIP :  </td>
		      <td width="57%"><input type="text" name="noip" id="noip" maxlength="10" value="<?Php  Print $noip?>">      </td>
		    </tr>
		    <tr> 
		      <td  align="left">Nom : </td>
		      <td ><input type="text" name="t_nom" id="t_nom" value="<?Php  Print $t_nom?>" onBlur="upperCase('t_nom')">      </td>
		    </tr>
		    <tr>
		      <td align="left">Prénom :</td>
		      <td  ><input type="text" name="t_prenom"  id="t_prenom"  value="<?Php  Print $t_prenom?>" onBlur="upperCase('t_prenom')"></td>
	        </tr>
		    <tr> 
		      <td align="left">R&eacute;cherche dans</td>
		      <?php 	$selelt_gilda="";
		      			$selelt_local="";
		      		if($opt_find=="LOCAL")
		      			$selelt_local="checked";
		      		else
		      			$selelt_gilda="checked";
		       ?>
		      <td><input name="opt_find" type="radio" id="LOCAL" value="LOCAL"  <?php print $selelt_local;?> >
		      Local 
	              &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="opt_find" id="LOCAL" value="GILDA" <?php print $selelt_gilda;?> >Gilda	 </td>
		    </tr>
		    <tr> 
		      <td  colspan="2" align="center"> 
			          <input name="Rechercher" type="submit" class="form_bouton_bleu" value="Rechercher">      
		              <input class="form_bouton_bleu" type="submit" name="Annuler" value="Vider les champs" id="Annuler">		     </td>
		    </tr>
		  </table>
  </div>
  
<?Php
  if($showtable) {?>
		<table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
			<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
                 <th width='75'>NIP</th>
                 <th width='150'>Nom</th>
                 <th width='150'>Prénom</th>
                 <th width='100'>Date de naissance</th>
                 <th width='30'>Sexe</th>
                 <th width='150'>Nom JF</th>
                 <th width='45'>tel</th>                 
              </tr>              
		</table>          
		<div style="overflow:auto;width:700px; height:150px; ">
		<table width="680" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
		  <?Php  
		  for($i=0;$i < count($data);$i++){  		
		  		$noip=$data[$i]['noip'];    			    			    			    			    		
				$t_nom=$data[$i]['nom'];
				$t_prenom=$data[$i]['prenom'];
				$t_njf=$data[$i]['njf'];
				$t_tel=$data[$i]['tel'];				
				if($opt_find=="GILDA")
					$ddn=$data[$i]['ddn'];
				else
					$ddn=date_Mysql2Normal($data[$i]['ddn']);
				$sexe=IsEmpty($data[$i]['sex']);
				$retval="'".$id_pat."|".$t_nom." ".$t_prenom." (".$noip.") (".$ddn.") (" .$sexe.") (tel:".$t_tel.")|$sexe|$noip|$t_nom|$t_prenom|$ddn|$t_njf|$t_tel'";
				
		  	 ?>
					<tr class="initial"                       
								onMouseOver="this.className='highlight'"
								onMouseOut="this.className='normal'"    
								onClick="CheckSelection(<?php print $retval?>)" >
				    <td width='75'> <?Php  Print $noip;?> </td>
				    <td width='150'> <?Php  Print $data[$i]['nom'];?></td>
				    <td width='150'> <?Php  Print $data[$i]['prenom'];?></td>
				    <td width='100'> <?Php  Print $ddn;?></td>
				    <td width='30'> <?Php  Print $data[$i]['sex'];?></td>
				    <td width='150'> <?Php  Print IsEmpty($t_njf);?></td>
				    <td width='45'> <?Php  Print IsEmpty($t_tel);?></td>				    
				  </tr>
		  <?Php    }?>

		</table>
</div>
<div style="overflow:auto;width:700px;  ">
		  		<input name="URM" id="URM" type="hidden"  value="<?php print $_SESSION["URM"]?>"> 
		  		<div  align="center" ><input name="fermer" type="submit" class="form_bouton_bleu" value="Fermer"></div>

	</div>		
	<?Php  }?>
</form>
</body>
</html>
