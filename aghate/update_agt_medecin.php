<?php
/*
 * Update_agt_medecin.php
 * Fichier qui apelle les fonctions de la classe Gilda
 * Insere, met à jour, inactive les medecins
*/

header('Content-Type: text/html; charset=utf-8');
set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);

// Include
include "./config/config.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./config/config_".$site.".php";

$gilda = new Gilda($ConnexionStringGILDA);
$mysql = new MySQL();
$Aghate= new Aghate();

$Aghate->AffcheTraceSurEcran=true;

$Aghate->init_trace_file_medecin();
$Aghate->AddTrace("\n #### Procedure mettre a jour les medecins ####\n==>LancÃ© Ã  ". date('d/m/Y H:i:s'). " \n" );

$medecinGilda = $gilda->GetAllMedecins();
$sql = "SELECT * FROM agt_medecin";
$medecinAghate = $mysql->select($sql);

function stripAccents($string){
	return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüıÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜİ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

//On parcours les medecins recuperer de Gilda
for($i = 0; $i < count($medecinGilda); $i++){
	//Onparcours ceux de Aghate
	$j = 0;
	$test = false;
	do{
		if ($medecinGilda[$i]['NMPHOS'] == strtoupper(stripAccents($medecinAghate[$j]['nom'])) && $medecinGilda[$i]['NMPPHS'] == strtoupper(stripAccents($medecinAghate[$j]['prenom'])))
		{
			$test = true;
			$service = substr($medecinGilda[$i]['CDPHOS'], 0, 2);
			
			if (($handle = fopen("ServiceSpeNck.csv", "r")) !== FALSE) {
			    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			    	//$Aghate->AddTrace (" \n service = ".$data[0]."  ".$service." ".$medecinAghate[$j]['nom']." ");
			        if ($data[0] == $service){
			        	$spe = $data[1];
			        }
			        else{
			        	$spe = "";
			        }
			    }
			}
	   		 fclose($handle);
	   		 //$Aghate->AddTrace (" \n specialite = ".$spe."");
	   		 $sqlUpdate = "UPDATE agt_medecin SET specialite ='".$spe."' Where id_medecin = ".$medecinAghate[$j]['id_medecin']."";
	   		 $mysql->update($sqlUpdate);
	   		 $Aghate->AddTrace(" \n MISE A JOUR de ".$medecinGilda[$i]['LBTITR']." ".$medecinGilda[$i]['NMPHOS']." ".$medecinGilda[$i]['NMPPHS']." ".$spe."");		
		}
		 $j++;
	}while($j < count($medecinAghate));
	//Si un medecin de Gilda n'est pas présent dans Aghate on l'insère
	if ($test == false){
		$service = substr($medecinGilda[$i]['CDPHOS'], 0, 2);
	
		if (($handle = fopen("ServiceSpeNck.csv", "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
		        if ($data[0] == $service){
		        	$spe = $data[1];
		        }
		    }
		}
	   	 fclose($handle);
		
		$sqlInsertion = "INSERT INTO agt_medecin(titre, nom, prenom, specialite) VALUES (\"".$medecinGilda[$i]['LBTITR']."\", \"".$medecinGilda[$i]['NMPHOS']."\", \"".$medecinGilda[$i]['NMPPHS']."\, \"".$spe."\") ";
		$mysql->insert($sqlInsertion);
		$Aghate->AddTrace(" \n INSERTION de ".$medecinGilda[$i]['LBTITR']." ".$medecinGilda[$i]['NMPHOS']." ".$medecinGilda[$i]['NMPPHS']." ".$spe."");
	}
}
	

/*
 * 
 * INNACTIVATION ARRETER CAR TOUS LES MEDECINS NE SONT PAS DANS GILDA
 * 
 */
//On parcours les medecins de Aghate
/*for($i = 0; $i < count($medecinAghate); $i++){
	//On parcours ceux de Gilda
	$j = 0;
	$test = false;
	while($j < count($medecinGilda)){
		if ($medecinGilda[$j]['NMPHOS'] == strtoupper(stripAccents($medecinAghate[$i]['nom'])) && $medecinGilda[$j]['NMPPHS'] == strtoupper(stripAccents($medecinAghate[$i]['prenom'])) )
		{
			$test = true;
		}
		 $j++;
	}
	//Si un medecin de Aghate n'est pas présent dans Gilda on le supprime
	if ($test == false){
		$sqlInsertion = "UPDATE agt_medecin SET actif = 'n' WHERE id_medecin = ".$medecinAghate[$i]['id_medecin']."";
		$mysql->insert($sqlInsertion);
		$Aghate->AddTrace("\n INNACTIVATION de ".$medecinAghate[$i]['titre']." ".$medecinAghate[$i]['nom']." ".$medecinAghate[$i]['prenom']."");
	}
}*/		
 
$Aghate->write_trace_file();
?>