<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
#		IMPORT STRUCTURE                                                                       #
#                                                                                          #
#   Date creation 07/06/2012                                                               #
#		Date dernière 07/06/201			                                                           #
#   PAR MOHANRAJU @ SLS APHP PARIS                                                         #
############################################################################################
*/
/*-----------------------------------------------------
// DECLARAITONS VARIABLES
-----------------------------------------------------*/
$FichierCsv="./SSR_PSY_LRB.xls";
$SeparateurCsv="\t";
$TableMysql="structure_gh";
	
//force maximum execution time 
ini_set ('max_execution_time', 0); //pas de limitation
ini_set('memory_limit','254M'); // 254 limitation


include("../../config/config.php");
include("../../commun/include/CommonFonctions.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassHtml.php");

//init et DECL OBJETS
$Functions= new CommonFunctions(true);
$Html= new html();
$db = new Mysql();



if (file_exists($FichierCsv)) {
	$ficInfo= "<b>$FichierCsv </b>a été modifié le : " . date ("d/m/Y à H:i:s.", filemtime($FichierCsv));
}else{
	echo "Ficheir structure introuvable".$FichierCsv;
	exit;
}
echo $ficInfo;
?>
<frameset>
	<legend>
	<form Action="<?php print $_SERVER['PHP SELF']?>" method="POST">
		<?php Print $Html->InputCheckBox('del_old',$del_old,'Delete ancienne strucutre ');?>
		<input name="Ok" type="submit" value="Mise a jour" />
	</form>
	</legend>
</frameset>
<?php


if($Ok == "Mise a jour")
{
	echo $FichierCsv;

	//HOPITAL","HOPITAL.LIB","POLE","POLE.LIB","CHEF.SERVICE","SERVICE","SERVICE.LIB","URM","URM.LIB","UH","UH.LIB","UA","VAL.DEB","VAL.FIN
	
	$BaseColonne=array("hopital","hopital_lib","pole","pole_lib","chef_service","service","service_lib","urm","urm_lib","uh","uh_lib","ua", "date_deb","date_fin");
	$ColonnesDate2Mysql=array(12,13);
	$SautFirstLine=true;

	
	// vérifications
	if($SautFirstLine)
		$start=1;
	else
		$start=0;
		
	if (	strlen($FichierCsv)< 1){
		echo "Fichier CSV not defined";
		exit;
	}
	if (	is_file($FichierCsv)< 1){
		echo "ficheir introuvable dans le chemin : ".$FichierCsv;
		exit;
	}
 
		
	// convert array keys vs val
	foreach ($ColonnesDate2Mysql as  $vals)
	{
		$ColDateChk[$vals] =$vals;
	}
	 
	
	$lines = file($FichierCsv);
	$arr_size=count($lines);
	 
	 
	for($i=$start;$i < $arr_size;$i++)
	{
			 
			 
			 
		// explode le ligne dans un tableu
		$data=explode($SeparateurCsv,$lines[$i]); 
			// check structre cav avec structure base
		if(count($data)!= count($BaseColonne))
		{
			echo "<br><b>Nombre de collone dans le ficheir csv ne corresponds pas <br>Structure base diffrent en structure CSV </b><br><br> <b>Dans la Base structure </b><br>";
			echo "<pre>";
			print_r($BaseColonne);
			echo "</pre>";
			echo "<br><b>Dans le fichier :-</b><br>";
			echo "<pre>";
			print_r($data);	
			echo "</pre>";
			exit;
		}			
	 
		$sql="INSERT INTO ".$TableMysql." SET ";
		for ($c=0; $c < count($BaseColonne); $c++)
		{
			// convert date dd/mm/YYYY to YYYY-mm-DD for mysql
	
			if (array_key_exists($c, $ColDateChk))
			{
				$data[$c]=$Functions->Normal2Mysql(trim($data[$c]));	
			}
			
			if ($c==0)
				$sql .= $BaseColonne[$c] ." = '".trim($data[$c])."' ";
			else
				$sql .= ", ".$BaseColonne[$c] ." = '".trim($data[$c])."' ";
		}
		echo "<br>".$sql;
		$db->insert($sql);
	 
	}
}
?>		
