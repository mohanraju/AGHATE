<?Php  
/*
#########################################################################

#########################################################################
            
*/
include "../../config/config.php";
include "../../config/config.inc.php";
include "../../commun/include/functions.inc.php";
include "../../commun/include/$dbsys.inc.php";
include "../../commun/include/mrbs_sql.inc.php";
include "../../commun/include/misc.inc.php";

include "../../config/config.php";
include "../../config/config_".$site.".php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/CommonFonctions.php";

$mysql = new MySQL();
$Aghate = new Aghate();
$Aghate->NomTableLoc = "agt_prog";
$CommonFonctions = new CommonFunctions(true);

// Settings
date_default_timezone_set('Europe/Paris');
require_once("../include/settings.inc.php");
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
require_once("../include/session.inc.php");
// Resume session
if (!grr_resumeSession()) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};

$info_entry = $Aghate->GetInfoDemandeById($id);

$session_user = $session_user = $_SESSION['login'];

$user_level = authGetUserLevel($session_user,-1);

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
if($user_level < 1)
{
	echo "|ERR|Access Denied !";
	exit();
}

$TabCd['id'] 				= $id_prog;
$TabUpd['statut_consult']	= "O";


if (strlen($id_prog) >0){ 
	//ctrl si la prog est bien faite par ce medecin
	$res = $Aghate->GetInfoDemandeById($id_prog);
	if($res["create_by"]==$session_user){
		$res=$mysql->update_("agt_prog",$TabUpd,$TabCd);
	}
	else{
		echo "|ERR|Different createur !";
		exit;	
	}
}
else{
	echo "|ERR| Programmation incorrecte !";
	exit;

}

echo "|OK|$id_prog|"; 

?>

