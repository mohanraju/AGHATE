<?php

#########################################################################
#                         Class sftp.php                        		#
#				Fonction qui permet la connexion ftp				    #
#                Recuperation et insertions de fichier 					#
#																        #
######################################################################### 
class sftp
{
 
  /**
   * Information de connection
   * @var resource
   */
  private $_connection;
 
  /**
   * Information de flux
   * @var resource
   */
  private $_stream;
 
  public function __construct($host, $port, $login, $password)
  {
    $this->_connection = ssh2_connect($host, $port);
    if (!$this->_connection) {
      // Logguer une erreur de connexion
      die('Erreur de connexion');
    }
    if (! ssh2_auth_password($this->_connection, $login, $password)) {
      throw new Exception('Erreur d\'authentification avec '.$login .' et ' . $password);
    }
 
    $this->_stream = ssh2_sftp($this->_connection);
 
    if (! $this->_stream) {
      throw new Exception('Impossible d\'initialiser la connection SFTP');
    }
  }
 
  /**
   *
   * @param $remoteDir
   * @param $localDir
   * @param $params
   * @return unknown_type
   */
  public function getDir ($remoteDir, $localDir)
  {
    $streamDir = 'ssh2.sftp://'.$this->_stream.$remoteDir;
    $handle = opendir($streamDir);
    if ($handle === false) {
      throw new Exception('Impossible de se placer sur le répertoire '.$remoteDir);
    }
    while (false !== ($file = readdir($handle))) {
      if ($this->_allowedFile($file, $params)) {
        $this->getFile($remoteDir, $file, $localDir);
      }
    }
  }
 
  /**
   *
   * @param $localDir
   * @param $remoteDir
   * @return unknown_type
   */
  public function putDir ($localDir, $remoteDir)
  {
    $handle = opendir($localDir);
      while (false !== ($file = readdir($handle))) {
        if ($this->_allowedFile($file)) {
          $this->putFile($localDir, $file, $remoteDir, $params);
        }
    }
  }
 
  /**
   *
   * @param $remoteDir
   * @param $remoteFile
   * @param $localDir
   */
    public function getFile ($remoteDir, $nameFile, $localDir)
    {
 
        $streamFile = 'ssh2.sftp://'.$this->_stream.$remoteDir.'/'.$nameFile;
       
        $localFile = $localDir.'/'.$nameFile;
        if (file_exists($streamFile)) {
            $contents = file_get_contents($streamFile);
            if ($contents === false) {
                throw new Exception('Impossible de récupérer '.$remoteFile);
            }
            $return = file_put_contents($localFile, $contents);
            if ($return === false) {
                throw new Exception('Impossible de d\'écrire '.$localFile);
            }
        } else {
            throw new Exception('Fichier '.$remoteFile.' inexistant');
        }
    }
 
    /**
     *
     * @param $localDir
     * @param $localFile
     * @param $remoteDir
     */
    public function putFile ($localDir, $nameFile, $remoteDir)
    {
        $streamFile = 'ssh2.sftp://'.$this->_stream.$remoteDir.'/'.$nameFile;
       
        $localFile = $localDir.'/'.$nameFile;
        if (file_exists($localFile)) {
            $contents = file_get_contents($localFile);
            if ($contents === false) {
                throw new Exception('Impossible d\'envoyer '.$nameFile);
            }
            $return = file_put_contents($streamFile, $contents);
            if ($return === false) {
                throw new Exception('Impossible de d\'écrire '.$streamFile);
            }
        } else {
            throw new Exception('Fichier '.$localFile.' inexistant');
        }
    }
 
    /**
     *
     * @param $file
     * @param $params
     * @return boolean
     */
    private function _allowedFile ($file, $params)
    {
        if (is_dir($file) || substr("$file", 0, 1) == "." ) {
            return false;
        }
        return true;
    }
}
