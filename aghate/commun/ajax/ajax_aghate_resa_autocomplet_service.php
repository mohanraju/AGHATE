<?Php  
/*
* PROJET AGHATE
* Ajax Changement de ROOM pour les patient dans le panier
*
* @Mohanraju SBIM/SAINT LOUIS/APHP/Paris
* 
* date dernière modififation 14/05/2014
* 
*/

include "../../resume_session.php";
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
header('Content-type: text/html; charset=utf-8'); 
include "../../config/config.php";
include "../../commun/include/ClassMysql.php";
include "../../commun/include/ClassAghate.php";
include "../../commun/include/CommonFonctions.php";

$db = new MySQL();
$Aghate = new Aghate();
$CommonFonctions = new CommonFunctions(true);


// Session related functions
if (strlen($_SESSION['login']) < 1) {
    echo "|ERR|Session expaired, veuillez reconnectez svp!";
    exit;
};

// escape your parameters to prevent sql injection
$param   = utf8_decode($_GET['term']);
$tb=$_GET['tb'];
$ret_vals = array();

 

$service_autoriser = $Aghate->GetAllServiceAuthoriser($_SESSION['login'],$_SESSION['statut']);
//echo "tableau : ";
//print_r($service_autoriser);
for($i=0;$i<count($service_autoriser);$i++){
	$service_id[]=$service_autoriser[$i]['id'];
}
$str_srv_autoriser = implode(",",$service_id);
//echo "service autoriser".$str_srv_autoriser;

//echo $sql_ajax;
$ret_vals = array();

//execute SQL
$res=$Aghate->GetServiceInfoByValRech($param,$str_srv_autoriser,$tb);
$nb_rec=count($res);
 

//prepare Tableau
for($i=0; $i < $nb_rec;$i++)
{
	$id=$res[$i][0];
	$lib=$res[$i][1];
	
	//prepare Tableau	
	$ret_vals[] = array
					('value' => utf8_encode($lib),
        			 'id'    => $id,
    				);  
}

// format json format et envoyer
echo json_encode($ret_vals);
?>