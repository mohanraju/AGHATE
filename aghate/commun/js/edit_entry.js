//##############################################################
//LANCER AJAX avec le nom du script et parametres
//Scripturl = nom de url : example.php
//PageVariables = les varibales a passer donc "test=test&toto=toto&tati=tati"
//##############################################################
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
								alert("La page demandé n'existe pas ("+Scripturl+")");
							}
						}	  				
	 					}).responseText;
	 return html;
}

//##############################################################
//InsertChampsAdd
////##############################################################
function InsertChampsAdd(div_add,areas,entry_id,room_id){
	var e = document.getElementById("areas");
	var service_id = e.options[e.selectedIndex].value;
	var name = e.getAttribute('name');
	res = LanceAjax("./ajax_edit_entry_champs_add.php",name+"="+service_id+'&id='+entry_id+'&room='+room_id) ;
	if(res.length > 1){
		document.getElementById(div_add).innerHTML=res;
		}
	else{
		document.getElementById(div_add).innerHTML="";
	}
}


//##############################################################
//InsertChampsTypes
////##############################################################
function InsertChampsTypes(div_type,areas,room_id,type){
	var e = document.getElementById("areas");
	var service_id = e.options[e.selectedIndex].value;
	var name = e.getAttribute('name');
	if (typeof(type)!='undefined')
		var param = name+"="+service_id+'&room='+room_id+"&type="+type;
	else
		var param = name+"="+service_id+'&room='+room_id;
	res = LanceAjax("./ajax_edit_entry_types.php",param);
	if(res.length > 1){
		document.getElementById(div_type).innerHTML=res;
		}
	else{
		document.getElementById(div_type).innerHTML="";
	}
}

//##############################################################
//InsertChampsUh
////##############################################################
function InsertChampsUh(div_uh,areas,uh){
	var e = document.getElementById("areas");
	var service_id = e.options[e.selectedIndex].value;
	var name = e.getAttribute('name');
	if (typeof(uh)!='undefined')
		var param = name+"="+service_id+'&uh='+uh;
	else
		var param = name+"="+service_id;
	res = LanceAjax("./commun/ajax/ajax_edit_entry_change_uh.php",param);
	if(res.length > 1){
		document.getElementById(div_uh).innerHTML=res;
		}
	else{
		document.getElementById(div_uh).innerHTML="";
	}
}

//##############################################################
//InsertChampsRoom
////##############################################################
function InsertChampsRoom(div_uh,areas,room_id,entry_id){
	var e = document.getElementById("areas");
	var service_id = e.options[e.selectedIndex].value;
	var name = e.getAttribute('name');
	var param = name+"="+service_id;
	if (typeof(room_id)!='undefined') 
		param +='&room_id='+room_id;
	if (typeof(entry_id)!='undefined')
		param +='&id='+entry_id; 
	res = LanceAjax("./commun/ajax/ajax_edit_entry_change_room.php",param);
	if(res.length > 1){
		document.getElementById(div_uh).innerHTML=res;
		}
	else{
		document.getElementById(div_uh).innerHTML="";
	}
}
