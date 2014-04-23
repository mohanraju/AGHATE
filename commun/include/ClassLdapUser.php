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

class LdapUser extends MySQL{
	var $LdapConnexion;
	Var $dn;
	Var $UserAdmin;
	Var $PassAdmin;
	var $Base;

	function LdapUser()
	{
			//initialise connexion Mysql
			parent::MySQL(); 
	}
	
	
	function open_session($_login, $_password, $_user_ext_authentifie = '', $tab_login=array(), $tab_groups=array()){
		// Initialisation de $auth_ldap
		$auth_ldap = 'no';
		// On traite le cas où l'utilisateur a été authentifié par SSO
		// On traite le cas usuel (non SSO)
		$passwd_md5 = md5($_password);
		$sql = "select upper(login) login, password, prenom, nom, statut, now() start, 
					default_area, default_room, default_style, default_list_type, default_language, source, etat
					from agt_utilisateurs
					where login = '" . protect_data_sql($_login) . "' and
					password = '".$passwd_md5."'";
		$res_user = $this->select($sql);
		$num_row = count($res_user);
		if ($num_row < 1) {  // L'utilisateur n'est pas présent dans la base locale
			// Cas où Ldap a été configuré :
			// On tente une authentification ldap
			if ((getSettingValue("ldap_statut") != '') and (@function_exists("ldap_connect")) 
				and (@file_exists("./config/config_ldap.inc.php"))) {
					
				$login_search = ereg_replace("[^-@._[:space:][:alnum:]]", "", $_login);
				if ($login_search != $_login) {
					return "6"; //L'identifiant comporte des caratères non autorisés
					exit();
				}
			  // Convertir depuis UTF-8 (jeu de caracteres par defaut)
					if ((function_exists("utf8_decode")) and (getSettingValue("ConvertLdapUtf8toIso")=="y")) {
						$_password=utf8_encode($_password);
					}

				$user_dn = $this->grr_verif_ldap($_login, $_password);
				if ($user_dn=="error_1") {
					//  chemin invalide ou filtre add mauvais
					return "7";
					exit();
				} else if ($user_dn=="error_2") {
					// aucune entrée ne correspond au filtre
					return "8";
					exit();
				} else if ($user_dn=="error_3") {
					// plus de deux résultats dans la recherche -> Echec de l'authentification ldap
					return "9";
					exit();
				} else if ($user_dn) {
					$auth_ldap = 'yes'; // Voir suite plus bas
				} else {
					// Echec de l'authentification ldap
					return "4";
					exit();
				}
			} else {
				return "2";
				exit();
			}
		}
		else {
			// on récupère les données de l'utilisateur dans $row
			$row = $res[0];
			// S'il s'agit d'un utilisateur inactif, on s'arrête là
			if ($row['etat'] == 'inactif') {
				return "5";
				exit();
			}
		}
		// Cette partie ne concerne que les utilisateurs pour lesquels l'authentification ldap ci-dessus a réussi
		// On tente d'interroger la base ldap pour obtenir des infos sur l'utilisateur
		if ($auth_ldap == 'yes') {
			// on regarde si un utilisateur ldap ayant le même login existe déjà
			$sql = "select upper(login) login, password, prenom, nom, statut, now() start, default_area, default_room, default_style, default_list_type, default_language, source
			from agt_utilisateurs
			where login = '" . protect_data_sql($_login) . "' and
			source = 'ext' and
			etat != 'inactif'";
			$res_user = $this->select($sql);
			$num_row = count($res_user);
			if ($num_row == 1) {
				// un utilisateur ldap ayant le même login existe déjà
				// on récupère les données de l'utilisateur dans $row
				$row = $res_user[0];
			} else {
				 // pas d'utilisateur ldap ayant le même login dans la base GRR
				 // Lire les infos sur l'utilisateur depuis LDAP
				 include "./config/config_ldap.inc.php";
				 // Connexion à l'annuaire
				 $ds = $this->grr_connect_ldap($ldap_adresse,$ldap_port,$ldap_login,$ldap_pwd,$use_tls);
				 // Test with login and password of the user
					 if (!$ds) {
						   $ds = $this->grr_connect_ldap($ldap_adresse,$ldap_port,$_login,$_password,$use_tls);
					 }
				 if ($ds) {
					 $result = @ldap_read($ds, $user_dn, "objectClass=*", array(getSettingValue("ldap_champ_nom"),getSettingValue("ldap_champ_prenom"),getSettingValue("ldap_champ_email")));
				 }
				 if (!$result) {
					 return "2";
					 die();
				 }
				 // Recuperer les donnees de l'utilisateur
				 $info = @ldap_get_entries($ds, $result);
				 if (!is_array($info)) {
					 return "2";
					 die();
				 }
				 for ($i = 0; $i < $info["count"]; $i++) {
					 $val = $info[$i];
					 if (is_array($val)) {
						 if (isset($val[getSettingValue("ldap_champ_nom")][0]))
						   $l_nom = ucfirst($val[getSettingValue("ldap_champ_nom")][0]);
						   else $l_nom="Nom à préciser";
						 if (isset($val[getSettingValue("ldap_champ_prenom")][0]))
						   $l_prenom = ucfirst($val[getSettingValue("ldap_champ_prenom")][0]);
						   else $l_prenom="Prénom à préciser";
						 if (isset($val[getSettingValue("ldap_champ_email")][0])) $l_email = $val[getSettingValue("ldap_champ_email")][0]; else $l_email='';
					 }
				 }
				// Convertir depuis UTF-8 (jeu de caracteres par defaut)
				if ((function_exists("utf8_decode")) and (getSettingValue("ConvertLdapUtf8toIso")=="y")) {
					$l_email = utf8_decode($l_email);
					$l_nom = utf8_decode($l_nom);
					$l_prenom = utf8_decode($l_prenom);
				}
				// On teste si un utilisateur porte déjà le même login
				$test = $this->select(("select login from agt_utilisateurs where login = '".protect_data_sql($_login)."'"));
				if (count($test) != '-1') {
					// authentification bonne mais le login existe déjà : impossible d'importer le profil.
					return "3";
					die();
				} else {
					// On insère le nouvel utilisateur
					$sql = "INSERT INTO agt_utilisateurs SET
					nom='".protect_data_sql($l_nom)."',
					prenom='".protect_data_sql($l_prenom)."',
					login='".protect_data_sql($_login)."',
					password='',
					statut='".getSettingValue("ldap_statut")."',
					email='".protect_data_sql($l_email)."',
					etat='actif',
					source='ext'";
					if (grr_sql_command($sql) < 0)
					{	
						fatal_error(0, get_vocab("msg_login_created_error") . grr_sql_error());
						return "2";
						die();
					}

					$sql = "select upper(login) login, password, prenom, nom, statut, now() start, default_area, default_room, default_style, default_list_type, default_language, source
					from agt_utilisateurs
					where login = '" . protect_data_sql($_login) . "' and
					source = 'ext' and
					etat != 'inactif'";
					$res_user = $this->select($sql);
					$num_row = count($res_user);
					if ($num_row == 1) {
						// on récupère les données de l'utilisateur dans $row
						$row = $res_user[0];
				   } else {
					   return "2";
					   die();
				   }
				}
			}
		}

		// On teste si la connexion est active ou non
		if ((getSettingValue("disable_login")=='yes') and ($row['statut'] != "administrateur")) {
			return "2";
			die();
		}

		//
		// A ce stade, on dispose dans tous les cas d'un tableau $row contenant les informations nécessaires à l'établissment d'une session
		//
		return $row;
	}

	
	function grr_verif_ldap($_login, $_password) {
		global $ldap_filter;
		if ($_password == '') {
			return false;
			exit();
		}
		include "./config/config_ldap.inc.php";

		$ds = $this->grr_connect_ldap($ldap_adresse,$ldap_port,$ldap_login,$ldap_pwd,$use_tls);
			// Test with login and password of the user
			if (!$ds) {
				$ds = $this->grr_connect_ldap($ldap_adresse,$ldap_port,$_login,$_password,$use_tls);
			}

		if ($ds) {
			// Attributs testés pour egalite avec le login
			$atts = explode("|",getSettingValue("ldap_champ_recherche"));
			//$atts = array('uid', 'login', 'userid', 'cn', 'sn', 'samaccountname', 'userprincipalname');
			$login_search = ereg_replace("[^-@._[:space:][:alnum:]]", "", $_login); // securite
			// Tenter une recherche pour essayer de retrouver le DN
			reset($atts);
			while (list(, $att) = each($atts)) {
				$dn = grr_ldap_search_user($ds, $ldap_base, $att, $login_search, $ldap_filter);
				if (($dn=="error_1") or ($dn=="error_2") or ($dn=="error_3")) {
				  return $dn; // On renvoie le code d'erreur
				} else if ($dn) {
					// on a le dn
					if (@ldap_bind($ds, $dn, $_password)) {
						@ldap_unbind($ds);
						 return $dn;
					}
				}
			}
			// Si echec, essayer de deviner le DN, dans le cas où il n'y a pas de filtre supplémentaires
			reset($atts);
			if (!isset($ldap_filter) or ($ldap_filter="")) {
			  while (list(, $att) = each($atts)) {
				$dn = $att."=".$login_search.",".$ldap_base;
				if (@ldap_bind($ds, $dn, $_password)) {
					@ldap_unbind($ds);
					return $dn;
				}
			  }
			}
			return false;
		} else {
			return false;
		}
	}
	
	
	function grr_ldap_search_user($ds, $basedn, $login_attr, $login, $filtre_sup="", $diagnostic="no") {
		if (getSettingValue("ActiveModeDiagnostic")=="y")
			$diagnostic="yes";
		/*
		  // une alternative au filtre suivant :
		  $filter = "(|(".$login_attr."=".$login.")(".$login_attr."=".$login."@*))";
			if (!empty ($filtre_sup)){
				$filter = "(&".$filter.$filtre_sup.")";
		*/
		  // Construction du filtre
		  $filter = "(".$login_attr."=".$login.")";
			if (!empty ($filtre_sup)){
				$filter = "(& ".$filter.$filtre_sup.")";
			}
			$res = @ldap_search($ds, $basedn, $filter, array ("dn", $login_attr),0,0);
		  if ($res){
			  $info = @ldap_get_entries($ds, $res);
			  if  ((!is_array($info)) or ($info['count'] == 0)) {
					// Mode diagnostic
				  if ($diagnostic!="no")
					 return "error_2"; // aucune entrée ne correspond au filtre
						else
					 // Mode normal
						 return false;
				  die();
			  } else if ($info['count'] > 1) {
					  // Si plusieurs entrées, on accepte uniquement en mode diagnostic
					  if ($diagnostic!="no")
					return "error_3";
				  else
						  // Mode normal
					return false;
				  die();
					} else {
						return $info[0]['dn']; // Succès total -> on retourne le dn
						die();
				}
			  } else {
				  // Mode diagnostic
				if ($diagnostic!="no")
					return "error_1"; // chemin invalide ou filtre add mauvais
					  else
				   // Mode normal
				   return false;
			}
	}
		
	
	
	function grr_connect_ldap($Serveur,$Port="",$LdapBase="",$Admin="",$PassAdmin="") {
		
		if(!function_exists('ldap_connect'))
		{
			echo "LDAP :  [Err 101] Les fonctions LDAP ne sont pas disponibles , veuillez activer le module LDAP";
			exit;			
		}
		if (($Serveur)  and ($Port))
		{
			$this->LdapConnexion=@ldap_connect($Serveur,$Port)or die("LDAP ::  [Err 102] Unable to connect Serveur,".ldap_error($this->LdapConnexion)); 
		}
		elseif ($Serveur)
		{
			$this->LdapConnexion=@ldap_connect($Serveur) or die("LDAP ::  [Err 103] Unable to connect Serveur,".ldap_error($this->LdapConnexion)); 
		}
		else
		{
			echo "LDAP : Unable to connect serveur";
			exit;
		}
		// Les options de connection  suivantes sont indispensables pour pouvoir dialoguer avec le serveur AD
		ldap_set_option($this->LdapConnexion, LDAP_OPT_PROTOCOL_VERSION, 3) or exit (" LDAP ::[Err 104] Impossible de passer le protocole ldap en version 3, contactez un administrateur"); 
		ldap_set_option($this->LdapConnexion, LDAP_OPT_REFERRALS, 0) or exit (" LDAP ::[Err 105] Impossible de modifier les options LDAP, contactez un administrateur");		
		
		if($this->LdapConnexion) {
		   // Accès non anonyme
			if ($l_login != '') {
			  // On tente un bind
			  $b = ldap_bind($this->LdapConnexion, $l_login, $l_pwd);
			} 
			else {
			  // Accès anonyme
			  $b = ldap_bind($this->LdapConnexion);
			}
			if ($b) {
			   return $this->LdapConnexion;
			} 
			else {
			   if ($msg_error != "no") return "error_3";
			   return false;
			}
			else {
				if ($msg_error != "no") return "error_4";
				return false;
			}
		}
		
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
		if( !$this->LdapConnexion)
		{
				echo "LDAP :: [Err 106]Connection LDAP not etabilished!!!";			
				exit;
		}
		
		$dn=@ldap_bind($this->LdapConnexion,$TempUser,$Mdp); //or die("LDAP ::[Err 107] Nous ne pouvons pas d'obtenir des informations utilisateur,".ldap_error($this->LdapConnexion));
		if (!$dn)
		{
			return false;
			echo "LDAP :: [Err 108]nous ne pouvons pas d'obtenir des informations utilisateur!!!";			
			exit;
		}
		$filter= "sAMAccountName=".$User;		
		$recherche = @ldap_search($this->LdapConnexion,$this->Base, $filter) or die("LDAP :: [Err 109] Unable to filtre user info,".ldap_error($this->LdapConnexion)); ;
		$liste = @ldap_get_entries($this->LdapConnexion, $recherche) or die("LDAP :: [Err 109] Unable to get user info filtré,".ldap_error($this->LdapConnexion)); ;
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
		
		if( !$this->LdapConnexion)
		{
				echo "LDAP :: [Err 110]nous ne pouvons pas d'obtenir des informations utilisateur, connection LDAP not etabilished!!!";			
				exit;
		}
		$dn=@ldap_bind($this->LdapConnexion,$this->UserAdmin,$this->PassAdmin) or die ("LDAP:: [Err 111]Erreur authntification ,".ldap_error($this->LdapConnexion));
		if (!$dn)
		{
			//return false;
			echo "LDAP :: [Err 112]nous ne pouvons pas d'obtenir des informations utilisateur!!!unable to bind admin ";		
			exit;
		}
		$filter= "sAMAccountName=".$UserId;		
		$recherche = @ldap_search($this->LdapConnexion,$this->Base, $filter) or die("LDAP :: [Err 113] Unable to filtre user info,".ldap_error($this->LdapConnexion)); ;
		$liste = @ldap_get_entries($this->LdapConnexion, $recherche) or die("LDAP :: [Err 114] Unable to get user info filtré,".ldap_error($this->LdapConnexion)); ;
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
		
		if( !$this->LdapConnexion)
		{
				echo "LDAP :: [Err 110]nous ne pouvons pas d'obtenir des informations utilisateur, connection LDAP not etabilished!!!";			
				exit;
		}
		$dn=@ldap_bind($this->LdapConnexion,$this->UserAdmin,$this->PassAdmin) or die ("LDAP:: [Err 111]Erreur authntification ,".ldap_error($this->LdapConnexion));
		if (!$dn)
		{
			//return false;
			echo "LDAP :: [Err 112]nous ne pouvons pas d'obtenir des informations utilisateur!!!unable to bind admin ";		
			exit;
		}
		$filter= "sn=".$Nom;		
		$recherche = @ldap_search($this->LdapConnexion,$this->Base, $filter) or die("LDAP :: [Err 113] Unable to filtre user info,".ldap_error($this->LdapConnexion)); ;
		$liste = @ldap_get_entries($this->LdapConnexion, $recherche) or die("LDAP :: [Err 114] Unable to get user info filtré,".ldap_error($this->LdapConnexion)); ;
		return $liste;
		
	}	
		


	/*-----------------------------------------------------------------
		function close()
		close ldap Connexion 
	-------------------------------------------------------------------
	*/
	function Close()
	{
		ldap_close($this->LdapConnexion);
	}	


	
}

?>
