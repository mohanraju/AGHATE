//###################################################################################################
//JQUERY FONCTION 
//###################################################################################################


//##############################################################
// function LoadPatInfo()
// Charger les patient infos 
//##############################################################
 
function LoadPatInfo()
{
		//recupare page variables
		var val_rech		=$("#val_rech").val();
		// lancer ajax pour rechercher les patients info
  	res=LanceAjax("../commun/ajax/ajax_resa_get_patinfo.php","val_rech="+val_rech) ;	
		
		//affiche l'infromations récuparér de ajax
		if(res.length > 1)
		{
			$('#pat_info').css({ 'display': ''});
			$("#pat_info").html(res);
		}else
			$("#pat_info").html("<span>Aucun patient trouvé</span>");	
}

//##############################################################
// function LoadResaInfo()
// Charger les resa infos 
//##############################################################
 
function LoadResaInfo()
{
		//recupare page variables
		var id		=$("#id").val();
		var mode	=$("#mode").val();
		// lancer ajax pour rechercher les patients info
  	res=LanceAjax("../commun/ajax/ajax_resa_get_resainfo.php","entry_id="+id+"&mode="+mode);
		
		//affiche l'informations récupéré de l'ajax
		if(res.length > 1)
		{
			res=res.split("|_|");
			if(mode =="view_resa")
			{
				$('#view_resa').css({ 'display': ''});
				$("#view_resa").html(res[0]);
			}
			else if(id !="")
			{
				//alert(res[1]);
				//$MyVar=$Result["nom"]." ".$Result["prenom"]." (".$Result["nip"].") (".$ddn.") (" .$Result["type"].") (tel:".$Result["tel"].")";
				//$return=$MyVar."|".$Result['noip']."|".$Result['description']."|".$Result['service_id']."|".$Result['service_name']."|".$Result['protocole']."|".$start_date."|".$duration."|".$dur_units;
				retval=res[1].split("|");
				//mettre a jour les champs varibles
				$("#val_rech").val(retval[0]);
				$("#nip").val(retval[1]);
				$("#description").val(retval[2]);
				$("#id_service").val(retval[3]);
				$("#service").val(retval[4]);
				$("#protocole").val(retval[5]);
				$("#date_deb").val(retval[6]);
				$("#duree").val(retval[7]);
				switch (retval[8]) {
				 case 'jours':
					$('#dur_units').val('J');
				 break;
				 case 'heures':
					$('#dur_units').val('H');
				 break;
				 case 'minutes':
					$('#dur_units').val('M');
				 break;
				}
			}
		}else
			$("#view_resa").html("<span>Aucune reservation trouvé</span>");	
}

function Create_Ligne(id_table,id_name,id_value){
	var ligne  = "<tr >";                                                                                                                              
			ligne += "	<td align='right'><div class='input-prepend input-append'><textarea readonly name=\""+id_name+"[]\"  style=\"resize: none;width:585px;height: 24px;font-size:12px;\">"+id_value+"</textarea >"
			ligne += "	<span class='add-on'><img id=\"BTN_DEL_"+id_name+"\"  src=\"../commun/images/l_cancel2.gif\" /></span></div></td>"
	    ligne += "</tr>";
	id_table.find("tbody tr:first").after(ligne);
}

function SavePage(val)
{
	var noip				 =$("#nip").val();
	var service_id =$("#id_service").val();
	var protocole_value  =$("#protocole").val();
	var start_time   =$("#date_deb").val();
	var duree      =$("#duree").val();
	var id    =$("#id").val();
	var dur_units    =$("#dur_units").val();
	var area    =$("#area").val();
	var room    =$("#room").val();
	var description    =$("#description").val();
	
	//alert(noip+" | "+service_id+" | "+protocole_value+" | "+start_time+" | "+duree+" | "+id);

  res=LanceAjax("../aghate/ajax_edit_entry_handler.php","noip="+noip+"&service_id="+service_id +"&protocole_value="+protocole_value+"&start_time="+start_time+"&duree="+duree+"&id="+id+"&dur_units="+dur_units+"&area="+area+"&room="+room+"&description="+description);	
	
	result=res.split('|');
	if (result[1] != "OK"){
		alert(result[2]);
		return false;
	}
		document.resa.mode.value="view_resa";
		document.resa.id.value=result[2];
		//document.resa.submit();
		document.forms["resa"].submit();
  
}
 

//=================================================================
// document ready
//=================================================================
 
$(document).ready(function() {	
	
	LoadResaInfo()

	//----------------------------------------
	// on blur sur champ recherche affiche les patients 
	//----------------------------------------
 	
	
	$("#val_rech").keypress(function (event) {
    if (event.keyCode == 10 || event.keyCode == 13) {
    	//alert('toto');
			//LoadPatInfo();		
		}
	});
	
	 //##############################################################
	//      REMOVE ROWS FROM DAS LIST
	//##############################################################
  
    $("#DAS_LIST").on("click", "#BTN_DEL_DAS", function() {
        $(this).parent().parent().remove();
        
    });		
    
	//----------------------------------------
	// on click sur le TR affiche les sejours
	//----------------------------------------
  //$(".Patients tbody >tr").click(function() {
   $('body').on('click', '.Patients tbody tr', function () 
   {  
	  	//get first element.text of the tr clicked	donc le nip
			var nip = $(this).closest('tr').find('td:eq(0)').text();
			$("#nip").val(nip);
			var val_rech = $(this).attr("MyVar");
			$("#val_rech").val(val_rech);
 
			$('#pat_info').css({'background-color':'#FFFFFF','display':'none','padding':'10px','padding-top':'0px','overflow-y':'auto','height':'150px','width':'100%'});
			$("#pat_info").html('');
  });	
 

})// fin doc ready
