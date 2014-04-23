<?php
require("./config/Const.inc.php");

Class DBSQL
{
     
   function DBSQL($DBName)
   {     
     global $DBHost,$DBUser,$DBPassword;
     $conn=mysql_connect($DBHost,$DBUser,$DBPassword) or die("Erruer connetion base de donnée  .<br />".mysql_error($conn));
	  mysql_select_db($DBName,$conn); 
	  $this->CONN = $conn;
     return true;
   }
   
   function select($sql="")
   {
      if (empty($sql)) return false;
      if (empty($this->CONN)) return false;
      $conn = $this->CONN;
      $results = mysql_query($sql,$conn) or die("Erruer Conncetion Sql <br /> : $sql .<br />".mysql_error($conn));
      if ((!$results) or (empty($results)))
      {       
         return false;
      }
      $count = 0;
      $data = array();
      while ($row = mysql_fetch_array($results)) {
         $data[$count] = $row;
         $count++;
      }
      mysql_free_result($results);
      return $data;
   }

   
   function insert($sql="")
   {
      if (empty($sql)) return false;
      if (empty($this->CONN)) return false;

      $conn = $this->CONN;	
      $results = mysql_query($sql,$conn) or die("Erruer Insert Sql <br > :$sql .<br />".mysql_error($conn));
      if (!$results) return false;
      $results = mysql_insert_id();
      return $results;
   }

   
   function update($sql="")
   {
      if(empty($sql)) return false;
      if(empty($this->CONN)) return false;

      $conn = $this->CONN;
      $result = mysql_query($sql,$conn) or die("Erruer Update Sql :$sql .<br />".mysql_error($conn));;
      return $result;
   }

   
   function delete($sql="")
   {
      if(empty($sql)) return false;
      if(empty($this->CONN)) return false;

      $conn = $this->CONN;
      $result = mysql_query($sql,$conn) or die("Erruer Delete Sql :$sql .<br />".mysql_error($conn));;
      return $result;
   }
   



}

?>
