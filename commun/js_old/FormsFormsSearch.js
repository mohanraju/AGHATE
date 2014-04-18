$(document).ready(function() {
	//-------------------------------------------
	// ONBlur SUR Text BOX element
	//-------------------------------------------
	$('input[type="text"]').blur(function() {
		var data=$(this).val();
		var nameObj= $(this).attr('name');
		if($(this).attr("subtype")=='date'){data=data.substr(6,4)+'-'+data.substr(3,2)+'-'+data.substr(0,2) ;}
		var pagevariables="VAR="+data;	
   		res=LanceAjax("../commun/ajax/Ajax_searchForms.php",pagevariables)
		if (res.length >1){				
				alert(res);
				return;	
			}

	});	
});
