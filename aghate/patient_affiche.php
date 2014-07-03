<?php include "./commun/include/admin.inc.php";
echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");
?>
	<script type="text/JavaScript">
		// make upper case
		function upperCase(Idval){
			var x=document.getElementById(Idval).value
			document.getElementById(Idval).value=x.toUpperCase()
		}
		//retuen selected value
		function CheckSelection(retval){
			NomChamp =document.test.champ_du_owner.value;
		  	if (retval==""){
		   	alert("Aucune valeur selectionnée!!!");
		   	return false;
		   }
			retval=retval.split("|");
			window.opener.document.main.pat_id.value=retval[0];
			window.opener.document.main.name.value=retval[1];
			window.opener.document.main.type.value=retval[2];
			if(retval[2]=="F")
				window.opener.document.main.type.selectedIndex=2;			
			else
				window.opener.document.main.type.selectedIndex=1;			
			window.close();
		}
		
</script>
<title>Patients</title>
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
</head>
<body > 

		<?Php  
			include ("./commun/include/CustomSql.inc.php");
			$db = new CustomSQL($DBName);
			$showtable = true;
			$sql_main="SELECT * FROM agt_pat WHERE nom != 'null'";
			if(isset($Rechercher)) {
				if (isset($noip) && strlen($noip)> 0)			{$sql .=" AND noip like('$noip%')";}
				if (isset($T_nom) && strlen($T_nom)> 0)    		{$sql .=" AND nom like('$T_nom%')";	}
				if (isset($T_prenom) && strlen($T_prenom)> 0) 	{$sql .=" AND prenom like('$T_prenom%')";}
				if (isset($service) && strlen($noip)> 0)	   {$sqlt .=" AND service='$service'";	}
				if (isset($hospit) && strlen($hospit)> 0)	   {$sqlt .=" AND hospit='$hospit'";}
				if (isset($consult) && strlen($consult)> 0)	{$sqlt .=" AND noip='$consult'";	}

				if (strlen($sql) > 0){
					
					$sql= $sql_main. $sql ." ORDER BY nom";	
					$results=$db->select($sql);
					$showtable=true;
				}else{
					$errormsg="Aucune donnée (Numéro,Nom ou Prénom) renseigné";
				}
			}
		   if(isset($Annuler)) {
		   	$noip="";
		   	$T_nom="";
		   	$T_prenom="";
		   	
		   	}
			$id_pat=$_SESSION['id_pat'];
			$data=$db->GetPatInfo($id_pat);
		
		?>
      <div align="left" >
<form name="test">
		<table  border="0" cellspacing="1" cellpadding="1" align="center">
	       <tr bgcolor="#FFFFFF">
               <td   align="center"  bgcolor="#CCE4EC">Recherche d'indentit&eacute; des patients</td>
             </tr>
             <tr bgcolor="#FFFFFF">
               <td   class="table_top" align="center" ><table width="100%" border="1" cellspacing="1" cellpadding="1">
                 <tr>
                   <td align="center" bgcolor="#CCE4EC">NIP</td>
                   <td align="center" bgcolor="#CCE4EC"> Nom </td>
                   <td align="center" bgcolor="#CCE4EC"> Pr&eacute;nom </td>
                 </tr>
                 <tr>
                   <td><span class="label_form">
                     <input name="noip" type="text" class="textbox" value="<?Php   Print $noip?>" maxlength="10">
                   </span></td>
                   <td><input name="T_nom" type="text" class="textbox" onBlur="upperCase('nom')" value="<?Php   Print $T_nom;?>"></td>
                   <td><input name="T_prenom" type="text" class="textbox" onBlur="upperCase('prenom')" value="<?Php   Print $T_prenom;?>"></td>
                 </tr>
                 
               </table>
              <tr> <td  align="center" bgcolor="#CCE4EC">
                 <input class="form_bouton_bleu" type="submit" name="Rechercher" value="Rechercher">
                 <input class="form_bouton_bleu" type="submit" name="Annuler" value="Vider les champs" id="Annuler">
						<input type="hidden" name="champ_du_owner" value="<?php print $champ_du_owner; ?>">                 
              </td>
             </tr>
           </table>
		</form>           
		<p>
		  <?Php  
		 if ($errormsg){
				Print $errormsg;
			}
		?>
 </div  >
 
 <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
			 <thead class="fixedHeader">
         	<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
                 <th width='100'>NIP</th>
                 <th width='150'>Nom</th>
                 <th width='150'>Prénom</th>
                 <th width='100'>Date de naissance</th>
                 <th width='50'>Sexe</th>
                 <th width='150'>Nom JF</th>
              </tr>              
          </thead>             
</table>          
<div style="overflow:auto;width:700px; height:150px; ">
<table width="680" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">

				   <?Php   
					for($i=0; $i < count($results);$i++) {
						$id_pat=$results[$i]['id_pat'];	
						$noip=$results[$i]['noip'];
						$T_nom=$results[$i]['nom'];
						$T_prenom=$results[$i]['prenom'];
						$ddn=date_Mysql2Normal($results[$i]['ddn']);
						$sexe=IsEmpty($results[$i]['sex']);
						$retval="'".$id_pat."|".$T_nom." ".$T_prenom."-".$noip."-".$ddn."(" .$sexe.")|$sexe'";// attn "-" et "|" sont des seperators 
						$href_pat=IsEmpty($results[$i]['nom']);
						if (($i % 2)==0)
							$cl="normalRow";
						else
							$cl="alternateRow";											 
				 ?>                        

					<tr class="initial"                       
						onMouseOver="this.className='highlight'"
						onMouseOut="this.className='normal'"    
						onClick="CheckSelection(<?php print $retval?>)" >

                       <td width='100'><?Php print $noip;?></td>
				           <td width='150'><?Php  print $href_pat;?> </td>
				           <td width='150'><?Php   print IsEmpty($results[$i]['prenom']);?></td>
				           <td width='100'><?Php   print $ddn;?></td>
				           <td width='50'><?Php   print IsEmpty($results[$i]['sex']);?></td>
				           <td width='150'><?Php   print IsEmpty($results[$i]['nomjf']);?></td>
				   </tr>
				   <?Php     
				 }
				if ($i==0){
					Print "<tr><td colspan='6'>Aucune patient trouvée </td></tr>";
				}
			  ?> 
</table>
</div>
<div name="test" align="center" style="width:700px;"><a href="#" onclick="window.close()">Fermer</a></div>
</body></html>




