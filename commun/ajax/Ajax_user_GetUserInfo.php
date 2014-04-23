<?php 
header('Content-Type: text/html; charset=utf-8');

// insertion des objets
require("../../config/config.php");
require("../../commun/include/ClassMysql.php");
$_login=$_GET['login'];
// init les objets
$db=new Mysql();
$TableUser="utilisateur";
$sql="SELECT * FROM ".$TableUser." WHERE login='".$_login."'";
$res=$db->select($sql);

$retval='<table cellpadding="0" cellspacing="0" border="0" class="display" id="example1" >
					<tbody>
					<tr><td width="100px">Code APHP</td><td>:'. $res[0]['login'].'</td>  </tr>
					<tr><td>Nom</td>			<td>:'. $res[0]['nom'].'</td>        </tr>
					<tr><td>Prénom</td>		<td>:'. $res[0]['prenom'].'</td>     </tr>
					<tr><td>Profile</td>	<td>:'. $res[0]['profile'].'</td>    </tr>
					<tr><td>Etat Compte</td>		<td>:'. $res[0]['etat'].'</td> </tr>
					<tr><td>Default Site</td>		<td>:'. $res[0]['default_site'].'</td> </tr>
					<tr><td>Default Page</td>		<td>:'. $res[0]['default_page'].'</td> </tr>					
					</tbody>
				</table>';
 echo $retval;

?>
