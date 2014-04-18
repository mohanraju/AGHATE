/*
#########################################################################################
		ProjetMSI
		Module recherche
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 18/08/2013
		function d'appel js/ajax/		
*/
//=================================================================
// document ready
//=================================================================
 
$(document).ready(function() {	

	//----------------------------------------
	// on click sur recherche affiche les patients 
	//----------------------------------------
	$("#Rechercher").click(function(){
			maj_patients();		
	});
 
	//----------------------------------------
	// on click sur le TR affiche les sejours
	//----------------------------------------
  //$(".Patients tbody >tr").click(function() {
   $('body').on('click', '.Patients tbody tr', function () {  
  	//get first element.text of the tr clicked	donc le nip
		var nip = $(this).closest('tr').find('td:eq(0)').text();
		$("#NIP").val(nip);
		//alert(nip);
		// apple function get sejours
		recherche_sejours(nip);
  });	
	// change couleur on click
   $(".candidateNameTD").click(function() {
      $(this).closest("tr").siblings().removeClass("diffColor");
      $(this).parents("tr").toggleClass("diffColor", this.clicked);
  });
 

})// fin doc ready

 

// function rechere sejours par nip
//==========================================
function recherche_sejours(nip){
	//alert(nip);
  retval = LanceAjax('../commun/ajax/ajax_recherchepat_getsejours.php?NIP='+escape(nip))
	//alert(retval);
	$('.Sejours tbody').html(retval);
	
}
 
 
