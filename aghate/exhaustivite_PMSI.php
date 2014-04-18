<?php  
	include ("./commun/include/CustomSql.inc.php");
	$db = New CustomSQL($DBName);


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
	
?>
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

-->
</style>
<script language="javascript" type="text/javascript" src="./commun/js/JCalender.js"></script>
<script type="text/JavaScript">
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
<form method="GET" action="<?php print $_SERVER['PHP_SELF']?>" >
  <table width="80%" border="1" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td colspan="6" align="center"  ><p class="Style5">Codage activit&eacute; PMSI  Chirurgie H&eacute;pato-Gastro-Ent&eacute;rologie - POLE DUNE</p>
    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td  align="left"><select name="orderby2" id="orderby2">
          <option value="start_time">GASTRO</option>
          <option value="name">HDJ HEMATO</option>
                </select></td>
        <td  align="left">Date de sortie du: </td>
        <td   align="left"><input name="date_deb" type="text" id="date_deb" value="<?php print $date_deb?>" size="10" maxlength="10"/>
         <a href="javascript:PrepareCal('date_deb')"><img src="./commun/./commun/images/cal.gif" width="16" height="16" border="0" alt="Pick a date"></a> 
          au:
          <input name="date_fin" type="text" id="date_fin" value="<?php print $date_fin?>" size="10" maxlength="10"/>
          <a href="javascript:PrepareCal('date_fin')"><img src="./commun/./commun/images/cal.gif" width="16" height="16" border="0" alt="Pick a date"></a>          </td>
        <td  align="left">Tri par</td>
        <td   align="left"><select name="orderby" id="orderby">
            <option value="start_time" >Sejour</option>
            <option value="name">Patient</option>
            <option value="pmsi">Saisie service</option>
            <option value="tim ">Saisie Tim</option>
        </select></td>
        <td   align="left"><input type="submit" name="Rechercher" id="button" value="Afficher" /></td>
      </tr>
    </table>      </td>
    </tr>
  
  <tr>
    <td bgcolor="#99CCFF" align="center" class="Style4"> N° </td>
    <td bgcolor="#99CCFF" align="center" class="Style4"> Patient </td>
    <td bgcolor="#99CCFF" align="center" class="Style4"> Type </td>    
    <td bgcolor="#99CCFF" align="center" class="Style4"> S&eacute;jour </td>
    <td bgcolor="#99CCFF" align="center" class="Style4"> Saisie sur Aghate</td>
    <td bgcolor="#99CCFF" align="center" class="Style4"> Saisie sur Susie    </td>
  </tr>


 
  <?php
	
//===========================================================================================================
// notre stuff de recherche starts here
//==========================================================================================================  

   list($d,$m,$y)=explode("/",$date_deb);
   $start=mktime(0, 0, 0, $m, $d,$y);	   
   list($d,$m,$y)=explode("/",$date_fin);
   $end=mktime(23, 0, 0, $m, $d,$y);	  
   
   
	$sql="SELECT agt_loc.id,start_time,end_time,type,dp,dr,das,actes,pmsi,tim,name,hds 
						FROM agt_loc ,agt_room
						WHERE  agt_room.id=agt_loc.room_id
						AND agt_room.service_id in('1','4') 
						and end_time > $start AND end_time < $end
						AND name not like('TEST TEST%')
						
			 			ORDER BY ".$orderby." limit ".$from.",".$nbr_lignes;

			$res=$db->select($sql);
			for ($d=0;$d < count($res);$d++){
				$pat=explode("(",$res[$d]['name']);
				$name=$pat[0]."(".$pat[1];
				if($res[$d]['pmsi'])$pmsi= "Oui";else $pmsi= "Non";
				if (strlen($res[$d]['tim']) > 5){
					$date_tim=$res[$d]['tim'];
				}else{
					$date_tim="<input type=\"checkbox\" name=\"valid_tim\" id=\"valid_tim\" onclick=\"majpmsi('".$res[$d]['id']."')\"/>";
				}
				$link_pat="<a href=\"#\" onClick=\"ShowIt('".$res[$d]['id']."')\" >".$name."</a>";
				
				$row_no=$from + $d +1;
				 Print " <tr>
				    <td>".$row_no."</td>				 
				    <td>".$link_pat."</td>
				    <td>".$res[$d]['hds']."</td>				    
				    <td>".date("d/m/Y",$res[$d]['start_time']) ." à ".date("d/m/Y",$res[$d]['end_time'])."</td>
				    <td>".$pmsi."</td>
				    <td>".$date_tim  ."</td>
				  	</tr>	";			
	
			}
 

 
echo "  <tr>";
echo "<td colspan=\"2\" align=\"left\">";
if($page==0) 
	echo "Début de lsite";
else
	echo "<input type=\"submit\" name=\"previous\" id=\"button\" value=\"Page précédente\" />";	
echo "</td>";	

$c_page=$page+1;
echo "<td   align=\"center\">Page ".$c_page."</td>";

echo "<td colspan=\"2\" align=\"right\">";
if ($d < $nbr_lignes){
	$page--;
	echo "Fin de liste";
}else{
	echo "<input type=\"submit\" name=\"suivante\" id=\"button\" value=\"Page suivante\" />";	
}
echo "</td>";	
?>
<input type="hidden" name="page" id="page" value="<?php print $page?>" /> </td>
 </table>
</form>
</body>
</html>
