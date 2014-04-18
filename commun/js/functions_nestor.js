/*
#########################################################################################
		ProjetMSI
		Module Nestor
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/

$(document).ready(function(){
	
	//====================================================================
	// recupare tous les input element de la page puis envoi vers save page
	//====================================================================
	$("#saveinfo").click(function(){
		var Pagedata="";
		$("form :input").each(function(){
        switch ($(this).attr('type'))
        {
          case "checkbox":
						if($(this).prop('checked'))                
	   					Pagedata += $(this).attr('name')+"="+ $(this).val()+"&";                 
            break;
          case "radio":
          	if($(this).filter(':checked').val())                
          		Pagedata += $(this).attr('name')+"="+ $(this).val()+"&";
          	break;
          default:  //text, textarea, select , hidden ..
          		if($(this).attr('name') != "undefined" ){
								Pagedata += $(this).attr('name')+"="+ $(this).val()+"&";                 
							}
          	break;
        }					
			});

			//----------------------------------------
			//mise a jour dans la base
			//----------------------------------------
			var err=LanceAjax("../commun/ajax/ajax_nestor_save_commentaires.php",Pagedata);
			if(err.length >0)
					alert(err);
			//----------------------------------------
			//refresh le tableau avec les dernière enregistrement
			//----------------------------------------
			var res=LanceAjax("../commun/ajax/ajax_nestor_get_commentaires.php",Pagedata);	
			$("#TableCommentaires  tbody").html(res);			

			//----------------------------------------
			// vider les champs 
			//----------------------------------------
			$("form :input").each(function(){
	        switch ($(this).attr('type'))
	        {
	          case "checkbox":
							$(this).prop('checked',false);
	            break;
	          case "radio":
	          	$(this).prop('checked',false);
	          	break;
	          case "hidden":
							//pas d'action
	          	break;

	          default:  //text, textarea, select , hidden ..
									$(this).val('');
	          	break;
	        }					
				});

			
		});
		//====================================================================
		//      REMOVE ROWS FROM COEMMENAIRES LIST
		//====================================================================
	  
	    $("#TableCommentaires").on("click", ".DEL_COM", function() {
	    	  var row_id=$(this).attr("TAG");	        
	        $(this).parent().parent().remove();
					var del_res=LanceAjax("../commun/ajax/ajax_nestor_del_commentaires.php","row_id="+row_id);	        
					if(del_res.length >0)
						alert(del_res);
	    });		
 
	//====================================================================
	//	Gestion HIDE SHOW div elements next to un img
	//====================================================================

	// hide onload
	$("#nestor_commentaires").hide();
	//$("#nestor_add_commentaires").hide();
	$("#nestor_cma").hide();
	
	$(".ShowHide").click(function () {
		$(this).parent().next('div').toggle('slow');
		$(this).attr("src",$(this).attr("src")=="../commun/images/down.png"?"../commun/images/up.png":"../commun/images/down.png");
		// scroll le page to bootom of the page s'un div ouvert
 		$("html, body").animate({ scrollTop: $(document).height() }, "slow");
  		return false;		
  });

}); //fin doc ready

function ValideResume(id_div,mode,nda,dt_sor,ctrl,id_table)
{
  var user= '';
  var commentaire='';
	if (mode.length > 0){	  
	  user		= document.getElementById('user').value;	
	}
	if(confirm("Vous êtes cetrain de valider ce résumé ?\n Attention, cette action est irreversible!!"))
	{
	  var retval=LanceAjax('../commun/ajax/ajax_nestor_valide_resume.php?MODE='+escape(mode)+'&nda='+escape(nda)+'&user='+escape(user)+'&dt_sor='+escape(dt_sor)+'&commentaire='+escape(commentaire)+'&ctrl='+escape(ctrl)+'&id_table='+escape(id_table))
	  document.getElementById(id_div).innerHTML = retval;
	}
}
