<?php  
include "config/config.php";
include "config/config.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/misc.inc.php";

include "./config/config.php";
include "./commun/include/ClassMysql.php";
include "./commun/include/ClassAghate.php";
include "./commun/include/ClassGilda.php";
include "./commun/include/CommonFonctions.php";
include("../commun/layout/header.php");
$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc="agt_prog";

$CommonFonctions = new CommonFunctions(true);

// Settings
date_default_timezone_set('Europe/Paris');

require_once("./commun/include/settings.inc.php");

//-----------------------------------------------------------------------------
//Chargement des valeurs de la table settings
//-----------------------------------------------------------------------------
if (!loadSettings()){
    echo "|ERR|Erreur chargement settings";
		exit;
}
//-----------------------------------------------------------------------------
// Session related functions
//-----------------------------------------------------------------------------
require_once("./commun/include/session.inc.php");
// Resume session
if (!grr_resumeSession()) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};
//include "include/admin.inc.php";

//echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");
$session_user = $_SESSION['login'];
$session_statut = $_SESSION['statut'];

$consult_prog = $Aghate->GetConsultProg($session_user);
//print_r($consult_prog);
//$showtable = false;
$sql_ora="";
$sql_local="";

if (count($consult_prog) > 0)	{
	
}else{
	echo "<script type=\"text/JavaScript\"> CloseMe()</script>";

}

?>

<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="./commun/js/fonctions_aghate.js" ></script>
</head>
<script type="text/JavaScript">
	function upperCase(Idval){
		var x=document.getElementById(Idval).value
		document.getElementById(Idval).value=x.toUpperCase()
	}
	//retuen selected value
	function CloseMe(){
		window.close();
	}
	//retuen selected value
		
</script>

<body bgcolor="#FFFFFF" text="#000000">
<form>
  <div align="center">
		<table border="0" cellspacing="1" cellpadding="0" >
			<tr>
				<th colspan='5' bgcolor=#A8BBCA >Demande programmation </th>		</tr>	
			<tr>
				
			<tr id="idHeader" bgcolor=#A8BBCA  height=30>
				<th>&nbsp;&nbsp;Patient&nbsp;&nbsp;</th>		
				<th>&nbsp;&nbsp;Date de début&nbsp;&nbsp;</th>
				<th>&nbsp;&nbsp;Statut de la demande&nbsp;&nbsp;</th>
				<th>&nbsp;&nbsp;Consulter&nbsp;&nbsp;</th>
				<th>&nbsp;&nbsp;Motif&nbsp;&nbsp;</th>
            </tr>              
		
		
		  <?php  
		  for($i=0;$i < count($consult_prog);$i++){  		
		  		$patient=$consult_prog[$i]['noip']." ".$consult_prog[$i]['nom']." ".$consult_prog[$i]['prenom'].
									" ne(e) le ".$consult_prog[$i]['ddn']." (".$consult_prog[$i]['sex'].")";    			    			    			    			    		
				$date	=date("d/m/Y à H:i",$consult_prog[$i]['start_time']);
				$statut=$consult_prog[$i]['statut_entry'];
				$motif=$consult_prog[$i]['motif'];
		  	 ?>
					<tr>
						<td> <?php  Print $patient;?> </td>
						<td> <?php Print $date;?></td>
						<td> &nbsp;&nbsp;<?php  Print $statut;?></td>
						<td>
						<?php
							if($statut=="Consulter")
								$image="../commun/images/ok.jpg";
							else
								$image="../commun/images/ko.jpg";
					
							$js_fct="updateConsult('".$consult_prog[$i]['id']."');";
							print '<a href="#?" onclick='.$js_fct.'><img id="imgValid" src='.$image.' 
											width="20" height="20" alt="Valider" title="Valider" border="0" ></a>';
						?>
						</td>
						<td> &nbsp;&nbsp;<?php  Print $motif;?></td>
				  </tr>
		 <?php  } ?>
		</table>
</div>

<?php if($i==0)
		echo "<script type=\"text/JavaScript\"> CloseMe()</script>";

?>
<div style="overflow:auto;width:700px;  ">
		  		<div  align="center" >
					<input align="center" name="fermer" type="button" class="form_bouton_bleu" value="Fermer" onClick="CloseMe()"></div>
			</div>		
</div>	
</form>
</body>
</html>
