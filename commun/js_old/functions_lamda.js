/*
#########################################################################################
		ProjetMSI
		Module Lamda
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
		function d'appel js/ajax/		
*/
/*
===================================================================================
Update SUSIE(Saisie) to ATT/TR
===================================================================================
*/

function UpdateSusie(row_id,statut)
{
  retval = LanceAjax('../commun/ajax/ajax_lamda_update_susie.php?id='+escape(row_id))
	if(retval=="TR")
	 	$("#"+row_id+"_susie_TR").removeClass('btn btn-warning btn-mini').addClass("btn btn-success btn-mini");
  else if(retval=="ATT")
  	$("#"+row_id+"_susie_TR").removeClass('btn btn-success btn-mini').addClass("btn btn-warning btn-mini");
  else
  	alert(retval);
}


/*
===================================================================================
Update STATUS (valider)  ATT/VD
===================================================================================
*/
function UpdateStatut(row_id,statut)
{
  retval = LanceAjax('../commun/ajax/ajax_lamda_update_statut.php?id='+escape(row_id))
	if(retval=="VD")
	 	$("#"+row_id+"_statut_VD").removeClass('btn btn-warning btn-mini').addClass("btn btn-success btn-mini");
  else if(retval=="ATT")
  	$("#"+row_id+"_statut_VD").removeClass('btn btn-success btn-mini').addClass("btn btn-warning btn-mini");
  else
  	alert(retval);
}
