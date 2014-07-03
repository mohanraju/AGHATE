<?php
#########################################################################
#                         Class Ftp.php                        			#
#				Fonction qui permet la connexion ftp				    #
#                Recuperation et insertions de fichier 					#
#																        #
######################################################################### 
class Ftp
{
	/**
	* Information de connection
	*/
	private $_connection;
 
	/**==========================================================
	* Constructeur
	==========================================================*/
 
	public function __construct($host, $port, $login, $password)
	{
		$this->_connection = ftp_connect($host, $port);
		if (!$this->_connection) {
			// Logguer une erreur de connexion
			die('<br>FTP::Erreur de connexion host'.$host);
		}
		if (!ftp_login($this->_connection, $login, $password)) {
			throw new Exception('<br>Erreur d\'authentification avec '.$login ."PWD");
		}
	}
	
 
   /**==========================================================
	* getFile : recupere le fichier sur le serveur
	* et le créer en local  
	==========================================================*/
    function getFile($LocalFilePath, $LocalFileName, $ServeurFilePath, $ServeurFileName)
    {      
        $Local 		= $LocalFilePath."/".$LocalFileName;
        $Serveur 	= $ServeurFilePath."/".$ServeurFileName;
        if (ftp_get($this->_connection,$Local,$Serveur,FTP_BINARY)){
			echo "<br>Fichier transféré de $Serveur  vers local $Local";
        } 
        else {
            throw new Exception('<br>FTP ::Erreur Fichier '.$Serveur.' inexistant');
        }
    }
    
    
    /**=============================================================================
	* getFile : recupere le fichier en local
	* et le créer sur le serveur  
	===============================================================================*/
	function putFile ($ServeurFilePath, $ServeurFileName,$LocalFilePath, $LocalFileName)
    {
		$Local 		= $LocalFilePath."/".$LocalFileName;
		$Serveur 	= $ServeurFilePath."/".$ServeurFileName;
        if (file_exists($Local)) {
            if(ftp_put($this->_connection,$Serveur,$Local, FTP_BINARY)){
				echo "<br>FTP:: Fichier transféré de $Local transféré vers $Serveur";
			}
			else {
				throw new Exception('<br>FTP:: PutFile Un problème a eu lieu pendane le tranfert de ficheir '.$LocalFileName);
			}
		}else {
            throw new Exception('<br>FTP:: Fichier '.$localFile.' inexistant');
        }
    }
	
	
    /**=============================================================================
	* function ListFilesInServeur(serveur path, chemin)
	* retourne les fichiers dans un tableau singel dimention
	===============================================================================*/
	function ListFilesInServeur($ServeurDirPath){
		$Serveur 	= $ServeurDirPath;
		if(strlen($ServeurDirPath)>1)
		{
			if(!ftp_chdir($this->_connection,$Serveur))
			{
				echo "<br>FTP::Impossible de changer de dossier ou aucun fichier ou dossier de ce type ".$ServeurDirPath;
				exit;
			}
		}
		$FileList 	= ftp_nlist($this->_connection,".");
		return $FileList;
	}
 
	 /**=============================================================================
	* *getDir : recupere tout les fichiers du dossier ServeurDirName
	* et les insere dans le dossier en local
	===============================================================================*/
	function getDir ($LocalDirPath, $LocalDirName, $ServeurDirPath, $ServeurDirName)
	{
		$FileList 	= $this->getFileInDir($ServeurDirPath, $ServeurDirName);
		$Local 		= $LocalDirPath."/".$LocalDirName;
		$Serveur 	= $ServeurDirPath."/".$ServeurDirName;
		if (count($FileList)<1) {
			echo "<br>FTP::Pas de fichier dans le dossier ".$ServeurDirPath."/".$ServeurDirName ; 
		}
		for($i=0;$i<count($FileList);$i++){
			$this->getFile($Local,$FileList[$i],$Serveur,$FileList[$i]);
		}
	}
	
	
	 /**=============================================================================
	* *closeFtp : ferme la connexion
	===============================================================================*/	
	function CloseFtp(){
		ftp_close($this->_connection); 
	}
 
 
    
}
