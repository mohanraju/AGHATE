//##############################################################
// function d'appel pour AJAX par Mohanraju
//##############################################################
 
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
								alert("Le page demandé n'existe pas ("+Scripturl+")");
							}
						}	  				
	 					}).responseText;
	 return html;
}

$(document).ready(function() {	
	
    $(".table").on("click", "#Etat_bloc", function() {
    	
    	if($(this).attr("ipop_id")=="ACTES_OK")
    	{
    		alert("Vous ne pouvez pas annuler ce séjour, les Actes sont déjà codés");
    		return false;	
    	}
    	
    	var res=LanceAjax("../commun/ajax/ajax_ipop_update_etat.php","ipop_id="+$(this).attr("ipop_id"))
    	if(res=="KO")
    	{
    		$(this).attr("src","../commun/images/ko.jpg");
    		$(this).parent().prev().text('Annulation'); 
				$(this).parent().prev().css("background-color","white");    		   		
    	}else if(res=="OK")
    	{
    		$(this).attr("src","../commun/images/ok.jpg"); 
    		$(this).parent().prev().text('');
    		$(this).parent().prev().css("background-color","#FF9933");    		   		
    	}else	
    		alert(res);	
    });		

	
})// fin document ready


