//###################################################################################################
//JQUERY FONCTION 
//###################################################################################################

$(document).ready(function() {	
	  
	LoadResaInfo();
});
 
//##############################################################
// function LoadResaInfo()
// Charger les resa infos 
//##############################################################
 
function LoadResaInfo()
{
		//get page variables
		var id		=$("#id").val();
		var mode	=$("#mode").val();
		var table_loc	=$("#table_loc").val();		
	 	var description='';
		//Get info reservation from aghate (ajax response = json)
		//alert("../aghate/commun/ajax/ajax_aghate_get_resa_info_from_id.php?entry_id="+id+"&mode="+mode+"&table_loc="+table_loc);
		RefData=LanceAjax("../aghate/commun/ajax/ajax_aghate_get_resa_info_from_id.php","entry_id="+id+"&mode="+mode+"&table_loc="+table_loc);
 

		if(RefData.length > 1)
		{
	   		obj = JSON && JSON.parse(RefData) || $.parseJSON(RefData);
	   		
			if(id !="")
			{	$("#NDA").val(obj.nda);
				$("#ref").val("AGHATE");
				
				$("#RefData").val(RefData);
				$("#nip").val(obj.noip);
				$("#nda").val(obj.nda);
				$("#nom").val(obj.nom);
				$("#prenom").val(obj.prenom);
				$("#ddn").val(obj.naissance);
				$("#uhexec").val(obj.uh);
				$("#uhdem").val(obj.uh);
				if(!obj.nda) 
				{
					$("#uhdem").val("RESA");
					$("#uhexec").val("RESA");
				}
				$("#service_name").val(obj.service_name);
				$("#room_name").val(obj.room_name);
				$("#service_id").val(obj.service_id);
				$("#service").val(obj.service_name);
				$("#protocole").val(obj.protocole);
				$("#entry").val(obj.entry);
				$("#end").val(obj.end);
				
				if(typeof obj.description.DESC___COMPL !=  'undefined')description=obj.description.DESC___COMPL.Valeur;

				if(typeof obj.medecin == 'undefined' || obj.medecin == '') 
				{
					obj.prenom_medecin="";
					obj.nom_medecin="A renseigner";
					obj.specialite='';
				}
				if(obj.specialite == null) obj.specialite='';


				var html_resa  = "<a href='../aghate/reservation.php?id="+id+"&table_loc="+table_loc+"&mode=MODIFY'>Modifier cette r&eacute;servation</a> <br>";
				//alert(html_resa);
				html_resa +=  "<table width='700'>"  ;                                                                                                                            
				//	html_resa += "	<tr><td width=200><a href='./reservation.php?id="+id+"'>Modifier cette réservation</a> <br></td>"
				//html_resa += "	<tr><td width=200><NIP "+obj.noip+" NDA "+obj.nda+"<br></td>"
				html_resa += "  <td id='Patient'><b>"+obj.nom+" "+obj.prenom+"   N&eacute;e le "+obj.naissance+"</b><br>";
				html_resa += "	<b>NIP</b> "+obj.noip+" <b>NDA</b> "+obj.nda+"<br><br>";
				html_resa += "	<b>Motif</b> "+obj.protocole+"<br>";
				html_resa += "	<b>Medecin responsable</b> "+obj.nom_medecin+" "+obj.prenom_medecin+"<br><br>";
				html_resa += "  <b>Sp&eacute;cialit&eacute;</b> : "+obj.specialite+"<br><br>";
				html_resa += obj.service_name+" du "+obj.entry+" au "+obj.end+"<br>";
				html_resa += "</td></tr></table>";
				html_resa += "<br><b>Commentaire : </b>"+description;
				
				$("#view_resa").html(html_resa);
				//Force le NDA Temps à VID si pas de NDA
			
			//	$("#date_deb").val(retval[6]);
			//	$("#duree").val(retval[7]);
			//	switch (retval[8]) {
			//	 case 'jours':
			//		$('#dur_units').val('J');
			//	 break;
			//	 case 'heures':
			//		$('#dur_units').val('H');
			//	 break;
			//	 case 'minutes':
			//		$('#dur_units').val('M');
			//	 break;
			//	}
			}
	//		if(mode =="view_resa")
	//		{
	//			$('#view_resa').css({ 'display': ''});
	//			$("#view_resa").html(res[0]);
	//		}
		}else
			$("#view_resa").html("<span>Aucune reservation trouvé</span>");	
}


 
