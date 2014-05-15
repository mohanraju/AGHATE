<?php 
class Forms extends MySQL
{
	//dependancy
	//ClassHtml
	var $Html;
	var $Simpa;
	var $Gilda;
	function SetConnexionSimpa($DB){
		$this->Simpa=$DB;
	}
	function SetConnexionGilda($DB){
		$this->Gilda=$DB;
	}
 	function Forms($Site)
	{
		$this->Site=$Site;
		
		//initialise connexion Mysql
		parent::Mysql(); 
	}
	function GetDateFr($Date){
		if($Date)$DateFr=substr($Date,8,2).'/'.substr($Date,5,2).'/'.substr($Date,0,4);
		if(!$Date||$Date=='00/00/0000')$DateFr='JJ/MM/AAAA';
		return($DateFr);
	}


	function GetDescriptifProjet($FichierProjet){
		$fp = fopen($FichierProjet, 'rb');
		$aLines = file($FichierProjet);
		$DataProject=array();
		for ($i=0;$i<count($aLines);$i++) {  
			if(substr($aLines[$i],0,1)!='#'){
				$aCols = explode(":", $aLines[$i]);
				if (isset($aCols[1]))$DataProject[$aCols[0]]=trim($aCols[1]);
				}
			}
	parent::MySQL(); 
	return($DataProject);
	}

	function GetDetailsFormulaire($DP){
		$DP['FICHIER_REFERENCE']=trim($DP['FICHIER_REFERENCE']);
		$DP['NABV']=trim($DP['NABV']);
		//$FichierRef = $_SERVER['DOCUMENT_ROOT']."WebForms/".$DP['NABV']."/".$DP['FICHIER_REFERENCE'];
		$FichierRef = $DP['FICHIER_REFERENCE'];
	
		$fp = fopen($FichierRef, 'rb');
		$aLines = file($FichierRef);
		$count=0;
		foreach($aLines as $sLine) {  
			$aCols = explode("\t", $sLine);
			if($count==0){$NomCols=$aCols;}
			if($count>0){
				for($i=0;$i<count($aCols);$i++){
					$NomCols[$i]=trim($NomCols[$i]);
					if(in_array($NomCols[$i],$DP)){$DetailsFormulaire[$NomCols[$i]][$count-1]=trim($aCols[$i]);}
				}
			}
			$count++;
		}
		return $DetailsFormulaire;
	
	}


	function GestionID($ID,$DP){
	
	/// Fonction de gestion de l'intergite des donnees	
	/// Principe : 
	/// - l'ensemble des versions des formualaire sont sauvegardes dans la base
	/// - chaque version possède un identifiant unique VID
	/// Methode :
	/// - les insersions pour chaque nouveau patient ou chaque nouveau formualaire est gérée
	///   en amont de l'affichage du formualire web
	/// - la fonction d'engistrement du/des formulaires web n'utilise que la fonction Mysql update
	/// Nouveaux patients :
	/// - pour les nouveaux patients deux options sont possibles
	///   * soit creation par l'utilisateur d'un numero de patient :
	///   * soit attribution de façon automatique d'un numéro d'indentification
	/// - on identifie les nouveaux patients par l'arguement identifiant unique (ID) = 0

	//Création d'une nouvelle ligne dans la base de données pour l'identifiant
	//patient 0

	//Création d'une nouvelle ligne dans la base
	//sur la base d'une duplication de la dernière mise à jour 
	//pour un patient donnée
	
	//Recuperation des donnees formulaire	
	$DetailsFormulaire=$this->GetDetailsFormulaire($DP);
	
	//Récupération du VID créé servant d'identifiant unique pour le reste des enregistrements
	if($ID!=0){
		//All entry for this ID are inactives
		$Res=parent::update('UPDATE '.$DP['NABV'].' SET ACTIF=0 WHERE ID='.$ID);
		//Methode de duplicatinon de ligne tenant compte de l'auto-incrémentation
		// ref : http://www.av8n.com/computer/htm/clone-sql-record.htm
		$VidUpdate=$DP['db']->select('select max(vid) as vid from '.$DP['NABV'].' where id='.$ID);
		$Res=parent::execute('CREATE TEMPORARY TABLE tmp ENGINE=MEMORY SELECT * FROM '.$DP['NABV'].' WHERE vid='.$VidUpdate[0]['vid']);
		$Res=parent::execute('select max(vid) as vid from '.$DP['NABV']);$VidUpdate=$Res[0]['vid']+1;
		$Res=parent::execute('UPDATE tmp SET vid='.$VidUpdate);
		$Res=parent::execute('INSERT INTO '.$DP['NABV'].' SELECT * FROM tmp');
		$Res=parent::execute('DROP TABLE tmp');
		$Res=parent::execute('UPDATE '.$DP['NABV'].' SET ACTIF=0 WHERE ID='.$ID);
		$Res=parent::execute('UPDATE '.$DP['NABV'].' SET ACTIF=1 WHERE VID='.$VidUpdate);
	}

	if($ID==0){
		$IdUpdate=parent::execute('select max(id) as id from '.$DP['NABV']);
		$NewID=$IdUpdate[0]['id']+1;
		$Res=parent::execute('insert into '.$DP['NABV'].' (ID) values ('.$NewID.')');
		$VidUpdate=parent::select('select max(vid) as vid from '.$DP['NABV'].' where id='.$NewID);
		$VidUpdate=$VidUpdate[0]['vid']; 
	}



	return($VidUpdate);

	}

	function GetPatientFromNip($NIP){
		$sqlId="select NMMAL as \"Name\", NMPMAL as \"Firstname\", to_char(DANAIS,'DD-MM-YYYY') as \"Ddn\" 
						from pat
						where NOIP='".$NIP."'  ";
		$Res=$this->Simpa->OraSelect($sqlId);
		return $Res[0];
	}

	function GetSejFromNip($NIP){
		$Res=array();
		$sqlH="select noda as \"NDA\", noip as \"NIP\",to_char(DAENTR,'DD-MM-YYYY') as \"de\",
							to_char(DASOR,'DD-MM-YYYY') as \"ds\",
							tydos as \"type\",DASOR as \"ds\"
						from dos
						where NOIP='".$NIP."' ORDER BY DASOR desc ";
		$Res=$this->Simpa->OraSelect($sqlH);
	return $Res[0];
	}
	function GetSejFromNda($NDA){
		$Res=array();
		$sqlSej="select noda as NDA, noip as NIP,to_char(DAENTR,'DD-MM-YYYY') as \"DateEntree\",
							to_char(DASOR,'DD-MM-YYYY') as \"DateSortie\",
							tydos as \"Type\",DASOR as \"ds\"
						from dos
						where NODA='".$NDA."' ORDER BY DASOR desc ";
		$Res=$this->Simpa->OraSelect($sqlSej);
	return $Res[0];
	}
	
		function GetListeSejour($NIP)
	{
		$Res=array();
		$sqlH="select noda as \"NDA\", noip as \"NIP\",to_char(DAENTR,'DD-MM-YYYY') as \"de\",
							to_char(DASOR,'DD-MM-YYYY') as \"ds\",
							tydos as \"type\",DASOR as \"ds\"
						from dos
						where NOIP='".$NIP."' ORDER BY DASOR desc ";
		$Res=$this->Simpa->OraSelect($sqlH);
		
		return $Res;
	}

	function GetMvtFromNda($Nda){
		$sqlMvt="select to_char(DAMVAD,'DD/MM/YYYY') as DATEMVT ,NOUF,NODA
						from mvt
						where NODA='".$Nda."' ";
		$Res=$this->Simpa->OraSelect($sqlMvt);
		return $Res;
	}

	function InputTitle1($Label,$n){
		if($n!=0)$html.= '</table></td></tr>';
		$html.='<tr><td>&nbsp;</td><td>&nbsp;</td><td >&nbsp;</td></tr>
				<tr> <td id="SectionTitle" >';
		$html.= ''.$Label.'';
		$html.= '</td><td>&nbsp;&nbsp;&nbsp;</td><td>';
		$html.= '<table>';
		return $html;
	}

	function InputTitle2($Label,$i){
		$html.= '<tr><td><b>'.$Label.'</b></td></tr>';
		return $html;
	}

	function InputTitle3($Label,$i){
		$html.= '<tr><td><b>'.$Label.'</b></td></tr>';
		return $html;
	}

	function GetDB($DataBase){
		$db='parent';
		return $db;
	}
	function GetDataFromSource($VID,$Source,$NDA="",$NOHJO="",$UH=""){
		//echo $Source."<br>";
		
		//	if(!$NDA || !$UH) $NDA=$VID;
		if($Source=='GILDA'){$Data=array();	
				if(strlen($ID)==10){$Data['Patient']=$this->GetPatientFromNip($VID);}

				if(strlen($ID)==9){
					$Data['Sejour']=$this->GetSejFromNda($VID);
					$Data['Patient']=$this->GetPatientFromNip($Data['Sejour']['NIP']);
					$Data['Mvt']=$this->GetMvtFromNda($VID);
				}
		}

		if($Source!='GILDA'){
				$FileSource='../commun/sources/'.$Source.'.source';
				$DetailSource=$this->GetDescriptifProjet($FileSource);
				//Remplace VID par variable parametrable
				$DetailSource['SQL']=str_replace('FormUpdate_VID',$VID,$DetailSource['SQL']);
				$DetailSource['SQL']=str_replace('FormUpdate_Nohjo',$NOHJO,$DetailSource['SQL']);
				$DetailSource['SQL']=str_replace('FormUpdate_Nda',$NDA,$DetailSource['SQL']);
				$DetailSource['SQL']=str_replace('FormUpdate_Uhexec',$UH,$DetailSource['SQL']);
				$db=$this->GetDB($DetailSource['NABV']);
				if($db=='parent')$Data=parent::select($DetailSource['SQL']);
				//echo $DetailSource['SQL'].'<br>';

				//Si donnees en colonne transformation
				if($DetailSource['TYPEDATA']=='colonne' && $Source!='codage' && $Source!='codagedp' && $Source!='codagedate_date' && $Source!='formsAutoComp'){
					$tmp=$Data;$Data=array();
					for($i=0;$i<count($tmp);$i++){
						//rendre parametrable var et val
						$Data[0][$tmp[$i]['var']]=	$tmp[$i]['val'];			
					}
				}
				
				//Si donnees en colonne transformation
				if($Source=='codage' || $Source=='codagedp' || $Source=='codagedate_date'){
					$tmp=$Data;$Data=array();
					for($i=0;$i<count($tmp);$i++){
						//rendre parametrable var et val
						$Data[0][$tmp[$i]['type']][$tmp[$i]['id_codage_msi']]=	array(diag=>$tmp[$i]['diag'] ,	libdiag=>$tmp[$i]['libdiag'],	datrea=>$tmp[$i]['datrea'])	;		
					}
					
				}
				//Si donnees en colonne transformation
				if($Source=='formsAutoComp'){
					$tmp=$Data;$Data=array();
					for($i=0;$i<count($tmp);$i++){
						//rendre parametrable var et val
						$Data[0][$tmp[$i]['type']][$tmp[$i]['id']]=	array(diag=>$tmp[$i]['val'] ,	libdiag=>$tmp[$i]['libelle'],	datrea=>$tmp[$i]['datrea'])	;		
					}
					
				}
				//echo'<pre>';
				//print_r($Data);
		}
	
		return $Data[0];
	
	}
	

	function GetFormId($DP,$IdAdmin=''){
		//GetDetail Formulaire		
		$DetailsForm=$this->GetDetailsFormulaire($DP);

 		//Get Data from all sources
		$Data=array();
		foreach($DetailsForm['SOURCE'] as $Source){
			if(!isset($Data[$Source])&$Source!=''&$Source!='GILDA')	
				$Data[$Source]=$this->GetDataFromSource($VID,$Source);
		} 
		//print_r($Data);	
		if($IdAdmin&length($IdAdmin)==10){
			$Data['Patient']=$this->GetPatientFromNip($NIP);
			$NIP=$IdAdmin;
		}
		
		echo '<script src="../commun/js/FormsFormsDocready.js"></script>';
		echo '<script src="../commun/js/FormsFunctions.js"></script>';
		
		
		//L'affichage du formulaire repose sur 3 references :
		// - Donnees contenues dans le fichier de reference ($DetailsFormulaire)
		// - Champs dans la base de donnees ($Fields)
		// - Valeurs deja enregistrees s'il s'agit d'une mise a jour ($Valeurs)
		
		echo '<div class="row-fluid">';
		echo '<div class="span2 "></div>';
		

		echo '<div class="span8"><form method="post">';
		//variable ajax pour enregistrement
		echo '<input type="hidden" name="VID" id="VID" value="'.$VID.'">';	
		echo '<table width="700">';
		//Affichage données patient et séjour si présentes
		if($NIP){
		
			echo '	<tr><td width="100"></td><td></td><td></td></tr>
					<tr><td valign="top" ><input class="span10" id="NIP" name="NIP" type="text" value="'.$NIP.'"></td><td></td>
					<td id="Patient"><b>'.$Data['Patient']['Name'].' '.$Data['Patient']['Firstname'].'&nbsp;&nbsp;&nbsp;N&eacute;e le '.$Data['Patient']['Ddn'].'</b><br><br>';
			for ($i=0;$i<count($Data['Mvt']);$i++){
				$service=parent::select("select service_lib from structure_gh
						where uh='".$Data['Mvt'][$i]['NOUF']."' 
						and hopital='76'");
				echo $service[0]['service_lib'].' du '.$Data['Mvt'][$i]['DATEMVT'].' au '.$Data['Mvt'][$i+1]['DATEMVT'].'<br>';
			}
			echo'</td></tr>';
		}
		if(!$NIP){
		
			echo '	<tr><td></td><td></td><td></td></tr>
					<tr><td valign="top"><input class="span10" id="NIP" name="NIP" type="text" value="'.$NIP.'"></td><td></td>
					<td id="Patient"><b>Rechercher un patient</b><br><br>';
			echo'</td></tr>';
		}
	}



	function GetForm($NDA){
			$sql="select distinct val from forms where var='form' and nda='$NDA' order by val asc";
			$Res=$this->select($sql);
			return $Res;
	}
	
	/*
	* ==========================================================================
	* function updateNda($LocID,$InfoMVT[])
	* function permet de mise a jour les TempNDA vers le vrai NDA  et les UH
	* Ex :T00000001 => 761401224
	* ==========================================================================
	*/
	function updateNda($TempNDA,$InfoMVT)
	{
		//update forms
		$sql_updt="UPDATE forms set idref='".$InfoMVT['NDA']."', ref='GILDA', nda='".$InfoMVT['NDA']."', uhexec='".$InfoMVT['UH']."', uhdem='".$InfoMVT['UH']."' WHERE nda='".$TempNDA."'";
		$res = $this->update($sql_updt);	
		echo $sql_updt;
		//update codage
		$sql_updt="UPDATE codage_msi set nda='".$InfoMVT['NDA']."', uhexec='".$InfoMVT['UH']."', uhdem='".$InfoMVT['UH']."' WHERE nda='".$TempNDA."'";
		$res = $this->update($sql_updt);	
		echo '<br>'.$sql_updt;
	}
	
	function GetIdFromVID($vid)
	{
		$requete="SELECT ID from u2i where VID='$vid' AND ACTIF=1";
		
		$id=parent::select($requete);
	
		return $id[0]['ID'];
	}

	function getVID($NIP,$NDA,$UH)
	{
		$requete="SELECT VID FROM u2i WHERE NIP='".$NIP."' AND NDA='".$NDA."' AND UH='".$UH."' AND actif=1";
		
		$vid=parent::select($requete);
		
		if(!isset($vid[0]['VID']))
			return 0;
		
		return $vid[0]['VID'];
	}

	function PrintForm($DP,$result,$mode_ref=""){
		foreach ($result as $key => $value) {
			//echo "clé : ".$key." valeur : ".$value;
			${$key} = $value;
		}
		$VID=$id;$nip=$noip;$NDA=$nda;$UH=$uh;$uhexec=$uh;$uhdem=$uh;
		$FichierRef=$DP['MULTI_FORMS'];
		$nom = $_SERVER["SCRIPT_NAME"]; 
		$script_name=explode('/',$nom);
		$script_name=end($script_name);
		
		if(strlen($FichierRef) > 0){
			$fp = fopen($FichierRef, 'rb');
			$aLines = file($FichierRef);
			$count=0;
			foreach($aLines as $sLine) {  
				$aCols = explode("\t", $sLine);
				$FormsList[$aCols[0]]=$aCols[1];
			}
			$FichierRef=array_keys($FormsList);
			$formsr=$this->GetForm($NDA);
			
			//Attribution du formulaire à lire : utlisation de $_GET['filename'] qui est forcé si non renseigné
			if(!isset($_GET['filename'])){
				if($formsr)$_GET['filename'] = $formsr[0][val];
				else $_GET['filename'] = $FichierRef[0];
			}
			$DP['FICHIER_REFERENCE'] = $_GET['filename'].'.xls';
		
		
		//Le formulaire selectionné a-t-il été utilisé ?
		$InitForm='No';
		for($i=0;$i<count($formsr);$i++)if($formsr[$i]['val']==$_GET['filename'])$InitForm='Yes';
		
		}
		
		$DetailsForm=$this->GetDetailsFormulaire($DP);
		
 		//Get Data from all sources
		$Data=array();
		//$nda=$VID;
		$uh=$_GET['uhexec'];
		$table_loc = $_GET['table_loc'];

		foreach($DetailsForm['SOURCE'] as $Source){
			if(!isset($Data[$Source])&$Source!=''&$Source!='GILDA')$Data[$Source]=$this->GetDataFromSource($VID,$Source,$NDA,'',$uh);

		} 
		
		
		//Recupération des données du systeme d'information
		foreach($DetailsForm[$DP['VABV']] as $k=>$ABV){
				//if (strcasecmp($ABV, 'NDA') == 0){$RefNDA=$DetailsForm[$DP['SOURCE']][$k];$NDA=$Data[$RefNDA][$ABV];}
				if (strcasecmp($ABV, 'NIP') == 0){$RefNIP=$DetailsForm[$DP['SOURCE']][$k];$NIP=$Data[$RefNIP][$ABV];}
				if (strcasecmp($ABV, 'NOIP') == 0){$RefNIP=$DetailsForm[$DP['SOURCE']][$k];$NIP=$Data[$RefNIP][$ABV];}
		}
			
		echo '<script src="../commun/js/FormsFormsDocready.js"></script>';
		echo '<script src="../commun/js/FormsFunctions.js"></script>';
		echo ' <link href="../commun/styles/bootstrap_form.css" rel="stylesheet">';
		
		

		echo '<input type="hidden" name="'.$_GET['filename'].'" id="InitForm" value="'.$InitForm.'">';
		echo '<input type="hidden" name="Username" id="Username"	value='.$_SESSION['login'].'>';
		echo '<input type="hidden" name="SaveDateTime" id="SaveDateTime"	value="'.date("Y-m-d H:i", time()).'">';
		echo '<input type="hidden" name="VID" id="VID" value="'.$VID.'">';
	
	
		
		if(strlen($mode_ref) > 0  )
		{
			echo "<a href='#' onclick=\"$('#mode').val('Modif');\" id='other'>Modifier le formulaire</a><br/>";
			if(strlen($FichierRef)>0)
			{
				
				$compt=0;
				foreach ($formsr as $key => $val){
					foreach ($FormsList as $key1 => $val1){
						if($val['val']==$key1){
							$name="LBL_forms".$compt;
							$listResultat[]=$script_name."?&name=$name&id=$VID&table_loc=$table_loc&filename=$key1|$val1";
							$compt++;
						}
					}
				}
				
				echo '<br>'.$this->Html->InputChoix($listResultat,'forms',"","window.location = $(this).attr('cval')",false).'<br>';
			}
		}
		else
		{		
			echo "<a href='#' onclick=\"$('#mode').val('');\" id='other'>Retour au mode vue</a>";
			if(strlen($FichierRef)>0)
				echo "<br><br>Liste des formulaires  ".$this->Html->InputSelectFromArray($FormsList,'file',$_GET['filename'],"onchange='getLink(".$VID.",this,\"".$table_loc."\")'").'<br>';
		}
		
		$n=0;
		echo '<table width="700">';
		for ($i=0;$i<count($DetailsForm['LIBELLE']);$i++){
			$mode=$mode_ref;
			$name=$DetailsForm[$DP['VABV']][$i];
			if($DetailsForm[$DP['COMMENTAIRES']][$i]=='readonly') $mode="readonly";
				
			if($DetailsForm[$DP['LEGENDE']][$i] == "DP"){
				$Tab=$Data[$DetailsForm[$DP['SOURCE']][$i]][$DetailsForm[$DP['LEGENDE']][$i]];
				if($Tab){
					foreach($Tab as $key=>$value){
						$idF=$key;$value=$Tab[$key][libdiag];$code=$Tab[$key][diag];
					}
				}
			}elseif($DetailsForm[$DP['LEGENDE']][$i] == "DAS"){
				$value=$Data[$DetailsForm[$DP['SOURCE']][$i]][$DetailsForm[$DP['LEGENDE']][$i]];
						if($DAS){
							foreach($DAS as $key=>$value){
								$idF=$key;$value=$DAS[$key][libdiag];$code=$DAS[$key][diag];
								
							}
						}
			}elseif($DetailsForm[$DP['LEGENDE']][$i] == "BIO"){
				$value=$Data[$DetailsForm[$DP['SOURCE']][$i]][$DetailsForm[$DP['LEGENDE']][$i]];
						if($BIO){
							foreach($BIO as $key=>$value){
								$idF=$key;$value=$BIO[$key][libdiag];$code=$BIO[$key][diag];
								
							}
						}
			}elseif($DetailsForm[$DP['LEGENDE']][$i] == "DASDA"){
				$value=$Data[$DetailsForm[$DP['SOURCE']][$i]][$DetailsForm[$DP['LEGENDE']][$i]];
						if($DAS){
							foreach($DAS as $key=>$value){
								$idF=$key;$value=$DAS[$key][libdiag];$code=$DAS[$key][diag];
								
							}
						}
			}else{
				$value=$Data[$DetailsForm[$DP['SOURCE']][$i]][$DetailsForm[$DP['VABV']][$i]];
			
			}			
			
			
			//Title compt
			$nr=$n;
			if($DetailsForm[$DP['TYPE']][$i]=='titre1'){
					Print $this->InputTitle1($DetailsForm[$DP['LIBELLE']][$i],$n);
				$n++;}

			if($DetailsForm[$DP['TYPE']][$i]=='titre2'){Print $this->InputTitle2($DetailsForm[$DP['LIBELLE']][$i],$i);}
			if($DetailsForm[$DP['TYPE']][$i]=='titre3'){Print $this->InputTitle3($DetailsForm[$DP['LIBELLE']][$i],$i);}

			if($DetailsForm[$DP['TYPE']][$i]!='titre1'&&$DetailsForm[$DP['TYPE']][$i]!='titre2'&&$DetailsForm[$DP['TYPE']][$i]!='titre3'){
				if($DetailsForm[$DP['COMMENTAIRES']][$i]=='hidden'){
					if(strlen($value)<1) $value=${$name};
						echo '<input type="hidden" name="'.$name.'" id="'.$name.'" subtype="'.$subtype.'" value="'.$value.'">';
				}

				elseif($name!=''){
				
		    		if ($DetailsForm[$DP['TYPE']][$i]=='cs') {
		    			$enum=array();
							echo '<tr><td ><b>'.$DetailsForm[$DP['LIBELLE']][$i];
							if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
							echo '</b><br>';
							$enum = preg_replace ('!^enum\((.+)\)$!', '$1', $DetailsForm[$DP['LEGENDE']][$i]);
							$enum = str_replace ("'", "", $enum);
							$enum = explode ('|', $enum);
							Print $this->Html->InputChoice(	$Name=$name,$Values=$enum,$default=$value,$vertical=false,$VID=$VID,
																							$Source=$DetailsForm[$DP['SOURCE']][$i],$Abv=$name,
																							$Libelle=$DetailsForm[$DP['LIBELLE']][$i],
																							$compdata=$DetailsForm[$DP['COMPDATA']][$i]);
						
							echo '</td></tr>';
		      		}
					if ($DetailsForm[$DP['TYPE']][$i]=='cm') {
							$enum=array();
							if($DetailsForm[$DP['TYPE']][$i-1]!='cm')echo '<tr><td>';
							$enum[0] = preg_replace ('!^enum\((.+)\)$!', '$1', $DetailsForm[$DP['LEGENDE']][$i]);
							$enum[0] = str_replace ("'", "", $enum[0]);
							if($value=='oui')$value=$enum[0];
							Print $this->Html->InputChoice(	$Name=$name,$Values=$enum,$default=$value,$vertical=false,$VID=$VID,
																							$Source=$DetailsForm[$DP['SOURCE']][$i],$Abv=$name,
																							$Libelle=$DetailsForm[$DP['LIBELLE']][$i],
																							$compdata=$DetailsForm[$DP['COMPDATA']][$i]);
							if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
							if($DetailsForm[$DP['TYPE']][$i+1]!='cm')'</td></tr>';
		      		}
		    		elseif( $DetailsForm[$DP['TYPE']][$i]=='text'){
							echo '<tr><td><b>'.$DetailsForm[$DP['LIBELLE']][$i].'</b>';
							if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
							echo '&nbsp;&nbsp;';
							echo '<input  DataSource="'.$DetailsForm[$DP['SOURCE']][$i].'" type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" CompData="'.$CompData=$DetailsForm[$DP['COMPDATA']][$i].'" '.$mode.'/>';
							echo '&nbsp;&nbsp;'.$DetailsFormulaire[$DP['LEGENDE']][$i].'</td></tr>';}
					elseif( $DetailsForm[$DP['TYPE']][$i]=='longtext'){
							echo '<tr><td><b>'.$DetailsForm[$DP['LIBELLE']][$i];
							 	if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
							 echo '</b><br>';
							echo "<textarea  id='".$name."' type='textarea' onblur=\"updateForms('".$VID."','".$name."','".$VID."','".$value."','".$DetailsForm[$DP['SOURCE']][$i]."','".$DetailsForm[$DP['ABV']][$i]."','".$DetailsForm[$DP['LIBELLE']][$i]."','".$DetailsForm[$DP['COMPDATA']][$i]."')\" class=\"form-control\" rows=\"10\" cols=\"93\"  ".$mode."/>".$value."</textarea></td></tr>";
						}
					elseif($DetailsForm[$DP['TYPE']][$i]=='num'|$DetailsForm[$DP['TYPE']][$i]=='num'){
							echo '<tr><td><b>'.$DetailsForm[$DP['LIBELLE']][$i].'&nbsp;&nbsp;&nbsp;';
							echo '<input  DataSource="'.$DetailsForm[$DP['SOURCE']][$i].'" type="text" class="span2" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$mode.'/>';
							echo '&nbsp;&nbsp;'.$DetailsFormulaire[$DP['LEGENDE']][$i].'</td></tr>';
					}
					elseif($DetailsForm[$DP['TYPE']][$i]=='date'){
		    				$DateFr=$this->GetDateFr($value);
							echo '<tr><td colspan=2><b>'.$DetailsForm[$DP['LIBELLE']][$i];
								if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
							echo '</b>';
							
							echo '&nbsp;&nbsp;&nbsp;';
							echo '<input  DataSource="'.$DetailsForm[$DP['SOURCE']][$i].'"  type="date" class="span4" name="'.$name.'" value="'.$DateFr.'" '.$mode.' /></td></tr>';
		    		}
		    	elseif($DetailsForm[$DP['TYPE']][$i]=='InputCompletSimple'){
		    		echo '<tr><td><b>'.$DetailsForm[$DP['LIBELLE']][$i].'</b>';
		    		if($DetailsForm[$DP['COMPDATA']][$i]=='o')echo '*';
		    		echo '&nbsp;&nbsp;';
		    		$enum = preg_replace ('!^enum\((.+)\)$!', '$1', $DetailsForm[$DP['COMMENTAIRES']][$i]);
						$enum = str_replace ("'", "", $enum);
						$enum = explode ('|', $enum);
						foreach($enum as $sLine) {
							if(substr($sLine,0,1)!='#'){
								$aCols = explode("=", $sLine);
								$DetailAc[$aCols[0]]=$aCols[1];
							}
						}
		    		Print $this->Html->InputCompletSimple($VariableName=$name,$Source=$DetailsForm[$DP['SOURCE']][$i],
		    																					$default=$value,
		    																					$defaultCode=$code,
		    																					$table=$DetailAc['TABLE'],
		    																					$COND1=$DetailAc['CODE'],
		    																					$COND2=$DetailAc['LIBELLE'],
		    																					$ajax=$DetailAc['AJAX'],
		    																					$TypeAttribut=$DetailAc['TYPEATTRIBUT'],
		    																					$compdata=$DetailsForm[$DP['COMPDATA']][$i],
		    																					$id=$idF,
		    																					$autre=$mode);
		    		echo '</td></tr>';
		    	}
		    	elseif($DetailsForm[$DP['TYPE']][$i]=='InputCompletMulti'){	
		    		$enum = preg_replace ('!^enum\((.+)\)$!', '$1', $DetailsForm[$DP['COMMENTAIRES']][$i]);
						$enum = str_replace ("'", "", $enum);
						$enum = explode ('|', $enum);
						$DetailAc =array();
						foreach($enum as $sLine) {
							if(substr($sLine,0,1)!='#'){
								$aCols = explode("=", $sLine);
								$DetailAc[$aCols[0]]=$aCols[1];
							}
						}
						
		    		echo '<tr><td>';
						Print $this->Html->InputCompletMulti($VariableName=$name,$Source=$DetailsForm[$DP['SOURCE']][$i],
																								$default=$value,
		    																					$table=$DetailAc['TABLE'],
		    																					$COND1=$DetailAc['CODE'],
		    																					$COND2=$DetailAc['LIBELLE'],
		    																					$ajax=$DetailAc['AJAX'],
		    																					$TypeAttribut=$DetailAc['TYPEATTRIBUT'],
		    																					$Libelle=$DetailsForm[$DP['LIBELLE']][$i],
		    																					$Fields=$DetailAc['CHAMPSCOMP'],
		    																					$autre=$mode);
						
						
		    		echo '</td></tr>';
		    	}

				}//else if
			
					
			}//Fin If $legende

		}//fin for

	echo '</table></td></tr></table></div></div>';


}//fin function









		function HtmlTest($DP){


		$n=0;
		$NIP='1189200939';
			$sqlId="select NMMAL as \"name\", NMPMAL as \"firstname\", to_char(DANAIS,'DD-MM-YYYY') as \"ddn\" 
						from pat
						where NOIP='".$NIP."' ";
			$sqlH="select noda as \"nda\", noip as \"nip\",to_char(DAENTR,'DD-MM-YYYY') as \"de\",
							to_char(DASOR,'DD-MM-YYYY') as \"ds\",
							tydos as TYPE, DASOR
						from dos
						where NOIP='".$NIP."' ORDER BY DASOR desc ";
			
			$Id=$this->Simpa->OraSelect($sqlId);
			$H=$this->Simpa->OraSelect($sqlH);
			$sqlMvt="select to_char(DAMVAD,'DD/MM/YYYY') as DATEMVT ,NOUF
						from mvt
						where NODA='".$H[0]['nda']."' ";
			$Mvt=$this->Simpa->OraSelect($sqlMvt);
			//echo "toto ".$sqlMvt;
		echo '<div class="row-fluid">';
		echo '<div class="span2 "></div>';
		echo '<div class="span8"><form>';
  		echo '';
		echo '<table width="700">';
		echo '<tr><td></td><td></td><td></td></tr>
		<tr><td><input class="span10" id="NIP" type="text" value="'.$NIP.'"></td><td></td>
			<td id="Patient">'.$Id[0]['name'].' '.$Id[0]['firstname'].'&nbsp;&nbsp;&nbsp;N&eacute;e le '.$Id[0]['ddn'].'<br>';
		for ($i=0;$i<count($Mvt);$i++){
			$service=parent::select("select service_lib from structure_gh
					where uh='".$Mvt[$i]['NOUF']."' 
					and hopital='76'");
			echo $service[0]['service_lib'].' du '.$Mvt[$i]['DATEMVT'].' au '.$Mvt[$i+1]['DATEMVT'].'<br>';
		}
	echo'</td></tr>';
	echo "<tr><td></td><td></td><td>
	<a onclick=\"window.open('..bmr/switch_popup.php?&nip=".$NIP."&date_debut=".$Mvt[0]['DATEMVT']."&date_fin=".$Mvt[$i]['DATEMVT']."
				&mode=RES','popup','width=850,height=700,
				scrollbars=yes,resizable=yes,toolbar=no,directories=no,
				location=no,menubar=no,status=no,left=50,top=180'); return false\" href=\"../bmr/switch_popup.php?&nip=".$NIP."&date_debut=".$Mvt[0]['DATEMVT']."&date_fin=".$Mvt[$i]['DATEMVT']."&mode=RES\">
     Historique bacteriologie
    </a></td></tr>";
	
		
		
	}




	function SavePatient(){
		//TODO
	}


	function CreateTable($DP){
	//TODO :
	//	Page intermediaire avec choix suppression base de donnees existante
	//	si suppression dump de la base existante
	//	Description de l'insertion
		//TODO
		$PreFormatType=array();
		$PreFormatType[]='varchar(10)';
		$PreFormatType['nda']='varchar(10)';
		
		$DetailsFormulaire=$this->GetDetailsFormulaire($DP);
		$ReturLeg=array('date'=>0,'text'=>0,'num'=>0,'cs'=>0,'cm'=>0,'Libelle'=>NULL);
	
		$sqlT="create table ".$DP['NABV']." (
	
		ID  bigint(11) NOT NULL,
		VID  bigint(11) NOT NULL AUTO_INCREMENT,
		VTIME timestamp NOT NULL default CURRENT_TIMESTAMP,
		ACTIF  int default 1,
	
		USERID varchar(50) default NULL,";
		for ($i=0;$i<count($DetailsFormulaire[$DP['VABV']]);$i++){
			if($DetailsFormulaire[$DP['TYPE']][$i]==''){$ReturLeg['Libelle'].='Variable '.
				$DetailsFormulaire[$DP['LIBELLE']][$i].'('.
				$DetailsFormulaire[$DP['VABV']][$i].')'.'does not have type colomn have not been created in the table'.$DP['NABV'];
			}
			if($DetailsFormulaire[$DP['VABV']][$i]!=""&$DetailsFormulaire[$DP['TYPE']][$i]!=''&$DetailsFormulaire[$DP['VABV']][$i]!='ID'
			&$DetailsFormulaire[$DP['TYPE']][$i]!='cm-titre'&$DetailsFormulaire[$DP['TYPE']][$i]!='titre1'){
					$sqlT.=' '.$DetailsFormulaire[$DP['VABV']][$i];
					if($DetailsFormulaire[$DP['TYPE']][$i]=='date'){
						$sqlT.=' DATE default NULL,';
						$ReturLeg['date']++;
					}         
					if($DetailsFormulaire[$DP['TYPE']][$i]=='text') {
						  $sqlT.=' varchar(50) default NULL,';
						  $ReturLeg['text']++;
					}  
					if($DetailsFormulaire[$DP['TYPE']][$i]=='longtext') {
						  $sqlT.=' text default NULL,';
						  $ReturLeg['longtext']++;
					}         
					if($DetailsFormulaire[$DP['TYPE']][$i]=='num'){
					   $sqlT.=' int default NULL,';
					   $ReturLeg['num']++;
					}  
					if($DetailsFormulaire[$DP['TYPE']][$i]=='bigint'){
					   $sqlT.=' bigint(10) default NULL,';
					   $ReturLeg['bigint']++;
					}  
					if(substr($DetailsFormulaire[$DP['TYPE']][$i],1,5)=='numeric'){
					   $sqlT.=' '.$DetailsFormulaire[$DP['TYPE']][$i].' default NULL,';
					   $ReturLeg['numeric']++;
					}  
					if($DetailsFormulaire[$DP['TYPE']][$i]=='cm'){
					   $sqlT.=' enum("Oui","Non") DEFAULT NULL,';
					   $ReturLeg['cs']++;
					}  
					if($DetailsFormulaire[$DP['TYPE']][$i]=='cs') {
						$ReturLeg['cs']++;
						$sqlT.=' enum(';
						$enum = preg_replace ('!^enum\((.+)\)$!', '$1', $DetailsFormulaire[$DP['LEGENDE']][$i]);
						$enum = str_replace ("'", "", $enum);
						$enum = explode ('|', $enum);
								for ($k=0;$k<count($enum);$k++){
										$enum[$k]=trim($enum[$k]);
										$sqlT.='"'. $enum[$k].'",';
								}
						$sqlT=substr($sqlT,0,-1);
						$sqlT.=') DEFAULT NULL,';		
					}
					/* A reovoir !!!
					if(in_array($DetailsFormulaire[$DP['TYPE']][$i],array_keys($PreFormatType))){
					   $sqlT.=' '.$PreFormatType[$DetailsFormulaire[$DP['TYPE']][$i]].' DEFAULT NULL,';
						//echo 'toto : '.$DetailsFormulaire[$DP['TYPE']][$i].'<br>';
					   $ReturLeg['autre']++;
					} 
					*/ 
			}
		 
		}
		$sqlT.='	PRIMARY KEY (VID),INDEX (ID)
			)ENGINE=MyISAM DEFAULT CHARSET=latin1';

		$Res=parent::execute($sqlT);
		echo $sqlT.'<br>';
		echo 'La table';
		}


	function ListePatient($DP){
	echo ' <div id="content"> ';
		if($DP['NAMEPATIENT']){
		$select='select ID, VID, '.$DP['NAMEPATIENT'].','.$DP['FIRSTNAMEPATIENT'].','.$DP['IDPATIENT'].', UPPER(LEFT('.$DP['NAMEPATIENT'].',1)) as INI
			from '.$DP['NABV'].'
			WHERE ACTIF=1	LIMIT 0,99';
		$Res=parent::select($select);
		echo '<div class="tab" id="alphabetical"><div class="indexes columns">';
		echo 'Liste alphabetique';
		$null=0;
		echo '<br><br>';
	  	echo 	'	<ul class="index">';
		for($i=0;$i<count($Res);$i++){

			echo '	<a href="?Action=Modifier&ID='.$Res[$i]['ID'].'"><span>'.$Res[$i][$DP['IDPATIENT']].' <b>'.$Res[$i][$DP['NAMEPATIENT']].'
			'.$Res[$i][$DP['FIRSTNAMEPATIENT']].'</b></span></a><br>';
	
		}
		echo '</ul>';
		echo '<br><br><br><br>';
		echo '</div></div></div>';
		}
	
	
		if(!$DP['NAMEPATIENT']){
			$select='select ID, VID, '.$DP['IDPATIENT'].'
			from '.$DP['NABV'].'
			WHERE ACTIF=1';
		$Res=$DP['db']->select($select);
		echo '<div class="tab" id="alphabetical"><div class="indexes columns">';
		echo 'Liste des patients inclus';
		$null=0;
		echo '<br><br>';
	  	echo 	'	<ul class="index">';
		$tot=count($Res);$nbCol=($tot/3);$col=1;
		echo '<table><tr><td valing="top" width="20%">';
		for($i=0;$i<count($Res);$i++){
		
			echo '<a href="?Action=Modifier&ID='.$Res[$i]['ID'].'"><span>'.$Res[$i][$DP['IDPATIENT']].'</span></a><br>';
	
			if (($i/$col)>$nbCol){echo '</td><td valing="top" width="20%">';$col++;}
		}
		echo '</td></tr></table>';
		echo '</ul>';
		echo '<br><br><br><br>';
		echo '</div></div></div>';
		}
	}


	function SearchPatient($DP,$q){

		echo ' <div id="content"> ';
		if($DP['NAMEPATIENT']){
				$select='
					select VID,ID, '.$DP['NAMEPATIENT'].','.$DP['FIRSTNAMEPATIENT'].','.$DP['IDPATIENT'].'
					from '.$DP['NABV'].' where '.$DP['IDPATIENT'].' like "%'.$q.'%" AND ACTIF=1
					union 
					select VID,ID, '.$DP['NAMEPATIENT'].','.$DP['FIRSTNAMEPATIENT'].','.$DP['IDPATIENT'].'
					from '.$DP['NABV'].' where '.$DP['NAMEPATIENT'].' like "%'.$q.'%" AND ACTIF=1';
				}
		if(!$DP['NAMEPATIENT']){
				$select='
					select VID,ID,'.$DP['IDPATIENT'].'
					from '.$DP['NABV'].' where '.$DP['IDPATIENT'].' like "%'.$q.'%" AND ACTIF=1';
				}
		$Res=parent::select($select);
		echo '<div class="tab" id="alphabetical"><div class="indexes columns">';
		$null=0;

		if (!$Res){echo '<br><br><br><br>aucun resultats trouv&eacute;es<br><br><br><br><br><br><br><br><br>';}
		echo '<br><br><br>';  	
		echo 	'	<ul class="index">';
		for($i=0;$i<count($Res);$i++){
			if($DP['NAMEPATIENT']){
			echo '	<a href="?Action=Modifier&ID='.$Res[$i]['ID'].'"><span>'.$Res[$i][$DP['IDPATIENT']].' <b>'.$Res[$i][$DP['NAMEPATIENT']].'
			'.$Res[$i][$DP['FIRSTNAMEPATIENT']].'</b></span></a><br>';
			}
				if(!$DP['NAMEPATIENT']){
			echo '	<a href="?Action=Modifier&ID='.$Res[$i]['ID'].'">'.$Res[$i][$DP['IDPATIENT']].'</a><br>';
			}
		
		}
		echo '</ul>';
		echo '<br><br><br><br><br><br><br><br><br>';
		echo '</div></div>';
	}
}

?>
