<?php
/*
############################################################################################
#	                                                                                         #
#                                                                                          #
#		Suivi contole qualite                                                                  #
#		CRH,                                                                                   #
#		CRO,                                                                                   #
#		codage ,                                                                               #
#		qualité de codage ,                                                                    #
#		exhativité des codage et CR                                                            #
#		des sejours des patients sortie de l'hôpital.                                          #
#                                                                                          #
#                                                                                          #
#		Date dernière modification le 18/01/2012                                               #
#                                                                                          #
############################################################################################
*/
session_start();
//=================================================================================
// utilisateur est connecté
//================================================================================= 
if ($_SESSION["PROJET"]!="MSI"){
	header("Location: ../user/login.php");	
	exit;
}
//=================================================================================			
// vérify le site est decalré
//=================================================================================
$site=$_SESSION['site'];
if(strlen($site) < 1 )
{
	echo "<br/><br/><br/><br/><div align='center'>Config:: Site inconnu, veuillez spécifez votre site SVP  </div>";
	exit;
}

// force maximum execution time , car le module suivi est un peu long
ini_set ('max_execution_time', 0); // pas de limitation



//=================================================================================
// script s d'inclusion
//=================================================================================
include("../config/config.php");
include("../config/config_".strtolower($site).".php"); 
require("../commun/include/ClassMysql.php");
require("../commun/include/CommonFonctions.php");
include("../commun/include/ClassHtml.php");
include("../commun/include/progressbar.php"); 
include("../commun/include/ClassUser.php"); 
include("../commun/layout/header.php");



//Objet init
$db=new MYSQL();
$html = new Html();
$Functions = new CommonFunctions(true);
$User = new User($db);
 
//=================================================================================
// definition des péroides	
//=================================================================================
if ((strlen($date_deb) < 8) or (strlen($date_fin) < 8) )
{
	$dates = $Functions->GetFirstAndLastDaysOfWeek(date("W")-2,date("y"));	
	$date_deb=$dates[0];
 	$date_fin=$dates[1];
}

//=================================================================================
//preparation d'affichage menu roulant des service
//=================================================================================
if ($_SESSION["droits"]=="USER")
{
	// si group user pas de selection des service les service seront proposé par defaut
	$ServicesDispo=$User->GetUserServices($_SESSION['user']);
	if (count($ServicesDispo) < 1)	
		$Services="Aucun Service !";
	else
		$Services=$ServicesDispo[0][0];
	
	 // format a retourner : hopital,'-', service_lib
	 // format recuparer : service_lib(hopital)
	 list($service_lib,$hopital)=explode("(",$Services);		
	 $hopital=str_replace(")","-",$hopital);
		
	$select_pole="&nbsp;&nbsp;Service :" .$Services ."<input type='hidden' name='select_grp' id ='select_grp' value='SERVICE|$hopital$service_lib'><input type=hidden name ='uh_liste' id='uh_liste'  value=''>";		
}	
else
{

	$select_pole="<select class='pole' name='select_grp'  id='select_grp' onchange='VideUh()'>";
	$select_pole.="<option value='' Selected>Sélectionnez</option>";	
	
	/*-----------------------------------
		SELECTION PAR services
	-------------------------------------*/
	$sql="SELECT concat(hopital,'-', service_lib) as urm,concat(service_lib, '-(',hopital,')') as service_lib from structure_gh where service_lib is not null and service_lib<>''  
				group by hopital,service_lib order by service_lib";
	$res_poles=$db->select($sql);
	$nbr_pole=count($res_poles);
	
	$select_pole.="<optgroup label='SERVICES'>";
	
	for($i=0;$i < $nbr_pole; $i++)
	{
		if($lib_GRP==$res_poles[$i]['urm'])	
			$select_pole.="<option value='SERVICE|".$res_poles[$i]['urm']."' Selected>".$res_poles[$i]['service_lib']."</option>";
		else
			$select_pole.="<option value='SERVICE|".$res_poles[$i]['urm']."'>".$res_poles[$i]['service_lib']."</option>";
	}
	$select_pole.="</optgroup>";
	
		
	/*-----------------------------------
		SELECTION PAR POLES
	-------------------------------------*/
	$sql="SELECT pole,pole_lib from structure_gh where pole <>'' group by pole order by pole ";
	$res_poles=$db->select($sql);
	$nbr_pole=count($res_poles);
	
	list($GRP,$lib_GRP)=explode("|",$select_grp);		
	
	$select_pole.="<optgroup label='POLES'>";
	for($i=0;$i < $nbr_pole; $i++)
	{
	
		if($lib_GRP==$res_poles[$i]['pole'])
			$select_pole.="<option value=POLE|".$res_poles[$i]['pole']." Selected>".$res_poles[$i]['pole_lib']."</option>";	
		else
			$select_pole.="<option value=POLE|".$res_poles[$i]['pole'].">".$res_poles[$i]['pole_lib']."</option>";
	}
	$select_pole.="</optgroup>";
	
	
	

	
	$select_pole.="</select>";
}
 
?>
<script  type="text/javascript">
function VideUh(){
		document.getElementById('uh_liste').value='';
}
function VideGruoupSelection(){
	//document.getElementById('select_grp').setAttribute('size', 3);

	var grp = document.getElementById('select_grp');
  if ( grp != null && grp.options.length > 0 )
        grp.options[0].selected = "selected";	
}
</script>
<?php 
include("../commun/layout/menu.php");
?>

<div align="center" width='500px'>    
	<form   name="form1" method="POST" >
		<fieldset class="the-fieldset">	
			<div class='pageheading'>TBB de Suivi exhaustivité  </div> 

			    <div class="input-prepend input-append">
				    <span class="add-on">Période Du&nbsp;&nbsp;</span>
				    <input class="input-small" name="date_deb" type="text" id="date_deb" size="10" maxlength="10" value="<?php print $date_deb?>"  >
				    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				    <span class="add-on">Au</span>
				    <input class="input-small" name="date_fin" type="text" id="date_fin" size="10" maxlength="10" value="<?php print $date_fin?>">
			    </div>				

				<?php 
			 		if ($_SESSION["droits"]=="USER")
			 		{
			 		?>	
				    <div class="input-prepend input-append">
					    <span class="add-on">Service </span>
					    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					    <span class="add-on"><?php echo $select_pole;?> </span>
				    </div>		
					<?php 
			 		}else{
			 		?>
				    <div class="input-prepend input-append">
					    <span class="add-on">Service&nbsp;&nbsp;</span>
					    <?php echo $select_pole;?>
					    <span class="add-on">Les UH</span>
					   	<input  class="input-small" name="uh_liste" id="uh_liste"  autocomplete="off" value="<?php print $uh_liste?>" placeholder="Saisiez les UH" type="text" onKeyup="VideGruoupSelection()"> 				    
					    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					    
					   
				    </div>				
				  <?php  
			 		}
					?>				 	

  			<div class="input-prepend input-append">
					<span class="add-on">Exhaustivite sur &nbsp;&nbsp;</span>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php 
							print $html->InputCheckbox('crh',$crh,"CRH");
							print $html->InputCheckbox('cro',$cro,"CRO");							
							print $html->InputCheckbox('ipop',$ipop,"Actes IPOP");														
							print $html->InputCheckbox('codage',$codage,"Codage");
							print $html->InputCheckbox('nestor',$nestor,"Nestor");
					?>					
 				</div> 

					<div class="input-prepend input-append">
						<span class="add-on">Type séjour&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php 
							$_TypeSejours = array('HC|HC','HDJ|HDJ','TOUS|TOUS');
							if (strlen($typesej) <1)
								$typesej="TOUS";
							print $html->InputRadio($_TypeSejours,'typesej',$typesej," ",$true );
							?>
					</div>	
			 
			
			<div align='center'>
			 <input class="btn btn-info" type="submit" name="Afficher" value="Afficher">  
			</div>
 
	</fieldset>	
</form>
</div>
<br>
<?php
 
//=================================================================================---------------------
// Check nombre de jours entre deux dates 
// pour éviter le relantissement du serveur
//=================================================================================---------------------
$nbr_jours=$Functions->NombreJours(str_replace("/","-",$date_deb),str_replace("/","-",$date_fin));
//$nbr_jours=5;
if (intval($nbr_jours) > 31){
	echo "<div align=center><br> La prériode maximale de recherche est de 30 jours <br>Veuillez modifier vos critières de recherche </div>";
	exit;
}

if(  ($select_grp=="") and ($uh_liste=="")  and ($Afficher=="Afficher") )
{
Print '
		<div class="span5">&nbsp;
		   </div>
		<div class="alert alert-error span5">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
       	Veuillez sélectionner un service ou saisir  vos UH
     </div>
		<div class="span5">&nbsp;
		   </div>     
     ';	
}else{
	include("./tbb_suivi.php");
}

$PushFooter=20;	
include("../commun/layout/footer.php");
?>
