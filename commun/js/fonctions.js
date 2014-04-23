/*
#########################################################################################
		ProjetMSI
		commun function de la Projet
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/

//=================================================================
//	LANCER AJAX avec le nom du script et parametres
//	PageVariables = les varibales a passer donc "test=test&toto=toto&tati=tati"
//=================================================================
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
