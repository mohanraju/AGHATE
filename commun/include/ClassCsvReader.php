<?php
/*
Auteur MOHANRAJU
Class Data Validateur 
last modif le 28/10/2011
*/
Class CsvReader
{
	
	/*
	-------------------------------------------------------------------
	function LireStructure
	Lire le fichier structure 
	ATTn format de collone obligatoire
	-------------------------------------------------------------------
	*/
	function CsvReader($fichierStructure,$Seperateur=";") 
	{
		// le structure du Heading of CSV 
		$Heading= $this->Heading; 
		
		// lire le fihcier et mttre dan sun array	   
		if($CsvFile = file($fichierStructure))
		{
			//prepare col names			
	    //nombre de col dans le fichier structure
	    $keydata = explode($Seperateur,$CsvFile[0]);
	    $keynumb = count($keydata);
	    $Heading= $keydata;
	    
			// boucle sur le fichier et mettre dans un array
	    $NbrLignes = count($CsvFile);
	    $FinalData=array();
	    for($x=1; $x< $NbrLignes; $x++)
	    {
	    	$CsvFile[$x] = chop($CsvFile[$x]); //The chop() function will remove a white space or other predefined character from the right end of a string.
	      $CsvFile[$x] = str_replace($encl,'',$CsvFile[$x]);
	      $csv_data[$x] = explode($Seperateur,$CsvFile[$x]);
	      $i=0;
	      foreach($Heading as $key) 
	      {
	      	$FinalData[$x][$key] = $csv_data[$x][$i];
	        $i++;
        }   
	    }
		}
		else
		{
			Print "<br>Erreur reading Strcuture file ".$fichierStructure;
			return false;
		}
		echo "<pre>";
		print_r($FinalData);
		echo "</pre>";
		return $FinalData;	
	}	


}// FIN CLASS
?>
