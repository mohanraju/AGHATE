<?php
/*
CLASS HTML
Objet INPUT elements

*/

Class Html extends MySQL
{
	var $__Liste;


	//objet init	
	function Html($LoadListe=false)
	{
		if ($LoadListe)
		$this->PrepareListe();
	}	
	/*
	===========================================================================================
	Preparation des Liste pour multi choix (Radio /Select)
	Les donnés sont a recupare de Mysql
	
	===========================================================================================
	*/	
	function PrepareListe()
	{
		//recupare les listes défini dans le base Mysql
  	$db1 = new MYSQL($DBName);		
		$sql="SELECT grp from listes group by grp";
		$res=$db1->select($sql);

		//prepare liste		
		$list_dispo="";
		for($i=0;$i<count($res);$i++){
			$c_grp=$res[$i]['grp'];
			$list_dispo.=$res[$i]['grp']."<br>";
			$sqltemp="SELECT lib_value,libelle from listes where grp='".$c_grp."'  order by tri";
			$restemp=$db1->select($sqltemp);
			
			$arr_val="";
			for($k=0;$k<count($restemp);$k++){
				$arr_val.=$restemp[$k]['lib_value']."|".($restemp[$k]['libelle'])."@";
				//echo "<br>			".$arr_val;
			}	
			if(strlen($arr_val) > 1)	{
				$arr_val=substr($arr_val,0,strlen($arr_val)-1); // suprimmer le dernière vircule
				$$c_grp= explode("@",$arr_val);
				$this->__Liste[$c_grp]= explode("@",$arr_val);
			}
			
		}
		
	}
 

	//=================================================
	//	function  InputTextBox Par Mohan
	//=================================================
	function InputTextBox($VariableName,$default,$maxlength,$size,$autres="")	{
			if($_SESSION["readonly"]=="true")
				$cache="readonly disabled";
			$res="";
			$res.="<input type=\"text\" name=\"$VariableName\" id=\"$VariableName\" maxlength=\"$maxlength\" size=\"$size\" value=\"$default\" $autres $cache>";
			return $res;
	}
	
	//=================================================
	//	function  InputHiddenBox Par Thierry
	//=================================================
	function InputHiddenBox($VariableName,$default)	{
			$res="";
			$res.="<input type=\"hidden\" name=\"$VariableName\" id=\"$VariableName\" value=\"$default\">";
			return $res;
	}
			
	//=================================================
	// function InputCheckbox par mohan
	// default peut être 1 ou  null
	//=================================================
	function InputCheckbox($VariableName,$default,$lib="",$autres="")	{

		//getion read only
		if($_SESSION["readonly"]=="true")
				$cache="disabled";

		//checked
		if (strlen($default) > 0){
			$checked="checked";	
		}
		//preare retval
		$res=" <label class=\"checkbox inline\">
							<input  
							type=\"checkbox\"  
							name=\"$VariableName\"  
							id=\"$VariableName\" 
							value=\"1\" 
							$checked 
							$autres
							$cache >
							$lib 
							</label>";
		
		return $res;
	}

	//=================================================
	// function InputSelectFromArray par thierry
	// input select v2
	//================================================
	
	function InputSelectFromArray($List,$VariableName,$default,$autres='')	{
		// gestion readonly
		if($_SESSION["readonly"]=="true")
			$cache="disabled";

		// check taille 
		if ($taille > 1)
			$_taille="WIDTH='$taille' STYLE='width: ".$taille."px'";
		
	 	$res= "<select name='".$VariableName."' class=form ".$autres.">";
	 	foreach($List as $i=>$val){
	 		if ($default==$i)
		 		$res.= "<option value='$i' selected>".$val."</option>";
		 	else
		 		$res.= "<option value='$i' >".$val."</option>";
		}
		$res.= "</select>";
		return $res;
	}
	
	/*	
	//=================================================
	// function InputSelect par mohan
		$modelname1 : liste de variable sous form d'un tableau
									chaque element de tableau contenent le variable et son value dans le meme champ separer par un |	
									ex 033|France 
	//$VariableName,
	//$default,$taille="
	//===============================================
	*/
	
	function InputSelect($modelname1,$VariableName,$default,$taille="",$autres="")	{

		$modelname=&$modelname1;

		// gestion readonly
		if($_SESSION["readonly"]=="true")
			$cache="disabled";

		// check taille 
		if ($taille > 1)
			$_taille="WIDTH='$taille' STYLE='width: ".$taille."px'";
		
	 	$res= "<select $_taille name='".$VariableName."' id='".$VariableName."' $autres $cache>";

		// check nbr elements et proposee selectionne par default	 	
	 	if(count($modelname) >1 ){
			$res.= "<option value='' selected>Selectionnez</option>";
		}
	 	for($i=0;$i < count($modelname);$i++){
	 		list($val,$libelle)=explode("|",$modelname[$i]);

	 		if ($default==$val)
		 		$res.= "<option value='$val' selected>".htmlentities($libelle)."</option>";
		 	else
		 		$res.= "<option value='$val' >".htmlentities($libelle)."</option>";
		 	
		}
		 		$res.= "</select>";
		return $res;
	}

	/*	
	//=================================================
	// function InputOptSelect
		$Listes = array[opt1] = array()
						  array[opt2] = array()
						  ....
		dans opt chaque element de tableau contenent le variable et son value dans le meme champ separer par un |	
						ex 033|France 
	//$VariableName,
	//$default,$taille="
	//===============================================
	*/
	function InputOptSelect($Listes,$VariableName,$default,$taille="",$autres="")	{
			if($_SESSION["readonly"]=="true")
				$cache="disabled";
			if ($taille > 1)
				$_taille="WIDTH='$taille' STYLE='width: ".$taille."px'";
			
		 	$res= "<select $_taille name=".$VariableName." id=".$VariableName." $autres $cache>";
		 	
		 	if(count($Listes) > 1 ){
				$res.= "<option value='' selected>Selectionnez</option>";
			}
			// boucle sur chaque OPT group
			$compteur=0;
			foreach ($Listes as  $key => $value)
			{
				//fermer l'ancien opt group
				if($compteur > 0)
					$res.="</optgroup>"; 
				$compteur++;
				//ouverutre d'un nouveau opt group	
				$res.="<optgroup label='".$key."'>"; 
				$modelname=$value;				
			 	for($i=0;$i < count($modelname);$i++){
			 		list($val,$libelle)=explode("|",$modelname[$i]);
	
			 		if ($default==$val)
				 		$res.= "<option value='$val' selected>".htmlentities($libelle)."</option>";
				 	else
				 		$res.= "<option value='$val' >".htmlentities($libelle)."</option>";
				 	
				}
			}
			//fermer le dernière  opt group
			if($compteur > 0)
				$res.="</optgroup>"; 
	 		$res.= "</select>";
			return $res;
	}
	
	//=================================================
	// function InputRadio par thierry
	// default peut être 1 ou  null
	//================================================
	
	function InputRadio($modelname1,$VariableName,$default,$events="",$vertical=false)	{
			if($_SESSION["readonly"]=="true")
				$cache="disabled";
			
			if($vertical)
				$affiche="<br>";
			
			$modelname=&$modelname1;
 
			$lbl="lbl";
	 
			$taile=count($modelname);
		 	for($i=0;$i < $taile ;$i++){
		 		list($val,$libelle)=explode("|",$modelname[$i]);
		 		if ($default==$val)
		 			$res.="<label class='radio inline'><input class='inline' type='radio'  name='$VariableName' id='$VariableName' value='$val' checked  $events $cache> ".htmlentities($libelle)."</label>".$affiche;
			 	else
			 		$res.="<label class='radio inline'><input class='inline' type='radio'  name='$VariableName' id='$VariableName' value='$val' $events $cache> ".htmlentities($libelle)."</label>".$affiche;
			 	
			}
	 
			return $res;
	}
	
	//=================================================
	// function InputChoix Par Mohan
	// ATTN si default  est vide 99 est assignée
	//===============================================
	
	function InputChoix($modelname,$VariableName,$default,$events="",$vertical=false)	{
			if($vertical)
				$RetourLinge="<br>";
				
			$taile=count($modelname);
		
			 	for($i=0;$i < $taile ;$i++)
			 	{
			 		list($val,$libelle)=explode("|",$modelname[$i]);

			 		//focrce class pour default
			 		if ($default==$val)
			 			$class="OptionSelected";
			 		else	
			 			$class="OptionNonSelected";
			 		
					//prepare affichage			 		
					$retval	.= "<a href='#' 
											onclick=\"CocherElements('".$i."','$VariableName');$events\"
											class=\"".$class."\"
											id='LBL_".$VariableName.$i."' 
											cval='".$val."'>
											".$libelle."
											</a>
											&nbsp&nbsp;".$RetourLinge;			 		
				}
				
				//hidden varible pour stocker le value selected
				if(strlen($default) <  1)
					$default='99';
					
				$retval .= "<input type='hidden' name='".$VariableName."' id='".$VariableName."' value='".$default."'>";

			return $retval;
	}
		
	function InputChoice($Name,$Values,$default,$vertical=false,$VID,$SOURCE,$ABV)	{
			if($vertical)
				$NewLine="<br>";
				

			 	for($i=0;$i < count($Values) ;$i++)
			 	{
			 		if ($default==$Values[$i])$class="OptionSelected";
			 		else $class="OptionNotSelected";	
			 		$retval	.="<a style='vertical-align:bottom;'	href ='#?'
									onclick=\"SetColorInput('".$Name."','".$i."');updateForms('$VID','$Name','$i','$Values[$i]','$SOURCE','$ABV')\"
									class=\"". $class."\"
									id='LBL_".$Name.$i."' 
									cval='".$Values[$i]."'>".$Values[$i]."</a>	&nbsp;&nbsp;";
							 		
			
				}
				
				//hidden variable pour stocker le value selected
				if(strlen($default) <  1)
					$default='99';
					
				$retval .= "<input type='hidden' name='".$Name."' id='".$Name."' value='".$default."'>";
			return $retval;
	}
	
	
	//=================================================
	//	function  InputCompletSimple Par Thierry
	//=================================================
	function InputCompletSimple($VariableName,$Source,$default,$defaultCode,$table,$COND1,$COND2,$ajax,$TypeAttribut,$compdata,$id='',$autres='')	{
			if($id=='') $id=$VariableName;
			$source="'../commun/ajax/".$ajax.".php?tb=".$table."&code=".$COND1."&lib=".$COND2."'";
			$res="";
			$res.="<input type=\"text\" DataSource=\"".$Source."\" CompData=\"".$compdata."\" name=\"$id\" id=\"$VariableName\" value=\"$default\" code=\"$defaultCode\" TypeAttribut=\"".$TypeAttribut."\" onfocus=\"$('#$VariableName').autocomplete({ 
			source: $source,
			minLength: 3, 
			open: function(event, ui) {
				      $(this).removeClass('working');
	            $(this).autocomplete('widget').css({'width': 580, 'height' :400,'overflow-y': 'scroll', 'overflow-x': 'hidden'});	            
	     },
	     select :function(event, ui){
	     	var el = document.getElementById('$VariableName');
 					el.setAttribute('code',ui.item.id);
 					el.setAttribute('value' , ui.item.value);
	     	
	     		$('#$VariableName').val(ui.item.value);
	     	
	     	switch ($('#$VariableName').attr('name')) {
				 case 'protocole':
					$('#duree').val(ui.item.duree);
					$('#dur_units').val('M');
				 break;
				}
					return false;
			 	},	     
	
	     response: function( event, ui ) {
					$(this).removeClass('working');     	
	     },
	     error: function (result) {  
	        alert('En raison d\'erreurs inattendues nous ne pouvons pas charger les diagnostiques!');  
	     }  
		});
		\" $autres >";
			return $res;
	}
	
	//=================================================
	//	function  InputCompletMulti Par Thierry
	//=================================================
	function InputCompletMulti($VariableName,$Source,$default,$table,$COND1,$COND2,$ajax,$TypeAttribut,$Libelle,$Fields,$autres='')	{
			if($id=='') $id=$VariableName;
			$source="'../commun/ajax/".$ajax.".php?tb=".$table."&code=".$COND1."&lib=".$COND2."'";
			
			$res="<table id='$TypeAttribut' width='95%' border='0' cellspacing='0'>
		    		<tr>
							<td width='100%'>
								<div class='input-prepend input-append'>
									<span class='add-on'>$Libelle</span>";
			$res.="<input type=\"text\" save=\"non\" name=\"$VariableName\" id=\"$VariableName\" Fields=\"$Fields\" value=\"\" onfocus=\"$('#$VariableName').autocomplete({ 
			source: $source,
			minLength: 3, 
			open: function(event, ui) {
			      $(this).removeClass('working');
	            $(this).autocomplete('widget').css({
	                'width': 580, 'height' :400,
									'overflow-y': 'scroll', 'overflow-x': 'hidden'                
	            });
            
     },
		 select :function(event, ui){
		 	lib=ui.item.value;
   		codes=ui.item.id;
   		retval=lib+'|'+codes;
   		
		 	var uhexec=$('#uhexec').val();
			var nda=$('#nda').val();
			var TypeAttribut= $(this).attr('TypeAttribut');
			var Fields= $(this).attr('Fields');
			var entry=$('#entry').val();
			entry=entry.substr(6,4)+'-'+entry.substr(3,2)+'-'+entry.substr(0,2) ;
			var end=$('#end').val();
			end=end.substr(6,4)+'-'+end.substr(3,2)+'-'+end.substr(0,2) ;
			
		
		 	var pagevariables='FormUpdate_VID='+$('#VID').val()+'&FormUpdate_ID=$id&FormUpdate_REF=AGHATE&FormUpdate_Var=$VariableName&Source=$Source&FormUpdate_Nip='+$('#nip').val()+'&FormUpdate_Nda='+nda+'&FormUpdate_Nohjo='+$('#nohjo').val()+'&FormUpdate_Entry='+entry+'&FormUpdate_End='+end+'&FormUpdate_Uhdem='+$('#uhdem').val()+'&FormUpdate_Uhexec='+$('#uhexec').val()+'&FormUpdate_Libuhdem=&FormUpdate_Username='+$('#Username').val()+'&FormUpdate_SaveDateTime='+$('#SaveDateTime').val()+'&FormUpdate_Val='+codes+'&FormUpdate_Libelle='+lib+'&FormUpdate_TypeAttribut=$TypeAttribut';
			//alert(pagevariables);
			var Create_LigneVariables='FormUpdate_VID='+$('#VID').val()+'&Source=$Source';
     		res=LanceAjax('../commun/ajax/Ajax_updateForms.php',pagevariables);
     		var tbl = $('#$TypeAttribut');
     		var fieldsval = '';
     		//alert(tbl);
     		if(Fields != '')
     			Create_Ligne_FielsOnblur(tbl,'$Source',res,lib,fieldsval,Create_LigneVariables,'$autres');
     		else
     			Create_LigneOnblur(tbl,res,lib,Create_LigneVariables);
     		$('#$VariableName').val('');
				return false;
		 	},	     
	
	     response: function( event, ui ) {
					$(this).removeClass('working');     	
	     },
	     error: function (result) {  
	        alert('En raison d\'erreurs inattendues nous ne pouvons pas charger les diagnostiques!');  
	     }  
		});
		\" $autres >
			<span class='add-on'><img id='BTN_ADD_$TypeAttribut' src='../commun/images/l_add.gif'  /></span>
								</div>
							</td>
						</tr>";
			if(is_array($default))
			{
				foreach($default as $key=>$value){
								$idF=$key;$value=addslashes($default[$key][libdiag]);$code=$default[$key][diag];$date=$default[$key][$Fields];
								//$restbl.="key ".$idF." value ".$value."<br>";
								//$tbl='toto <script>tbl = $("#$TypeAttribut"); </script>';
								//echo $tbl;
								if($Fields!="")
									$restbl.="<script>var tbl = $('#$TypeAttribut'); var VID = $('#VID').val();Create_Ligne_FielsOnblur(tbl,'$Source',$idF,'$value','$date','&FormUpdate_VID='+VID+'&Source=$Source','$autres');</script>";
								else
									$restbl.="<script>var tbl = $('#$TypeAttribut'); var VID = $('#VID').val();Create_LigneOnblur(tbl,$idF,'$value','&FormUpdate_VID='+VID+'&Source=$Source');</script>";
								
							}
			}
						$res.="</table>";
						$res.=$restbl;
			return $res;
	}


/*//##############################################################
	//       ADD ROWS TO DAS LIST
	//##############################################################
    // Add button functionality
    $("#BTN_ADD_DAS").click(function() {
  
        var tbl_das = $(this).parents("#DAS_LIST");
        var das=$("#ID_DAS").val();
        // si aucun valeur saisie 

        if(das.length < 2)
        {
        	 return false;
        }
        Create_Ligne(tbl_das,"DAS",das);  
      $("#ID_DAS").val("");
    });  
	//#################################
	//  ENTER KEY HANDLING in DAS     #
	//#################################
	$('#ID_DAS').bind('keypress', function(e) {
		if(e.keyCode==13){
 			var das=$("#ID_DAS").val();			
 			if(das.length == 1)
      	{
        	$("#ID_DAS").val("");
        	return false;
       	}else
				{			
					$("#BTN_ADD_DAS").trigger("click");
				}
		}
	});*/
		
	 
		
}// fin class


?>
