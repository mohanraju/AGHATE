<?Php  
/*
* PROJET AGHATE
* Ajax Changement de ROOM pour les patient dans le panier
*
* @Mohanraju sp /Celeste Thierry SBIM/SAINT LOUIS/APHP /Paris
* 
* date dernière modififation 14/05/2014
* 
*/
include "../../resume_session.php";
include("../../config/config.php");
require("../../commun/include/ClassMysql.php");
require("../../commun/include/ClassAghate.php");
require("../../commun/include/CommonFonctions.php");

//Objet init
$Aghate=new Aghate();
$Aghate->NomTableLoc=$table_loc;
/*
 * check session
 */ 

if (strlen($login) < 0 ) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};

$res=$Aghate->UpdateDescriptionFromId ($FormUpdate_VID,$FormUpdate_Var,$FormUpdate_Val,$FormUpdate_Libelle);

?>