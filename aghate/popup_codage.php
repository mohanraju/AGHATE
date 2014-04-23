<?php
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);

?>


<script type="text/JavaScript">
function CheckSelection(retval){
	NomChamp =document.test.champ_du_owner.value;
  	if (retval==""){
		alert("Aucune valeur selectionnée!!!");
		return false;
	}
	if(NomChamp=="dp")	
		window.opener.document.pmsi.dp.value=retval;
	if(NomChamp=="dr")	
		window.opener.document.pmsi.dr.value=retval;
	if(NomChamp=="T_das")	
		window.opener.document.pmsi.T_das.value=retval;
	if(NomChamp=="T_act")	
		window.opener.document.pmsi.T_act.value=retval;
	window.close();
}
		
</script>
<?php
		$sql = "SELECT name,start_time,end_time,dp,dr,das,actes from agt_loc where id='".$id."'";
		
	$res = $db->select($sql);
//echo $sql;  
?>
<form name="test">

<table width="100%" border="1" cellspacing="1" cellpadding="1">
  <tr>
    <th width="31%" align="center">
    <?php 
    print $res[0]['name']." <br /> Sejour du ".
    date("d/m/Y",$res[0]['start_time']) ." à ".date("d/m/Y",$res[0]['end_time'])
    
    ?>
    </th>
  </tr>
  <tr>
    <td >DP: <?php print $res[0]['dp']?></d>
  </tr>
  <tr>
    <td >DR: <?php print $res[0]['dr']?></td>
  </tr>
  <tr>
    <td >DAS:- <br /> <?php print str_replace("@","<br />",$res[0]['das'])?></td>
  </tr>
  <tr>
    <td  >ACTES:- <br /> <?php print str_replace("@","<br />",$res[0]['actes'])?></td>
  </tr>
 
  
</table>
             
</form>
