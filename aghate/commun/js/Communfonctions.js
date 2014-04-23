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

/*                                                                                                      
===================================================================================                 
function CocherElements(CurrentElementNo,GroupId)                                                   
===================================================================================                 
*/                                                                                                      
function CocherElements(CurrentElementNo,GroupId)                                                       
{                                                                                                       
    //recupère le bgcolor du current element                                                            
    var ColorActuel=$("#LBL_"+GroupId+CurrentElementNo).attr('class'); 
   // alert(ColorActuel);                                 
    //remettre tous comme non selectionnées                                                             
    $("a[id^='LBL_"+GroupId+"']").each(function() {                                                     
            $(this).removeClass('OptionSelected').addClass('OptionNonSelected');                        
    });                                                                                                 
                                                                                                        
    //                                                                                                  
    if(ColorActuel=="OptionNonSelected")                                                                
     {                                                                                                  
        $("#LBL_"+GroupId+CurrentElementNo).removeClass('OptionNonSelected').addClass('OptionSelected');
        $("#"+GroupId).val($("#LBL_"+GroupId+CurrentElementNo).attr('cval'));                           
     }else                                                                                              
     {    
		 //$("#LBL_"+GroupId+CurrentElementNo).removeClass('OptionSelected').addClass('OptionNonSelected');
         $("#"+GroupId).val('99');                                                                      
     }                                                                                                  
}                                

/*###########################################################################
#  Commun date picker jqueryUI fonction 
#  function avec le ID du HTML element date_deb et date_fin
#  ex <input type =text id="date_deb">
############################################################################
*/
jQuery(function($){
   $.datepicker.regional['fr'] = {
      closeText: 'Fermer',
      prevText: '<Préc',
      nextText: 'Suiv>',
      currentText: 'Courant',
      monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
      'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
      monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
      'Jul','Aoû','Sep','Oct','Nov','Déc'],
      dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
      dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
      dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
      weekHeader: 'Sm',
      //dateFormat: 'dd/mm/yy',
                dateFormat: 'dd/mm/yy',
      firstDay: 1,
      isRTL: false,
      showMonthAfterYear: false,
      yearSuffix: ''};
   $.datepicker.setDefaults($.datepicker.regional['fr']);
});
		
$(function() {
if(($('#date_deb').length))	
	$( "#date_deb" ).datepicker();
	
if(($('#date_fin').length))		
	$( "#date_fin" ).datepicker();
});
                                                                
