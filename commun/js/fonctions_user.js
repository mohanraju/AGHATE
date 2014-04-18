/*
#########################################################################################
		ProjetMSI
		Module User
		Auther Mohanraju Sp @SLS-APAP
########################################################################################
		Date creation 
		Date dernière modif : 30/05/2013
*/
//-------------------------------------------
//fonctions get user info
//-------------------------------------------
function GetUserInfo(login){
	document.getElementById('adduser').style.display='none';
	res=LanceAjax(".../commun/ajax/GetUserInfo.php","login="+login) ;						
	document.getElementById('patient_info').innerHTML=res;
}

//-------------------------------------------
//fonctions edit user info
//-------------------------------------------
function EditUserInfo(login){
	document.getElementById('adduser').style.display='block';
	document.getElementById('login').value=login;

	res=LanceAjax("../user/user_edit.php","login="+login);						
	document.getElementById('patient_info').innerHTML=res;
			
  //mywindow=open('user_edit.php?login='+login,'myname','resizable=yes,top=100,left=400,width=550,height=500,menubar=no,status=yes,scrollbars=yes,,menubar=no,toolbar=no');
  //mywindow.location.href = 'user_edit.php?login='+login;
  //if (mywindow.opener == null) mywindow.opener = self;

}

//-------------------------------------------
//fonctions change SITE(hopital) dans le session
//-------------------------------------------
function ChangeSite(){
  mywindow=open('../commun/user/changesite.php','myname','resizable=yes,top=100,left=400,width=500,height=300,menubar=no,status=yes,scrollbars=yes,,menubar=no,toolbar=no');
  mywindow.location.href = './user/changesite.php';
  if (mywindow.opener == null) mywindow.opener = self;

}	
//-------------------------------------------
//fonctions get user info
//-------------------------------------------
function AddUserDroits(login,urm){
  mywindow=open('../commun/ajax/AjaxAddUserDroits.php?login='+login+'&urm='+urm,'myname','resizable=yes,top=100,left=400,width=500,height=300,menubar=no,status=yes,scrollbars=yes,,menubar=no,toolbar=no');
  mywindow.location.href = 'user_edit.php?login='+login+'&urm='+urm;
  if (mywindow.opener == null) mywindow.opener = self;

}	

//-------------------------------------------
//fonctions close popup
//-------------------------------------------
function closeme(RefrershParent){
	if(RefrershParent)
	{
	window.opener.location.reload()	;
	}
	window.close();
}	

//###############################################
// remove selected item d'in select box	
// utilisée dans gestion droits users
//###############################################
function RemoveItem(_Source,_Target)
{
  var src_opt = document.getElementById(_Source);
  var trg_opt = document.getElementById(_Target);  
	id_src="#" + _Source;
	id_trg="#" + _Target;
	
	// Create an Option object
	var opt  
  var i;
  var last_selected=1;
  nbr_items=src_opt.length ;
  
  for (i=nbr_items-1 ;i >=0 ;  i--) {
    if (src_opt.options[i].selected) 
    {
    	$(id_trg).append('<option value="'+$(id_src).val()+'">'+$(id_src).val()+'</option>');
      src_opt.remove(i);
      last_selected=i;
    }
  }
  // force to select un item
	if(src_opt.length > last_selected)
  	src_opt[last_selected].selected = true;
  else
  	src_opt[0].selected = true;
}		
//###############################################
// select all before saving
// utilisée dans gestion droits users
//###############################################
function SelectAll(_Target)
{
  var trg_opt = document.getElementById(_Target);  
	 var res="";

  var i;
  nbr_items=trg_opt.length ;
  
  for (i=nbr_items-1 ;i >=0 ;  i--) 
  	trg_opt[i].selected = true;

  
	var form = document.UserInfo;	  	

	for (i=0 ; i<= form.length-1 ; i++)
	{
	  res = res +  form[i].name + " = " + form[i].value+'\n';
	  
	}

}
			
/*		
	===================================================================================
	FORM USER SAVE MODIFICATION DES PREVILEGES
	===================================================================================		
*/
$(document).ready(function() {
	$("#adduser").click(function() {

		// force de selectionner tous les element de la target div pour envoyer dans l'ajax pour l'enregistrement
		SelectAll('actuel_droits')				
		
		// recupare all form datas
		data =$(this.form).serialize()+'&login='+$("#login").val(); ;
		if(data.length < 1)
		{
			alert('Aucun modification dans le page! .')
		}
		else
		{
		var request =	$.ajax({
				type: "POST",
				url: "../commun/ajax/Ajax_user_SaveUserInfo.php",
				data: data
			});
			request.done(function(msg) {
				alert('Status : '+ msg)
			});
			
			request.fail(function(jqXHR, textStatus) {
			  alert( "Status :  " + textStatus );
			});

			
		}
		return false;
	});
});

		
