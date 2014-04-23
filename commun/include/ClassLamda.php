<?php
/*
###################################################################################################
#
#											OBJET LAmda
#											
#
#							By Mohanraju @ APHP
###################################################################################################
*/
class Lamda extends MySQL
{
	var $DateDeb;
	var $DateFin;
 	var $Site	;				// Code du Hopital ex 076 pour sls, 047 pour lrb ....
 	var $TableLamda ;         	// nom de Table Lamda dans mysql ::permet de defrentiser si plusiers sites...
 	/*
 	==============================================================================
 	Constructeur 
 	function nestro($site)
 	==============================================================================
 	*/
	function Lamda($Site)
	{
		$this->Site=$Site;
 		//initialise connexion Mysql
		parent::MySQL(); 
	}
 	/*
 	==============================================================================
 	function SetTableLamda($NomduTable)
 	==============================================================================
 	*/
	function SetTableLamda($NomduTable)
	{
		if (strlen($NomduTable) < 1)
		{
			echo "<br>Lamda:: Nom du Table_Lamda est vide ou non declaré<br> Operation abondonnée !!!, consult config/config_[site].php";
			exit;
		}
		else	
		{
			$this->TableLamda=$NomduTable;
		}
	}

 

 	/*
 	==============================================================================
 	funtion SetPeroide (date_deb et date_fin )
 	initalise les peroides dans Lamda;
 	vérify le format de dates 
 	si les dates sont vide le peroide sera le 1er jour de l'anne à aujourdhui
 	==============================================================================
 	*/
	function SetPeroide($DateDeb="",$DateFin="")
	{
		if ((strlen($DateDeb) < 8)||(strlen($DateFin) < 8))
		{
			// pour janvier et fevrier traites les année d'avant aussi
			if( (date('m')=='01') || (date('m')=='02')){
					$year=date("Y");
					$year--;
					$this->DateDeb="01/01/".$year;
	 		}else{
				$this->DateDeb=date("01/01/Y");
			}
			$this->DateFin=date("d/m/Y");
		}else{
			$this->DateDeb=$DateDeb;
			$this->DateFin=$DateFin;
		}
		
	}
 

	/*
	============================================================
	function GetSejours($Service="",$Anesthesiste="",$Chirurgien="");
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetSejours($Service="",$_annee="2010" )
	{
		$filtre_sql="";

		//Service
		if ((strlen($Service) > 0) and ($Service !="TOUS"))
		{
			$sql_service.= " AND ".$this->TableLamda.". service='".$Service."' ";					
		}
		//Annee
		if ((strlen($_annee) > 0) )
		{
			$sql_annee.= " AND YEAR(".$this->TableLamda.". ds)='".$_annee."' ";					
		}
		
		$sql="SELECT * from lamda WHERE 1 >0 " .$sql_service. $sql_annee. " Order by nom ,prenom";

		$data=parent::select($sql);
	
		return $data;
	}
  	
 

	/*
	============================================================
	function GetAllServices();
	return les resulata dans le tableau
	=================================================================	
	*/
	function GetAllServices()
	{

	  $sql = "SELECT service from ".$this->TableLamda."   group by service order by  service";
   	$data=parent::select($sql);
		return $data;
	}




}//FIN CLASS
?>
