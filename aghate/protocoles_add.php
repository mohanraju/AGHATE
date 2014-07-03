<?php 
/* Projet Aghate
 * Gestion protocole
 * 
 * 
 * 
 */ 
include "./commun/include/admin.inc.php";
include("./config/config.php");
require("./commun/include/ClassMysql.php");
include("./commun/include/ClassHtml.php");
include("./commun/include/CommonFonctions.php");
include("./commun/include/ClassAghate.php");


$com=new CommonFunctions(true);
$Html=new Html();
$Mysql = new MySQL();

if($Enregistrer=="Enregistrer")
{
	$actif=($actif==1)?$actif:'0';
	$sql="protocole  ='$protocole',
		 duree      ='$duree',
 		 actif	   ='$actif'";
 	// id_protocole=new send by lien	 
	if ($id_protocole !="NEW"){	
		$sql= "UPDATE agt_protocole set ".$sql. " where id_protocole ='$id_protocole'";
		$Mysql->insert($sql);		
	}else{
		$sql= "INSERT into agt_protocole set service='".$service_id."',".$sql;
		$id_protocole=$Mysql->update($sql);		
	}
	$msg="Donnée enregistré";
	echo "<script>window.opener.popUpClosed();</script>";
}
 
$sql="select * from agt_protocole where id_protocole='$id_protocole' order by actif desc, protocole";
$results=$Mysql->select($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Aghate :: Protocoles</title>
<link rel="stylesheet" href="./commun/style/bootstrap.min.css">
<link rel="stylesheet" href="./commun/style/bootstrap-theme.min.css">

<script type="text/javascript" src="./commun/js/jquery-1.10.2.js"></script>
<script src="./commun/js/bootstrap.min.js"></script> 
<style type="text/css">
    .bs-example{
    	margin: 30px;
    }
</style>
</head>

<body onunload="window.opener.popUpClosed();">
	<?php print "<h3>".$msg."</h3>";?>
<div class="bs-example">
	<form method="POST">
	  <fieldset>

			<div class="form-group">
				<label for="inputEmail">Protocole</label>
				<input type="text" class="form-control"  name="protocole"  value="<?php echo $results[0]['protocole']?>"  placeholder="Protocole">             
			</div>    
			<div class="form-group">
				<label for="inputEmail">Duree protocole(en minutes)</label>
				<input class="form-control" type="text" name="duree" size="30"  maxlength="4"  value="<?php echo $results[0]['duree'];?>">      
			</div>    
			
		<label class="checkbox">
		  <input type="checkbox" name="actif" value='1' checked> Active
		</label>
		<input type="submit" name="Enregistrer" value="Enregistrer">
		<input type="hidden" name="id_protocole" value="<?php echo $id_protocole?>">	
	  </fieldset>
	</form>    
</div>    

  </body>
</html>

 