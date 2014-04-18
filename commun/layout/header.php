<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset=utf-8>
  <title>Intranet MSI <?php print $PageHeader?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=8" > 
  <meta http-equiv="X-UA-Compatible" content="IE=edge">  
	<link rel="shortcut icon" type="image/x-icon" href="../commun/images/favicon.ico" />      
  <meta name="description" content="">
  <meta name="author" content="mohanraju">

  <!-- Le styles -->

		    <link href="../commun/styles/bootstrap.css" rel="stylesheet" media="screen">
		    <link href="../commun/styles/bootstrap-responsive.css" rel="stylesheet">
		    <link href="../commun/styles/bootstrap_extra.css" rel="stylesheet">  
		    <link href="../commun/styles/smoothness/jquery-ui-1.10.2.custom.css" rel="stylesheet">  
		    
		    <script src="../commun/js/jquery.js"></script>
		    <script src="../commun/js/jquery_ui.js"></script>
				<script src="../commun/js/fonctions.js"></script>		    		    
				<script src="../commun/ajax/ajax_changer_hopital.js"></script>		    		    				
				
		    
  <style type="text/css">
    body {
      padding-top: 5px;
      padding-bottom: 40px;

    }
  </style>
  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="../commun/js/html5shiv.js"></script>
  <![endif]-->
  <?php
  $_ScreenWidth= "<script>document.write(screen.width)</script>";  
  $_ScreenHeight= "<script>document.write(screen.height)</script>";

  ?>  
  <script>
  /*###########################################################################
  #  Commun date picker jqueryUI fonction 
  #  function avec le ID du HTML element date_deb et date_fin
  #  ex <input type =text id="date_deb">
  ############################################################################
  */
		jQuery(function($){
		   $.datepicker.regional['fr'] = {
		      closeText: 'Fermer',
		      prevText: '<Préc',
		      nextText: 'Suiv>',
		      currentText: 'Courant',
		      monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		      'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		      monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun',
		      'Jul','Aoû','Sep','Oct','Nov','Déc'],
		      dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
		      dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
		      dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		      weekHeader: 'Sm',
		      //dateFormat: 'dd/mm/yy',
		                dateFormat: 'dd/mm/yy',
		      firstDay: 1,
		      isRTL: false,
		      showMonthAfterYear: false,
		      yearSuffix: ''};
		   $.datepicker.setDefaults($.datepicker.regional['fr']);
		});
				
		$(function() {
		if(($('#date_deb').length))	
			$( "#date_deb" ).datepicker();
			
		if(($('#date_fin').length))		
			$( "#date_fin" ).datepicker();
		});
	</script>
  <script>
  // GESTION TOOLTIP 

	$(function() {
  	$( document ).tooltip({
      items: "img, [images], [title], [TOOLTIP]",
      content: function() {
        var element = $( this );
        if ( element.is( "[images]" ) ) {
          var FicImage =element.attr("images")
           return "<img  src='"+FicImage+"'>";
        }
        if ( element.is( "[title]" ) ) {
          return element.attr( "title" );
        }
        if ( element.is( "[TOOLTIP]" ) ) {
          return element.attr( "TOOLTIP" );
        }        
        if ( element.is( "img" ) ) {
          return element.attr( "alt" );
        }
 
      }
    });
  });  	
		
  	
 
  </script>


<!-- Piwik --> 
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://webstats.sls.aphp.fr/" : "http://webstats.sls.aphp.fr/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 3);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://webstats.sls.aphp.fr/piwik.php?idsite=3" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->
