<?php 
/*
############################################################################################
#	   codage PMSI                                                                           #
#                                                                                          #
#		Permet de afficher les codage faite dans AGHATE                                        #
#		creation  2009    
#                                                                     #
#		Modification 06/02/2013 :: ajouter le NDA                                              #
#		codage ,                                                                               #
#                                                                                          #
############################################################################################
pas d'authentification pour cet amodule
*/

	// incluisionde d'objet mysql 
	include "./commun/include/CustomSql.inc.php";
	$db = New CustomSQL($DBName);

	// vérification des dates
	if (strlen($date_deb)< 8) $date_deb=date("d/m/Y",mktime(00, 00, 00, date('m')-1, 01));
	if (strlen($date_fin)< 8) $date_fin=date("d/m/Y", mktime(00, 00, 00, date('m'), 00));
	if (strlen($orderby) < 4 )$orderby ="start_time";
	if (strlen($page) < 1 ){
		$page =0;
	}else{
		if(isset($suivante))$page++;
		if(isset($previous))$page--;
		if ($page < 0) $page=0;
	}
	if ($Rechercher=="Afficher")$page=0;
	
	$nbr_lignes=25;
	$from=$page * $nbr_lignes;

	//=----------------------------------------------
	// select services
	$area_select="	<select name='SERVICE' id='SERVICE'>";

	$sql	=	"select id, service_name from agt_service order by id";
	$area=$db->select($sql);
	
	for($i=0;$i < count($area);$i++){
		if ($area[$i]['id']==$SERVICE)
			$area_select .="<option value=".$area[$i]['id']." selected>".$area[$i]['service_name']."</option>";
		else
			$area_select .="<option value=".$area[$i]['id'].">".$area[$i]['service_name']."</option>";
	}
	//-----------------------------------------------
	//Selecet TYPE UH LISTE
	//-----------------------------------------------
	
	$uhtype_list="	<select name='UHTYPE' id='UHTYPE'>";
		if ("TOUS"==$UHTYPE) {
			$uhtype_list .="<option value='TOUS' selected>TOUS</option>";
			$sql_type = " ";
		}else{
			$uhtype_list .="<option value='TOUS' >TOUS</option>";
		}
	
	$sql	=	"select id, type_name,type_letter from agt_type_area order by type_name";
	$type=$db->select($sql);
	
	for($i=0;$i < count($type);$i++){
		if ($type[$i]['type_letter']==$UHTYPE){
			$uhtype_list .="<option value=".$type[$i]['type_letter']." selected>".$type[$i]['type_name']."</option>";
			$sql_type = " AND agt_loc.type='".$type[$i]['type_letter']."' ";
		}else{
			$uhtype_list .="<option value=".$type[$i]['type_letter'].">".$type[$i]['type_name']."</option>";
		}
	}
	
	
?>
<!--
=========================================================================================================
					partie HTML
=========================================================================================================
-->
<html>
<head>
<title>Exh Codage</title>
<style type="text/css">
<!--
 table {
	 border-top:1px solid #e5eff8;
	 border-right:1px solid #e5eff8;
	 border-collapse:collapse;
 }
 td {
	 border-bottom:1px solid #e5eff8;
	 border-left:1px solid #e5eff8;
	 padding:.1em 1em;

 }
.Style4 {color: #000000; font-style: italic; font-weight: bold; }
body,td,th {
	font-size: 11px;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.Style5 {
	font-size: x-large
}
 titre_col {
	border-bottom:1px solid #e5eff8;
	border-left:1px solid #e5eff8;
	padding:.1em 1em;
	background-color: #9CC;
	font-size: 10px;
	font-weight: bold;
 }

-->
</style>
<script language="javascript" type="text/javascript" src="./commun/js/JCalender.js"></script>
<script type="text/JavaScript">
 	function popup_syncro_Gilda(lien){
 		var dt= document.getElementById('date_deb')
 		var today="";
 		if (dt)
 			today=dt.value
 		
 		dates=today.split("/");
 		lien = lien +"?area=1&month="+dates[1]+"&year="+dates[2]+"&day="+dates[0];
		mywindow=open(lien,'myfind','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
		mywindow.location.href = lien;
		//  if (mywindow.opener == null) mywindow.opener = self;
		if(mywindow.window.focus){mywindow.window.focus();}    
	}		

	function majpmsi(id){
		if (confirm("Voulez vous confirmer ?")){
			retval = CallAjaxScript('./ajax_pmsi_maj_tim.php?param='+escape(id))
		}
	}
	
	function ShowIt(id) {
		mywindow=open('popup_codage.php?id='+id,'myname','resizable=yes,width=750,height=600,status=yes,scrollbars=yes');
	   mywindow.location.href = 'popup_codage.php?id='+id;
    	if (mywindow.opener == null) mywindow.opener = self;

	}
	
	function CallAjaxScript(fichier)
	{
		if(window.XMLHttpRequest) // FIREFOX
		xhr_object = new XMLHttpRequest();
		else if(window.ActiveXObject) // IE
		xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
		else
		return(false);
		xhr_object.open("GET", fichier, false);
		xhr_object.send(null);
		if(xhr_object.readyState == 4) return(xhr_object.responseText);
		else return(false);
	}
	
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body >
<!--
=========================================================================================================
				formaulaire d'input section
=========================================================================================================
-->

<form method="GET" action="<?php print $_SERVER['PHP_SELF']?>" >
<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="6" align="center"  ><p class="Style5">Codage activit&eacute;  dans  AGHATE</p>
   </tr> 
      <tr class="titre_col">
        <td height="32"  align="center" bgcolor="#00CCFF"><strong>Service</strong></td>
        <td  align="center" bgcolor="#00CCFF"><strong>Date sortie service</strong></td>
        <td   align="center" bgcolor="#00CCFF"><strong>Trier par</strong></td>
        <td  align="left" bgcolor="#00CCFF"><strong>Rechercher/Filtrer</strong></td>
        <td  align="left" bgcolor="#00CCFF"><strong>TYPE/UH</strong></td>        
        <td   align="center" bgcolor="#00CCFF"><strong>Afficher</strong></td>
        <td   align="left" bgcolor="#00CCFF">&nbsp;</td>
      </tr>
      <tr>
        <td  align="left" bgcolor="#FFFFCC"><?php print $area_select;?>                
                </td>
        <td  align="left" bgcolor="#FFFFCC">du:
          <input name="date_deb" type="text" id="date_deb" value="<?php print $date_deb?>" size="10" maxlength="10"/>
          <a href="javascript:PrepareCal('date_deb')"><img src="./commun/./commun/images/cal.gif" width="16" height="16" border="0" alt="Pick a date"></a>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <!-- Syncronisation avec Gilda -->
	      	<A href="#"  onClick="popup_syncro_Gilda('update_nda.php')" >
			                 	<img src="./commun/images/syncronisation.gif" width="20" height="20" border="0" alt="Synchronise Gilda" title="Synchronise avec GILDA de date_debut"/>  </A>          
          
          <br /> au:
          <input name="date_fin" type="text" id="date_fin" value="<?php print $date_fin?>" size="10" maxlength="10"/>
          <a href="javascript:PrepareCal('date_fin')"><img src="./commun/./commun/images/cal.gif" width="16" height="16" border="0" alt="Pick a date"></a></td>

        <td   align="left" bgcolor="#FFFFCC"><select name="orderby" id="orderby">
          <option value="start_time" <?php if($orderby=="start_time") echo "selected" ?> >Sejour</option>
          <option value="name" <?php if($orderby=="name") echo "selected" ?> >Patient</option>
          <option value="pmsi" <?php if($orderby=="pmsi") echo "selected" ?> >Saisie service</option>
          <option value="tim " <?php if($orderby=="tim") echo "selected" ?> >Saisie Tim</option>
        </select></td>
        <td  align="left" bgcolor="#FFFFCC"><input type="text" name="V_find" size="10" maxlength="25" value="<?Php   print $V_find?>" title="Chercher par nom/nip  dans la liste " ></td>

      
      <td   align="left" bgcolor="#FFFFCC"><?php echo $uhtype_list?> </td>
      
              
        <td   align="left" bgcolor="#FFFFCC"><input type="radio" name="filtre" id="tous" value="tous" <?php if($filtre=='tous') echo 'checked'; ?>>
          Tous 
          <br />
          <input name="filtre" type="radio" id="code" value="code" <?php if($filtre=='code') echo 'checked'; ?> >
        Non cod&eacute;s</td>
        
        <td   align="left" bgcolor="#FFFFCC"><input type="submit" name="Rechercher" id="button" value="Afficher" /></td>
      </tr>
    </table>      </td>
    </tr>

<?php
//===========================================================================================================
// notre stuff de recherche starts here
//==========================================================================================================  

list($d,$m,$y)=explode("/",$date_deb);
$start=mktime(0, 0, 0, $m, $d,$y);	   
list($d,$m,$y)=explode("/",$date_fin);
$end=mktime(23, 0, 0, $m, $d,$y);	  
$find_string="";
if (strlen(trim($V_find)) > 3){
	$find_string=" AND (agt_loc.name like ('%".trim($V_find)."%')  or agt_loc.nda like ('%".trim($V_find)."%'))";
}
if ($filtre=='code')
	$filtre_string="AND LENGTH(TIM) < 5";
else
	$filtre_string="";

// requette   
$sql="SELECT agt_loc.id,start_time,end_time,type,dp,dr,das,actes,pmsi,TIM,name,hds,nda 
					FROM agt_loc ,agt_room
					WHERE  agt_room.id=agt_loc.room_id
					AND agt_room.service_id in('".$SERVICE."') 
					and end_time > $start AND end_time < $end
					AND name not like('TEST TEST%')
					$sql_type
					$filtre_string
					$find_string
		 			ORDER BY ".$orderby;
		 			

$res=$db->select($sql);
$slno=1;

// boucle sur le resultat
for ($d=0;$d < count($res);$d++){
	$pat=explode("(",$res[$d]['name']);
	$name=$pat[0]."(".$pat[1];
	if($res[$d]['pmsi'])$pmsi= "Oui";else $pmsi= "Non";
	if (strlen($res[$d]['TIM']) > 5){
		$maj_tim=$res[$d]['TIM'];
	}else{
		$maj_tim="&nbsp;&nbsp;&nbsp;&nbsp;(Séjour codé dans SUSIE :<input type=\"checkbox\" name=\"valid_tim\" id=\"valid_tim\" onclick=\"majpmsi('".$res[$d]['id']."')\"/>)";
	}
	$link_pat="<a href=\"#\" onClick=\"ShowIt('".$res[$d]['id']."')\" > <B>".$name."</B></a>";
	if (strlen($res[$d]['dp']) > 3){
		$row_no=$from + $d +1;
		if (strlen($res[$d]['TIM']) > 5){
			$maj_tim="&nbsp;&nbsp;&nbsp;&nbsp;(Séjour codé dans SUSIE :Oui le ".($res[$d]['TIM']).")";
		}else{					
			$maj_tim="&nbsp;&nbsp;&nbsp;&nbsp;(Séjour codé dans SUSIE :<input type=\"checkbox\" name=\"valid_tim\" id=\"valid_tim\" onclick=\"majpmsi('".$res[$d]['id']."')\"/>)";
		}
		if (strlen($res[$d]['nda']) != 9) $res[$d]['nda']=" inconnu! ";
		Print "<table width='90%' border='1' cellspacing='1' cellpadding='1' align='center'>
			  <tr>
			    <td align='left'>$slno. ) 
			    <B>". $res[$d]['name']." NDA : ". $res[$d]['nda']."</B> 
			    Sejour du ".date("d/m/Y",$res[$d]['start_time']) ." à ".date("d/m/Y",$res[$d]['end_time']);
		Print $maj_tim;	    
    	print "</td>
			  </tr>
			  <tr>
			    <td><b>DP:</b> ".$res[$d]['dp'] ."</d>
			  </tr>
			  <tr>
			    <td><b>DR:</b>  ".$res[$d]['dr'] ."</td>
			  </tr>
			  <tr>
			    <td><b>DAS:-</b> <br /> ". str_replace("@","<br />",$res[$d]['das']) ."</td>
			  </tr>
			  <tr>
			    <td><b>ACTES:-</b>  <br /> ".str_replace("@","<br />",$res[$d]['actes'])."</td>
			  </tr>
			</table>	<hr>	";		
			$slno++;  	
	}
	  	

}
			
			if ($d==0)
			echo "<div align='center'>Aucun patient trouvée!!!</div>"
 

 
?>
</form>
</body>
</html>
