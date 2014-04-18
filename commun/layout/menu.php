</head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
					<!-- Menu starts here -->	
					 <a class="brand" href="#">
					 	 
					 		<img src="../commun/images/Portail.png" style="height:30px; ">
					  </a>
           
          <div class="nav-collapse collapse">
            <ul class="nav">
            

              <li><a href="../exhaustivite/" title="Evolution journalière de l'exhaustivité du codage" ><i class='icon-home icon-white'></i> Accueil</a></li>
             <?php
 	             
             	if ($_SESSION["PROJET"]=="MSI")
             	{
             		//TBB et suivi pout tous
             		//---------------------------------
             	  print "<li><a href='../tdb'><i class='icon-ok  icon-white'></i>TdB</a></li>
											<li><a href='../suivi'><i class='icon-tasks  icon-white'></i>Tableau de suivi</a></li>";

								// NESTOR et IPOP pour MSI			
								//---------------------------------
             	  if ( ($_SESSION["droits"]=="ADMIN") or ($_SESSION["droits"]=="MSI") or ($_SESSION["droits"]=="DEMO") )
             	  { 	
			             print "<li><a href='../nestor'><i class='icon-thumbs-up  icon-white'></i>Qualité</a></li>
			             				<li><a href='../ipop'><i class='icon-eye-open  icon-white'></i>Surveillance Ipop</a></li>";
			          }

								// Lamda et ouitl MSI pour SLS-MSI
								//---------------------------------
								if( (($_SESSION["droits"]=="ADMIN") or ($_SESSION["droits"]=="MSI")) and ($_SESSION[site]=="076"))
								{
									
									print "
			             
			             <li class='dropdown'>
			               <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-wrench icon-white'></i>Ouitl MSI <b class='caret'></b></a>
			               <ul class='dropdown-menu'>
			                 $MenuChangeSite
			                 <li><a href='../user/liste_structure.php'>																		Structure GH</a></li>			                 
			                 <li><a href='../outilmsi/MSI_mouvement_gilda.php'>												Mouvements Gilda</a></li>
			                 <li><a href='../outilmsi/recherche_patient.php'>													Recherche patients</a></li>
			                 <li><a href='../outilmsi/MSI_consultation_gilda.php'>										Recherche consultations</a></li>
			                 <li class='divider'></li>		                 
			                 <li><a href='../outilmsi/MSI_resume_manquante.php'>											Résumés Manquants</a></li>			               
			                 <li><a href='../outilmsi/MSI_req_soins_pal.php'>													Soins paliatifs</a></li>
			                 <li><a href='../outilmsi/MSI_puit_actes.php'>														Puit d'actes</a></li>	
 			                 <li class='divider'></li>		                 			                  			                 
			                 <li><a href='http://gesthdj.sls.aphp.fr/codage_pmsi.php' target='_blnak'>Codage AGHATE</a></li>

 			                 <li class='divider'></li>	
			                 <li><a href='../codage/code_liste.php'>																	Outil de codage : Liste des libelles sans code </a></li>			                 
			             		<li><a href='../lamda'>Lamda</a></li>     
			               </ul>
		  							</li>";	
		  						}	

		  						
		  						//Gestion users pour ADMIN ou MSI
		  						//---------------------------------
		  						if(($_SESSION["droits"]=="ADMIN") or  ($_SESSION["droits"]=="MSI"))
		  						{


 										print "
 										<li class='dropdown'>
			               <a id='menu_user' href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='icon-user icon-white'></i>Utilisateurs<b class='caret'></b></a>
													<ul class='dropdown-menu' role='menu' aria-labelledby='menu_user'>
													<li><a role='menuitem' tabindex='-1' href='../user/user_liste.php'><i class='icon-th-list' ></i> Gestion droits</a></li>
													<li><a role='menuitem' tabindex='-1' href='../user/user_ajoute.php'><i class='icon-pencil'></i> Ajouter</a></li>
													<li><a role='menuitem' tabindex='-1' href='../user/traces_site.php'><i class='icon-pencil'></i>Log</a></li>
													<li><a role='menuitem' tabindex='-1' href='#' onclick='ChangerHopital()'  >[".$_SESSION[site]."] Changer Hôpital</a></li>
													</ul>
		  							</li>";
		  						}

		  						//Deconnexion pour tous
		  						//---------------------------------
						      Print "  <li><a href='../user/logout.php'  title='Deconnexion'><i class='icon-off icon-white'></i>  </a></li>";  							
          
						}else{
						    echo "<li><a href='../tdb'  title='Tableau de bord Activité GH/Hopital/service'><i class='icon-ok  icon-white'></i>Tableau de bord Activité</a></li>";
				        echo  "<li><a href='../user/login.php' TOOLTIP='Accès au rubrique securisé par authentification'><i class='icon-user icon-white'></i> Connexion</a></li>";

						}
           ?>
           <!-- RECHERCHE Module a developper
           <li>		
               <form class="form-search">
    							<input type="text" class="input-medium search-query">
    							<i class="icon-search icon-white"></i>
    					</form>
    				</li>	
 					</ul>
 					-->
 
          </div><!--/.nav-collapse -->
          
          <!-- Menu Ends here -->	
        </div>
      </div>
    </div>
    
 
