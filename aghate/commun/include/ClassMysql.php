<?php
/*
#############################################################################
#                         Class MySQL.php    								#    
#             Classe qui permet d'effectuer des reqêtes SQL 				#
#				Utilise PDO													#
#              Mise a jour le 28/10/2013                        			#
#                                                   						#
#                                                   						#
#############################################################################
*/

Class MySQL  
{
	var $Status;
	var $Erreur="";  
	Var $CONN;
	var $Trace;
	   
	// Connexion avec la base de donnée en utilisant PDO
	function MySQL()
	{     
		if(!class_exists('PDO'))
		{
			echo "Mysql :  [Err 101] L'extension PDO ne sont pas disponibles , veuillez activer l'extension PDO";
			exit;			
		}
		global $DBHost,$DBUser,$DBPassword,$DBName;
		$DNS="mysql:dbname=$DBName;host=$DBHost";
		try {
				$conn=new PDO($DNS,$DBUser,$DBPassword, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
			} 
		catch (PDOException $e){
					$msg = 'ERREUR PDO dans ' . $e->getFile() . ' L.' . $e->getLine() . ' : ' . $e->getMessage()."\n";
					$this->Trace .= $msg;
					exit;
					}				
		$this->CONN = $conn;
	}
	   
	   
	/*=======================================================================
	* Fonction Select ($sql)
	* $sql = requête
	* Renvoie le résultat de la requete dans un tableau 
	* ======================================================================*/
	function select($sql="")
	{
	
		if (empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		$conn = $this->CONN;

		try{
			$results = $conn -> query ($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>Executing SQL<br>". $sql."<br> ".$e->getMessage()."\n";
			$this->Trace .= $Err."\n";
			echo $Err;
			exit;
		}	

		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>Executing SQL<br>". $sql."<br> ".var_dump($error)."\n";
			$this->Trace .= $Err."\n";
			echo $Err;
			return false;
		} 
		else 
		{
		  $count = 0;
		  $data = array();
		  while ($row = $results-> fetch())
		  {
			unset($NewRow);
			foreach($row as $key=>$val){
				$NewRow[$key]=stripslashes($val); 
			}
			$data[$count] = $NewRow;
			$count++;
		  }
		  $results -> closeCursor();
		  return $data;
		}
	}


	/*=======================================================================
	* Fonction select single colonne et retoure le colonne selstionné
	* $sql = requête
	* Renvoie le résultat de la requete dans un tableau 
	* ======================================================================*/
	function selectCol($sql="")
	{
	
		if (empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		$conn = $this->CONN;

		try{
			$results = $conn -> query ($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>Executing SQL<br>". $sql."<br> ".$e->getMessage()."\n";
			$this->Trace .= $Err."\n";
			echo $Err;
			exit;
		}	

		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>Executing SQL<br>". $sql."<br> ".var_dump($error)."\n";
			$this->Trace .= $Err."\n";
			echo $Err;
			return false;
		} 
		else 
		{
			$row = $results-> fetch();
			$data = $row[0];
			$results -> closeCursor();
		  return $data;
		}
	}
	
	
	/*========================================================================
	* Fonction insertion ($nomTable,$data)
	* $nomTable = Nom de la table 
	* $data[] 	= tableau[clé]= tableau[valeur]
	* Renvoie le dernier id inséré
	* =====================================================================*/
	function insertion($nomTable="",$data="",$nomVariable)
	{

		if (empty($data) or empty($nomTable)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		//On crée la requete qui  permetre d'inserer les données
		$requete='INSERT INTO '.$nomTable.' SET ';
		$compteur=0;
		
		foreach($data as $cle => $valeur){
			if($compteur==0)
				$champs.= $cle."='".addslashes($valeur)."'";
			else
				$champs.=",".$cle."='".addslashes($valeur)."'";
			$compteur ++;
		}
		$requete=$requete.$champs;	
		//echo "<br>".$requete;
		try{
			//$this->CONN->beginTransaction();
			$results = $this->CONN->query($requete);
			//$this->CONN->commit();
			$stmt=NULL;
		}catch(Exception $e){
			$Err ="Erreur :=>SQL Insert <br>".$requete."<br>".$e->getMessage();
			$this->Trace .= $Err;
			echo $Err;
			$conn->rollBack();
			$results=0;
			exit;
		}
		
		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>SQL Insert <br>".$requete."<br>".var_dump($error);
			$this->Trace .= $Err;
			echo $Err;
			return false;
		} 
		else
		{
			$id=$this->GetLastInsertId($nomTable,$nomVariable);
			if(strlen($id) < 1)
				$id = $this->CONN->lastInsertId(); 		  
		}  
		
		return $id;
	}
	
	/*========================================================================
	function GetLastInsertId($nomTable,$nomVariable="")
	* si nom variable existe retourne la value max de nomvariable dans la table
	* =====================================================================*/
	function GetLastInsertId($nomTable,$nomVariable=""){
		$sql = "SELECT MAX(id) as last_id FROM ".$nomTable;
		if(strlen($nomVariable)>0){
			$sql = "SELECT MAX(".$nomVariable.") as last_id FROM ".$nomTable;
		}
		$res = $this->select($sql);
		return $res[0]['last_id'];
	}
		   
	/*
	 * ========================================================================
	* Fonction insert ($sql)
	* $sql = requête
	* Effectue la requete
	* Renvoie le dernier id inséré 
	* =====================================================================
	* */
	function insert($sql="")
	{
		if (empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		try
		{
			$results = $this->CONN->query($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>SQL Insert <br>".$sql ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;
			exit;
		}

		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>SQL Insert <br>".$sql ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;			
			return false;
		} 
		else
		{
			$id = $this->CONN->lastInsertId(); 
		}  
			
		return $id;
	}

	   
		
	/*========================================================================
	* Fonction update_ ($nomTable,$data,$condition)
	* $sql = $nomTable = nom de la table
	* 		 $data = Tableau contenant les données (clé => valeur)
	* 		$condition = Tableau contenant les conditions (where)
	* Effectue la requete 
	* =====================================================================*/
	function update_($nomTable="",$data="",$condition="")
	{
		if (empty($data) or empty($nomTable)) return false;
		if (empty($this->CONN))
		{
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		//On crée la requete qui va permetre d'inserer les données
		$requete='UPDATE  '.$nomTable.' SET ';
		$compteur=0;
		foreach($data as $cle => $valeur){

			if($compteur==0){
				$champs.= $cle."='".$valeur."'";
			}
			else{
				$champs.=",".$cle."='".$valeur."'";
			}
			$compteur ++;
		}
		
		$requete=$requete.$champs;	
		
		if (!empty($condition)){
			$requete=$requete." WHERE ";
			foreach($condition as $key => $val){
				if($cmpt_cd==0){
					$cd_ch.= $key."='".$val."'";
				}
				else{
					$cd_ch.=" AND ".$key."='".$val."'";
				}
				$cmpt_cd++;
			}
		}
		$requete=$requete.$cd_ch;	
		try{
			$this->CONN->beginTransaction();
			$results = $this->CONN->query($requete);
			$this->CONN->commit();
			$stmt=NULL;
		}catch(Exception $e){
			$Err ="Erreur :=>SQL Update <br>".$sql ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			exit;
		}
		
		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>SQL Update <br>".$sql ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;			
			return false;
		} 
		else
		{
		   return true;
		}  
			  
	}
	   
	/*=============================================================
	* Fonction update($sql)
	* $sql = reqûete
	* Effectue la requete 
	* ===========================================================*/
	function update($sql="")
	{
		if(empty($sql)) return false;
 	
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 

		$conn = $this->CONN;
		try{
			// attenetion EXEC return only number of rows affected
			$results = $conn -> exec ($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>SQL Update <br>".$sql ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			exit;
		}
  
		return $results;
	}
	   
	   
	/*=============================================================
	* Fonction delete_($sql)
	* $sql = reqûete
	* Effectue la requete 
	* ===========================================================*/
	function delete_($sql="")
	{
		if(empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		$conn = $this->CONN;
		try{
			$results = $conn -> query ($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>SQL delete <br>".$sql ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			exit;
		}
		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>SQL delete <br>".$sql ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;			
			return false;
		} 
		return $results->rowCount();
	}

	   
	 /*=============================================================
	* Fonction drop_table($table_name,$db_name)
	* $table_name = nom de la table
	* $db_name = nom de la base
	* supprime la table
	* ===========================================================*/   
	function drop_table($table_name,$db_name="")
	{
		if(empty($table_name)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		  
		$conn = $this->CONN; 
		if (empty($db_name))
			$requete = "DROP TABLE IF EXISTS ".$table_name;
		else
			$requete = "DROP TABLE IF EXISTS ".$db_name.".".$table_name;

		try{
			$results = $conn -> query ($requete);
		}catch(Exeception $e){
			$Err ="Erreur :=>SQL drop table <br>".$requete ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			exit;			  
		}
	      
	     if ((!$results) or (empty($results)))
	     { 
			$Err ="Erreur :=>SQL drop table <br>".$requete ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;
	        return false;
	     } 
	        	
	       return $results;
	  }

	/*=============================================================
	* Fonction truncate($table_name,$db_name)
	* $table_name = nom de la table
	* $db_name = nom de la base
	* vide la table
	* ===========================================================*/   
	function truncate($table_name,$db_name="")
	{
		if(empty($table_name)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
	  
		$conn = $this->CONN;
	  
		if (empty($db_name))
			$requete = "TRUNCATE TABLE ".$table_name;
		else
			$requete = "TRUNCATE TABLE ".$db_name.".".$table_name;
		
		try{
			$results = $conn -> query ($requete);
		}catch(Exeception $e){
			$Err ="Erreur :=>SQL Truncate <br>".$requete ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			exit;				
		}
	  
		if ((!$results) or (empty($results)))
		{ 
			$Err ="Erreur :=>SQL Truncate <br>".$requete ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;
	        return false;
		} 
			
	   return $results;
	}
	   

	    
	/*=============================================================
	* Fonction create($sql)
	* Execute la requete sql
	* ===========================================================*/   
	function create($sql="")
	{
		if(empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 

		$conn = $this->CONN;

		$result = $conn -> query ($sql);
		return $result;
	}
	
	/*=============================================================
	* Fonction delete($sql)
	* Execute la requete sql
	* ===========================================================*/   
	function delete($sql="")
	{
		if(empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 

		$conn = $this->CONN;
		$result = $conn -> query ($sql);
		return $result;
	}

	 
	   
	/*=============================================================
	* Fonction execute($sql)
	* Execute la requete sql
	* ===========================================================*/   
	function execute($sql="")
	{
		if (empty($sql)) return false;
		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 
		$conn = $this->CONN;
		try{
			$results = $conn -> exec ($sql);
		}catch(Exeception $e){
			$Err ="Erreur :=>Executing SQL<br>". $sql."<br> ".$e->getMessage()."\n";
			$this->Trace .= $Err."\n";
			echo $Err;
			exit;
		}	
		return $results;


	}   

 	   
	    
	/*=============================================================
	* Fonction CreateTable($TableName,$TableStructure,$Engine="")
	* Creer une table en fonction des paramètres envoyés
	* ===========================================================*/   
	function CreateTable ($TableName,$TableStructure,$Engine=""){

		if (empty($this->CONN)){
			$Err ="Erreur :=>Aucune connexion MySQL trouvée\n";
			$this->Trace .= $Err."\n";
			echo $Err;			
			exit;
		} 

		$requete.="CREATE TABLE IF NOT EXISTS `".$TableName."`";
		$requete.=" ( ".$TableStructure." )";
		if (strlen($Engine)>1) {
			$requete.=" ENGINE=`".$Engine."` ";
		}
		$requete.=" ; ";
		try{
			$results = $this->CONN->query($requete);
		}catch(Exception $e){
			$Err ="Erreur :=>SQL Truncate <br>".$requete ."<br>".$e->getMessage()."\n";
			$this->Trace .=$Err;
			echo $Err;			
			$conn->rollBack();
			exit;
		}

		if ((!$results) or (empty($results))){ 
			$Err ="Erreur :=>SQL Truncate <br>".$requete ."<br>".var_dump($error)."\n";
			$this->Trace .=$Err;
			echo $Err;
	        return false;
		 }
		return $results;
	}
	   
	/*=============================================================
	* Fonction GetSchemaInformation($TableName,$Database="",$Extra="",$ColumDefault="",$IsNullable="")
	* Creer une table en fonction des paramètres envoyés
	* ===========================================================*/   
	function GetSchemaInformation ($TableName,$Database="") {
	   
		if (empty($this->CONN))
		{
			$this->Trace .= "Aucune connexion MySQL trouvée"."\n";
			echo "<br /> No connexion";			
			exit;
		} 
		$requete.= "SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '".$TableName."'";

		if(strlen($Database)>1) {
			$requete.= "AND TABLE_SCHEMA = '".$Database."'";
		}

		if($Extra==true)
			$requete.= "AND TABLE_SCHEMA = '".$Database."'";

		$schema_info = $this->select($requete);

		return $schema_info;
					 
	}
	
	/*=============================================================
	* Fonction PrepareStructureString ($schema_info,$IsNullable="",$ColumDefault="",$Extra="") {
	* Creer un string contenant la structure en fonction des paramètres envoyés
	* ===========================================================*/   

	function PrepareStructureString ($schema_info,$IsNullable="",$ColumDefault="",$Extra="") 
	{

		$nb_info = count($schema_info);
		$structure="";
		for($i=0;$i<$nb_info;$i++){	
			$column_type = $schema_info[$i]['COLUMN_TYPE'];
			if ($schema_info[$i]['COLUMN_TYPE']=='text'){
				$column_type = 'varchar(30)';
			}

			$structure.= "`".$schema_info[$i]['COLUMN_NAME']."` ".$column_type;

			if($IsNullable==true){
			if ($schema_info[$i]['IS_NULLABLE']=='NO')
				$structure.=" NOT NULL";
			}

			if ($ColumDefault==true){
				$colum_length = strlen($schema_info[$i]['COLUMN_DEFAULT']);
				if ($colum_length==0 && $schema_info[$i]['IS_NULLABLE']!='NO')
				{
					$structure.=" DEFAULT NULL";
				}
				elseif ($colum_length>0 && $colum_length < 4 )
				{
					$structure.=" DEFAULT `".$schema_info[$i]['COLUMN_DEFAULT']."`";
				}
				elseif($colum_length>4)
				{
					$structure.=" DEFAULT ".$schema_info[$i]['COLUMN_DEFAULT'];
				}
			}

			if ($Extra==true){
				$structure.=" ".$schema_info[$i]['EXTRA'];
			}

			if($i!=$nb_info-1)
				$structure.= ",";

			$structure.="\n";
		}
		return $structure;
	}
	   
}

?>
