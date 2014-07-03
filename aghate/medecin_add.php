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
	$etat=($etat==1)?$etat:'0';
	$sql="titre  ='$titre',
		 nom      ='$nom',
		 prenom      ='$prenom',
		 tel      ='$tel',
		 specialite      ='$specialite',		 		 		 
		 email      ='$email',		 		 		 
 		 etat	   ='$etat'";
 	// id_medecin=new send by lien	 
	if ($id_medecin !="NEW"){	
		$sql= "UPDATE agt_medecin set ".$sql. " where id_medecin ='$id_medecin'";
		$Mysql->insert($sql);		
	}else{
		$sql= "INSERT into agt_medecin set service_id='".$service_id."',".$sql;
		$id_medecin=$Mysql->update($sql);		
	}
	$msg="Donnée enregistré";
	echo "<script>window.opener.popUpClosed();</script>";
}

$sql="select * from agt_medecin where id_medecin='$id_medecin' ";
$res=$Mysql->select($sql);

$ListeTitre[]="Dr|Dr";
$ListeTitre[]="Pr|Pr";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Aghate :: Medecins</title>
<link rel="stylesheet" href="./commun/style/bootstrap.css">
<link rel="stylesheet" href="./commun/style/bootstrap-theme.css">

<script type="text/javascript" src="./commun/js/jquery-1.10.2.js"></script>
<script src="./commun/js/bootstrap.min.js"></script> 
<style type="text/css">
    .bs-example{
    	margin: 30px;
    }
</style>
 	
</head>

<body >
<?php print "<h3>".$msg."</h3>";?>
<div class="bs-example">
	<form method="POST">
	 
 		  
    <div class="input-prepend input-append">
	    <span class="add-on">Titre</span>
	    <?php Print $Html->InputSelect($ListeTitre,'titre',$res[0]['titre'],100,100);?>
    </div>
    <div class="input-prepend input-append">
	    <span class="add-on">Nom</span>
	    <input class="input-mediem" name="nom" type="text" id="nom" size="30" maxlength="30" value="<?php print $res[0]['nom']?>">
    </div>
			
    <div class="input-prepend input-append">
 	    <span class="add-on">Prenom</span>
		<input class="input-mediem" name="prenom" type="text" id="prenom" size="30" maxlength="30" value="<?php print $res[0]['prenom']?>">
    </div>			

    <div class="input-prepend input-append">
 	    <span class="add-on">Specialite</span>
		<input class="input-mediem" name="specialite" type="text" id="specialite" size="30" maxlength="30" value="<?php print $res[0]['specialite']?>">
    </div>	
    <div class="input-prepend input-append">
 	    <span class="add-on">Téléphone</span>
		<input class="input-mediem" name="tel" type="text" id="tel" size="30" maxlength="30" value="<?php print $res[0]['tel']?>">
    </div>	    
		<label class="checkbox">
		  <input type="checkbox" name="etat" value='1' checked> Active
		</label>
		<input type="submit" name="Enregistrer" value="Enregistrer">
		<input type="hidden" name="id_medecin" value="<?php echo $id_medecin?>">	
		<input type="hidden" name="service_id" value="<?php echo $service_id?>">		 
	</form>    
</div>    



  </body>
</html>

 