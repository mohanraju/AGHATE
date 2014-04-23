<?php
/********************************************************************************************
*                                                                               	        *
*                                http:\\sylvain.guernion.free.fr                  	        *
*                                                                               	        *
*                                    Classe de connection Sql                  	          	*
*                                                                             	            *
*                                                                           	            *
*                                                                                         	*
*                                                                        	                *
*                              	  Email : gsylvain35@free.fr                         	    *
********************************************************************************************/
/********************************************************************************************
*																						  	*
*																							*
* Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou 					*
*le modifier au titre des clauses de la Licence Publique Générale GNU, telle que publiée 	*
*par la Free Software Foundation ; soit la version 2 de la Licence, ou (à votre discrétion) *
*une version ultérieure quelconque. Ce programme est distribué dans l'espoir 				*
*qu'il sera utile, mais SANS AUCUNE GARANTIE ; sans même une garantie implicite 			*
*de COMMERCIABILITE ou DE CONFORMITE A UNE UTILISATION PARTICULIERE. 						*
*Voir la Licence Publique Générale GNU pour plus de détails.								*
*																							*
*					http://fsffrance.org/gpl/gpl-fr.fr.html									*
********************************************************************************************/

/**
 * Sql.class
 * 
 * @name Sql.class
 * @author Guernion Sylvain <gsylvain35.free.fr>
 * @version 2.0
 * @package classes
 * @subpackage php4
 */
class Sql {
	
	/**
     * 
     * 
     * @access private
     * @var array
     */
	var $db;
	
	/**
     * 
     * 
     * @access public
     * @var resource
     */
	var $db_result;
	/**
     * 
     * 
     * @access public
     * @var integer
     */
	var	$db_num_row;
	
	
	/**
	* Constructeur
	*
	* @name  Sql
	* @param String		$host 	adresse du serveur
	* @param String		$user 	utilisateur
	* @param String		$passwd	mot de passe
	* @param String		$db		nom de la base
	* @param boolean	$create	création de la base si elle n'existe pas	
	*/
	function Sql(){
		$host="localhost";
		$user="root";
		$passwd="";
		$db="gestion_salles";
		
			$this->db['serveur'] = $host;
			$this->db['user'] = $user;
			$this->db['pass'] = $passwd;
			$this->db['base'] = $db;
			$this->db_connection();
			$this->select_db();
			if (!$this->db['handler'] && $create){
				$this->create_db();
			}	
			if(!$this->db['handler']) $this->erreur("select","impossible de selectionner la base","");
	
	}
	
	/**
	* ouverture d'une connexion
	*
	* @name  db_connection
	* @access public
	*/
	function db_connection(){
		$this->db['connexion']=@mysql_connect($this->db['serveur'],$this->db['user'],$this->db['pass']) 
				or $this->erreur("connexion impossible",mysql_error(),mysql_errno());
	}
	
	
	/**
	* selection de la base
	*
	* @name  select_db
	* @access public
	* @param String		$db 	nom de la base
	*/
	function select_db($db=""){
		if($db!=""){
			$this->db['base'] = $db;
		}
		$this->db['handler'] = @mysql_select_db($this->db['base'],$this->db['connexion']);
	}
	
	
	/**
	* liste les bases de données du serveur
	*
	* @name  drop_db
	* @access public
	* @return String
	*/
	function liste_db(){
		$list = mysql_list_dbs();
		while ($row = mysql_fetch_array($list,MYSQL_NUM)){
			$db_list[]=$row[0];
		}
		return $db_list;
	}
	
	/**
	* supprime une base
	*
	* @name  drop_db
	* @access public
	* @param String		$db 	nom de la base
	*/
	function drop_db($db){
		mysql_query("DROP DATABASE $db") or $this->erreur("DROP DATABASE $db",mysql_error(),mysql_errno());  
	}
	
	/**
	* creation d'une base
	*
	* @name  create_db
	* @access public
	*/
	function create_db(){
		mysql_query("CREATE DATABASE ".$this->db['base']) or $this->erreur("create_db",mysql_error(),mysql_errno());  
		$this->select_db();
	}
	
	
	/**
	* fermeture de la connexion
	*
	* @name  close_db
	* @access public
	*/
	function close_db() {  
		mysql_close($this->db['connexion']);  
	}
	
	/**
	* creation de table
	*
	* @name  create_table
	* @access public
	* @param String			$table		nom de la table
	* @param String	| array	$tblStruct	structure de la table
	*/
	function create_table($table,$tblStruct) {  
			if (is_array($tblStruct)) $theStruct=implode(",",$tblStruct); else $theStruct=$tblStruct;  
			mysql_query("create table $table ($theStruct)") or $this->erreur("create table $table ($theStruct)",mysql_error(),mysql_errno());  
	} 
	
	//ajout d'un champ dans une table
	function add_field_table($table,$nom_field,$param_field) {  
		mysql_query("ALTER TABLE `".$table."` ADD `".$nom_field."` $param_field") 
			or $this->erreur("ALTER TABLE `".$table."` ADD `".$nom_field."` $param_field",mysql_error(),mysql_errno());
	} 
	
	/**
	* supprime une table
	*
	* @name  drop_table
	* @access public
	* @param String			$table		nom de la table
	*/
	function drop_table($table) {  
		mysql_query("drop table if exists $table") 
			or $this->erreur("drop table if exists $table",mysql_error(),mysql_errno());  
	}  
	
	/**
	* executer une requéteb
	*
	* @name  db_query
	* @access public
	* @param String		$sql_stat		requéte
	*/
	function db_query($sql_stat) {  
			$this->db_result=mysql_query($sql_stat) or $this->erreur($sql_stat,mysql_error(),mysql_errno());  
			$this->db_num_row=mysql_num_rows($this->db_result);  
	}  
	
	
	/**
	* Selection
	*
	* @name  select
	* @access public
	* @param String	| array	$fields		nom des champ(s)
	* @param String			$table		nom de la table
	* @param String			$where		condition
	* @param String			$order_by
	* @param String			$group_by
	* @param String			$having
	* @param String			$limit	
	* @return String
	*/	
	function select($fields,$tables,$where="",$order_by="",$group_by="",$having="",$limit="") {  
			
			if (is_array($fields)) $theFields=implode(",",$fields); else $theFields=$fields;			
			
			$sql_stat=" select $theFields from $tables ";  
			  
			if (!empty($where)) $sql_stat.="where $where ";  
			if (!empty($group_by)) $sql_stat.="group by $group_by ";  
			if (!empty($order_by)) $sql_stat.="order by $order_by ";  
			if (!empty($having)) $sql_stat.="having $having ";  
			if (!empty($limit)) $sql_stat.="limit $limit ";  
			
			$this->db_query($sql_stat);
			
			return $sql_stat;
	}  
	
	
	/**
	* recupérer les resultats dans un tableau
	*
	* @name  db_result_ToArray
	* @access public
	* @param String			$type		MYSQL_ASSOC(indexé par le nom des champs)
	*									MYSQL_BOTH(indexé par le nom des champs et par numéro)
	*									MYSQL_NUM(indexé par numéro)
	* @return array
	*/	
	function db_result_ToArray($type=MYSQL_ASSOC){
		/*$type -> MYSQL_ASSOC ,MYSQL_BOTH,MYSQL_NUM*/
		$array_result=array();
		while ($row = mysql_fetch_array($this->db_result,$type)) {
			$array_result[]=$row;
			
		}
		return $array_result;
	}
	
	/**
	* Insertion
	*
	* @name  insert_aray
	* @access public
	* @param String			$table		nom de la table
	* @param String	|array	$fields		nom des champs
	* @param String	|array	$values		valeurs
	*/	
	function insert($query) {  
		  
			mysql_query($query) or $this->erreur("insert",mysql_error(),mysql_errno());  
	}  
  	
	/**
	* Insertion
	*
	* @name  insert_aray
	* @access public
	* @param String			$table		nom de la table
	* @param String	|array	$fields		nom des champs
	* @param String	|array	$values		valeurs
	*/	
	function insert_array($table,$fields="",$values="") {  
			$sql_stat="insert into $table ";  
			  
			if (is_array($fields)) $theFields=implode(",",$fields); else $theFields=$fields;  
			if (is_array($values)) $theValues="'".implode("','",$values)."'"; else $theValues=$values;  
			  
			$theValues=str_replace("'now()'","now()",$theValues);  
			  
			if (!empty($theFields)) $sql_stat.="($theFields) ";  
			$sql_stat.="values ($theValues)";  
			  
			mysql_query($sql_stat) or $this->erreur("Insert :",mysql_error(),mysql_errno());  
	}  
  
	
	/**
	* Update
	*
	* @name  update
	* @access public
	* @param String			$table		nom de la table
	* @param String	|array	$newvals	nouvelle(s) valeur(s)
	* @param String			$where		condition
	*/	
	function update($table,$newvals,$where="") {  
			if (is_array($newvals)) $theValues=implode(",",$newvals); else $theValues=$newvals;  
			  
			$sql_stat="update $table set $theValues";  
			  
			if (!empty($where)) $sql_stat.=" where $where";  
			mysql_query($sql_stat) or $this->erreur("update",mysql_error(),mysql_errno());  
	}  
	
	
	/**
	* Delete
	*
	* @name  delete
	* @access public
	* @param String		$table		nom de la table
	* @param String		$where		condition
	*/		  
	function delete($table,$where="") {  
			  
			$sql_stat="delete from $table ";  
			  
			if (!empty($where)) $sql_stat.="where $where ";  
			  
			mysql_query($sql_stat) or $this->erreur("delete",mysql_error(),mysql_errno()); 
	}  
	
	
	/**
	* 
	*
	* @name  result
	* @access public
	* @param Integer	$recno		numéro
	* @param String		$field		champ
	* @return String
	*/	
	function result($recno,$field) {  
			return mysql_result($this->db_result,$recno,$field);  
	} 
	
	
	/**
	* recupére la ligne courante dans un tableau indexé par numéro
	*
	* @name fetch_row
	* @access public
	* @return array
	*/
	function fetch_row() {  
			return mysql_fetch_array($this->db_result,MYSQL_NUM);   
	} 
	
	
	/**
	* recupére la ligne courante dans un tableau indexé par le nom des champs
	*
	* @name fetch_array
	* @access public
	* @return Integer
	*/
	function fetch_array() {  
			return mysql_fetch_array($this->db_result,MYSQL_ASSOC);  
	} 	
	
	
	/**
	* indique le nombres de champs du resultat
	*
	* @name num_fields
	* @access public
	* @return Integer
	*/
	function num_fields(){  
			return mysql_num_fields($this->db_result);  
	}  
	
	
	/**
	* libére le resultat
	*
	* @name free
	* @access public
	*/
	function free() {  
			mysql_free_result($this->db_result) or $this->erreur("",mysql_error(),mysql_errno()); 
	} 
	
	
	/**
	* affiche les messages d'erreur
	*
	* @name erreur
	* @access private
	* @param string $type
	* @param string $erreur
	* @param string $errno
	* @return array
	*/
	function erreur($type,$erreur,$errno){
		if(LOG){
			$fh = fopen("erreur.log", 'a+') or die("can't open file");
			$stringData =date("Y-m-d : H:i:s")." - err :".$type." : ".$erreur."\n";
			fwrite($fh, $stringData);
			fclose($fh);
		}
		die("<center><b>ERREUR ".$type."  :
		<font color='red'>".$erreur."</font><br /> ERREUR N° :".$errno."</b><center>");
	}
	
	
	/**
	* liste les tables d'une base
	*
	* @name list_tables
	* @access public
	* @return array
	*/
	function list_tables(){
		$db_table=array();
		$this->db_query("SHOW TABLES FROM ".$this->db['base']);
		while ($row = mysql_fetch_array($this->db_result,MYSQL_NUM)){
			$db_table[]=$row[0];
		}
		return $db_table;
	}
	
	
	/**
	*
	*
	* @name getMysqlVersion
	* @access public
	* @return string | boolean
	*/
	function getMysqlVersion()
	{
		$r = query('SELECT version()');
		if($r)
		{
			$l = mysql_fetch_row($r);
			return $l[0];
		}
		return false;
	}
}




?>
