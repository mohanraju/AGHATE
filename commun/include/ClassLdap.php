<?php
/*
LDAP Authentification Simple 
par MOHANRAJU@sls.aphp.fr

/* UTILISATION

	$ldap= new Ldap($Serveur,$Port,$LdapBase);
	$check=$ldap->Authentification($user,$mdp);
	if ($check)
	{
		echo "Authentification ok..";
		$ldap->Close();
	}
	else
		echo "Login ou mot de passe incorrect!!!";	
*/

class Ldap{
	var $Connexion;
	Var $dn;
	Var $UserAdmin;
	Var $PassAdmin;
	var $Base;
	/*-----------------------------------------------------------------
		function Ldap($Serveur,$Port)
		Connexion to ldap Serveur	
	-------------------------------------------------------------------
	*/
	function Ldap($Serveur,$Port="",$LdapBase="",$Admin="",$PassAdmin="")
	{
		if(!function_exists('ldap_connect'))
		{
			echo "LDAP :  [Err 101] Les fonctions LDAP ne sont pas disponibles , veuillez activer le module LDAP";
			exit;			
		}
		
		if (($Serveur)  and ($Port))
		{
			$this->Connexion=@ldap_connect($Serveur,$Port)or die("LDAP ::  [Err 102] Unable to connect Serveur,".ldap_error($this->Connexion)); 
		}
		elseif ($Serveur)
		{
			$this->Connexion=@ldap_connect($Serveur) or die("LDAP ::  [Err 103] Unable to connect Serveur,".ldap_error($this->Connexion)); 
		}
		else
		{
			echo "LDAP : Unable to connect serveur";
			exit;
		}
		// Les options de connection  suivantes sont indispensables pour pouvoir dialoguer avec le serveur AD
		ldap_set_option($this->Connexion, LDAP_OPT_PROTOCOL_VERSION, 3) or exit (" LDAP ::[Err 104] Impossible de passer le protocole ldap en version 3, contactez un administrateur"); 
		ldap_set_option($this->Connexion, LDAP_OPT_REFERRALS, 0) or exit (" LDAP ::[Err 105] Impossible de modifier les options LDAP, contactez un administrateur");		
		//on remtet les values user et pass 
		if ($Admin) 
			$this->UserAdmin=$Admin;
		if($PassAdmin)
			$this->PassAdmin=$PassAdmin;
		if($LdapBase)
			$this->Base=$LdapBase;		
	}
	
	/*-----------------------------------------------------------------
			function Authentification($user,$mdp)
		
	-------------------------------------------------------------------
	*/
	function Authentification($User,$Mdp)
	{
		$_user=$User."@wprod.ds.aphp.fr";
		$dn=@ldap_bind($this->Connexion,$_user,$Mdp);
		if ($dn)
		{

			return true;
		}
		else
		{
			return false;
		}	
		
	}	
	/*-----------------------------------------------------------------
		function GetUserInfoByCodeAphp($User)
		cete fonctionn retourne 
			les resultat sois forma de tableau
			si non retourne false
	-------------------------------------------------------------------
	*/
	function GetUserInfoByCodeAphp($User,$Mdp)
	{
		$TempUser=$User."@wprod.ds.aphp.fr";		
		if( !$this->Connexion)
		{
				echo "LDAP :: [Err 106]Connection LDAP not etabilished!!!";			
				exit;
		}
		
		$dn=@ldap_bind($this->Connexion,$TempUser,$Mdp); //or die("LDAP ::[Err 107] Nous ne pouvons pas d'obtenir des informations utilisateur,".ldap_error($this->Connexion));
		if (!$dn)
		{
			return false;
			echo "LDAP :: [Err 108]nous ne pouvons pas d'obtenir des informations utilisateur!!!";			
			exit;
		}
		$filter= "sAMAccountName=".$User;		
		$recherche = @ldap_search($this->Connexion,$this->Base, $filter) or die("LDAP :: [Err 109] Unable to filtre user info,".ldap_error($this->Connexion)); ;
		$liste = @ldap_get_entries($this->Connexion, $recherche) or die("LDAP :: [Err 109] Unable to get user info filtré,".ldap_error($this->Connexion)); ;
		return $liste;
		
	}	
	
	/*-----------------------------------------------------------------
		function GetUserInfo($User) for TESTSSSSSSSSSSSSSSSSSSSSSS et récuparer d'informaion d'unutilisatur en connectant en tant que admin
		cete fonctionn retourne 
		les resultat sois forma de tableau
		si non retourne false
	-------------------------------------------------------------------
	*/
	function GetUserInfo($UserId)
	{
		
		if( !$this->Connexion)
		{
				echo "LDAP :: [Err 110]nous ne pouvons pas d'obtenir des informations utilisateur, connection LDAP not etabilished!!!";			
				exit;
		}
		$dn=@ldap_bind($this->Connexion,$this->UserAdmin,$this->PassAdmin) or die ("LDAP:: [Err 111]Erreur authntification ,".ldap_error($this->Connexion));
		if (!$dn)
		{
			//return false;
			echo "LDAP :: [Err 112]nous ne pouvons pas d'obtenir des informations utilisateur!!!unable to bind admin ";		
			exit;
		}
		$filter= "sAMAccountName=".$UserId;		
		$recherche = @ldap_search($this->Connexion,$this->Base, $filter) or die("LDAP :: [Err 113] Unable to filtre user info,".ldap_error($this->Connexion)); ;
		$liste = @ldap_get_entries($this->Connexion, $recherche) or die("LDAP :: [Err 114] Unable to get user info filtré,".ldap_error($this->Connexion)); ;
		return $liste;
		
	}	

	/*-----------------------------------------------------------------
		function GetUserInfoByNom($Nom) for TESTSSSSSSSSSSSSSSSSSSSSSS et récuparer d'informaion d'unutilisatur en connectant en tant que admin
		cete fonctionn retourne tous les personnes comancenet avec ce nom
		les resultat sois forma de tableau
		si non retourne false
	-------------------------------------------------------------------
	*/
	function GetUserInfoByNom($Nom)
	{
		
		if( !$this->Connexion)
		{
				echo "LDAP :: [Err 110]nous ne pouvons pas d'obtenir des informations utilisateur, connection LDAP not etabilished!!!";			
				exit;
		}
		$dn=@ldap_bind($this->Connexion,$this->UserAdmin,$this->PassAdmin) or die ("LDAP:: [Err 111]Erreur authntification ,".ldap_error($this->Connexion));
		if (!$dn)
		{
			//return false;
			echo "LDAP :: [Err 112]nous ne pouvons pas d'obtenir des informations utilisateur!!!unable to bind admin ";		
			exit;
		}
		$filter= "sn=".$Nom;		
		$recherche = @ldap_search($this->Connexion,$this->Base, $filter) or die("LDAP :: [Err 113] Unable to filtre user info,".ldap_error($this->Connexion)); ;
		$liste = @ldap_get_entries($this->Connexion, $recherche) or die("LDAP :: [Err 114] Unable to get user info filtré,".ldap_error($this->Connexion)); ;
		return $liste;
		
	}	
		


	/*-----------------------------------------------------------------
		function close()
		close ldap Connexion 
	-------------------------------------------------------------------
	*/
	function Close()
	{
		ldap_close($this->Connexion);
	}	


	
}

?>
