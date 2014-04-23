/*
############################################################
partie Java script du module changet_hopital
A inclure avant body dans le header avant d'appller
fonctionnes avec 
ajax_changer_hopital.html
ajax_changer_hopital.php
############################################################
*/
$(function() {
	var site = $( "#site" );	 
	if(!($('#IdChangerHopital').length))
	{
		return false;
	}
	
	$("#IdChangerHopital" ).dialog({
		autoOpen: false,
		height: 230,
		width: 400,
		modal: true,
		buttons: {
			"Changer": function() {
				var reponse=$.ajax({
							  				url:"../commun/ajax/ajax_changer_hopital.php",
							  				data :{site :site.val() } ,
												beforeSend: function(xhr) {
													xhr.setRequestHeader('Content-type','utf-8');
												},		  				
							  				async: false
							 					}).responseText;			
				if(reponse=="KO"){
					$("#ErreurMessages").html("Impossible de changer le site");
				}else{
					$("#ErreurMessages").html("La configuration du site courant est forcé à <b>"+site.val()+ "</b><br> Veuillez fermer cette fênetre puis actualisez la page pour appliquer cette modification.")
				}

			},			
		
			Fermer: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			
		}
	});	

});

function ChangerHopital(){
	$( "#IdChangerHopital" ).dialog( "open" );
}
