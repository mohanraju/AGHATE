// INITIALIZE AJAX ENGINE //////////////////////////////////////

function ajaxObject(){
	if(window.ActiveXObject){ 
		// Support Internet Explorer
		var request = new ActiveXObject("Microsoft.XMLHTTP");
		return request;
	} else 	if(window.XMLHttpRequest){ 
		// Support Firefox, Safari, Opera
		var request = new XMLHttpRequest();
		//request.setRequestHeader("Content-Type", "text/xml;charset=utf-8")
		request.overrideMimeType('text/html; charset=utf-8');
		return request;
	} else {
		// Aucun support
		alert('Désolé, mais votre navigateur ne supporte pas la technologie AJAX. Nous vous conseillons d\'utiliser un de ces navigateurs compatibles : Mozilla Firefox, Microsoft Internet Explorer, Opera.');
		return false;
	}
}

/////////////////// JABBAX.LoadPage  /////////////////////:

function writeHTML(htmlStream, div){
	document.getElementById(div).innerHTML = htmlStream;
}


function loadPage(url,div){
	request = ajaxObject()
	request.open("POST", url, false);
	request.send(null);
	if(request.readyState == 4) {
		writeHTML(request.responseText, div);
	} else { 
		return false;
	}
}


function getPage(url,destination){
	page = url
	div = destination
	if(page != '')
	{
		// pour ne pas envoyer systématiquement une requête dès que l'utilisateur frappe une touche (délai d'2 seconde)
		setTimeout("loadPage(page,div);",0); 
		//writeHTML('&nbsp;&nbsp;Veuillez patienter...',div);
	}
}

function GetCodage(retval){
		//if(!getSejSelc())
			//return false;
		
		retval=retval.split("|");
		TypeCodage = document.getElementById('TypeCodage').value;
		
		//GESTION DES CAS 1 LIBELLÉ ET PLUSIEURS CODES :
		//syntaxe :
      //    1er code DP, 2ème code DR, 3ème code DAS,...
      //    possibilité de laisser des champs vides
		switch (TypeCodage) {
      
      //Si focus DP :
      //    remplissage selon syntaxe
      //    remplacer les DR existant
      //    ne pas remplacer les DAS (ajout simple)
		 case 'ID_DP':
		 	$("#ID_DP").val(retval[0]+' [('+retval[1]+')]');
		 	if (retval[2] && retval[2].length > 0)
		 		$("#ID_DR").val(retval[0]+' [('+retval[2]+')]');
		 	
		 	for(var i=3;i<retval.length;++i) {
		 		if (retval[i] && retval[i].length > 0){
		 			var tbl_das = $("#DAS_LIST");
	      	var das=(retval[0]+' [('+retval[i]+')]');
	      	Create_Ligne(tbl_das,'DAS',das);
	      }
		 	}
		 break;
		 //Si focus DR :
      //    Premier code DR
      //    Autres codes DAS
		 case 'ID_DR':
		 	$("#ID_DR").val(retval[0]+' [('+retval[1]+')]');
		 	for(var i=2;i<retval.length;++i) {
		 		if (retval[i] && retval[i].length > 0){
		 			var tbl_das = $("#DAS_LIST");
	      	var das=(retval[0]+' [('+retval[i]+')]');
		 			Create_Ligne(tbl_das,'DAS',das);
		 		}
		 	}
		 break;
		  //Si focus DAS :
      //    Tous les codes DAS
		 case 'ID_DAS':
		 	for(var i=1;i<retval.length;++i) {
		 		if (retval[i] && retval[i].length > 0){
		 			var tbl_das = $("#DAS_LIST");
	      	var das=(retval[0]+' [('+retval[i]+')]');
		 			Create_Ligne(tbl_das,'DAS',das);
		 		}
		 	}
		 break;
		 //Si focus ACTES :
      //    Tous les codes ACTES
		 case 'ID_ACTES':
		 	for(var i=1;i<retval.length;++i) {
		 		if (retval[i] && retval[i].length > 0){
		 			var tbl_actes = $("#ACTES_LIST");
	      	var actes=(retval[0]+' [('+retval[i]+')]');
		 			Create_Ligne(tbl_actes,'ACTES',actes);
		 		}
		 	}
		 break;
		 default: 
		 	for(var i=1;i<retval.length;++i) {
		 		if (retval[i] && retval[i].length > 0){
		 			var tbl_das = $("#DAS_LIST");
	      	var das=(retval[0]+' [('+retval[i]+')]');
		 			Create_Ligne(tbl_das,'DAS',das);
		 		}
		 	}
		 break;
		}
	}
