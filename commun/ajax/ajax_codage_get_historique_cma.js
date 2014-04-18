//==============================
// function d'appel pour AJAX par Mohanraju
//===============================
//function file(fichier)
function LancerAjax(fichier)
{
    if(window.XMLHttpRequest) // FIREFOX
        xhr_object = new XMLHttpRequest();
    else if(window.ActiveXObject) // IE
        xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
    else
        return(false);
    xhr_object.open("GET", fichier, false);
    xhr_object.send(null);
    if(xhr_object.readyState == 4) return(xhr_object.responseText);
    else return(false);
} 

function ajax_codage_get_historique_cma(id_nip,idResult )
{
	obj_nip=document.getElementById(id_nip);
	if(!obj_nip){
		alert("Id_nip introuvable dans le formulaire!");
		return false;
	}else{	
		nip=obj_nip.value;
		if (nip.length < 3 )
		{
			alert("Invalid nip ou nip vide ["+nip+"]");
			obj_nip.focus();
			return false
		}
	}
 
  retval = LancerAjax('../commun/ajax/ajax_codage_get_historique_cma.php?nip='+escape(nip))
 	document.getElementById(idResult ).innerHTML = retval;
}
