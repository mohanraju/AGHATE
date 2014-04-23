//function $(elem) {	return (document.getElementById(elem));}

//==============================================================
// Function popup_gilda
//==============================================================
function popup_gilda(number) {
	NIP=document.getElementById('NIP').value;
	if (NIP.length == 10){
		champ_du_owner="CODAGE";
    mywindow=open('sejour_patient.php?NIP='+NIP,'mypat','resizable=yes,width=600,height=350,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'sejour_patient.php?NIP='+NIP;
    if (mywindow.opener == null) mywindow.opener = self;
     if(mywindow.window.focus){mywindow.window.focus();}        
	}
	else
 		return false; 
}

function popup_gilda2(number) {
	NDA=document.getElementById('NDA').value;
	if (NDA.length == 9){
		champ_du_owner="CODAGE";
    mywindow=open('sejour_patient.php?NDA='+NDA,'mypat','resizable=yes,width=600,height=350,left=500,top=200,status=yes,scrollbars=yes');
    mywindow.location.href = 'sejour_patient.php?NDA='+NDA;
    if (mywindow.opener == null) mywindow.opener = self;
     if(mywindow.window.focus){mywindow.window.focus();}        
	}
	else
		return false; 
}

function testerRadio(radio) {
	if (!radio)
	return 0;
  for (var i=0; i<radio.length;i++) {
     if (radio[i].checked) {
        return radio[i].value;
     }
  }
}

//##############################################################
// function LoadPatInfo()
// Charger le patient info et les sejours 
// avec les infomations fournis par l'utilisater
// parametres attendus NIP,NDA,UH,NOHJO 
//##############################################################
 
function LoadPatInfo()
{
		//recupare page variables
		var NIP		=$("#NIP").val();
		var NDA		=$("#NDA").val();
		// lancer ajax pour rechercher les patients info
  	XmlPatient=LanceAjax("../commun/ajax/ajax_codage_get_patinfo.php","NIP="+NIP+"&NDA="+NDA) ;	
		// parse XML
		ParseXmlPatient= $.parseXML(XmlPatient) ;

  	//affiche l'infromations récuparér de ajax
		if($(ParseXmlPatient).find('nom').text().length > 1)
		{
			patient=$(ParseXmlPatient).find('nom').text()+ ' ' +$(ParseXmlPatient).find('prenom').text()+ ' né(e) le ' +$(ParseXmlPatient).find('ddn').text()+ ' (' +$(ParseXmlPatient).find('sexe').text()+ ') ';
			$('#pat_info').css({ 'display': ''});
			$("#pat_info").html(patient);
			//mette a jour les var hidden
  		$('#NIP').val($(ParseXmlPatient).find('nip').text());
  		$('#NOM').val($(ParseXmlPatient).find('nom').text());
  		$('#PRENOM').val($(ParseXmlPatient).find('prenom').text());
  		$('#DDN').val($(ParseXmlPatient).find('ddn').text());
  		$('#SEXE').val($(ParseXmlPatient).find('sexe').text());			
			
		}else
			$("#pat_info").html("<span>Aucun patient trouvé</span>");	
} 

//-------------------------------------------
//fonctions edit libellé code
//-------------------------------------------
function EditCodeInfo(id_codage_msi,nda,uhdem){
	//alert(id_codage_msi+" "+nda+" "+uhdem);
	document.getElementById('addcode').style.display='block';
	document.getElementById('NDA').value=nda;
	document.getElementById('UH').value=uhdem;
	document.getElementById('id_codage_msi').value=id_codage_msi;

	LoadPatInfo();

	res=LanceAjax("./code_edit.php","id_codage_msi="+id_codage_msi);
	document.getElementById('codage_info').innerHTML=res;

}

function getTypeCodage(obj) {
	document.getElementById('TypeCodage').value=obj.name;
	//alert(obj.name);
}

function getSejSelc() {
	SejSelc=document.getElementById('DTEENT').value;
	if (SejSelc.length < 1)
		alert('Veuillez sélectionner un séjour dans le tableau ci-dessus avant de commencer le codage svp.');
	//alert(obj.name);
}
  
function ieExecWB( intOLEcmd, intOLEparam ){
		// Pour afficher l'aperçu avant impression
		//
		// Create OLE Object
		var WebBrowser = '<OBJECT ID="WebBrowser1" WIDTH=0 HEIGHT=0 CLASSID="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></OBJECT>';
		// Place Object on page
		document.body.insertAdjacentHTML('beforeEnd', WebBrowser);
		// if intOLEparam is not defined, set it
		if ( ( ! intOLEparam ) || ( intOLEparam < -1 ) || (intOLEparam > 1 ) )
		intOLEparam = 1;
		// Execute Object
		WebBrowser1.ExecWB( intOLEcmd, intOLEparam );
		//alert("fini");
		// Destroy Object
		WebBrowser1.outerHTML = ""; 
	}
function changeTitle(str){
	document.getElementById('title_bar').innerHTML = '<h1>' + str + '</h1>';
}
function confirmEvent(str){
	if (confirm(str))
		return (true);
	return (false);
}
function AffsimpleDIV(ID){
	//div = "DIV_" + ID;
	if (ID == 1)
	{
		document.getElementById('DIV_1').style.display = "";
		document.getElementById('DIV_2').style.display = "none";
	}
	else
	{
		document.getElementById('DIV_1').style.display = "none";
		document.getElementById('DIV_2').style.display = "";
	}	
}
function change_title(str, type){
	//alert(window.parent.titre.title_bar.innerHTML);
	if (window.parent.titre.title_bar != null)
	{
		if (type == 0)
			window.parent.titre.title_bar.innerHTML = "<div class='titre'>"+str+"</div>";
		else
			window.parent.titre.title_bar.innerHTML = "<div class='titre_green'>"+str+"</div>";
	}
}
function is_numeric(num){
	var exp = new RegExp("^[0-9-.]*$","g");
	return (exp.test(num));
}
function MontreMenu(objet){
	if (document.getElementById('menu'+objet)) 
	{
		document.getElementById('menu'+objet).style.display = 'block';
		document.getElementById('menu'+objet).style.zIndex = 100;
    }
}
function CacheMenu(objet){
	if (document.getElementById('menu'+objet))
		document.getElementById('menu'+objet).style.display = 'none';
}
function MontreCacheMenu(nummenu){
	CacheMenu(1);
	CacheMenu(2);
	CacheMenu(3);
	MontreMenu(nummenu);
	//if (nummenu==2){window.location.href = './administration.php';}
	//if (nummenu==3){window.location.href = './documentation.php';}
	if (nummenu == 4)
		MontreMenu(1);
}
function Deconnexion(){
	result = confirm('Etes-vous sûr de vouloir vous déconnecter ?');
	if (result == true)
		window.parent.location.href = './log_pass.php';
}
function UpCase(val, event){
	var codeDecimal = codeTouche(event);
	if ((codeDecimal < 33 || codeDecimal > 40) && codeDecimal != 8 && codeDecimal != 46)
		val.value = val.value.toUpperCase();
}
function codeTouche(evenement){
  for (prop in evenement)
  {
		if (prop == 'which')
			return (evenement.which);
  }
  return (evenement.keyCode);
}
function CheckInput(txt, msg, all_error){
	if (txt == "")
		all_error += '"' + msg + '" non rempli\n';
	return (all_error);
}
function CheckCheckbox(variable, msg, all_error, binaire){
	var nb = document.getElementById(variable + '_nb').value;
	if (nb > 1)
	{
		for (i = 1; i <= nb; i++)
		{
			if (document.getElementById(variable+binaire[i]).checked == true)
			{
				i = -1;
				break;
			}
		}
		if (i != -1)
			all_error += '"' + msg + '" non rempli\n';
	}
	return (all_error);
}
function CheckRadio(tab, variable, msg, all_error){
	var nb = document.getElementsByName(variable + '_nb')[0].value;
	for (i = 0, ok = 0; i < nb; i++)
	{
		if (tab[i].checked == true)
		{
			ok = -1;
			break;
		}
	}
	if (ok != -1)
		all_error += '"' + msg + '" non rempli\n';
	if (variable == 'orien_struct' && document.getElementById('etablissement_os').value == '' && (i == 1 || i == 3 || i == 4 || i == 5))
		all_error += '"Etablissement Orientation structure sanitaire page 2" non rempli\n';
	return (all_error);
}
function CheckForm(form){
	var binaire = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19");
	all_error = "";

	all_error = CheckInput(form.PATIENT_NIP.value, "NIP", all_error);
	all_error = CheckInput(form.PATIENT_NOMPAT.value, "NOM", all_error);
	all_error = CheckInput(form.PATIENT_NJFPAT.value, "NOM JF", all_error);
	all_error = CheckInput(form.PATIENT_PRENOMPAT.value, "PRENOM", all_error);
	all_error = CheckInput(form.PATIENT_DDNPAT.value, "DATE DE NAISSANCE", all_error);
	all_error = CheckInput(form.PATIENT_AGEPAT.value, "AGE", all_error);
	all_error = CheckInput(form.PATIENT_SEXEPAT.value, "SEXE", all_error);
	if ($('date_hospi_m').style.display == 'none')
		all_error = CheckInput(form.date_hospi.value, "DATE D'HOSPITALISATION", all_error);
	else
	{
		all_error = CheckInput(form.DATE_ENTREE_M.value, "Date d'entrée", all_error);
		all_error = CheckInput(form.DATE_SORTIE_M.value, "Date de sortie", all_error);
	}
	all_error = CheckInput(form.PATIENT_SERVICE.value, "SERVICE", all_error);
	if (all_error != "")
		all_error += "\n";
	all_error = CheckRadio(form.num_dem, "num_dem", "Etat demande page 1", all_error);
	all_error = CheckInput(form.situation.value, "Situation familiale page 1", all_error);
	all_error = CheckInput(form.residence.value, "Résidence page 1", all_error);
	all_error = CheckInput(form.soins.value, "Structure de soins fréquentés page 1", all_error);
	all_error = CheckInput(form.entourage.value, "Entourage page 1", all_error);
	all_error = CheckInput(form.cond_log.value, "Condition de logement page 1", all_error);
	all_error = CheckInput(form.nationalite.value, "Nationalité page 1", all_error);
	if (form.nationalite.value != '1')
		all_error = CheckInput(form.sit_etr.value, "Situation des étrangers page 1", all_error);
	all_error = CheckCheckbox("orig_rev", "Origines des revenus page 1", all_error, binaire);
	all_error = CheckCheckbox("couv_soc", "Couverture sociale page 1", all_error, binaire);
	all_error = CheckCheckbox("couv_soc_comp", "Assurance complémentaire page 1", all_error, binaire);
	all_error = CheckCheckbox("fiche_glob", "Fiche globale statistique page 2", all_error, binaire);
	all_error = CheckCheckbox("eval_soc", "Evaluation sociale page 2", all_error, binaire);
	var nb = document.getElementsByName('orien_struct_nb')[0].value;
	var tab = form.orien_struct;
	for (i = 0, ok = 0; i < nb; i++)
	{
		if (tab[i].checked == true)
		{
			ok = -1;
			break;
		}
	}
	if (document.getElementById('etablissement_os').value == '' && (i == 2 || i == 4 || i == 5 || i == 6))
		all_error += '"Etablissement Orientation structure sanitaire page 2" non rempli\n';

	if (all_error != "")
	{
		alert(all_error);
		return (false);
	}
	if (confirm("Etes-vous sur d'avoir correctement rempli le formulaire ?"))
		return (true);
	return (false);
}
function CheckFormFaq(){
	error = "";
	if (document.FORM_QUESTION.faq_sujet.value == "")
		error += 'Veuillez saisir un sujet.\n';
	if (document.FORM_QUESTION.faq_message.value == "")
		error += 'Veuillez saisir un message.\n';
	if (error == "")
		return (true);
	alert(error);
	return (false);
}
function calc_mois(mois){
	dt = new Date();
	current_year = dt.getFullYear();
	current_month = dt.getMonth() + 1;
	current_day = dt.getDate();
	if (current_day < 10)
		current_day = "0"+current_day;
	if (current_month < 10)
		current_month = "0"+current_month;

	if (mois == "13")
	{
		document.getElementById('date_debut').value = "01/01/"+current_year;
		document.getElementById('date_fin').value = "31/12/"+current_year;
	}
	else if (mois == "14")
	{
		document.getElementById('date_debut').value = current_day+"/"+current_month+"/"+current_year;
		document.getElementById('date_fin').value = current_day+"/"+current_month+"/"+current_year;
	}
	else if (mois == "15")
	{
		if (((current_month == "01" || current_month == "03" || current_month == "05" ||
			current_month == "07" || current_month == "08" || current_month == "10") && current_day == "31") ||
			((current_month == "04" || current_month == "06" || current_month == "09" ||
				current_month == "11") && current_day == "30"))
		{
			current_month++;
			if (current_month < 10)
				current_month = "0"+current_month;
			document.getElementById('date_debut').value = "01/"+current_month+"/"+current_year;
			document.getElementById('date_fin').value = "01/"+current_month+"/"+current_year;
		}
		else if (current_month == "02")
		{
			if (current_day == "29")
			{
				document.getElementById('date_debut').value = "01/03/"+current_year;
				document.getElementById('date_fin').value = "01/03/"+current_year;
			}
			else if (current_day == "28")
			{
				if (current_year % 4 == 0 && current_year % 100 != 0 || current_year % 400 == 0)
				{
					document.getElementById('date_debut').value = "29/02/"+current_year;
					document.getElementById('date_fin').value = "29/02/"+current_year;
				}
				else
				{
					document.getElementById('date_debut').value = "01/03/"+current_year;
					document.getElementById('date_fin').value = "01/03/"+current_year;
				}
			}
			else
			{
				current_day++;
				if (current_day < 10)
					current_day = "0"+current_day;
				document.getElementById('date_debut').value = current_day+"/"+current_month+"/"+current_year;
				document.getElementById('date_fin').value = current_day+"/"+current_month+"/"+current_year;
			}
		}
		else if (current_month == "12" && current_day == "31")
		{
			current_year++;
			document.getElementById('date_debut').value = "01/01/"+current_year;
			document.getElementById('date_fin').value = "01/01/"+current_year;
		}
		else
		{
			current_day++;
			if (current_day < 10)
				current_day = "0"+current_day;
			document.getElementById('date_debut').value = current_day+"/"+current_month+"/"+current_year;
			document.getElementById('date_fin').value = current_day+"/"+current_month+"/"+current_year;
		}
	}
	else if (mois == "16")
	{
		if ((current_month == "05" || current_month == "07" || current_month == "10" ||
			current_month == "12") && current_day == "01")
		{
			current_month--;
			if (current_month < 10)
				current_month = "0"+current_month;
			document.getElementById('date_debut').value = "30/"+current_month+"/"+current_year;
			document.getElementById('date_fin').value = "30/"+current_month+"/"+current_year;
		}
		else if ((current_month == "02" || current_month == "04" || current_month == "06" ||
				current_month == "08" || current_month == "09" || current_month == "11") && current_day == "01")
		{
			current_month--;
			if (current_month < 10)
				current_month = "0"+current_month;
			document.getElementById('date_debut').value = "31/"+current_month+"/"+current_year;
			document.getElementById('date_fin').value = "31/"+current_month+"/"+current_year;
		}
		else if (current_month == "03")
		{
			if (current_day == "01")
			{
				if (current_year % 4 == 0 && current_year % 100 != 0 || current_year % 400 == 0)
				{
					document.getElementById('date_debut').value = "29/02/"+current_year;
					document.getElementById('date_fin').value = "29/02/"+current_year;
				}
				else
				{
					document.getElementById('date_debut').value = "28/02/"+current_year;
					document.getElementById('date_fin').value = "28/02/"+current_year;
				}
			}
			else
			{
				current_day--;
				if (current_day < 10)
					current_day = "0"+current_day;
				document.getElementById('date_debut').value = current_day+"/"+current_month+"/"+current_year;
				document.getElementById('date_fin').value = current_day+"/"+current_month+"/"+current_year;
			}
		}
		else if (current_month == "01" && current_day == "01")
		{
			current_year--;
			document.getElementById('date_debut').value = "31/12/"+current_year;
			document.getElementById('date_fin').value = "31/12/"+current_year;
		}
		else
		{
			current_day--;
			if (current_day < 10)
				current_day = "0"+current_day;
			document.getElementById('date_debut').value = current_day+"/"+current_month+"/"+current_year;
			document.getElementById('date_fin').value = current_day+"/"+current_month+"/"+current_year;
		}
	}
	else if (mois != "13" && mois != "14")
	{
		form_date_debut = document.getElementById('date_debut').value;
		form_date_fin = document.getElementById('date_fin').value;
		current = new Date(current_year, mois, 0);
		document.getElementById('date_debut').value = "01/"+mois+"/"+current_year;
		document.getElementById('date_fin').value = current.getDate()+"/"+mois+"/"+current_year;
	}
}
function CheckLibitem(form){
	if (form.LIBITEM.value == '')
	{
		alert('Le champ doit etre rempli');
		form.LIBITEM.focus();
		return (false);
	}
	return (true);
}
function ChangeSitEtrFromNat(){
	if (document.forms.PASSWEB.nationalite.options.selectedIndex == 1)
	{
		document.getElementsByName('sit_etr')[0].value = '';
		document.getElementsByName('sit_etr')[0].style.visibility = 'hidden';
	}
	else
		document.getElementsByName('sit_etr')[0].style.visibility = '';
}
function ChangOrienStruc(){
	if (document.getElementById('orien_struct2').checked == true || document.getElementById('orien_struct4').checked == true || document.getElementById('orien_struct5').checked == true || document.getElementById('orien_struct6').checked == true)
	{
		document.getElementById('etablissement_orien_struct').style.display = '';
		document.getElementById('etablissement_orien_struct').value = '';
	}
	else
		document.getElementById('etablissement_orien_struct').style.display = 'none';
}
function Redirect(url){
	window.location.href = url;
}
function CancelDemande(numdos){
	if (confirmEvent("Etes-vous sûr de vouloir annuler cette demande ?"))
	{
		new Ajax('../ajax/demande_cancel.php', {
				data: 'cancel=1&numdos='+numdos,
				method: 'post',
					onComplete: function(request) {
					alert(request);
				}
				}).request();
	}
}
function AfficherCacherRecherche(id) { 
	document.getElementById("formtablegauche").style.display = "none"; 
	document.getElementById("formtablemessage").style.display = "none";
	document.getElementById(id).style.display = "block"; 	
	}	
function Redirection() {
setTimeout("window.location.href='faq_sommaire.php'",2000)}
function init(){
	modevisible = 2;
	ref_img = window.img_bascule;
	bascule();
}
function noclic(clic) {
	//return;
	if (document.layers)
		document.captureEvents(Event.MOUSEDOWN);
	document.onmousedown = noclic;
}

var Compteur = 0; 
//-------------------------- 
function Delete_Ligne( obj_){ 
  var Parent; 
  var Obj = obj_; 
  if( Obj){ 
    //-- tant que pas la balise <TR> 
    do{ 
       Obj = Obj.parentNode; 
    }while( Obj.tagName != "TR") 
    //-- Recup du parent 
    Parent = Obj.parentNode; 
    //-- Suppression de la ligne 
    if( Parent){ 
      Parent.deleteRow( Obj.rowIndex) 
    } 
  } 
} 

//---------------------- 
function Create_Ligne(i,nom_table,lib,code){ 
	//-- Get objet tableau 
  var O_Table = document.getElementById(nom_table); 
  //-- Get nombre de ligne du tableau 
  var NbrLigne = O_Table.rows.length; 
  //-- Position d'insertion 
  var Pos = NbrLigne; 
   //if (document.getElementById('LIB[]').value !isNull) alert(document.getElementById('LIB[]').value+" "+NbrLigne);
  var ligne_a_creer= i; 
  var j; 
  for(j=0; j<ligne_a_creer; j++){ 
	   Compteur++; 
	  //-- Insertion d'une ligne 
	  O_Row  = O_Table.insertRow( Pos); 
	  //-- Insertion des cellules 
	  O_Cell = O_Row.insertCell(-1); 
	  if(code!="") 
	  {
	  	O_Cell.innerHTML = '<textarea name="LIB[]" id="LIB[]" style="resize: none;width: 532px;height: 30px;font-size:12px">'+lib+' [('+code+')]</textarea>'; 
	  	
	  	O_Cell = O_Row.insertCell(-1); 
	  	O_Cell.innerHTML = '&nbsp;&nbsp;<img src="../commun/images/l_cancel2.gif" onClick="Delete_Ligne(this)" />';
	  }
		else 
		{
			if(lib != "")
			{
				O_Cell.innerHTML = '<textarea name="LIB[]" id="LIB[]" style="resize: none;width: 532px;height: 30px;font-size:12px">'+lib+'</textarea>'; 
				
				O_Cell = O_Row.insertCell(-1); 
	  		O_Cell.innerHTML = '&nbsp;&nbsp;<img src="../commun/images/l_cancel2.gif" onClick="Delete_Ligne(this)" />';
	  	}
	  	else
	  	{
	  		alert("Veuiller saisir un DAS svp");
	  	}
		}
  }
  //isEdit=true; // control sur modif sur le formulaires		 
} 

function NewDivTab(MaDivTab,MonContenu){
	//alert(MaDivTab);
	document.getElementById(MaDivTab).innerHTML="<table id='das-table' border='0'><tr><td colspan='2'>"+MonContenu+"</td></tr></table>";
}

function clearInputTxt(objet1,objet2){
	document.getElementById(objet1).value = '';
	//document.getElementById(objet2).value = '';
}

function PutInputTxt(objet1,objet2,val1,val2){
	document.getElementById(objet1).value = val1+' [('+val2+')]';
	/*document.getElementById(objet1).value = val1;
	document.getElementById(objet2).value = val2;*/
}

function showOrHide(id){
	if (id=="TOUS"){
		for(c=0;c < pages.length;c++){
			ShowMe(pages[c]);
			}
	}else if (id==""){
		ShowMe(pages[0]);
		for(c=1;c < pages.length;c++){
			HideMe(pages[c]);
			}
	}else{
		for(c=0;c < pages.length;c++){
			if (id==pages[c]){
				ShowMe(pages[c]);
			}else{
				HideMe(pages[c]);			
			}
		}
	}
	document.getElementById('C_DIV').value=id;
}

function HideMe(id){
 	document.getElementById(id).style.display='none';

}
function ShowMe(id){
 	document.getElementById(id).style.display='';

}

function simulv16()
{
	//alert('Lancer simulateur');
	//document.CODAGE.action="http://10.129.223.30/pdg/progsimulatorsimpleV17.php";
	//document.CODAGE.target="mainFrame"
	
	//Récupération d'AGE
	if (document.getElementById('AGE').value != "")
	{
		var elem = document.getElementById('AGE').value.split('/');
		document.getElementById('Age').value=elem[0];
	}
	
	//Récupération du SEXE
	if (document.getElementById('SEXE').value != "")
	{
		switch (document.getElementById('SEXE').value)
		{
			case 'F':
				document.getElementById('Sexe').value='2';
			break; 
			case 'M':
				document.getElementById('Sexe').value='1';
			break;
			default :
			break;
		}
	}
	//Récupération DP
	obj_DP=document.getElementById('LIB1');
	if(!obj_DP){
		alert("Id_DP introuvable dans le formulaire!");
		return false;
	}else{	
		DP=obj_DP.value;
		if (DP.length < 3 )
		{
			alert("Invalid DP ou DP vide ["+DP+"]");
			obj_DP.focus();
			return false
		}
	}
	//decoupe le DP
	document.getElementById('Dp').value=DP.substring(DP.lastIndexOf("[(")+2,DP.lastIndexOf(")]"));
		
	//Récupération DR
	obj_DR=document.getElementById('LIB2');
	if(!obj_DR){
		alert("Id_DR introuvable dans le formulaire!");
		return false;
	}else{
		DR1=obj_DR.value;
	}
	//decoupe le DR
	if (DR1.length > 3 )
		{
			document.getElementById('Dr').value=DR1.substring(DR1.lastIndexOf("[(")+2,DR1.lastIndexOf(")]"));
		}
	
	
	//Récupération des DAS
	LIB = document.getElementsByName('LIB[]');
	NoMax = LIB.length;
	ListDas = "";
	
	for (i=0;i < NoMax ;i++)
	{
		if (i == 0)
			ListDas=LIB[i].value.substring(LIB[i].value.lastIndexOf("[(")+2,LIB[i].value.lastIndexOf(")]"));
		else
			ListDas=ListDas+" "+LIB[i].value.substring(LIB[i].value.lastIndexOf("[(")+2,LIB[i].value.lastIndexOf(")]"));
	}
	document.getElementById('Das').value=ListDas;
		
	//Récupération Acte
	document.getElementById('Acte').value=document.getElementById('LIBActe').value;
	
	document.CODAGE.button_clicked.value="Simulateur";
	document.CODAGE.submit();
	
}

function SavePage(val)
{
	//alert(document.getElementById('LIB1').value.length);
	msg = "Les éléments suivants sont nécessaires pour enregistrer le codage :\n";
	if (document.getElementById('NDA').value.length < 9) msg=msg+("	- NDA\n");
	if (document.getElementById('UH').value.length < 1) msg=msg+("	- Séjour\n");
	//if (document.getElementById('LIB1').value.length < 1) msg=msg+("	- DP\n");
	if (msg == "Les éléments suivants sont nécessaires pour enregistrer le codage :\n")
	{
		document.CODAGE.button_clicked.value=val;
		document.CODAGE.action = "PrintCodage.php"; 
		document.CODAGE.submit();
	}
	else
	{
		//alert("toto");
		alert(msg);
		return false;
	}
}
function HideSimulateur(YesNo){
	Idsimulateur='ResultatSimulateur';
	IdRubrique='RubriqueCIM10';
	obj_simul = document.getElementById(Idsimulateur)
	obj_rubriq= document.getElementById(IdRubrique)
	if (obj_simul && obj_rubriq){
		if (YesNo) // true or false
		{
			obj_simul.style.display='none';
			obj_rubriq.style.display='block';
		}	
		else
		{
			obj_simul.style.display='block';
			obj_rubriq.style.display='none';
		}
		
	}else{
	alert(' Id introuvable dans le formaulaire! '+Id);
	}
	
}

function row_mout(mout_value){
	document.getElementById(mout_value).style.background = "#FFFFFF";
}

function row_mover(mover_value){
	document.getElementById(mover_value).style.background = "#D0D8EB";
}

function row_click(click_value){
	click_var=document.getElementById('chk_'+click_value);
	//alert(click_var.value);
	if(click_var.value == "yes"){ 
		document.getElementById(click_value).style.background = "#D0D8EB"; 
		document.getElementById(click_value).style.fontWeight = "bold"; 
		document.getElementById(click_value).onmouseout = ""; 
		document.getElementById(click_value).onmouseover = "";
	}//if over
	else{ 
		document.getElementById(click_value).style.background = "#FFFFFF"; 
		document.getElementById(click_value).style.fontWeight = "normal"; 
		document.getElementById(click_value).onmouseout = function onmouseout(event){row_mout(click_value);}
		document.getElementById(click_value).onmouseover = function onmouseover(event){row_mover(click_value);}
	}//else if over
}//row_click over

function row_click_unclick(mclick_value){
	chk_max=document.getElementById('chk_max').value ;
	for(i=0;i < chk_max;i++){
		if (mclick_value==i)
			document.getElementById('chk_'+i).value = "yes";
		else
			document.getElementById('chk_'+i).value = "no";
		row_click(i);
	}
}

function trim(myString)
{
	return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
} 
