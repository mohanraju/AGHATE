<?php
include "./config/config.php";
include "./config/config.inc.php";
include "./commun/include/misc.inc.php";
include "./commun/include/mrbs_sql.inc.php";
include "./commun/include/functions.inc.php";
include "./commun/include/$dbsys.inc.php";
include "./commun/include/mincals.inc.php";
$grr_script_name = "day.php";
#Paramètres de connection
require_once("./commun/include/settings.inc.php");
   global $vocab, $search_str, $grrSettings, $session_statut, $clock_file, $is_authentified_lcs, $desactive_VerifNomPrenomUser, $grr_script_name;
   global $use_prototype, $use_tooltip_js, $desactive_bandeau_sup;

   if (!($desactive_VerifNomPrenomUser)) $desactive_VerifNomPrenomUser = 'n';
   // On vérifie que les noms et prénoms ne sont pas vides
 
   if ($type == "with_session")
       echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");
   else
       echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"no_session");
   // Si nous ne sommes pas dans un format imprimable
   if ((!isset($_GET['pview'])) or ($_GET['pview'] != 1)) {

   # If we dont know the right date then make it up
     if (!isset($day) or !isset($month) or !isset($year) or ($day == '') or ($month == '') or ($year == '')) {
         $date_now = time();
         if ($date_now < getSettingValue("begin_bookings"))
             $date_ = getSettingValue("begin_bookings");
         else if ($date_now > getSettingValue("end_bookings"))
             $date_ = getSettingValue("end_bookings");
         else
             $date_ = $date_now;
        $day   = date("d",$date_);
        $month = date("m",$date_);
        $year  = date("Y",$date_);
     }
   if (!(isset($search_str))) $search_str = get_vocab("search_for");
   if (empty($search_str)) $search_str = "";
   ?>
   <SCRIPT type="text/javascript" LANGUAGE="JavaScript">
    chaine_recherche = "<?php echo $search_str; ?>";
    function encode_adresse(user,domain,label,link) {
        var address = user+'@'+domain;
        var toWrite = '';
        if (link > 0) {toWrite += '<a href="mailto:'+address+'">';}
        if (label != '') {toWrite += label;} else {toWrite += address;}
        if (link > 0) {toWrite += '<\/a>';}
        document.write(toWrite);
    }

   	function OnSubmitForm()
		{
			if(document.pressed == 'd')
			{
				document.myform.action ="day.php";
			}
			if(document.pressed == 'w')
			{
				document.myform.action ="week_all.php";
			}
			if(document.pressed == 'm')
			{
           <?php
				echo "		document.myform.action = \"";
				if (isset($_SESSION['type_month_all'])) {echo $_SESSION['type_month_all'].".php";}
				else {echo "month_all.php";}
				echo "\";\n";
           ?>
			}
			return true;
		}
		</SCRIPT>


    <?php

    // Affichage du message d'erreur en cas d'échec de l'envoi de mails automatiques
    if (!(getSettingValue("javascript_info_disabled"))) {
      if ((isset($_SESSION['session_message_error'])) and ($_SESSION['session_message_error']!=''))  {
        echo "<script type=\"text/javascript\" language=\"javascript\">";
        echo "<!--\n";
        echo " alert(\"".get_vocab("title_automatic_mail")."\\n".$_SESSION['session_message_error']."\\n".get_vocab("technical_contact")."\")";
        echo "//-->";
        echo "</script>";
        $_SESSION['session_message_error'] = "";
      }
    }
if (!(isset($desactive_bandeau_sup) and ($desactive_bandeau_sup==1) and ($type != 'with_session'))) {
?>

   <TABLE WIDTH="100%" border="0">
    <TR>
      <TD class="border_banner">
       <TABLE WIDTH="100%" BORDER=0>
        <TR>
         <TD CLASS="banner">
     <?php
          $param= 'yes';
          // On fabrique une date valide pour la réservation si ce n'est pas le cas
          $date_ = mktime(0, 0, 0, $month, $day, $year);
          if ($date_ < getSettingValue("begin_bookings"))
              $date_ = getSettingValue("begin_bookings");
          else if ($date_ > getSettingValue("end_bookings"))
              $date_ = getSettingValue("end_bookings");
          $day   = date("d",$date_);
          $month = date("m",$date_);
          $year  = date("Y",$date_);

          echo "&nbsp;<A HREF=\"".page_accueil($param)."day=$day&amp;year=$year&amp;month=$month\">".get_vocab("welcome")."</A> - ";
            echo "<A HREF='admin_accueil.php?day=$day&amp;month=$month&amp;year=$year'>".get_vocab("admin")."</A>\n";
          if ($type == 'no_session') {

//            echo "<br />&nbsp;<a href='login.php'>".get_vocab("connect")."</a>";

				// (UT1-LC 05/2008) distinction connexion CAS-LDAP / locale
				if ((getSettingValue('sso_statut') == 'cas_visiteur') or (getSettingValue('sso_statut') == 'cas_utilisateur'))
					{
					echo "<br />&nbsp;<a href='index.php'>".get_vocab("authentification")."</a>";
					echo "<br />&nbsp;<small><i><a href='login.php'>".get_vocab("connect_local")."</a></i></small>";
					}
				else
					{
					echo "<br />&nbsp;<a href='login.php'>".get_vocab("connect");
					echo "<img src=\"./commun/images/connexion.jpg\" width=\"20\" height=\"20\" /> </a>";					
					}


          } else {
        echo "<br />&nbsp;<b>".get_vocab("welcome_to").$_SESSION['prenom']." ".$_SESSION['nom']."</b>";
             if (IsAllowedToModifyMdp() or IsAllowedToModifyProfil())
                echo "<br />&nbsp;<a href=\"my_account.php?day=".$day."&amp;year=".$year."&amp;month=".$month."\">".get_vocab("manage_my_account")."</a>";
             //if ($type == "with_session") {
                 $parametres_url = '';
                 $_SESSION['chemin_retour'] = '';
                 if (isset($_SERVER['QUERY_STRING']) and ($_SERVER['QUERY_STRING'] != '')) {
                     // Il y a des paramètres à passer
                     $parametres_url = htmlspecialchars($_SERVER['QUERY_STRING'])."&amp;";
                     $_SESSION['chemin_retour'] = traite_grr_url($grr_script_name)."?". $_SERVER['QUERY_STRING'];
                 }
                 echo " - <a href=\"".traite_grr_url($grr_script_name)."?".$parametres_url."default_language=fr\"><img src=\"./commun/images/fr_dp.png\" alt=\"France\" title=\"france\" width=\"20\" height=\"13\" align=\"middle\" border=\"0\" /></a>\n";
                 echo "<a href=\"".traite_grr_url($grr_script_name)."?".$parametres_url."default_language=de\"><img src=\"./commun/images/de_dp.png\" alt=\"Deutch\" title=\"deutch\" width=\"20\" height=\"13\" align=\"middle\" border=\"0\" /></a>\n";
                 echo "<a href=\"".traite_grr_url($grr_script_name)."?".$parametres_url."default_language=en\"><img src=\"./commun/images/en_dp.png\" alt=\"English\" title=\"English\" width=\"20\" height=\"13\" align=\"middle\" border=\"0\" /></a>\n";
                 echo "<a href=\"".traite_grr_url($grr_script_name)."?".$parametres_url."default_language=it\"><img src=\"./commun/images/it_dp.png\" alt=\"Italiano\" title=\"Italiano\" width=\"20\" height=\"13\" align=\"middle\" border=\"0\" /></a>\n";
                 echo "<a href=\"".traite_grr_url($grr_script_name)."?".$parametres_url."default_language=es\"><img src=\"./commun/images/es_dp.png\" alt=\"Spanish\" title=\"Spanish\" width=\"20\" height=\"13\" align=\"middle\" border=\"0\" /></a>\n";

             //}
             if (!((getSettingValue("sso_statut") == 'lcs') and ($_SESSION['source_login']=='ext') and ($is_authentified_lcs == "yes")))
               if (getSettingValue("authentification_obli") == 1) {
                 echo "<br />&nbsp;<a href=\"./logout.php?auto=0\" >".get_vocab('disconnect')."</a>";
               } else {
                 echo "<br />&nbsp;<a href=\"./logout.php?auto=0&amp;authentif_obli=no\" >".get_vocab('disconnect')."</a>";
               }
	             if ((getSettingValue('sso_statut') == 'lasso_visiteur')
		           or (getSettingValue('sso_statut') == 'lasso_utilisateur')) {
		               echo "<br />";
		               if ($_SESSION['lasso_nameid'] == NULL)
		                   echo "<a href=\"lasso/federate.php\">".get_vocab('lasso_federate_this_account')."</a>";
		               else
		                   echo "<a href=\"lasso/defederate.php\">".get_vocab('lasso_defederate_this_account')."</a>";
	            }
          }
      ?>
     </TD>
     <?php
     if ($page=="no_admin") {
     ?>
         <TD CLASS="banner"  ALIGN=center>
           <form name="myform" action="" method="get" onSubmit="return OnSubmitForm();">
           <?php
          
           if (!empty($area)) echo "<INPUT TYPE=\"hidden\" name=\"area\" value=\"$area\" />"
           ?>
           <br />
           <br />
           </form>
           <form name="myform2" action="" method="get" onSubmit="return OnSubmitForm();">
           <?php
           if (!empty($area)) echo "<INPUT TYPE=\"hidden\" name=\"area\" value=\"$area\" />"
           ?>
           </FORM>
         </TD>
         <?php
     }
     if ($type == "with_session") {
          if ((authGetUserLevel(getUserName(),-1,'area') >= 4) or (authGetUserLevel(getUserName(),-1,'user') == 1))  {
           echo "<TD CLASS=\"banner\" ALIGN=center>";
 echo "<img src=\"./commun/images/logo-Saint-Louis-2004-2.gif\" width=\"200\" height=\"65\" />";

/*
           if(authGetUserLevel(getUserName(),-1,'area') >= 5)  {
              echo "<br /><form action=\"admin_save_mysql.php\" method=\"get\" name=sav>\n
              <input type=\"submit\" value=\"".get_vocab("submit_backup")."\" />\n
              </form>";
              how_many_connected();
           }
  */         
           echo "\n</TD>";
      }
     }
      ?>
          <TD CLASS="banner" ALIGN=left>
          
      <?php 
      

      /*  par mohan script horologe arreté
      if (@file_exists($clock_file)) {
        echo "<script type=\"text/javascript\" LANGUAGE=\"javascript\">";
        echo "<!--\n";
        echo "new LiveClock();\n";
        echo "//-->";
        echo "</SCRIPT><br />";
      }
	*/
     // echo grr_help("","")."<br />";
/*    
    if (verif_access_search(getUserName())) {
          echo "<A HREF=\"report.php\">".get_vocab("report")."</A><br />";
      }
      //echo "<span class=\"small\">".affiche_version()."</span> - ";
      
      if ($type == "with_session") {
          if ($_SESSION['statut'] == 'administrateur') {
              $email = explode('@',getSettingValue("technical_support_email"));
              $person = $email[0];
              $domain = $email[1];
              echo "<script type=\"text/javascript\">encode_adresse('".$person."','".$domain."','".AddSlashes(get_vocab("technical_contact"))."',1);</script><br />";
          } else {
              $email = explode('@',getSettingValue("webmaster_email"));
              $person = $email[0];
              $domain = $email[1];
              echo "<script type=\"text/javascript\">encode_adresse('".$person."','".$domain."','".AddSlashes(get_vocab("administrator_contact"))."',1);</script><br />";
          }
      } else {
              $email = explode('@',getSettingValue("webmaster_email"));
              $person = $email[0];
              $domain = $email[1];
              echo "<script type=\"text/javascript\">encode_adresse('".$person."','".$domain."','".AddSlashes(get_vocab("administrator_contact"))."',1);</script><br />";
      }
*/
          ?>
         </TD>
        </TR>
       </TABLE>
      </TD>
     </TR>
    </TABLE>
<?php
}
if (isset($use_prototype))
    echo "<script type=\"text/javascript\" src=\"./commun/js/prototype-1.6.0.2.js\"></script>";
if (isset($use_tooltip_js))
    echo "<script type=\"text/javascript\" src=\"./commun/js/tooltip.js\"></script>";
echo getSettingValue('message_accueil');
  }



?>
