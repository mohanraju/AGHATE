<?php
/*
###################################################################################################
#
#							OBJET USER
#							By Mohanraju @ APHP
###################################################################################################
*/
Class User extends MySQL
{
	var $TableUser ; 	// nom de Table User dans mysql 
	var $TableLog; // nom du table Trace   
	function User()
	{
			//initialise connexion Mysql
			parent::MySQL(); 
	}

 	/*
 	==============================================================================
 	function SetTableUser($NomTable)
 	==============================================================================
 	*/
	function SetTable($NomTable)
	{
		if (strlen($NomTable) < 1)
		{
			echo "<br>Utiisateur:: Nom du TableUser est vide ou non declaré<br> Operation abondonnée !!!, consult config/config_[site].php";
			exit;
		}
		else	
		{
			$this->TableUser=$NomTable;
		}
	}
 	/*
 	==============================================================================
 	function SetTableLog($TableLog)
 	==============================================================================
 	*/
	function SetTableLog($TableLog)
	{
		if (strlen($TableLog) < 1)
		{
			echo "<br>Utiisateur:: Nom du TableUser est vide ou non declaré<br> Operation abondonnée !!!, consult config/config_[site].php";
			exit;
		}
		else	
		{
			$this->TableLog=$TableLog;
		}
	}	
 	/*
 	==============================================================================
 	function GetUserInfoByUserName($username)
 	==============================================================================
 	*/
 	function GetUserInfoByUserName($username)
	{      
    $sql = "select * from ".$this->TableUser." where login = '".$username."'";
    $result = parent::select($sql);
    return $result;
 	}  
 	/*
 	==============================================================================
 	function UpdateLastAccess($username)
 	==============================================================================
 	*/
 	function UpdateLastAccess($username)
	{      
    $sql = "UPDATE ".$this->TableUser." set last_access='".date('Y-m-d H:i:s')."' where login = '".$username."'";
    $result = parent::update($sql);
    return $result;
 	} 	
 	/*
 	==============================================================================
 	AddNewUser($login,$nom,$prenom,$profile,$default_site,$default_page,$etat)
 	==============================================================================
 	*/
	function AddNewUser($login,$nom,$prenom,$profile,$default_site,$default_page,$etat,$email="") 
	{
		$sql="INSERT INTO ".$this->TableUser." set 
							login='$login',
							nom ='$nom',
							prenom ='$prenom',
							profile='$profile',
							default_site='$default_site',
							default_page='$default_page',
							email='$email',
							etat='$etat'";				
 		
		parent::insert($sql);	
  }

 	/*
 	==============================================================================
 	UpdateUser($login,$nom,$prenom,$email)
 	==============================================================================
 	*/
	function UpdateUser($login,$nom,$prenom,$email='') 
	{
		$sql="UPDATE ".$this->TableUser." SET
							nom ='$nom',
							prenom ='$prenom',
							email='$email'
					WHERE login='$login'";		
		parent::update($sql);	
	
  }


 	/*
 	==============================================================================
 	function GetUserServices($User)
 	cette funtion retourne les ensembles des services les queles l'utilisateur a droit
 	attn  dans la base les droits sont stocké dans la manière suivante
 		NomDeService- (codeHopital)
 		ex : Chirurgie plastique (Pr Revol)-(76) donc les dernier 5 chars sont d'hopital 	
 		
 	NOTE ::cette methode n'est pas correct  il faut simplier dans le prochane version	
 	en modifient le getsion des services d'utlisateur egalement
 	==============================================================================
 	*/  
	function GetUserServices($User)
	{
		$sql = "SELECT droit_type,droit_value FROM user_droits WHERE login='$User' order by droit_value";
		//echo $sql;
		$result = parent::select($sql);
		for($i=0;$i < count($result); $i++)
		{
	 		// format recuparer de base : Chirurgie generale(76)
     // format a retourner : SERVICE@76-Chirurgie generale|Chirurgie generale-(76) // FORMAT HTML->InputSelect()
			$data=explode("(",$result[$i]['droit_value']);	
			$pos=strrpos($result[$i]['droit_value'], '(', -1);

			if($pos > 1)
			{
				$service_lib	=	substr($result[$i]['droit_value'],0,$pos);
				$hopital			=	substr($result[$i]['droit_value'],$pos + 1,2  );
	 		}else{
	 			// si rien on envoi le même pour eviter l'erreur
	 			$service_lib=$result[$i]['droit_value'];
	 			$hopital=$result[$i]['droit_value'];	
	 		}
			$retval[]=$result[$i]['droit_type']."@".$hopital."-".$service_lib."|".$service_lib."-(". $hopital.")";	
		}


	  return $retval;
		

 
	}	
	
	/*
 	==============================================================================
 	function GetUserDroit($User,$droit_type="")
 	cette funtion retourne les ensembles des droits Sous forme de deux colonnes  droit_type|droit_value
 	==============================================================================
 	*/  
	function GetUserDroit($User,$droit_type="")
	{
		if($droit_type)
			$sql_droit_type="AND droit_type='".$droit_type."'";
		$sql = "SELECT droit_type, droit_value FROM user_droits WHERE login='$User' $sql_droit_type order by droit_type,droit_value";
		//echo $sql;
		$result = parent::select($sql);
		
	  return $result;
	}	

 	/*
 	==============================================================================
 	function GetAllServices()
 	les resultat sont retournée dans un format utilisable dans Html->InputSelec();	
 	==============================================================================
 	*/  
	function GetAllServices()
	{
		$sql="SELECT concat(hopital,'-', service_lib) as service,
								 concat(service_lib, '-(',hopital,')') as service_lib 
					FROM structure_gh where service_lib is not null and service_lib<>''  
				  GROUP BY hopital,service_lib order by service_lib";
		//echo $sql;
		$result = parent::select($sql);
		for($i=0;$i < count($result); $i++)
		{
			$retval[]="SERVICE@".$result[$i]['service']."|".$result[$i]['service_lib'];	
		}
	  return $retval;
	}	
 	/*
 	==============================================================================
 	function GetAllPoles()
 	les resultat sont retournée dans un format utilisable dans Html->InputSelec();	
 	==============================================================================
 	*/  
	function GetAllPoles()
	{
		$sql="SELECT pole,
								 concat(pole_lib, '-(',pole,')') as pole_lib 
					FROM structure_gh where service_lib is not null and pole_lib<>''  
				  GROUP BY  pole_lib order by pole_lib";
		//echo $sql;
		$result = parent::select($sql);
		for($i=0;$i < count($result); $i++)
		{
			$retval[]="POLE@".$result[$i]['pole']."|".$result[$i]['pole_lib'];	
		}
	  return $retval;
	}	
		
 	/*
 	==============================================================================
 	function GetListeThesaurusByAdmin($Site)
 	cette funtion retourne les ensembles des services les queles l'utilisateur a droit
 	==============================================================================
 	*/  
	function GetListeThesaurusByAdmin($Site){
		$sql = "SELECT CONCAT( service_lib, '(', hopital, ')' ) AS Result FROM structure_gh WHERE hopital='$Site' group by Result";
		echo $sql;
		$result = $this->select($sql);
	  return $result;
	}		

	/*
	==============================================================================
	Function AddLog($msg,$rubrique,$user)
	$msg : le trace a jouter dans le table(obligatoire)
	$rubrique : multi usage   (optionnel)
					soit
						-	nom du patient
						- nip 
						- nda
						- le rubrique  consulté
						- etc
	$user : login de utilisateur (optionnel)
				si vide on va récuparer de session;
	==============================================================================
	*/
	
	function  AddLog($msg,$rubrique="",$user="")
	{
		if (strlen($user) < 1)
			$user=$_SESSION['user'];

		if (strlen($user) < 1){
			$user="inconnu"; // jamais 	inconnu pour éviter l"erreur SQL
		}
		
		// check table nom defini ou non si non force to "log"
		if (strlen($this->TableLog)<1)
			$this->SetTableLog("log");
			
		
		$sql_log="INSERT INTO ".$this->TableLog."  (user,time,tache) values (
						  '$user',
						  '".date("Y-m-d H:i:s")."',
						  '".addslashes($msg)."')";
		$nbr_rows=$this->insert($sql_log);
	}
 	/*
 	==============================================================================
 	function CompteAdmin()
 	checks moins un compte admin sur la base
 	retoure true ou false
 	==============================================================================
 	*/  
	function CompteAdmin(){
    $sql = "select * from ".$this->TableUser." where profile = 'ADMIN'";
    $result = parent::select($sql);
		if(count($result) > 0)
		{
	  	return true;
	  }
	  else	
	  {
	  	return false;
	  	
	  }
	}		
 	/*
 	==============================================================================
 	function GetDefaultSite()
 	checks moins un compte admin sur la base
 	retoure true ou false
 	==============================================================================
 	*/  
	function GetDefaultSite(){
    $sql = "select lib_value from listes where grp='ListeSites' ";
    $result = parent::select($sql);
		if(count($result) > 0)
		{
	  	return $result[0]['lib_value'];
	  }
	  else	
	  {
	  	return "";
	  	
	  }
	}	
}
?>
