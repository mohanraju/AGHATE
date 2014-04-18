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
// grisser les dates quand patient en cours d'ahopit selectionnèes
function GriseDates()
{
	if (document.getElementById('HospitEnCours').checked)
	{
		document.getElementById('date_deb').disabled=true;
		document.getElementById('date_deb').style.background="#D4D0C8";		
		document.getElementById('date_fin').disabled=true;
		document.getElementById('date_fin').style.background="#D4D0C8";				
	}	
	else
	{
		document.getElementById('date_deb').disabled=false;
		document.getElementById('date_deb').style.background="#FFFFFF";
		document.getElementById('date_fin').disabled=false;
		document.getElementById('date_fin').style.background="#FFFFFF";
	}	
		
}
