/*
##########################################################################################
	Projet CODAGE
	Script appelé dans la page "index.php"
	Liés avec "ajax_codage_get_sejour.php", "ajax_codage_getdatesortie.php", "ajax_codage_get_codage.php"
	Auteur Thierry CELESTE SLS APHP
	Maj le 22/05/2013
##########################################################################################
	Parametres de page 
*/


/*
//------------------------------------------------------------
	function CheckSelection(parametres)
  parametres : NDA|AGE|DAMVAD|UH|LBUF|NOHJO
 	get codage d'un sejour selectionné + date sortie
//------------------------------------------------------------
*/
function CheckSelection(retval){
	if (retval==""){
 		alert("Aucune valeur selectionnée!!!");
 		return false;
 	}
	//alert(retval);
	//exit;
	//$retval=$NDA."|".$AGE."|".$DAMVAD."|".$UH."|".$LBUF."|".$NOHJO."|".$DASOR."|".$Jours;
	retval=retval.split("|");
	//alert(retval);
	//$retdate="'".$dasor."|".$Jours."'";
	//retdate=LanceAjax("../commun/ajax/ajax_codage_getdatesortie.php",'nda='+escape(retval[0])+'&uh='+escape(retval[3])+'&dtent='+escape(retval[2])+'&tydos='+escape(retval[5])) ;	
	//retdate=retdate.split("|");
	if(retval[5] == 0) 
 		retval[5]="";

	//mettre a jour les hidden varibles
	$("#NDA").val(retval[0]);
	$("#AGE").val(retval[1]);
	$("#DTEENT").val(retval[2]);
	$("#UH").val(retval[3]);
	$("#LIBUH").val(retval[4]);
	$("#DATSOR").val(trim(retval[5]));
	$("#Jours").val(retval[6]);
	$("#NOHJO").val(retval[7]);

	//Remise à jour données patient
	$('#NIP').val("");
	LoadPatInfo();
	
	//recupre les codage format XML
	XmlCodage=LanceAjax("../commun/ajax/ajax_codage_get_codage.php",'NDA='+escape(retval[0])+'&UH='+escape(retval[3])+'&NOHJO='+escape(retval[7])) ;	
	// parse XML
	ParseXmlCodage= $.parseXML(XmlCodage) ;

	//decoupe DP et DR	recuparer par XML
	dp=$(ParseXmlCodage).find('dp').text();
	dr=$(ParseXmlCodage).find('dr').text(); 	
	$("#ID_DP").val(dp);
	$("#ID_DR").val(dr);


	// GESTION DAS
	var tbl_das = $("#DAS_LIST");
	
	//vider la table das avant d'afficher les nouveaux das
	$("#DAS_LIST tr:gt(0)").remove();
	
	// boucle sur les Das recuparer par XML
	$(ParseXmlCodage).find('das').each(function(index,value){
		das=($(this).text());
		Create_Ligne(tbl_das,"DAS",das);  
	});
	
	// GESTION ACTES
	if($("#AfficheActes").val()=='1')
	{
		var tbl_actes = $("#ACTES_LIST");
		
		//vider la table actes avant d'afficher les nouveaux actes
		$("#ACTES_LIST tr:gt(0)").remove();
		
		// boucle sur les Das recuparer par XML
		$(ParseXmlCodage).find('actes').each(function(index,value){
			actes=($(this).text());
			Create_Ligne(tbl_actes,"ACTES",actes);  
		});
		
		//Charge la liste des dates intervention ipop du patient au CheckSelection
		//getPage('../commun/ajax/ajax_codage_get_intervention.php?NDA='+$("#NDA").val()+'&DTEENT='+$("#DTEENT").val(),'refresh_liste_intervention');
		InterventionListe=LanceAjax("../commun/ajax/ajax_codage_get_intervention.php",'NIP='+$("#NIP").val()+'&NDA='+$("#NDA").val()+'&DTEENT='+$("#DTEENT").val()+'&NOHJO='+$("#NOHJO").val()) ;	
		$('#actes_info').css({ 'display': ''});
		$("#actes_info").html(InterventionListe);
		//Force RefreshInfoIntervention 
		var MyIntervention=$("#MyIntervention").val();
				RefreshInfoIntervention(MyIntervention);
				MyROWACTID=$("#MyROWACTID").val();
				$("tr[name="+MyROWACTID+"]").css({"background-color":"#D0D8EB","font-weight":"bold"});
	}
	//Charge l'historique codage + cma du patient au CheckSelection
	getPage('../commun/ajax/ajax_codage_get_historique_cma.php?NIP='+$("#NIP").val(),'boitecodage');
	//Chargement Menu Rubrique 
	//toto = "tata"+<?php echo $NIP; ?>;
	//getPage('../commun/ajax/ajax_codage_menu_rubrique.php?thesaurus='+thesaurus,'refresh_menu_rubrique');
	//thesaurus='076';
	//alert($("#FicherCodage").val());
	ThesaurusListe=LanceAjax("../commun/ajax/ajax_codage_menu_rubrique.php","thesaurus="+$("#FicherCodage").val()) ;	
	$("#refresh_menu_rubrique").html(ThesaurusListe);
		
}
