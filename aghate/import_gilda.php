<?php
//Export Gilda
ini_set("display_errors",1);
ini_set("max_execution_time",0);

include "./commun/include/ClassSftp.php";
include "./config/config_ftp.php";

 // Mise en place d'une connexion basique
$conn_id = ftp_connect($host);

// Identification avec un nom d'utilisateur et un mot de passe
if(!$login_result = ftp_login($conn_id, $user, $mdp))
{
	echo "Connexion faillure !!";
	exit;
	}

//  set dossier serveur
$dosseir_serveur="aghate.lan/gilda";
if (!ftp_chdir($conn_id, $dosseir_serveur)) 
{
    echo "<br />Impossible de changer de dossier : $dosseir_serveur \n";
}
echo "<br />Dossier serveur : " . ftp_pwd($conn_id) . "\n";
 $dosseir_serveur=ftp_pwd($conn_id);

// set local dossier
$dosseir_local="C:\\wamp\\www\\AGhATE\\gilda";

chdir($dosseir_local);

// dossier courant
echo "<br />Dossier Local :".getcwd() . "\n";


// get files
$files[]="loc.csv";
$files[]="mvt.csv";

for($i=0; $i < count($files);$i++)
{
	if (ftp_get($conn_id, $files[$i], $files[$i], FTP_BINARY)) {
		echo "<br />Le fichier $files[$i] a ete recupre \n";
	} else {
		echo "<br />Il y a un problème\n";
	}
}
// Fermeture de la connexion
ftp_close($conn_id);

?>
