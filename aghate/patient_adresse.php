<?php
function GetAdresse($nip){
		require("./config/connexion_gilda.php");		
		$addr1 = "Adresse 1";
		$addr2 = "Adresse 2";
		$addr3 = "Adresse 3";
		$addr4 = "Adresse 4";
		$addr5 = "Code postal";
		$addr6 = "Ville ";
		$sql_addr="SELECT 
						   COIDPA,
						   COADPA,
						   ADREPA, 
						   LBCOPA, 
						   CDPOPA, 
						   LIACPA 
						FROM HAF 
						WHERE NOIP='$nip' 
						ORDER BY DACRAD DESC
						";
		if ($ConnGilda){
	
			$result = ociparse($ConnGilda, $sql_addr);
			ociexecute($result);
			while(ocifetch($result)){
				$addr1= ociresult($result, 1);
				$addr2= ociresult($result, 2);
				$addr3= ociresult($result, 3);
				$addr4= ociresult($result, 4);
				$addr5= ociresult($result, 5);
				$addr6= ociresult($result, 6);										
				break;	
			}
			return 	"$addr1 |	$addr2 |	$addr3 |	$addr4 |	$addr5 |	$addr6 ";		
		}else{
			return 	"Problème d'accès GILDA  |$addr2	 |	$addr3 |	$addr4 |	$addr5 |	$addr6 ";	
		}
	}
?>
