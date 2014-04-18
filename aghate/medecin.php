<?php 
include "./commun/include/admin.inc.php";
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");

$com=new CommonFunctions(true);
$Html=new Html();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

 
$grr_script_name = "titres.php";
$back = '';

if ((authGetUserLevel(getUserName(),-1) < 3) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
 
if($t_mode=="MODIF")$tmode="MODIF";
if(!isset($tmode))$tmode="AFFICHE";

//------------------------------
// gestion enregistrement
//------------------------------
if (isset($Enregistrer)){
	// vérifications des données
	$err="";
	if (strlen($titre)==0)$err.=" titre  Vide !<br />";
	if (strlen($m_nom) < 2) 	$err.=" Nom Vide ! <br />";
	if (strlen($err) > 0 ){
	}else{

  		if($tmode=="Nouveau"){		
  				
			$sql="INSERT INTO agt_medecin (titre,nom,prenom,tel,email,service,service_id) values('$titre','$m_nom','$m_prenom','$tel','$email','$urm','$area')";
			//echo $sql;							
			$Aghate->insert($sql);
		}
    	if($tmode=="MODIF"){			
			$sql="UPDATE agt_medecin set 
			titre  ='$titre',
			nom      ='$m_nom',
			prenom   = '$m_prenom',
			tel 	 ='$tel',
			email ='$email'
			service_id ='$area'				
			where id_medecin ='$id_medecin'";
			$Aghate->insert($sql);
		}
		$tmode="AFFICHE";
	}
}else{
	// on initialise les données
	$titre="";
	$m_nom="";	
	$m_prenom="";	
	$tel="";
}
	
 
	$sql="select * from agt_medecin order by nom ";
	$results=$Aghate->select($sql);
 
# print the page header
print_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
include "admin_col_gauche.php";
	
?>
<script type="text/JavaScript">
function CheckSelection(retval)
{
	if (retval==""){
		alert("Aucune valeur selectionnée!!!");
		return false;
	}
	retval=retval.split("|");
	if (confirm("Voulez vous modifier ce médecin \n "+ retval[2]))
	{
		var tmode = document.getElementById('t_mode');							
		tmode.value="MODIF";
 		var prot = document.getElementById('id_medecin');
		prot.value=retval[0];
		document.form1.submit();
	}
	
}
</script>		
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<div align="center" style="overflow:auto;width:600px;" >
  <h2>M&eacute;decin</h2>
</div>

		<form name="form1" >	 
		 <table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
					 <thead class="fixedHeader">
		         	<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
		                 <th width='125'>Titre</th>
	                  <th width='150'>Nom</th>
	                  <th width='150'>Pr&eacute;nom</th>
	                  <th width='150'>T&eacute;l</th>
		             </tr>              
		          </thead>             
		</table>          
		<div style="overflow:auto;width:600px; height:150px; ">
		<table width="580" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
			<?Php   
							for($i=0; $i < count($results);$i++) {
								$id_prt=$results[$i]['id_medecin'];	
								$titre=$results[$i]['titre'];
								$m_nom=$results[$i]['nom'];						
								$m_prenom=($results[$i]['prenom']);
								$tel=($results[$i]['tel']);
								$retval="'".$id_prt."|".$titre."|".$m_nom."|".$m_prenom."|".$tel."'";
											
								Print "<tr class=\"initial\" 
											onMouseOver=\"this.className='highlight'\" 
											onMouseOut=\"this.className='normal'\" 
											onClick=\"CheckSelection(".$retval.")\">";
							
								Print "<td width='125'>".$results[$i]['titre']. " </td>";
								Print "<td width='150'>".$results[$i]['nom']. " </td>";
								Print "<td width='150'>".$m_prenom. " </td>";
								Print "<td width='150'>".$tel. " </td>";
								Print "</tr>";
								
		
						 }
						if ($i==0){
							Print "<tr><td colspan='6'>Aucune médecin trouvée </td></tr>";
						}
					  ?> 
		</table>
		</div >
				Cliquez sur la ligne &agrave; modifier<br />
		
<?php
if($Annuler=="Annuler")$tmode="AFFICHE";
if($t_mode=="MODIF")$tmode=$t_mode; 	

print '<div align ="center" style="overflow:auto;width:600px;  ">';
if((strcmp($tmode,"AFFICHE") == 0)) 
{
	echo '<input type="submit" name="tmode" value="Nouveau">';		
}
print '<input type="hidden" name="t_mode" id="t_mode" value="">';
print '<input type="hidden" name="id_medecin"  id="id_medecin"  value="<?php print $id_medecin?>">';
print '<input type="hidden" name="cur_mode"  id="cur_mode"  value="<?php print $tmode?>">';
print "</div>	";


//---------------------------------------------------
// affiche l'affichage l'err
//---------------------------------------------------
	if (strlen($err) > 0 ){
	echo "<br /><div color='red'>$err</div><br />";
	}	
//---------------------------------------------------
// Nouveau
//---------------------------------------------------		
if($tmode=="Nouveau"){
		$titre="";
		$m_nom="";	
		$m_prenom="";	
		$tel="";	  	
		?>
	  
		<table width="46%" border="0" id="datatabele">
		
		  <tr>
		    <td colspan="2" height="30" align="center"> <font size ="2" color="#000099"><b> 
		      Nouveau </b></font></td>
		</tr>
		  <tr> 
		    <td width="45%" align="right" class="textnoraml">Titre</td>
		    <td width="60%"><select name="titre" id="select">
		        <option value="Docteur">Docteur</option>
		        <option value="Professeur">Professeur</option>
		        </select></td>
		  </tr>
		  <tr> 
		    <td width="45%" align="right" class="textnoraml"> Nom</td>
		    <td width="60%"><input type="text" name="m_nom" size="30"   value="<?php echo $m_nom;?>" /></td>
		  </tr>
		  
		  <tr> 
		    <td width="45%" class="textnoraml" align="right">Pr&eacute;nom</td>
		    <td width="60%"> 
		      <input type="text" name="m_prenom" size="30"    value="<?php echo $m_prenom;?>"></td>
		  </tr>
		  <tr>
		    <td class="textnoraml" align="right"><p>T&eacute;l</p></td>
		    <td><input type="text" name="tel" size="30"     value="<?php echo $tel;?>" /></td>
		  </tr>
		  <tr>
		    <td class="textnoraml" align="right"><p>Email</p></td>
		    <td><input type="text" name="email" size="30"    value="<?php echo $email;?>" /></td>
		  </tr>
		  
		  <tr> 
		    <td width="45%"> 
		   </td>
		    <td width="60%"> 
		      <input type="submit" name="Enregistrer" value="Enregistrer">
		      <input type="submit" name="Annuler" value="Annuler">			        	        	        		        	        
		      <input type="hidden" name="tmode" value="Nouveau">	        
		  </tr>
		</table>

<?php } 
	//---------------------------------------------------
	// MODIFICATION
	//---------------------------------------------------
	 if($tmode=="MODIF"){
			$sql="select * from agt_medecin where id_medecin='$id_medecin'";
			$results=$Aghate->select($sql);
			
			$id_medecin=$results[0]['id_medecin'];	
			$titre=$results[0]['titre'];
			$m_nom=$results[0]['nom'];						
			$m_prenom=($results[0]['prenom']);
			$tel=($results[0]['tel']);
			$email=($results[0]['email']);


?>	
	
	  
	  <table width="46%" border="0">
	
	    <tr>
	      <td colspan="2" height="30" align="center"> <font size ="2" color="#000099"><b> 
	        Modifier </b></font></td>
	  </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml">Titre : </td>
	      <td width="60%"><select name="titre" id="titre">
            <option value="Docteur">Docteur</option>
            <option value="Professeur">Professeur</option>
          </select></td>
	    </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml"> Nom : </td>
	      <td width="60%"><input type="text" name="m_nom" size="30"  value="<?php echo $m_nom;?>" /></td>
	    </tr>
	    
	    <tr> 
	      <td width="45%" class="textnoraml" align="right">Pr&eacute;nom : </td>
	      <td width="60%"> 
	        <input type="text" name="m_prenom" size="30"     value="<?php echo $m_prenom;?>"></td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right">T&eacute;l : </td>
	      <td><input type="text" name="tel" size="30"   value="<?php echo $tel;?>" /></td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right">Email :   </td>
	      <td><input type="text" name="email" size="30"    value="<?php echo $email;?>" /></td>
	    </tr>
	    
	    <tr> 
	      <td width="45%"> 
	     </td>
	      <td width="60%"> 
	        <input type="submit" name="Enregistrer" value="ENREGISTRER">
	        <input type="hidden" name="tmode" value="MODIF">	        
	        <input type="submit" name="Annuler" value="Annuler">			        	        


	        
	    </tr>
	  </table>


<?php }?>
	</form>
