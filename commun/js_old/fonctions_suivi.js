/*
#########################################################################################
		ProjetMSI
		Module User
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/
function VideUh(){
		document.getElementById('uh_liste').value='';
}
function VideGruoupSelection(){
	//document.getElementById('select_grp').setAttribute('size', 3);

	var grp = document.getElementById('select_grp');
  if ( grp != null && grp.options.length > 0 )
        grp.options[0].selected = "selected";	
}
