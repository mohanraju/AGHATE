$(document).ready(function() {

	//-------------------------------------------
	// ONBlur SUR Text BOX element
	//-------------------------------------------
	$('input[type="text"]').blur(function() {

		if($(this).attr('save')=='non')
			return;
		var data=$(this).val();
		var idF=$(this).attr('id');
		var nameObj= $(this).attr('name');
		var DataSource= $(this).attr('DataSource');
		var CompData= $(this).attr('CompData');
		var Fields= $(this).attr('Fields');
		var code= $(this).attr('code');
		var TypeAttribut= $(this).attr('TypeAttribut');
		
		var uhexec=$('#uhexec').val();
		//var nda=$('#nda').val();
		//if(!nda || !uhexec) nda=$('#VID').val();
		var nda=$('#NDA').val();
	

		var entry=$('#entry').val();	
		entry=entry.substr(6,4)+'-'+entry.substr(3,2)+'-'+entry.substr(0,2) ;
		var end=$('#end').val();
		end=end.substr(6,4)+'-'+end.substr(3,2)+'-'+end.substr(0,2) ;
	
		
		if($(this).attr("subtype")=='date'){data=data.substr(6,4)+'-'+data.substr(3,2)+'-'+data.substr(0,2) ;}
		var pagevariables="FormUpdate_VID="+$('#VID').val()+"&FormUpdate_ID="+idF+"&FormUpdate_REF="+$('#ref').val()+"&FormUpdate_Var="+nameObj+"&FormUpdate_Libelle="+$(this).parent().children()[0].innerHTML+"&FormUpdate_Val="+data+"&Source="+DataSource+"&FormUpdate_Nip="+$('#nip').val()+"&FormUpdate_Nda="+nda+"&FormUpdate_Nohjo="+$('#nohjo').val()+"&FormUpdate_Entry="+entry+"&FormUpdate_End="+end+"&FormUpdate_Uhdem="+$('#uhdem').val()+"&FormUpdate_Uhexec="+$('#uhexec').val()+"&FormUpdate_Libuhdem=&FormUpdate_TypeAttribut="+TypeAttribut+"&FormUpdate_Username="+$('#Username').val()+"&FormUpdate_SaveDateTime="+$('#SaveDateTime').val()+"&FormUpdate_Diag="+code+"&FormUpdate_Libdiag="+data;
	 	//alert(pagevariables);
	 	if(Fields){
	 		pagevariables+="&FormUpdate_DataChamps="+data+"&FormUpdate_Champs=datrea";
	 		res=LanceAjax("../commun/ajax/Ajax_updateFormsFields.php",pagevariables);
		}else{
			res=LanceAjax("../commun/ajax/Ajax_updateForms.php",pagevariables);
			document.getElementById(idF).setAttribute("name",res);
			if(CompData=='o'){
				res=LanceAjax("../aghate/commun/ajax/ajax_update_description.php",pagevariables+"&table_loc="+$('#table_loc').val());
			}
			var InitForm=$('#InitForm').val();	
			if(InitForm=='No'){
				var pagevariables2="FormUpdate_VID="+$('#VID').val()+"&FormUpdate_ID="+idF+"&FormUpdate_REF="+$('#ref').val()+"&FormUpdate_Var=Form&FormUpdate_Libelle=Nom du formulaire&FormUpdate_Val="+$('#InitForm').attr('name')+"&Source=forms&FormUpdate_Nip="+$('#nip').val()+"&FormUpdate_Nda="+nda+"&FormUpdate_Nohjo="+$('#nohjo').val()+"&FormUpdate_Entry="+entry+"&FormUpdate_End="+end+"&FormUpdate_Uhdem="+$('#uhdem').val()+"&FormUpdate_Uhexec="+$('#uhexec').val()+"&FormUpdate_Libuhdem=&FormUpdate_TypeAttribut="+TypeAttribut+"&FormUpdate_Username="+$('#Username').val()+"&FormUpdate_SaveDateTime="+$('#SaveDateTime').val()+"&FormUpdate_Diag="+code+"&FormUpdate_Libdiag="+data;
				res2=LanceAjax("../commun/ajax/Ajax_updateForms.php",pagevariables2);
				$("#InitForm").val("Yes");
			}
		}
	 	
	 	
  	//AGHATE
  	if (res.length >1){				
				return;	
			}
	});	
});
