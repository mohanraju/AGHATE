<?php

Class MySQL  
{
		var $Status;
	   var $Erreur="";  
	   Var $CONN;
	   function MySQL()
	   {     
    		global $DBHost,$DBUser,$DBPassword,$DBName;	   	
		    if ($conn=mysql_connect($DBHost,$DBUser,$DBPassword)){
			  	mysql_select_db($DBName,$conn) or die("Mysql::Erruer selection base: $DBName .<br>".mysql_error($conn)); 
			  	$this->CONN = $conn;
		      return true;
	   		}else{
	   	 		echo "Mysql::Impossible de connecter le base, veuillez vérifier config.php ";
	   	 		exit;
	   	 		return false;
	   		}
	   }
	   
	   function select($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	      $conn = $this->CONN;
	      $results = mysql_query($sql,$conn) or die("Mysql::Erruer Sql1 :$sql .<br>".mysql_error($conn));
	      if ((!$results) or (empty($results)))
	      {       
	         return false;
	      }

	      $count = 0;
	      $data = array();
	      while ($row = mysql_fetch_array($results,MYSQL_ASSOC)) {
	         $data[$count] = $row;
	         $count++;
	      }
	      mysql_free_result($results);
	      return $data;
	   }
	
	   function execute($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	      $conn = $this->CONN;
	      $results = mysql_query($sql,$conn) or die("Mysql::Erruer Sql :$sql .<br>".mysql_error($conn));
	      if ((!$results) or (empty($results)))
	      {       
	         return false;
	      }
	      return $results;
	      
	   }
	
	   
	   function insert($sql="")
	   {
	      if (empty($sql)) return false;
	      if (empty($this->CONN)) return false;
	
	      $conn = $this->CONN;	
	      $results = mysql_query($sql,$conn) or die("Mysql::Erruer Sql :$sql .<br>".mysql_error($conn));
	      if (!$results) return false;
	      $results = mysql_insert_id();
	      return $results;
	   }
	
	   
	   function update($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	
	      $result = mysql_query($sql,$conn) or die("Mysql::Erruer Sql :$sql .<br>".mysql_error($conn));;
	      return $result;
	   }
	
	   
	   function create($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	
	      $result = mysql_query($sql,$conn) or die("Mysql::Erruer Sql :$sql .<br>".mysql_error($conn));;
	      return $result;
	   }
	
	   
	   function delete($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	      $result = mysql_query($sql,$conn) or die("Mysql::Erruer Sql :$sql .<br>".mysql_error($conn));;
	      return $result;
	   }
	   
	   
	   function ViderTable($sql="")
	   {
	      if(empty($sql)) return false;
	      if(empty($this->CONN)) return false;
	
	      $conn = $this->CONN;
	      $result = mysql_query($sql,$conn);
	      return $result;
	   }
	   

	
}
	

?>
