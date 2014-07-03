<?php
/*
 * update_structure.php
 * Fichier qui apelle les fonctions de la classe GildaToAghate
 * Synchronise la structure de Gilda avec Aghate
*/

set_time_limit(600000);
ini_set("display_errors","1");
error_reporting(E_ALL ^ E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassGildaToAghate.php";


#Paramètres de connection
require_once("./commun/include/settings.inc.php");
#Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");
#Fonction relative à la session
require_once("./commun/include/session.inc.php");

// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
    die();
};
include "./commun/include/language.inc.php";


// Initialisation
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Aghate->AffcheTraceSurEcran=true;
$Gilda= new Gilda($ConnexionStringGILDA);

$debut = time();

$user_level = authGetUserLevel(getUserName(),-1);
$login= $_SESSION['login'];

if(($user_level < 5 )){
	echo "<h1> Vous n'avez pas les droits pour lancer la mise à jour de la structure </h1>";
	header('Location : ./day.php');
	exit;
}



// Recuperation des données de Gilda
$litpost = $Gilda->GetLitPost();
$upf_tab = $Gilda->GetUPF();

// Initialise le fichier de trace
$Aghate->init_trace_file ();

// Lance le script pour inserer les services/lits

$Aghate->AddTrace(" | Mise a jour structure Gilda \n Lance a ". date('d-m-Y H:i:s')."\n");

$nb = count($litpost);
$cpt_a = 0;
$cpt_r = 0;
$cpt_da = 0;
$cpt_dr = 0;
$cpt_del = 0;
$last_area="";

// on boucle sur les service
for ($i=0;$i < $nb;$i++)
{
	$service_name = $litpost[$i]["LBSERV"].'-'.$litpost[$i]["NOPOST"];
	// check date velidité su service
	if ($Aghate->IsServiceDateValide($litpost[$i]['DFVALI'])) 
	{
		if ($last_area != $service_name)
		{
			$last_area=$service_name;
			
			if($Aghate->IsExistService($service_name)) 
			{
				$uh = "";
				$uh = $upf_tab[$litpost[$i]["NOPOST"]];
				$req = "";
				$req = "INSERT INTO agt_service set
								service_name                     ='".$service_name."',                            
								noposte							             ='".$litpost[$i]["NOPOST"]."',
								grp_service                      ='".$litpost[$i]["LBSERV"]."',
								access                           ='r',                                            
								order_display                    ='0',                                            
								morningstarts_area               ='0',                                            
								eveningends_area                 ='23',                                           
								duree_max_resa_area              ='-1',                                           
								resolution_area                  ='3600',                                         
								eveningends_minutes_area         ='0',                                            
								weekstarts_area                  ='1',                                            
								twentyfourhour_format_area       ='1',                                            
								calendar_default_values          ='n',                                            
								enable_periods                   ='n',                                            
								display_days                     ='yyyyyyy',                                      
								id_type_par_defaut               ='-1',                                           
								duree_par_defaut_reservation_area='1800',                                                    
								uh                               ='".$uh."',                                      
								etat                             ='n',                                            
								duree_previsionnel               ='5'"; 
				$last_id = $Aghate->insert($req);
				if($cpt_a==0){
					$first_area = $last_id;
				}
				$Aghate->AddTrace("\n"."Service ".$service_name." insere "."\n");
				$cpt_a++;
			}
			else 
			{
				$Aghate->AddTrace("Service ".$service_name." dejà existant "."\n");

				$cpt_da++;
			}
			
			$service_info = $Aghate->GetServiceInfoByServiceName($service_name);
			$service_id = $service_info[0]['id']; 
			
			if ($Aghate->IsExistRoom($litpost[$i]["NOLIT"],$service_id))
			{
				$sql = "INSERT INTO agt_room set
								service_id                  ='".$service_id."',          
								room_name                   ='".$litpost[$i]["NOLIT"]."',
								room_alias                  ='".$litpost[$i]["NOLIT"]."',
								capacity                    ='0',                        
								max_booking                 ='-1',                       
								statut_room                 ='1',                        
								show_fic_room               ='n',                        
								delais_max_resa_room        ='-1',                       
								delais_min_resa_room        ='0',                        
								allow_action_in_past        ='n',                        
								dont_allow_modify           ='n',                        
								order_display               ='0',                        
								delais_option_reservation   ='0',                        
								type_affichage_reser        ='0',                        
								moderate                    ='0',                        
								qui_peut_reserver_pour      ='2',                        
								active_ressource_empruntee   ='n'";
						
				$Aghate->insert($sql);
				$Aghate->AddTrace(" Room : ".$litpost[$i]["NOLIT"]." insere au service ".$litpost[$i]["NOPOST"]."\n");
				$cpt_r++;
			}
			else
			{
				$Aghate->AddTrace(" Room : ".$litpost[$i]["NOLIT"]." dejà insere"."\n");
				$cpt_dr++;
			}
		}
		else
		{
			$service_info = $Aghate->GetServiceInfoByServiceName($service_name);
			$service_id = $service_info[0]['id']; 
			
			if ($Aghate->IsExistRoom($litpost[$i]["NOLIT"],$service_id))
			{
				$sql = "INSERT INTO agt_room set
								service_id                  ='".$service_id."',          
								room_name                   ='".$litpost[$i]["NOLIT"]."',
								room_alias                  ='".$litpost[$i]["NOLIT"]."',
								capacity                    ='0',                        
								max_booking                 ='-1',                       
								statut_room                 ='1',                        
								show_fic_room               ='n',                        
								delais_max_resa_room        ='-1',                       
								delais_min_resa_room        ='0',                        
								allow_action_in_past        ='n',                        
								dont_allow_modify           ='n',                        
								order_display               ='0',                        
								delais_option_reservation   ='0',                        
								type_affichage_reser        ='0',                        
								moderate                    ='0',                        
								qui_peut_reserver_pour      ='2',                        
								active_ressource_empruntee   ='n'";
																
				$Aghate->insert($sql);
				$Aghate->AddTrace(" Room : ".$litpost[$i]["NOLIT"]." insere au service ".$litpost[$i]["NOPOST"]."\n");
				$cpt_r++;
			}
			else
			{
				$Aghate->AddTrace(" Room : ".$litpost[$i]["NOLIT"]." dejà insere"."\n");
				$cpt_dr++;
			}
		}
	}
	else
	{
		$Aghate->AddTrace("Date de fin de validite du service : " .$service_name."-".$litpost[$i]['DFVALI']."\n");
	}
}

//modif le 04/02/2014 defaul_duree force a 5 jours si le duree est vide
$sql ="UPDATE agt_service set duree_previsionnel='5' WHERE duree_previsionnel is null";
$Aghate->update($sql);

$usr = $Aghate->GetUserInfo($login);
$default_area = $usr[0]['default_area'];
if (strlen($default_area)>0){
	if(!($Aghate->CheckDefaultAreaValide($default_area))){
		$sql = "UPDATE agt_utilisateurs SET default_area='".$first_area."'
					WHERE login='".$login."'";
		$Aghate->update($sql);
		$Aghate->AddTrace("\n Mise à jour du default area de l'user ".$login." en ".$first_area); 
	}
	else{
		$Aghate->AddTrace("\n Aucune mise à jour necessaire pour l'user ".$login); 
	}
}
// Lance le script pour inserer les Panier a chaque service
$Aghate->InsertPanier();

$Aghate->AddTrace("Fin Fonction InsertServiceEtRooms :"."\n");
$Aghate->AddTrace("Service Insere :".$cpt_a."\n");
$Aghate->AddTrace("Service Double :".$cpt_da."\n");
$Aghate->AddTrace("Room Insere :".$cpt_r."\n");
$Aghate->AddTrace("Room Double :".$cpt_dr."\n");




/*
 ==================================================================================================
 *    UPDATE medecins
 * 
 * 
 *=================================================================================================
 */
// Import Medecin
$ListMed=$Gilda->GetAllMedecins();
$ListExcure[]="INTERNE";///// a faire
		
$Aghate->AddTrace(" #####=> Mise a jour Medecins \n");				
$TotalMed=count($ListMed);
for($i=0; $i < $TotalMed; $i++)
{
	$CheckPresentSql="SELECT * from agt_medecin where nom='".addslashes($ListMed[$i]['NMPHOS']) ."' AND prenom='" .addslashes($ListMed[$i]['NMPPHS'])."'";
	if(count($Aghate->select($CheckPresentSql)) < 1 )
	{
		$TableauInsertDonnee['titre'] 	= 	$ListMed[$i]['LBTITR'];
		$TableauInsertDonnee['nom'] 	=	$ListMed[$i]['NMPHOS'];
		$TableauInsertDonnee['prenom'] 	=	$ListMed[$i]['NMPPHS'];	
					
		$Aghate->insertion('agt_medecin',$TableauInsertDonnee);				
		$Aghate->AddTrace("\nMedecin ".$ListMed[$i]['NMPHOS']."=> Inseré");
	}else{
		$Aghate->AddTrace("\nMedecin ".$ListMed[$i]['NMPHOS']." Déja present");
	}
} 


// Ecrit dans le fichier de script
$Aghate->write_trace_file();


$fin = time();

$result = $fin - $debut;
echo "<br />Temps du traitement : ";
echo gmdate("H:i:s", $result); 

?>
