<?php

echo '<script type="text/javascript" src="./commun/js/jquery-1.11.0.js"></script>';
echo '<script type="text/javascript" src="./commun/js/functions.js"></script>';


		//-------------------------------------------
		// mise a jour les NDA, si programmé	
		//-------------------------------------------
		
		for($i=0;$i < 10; $i++){	
			echo $i."<br>";
			// mise a jour TEMPS NDA dans forms/coadge a faire ici
			$TempNda='T00000041';
			$Nda='700000041';
			$Uh='999';
			echo "<script>res=LanceAjax('../commun/ajax/ajax_forms_update_temp_nda.php','temp_nda=".$TempNda."&nda=".$Nda."&uh=".$Uh."');</script>";
		}


?>

