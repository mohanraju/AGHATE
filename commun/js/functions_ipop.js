/*
#########################################################################################
		ProjetMSI
		Module IPOP
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
		function d'appel js/ajax/		
*/
//=================================================================
// doxument ready
//=================================================================
 
$(document).ready(function() {	

	//----------------------------------------
	// on click sur Annulation sur la tableau
	//----------------------------------------
  $(".table tr").on("click", "#Etat_bloc", function(e) {

  	if($(this).attr("ipop_id")=="ACTES_OK")
  	{
  		alert("Vous ne pouvez pas annuler ce séjour, les Actes sont déjà codés");
  		return false;	
  	}
  	var res=LanceAjax("../commun/ajax/ajax_ipop_update_etat.php","ipop_id="+$(this).attr("ipop_id"))

  	if(res.indexOf("[KO]") != -1 )
  	{
  		$(this).attr("src","../commun/images/ko.jpg");
  		$(this).parent().prev().text('Annulation'); 
			$(this).parent().prev().css("background-color","white");    		   		
  	}else if(res.indexOf("[OK]") != -1 )
  	{
  		$(this).attr("src","../commun/images/ok.jpg"); 
  		$(this).parent().prev().text('');
  		$(this).parent().prev().css("background-color","#FF9933");    		   		
  	}else	
  		alert("Erreur Réponse du servuer: " +res +" "+res.length);	
		
  });		

	//----------------------------------------
	// on click sur le nip affiche les actes
	//----------------------------------------
  $(".table").on("click", "#list_actes", function() {
  	var ipop_id=$(this).attr("ipop_id");
  	PopupAfficheActes(ipop_id);
  });	
 
	
})// fin document ready




//----------------------------------------
//	Popup Affiche actes
//----------------------------------------
function PopupAfficheActes(id) {
 
    mywindow=open('PopupActes.php?id='+id,'myname','resizable=yes,width=800,height=470,status=yes,scrollbars=yes');
    mywindow.location.href = 'PopupActes.php?id='+id;
    if (mywindow.opener == null) mywindow.opener = self;
}

//----------------------------------------
//	mises a jour etat 
//----------------------------------------
function maj_etat(ipop_id) {
 
    mywindow=open('PopupActes.php?id='+id,'myname','resizable=yes,width=800,height=470,status=yes,scrollbars=yes');
    mywindow.location.href = 'PopupActes.php?id='+id+'&date_interv='+date_inv;
    if (mywindow.opener == null) mywindow.opener = self;
}

//----------------------------------------
//	Popup Affiche actes
//----------------------------------------
function ChangeCheckIpop() {
	// si DonneeTempsReal est coché on force 1  si non 0
	if($('#DonneeTempsReal').prop('checked'))  	
		$('#CheckIpop').val('1')
	else	
		$('#CheckIpop').val('0')
		
 
}
