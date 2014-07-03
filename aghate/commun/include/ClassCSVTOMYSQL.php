<?php
/*
############################################################################################
#                                                                                          #
#		EXPORT CSV2MYSQL        
#   Date creation 07/01/2013                                                               #
#		Date dernière 02/05/2013			                                                          #
#   PAR MOHANRAJU @ SLS APHP PARIS                                                         #

		Description de projet
		Exports un Fichier CSV to Mysql 
			1. le structure sont crée atuomatiquement 
			
#    ***************  ATTN  ***********************
#    LE BASE DE DONNEE A MODIFIER DANS LE include/config.php
#    CETTE MODULE VA ANALYSER LE FIHCIER PUIS CREÉ LE TABLE ET INSERT LE DONNÉES
#    IL MODIFIE LE STRUCTURE DE LA TABLE AUTOMATIQUEMENT
#    
############################################################################################
*/
ini_set("memory_limit","1200M");
ini_set('max_execution_time', 0);


class CSVTOMYSQL
{
	var $db;
	Var $ColNames;
	var $ColType;
	var $ColNamesize;
	var $DropTable;
	var $AlterTable;
	
	//##############################################
	//Constructeur
	//##############################################
	function ExportCsv2Mysql($DropTable=false,$AlterTable=false){
		$this->DropTable=$DropTable;
		$this->AlterTable=$AlterTable;
	}
	
	//##############################################
	// ExportFile($CurrentFile)
	//##############################################
	function ExportFile($CurrentFile,$TableName,$DataBaseConnexion){
		if (is_file($CurrentFile)){
			if ($handle = fopen($CurrentFile, "r")){
				$count=0;
				while ($Data	=fgetcsv($handle,2048, ";")) {
					switch($count)
					{
						case 0:
								$this->ColNames=$Data;
							break;
						case 1:
							$this->CreateTable($TableName,$Data,$DataBaseConnexion);						
							$this->InsertData($TableName, $Data,$DataBaseConnexion);
							break;
						case ($count > 1):
							$this->InsertData($TableName, $Data,$DataBaseConnexion);
							break;
							
						default:
							echo "<br /><br />Finished";
							break;	
					}
					$count++;
	  			$Data="";
	  			//echo ",";
				}
			}		
		}else{
			Print "<br />ExportFile::Erreur d'ouverure de fichier ou aucun contenu dans le fichier :".$CurrentFile;
			exit;	
		}
		fclose($handle);
		
	}														
	//########################################################################
	// insert les données dans la base mysql
	//
	//########################################################################
	function InsertData($TableName, $Data,$db)
	{
	  $Col1=$Data;
	  $CurrentRow=$Data;
	  $NbrColNames=count($this->ColNames);
	  $InsertSql .="(";
	  $RowName .="(";
		// boucle sur les ColNames	
		for($c=0;$c < $NbrColNames ; $c++)
		{
			if ($c!=0){
				$InsertSql .=",";
				$RowName .=","; }
				// suprime le mot SET dans le date
				$CurrentRow[$c]=str_replace("EST","",$CurrentRow[$c]);
				// check null
				if(strlen($CurrentRow[$c]) < 1){
					$RowName .= $this->ColNames[$c];
					$InsertSql .=" NULL ";
				}				
				else{
					$RowName .=  $this->ColNames[$c];
					$InsertSql .= "'". addslashes($CurrentRow[$c]). "'";
				}
				// Check taille before insert si non on modfie le taille sur la table
				if(($this->ColNamesize[$c] < strlen($CurrentRow[$c])) and ($this->AlterTable) and ($this->ColType[$c] !="DATE")  )
				{
					 	$AlterSql="ALTER TABLE ".$TableName ." CHANGE ".$this->ColNames[$c]." ".  $this->ColNames[$c]. " ".  $this->ColType[$c]." (". strlen($CurrentRow[$c]) .")  ";
            $this->ColNamesize[$c] = strlen($CurrentRow[$c]);
				}
		}
		$RowName .= ")";
		$InsertSql .= ");";
		
		$InsertSql="INSERT INTO ".$TableName." ".$RowName. " VALUES ".$InsertSql;
		echo "<br />".$InsertSql		;
		echo $db->insert($InsertSql);
	}
	
	//########################################################################
	//create table structure tabel dans le base mysql
	//
	//########################################################################
	function CreateTable($TableName, $Col1,$db)
	{

		$CreateSql="";
		$NbrColNames=count($this->ColNames);
		for($c=0;$c < $NbrColNames ; $c++)
		{
			//minimum taille 
			if (strlen($Col1[$c])< 10) 
				$taille=10;
			else
				$taille=strlen($Col1[$c]);
				
			// check data types, default varchar
			$DataType=" VARCHAR ";

			// les variables avec mots TIME or DATE sera forcé  en date format
			
			if( (strpos(strtoupper($this->ColNames[$c]), 'TIME') > 0) or 	 (strstr(strtoupper($this->ColNames[$c]), 'DATE')> 0) )
			{
				$DataType=" DATE ";	
				$CreateSql .= $this->ColNames[$c] ." DATE  NULL default null,";
				$this->ColNamesize[$c]=$taille;
				$this->ColType[$c]="DATE";				
			}
			/*elseif( (is_numeric($Col1[$c])) and (strlen(trim($Col1[$c])) > 0 ) )
			{
				// les value  numerique dans le premier col sera forcé  en float
				$CreateSql .= $this->ColNames[$c]." FLOAT NULL  default null,";			
			}*/
			else
			{	
				// les restes sont forcé en Varchar
				$CreateSql .= $this->ColNames[$c].$DataType ."(".$taille.") NULL  default null, ";
				$this->ColNamesize[$c]=$taille;
				$this->ColType[$c]="VARCHAR";				
		 	}
		}
		//drop table if exists
	  if($this->DropTable)
	  {
			$DropTable ="DROP TABLE IF EXISTS  ".$TableName;
			//$db->execute($DropTable);
		}
		
 
		// create Tabel
		
		$CreateSql ="Create TABLE IF NOT EXISTS ".$TableName."   (id int(9) NOT NULL auto_increment,"
									.$CreateSql.
									" PRIMARY KEY  (`id`)
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		
		echo "<br /> ".$CreateSql;
		$db->execute($CreateSql);
	
 }
 
 
 
}// fin class
 
		
?>		
