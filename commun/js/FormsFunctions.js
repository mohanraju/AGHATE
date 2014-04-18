function LanceAjax(Scripturl,PageVariables){
		var html = $.ajax({
		  				url: Scripturl,
		  				data :PageVariables,
							beforeSend: function(xhr) {
								xhr.setRequestHeader('Content-type','utf-8');
							},		  				
		  				async: false
		 					}).responseText;
		 return html;
	}

function SetColorInput(Name,Id)
{
	//recupère le bgcolor du current element
	var ColorActuel=$("#LBL_"+Name+Id).attr('class');

	//remettre tous comme non selectionnées 
	$("a[id^='LBL_"+Name+"']").each(function() {
			$(this).removeClass('OptionSelected').addClass('OptionNotSelected');		    
	});	
	
	//
	if(ColorActuel=="OptionNotSelected")
 	{
		$("#LBL_"+Name+Id).removeClass('OptionNotSelected').addClass('OptionSelected');
		$("#"+Name).val($("#LBL_"+Name+Id).attr('cval'));	
 	}else
 	{
 		$("#"+Name).val('99');	
 	}
}

function updateForms(VID,Name,Id,Val,Source,ABV,libelle,compdata) {

		var ColorActuel=$("#LBL_"+Name+Id).attr('class');
		var valeur=Val;

		if($("#"+Name).attr('type')=='textarea')valeur=$('#'+Name).val();
		//valeur=$('#'+Name).val();
		if(ColorActuel=="OptionNotSelected")valeur='99';
		var pagevariables="FormUpdate_VID="+VID+"&FormUpdate_REF="+$('#ref').val()+"&FormUpdate_Var="+Name+"&FormUpdate_Libelle="+libelle+"&FormUpdate_Val="+valeur+"&Source="+Source+"&FormUpdate_ABV="+ABV+"&FormUpdate_Nip="+$('#nip').val()+"&FormUpdate_Nda="+$('#nda').val()+"&FormUpdate_Nohjo="+$('#nohjo').val()+"&FormUpdate_Entry="+$('#entry').val()+"&FormUpdate_End="+$('#end').val()+"&FormUpdate_Uhdem="+$('#uhdem').val()+"&FormUpdate_Uhexec="+$('#uhexec').val()+"&FormUpdate_Libuhdem="+$('#service_name').val()+"&FormUpdate_Type="+$('#type_codage').val()+"&FormUpdate_Username="+$('#Username').val()+"&FormUpdate_SaveDateTime="+$('#SaveDateTime').val()	;
		//alert(pagevariables);
	 		res=LanceAjax("../commun/ajax/Ajax_updateForms.php",pagevariables);
	 		//alert(res);
	 		if(compdata=='o'){
				res=LanceAjax("../aghate/commun/ajax/ajax_update_description.php",pagevariables+"&table_loc="+$("#table_loc").val());
			}
			var InitForm=$('#InitForm').val();	
			if(InitForm=='No'){
				var pagevariables2="FormUpdate_VID="+VID+"&FormUpdate_REF="+$('#ref').val()+"&FormUpdate_Var=Form&FormUpdate_Libelle=Nom du formulaire&FormUpdate_Val="+$('#InitForm').attr('name')+"&Source=forms&FormUpdate_ABV="+ABV+"&FormUpdate_Nip="+$('#nip').val()+"&FormUpdate_Nda="+$('#nda').val()+"&FormUpdate_Nohjo="+$('#nohjo').val()+"&FormUpdate_Entry="+$('#entry').val()+"&FormUpdate_End="+$('#end').val()+"&FormUpdate_Uhdem="+$('#uhdem').val()+"&FormUpdate_Uhexec="+$('#uhexec').val()+"&FormUpdate_Libuhdem="+$('#service_name').val()+"&FormUpdate_Type="+$('#type_codage').val()+"&FormUpdate_Username="+$('#Username').val()+"&FormUpdate_SaveDateTime="+$('#SaveDateTime').val()	;
				res2=LanceAjax("../commun/ajax/Ajax_updateForms.php",pagevariables2);
				$("#InitForm").val("Yes");
			}
   		if (res=="KO")alert("Erreur id veuillez reessayer plus tard..");
   		
}


function disableModifier()
{
	var lien=$("[id^=LBL_]");
	
	for(var i=0;i<lien.length;i++)
	{
		lien[i].removeAttribute("onclick");
	}
}

function Create_LigneOnblur(id_table,id_name,id_value,pagevar){
	var ligne  = "<tr >";                                                                                                                              
			ligne += "	<td align='left'><div class='input-prepend input-append'><textarea readonly name=\""+id_name+"\"  style=\"resize: none;width:255px;height: 24px;font-size:12px;\">"+id_value+"</textarea >"
			ligne += "	<span class='add-on'><img id=\"BTN_DEL_"+id_table.attr('id')+"_"+id_name+"\"  src=\"../commun/images/l_cancel2.gif\" /></span></div></td>"
	    ligne += "</tr>";
	id_table.find("tbody tr:first").after(ligne);
	$("#"+id_table.attr('id')).on("click", "#BTN_DEL_"+id_table.attr('id')+"_"+id_name, function() {
		$(this).parent().parent().remove();
	pagevariables='FormUpdate_VidChamps='+id_name+'&'+pagevar;
		res=LanceAjax('../commun/ajax/Ajax_updateFormsFields.php',pagevariables);
		
	});
	
}

function Create_Ligne_FielsOnblur(id_table,id_source,id_name,id_value,id_value_fields,pagevar,mode){
	var ligne  = "<tr >";                                                                                                                              
			ligne += "	<td align='left'><div class='input-prepend input-append'><textarea readonly name=\""+id_name+"\"  style=\"resize: none;width:255px;height: 24px;font-size:12px;\">"+id_value+"</textarea ><input type='text' Fields='oui' style=\"width:70px;\" name=\""+id_name+"\" DataSource=\""+id_source+"\" id=\""+id_name+"\" value=\""+id_value_fields+"\""+mode+">"
			ligne += "	<span class='add-on'><img id=\"BTN_DEL_"+id_table.attr('id')+"_"+id_name+"\"  src=\"../commun/images/l_cancel2.gif\" /></span></div></td>"
	    ligne += "</tr>";
	id_table.find("tbody tr:first").after(ligne);
	$("#"+id_table.attr('id')).on("click", "#BTN_DEL_"+id_table.attr('id')+"_"+id_name, function() {
		$(this).parent().parent().remove();
		pagevariables='FormUpdate_VidChamps='+id_name+'&'+pagevar;
		res=LanceAjax('../commun/ajax/Ajax_updateFormsFields.php',pagevariables);
	});
}

function disableModifier(){
	var lien=$("[id^=LBL_]");
	var compare;
	for(var i=0;i<lien.length;i++){
		compare=lien[i].id;
			compare=compare.substring(0,compare.length-1);
			if(compare != "LBL_forms")
			lien[i].removeAttribute("onclick");
	}
}
function getLink(id,balise,TableLoc){
	window.location.href="reservation_aghate.php?id="+id+"&filename="+balise.value+"&mode=Modifier"+"&table_loc="+TableLoc;
}
function formsView(){
	var id = $("#name").val();
	var Image_Node = document.getElementById(id);
	Image_Node.setAttribute("class","FileSelected");	
	return false;
}
function returnView(file,id,TableLoc,script_name){
	
	//var TableLoc = $("table_loc").val();
	//alert(TableLoc);
	var variables="filename="+file;
	res=LanceAjax("../commun/ajax/ajax_form_retourVue.php",variables);
	if (res=="KO")alert("Erreur id veuillez reessayer plus tard..");
	if(res.length>0){
		window.location =script_name+"?id="+id+"&filename="+file+"&table_loc="+TableLoc+"&name=LBL_forms"+res;
	}
	else{
		window.location =script_name+"?id="+id+"&filename="+file+"&table_loc="+TableLoc;
	}   		
}
//=================================================================
// document ready
//=================================================================
 
$(document).ready(function() {	
	
   
 	//----------------------------------------
	// on blur sur champ recherche affiche les patients 
	//----------------------------------------
 	
	
	$("#val_rech").keypress(function (event) {
    if (event.keyCode == 10 || event.keyCode == 13) {
    	//alert('toto');
			//LoadPatInfo();		
		}
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

//##############################################################
// DIV PAT RECH
//##############################################################

jQuery.fn.AutoSuggest = function(ScriptAjax) 
{

    var pos=$(this).offset();
    var h=$(this).height();
    var w=$(this).width();
		var l=$(this).position().left;
		var id=$(this).attr("id");
		var RechercheDiv = $('<div />').appendTo('body');
		var str="";
		//RechercheDiv.html("Recherer un patient<br> vuillez saisir ");
		RechercheDiv.attr('id', 'AutoSuggest');
		RechercheDiv.css({ 
				left: pos.left ,
				top: pos.top + h + 10,
				Height :200,
				display: 'inline-block',
				position: 'absolute',
				border:'4px solid #D4D0C8',
				'background-color':'#F7F7F7'
			 });
		RechercheDiv.hide();	// cahcer au demarage 
		$(this).keypress(function( event )
		{
			if ( event.which == 13 )
			{
				l=$(this).offset().left;
				RechercheDiv.css({'left':l});
				//récupère les valeurs saisie..
				var val_rech		=$(this).val();
				// lancer ajax pour rechercher les patients info
			  res=LanceAjax(ScriptAjax,"val_rech="+val_rech) ;	
				//affiche l'infromations récuparér de ajax
				if(res.length > 1)
				{
					res=eval(res);
					var listselect="<select name='AutoSuggestRechercheList' id='AutoSuggestRechercheList'  size='15' style='width:450px;' >";
					// boucle sur les données					
		      $.each( res, function( index, pat){
		      		data 				= pat.NIP+" "+pat.NOM+ " "+pat.PRENOM +" ne(e)"+pat.DANAIS+" ("+pat.SEXE+")";
		 					listselect +=	"<option value='"+pat.NIP+"'>"+data+"</option>";
		      });

					$(AutoSuggest).html(listselect);
				}else
				{
					$(AutoSuggest).html("Aucune resultat trouvé!");	
					$(AutoSuggest).show();
				} 
			}
		});
		//handling flash keys
		$(this).keyup(function (e) {
			if ((e.keyCode == 39)||(e.keyCode == 40)) {
				$("#AutoSuggestRechercheList").focus();
			}
		});
		
    $(this).click(function(e) {
   		RechercheDiv.show();
    });

    //handling select option onclik on change
    //----------------------------------------
		$('body').on('click', '#AutoSuggestRechercheList', function() {
	 		str=$(this).find("option:selected").text();
	    $("#"+id).val(str);
	    RechercheDiv.hide();
		});    
     //handling select option onclik on change
    //----------------------------------------
		$('body').on('keyup', '#AutoSuggestRechercheList', function(event) {
			if(event.keyCode == 13)
			{
	 			str=$(this).find("option:selected").text();
	    	$("#"+id).val(str);
	    	RechercheDiv.hide();
	    }
		});
};

