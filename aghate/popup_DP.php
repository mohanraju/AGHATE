<?php include "./commun/include/admin.inc.php";
$grr_script_name = "titres.php";
$back = '';
	include ("./commun/include/CustomSql.inc.php");
	$db = new CustomSQL($DBName);

if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if ((authGetUserLevel(getUserName(),-1) < 3) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    //showAccessDenied($day, $month, $year, $area,$back);

}
# print the page header
simple_header("","","","",$type="with_session", $page="admin");
// Affichage de la colonne de gauche
?>

<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
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


$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);

if (isset($_GET["action_moderate"])) {
    // on modère
    moderate_entry_do($id,$_GET["moderate"],$_GET["description"]);
};
	if ($champ=="dp"){
		$sql = "SELECT code, description  FROM grr_top100 WHERE tag='DP' ORDER BY  code ";
		$titre="Diagnostique Principal";
	}	
	if ($champ=="dr"){
		$sql = "SELECT code, description  FROM grr_top100 WHERE tag='DR' ORDER BY  code ";
		$titre="Diagnostique Relié";
	}	
	if ($champ=="T_das"){
		$sql = "SELECT code, description  FROM grr_top100 WHERE tag='DAS' ORDER BY  code ";
		$titre="Diagnostique associés";
	}	
	if ($champ=="T_act"){
		$sql = "SELECT code, description  FROM grr_top100 WHERE tag='ACTES' ORDER BY  code ";
		$titre="Actes";
	}	
		
	$req = $db->select($sql);
//echo $sql;  
?>
<form name="test">

<table width="100%" border="1" cellspacing="1" cellpadding="1">
  <tr>
    <th width="31%" align="center">Liste <?php print $titre?></th>
  </tr>
 
  
  <?php 
  $res = grr_sql_query($sql);
  
  if ($res) {
	  for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
	  	$code=urldecode($row[0]); 
	  	$desc=urldecode($row[1]); 
	  	if(($i%2)==0)$bgcolor="";else $bgcolor="#EED7D2";

	    ?>
	  <tr class="initial"                       
			onMouseOver="this.className='highlight'"
			onMouseOut="this.className='normal'"    
			onClick="CheckSelection('<?php print  $code." ".mysql_real_escape_string(($desc))?>')" >	  
	    <td><?php print $code." ".$desc?></td>
      </tr>
	  <?php 
	}
	}
    ?>
</table>
	<input type="hidden" name="champ_du_owner" value="<?php print $champ; ?>">                 
</form>
<?php  
  
include_once("./commun/include/trailer.inc.php");
?>
