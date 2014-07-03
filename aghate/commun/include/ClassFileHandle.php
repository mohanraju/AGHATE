<?php
Class FileHandle{
	public $Err;

	//-------------------------------------------------------------------------------------  
	// Lit un fichier, et le placer dans une chaîne
	//-------------------------------------------------------------------------------------  
	function ReadFile($filename){	
		$contents = "";
		if (is_file($filename)){
			if ($handle = fopen($filename, "r")){
				while (!feof($handle)) {
		  			$contents .= fread($handle, 1024);//.$this->EndOFLine;
				}
			}else{
				$this->Err = "Erreur d'ouverure de ficheir le fichier ou aucuen contenu dans le fihier :".$filename;
				echo "<br />".$this->Err;
				return false;
			}	
		}else{
			$this->Err = "Le fichier ou répertoire inexistence on inconnu      : ".$filename;
			echo "<br />".$this->Err;
			return false;
		
		}
		fclose($handle);
		return $contents;
	}

	//-------------------------------------------------------------------------------------  
	// MOVE un fichier
	//-------------------------------------------------------------------------------------  
	function MoveFile($Chemin_src,$Fic_src,$Chemin_trg,$Fic_trg){
		if (!is_dir($Chemin_src)) {
			$this->Err = "Chemin source is not a directory : ".$Chemin_src;
			echo "<br />".$this->Err;
			return false;
		}
		if (!is_dir($Chemin_trg)){ 
			$this->Err =  "Chemin target is not a directory : ".$Chemin_trg;
			echo "<br />".$this->Err;
			return false;
		}
			
		if (!is_file($Chemin_src.$Fic_src)){
			$this->Err =  "Fichier introuvable  : ".$Chemin_src.$Fic_src;
			echo "<br />".$this->Err;
			return false;
		}
		if(copy($Chemin_src.$Fic_src,$Chemin_trg.$Fic_trg )){
	    	$do = unlink($Chemin_src.$Fic_src);
	    	if($do=="1"){
						$ok="ok";
	    	} else { 
	    		$this->Err = "There was an error trying to delete the file.".$Chemin_src;
	    		echo "<br />".$this->Err;
	    	}
			return true;
		}else{
			$this->Err="Unable to copy file problem inconne (dorit d'ecriture ou lecture..? : ". $Chemin_src.$Fic_src;
			echo "<br />".$this->Err;
			return false;
		}	
	}
	//===================================
	// ecrire le trance dans un fichier 
	//====================================
	function WriteFile($fic,$msg){
		$retval="";
		$filename=$fic;
		if (!$handle = fopen($filename, 'a')) {
			echo "Impossible d'ouvrir le fichier ($filename)";
		}else{
			//$msg=date("Y-m-d h:m:s"). " - ".$msg."\n";
			$msg .="\n";
			if (fwrite($handle, $msg) === FALSE) {
				echo  "Impossible d'écrire dans le fichier ($filename)";
			}
		}
		fclose($handle);
	}
	
	//===================================
	// CreateFile
	//====================================
	function CreateFile($File){
		touch($File);
	}
	
	/*
	-------------------------------------------------------------------------------------  
	 ARRAY TO CSV
	 function Array2csv($Array,$FileName)
	 $Array : tableau deux dimensions
	 $FileName : avec chemin complet
	-------------------------------------------------------------------------------------  
	*/
	function Array2csv($Array,$FileName)
	{
		foreach($Array as $data)
		{
			$retval .= implode(";",$data) ;	
			$retval .= "\n";
		}
		$this->WriteFile($FileName,$retval);
	}
	
}
?>
