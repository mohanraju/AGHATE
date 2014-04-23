<?php

//inclusion des objets
header('Content-Type: text/html; charset=utf-8');
include "./config/config.php";
include "./config/config.inc.php";

include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include"./commun/include/settings.inc.php";
include("./commun/include/session.inc.php"); //#Fonction relative à la session

include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/CommonFonctions.php";
include "./commun/include/ClassHtml.php";
include "./commun/include/language.inc.php"; // Paramètres langage

#Chargement des valeurs de la table settings
if (!loadSettings())
    die("Erreur chargement settings");
    

// Resume session
if ((!grr_resumeSession())and (getSettingValue("authentification_obli")==1)) {
    header("Location: ./logout.php?auto=1");
	
    die();
};


if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
    $session_login = '';
    $session_statut = '';
    $type_session = "no_session";
} else {
    $session_login = $_SESSION['login'];
    $session_statut = $_SESSION['statut'];
    $type_session = "with_session";
}

if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}

# print the page header
print_header($day, $month, $year, $area, $type_session);

// init objets
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_loc";

$Html 	= new Html();
$db		= new MySQL();
$funtions= new CommonFunctions(true);


//----------------------------------
// get current user allowed areas 
//----------------------------------
$session_user = $_SESSION['login'];
$session_statut = $_SESSION['statut'];

   
//----------------------------------------------------
// Preparation Service liste
//----------------------------------------------------
$Services=$Aghate->GetAllArea();

for($i = 0; $i < count($Services); $i++)
{
	$ListeServices[] = $Services[$i]['id']."|".$Services[$i]['service_name'];
}
$area_select=$Html->InputSelect($ListeServices,'area','100','100');
 

//----------------------------------------------------
// Preparation Protocoles  list
//----------------------------------------------------
$sql = "SELECT protocole from agt_protocole order by protocole";
$Protocoles = $db->Select($sql);
    
for($i = 0; $i < count($Protocoles); $i++)
{
	$ListeProtocoles[] = $Protocoles[$i]['protocole']."|".$Protocoles[$i]['protocole'];
}


//----------------------------------------------------
// Preparation Medecines  list
//----------------------------------------------------
$sql = "SELECT DISTINCT specialite FROM `agt_medecin`";
$Specialite = $db->Select($sql);
    
for($i = 0; $i < count($Specialite); $i++)
{
	$ListeSpecialite[] = $Specialite[$i]['specialite']."|".$Specialite[$i]['specialite'];
}


//----------------------------------------------------
// Preparation specialité  list
//----------------------------------------------------
$sql = "SELECT * from agt_medecin order by nom";
$Medecines = $db->Select($sql);
    
for($i = 0; $i < count($Medecines); $i++)
{
	$ListeMedecines[] = $Medecines[$i]['id_medecin']."|".$Medecines[$i]['nom']." ".$Medecines[$i]['prenom'];
}




$date_deb = isset($date_deb) ? $date_deb : date('d/m/Y',strtotime("-10 days"));
$date_fin = isset($date_fin) ? $date_fin :date("d/m/Y");

//listes et default values
$Listchoix[]="P|Programé";
$Listchoix[]="H|Hospitalisé";
//force default 
if($typesej=="99")
	$typesej="H";
	
$ListMvt[]="E|Entrée entre";
$ListMvt[]="S|Sortie entre";	
if($TypeMvt=="99")
	$TypeMvt="E";

if ($Afficher=="Afficher")
{

	list($day,$mois,$annee)=explode("/",$date_deb);
	$date_deb_=mktime(0,0,0,$mois,$day,$annee);	
	list($day,$mois,$annee)=explode("/",$date_fin);  	
	$date_fin_=mktime(23,59,59,$mois,$day,$annee);	

	//patients deja hospitalisé
	$sql="";

	if($typesej=="H")
		$cond=" AND e.de_source != 'Programme' ";
	else
		$cond=" AND e.de_source ='Programme' ";

	// prepare l'affichage
	$sql = "SELECT  p.noip,p.nom,p.prenom,p.ddn,p.sex,
				FROM_UNIXTIME(e.start_time, '%d/%m/%Y %Hh%i') as date_deb,
				FROM_UNIXTIME(e.end_time, '%d/%m/%Y %Hh%i') as date_fin,					
				e.id as entry_id,a.service_name, r.room_name, r.description, a.id,
				e.protocole,e.medecin,e.uh,e.nda
			FROM agt_loc e, agt_service a, agt_room r, agt_pat p
			WHERE e.room_id = r.id 
			  AND r.service_id = a.id   
			  AND p.noip=e.noip
              AND ( e.medecin IN ( Select id_medecin from agt_medecin) OR e.medecin IS NULL)
              
			  "; 
	//filtre entrée / sortie
	if($TypeMvt=="E")
		$sql .= "AND start_time between $date_deb_ and $date_fin_";
	else
		$sql .= "AND end_time between $date_deb_ and $date_fin_";
		
	//filtre par service				  
	if (strlen($area) > 0){
		 $sql .= " AND a.id ='".$area."' " ;
		}
		
	//filtre par protocoles 
	if (strlen($protocole) > 0){
		 $sql .= " AND e.protocole ='".$protocole."'";
		}

	//filtre par medecin 
	if (strlen($medecin) > 0){
		 $sql .= " AND e.medecin ='".$medecin."'";
		}
		
//filtre par medecin 
	if (strlen($specialite) > 0){
		 $sql .= " AND '".$specialite."' IN (Select specialite from agt_medecin where id_medecin = e.medecin)";
		}
$sql.=" $cond Order by p.nom,p.prenom limit 500"	;						
	 
$res = $db->select($sql);
}
?>


<link rel="stylesheet" href="./commun/style/redmond/jquery-ui-1.10.3.custom.css">
<script src="./commun/js/jquery-1.9.1.js"></script>
<script src="./commun/js/jquery-ui.js"></script>
<script language="javascript" type="text/javascript" src="./commun/js/JCalender.js"></script>
<script language="javascript" type="text/javascript" src="./commun/js/Communfonctions.js"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="./commun/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf-8" language="javascript" src="./commun/js/DT_bootstrap.js"></script>

<script type='text/javascript'>
	$(function()
	{
		$( "#date_deb" ).datepicker();
		$( "#date_fin" ).datepicker();
	});


	function change_val() {
		document.getElementById('view').value="NON";	
		document.reports.submit();
	}

	function OpenPopupResa(url) {
		mywindow1=window.open(url,'myname','resizable=yes,width=850,height=670,left=150,top=100,status=yes,scrollbars=yes');
		mywindow1.location.href = url;
		if (mywindow1.opener == null) mywindow1.opener = self;
	}
 
</script>
<link rel="stylesheet" type="text/css" href="./commun/style/bootstrap_extra.css">
<!-- DEBUT de parite utulise pour DataTable Bootstrap -->
<link rel="stylesheet" type="text/css" href="./commun/style/bootstrap.css">

<style>
	#DivDataTable{
	width :1200px; 
	margin:0px auto; 
	text-align:center;
	} 	
	th{
	text-align:center;
	}
</style>
<!-- FIN parite utulise pour DataTable Bootstrap -->
<br /> <br />
<div class="container">
  <div class="row">
  <!-- 
  =======================================================================================
  	Formualire d'input GAUCHE
	=======================================================================================  	
   -->
    <div class="span3">
 			<form name="reports" method='POST' action=report.php>
				<fieldset>
				<legend>Critère de recherche</legend>					

				<label for="dat">Patients</label>
				<?php 
					Print $Html->InputChoix($ListMvt,'TypeMvt',$TypeMvt);?>  					
					<br>
					<?php Print $Html->InputTextBox('date_deb',$date_deb,10,10);?>
					<?php Print $Html->InputTextBox('date_fin',$date_fin,10,10);?>
				
				
					<label>Service </label>
				 <?php Print $Html->InputSelect($ListeServices,'area',$area,'200');?>	
				
				  <label for="dat">Protocole</label>
				  <?php Print $Html->InputSelect($ListeProtocoles,'protocole',$protocole,'200');?>
				
				  <label for="dat">Medecin</label>
				  <?php Print $Html->InputSelect($ListeMedecines,'medecin',$medecin,'200');?> 
					
				  <label for="dat">Specialité</label>
				  <?php Print $Html->InputSelect($ListeSpecialite,'specialite',$specialite,'200');?> 
					

					<label for="dat">Type séjours</label>
					<?php 
		
						
					Print $Html->InputChoix($Listchoix,'typesej',$typesej);?>  
					</br></br>
				  <input type="submit" name="Afficher" id="Afficher" value="Afficher" class="btn btn-success"/> 	
				</fieldset> 

			</form>			
    </div>                                                                                                                        
    <div class="span9" >
		  <!-- 
		  =======================================================================================
		  	Affihcage DROITE
			=======================================================================================  	
		   -->

			<div id="DivDataTable" align="center">
			<table  align='center' border="1" cellspacing="0" cellpadding="0" border="0" class="table table-striped table-bordered" id="ModelDataTable">
				<thead>
				<tr>
					<th>NIP</th>
					<th>Patient</th>
					<th>NDA</th>
					<th>UH</th>
					<th>Service </th>
					<th>Date Debut</th>
					<th>Date Fin</th>    
					<th>Medecin</th>					
					<th>Specialite</th>					
					<th>Protocole</th>

					
				</tr>
				</thead>
				<?php
				$NbrRows= count($res);
				$NbrRows = ($NbrRows >1000)?1000:$NbrRows; //limit max rows a 1000
				 for ($i = 0; $i < $NbrRows; $i++)
				 {    
					if ($cur_class=="one") 
						$cur_class="two";
					else
						$cur_class="one";
					$pat =	$res[$i]['nom']." ".$res[$i]['prenom']." né(e)".$funtions->Mysql2Normal($res[$i]['ddn']);
					$EditLink =$ModuleReservationEdit."?id=".$res[$i]['entry_id'];						
					$EditLink ="<a href='#?'  onClick=\"OpenPopupResa('".$EditLink."')\">".$pat." </a>";                        				

					$info_med	=$Aghate->GetInfoMedecinById($res[$i]['medecin']);
					$medecin	=$info_med['nom']." ".$info_med['prenom'];
					$specialite	=$info_med['specialite'];
					
					Print "<tr class='$cur_class'>
									<td>".$res[$i]['noip']."</td>														
									<td>".$EditLink."</td>	
									<td>".$res[$i]['nda']."</td>									
									<td>".$res[$i]['uh']."</td>										
									<td>".$res[$i]['service_name']."</td>
									<td>".$res[$i]['date_deb']."</td>
									<td>".$res[$i]['date_fin']."</td>
									<td>".$medecin."</td>									
									<td>".$specialite."</td>									
									<td>".$res[$i]['protocole']."</td>
									
					  		</tr>";    	
			 	 }
				 
				echo "</table>
				</div>";
				 
				?>
      </div>
  </div>
</div>


