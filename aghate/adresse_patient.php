<?php
function GetAdresse($nip){
		require("./config/connexion_gilda.php");		
		$addr1 = "Adresse 1";
		$addr2 = "Adresse 2";
		$addr3 = "Code postal";
		$addr4 = "Ville";
		$sql_addr="SELECT 
								COIDPA,
								COADPA,
						   ADREPA, 
						   LBCOPA, 
						   CDPOPA, 
						   LIACPA 
						FROM HAF 
						WHERE NOIP='$nip' 
						and cdpopa is not null 
						ORDER BY DACRAD DESC
						";
		if ($ConnGilda){
			$result = ociparse($ConnGilda, $sql_addr);
			ociexecute($result);
			while(ocifetch($result)){
				$addr1= ociresult($result, 1)." ".ociresult($result, 2);
				$addr2= ociresult($result, 3);
				$addr3= ociresult($result, 4);
				$addr4= ociresult($result, 5);
				$addr5= ociresult($result, 6);
				break;	
			}
			return		"$addr1 |$addr2 |$addr3 |$addr4 |$addr5 ";		
		}else{
			return	"Problème d'accès GILDA  |$addr2	 |$addr3 |$addr4 |$addr4";	
		}
	}
	
	//echo GetAdresse('1800025066');
?>
