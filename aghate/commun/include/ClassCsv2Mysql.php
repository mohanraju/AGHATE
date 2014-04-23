<?php
/*
############################################################################################
#                                                                                          #
#		EXPORT CSV2MYSQL        
#   Date creation 07/01/2013                                                               #
#   PAR MOHANRAJU @ SLS APHP PARIS                                                         #

		Description de projet
		Exports un Fichier CSV to Mysql 
			1. le structure sont crée atuomatiquement 
			
#    ***************  ATTN  ***********************
#    LA BASE DE DONNEE A MODIFIER DANS  include/config.php
#    CE MODULE VA ANALYSER LE FICHIER PUIS CREÉ LA TABLE ET INSERT LES DONNÉES
#    MODIFIE LA STRUCTURE DE LA TABLE AUTOMATIQUEMENT
# 
* Mise a jour le 28/10/2013   
* 
############################################################################################
*/
ini_set("memory_limit","1200M");
ini_set('max_execution_time', 0);


class Csv2Mysql
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
		$cpt_dbl = 0;
		$cpt_in = 0;
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
								//if ($this->SelectData($TableName, $Data,$DataBaseConnexion)) 
								//{					
									$this->InsertData($TableName, $Data,$DataBaseConnexion);
									$cpt_in++;
									break;
								//}
								/*else
								{
									echo "doublons";
									$cpt_dbl++;
								}*/
						case ($count > 1):
							//if ($this->SelectData($TableName, $Data,$DataBaseConnexion))
							//{
								$this->InsertData($TableName, $Data,$DataBaseConnexion);
								$cpt_in++;
								break;
							//}
							/*else
							{
								echo "doublons";
								$cpt_dbl++;
							}*/
						default:
							echo "<br /><br />Finished";
							break;	
					}
					$count++;
	  			$Data="";
	  			echo ",";
				}
			}		
		}else{
			Print "<br />ExportFile::Erreur d'ouverure de ficheir le fichier ou aucuen contenu dans le fihier :".$CurrentFile;
			exit;	
		}
	//	echo "<br />Nombre de doublons :".$cpt_dbl;
		echo "<br />Nombre d'insertions :".$cpt_in;;
		fclose($handle);
	}		
													
	//########################################################################
	// insert le data dans  dans le base mysql
	//
	//########################################################################
	function InsertData($TableName, $Data,$db)
	{
	$Col1=$Data;
	  $CurrentRow=$Data;
	  $NbrColNames=count($this->ColNames);
	  $InsertSql="";
		// boucle sur les ColNames	
		for($c=0;$c < $NbrColNames ; $c++)
		{
				// suprime le mot SET dans le date
				$CurrentRow[$c]=str_replace("EST","",$CurrentRow[$c]);
				// check null
				if(!empty($this->ColNames[$c]) && $this->ColNames[$c]!=" " 
					&& $this->ColNames[$c]!="" && isset($this->ColNames[$c]) )
				{
					if ($c==0){
						if(strlen($CurrentRow[$c]) < 1){
							$InsertSql .= $this->ColNames[$c]." = NULL ";		
						}		
						else{
							$InsertSql .= $this->ColNames[$c]." ='".addslashes($CurrentRow[$c])."'";
						}
					}
					else{
						if(strlen($CurrentRow[$c]) < 1){
							$InsertSql .= ",".$this->ColNames[$c]." = NULL ";		
						}		
						else{
							$InsertSql .= ",".$this->ColNames[$c]." ='".addslashes($CurrentRow[$c])."'";
						}
					}
				// Check taille before insert si non on modfie le taille sur la table
					if(($this->ColNamesize[$c] < strlen($CurrentRow[$c])) and ($this->ColType[$c] !="DATE")  ){
							echo "ALTER TABLE";
							$AlterSql="ALTER TABLE ".$TableName ." CHANGE ".$this->ColNames[$c]." ".  $this->ColNames[$c]. " ".  $this->ColType[$c]." (". strlen($CurrentRow[$c]) .")  ";
							$db->execute($AlterSql);  
							//remettre le nouveau taille dans
							$this->ColNamesize[$c] = strlen($CurrentRow[$c]);
					}
				}
		}
		$InsertSql="INSERT INTO ".$TableName. " SET ".$InsertSql;
		echo "<br />".$InsertSql."<br />"		;
		echo "<br /><br /><br /><br /><br /><br />";
		$db->insert($InsertSql);
	}
	
	
	//########################################################################
	// select data dans  dans le base mysql
	//
	//########################################################################
	function SelectData($TableName, $Data,$db)
	{
	  $Col1=$Data;
	  $CurrentRow=$Data;
	  $NbrColNames=count($this->ColNames);
	  $SelectSql="";
		// boucle sur les ColNames	
		for($c=0;$c < $NbrColNames ; $c++)
		{
			if ($c==0)
			{
				$SelectSql .= "";
			}
				// suprime le mot SET dans le date
				$CurrentRow[$c]=str_replace("EST","",$CurrentRow[$c]);
				// check null
				if(!empty($this->ColNames[$c]) && $this->ColNames[$c]!=" " 
					&& $this->ColNames[$c]!="" && isset($this->ColNames[$c]) )
				{
					if ($c==0)
					{
						if(strlen($CurrentRow[$c]) < 1)
						{
							$SelectSql .= $this->ColNames[$c]." IS NULL ";
						}		
						else
						{
							$SelectSql .= $this->ColNames[$c]." ='".addslashes($CurrentRow[$c])."'";
						}
					}
					else
					{
						if(strlen($CurrentRow[$c]) < 1)
						{
							$SelectSql .= "AND ".$this->ColNames[$c]." IS NULL ";
						}		
						else
						{
							$SelectSql .= "AND ".$this->ColNames[$c]." ='".addslashes($CurrentRow[$c])."'";
						}
					}
				}
		}
		$SelectSql = "SELECT * FROM ".$TableName." WHERE ".$SelectSql;
		echo "<br />".$SelectSql."<br />";
		$res = $db->select($SelectSql);
		//echo count($res);
		if(count($res)>0)
		{
			return false;
		}
		else
		{
			return true;
		}
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
			if ($c==$NbrColNames-1)
			{
				if(!empty($this->ColNames[$c]) && $this->ColNames[$c]!=" " && $this->ColNames[$c]!="" && isset($this->ColNames[$c]) )
				{
					if( (strpos(strtoupper($this->ColNames[$c]), 'TIME') > 0) or 	 (strstr(strtoupper($this->ColNames[$c]), 'DATE')> 0) )
					{
						$DataType=" DATE ";	
						$CreateSql .= $this->ColNames[$c] ." DATE  NULL default null";
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
						$CreateSql .= $this->ColNames[$c].$DataType ."(".$taille.") NULL  default null ";
						$this->ColNamesize[$c]=$taille;
						$this->ColType[$c]="VARCHAR";				
					}
				}
			}
			else
			{
				if(!empty($this->ColNames[$c]) && $this->ColNames[$c]!=" " && $this->ColNames[$c]!="" && isset($this->ColNames[$c]) )
				{
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
						if (empty($this->ColNames[$c+1]))
						{
						 	$CreateSql .= $this->ColNames[$c].$DataType ."(".$taille.") NULL  default null";
							$this->ColNamesize[$c]=$taille;
							$this->ColType[$c]="VARCHAR";
						}
						else
						{
							$CreateSql .= $this->ColNames[$c].$DataType ."(".$taille.") NULL  default null, ";
							$this->ColNamesize[$c]=$taille;
							$this->ColType[$c]="VARCHAR";
						}
						// les restes sont forcé en Varchar
										
					}
				}
			}
		}
		//drop table if exists
	  if($this->DropTable)
	  {
			$DropTable ="DROP TABLE IF EXISTS  ".$TableName;
			$db->execute($DropTable);
		}
		
 
		// create Tabel
		/*$CreateSql ="Create TABLE IF NOT EXISTS ".$TableName."   (id int(9) NOT NULL auto_increment,"
									.$CreateSql.
									" PRIMARY KEY  (`id`)
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";*/
		
		$CreateSql ="Create TABLE IF NOT EXISTS ".$TableName."   ("
									.$CreateSql.
									"
									) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
		
		echo "<br />Structure =><br /> ".$CreateSql;
		$db->execute($CreateSql);
	
 }
 
 
 
}// fin class
 
		
?>		
