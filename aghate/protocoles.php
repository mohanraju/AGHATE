<?php 
include "./commun/include/admin.inc.php";
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");
include("./config/config_".$site.".php");
//include("../commun/layout/header.php");

$com=new CommonFunctions(true);
$Html=new Html();
$mysql = new MySQL();

$grr_script_name = "protocoles.php";
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
	
	if (isset($Enregistrer)){
		// vérifications des données
		$err="";
		if (strlen($protocole)==0)$err.=" Protocole  Vide !<br />";
		if (strlen($duree) < 2) 	$err.=" Durée invalide... <br />";
		//if (!isdate($date_deb))		$err.=" Date Debut invalid " .$date_deb ."<br />";
		//if (!isdate($date_fin))  $err.=" Date Fin invalide " .$date_fin ."<br />";
		if (strlen($err) > 0 ){
		}else{

    		if($tmode=="Nouveau"){			
				$date_deb=$com->Normal2Mysql($date_deb);
				$date_fin=$com->Normal2Mysql($date_fin);
				$sql="INSERT INTO agt_protocole (protocole,duree,service,date_deb,date_fin,actif) values('$protocole','$duree','$urm','$date_deb','$date_fin','$actif')";
				//echo $sql;							
				$mysql->insert($sql);
				header("Location: protocoles.php");
			}
	    	if($tmode=="MODIF"){			
				$date_deb=$com->Normal2Mysql($date_deb);
				$date_fin=$com->Normal2Mysql($date_fin);
				$sql="UPDATE agt_protocole set 
				protocole  ='$protocole',
				duree      ='$duree',
				date_deb   = '$date_deb',
				date_fin 	 ='$date_fin',
				actif	   ='$actif'
				where id_protocole ='$id_protocole'";
				$mysql->update($sql);
				header("Location: protocoles.php");
			}
			$tmode="AFFICHE";
		}
	}else{
		// on initialise les données
		$protocole="";
		$duree="";	
		$date_deb="2000-01-01";	
		$y=date("Y")+1;
		$date_fin="31/01/".$y;
	}
		
	# print the page header
	print_header("","","","",$type="with_session", $page="admin");
	// Affichage de la colonne de gauche
	include "admin_col_gauche.php";
	?>
	
	<script type="text/JavaScript">
		function CheckSelection(retval){
		  	if (retval==""){
		   	alert("Aucune valeur selectionnée!!!");
		   	return false;
		   }
			retval=retval.split("|");
			if (confirm("Voulez vous modifier ce protocole \n "+ retval[1])){
				var tmode = document.getElementById('t_mode');							
				tmode.value="MODIF";
	   		var prot = document.getElementById('id_protocole');
				prot.value=retval[0];
				document.form1.submit();
			}
			
		}
	</script>		
	<script type="text/javascript" src="./commun/js/fonctions_aghate.js"></script>
	<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
	<div align="center" style="overflow:auto;width:600px;" ><h2>Protocoles</h2></div>
	<?php 

	$sql="select * from agt_protocole where service='$urm' order by actif desc, protocole";
	$results=$mysql->select($sql);
	?>
		<form name="form1" >	 
		 <table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
					 <thead class="fixedHeader">
		         	<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
		                 <th width='180'>Protocole</th>
		                 <th width='50'>Dur&eacute;e (minutes)</th>
		                 <th width='75'>D&eacute;but de validit&eacute;</th>
	               		 <th width='95'>Fin de validit&eacute;</th>
	           			 <th width='50'>Validité</th>
	           			 <th width='150'>Supprimer</th>
		             </tr>              
		          </thead>             
		</table>          
		<div style="overflow:auto;width:600px; height:150px; ">
		<table width="580" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
			<?Php   
							for($i=0; $i < count($results);$i++) {
								$id_prt=$results[$i]['id_protocole'];	
								$protocole=$results[$i]['protocole'];
								$duree=$results[$i]['duree'];						
								$date_deb=$com->Mysql2Normal($results[$i]['date_deb']);
								$date_fin=$com->Mysql2Normal($results[$i]['date_fin']);
								$actif=$results[$i]['actif'];
								$retval="'".$id_prt."|".str_replace("'","\'",$protocole)."|".$duree."|".$date_deb."|".$date_fin."'";
								Print "<tr class=\"initial\" 
											onMouseOver=\"this.className='highlight'\" 
											onMouseOut=\"this.className='normal'\" >";
							
								Print "<td width='180' onClick=\"CheckSelection(".$retval.")\">".$results[$i]['protocole']. " </td>";
								Print "<td width='75' onClick=\"CheckSelection(".$retval.")\">".$results[$i]['duree']. " </td>";
								Print "<td width='75' onClick=\"CheckSelection(".$retval.")\">".$date_deb. " </td>";
								Print "<td width='95' onClick=\"CheckSelection(".$retval.")\">".$date_fin. " </td>";
								Print "<td width='50' onClick=\"CheckSelection(".$retval.")\">".$actif."</td>";
								Print "<td width='150'> <input id=\"Supprimer_".$i."\" type=\"button\" onclick=\"DelProtocole(".$id_prt.");\" value=\"Supprimer\" name=\"Supprimer\"> </td>";
								Print "</tr>";
								
		
						 }
						if ($i==0){
							Print "<tr><td colspan='6'>Aucune Protocle trouvée </td></tr>";
						}
					  ?> 
		</table>
		</div >
				<br />
		<div align ="center" style="overflow:auto;width:600px;  ">
 <?php
 	if($Annuler=="Annuler")$tmode="AFFICHE";
 	if($t_mode=="MODIF")$tmode=$t_mode; 	
   if((strcmp($tmode,"AFFICHE") == 0)) { ?>		
	        <input type="submit" name="tmode" value="Nouveau">		
    <?php }?>    
	        <input type="hidden" name="t_mode" id="t_mode" value="">			        
			<input type="hidden" name="id_protocole"  id="id_protocole"  value="<?php print $id_protocole?>">			 
	        <input type="hidden" name="cur_mode"  id="cur_mode"  value="<?php print $tmode?>">	        	        			  
		</div>	
 	
<?php 

	//---------------------------------------------------
	// affiche l'affichage l'erreur
	//---------------------------------------------------
		if (strlen($err) > 0 ){
		echo "<br /><div color='red'>$err</div><br />";
		}	
	//---------------------------------------------------
	// Nouveau
	//---------------------------------------------------		
  if($tmode=="Nouveau"){
			$protocole="";
			$duree="";	
			$date_deb="01/01/2000";	
			$y=date("Y")+1;
			$date_fin="31/01/".$y;	  	
  	?>
	  
	  <table width="46%" border="0">
	
	    <tr>
	      <td colspan="2" height="30" align="center"> <font size ="2" color="#000099"><b> 
	        Nouveau Protocole</b></font></td>
	  </tr>
	  <tr>
	  		<td width="45%" align="right" class="textnoraml"> Actif</td>
	  		<td width="60%"><select name="actif" id="actif">
            	<option value="o">oui</option>
           		 <option value="n">non</option>
         	 </select></td>
       </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml">Protocole</td>
	      <td width="60%"> 
	        <input type="text" name="protocole" size="35" maxlength="75"  value="<?php echo $protocole?>" >      </td>
	    </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml"> Description</td>
	      <td width="60%"> 
	        <textarea name="description" cols="30" rows="3" Onblur="return cUpper(this)" ><?php echo $description?></textarea>      </td>
	    </tr>
	    
	    <tr> 
	      <td width="45%" class="textnoraml" align="right">Dur&eacute;e estim&eacute;e</td>
	      <td width="60%"> 
	        <input type="text" name="duree" size="30"  maxlength="4"  value="<?php echo $duree;?>"> 
	        en minutes      </td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right"><p>D&eacute;but de validit&eacute;</p></td>
	      <td><input type="text" name="date_deb" size="30"  maxlength="10"  value="<?php echo $date_deb;?>" />
	        JJ/MM/YYYY</td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right"><p>Fin de validit&eacute;</p></td>
	      <td><input type="text" name="date_fin" size="30"  maxlength="10"   value="<?php echo $date_fin;?>" />
	      JJ/MM/YYYY </td>
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
			$sql="select * from agt_protocole where id_protocole='$id_protocole'";
			$results=$mysql->select($sql);
			
			$actif=$results[0]['actif'];	
			$id_protocole=$results[0]['id_protocole'];	
			$protocole=$results[0]['protocole'];
			$duree=$results[0]['duree'];						
			$date_deb=$com->Mysql2Normal($results[0]['date_deb']);
			$date_fin=$com->Mysql2Normal($results[0]['date_fin']);


?>	
	
	  
	  <table width="46%" border="0">
	
	    <tr>
	      <td colspan="2" height="30" align="center"> <font size ="2" color="#000099"><b> 
	        Modifier un  Protocole</b></font></td>
	    </tr>
	    <tr>
	  		<td width="45%" align="right" class="textnoraml"> Actif</td>
	  		<td width="60%"><select name="actif" id="actif">
            	<option value="o">oui</option>
           		<option value="n">non</option>
         	</select></td>
       </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml">Protocole</td>
	      <td width="60%"> 
	        <input type="text" name="protocole" size="35" maxlength="75"  value="<?php echo $protocole?>" >      </td>
	    </tr>
	    <tr> 
	      <td width="45%" align="right" class="textnoraml"> Description</td>
	      <td width="60%"> 
	        <textarea name="description" cols="30" rows="3" Onblur="return cUpper(this)" ><?php echo $description?></textarea>      </td>
	    </tr>
	    
	    <tr> 
	      <td width="45%" class="textnoraml" align="right">Dur&eacute;&eacute; estim&eacute;e</td>
	      <td width="60%"> 
	        <input type="text" name="duree" size="30"  maxlength="4"  value="<?php echo $duree;?>"> 
	        en minutes      </td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right">Validi&eacute; de </td>
	      <td><input type="text" name="date_deb" size="30"  maxlength="10"  value="<?php echo $date_deb;?>" />
	        JJ/MM/YYYY</td>
	    </tr>
	    <tr>
	      <td class="textnoraml" align="right">Validit&eacute; jusqu au  </td>
	      <td><input type="text" name="date_fin" size="30"  maxlength="10"   value="<?php echo $date_fin;?>" />
	      JJ/MM/YYYY </td>
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
