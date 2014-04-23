//===================================================
// auto sugesstion javascript par moharnaju le 18/01/10
//===================================================	

	//-------------------------------------------
	// VARIABLES
	//-------------------------------------------	
	 var isWorking = false; // est-ce que le canal est occupé

	//-------------------------------------------
	// fontion appellé par text box avec l'id du text box
	//-------------------------------------------	 
	 function callsuggestionbox(champ_id,ajax_script){ // la requête
	 	if (!isWorking ) {
	 		var cur_champ=get_element(champ_id);
			ch = cur_champ.value;
			if (ch.length > 1){
				texte = Ajax_file(ajax_script+"?abr=" + escape(ch), true);	

				make_sugession(texte,champ_id)
				isWorking = true;			
			}
		if ((ch.length<1) && (document.getElementById('suggestionbox').style.visibility == 'visible'))
			document.getElementById('suggestionbox').style.visibility = 'hidden';
			isWorking = false;		
	 	}
	 }

	//-------------------------------------------
	// Get proposition and make the to affiche
	//-------------------------------------------
	function make_sugession(res,champ_id) { // traitement de la demande
				var elements = res.split("|");
				var result="";
			 	if (elements[0] != ''){
			 		for (var i=0;i<elements.length;i++){
						//c_element= elements[i].replace(/'/gi, '`')
						c_element= elements[i];
						c_element= c_element.replace('\\', '')
			 			result = result + "<a style=\"text-decoration:none;color:red;\" href=\"javascript:GetSlected('" + elements[i] + "','"+champ_id+"');\">" + c_element + "</a><br />";
			 		}
			 		var pos=get_pos(champ_id);
			 		var sug_box=get_element('suggestionbox');
			 		
			 		sug_box.style.left 		= pos.x + "px";
			 		sug_box.style.top 		= pos.y  + 20 + "px";
			 		sug_box.style.width = (document.getElementById(champ_id).offsetWidth) - 2 * 1;
			 		document.getElementById('suggestionbox').innerHTML = result;
		 		
			 		document.getElementById('suggestionbox').style.visibility = 'visible';
			 	}else{ 
			 		document.getElementById('suggestionbox').style.visibility = 'hidden';
				}
			 	isWorking = false;
	}
	//-------------------------------------------
	// remet le selcted value
	//-------------------------------------------
	function GetSlected(ch,champ_id){
		document.getElementById(champ_id).value = ch;
 		document.getElementById('suggestionbox').style.visibility = 'hidden';
 		isWorking = false;		
	}
	//-------------------------------------------
	// ajax lancing file
	//-------------------------------------------
	function Ajax_file(fichier)
	
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
	//-------------------------------------------
	/* get element from body*/
	//-------------------------------------------
	function get_element(e)	{
		var t=typeof(e);
		if (t == "undefined")
			return 0;
		else if (t == "string")
		{
			var re = document.getElementById( e );
			if (!re)
				return 0;
			else if (typeof(re.appendChild) != "undefined" )
				return re;
			else
				return 0;
		}
		else if (typeof(e.appendChild) != "undefined")
			return e;
		else
			return 0;
	}
	//-------------------------------------------
	/* get position of a element dans le body */
	//-------------------------------------------
	function get_pos(e){
		var e = get_element(e);
	
		var obj = e;
	
		var curleft = 0;
		if (obj.offsetParent)
		{
			while (obj.offsetParent)
			{
				curleft += obj.offsetLeft;
				obj = obj.offsetParent;
			}
		}
		else if (obj.x)
			curleft += obj.x;
		
		var obj = e;
		
		var curtop = 0;
		if (obj.offsetParent)
		{
			while (obj.offsetParent)
			{
				curtop += obj.offsetTop;
				obj = obj.offsetParent;
			}
		}
		else if (obj.y)
			curtop += obj.y;
	
		return {x:curleft, y:curtop};
	}




	//===================================================	
	// AJAX ENDS HERE	
	//===================================================			

