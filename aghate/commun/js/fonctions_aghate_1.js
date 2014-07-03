// les script pour popup Panier 
$(document).ready(function() {
	
	$( "#div_patient_panier" ).dialog({
	 autoOpen: false,
	 height: 300,
	 width: 550,
	 modal: true
	});
	
});

//=================================================================
//  CalculDate()
// recupere les valeurs des balises dates et heure
// calcul la différence et retourne la différence
//=================================================================
function CalculDate(start_time,end_time)
{

	var date_heure_deb  = start_time;
	var res_deb 		= date_heure_deb.split(" ");
	var date_deb_res 	= res_deb[0].split("/");
	var day_deb 		= 	date_deb_res[0];                
	var month_deb 		= date_deb_res[1];            
	var year_deb 		= 	date_deb_res[2];
	
	
	var date_heure_fin = end_time;
	var res_fin = date_heure_fin.split(" ");
	var date_fin_res = res_fin[0].split("/");
	var day_fin = 	date_fin_res[0];                
	var month_fin = date_fin_res[1];            
	var year_fin = 	date_fin_res[2];
	
	var heure_deb_val = res_deb[1].split(":");
	var heure_deb = 	heure_deb_val[0];
	var min_deb = 		heure_deb_val[1];
	
	if(typeof min_deb == 'undefined')
	min_deb = '00';
	
	var heure_fin_val = res_fin[1].split(":");
	var heure_fin = 	heure_fin_val[0];                
	var min_fin = 		heure_fin_val[1];                
	
	if(typeof min_fin == 'undefined')
	min_deb = '00';
	
	
	//alert(year_deb+month_deb+day_deb+heure_deb+min_deb);
	//alert(year_fin+month_fin+day_fin+heure_fin+min_fin);
	        
	date1 = new Date(year_deb,month_deb,day_deb,heure_deb,min_deb,00);
	date2 = new Date(year_fin,month_fin,day_fin,heure_fin,min_fin,00);
	
	diff = dateDiff(date1,date2);
	return diff;
} 


//=================================================================
// dateDiff(date1, date2) prend deux dates en parametres
// return le nombre de jours, d'heures, de min, de sec de différence
// source : http://www.finalclap.com/faq/88-javascript-difference-date
//=================================================================
function dateDiff(date1, date2){
    var diff = {}                           // Initialisation du retour
    var tmp = date2 - date1;
 
	//alert(tmp);
    tmp = Math.floor(tmp/1000);             // Nombre de secondes entre les 2 dates
    diff.sec = tmp % 60;                    // Extraction du nombre de secondes
 
    tmp = Math.floor((tmp-diff.sec)/60);    // Nombre de minutes (partie entière)
    diff.min = tmp % 60;                    // Extraction du nombre de minutes
 
    tmp = Math.floor((tmp-diff.min)/60);    // Nombre d'heures (entières)
    diff.hour = tmp % 24;                   // Extraction du nombre d'heures
     
    tmp = Math.floor((tmp-diff.hour)/24);   // Nombre de jours restants
    diff.day = tmp;
     
    return diff;
}


//##############################################################
//LANCER AJAX avec le nom du script et parametres
//Scripturl = nom de url : example.php
//PageVariables = les varibales a passer donc "test=test&toto=toto&tati=tati"
//##############################################################
function LanceAjax(Scripturl,PageVariables){
	var html = $.ajax({
	  				url: Scripturl,
	  				data :PageVariables,
						beforeSend: function(xhr) {
							xhr.setRequestHeader('Content-type','utf-8');
						},		  				
	  				async: false,
						statusCode: {
							404: function() {
								alert("La page demandé n'existe pas ("+Scripturl+")");
							}
						}	  				
	 					}).responseText;
	 
 
	// trace les resultat dans log
	if($('#LOG').length > 0)
	{
		$( "#LOG" ).append("<br>Script :" + Scripturl +  PageVariables + "<br>Res =>" + html );		
	}
	 	 

	//return resultat 					
	 return html;
}
//##############################################################
// AfficheDetail permet d'afficher le détail des lits dans le service
// AreaId : Id du service
// Appelle la fonction lance ajax sur le fichier affiche_service_detail
// contenant le tableau du détail des lits du service
//##############################################################
 
 
function AfficheDetail(TrId,TableId,AreaId,mode)
{
		$("#"+TrId).toggle(50);
		
  	res=LanceAjax("./affiche_service_detail.php","AreaId="+AreaId+"&TableId="+TableId+"&TrId="+TrId+"&mode="+mode) ;	
  	//affiche les informations récuperer de ajax
		if(res.length > 1)
		{
			$("#"+TableId).html(res);
		}else
			$("#"+AreaDetailId).html("<span>Probleme d'execution Ajax</span>");	
} 

function ChampsDate(td_Id,check,room_id){
	
	res = LanceAjax("./champs_date.php","room="+room_id) ;
	if (check == true){
		if(res.length > 1){
			document.getElementById(td_Id).innerHTML=res;
		}
	}
	else{
		document.getElementById(td_Id).innerHTML="";
	}
}

//=============================================================================================
// Gestion Panier
// 	preparation de popup 
// 	gestion on click popup
//	affice les patients dans le popup(Ajax)
//=============================================================================================

// on click sur les nombre des patients
function Affiche_Panier(PanierId,Cdate,HeureDeb,HeureFin,ServiceId)
{
	width = 800;
	height = 600;
	if(window.innerWidth){
		var left = (window.innerWidth-width)/2;
		var top = (window.innerHeight-height)/2;
	}
	else{
		var left = (document.body.clientWidth-width)/2;
		var top = (document.body.clientHeight-height)/2;
	}
 	window.open("./popup_liste_panier.php?PanierId="+PanierId+"&Cdate="+Cdate+"&HeureDeb="+HeureDeb+"&HeureFin="+HeureFin+"&ServiceId="+ServiceId,'Panier','menubar=no, scrollbars=yes, top='+top+', left='+left+', width='+width+', height='+height+'') ;	
 	/*$("#PanierList").html(res);
 	$( "#div_patient_panier" ).dialog( "open" );*/
}




//=============================================================================================
// function onclick sur situation_lits
// 	preparation de popup 
// 	gestion on click popup
//	affice les patients dans le popup(Ajax)
//=============================================================================================

// on click sur les nombre des patients
function refresh_page(ModeAffichage)
{
	var today="";
	//recupaère le deernière date selectionnée
	today = $("input:text[name=today]" ).val();

	switch(ModeAffichage){
		case "journaliere":
			self.location="situation_lits.php?today="+today+"&vue=journaliere"; 
			break;
		case "hebdomadaire" :
			self.location="situation_lits.php?today="+today+"&vue=hebdomadaire"; 
			break;		
		case "mensuelle" :
			self.location="situation_lits.php?today="+today+"&vue=mensuelle"; 
			break;
		case "accueil" :
			self.location="situation_lits.php?today="+today+"&vue=accueil"; 
			break;
		default :				
			self.location="situation_lits.php?today="+today+"&vue=accueil"; 
			break;		
	}

}


//###################################################################################################
//JQUERY FONCTION 
//=============================================================================================
//function ValideDemande(TableLoc)
//=============================================================================================
 
function ValideDemande(TableLoc){
	_description=$("#description").val();
	var FormVariables = $("#FormResa").serialize();
	FormVariables=FormVariables+"&_description="+_description; 
	msg=" Voulez-vous valider cette demande ?";
	if (confirm(msg)==false) 
		return false;
	//alert(FormVariables+"&table_loc="+TableLoc);	
	res=LanceAjax("./commun/ajax/ajax_aghate_valide_demande.php",FormVariables+"&table_loc="+TableLoc);	
	result=res.split('|');
	//alert(result[1]);
	if (result[1] != "OK"){
		alert(result[2]);
		return false;
	}
	//alert( "../forms/reservation_aghate.php?id="+result[2]+"&table_loc="+TableLoc+"&type_reservation"+TypeResa);
	window.opener.location.reload(false);
	window.location.href = "../forms/reservation_aghate.php?id="+result[2]+"&table_loc=agt_loc&type_reservation=valide";
}
 
//=============================================================================================
//function function RefuseDemande(TableLoc)
//=============================================================================================
function RefuseDemande(TableLoc){
	var id    	=$("#id").val();
	msg			=" Voulez-vous refuser cette demande ?";
	if (confirm(msg)==false) 
		return false;
	else
	{
		var motif = prompt("Motif");
		//alert(motif);
		res=LanceAjax("./commun/ajax/ajax_aghate_refuse_demande.php","id="+id+"&table_loc="+TableLoc+"&motif="+motif);	
		result=res.split('|');
		if (result[1] != "OK"){
			alert(result[2]);
			return false;
		}
		window.opener.location.reload(false);
		//msg=" Réservation supprimé ";
		//if (confirm(msg)==false) return false;
		window.close();
	}
} 
 
//=============================================================================================
//function SavePage(TableLoc,TypeResa)
//=============================================================================================
function SavePage(TableLoc,TypeResa){
	//problème d'encodage avec les function serialize();
	_description=$("#description").val();
	var FormVariables = $("#FormResa").serialize();
	FormVariables=FormVariables+"&_description="+_description; 
	msg=" Voulez-vous enregistrer cette réservation ?";
	if (confirm(msg)==false) 
		return false;
	//alert("./commun/ajax/ajax_aghate_save_"+TypeResa.toLowerCase()+".php?"+FormVariables+"&table_loc="+TableLoc);			
	res=LanceAjax("./commun/ajax/ajax_aghate_save_"+TypeResa.toLowerCase()+".php",FormVariables+"&table_loc="+TableLoc);	
	$( "#LOG" ).append("\nSave resa:"+res );		
	result=res.split('|');

	if (result[1] != "OK"){
		alert(result[2]);
		return false;
	}
	//alert(TableLoc);
	window.opener.location.reload(false);
	window.location.href = "../forms/reservation_aghate.php?id="+result[2]+"&table_loc="+TableLoc+"&type_reservation="+TypeResa;
}
 

//=============================================================================================
//function SaveExamCompl(TableLoc,TypeResa)
//=============================================================================================
function SaveExamCompl(TableLoc,TypeResa){
	//problème d'encodage avec les function serialize();
	_description=$("#description").val();
	var FormVariables = $("#FormResa").serialize();
	FormVariables=FormVariables+"&_description="+_description; 
	msg=" Voulez-vous enregistrer cette réservation ?";
	if (confirm(msg)==false) 
		return false;
	//alert("./commun/ajax/ajax_aghate_save_"+TypeResa.toLowerCase()+".php?"+FormVariables+"&table_loc="+TableLoc);
	//return false;			
	res=LanceAjax("./commun/ajax/ajax_aghate_save_"+TypeResa.toLowerCase()+".php",FormVariables+"&table_loc="+TableLoc);	
	$( "#LOG" ).append("\nSave resa:"+res );		
	result=res.split('|');
	if (result[1] != "OK"){
		alert(result[2]);
		return false;
	}
	//alert(TableLoc);
	window.opener.location.reload(false);
	window.close();
}

 
//=============================================================================================
//function DelReservation(TableLoc,TypeResa)
//=============================================================================================
function DelReservation(TableLoc,TypeResa)
{
	var id    	=$("#id").val();
	msg			=" Voulez-vous définitivement supprimer  cette réservation ?";
	if (confirm(msg)==false) 
		return false;
	else
	{
		res=LanceAjax("./commun/ajax/ajax_aghate_del_"+TypeResa.toLowerCase()+".php","id="+id+"&table_loc="+TableLoc);	
		$( "#LOG" ).append("\nDel resa:"+res );				
		result=res.split('|');
		if (result[1] != "OK"){
			alert(result[2]);
			return false;
		}
		window.opener.location.reload(false);
		msg=" Réservation supprimé ";
		if (confirm(msg)==false) return false;
		window.close();
	}
}

//=============================================================================================
//function AnnuleReservation(TableLoc,TypeResa)
//=============================================================================================
function AnnuleReservation(TableLoc){
	var id    	=$("#id").val();
	msg			=" Voulez-vous annuler cette réservation ?";
	if (confirm(msg)==false) 
		return false;
	else {
		var motif = "";
		do {
			var motif = prompt("Motif de l'annulation ?");
			if(motif==null){
				motif = "";
			}
		}while(motif.length < 1);
		//alert("./commun/ajax/ajax_aghate_annule_reservation.php?"+"id="+id+"&motif="+motif+"&table_loc="+TableLoc);
		//return false;
		res=LanceAjax("./commun/ajax/ajax_aghate_annule_reservation.php","id="+id+"&motif="+motif+"&table_loc="+TableLoc);	
		$( "#LOG" ).append("\nAnnule resa:"+res );				
		result=res.split('|');
		if (result[1] != "OK"){
			alert(result[2]);
			return false;
		}
		window.opener.location.reload(false);
		window.close();
	}
}

//=============================================================================================
//function updateConsult(id_prog)
//=============================================================================================
function updateConsult(id_prog)
{
	var pagevariables="id_prog="+id_prog;
   	res=LanceAjax("./commun/ajax/ajax_aghate_consult_prog.php",pagevariables);
	result=res.split('|');
	if (result[1] != "OK"){
		alert(result[2]);
		return false;
	}
	window.location.reload(false);
}


//=============================================================================================
//function DelMedecin(val)
//=============================================================================================
function DelMedecin(val)
{
	msg=" Voulez-vous vraiment désactiver ce médecin ?";
	if (confirm(msg)==false) 
		return false;
	else
	{
		res=LanceAjax("./commun/ajax/ajax_aghate_del_medecin.php","id="+val);	
		$( "#LOG" ).append("\nDel medecin:"+res );		
		result=res.split('|');
		if (result[1] != "OK"){
			//alert(result[2]);
			return false;
		}
		
		msg=" Médecin supprimé ";
		if (confirm(msg)==false) {return false;}
			else {window.location.reload();}
		
	}
}


//=============================================================================================
//function DelProtocole(val)
//=============================================================================================
function DelProtocole(val)
{
	msg=" Voulez-vous vraiment désactiver ce protocole ?";
	if (confirm(msg)==false) 
		return false;
	else
	{
		res=LanceAjax("./commun/ajax/ajax_aghate_del_protocole.php","id="+val);	
		$( "#LOG" ).append("\nDel Protocole:"+res );					
		result=res.split('|');
		if (result[1] != "OK"){
			//alert(result[2]);
			return false;
		}
		
		msg=" Protocole supprimé ";
		if (confirm(msg)==false){ return false;}
			else{window.location.reload();}
	}
}
//##############################################################
// DIV PAT RECH
//##############################################################

jQuery.fn.AutoSuggest = function(ScriptAjax) 
{
// check element present dans la page
	
    var pos=$(this).offset();
    var h=$(this).height();
    var w=$(this).width();
	
		var l=$(this).position().left;
		var id=$(this).attr("id");
		var RechercheDiv = $('<div />').appendTo('form');
		var IdRechercheList="AutoSuggestRechercheList";
		var str="";
		var res;
		//create hidden var pour stocker selected val
		var HiddenID=$('<input>').attr('type','hidden').appendTo('form');
		HiddenID.attr('id', id+'__id');
		
		//RechercheDiv.html("Rechercher un patient<br> veuillez saisir ");
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
		RechercheDiv.hide();	// cacher au demarrage 
		$(this).keypress(function( event )
		{
			if ( event.which == 13 )
			{

				//récupère les valeurs saisie..
				var val_rech		=$(this).val();
				if(val_rech.length >2)
				{
					// lancer ajax pour rechercher les patients info
				  res=LanceAjax(ScriptAjax,"val_rech="+val_rech) ;
					//affiche l'infromations récuparér de ajax
					res=eval(res);
					//if(res.length > 0)
					if( (typeof res == "object") && (res !== null) )
					{
						
						var listselect="<select name='"+IdRechercheList+"' id='"+IdRechercheList+"'  size='15' style='width:450px;' >";
						// boucle sur les données					
					  $.each( res, function( index, pat){
								idval= pat.NIP+"|"+pat.NOM+"|"+pat.PRENOM +"|"+pat.DANAIS+"|"+pat.SEXE+"|";
								data = pat.NIP+" "+pat.NOM+ " "+pat.PRENOM +" ne(e)"+pat.DANAIS+" ("+pat.SEXE+")";
									listselect +=	"<option value='"+idval+"'>"+data+"</option>";
					 });
	
						$(RechercheDiv).html(listselect);
					}else
					{
						$(RechercheDiv).html("Aucune resultat trouvé!");	
						$(RechercheDiv).show();
					} 
				}
			}
		});
		//handling flash keys
		$(this).keyup(function (e) {
			if ((e.keyCode == 39)||(e.keyCode == 40)) {
				$("#"+IdRechercheList).focus();
			}
		});
		
    $(this).click(function(e) {
   		RechercheDiv.show();
    });
    
	// hide recherche div
	$('body').mouseup(function (e)
	{
		  RechercheDiv.hide();
	});
	
    //handling select option onclik on change
    //----------------------------------------
		$('body').on('click', '#'+IdRechercheList, function() {
	 		str=$(this).find("option:selected").text();
	    $("#"+id).val(str);
	 		vals=$(this).find("option:selected").val();	    	
	    $(HiddenID).val(vals);
	    $("#"+id).focus();
	    RechercheDiv.hide();
		});       
     //handling select option onclik on change
    //----------------------------------------
			$('body').on('keyup', '#'+IdRechercheList, function(event) {
			if(event.keyCode == 13)
			{
	 			str=$(this).find("option:selected").text();
	    	$("#"+id).val(str);
	 			vals=$(this).find("option:selected").val();	    	
	    	$(HiddenID).val(vals);
	    	$("#"+id).focus();
	    	RechercheDiv.hide();
	    }
		});
};


//##############################################################
// charge Rooms en function de service
//##############################################################

function RechargeRooms(DivRooms)
{
 
	//recupare page variables
	var id_service	=$("#id_service").val();
	var room_id		=$("#room").val();
	if (id_service.length < 1)
	{
			alert('Invalide service / localisation ID');
			return false;
			
	}
	res=LanceAjax("../aghate/commun/ajax/ajax_edit_entry_change_room.php","service_id="+id_service+"room_id="+room_id) ;	
	//affiche l'infromations récuparér de ajax
	var lit = '<span class="add-on"><b>Lit :</b>&nbsp;&nbsp;&nbsp;&nbsp;</span>';	
	if(res.length > 1)
	{
		$(DivRooms).html(lit+res);
	}else
		$(DivRooms).html(lit);	
}
//##############################################################
//  popup url
//##############################################################

 function OpenPopupResa(url) {

    mywindow1=window.open(url,'myname','resizable=yes,width=850,height=670,left=150,top=100,status=yes,scrollbars=yes');
    mywindow1.location.href = url;
    if (mywindow1.opener == null) mywindow1.opener = self;
}
 
 
 
