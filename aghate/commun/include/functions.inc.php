<?php
#########################################################################
#                        functions.inc.php                              #
#                                                                       #
#                Bibliothèque de fonctions                              #
#                                                                       #
#            Dernière modification : 28/03/2008                         #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
 * D'après http://mrbs.sourceforge.net/
 *
 * This file is part of GRR.
 *
 * GRR is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GRR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GRR; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

error_reporting(E_ALL^E_NOTICE);
/**
 * FUNCTION: how_many_connected()
 * DESCRIPTION: Si c'est un admin qui est connecté, affiche le nombre de personnes actuellement connectées.
 */
function how_many_connected() {
    if(authGetUserLevel(getUserName(),-1) >= 5)
    {
      $sql = "SELECT LOGIN FROM agt_log WHERE END > now()";
      $res = grr_sql_query($sql);
      $nb_connect = grr_sql_count($res);
      if ($nb_connect == 1)
        echo "<a href='admin_view_connexions.php'>".$nb_connect.get_vocab("one_connected")."</a>";
      else
        echo "<a href='admin_view_connexions.php'>".$nb_connect.get_vocab("several_connected")."</a>";
        // Vérification du numéro de version
      if (verif_version())
         affiche_pop_up(get_vocab("maj_bdd_not_update").get_vocab("please_go_to_admin_maj.php"),"force");
    }
}

/*
Teste s'il reste ou non des plages libres sur une journée donnée pour un domaine donné.
Arguments :
$id_room : identifiant de la ressource
$month_week : mois
$day_week : jour
$year_week : année
Renvoie vraie s'il reste des plages non réservées sur la journée
Renvoie faux dans le cas contraire
*/
function plages_libre_semaine_ressource($id_room, $month_week, $day_week, $year_week) {
    global $morningstarts, $eveningends, $eveningends_minutes, $resolution, $enable_periods;
    $date_end = mktime($eveningends, $eveningends_minutes, 0, $month_week, $day_week, $year_week);
    $date_start = mktime($morningstarts,0,0,$month_week,$day_week,$year_week);
    $t = $date_start-1;
    $plage_libre = FALSE;
    $plage_libre = 0;
    while ($t < $date_end) {
        $t += $resolution;
        $test = grr_sql_query1("SELECT count(id) FROM agt_loc where
         room_id='".$id_room."' and
         start_time <= ".$t." AND
         end_time >= ".$t." ");
        if ($test == 0)
            $plage_libre = TRUE;
    }
    return $plage_libre ;
}


/*
Arguments :
$mot_clef : mot clé référencé dans la base de données de la documentation et permettant d'accéder au bon article
$ancre : ancre à l'intérieur de l'article (facultatif)

Renvoie une portion de code html correspondant à l'affichage d'une image indiquant la présence d'une aide
Un lien sur l'image renvoie sur un article de la documentation sur le site http://grr.mutualibre.org/documentation
*/
function grr_help($mot_clef,$ancre="") {
    // lien aide sur la page d'accueil
    if  ($mot_clef=="") {
       if (getSettingValue("gestion_lien_aide")=='perso') {
           $tag_help = "&nbsp;<A href='".getSettingValue("lien_aide")."' target='_blank'>".get_vocab("help")."</a>";
       } else {
        $tag_help = "&nbsp;<A href='http://grr.mutualibre.org/documentation/index.php' target='_blank'>".get_vocab("help")."</a>";
       }
    } else {
        $tag_help = "&nbsp;<A href='javascript:centrerpopup(\"";
        $tag_help .= "http://grr.mutualibre.org/documentation/index.php";
        $tag_help .= "?mot_clef=".$mot_clef;
            if ($ancre !="") $tag_help .="&amp;ancre=".$ancre;
        $tag_help .= "\",800,480,\"scrollbars=yes,statusbar=no,resizable=yes\")'>";
        $tag_help .= "<img src=\"./commun/images/help.png\" align=\"middle\" alt=\"Help\" title=\"Help\" width=\"16\" height=\"16\" border=\"0\"  />";
        $tag_help .= "</a>";
    }
    return $tag_help;
}

/* Fonction spéciale SE3grr_sql_query1
 $grp : le nom du groupe
 $uid : l'uid de l'utilisateur
 Cette fonction retourne "oui" ou "non" selon que $uid appartient au groupe $grp, ou bien "faux" si l'interrogation du LDAP échoue
 Seuls les groupes de type "posixGroup" sont supportés (les groupes de type "groupOfNames" ne sont pas supportés).
*/
function se3_grp_members ($grp,$uid) {
    include "./config/config_ldap.inc.php";
    $est_membre="non";
    // LDAP attributs
    $members_attr = array (
    "memberUid"   // Recherche des Membres du groupe
    );
    // Avec des GroupOfNames, ce ne serait pas ça.
    $ds=@ldap_connect($ldap_adresse,$ldap_port);
    if($ds){
        $r=@ldap_bind ( $ds ); // Bind anonyme
        if($r){
            // La requête est adaptée à un serveur SE3...
            $result=@ldap_read($ds,"cn=$grp,ou=Groups,$ldap_base","cn=*",$members_attr);
            // Peut-être faudrait-il dans le $tab_grp_autorise mettre des chaines 'cn=$grp,ou=Groups'
            if($result){
                $info=@ldap_get_entries($ds,$result);
                if($info["count"]==1){
                    $init=0;
                    for($loop=0;$loop<$info[0]["memberuid"]["count"];$loop++){
                        if($info[0]["memberuid"][$loop]==$uid){
                            $est_membre="oui";
                        }
                    }
                }
                @ldap_free_result($result);
            }
        }
        else{
            return false;
        }
        @ldap_close($ds);
    }
    else{
        return false;
    }
    return $est_membre;
}

/*
Arguments :
$id_entry : identifiant de la réservation
$login_moderateur : identifiant du modérateur
$motivation_moderation : texte facultatif

Insère dans la table agt_loc_moderate les valeurs de agt_loc dont l'identifiant est $id_entry
*/
function  grr_backup($id_entry,$login_moderateur,$motivation_moderation) {
    $sql = "SELECT * FROM agt_loc WHERE id='".$id_entry."'";
    $res = grr_sql_query($sql);
    if (! $res)  return FALSE;
    $row = grr_sql_row_keyed($res, 0);

    $req = "insert into agt_loc_moderate set
    id = '".$row['id']."',
    start_time = '".$row['start_time']."',
    end_time  = '".$row['end_time']."',
    entry_type  = '".$row['entry_type']."',
    room_id = '".$row['room_id']."',
    timestamp = '".$row['timestamp']."',
    create_by = '".$row['create_by']."',
    name = '".protect_data_sql($row['name'])."',
    type = '".$row['type']."',
    description = '".protect_data_sql($row['description'])."',
    statut_entry = '".$row['statut_entry']."',
    motivation_moderation = '".protect_data_sql(strip_tags($motivation_moderation))."',
    login_moderateur = '".protect_data_sql($login_moderateur)."'";

    $res = grr_sql_query($req);
    if (! $res)
        return FALSE;
    else
        return TRUE;
}
/*
Remplace la fonction PHP html_entity_decode(), pour les utilisateurs ayant des versions antérieures à PHP 4.3.0 :
En effet, la fonction html_entity_decode() est disponible a partir de la version 4.3.0 de php.
*/
function html_entity_decode_all_version ($string)
{
   global $use_function_html_entity_decode;
   if (isset($use_function_html_entity_decode) and ($use_function_html_entity_decode == 0)) {
       // Remplace les entités numériques
       $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
       $string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
       // Remplace les entités litérales
       $trans_tbl = get_html_translation_table (HTML_ENTITIES);
       $trans_tbl = array_flip ($trans_tbl);
       return strtr ($string, $trans_tbl);
   } else
       return html_entity_decode($string);
}

function verif_version() {

    global $version_grr, $version_grr_RC;
    $_version_grr = $version_grr; // On préserve la variable $version_grr
    $_version_grr_RC = $version_grr_RC;// On préserve la variable $version_grr_RC
    $version_old = getSettingValue("version");
    $versionRC_old = getSettingValue("versionRC");

/*
    // par mohan $CurrentVertion dans config.php
	if ($CurrentVertion >  $version_old) 
		return false;
	else	
		return true;
	exit;
	
*/	
    // S'il s'agit de la version stable, on positionne malgré tout $versionRC_old = 9 pour la cohérence du test
    if ($versionRC_old == "") $versionRC_old = 9;
    // S'il s'agit de la version stable, on positionne malgré tout $versionRC_old = 9 pour la cohérence du test
    if ($_version_grr_RC == "") $_version_grr_RC = 9;
    if (
        ($version_old =='')
        or ($_version_grr > $version_old)
        or (($_version_grr == $version_old) and ($_version_grr_RC > $versionRC_old))
       )
        return true;
    else
        return false;
}

// Affiche le numéro de version de GRR selon les cas sous la forme "GRR x.x.x_RCx" ou "GRR x.x.x
function affiche_version() {
    global $version_grr, $version_grr_RC, $sous_version_grr;
    if (getSettingValue("versionRC")!="")
        return "GRR ".getSettingValue("version")."_RC".getSettingValue("versionRC").$sous_version_grr;
    else
        return "GRR ".getSettingValue("version").$sous_version_grr;
}

/*


*/
function affiche_date($x) {
 $j   = date("d",$x);
$m = date("m",$x);
$a  = date("Y",$x);
$h  = date("H",$x);
$mi = date("i",$x);
$s = date("s",$x);
//$result = $h.":".$mi.":".$s.": le ".$j."/".$m."/".$a;
$result = $j."/".$m."/".$a;
return $result;
}


# L'heure d'été commence le dernier dimanche de mars * et se termine le dernier dimanche d'octobre
# Passage à l'heure d'hiver : -1h, le changement s'effectue à 3h
# Passage à l'heure d'été : +1h, le changement s'effectue à 2h
# Si type = hiver => La fonction retourne la date du jour de passage à l'heure d'hiver
# Si type = ete =>  La fonction retourne la date du jour de passage à l'heure d'été
function heure_ete_hiver($type, $annee, $heure)
 {
    if ($type == "ete")
       $debut = mktime($heure,0,0,03,31,$annee); // 31-03-$annee
    else
       $debut = mktime($heure,0,0,10,31,$annee); // 31-10-$annee

    while (date("D", $debut ) !='Sun')
    {
       $debut = mktime($heure,0,0,date("m",$debut), date("d",$debut)-1, date("Y",$debut)); //On retire 1 jour par rapport à la date examinée
    }
    return $debut;
}

# Remove backslash-escape quoting if PHP is configured to do it with
# magic_quotes_gpc. Use this whenever you need the actual value of a GET/POST
# form parameter (which might have special characters) regardless of PHP's
# magic_quotes_gpc setting.
function unslashes($s)
{
    if (get_magic_quotes_gpc()) return stripslashes($s);
    else return $s;
}

// Corrige les caracteres degoutants utilises par les Windozeries
function corriger_caracteres($texte) {
    // 145,146,180 = simple quote ; 147,148 = double quote ; 150,151 = tiret long
    $texte = strtr($texte, chr(145).chr(146).chr(180).chr(147).chr(148).chr(150).chr(151), "'''".'""--');
    return ereg_replace( chr(133), "...", $texte );
}

// Traite les données avant insertion dans une requête SQL
function protect_data_sql($_value) {
    global $use_function_mysql_real_escape_string;
    if (get_magic_quotes_gpc()) $_value = stripslashes($_value);
    if (!is_numeric($_value)) {
        if (isset($use_function_mysql_real_escape_string) and ($use_function_mysql_real_escape_string==0))
             $_value = mysql_escape_string($_value);
        else
             $_value = mysql_real_escape_string($_value);
    }
    return $_value;
}

// Traite les données envoyées par la methode GET de la variable $_GET["page"]
function verif_page() {
    if (isset($_GET["page"]))
        if (($_GET["page"] == "day") or ($_GET["page"] == "week") 
        or ($_GET["page"] == "month") or ($_GET["page"] == "week_all")
        or ($_GET["page"] == "month_all"))
            return $_GET["page"];
        else
            return "day";
    else
       return "day";
}

function page_accueil($param='no') {
   // Definition de $defaultroom
   if (isset($_SESSION['default_room']) and ($_SESSION['default_room'] != '0')) {
      $defaultroom = $_SESSION['default_room'];
   } else {
      $defaultroom = getSettingValue("default_room");
   }

   // Definition de $defaultarea
   if (isset($_SESSION['default_area']) and ($_SESSION['default_area'] !='0')) {
      $defaultarea = $_SESSION['default_area'];
   } else if (getSettingValue("default_area") != "") {
      $defaultarea = getSettingValue("default_area");
   } else
      $defaultarea = get_default_area();


   // Calcul de $page_accueil
   if ($defaultarea == -1) {
      $page_accueil="day.php";
      if ($param=='yes') $page_accueil=$page_accueil."?";
   } else if ($defaultroom == -1) {
      $area_accueil = $defaultarea;
      $page_accueil="day.php?area=$area_accueil";
      if ($param=='yes') $page_accueil=$page_accueil."&amp;";
   } else if ($defaultroom == -2) {
      $area_accueil = $defaultarea;
      $page_accueil="week_all.php?area=$area_accueil";
      if ($param=='yes') $page_accueil=$page_accueil."&amp;";
   } else if ($defaultroom == -3) {
      $area_accueil = $defaultarea;
      $page_accueil="month_all.php?area=$area_accueil";
      if ($param=='yes') $page_accueil=$page_accueil."&amp;";
   } else if ($defaultroom == -4) {
      $area_accueil = $defaultarea;
      $page_accueil="month_all2.php?area=$area_accueil";
      if ($param=='yes') $page_accueil=$page_accueil."&amp;";
   } else {
      $area_accueil = $defaultarea;
      $room_accueil = $defaultroom;
      $page_accueil="week.php?area=$area_accueil&amp;room=$room_accueil";
      if ($param=='yes') $page_accueil=$page_accueil."&amp;";
   }
   return $page_accueil;
}

function begin_page($title,$page="with_session")
{
	$charset_html = "utf-8";
header("Content-Type: text/html;charset=". ((isset($unicode_encoding) and ($unicode_encoding==1)) ? "utf-8" : $charset_html)); header("Pragma: no-cache");                          // HTTP 1.0
if ($page=="with_session")
  {
  
    if (isset($_SESSION['default_style'])) $sheetcss = "./commun/style/themes/".$_SESSION['default_style']."/css/style.css";
    else $sheetcss="./commun/style/themes/default/css/style.css";

    if (isset($_GET['default_language']))
      {
// Suppression des trois lignes suivantes afin de corriger le bug suivant :
// Lorqu'un administrateur modifiait le réglage de la langue par défaut, son réglage personnel était écrasé et prenait la valeur du réglage par défaut.
//        $sql = grr_sql_command("UPDATE agt_utilisateurs SET
//        default_language = '" . $_GET['default_language']."'
//        WHERE login='". $_SESSION['login']."'");
        $_SESSION['default_language'] = $_GET['default_language'];
        if (isset($_SESSION['chemin_retour']) and ($_SESSION['chemin_retour'] != ''))
            header("Location: ".$_SESSION['chemin_retour']);
        else
            header("Location: ".traite_grr_url());
        die();
      }
  }
 else {
     if (getSettingValue("default_css")) $sheetcss = "./commun/style/themes/".getSettingValue("default_css")."/css/style.css";
     else $sheetcss="./commun/style/themes/default/css/style.css";
   }

 global $vocab, $charset_html, $unicode_encoding, $clock_file;
 /*
// Essai de prise en compte de l'environnement lemonldap
$page = "/grr/test.php";
$host = "127.0.0.1";  // ou bien www.tonsite.fr
$fp = pfsockopen($host, 80, $errno, $errstr);
if (!$fp) {
   echo "$errstr ($errno)<br/>\n";
   echo $fp;
   die();
} else {
   $header = "GET $page  HTTP/1.1\r\n";
   $header .=  "Host: $host\r\n";
   $header .= "Authorization: Basic ".base64_encode("delineau")."\r\n";
   $header .= "Connection: close\r\n\r\n";
   fwrite($fp, $header);
   $result = "";
   while (!feof($fp)) {
       $result .= fgets($fp, 128);
   }
   echo $result;
   fclose($fp);
}
//header("Authorization: Basic ".base64_encode('user:mot_de_passe')."=");
*/
 
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
//$a='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
//$a='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
$a='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
//$a='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

$a.='<html>
<head>
 <meta http-equiv="X-UA-Compatible" content="IE=8" /> 
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />  
<link rel="stylesheet" href="'.$sheetcss.'" type="text/css" />
<link rel="stylesheet" href="./commun/style/div_top_fix.css" type="text/css" />';
$a.='<link href="./commun/style/admin_grr.css" rel="stylesheet" type="text/css" />';
// Pour le format imprimable, on impose un fond de page blanc
if ((isset($_GET['pview'])) and ($_GET['pview'] == 1))
    $a .= '<link rel="stylesheet" href="./commun/style/themes/print/css/style.css" type="text/css" />';
/*
if (function_exists("getSettingValue")) {
    $a.='<LINK REL="SHORTCUT ICON" href="'.traite_grr_url().'./commun/images/favicon.ico" />';
}
*/
$a.='<style type="text/css">div#fixe   { position: fixed; bottom: 5%; right: 5%;}</style>';
$a.='<link rel="SHORTCUT ICON" href="./commun/images/favicon.ico" />';
$a .="\n<title>$title</title>";
$a .="\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=";
if ($unicode_encoding)
    $a .= "utf-8";
else
    $a .= $charset_html;
$a .= "\" />";
$a .="\n<meta NAME=\"Robots\" content=\"noindex\" />";

$a .="\n</head>\n
<body text=\"black\" link=\"#5B69A6\" vlink=\"#5B69A6\" alink=\"red\">\n";

if (@file_exists($clock_file)) {
   $a .='<script type="text/javascript" LANGUAGE="JavaScript" SRC="'.$clock_file.'"></SCRIPT>';
}
# show a warning if this is using a low version of php
if (substr(phpversion(), 0, 1) == 3)  $a .=get_vocab('not_php3');
return $a;
}
//============================================
// HEADER 
//============================================
function simple_header($day, $month, $year, $area, $type="with_session", $page="no_admin")
{
   global $vocab, $search_str, $grrSettings, $session_statut, $clock_file, $is_authentified_lcs, $desactive_VerifNomPrenomUser, $grr_script_name;
   global $use_prototype, $use_tooltip_js, $desactive_bandeau_sup;

   if (!($desactive_VerifNomPrenomUser)) $desactive_VerifNomPrenomUser = 'n';
   // On vérifie que les noms et prénoms ne sont pas vides
   VerifNomPrenomUser($type);
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

if (isset($use_prototype))
    echo "<script type=\"text/javascript\" src=\"./commun/js/prototype-1.6.0.2.js\"></script>";
if (isset($use_tooltip_js))
    echo "<script type=\"text/javascript\" src=\"./commun/js/tooltip.js\"></script>";
echo getSettingValue('message_accueil');
  }
}

//=======================================
// FIN HEADER
//========================================
//============================================
// HEADER 
//============================================
function print_header($day, $month, $year, $area, $type="with_session", $page="no_admin")
{
   global $vocab, $search_str, $grrSettings, $session_statut, $clock_file, $is_authentified_lcs, $desactive_VerifNomPrenomUser, $grr_script_name;
   global $use_prototype, $use_tooltip_js, $desactive_bandeau_sup;

	$user_level = authGetUserLevel(getUserName(),-1,'area');
   if (!($desactive_VerifNomPrenomUser)) $desactive_VerifNomPrenomUser = 'n';
   // On vérifie que les noms et prénoms ne sont pas vides
   VerifNomPrenomUser($type);
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
		function popup(lien){
		    mywindow=open(lien,'myfind','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
		    mywindow.location.href = lien;
		  //  if (mywindow.opener == null) mywindow.opener = self;
		     if(mywindow.window.focus){mywindow.window.focus();}        
	    
		}		

		function popup_syncro_Gilda(lien){
			var listes=lien.split("=");
			var dt = new Date();
			var d =dt.getDate(); 
			var LancePopup=false;
			if (d < 10)
				d='0'+d;

			//if today	demande de confirmations
			if (d ==listes[4])
			{	
				var msg="Le synchronisation avec GILDA est automatique tous heures entre 8 heure a 18 heure pour les patients d'aujourdhui. \nPour effectuer  une synchronisation d'une journée spécifique veuillez sélectionnée le jour dans le calendrier puis lancer la synchronisation \n«OK» pour lancer le synchronisation  ou  «Annuler» pour sortir dici." ;
				if(confirm(msg))
					LancePopup=true;
				else
		   		LancePopup=false;
		  }
		  else
		   		LancePopup=true;


		  
		  if(LancePopup)
		  {
		  	mywindow=open(lien,'myfind','resizable=yes,width=725,height=400,left=500,top=200,status=yes,scrollbars=yes');
			  mywindow.location.href = lien;
			  //  if (mywindow.opener == null) mywindow.opener = self;
			  if(mywindow.window.focus){mywindow.window.focus();}    
		  	
		  }
		  
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

          echo "&nbsp;<A HREF=\"".page_accueil($param)."day=$day&amp;year=$year&amp;month=$month\">".
       	"<img src=\"./commun/images/home.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Accueil\" title=\"Accueil\"/>  </A> &nbsp;";
          if ($type == 'no_session') {

//            echo "<br />&nbsp;<a href='login.php'>".get_vocab("connect")."</a>";

				// (UT1-LC 05/2008) distinction connexion CAS-LDAP / locale
				if ((getSettingValue('sso_statut') == 'cas_visiteur') or (getSettingValue('sso_statut') == 'cas_utilisateur'))
					{
					echo " &nbsp;<a href='index.php'>".get_vocab("authentification")."</a>";
					echo " &nbsp;<small><i><a href='login.php'>".
					"<img src=\"./commun/images/connexion.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Se connecter\" title=\"Se connecter\"/> "
					."</a></i></small>";
					}
				else
					{
					echo " &nbsp;<a href='login.php'>".
					"<img src=\"./commun/images/connexion.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Se connecter\" title=\"Se connecter\"/> "
					."</a>";
					}


          } else {
		          echo "<A HREF='admin_accueil.php?day=$day&amp;month=$month&amp;year=$year'>".
		       	"<img src=\"./commun/images/admin.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Administration\" title=\"Administration\"/>  </A> &nbsp;";          
          	
			 		echo "  <A href=\"#\"  onClick=\"popup('recherche_pat.php')\" >
			 		<img src=\"./commun/images/patient.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Localisation patients\" title=\"Localisation patients\"/> 
					</a> &nbsp;";                   	
			 		echo "  <A href=\"#\"  onClick=\"popup('premier_place_dispo.php')\" >
			 		<img src=\"./commun/images/first.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Premiere place disponible\" title=\"Premiere place disponible\"/> 
			 		</a> &nbsp;";                   	
			    	      			                  	
			      if ((verif_access_search(getUserName())) and ($_SESSION['login']=="ADMIN"  or $_SESSION['login']=="3237038")   ) {
			      		echo "  <A style='cursor:pointer;' onClick=\"popup_syncro_Gilda('update_agt.php')\" >
			                 	<img src=\"./commun/images/syncronisation.gif\" width=\"20\" height=\"20\" border=\"0\" alt=\"Synchronise Gilda\" title=\"Synchronise avec GILDA\"/>  </A> &nbsp; ";          
			      }			
			     
			     //REPORTS 
			     if($user_level >= 1) { 
			      echo "<a href=\"report.php\">
				 							<img src=\"./commun/images/reports-icon.png\" 
				 							width=\"25\" height=\"25\" border=\"0\" 
				 							alt=\"Recherche sejours\" title=\"Recherche sejours\"/> 
									</a> &nbsp;"; 
			     
					echo "  <a href=\"situation_lits.php\">
						<img src=\"./commun/images/bed-hospital.png\" width=\"20\" height=\"20\" border=\"0\" alt=\"Situation des lits\" title=\"Situation des lits\"/> 
						</a> &nbsp;"; 
					}
	
             if (!((getSettingValue("sso_statut") == 'lcs') and ($_SESSION['source_login']=='ext') and ($is_authentified_lcs == "yes")))
               if (getSettingValue("authentification_obli") == 1) {
                 echo "<a href=\"./logout.php?auto=0\" > 
                 <img src=\"./commun/images/deconnexion.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Se déconnecter\" title=\"Se déconnecter\"/> </a>&nbsp;";

               } else {
                 echo "<a href=\"./logout.php?auto=0&amp;authentif_obli=no\" > 
                 	<img src=\"./commun/images/deconnexion.jpg\" width=\"20\" height=\"20\" border=\"0\" alt=\"Se déconnecter\" title=\"Se déconnecter\"/> </a>&nbsp;";
               }
               

               
               
	             if ((getSettingValue('sso_statut') == 'lasso_visiteur')
		           or (getSettingValue('sso_statut') == 'lasso_utilisateur')) {
		               echo "<br />";
		               if ($_SESSION['lasso_nameid'] == NULL)
		                   echo "<a href=\"lasso/federate.php\">".get_vocab('lasso_federate_this_account')."</a>";
		               else
		                   echo "<a href=\"lasso/defederate.php\">".get_vocab('lasso_defederate_this_account')."</a>";
	            }

			       		
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

             //}
             

          }
         //========================================================================
         // Modifier default area ajouter une colonne dans agt_service is_default ?
         //======================================================================== 
         if (isset($_SESSION['default_area']) and ($_SESSION['default_area'] !='0')) {
		  $defaultarea = $_SESSION['default_area'];
	   } else if (getSettingValue("default_area") != "") {
		  $defaultarea = getSettingValue("default_area");
	   } else
		  $defaultarea = get_default_area();
		//echo $default_area;  
      ?>
     </TD>
     <?php
      echo "<TD CLASS='banner' align='center'><a href='day.php?area=$defaultarea' title=\"".htmlspecialchars(get_vocab("page_accueil"))."\"><img src='./commun/images/logo.gif' width='350' height='55' border='0'/></a>"; 
       ?>
       </TD>
       <TD CLASS="banner" ALIGN="right">
 			<img src="./commun/images/logo_hopital.gif" width="200" height="50" />
       </TD>
     <?php
     if ($page=="no_admin") {
     	   //<TD CLASS="banner"  ALIGN=center>
     ?>
         
           <form name="myform" action="" method="get" onSubmit="return OnSubmitForm();">
           <?php
           if (!empty($area)) echo "<INPUT TYPE=\"hidden\" name=\"area\" value=\"$area\" />"
           ?>

           </form>
           <form name="myform2" action="" method="get" onSubmit="return OnSubmitForm();">
           <?php
           if (!empty($area)) echo "<INPUT TYPE=\"hidden\" name=\"area\" value=\"$area\" />"
           ?>
           </FORM>

         <?php
         //</TD>         
     }
     if ($type == "with_session") {
          if ((authGetUserLevel(getUserName(),-1,'area') >= 4) or (authGetUserLevel(getUserName(),-1,'user') == 1))  {
/*          	
           echo "<TD CLASS=\"banner\" ALIGN=right>";
 			echo "<img src=\"./commun/images/logo-Saint-Louis-2004-2.gif\" width=\"200\" height=\"65\" />";

				
           if(authGetUserLevel(getUserName(),-1,'area') >= 5)  {
              echo "<br /><form action=\"admin_save_mysql.php\" method=\"get\" name=sav>\n
              <input type=\"submit\" value=\"".get_vocab("submit_backup")."\" />\n
              </form>";
              how_many_connected();
           }
  
           echo "\n</TD>";
				*/                    
      }
     }
      ?>
         
          
      <?php 
      

      /*  par mohan script horologe arreté
 <TD CLASS="banner" ALIGN=left>      
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

         </TD>
*/

          ?>

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
}

//=======================================
// FIN HEADER
//========================================
/*
  Vérifie que les noms et prénoms d'un utilisateur ne sont pas vides
  Dans le cas contraire, redirige vers la page de "gérer mon compte"
*/
function VerifNomPrenomUser($type) {
    global $desactive_VerifNomPrenomUser; // ne pas prendre en compte la page my_account.php
    if (($type == "with_session") and ($desactive_VerifNomPrenomUser!='y') and (IsAllowedToModifyProfil())) {
        $test = grr_sql_query1("select login from agt_utilisateurs where (login = '".getUserName()."' and (nom='' or prenom = ''))");
        if ($test != -1) {
            header("Location:my_account.php");
            die();
        }
    }


}

/*
  Vérifie que l'utilisateur est autorisé à changer ses noms et prénoms
  Renvoie True (peut changer ses noms et prénoms) ou False (ne peut pas)
*/
function IsAllowedToModifyProfil() {
    // l'utilisateur connecté n'a pas le niveau suffisant pour modifier son compte
    if (authGetUserLevel(getUserName(),-1) < getSettingValue("allow_users_modify_profil"))
        return FALSE;
    else if (getSettingValue("sso_statut") == "lcs") {
        // ou bien on est dans un environnement LCS et l'utilisateur n'est pas un utilisateur local
        $source = grr_sql_query1("select source from agt_utilisateurs where login = '".getUserName()."'");
        if ($source == "ext")
            return FALSE;
        else
            return TRUE;
    } else
        return TRUE;
}
/*
  Vérifie que l'utilisateur est autorisé à changer son emai
  Renvoie True (peut changer son email) ou False (ne peut pas)
*/
function IsAllowedToModifyEmail() {
    // l'utilisateur connecté n'a pas le niveau suffisant pour modifier son compte
    if (authGetUserLevel(getUserName(),-1) < getSettingValue("allow_users_modify_email"))
        return FALSE;
    else if (getSettingValue("sso_statut") == "lcs") {
        // ou bien on est dans un environnement LCS et l'utilisateur n'est pas un utilisateur local
        $source = grr_sql_query1("select source from agt_utilisateurs where login = '".getUserName()."'");
        if ($source == "ext")
            return FALSE;
        else
            return TRUE;
    } else
        return TRUE;
}

/*
  Vérifie que l'utilisateur est autorisé à changer son mot de passe
  Renvoie True (peut changer) ou False (ne peut pas)
*/
function IsAllowedToModifyMdp() {
    // l'utilisateur connecté n'a pas le niveau suffisant pour modifier son compte
    if (authGetUserLevel(getUserName(),-1) < getSettingValue("allow_users_modify_mdp"))
        return FALSE;
    else if ((getSettingValue("sso_statut") != "") or  (getSettingValue("ldap_statut") != '')) {
        // ou bien on est dans un environnement SSO ou ldap et l'utilisateur n'est pas un utilisateur local
        $source = grr_sql_query1("select source from agt_utilisateurs where login = '".getUserName()."'");
        if ($source == "ext")
            return FALSE;
        else
            return TRUE;
    } else
        return TRUE;
}

// Transforme $dur en un nombre entier
// $dur : durée
// $units : unité
function toTimeString(&$dur, &$units)
{
    global $vocab;
    if($dur >= 60)
    {
        $dur = $dur/60;

        if($dur >= 60)
        {
            $dur /= 60;

            if(($dur >= 24) && ($dur % 24 == 0))
            {
                $dur /= 24;

                if(($dur >= 7) && ($dur % 7 == 0))
                {
                    $dur /= 7;

                    if(($dur >= 52) && ($dur % 52 == 0))
                    {
                        $dur  /= 52;
                        $units = get_vocab("years");
                    }
                    else
                        $units = get_vocab("weeks");
                }
                else
                    $units = get_vocab("days");
            }
            else
                $units = get_vocab("hours");

        }
        else
            $units = get_vocab("minutes");
    }
    else
        $units = get_vocab("seconds");
}

// Transforme $dur en un nombre entier
// $dur : durée
// $units : unité
function toPeriodString($start_period, &$dur, &$units)
{
    global $enable_periods, $periods_name, $vocab;
    $max_periods = count($periods_name);
    $dur /= 60;
        if( $dur >= $max_periods || $start_period == 0 )
        {
                if( $start_period == 0 && $dur == $max_periods )
                {
                        $units = get_vocab("days");
                        $dur = 1;
                        return;
                }

                $dur /= 60;
                if(($dur >= 24) && is_int($dur))
                {
                    $dur /= 24;
            $units = get_vocab("days");
                        return;
                }
                else
                {
            $dur *= 60;
                        $dur = ($dur % $max_periods) + floor( $dur/(24*60) ) * $max_periods;
                        $units = get_vocab("periods");
                        return;
        }
        }
        else
        $units = get_vocab("periods");
}




function genDateSelectorForm($prefix, $day, $month, $year,$option)
{
    global $nb_year_calendar;
    $selector_data = "";

    // Compatibilité avec version GRR < 1.9
    if (!isset($nb_year_calendar)) $nb_year_calendar = 5;

    if(($day   == 0) and ($day != "")) $day = date("d");
    if($month == 0) $month = date("m");
    if($year  == 0) $year = date("Y");

    if ($day != "") {
     $selector_data .= "<SELECT NAME=\"${prefix}day\" id=\"${prefix}c_day\">\n";
     for($i = 1; $i <= 31; $i++)
      $selector_data .= "<option" . ($i == $day ? " SELECTED" : "") . ">$i</option>\n";

     $selector_data .= "</SELECT>";
    }
    $selector_data .= "<SELECT NAME=\"${prefix}month\">\n";

    for($i = 1; $i <= 12; $i++)
    {
        $m = utf8_strftime("%b", mktime(0, 0, 0, $i, 1, $year));

        $selector_data .=  "<option VALUE=\"$i\"" . ($i == $month ? " SELECTED" : "") . ">$m</option>\n";
    }

    $selector_data .=  "</SELECT>";
    $selector_data .=  "<SELECT NAME=\"${prefix}year\">\n";

    $min = strftime("%Y", getSettingValue("begin_bookings"));
    if ($option == "more_years") $min = date("Y") - $nb_year_calendar;

    $max = strftime("%Y", getSettingValue("end_bookings"));
    if ($option == "more_years") $max = date("Y") + $nb_year_calendar;

    for($i = $min; $i <= $max; $i++)
      $selector_data .= "<option value=\"".$i."\"" . ($i == $year ? " SELECTED" : "") . ">$i</option>\n";

    $selector_data .= "</SELECT>";

    return $selector_data;
}


function genDateSelector($prefix, $day, $month, $year,$option)
{
  echo genDateSelectorForm($prefix, $day, $month, $year,$option);
}




# Error handler - this is used to display serious errors such as database
# errors without sending incomplete HTML pages. This is only used for
# errors which "should never happen", not those caused by bad inputs.
# If $need_header!=0 output the top of the page too, else assume the
# caller did that. Alway outputs the bottom of the page and exits.
function fatal_error($need_header, $message)
{
    global $vocab;
    if ($need_header) print_header(0, 0, 0, 0);
    echo $message;
    include "trailer.inc.php";
    exit;
}


# Retourne le domaine par défaut; Utilisé si aucun domaine n'a été défini.
function get_default_area()
{
    if (OPTION_IP_ADR==1) {
        // Affichage d'un domaine par defaut en fonction de l'adresse IP de la machine cliente
        $res = grr_sql_query("SELECT id FROM agt_service WHERE ip_adr='".protect_data_sql($_SERVER['REMOTE_ADDR'])."' ORDER BY service_name, access, order_display");
        if ($res && grr_sql_count($res)>0 ) {
            $row = grr_sql_row($res, 0);
            return $row[0];

        }
    }
    if(authGetUserLevel(getUserName(),-1) >= 5)
        // si l'admin est connecté, on cherche le premier domaine venu
        $res = grr_sql_query("SELECT id FROM agt_service ORDER BY  service_name, access, order_display");
    else
        // s'il ne s'agit pas de l'admin, on cherche le premier domaine à accès non restreint
        $res = grr_sql_query("SELECT id FROM agt_service where access!='r' ORDER BY service_name, access, order_display ");
    if ($res && grr_sql_count($res)>0 ) {
        $row = grr_sql_row($res, 0);
        return $row[0];
    } else {
        // On cherche le premier domaine à accès restreint
        $res = grr_sql_query("select id from agt_service, agt_j_user_area where
        agt_service.id=agt_j_user_area.id_area and
        login='" . getUserName() . "'
        ORDER BY service_name,order_display ");
        if ($res && grr_sql_count($res)>0 ) {
            $row = grr_sql_row($res, 0);
            return $row[0];
        }
    else
        return 0;
    }

}

# Get the local day name based on language. Note 2000-01-02 is a Sunday.
function day_name($daynumber)
{
    return utf8_strftime("%A", mktime(0,0,0,1,2+$daynumber,2000));
}

function hour_min_format()
{
        global $twentyfourhour_format;
        if ($twentyfourhour_format)
    {
              return "H:i";
    }
    else
    {
        return "h:ia";
    }
}

/*
Fonction utilisée dans le cas où les créneaux de réservation sont basés sur des intitulés pré-définis :
Formatage de la date de début ou de fin de réservation.
Dans le cas du début de réservation on a $mod_time=0
Dans le cas de la fin de réservation on a $mod_time=-1
*/
function period_date_string($t, $mod_time=0)
{
    global $periods_name, $dformat;
    $time = getdate($t);
    $p_num = $time["minutes"] + $mod_time;
    if( $p_num < 0 ) {
        // fin de réservation : cas $time["minutes"] = 0. il faut afficher le dernier créneau de la journée précédente
        $t = $t - 60*60*24;
        $p_num = count($periods_name) - $p_num;
    }
    if( $p_num >= count($periods_name) - 1 ) $p_num = count($periods_name) - 1;
    return array($p_num, $periods_name[$p_num] . utf8_strftime(", ".$dformat,$t));
}

/*
Fonction utilisée dans le cas où les créneaux de réservation sont basés sur des intitulés pré-définis :
Formatage des périodes de début ou de fin de réservation.
Dans le cas du début de réservation on a $mod_time=0
Dans le cas de la fin de réservation on a $mod_time=-1
*/
function period_time_string($t, $mod_time=0)
{
    global $periods_name;
    $time = getdate($t);
    $p_num = $time["minutes"] + $mod_time;
    if( $p_num < 0 ) $p_num = 0;
    if( $p_num >= count($periods_name) - 1 ) $p_num = count($periods_name) - 1;
    return $periods_name[$p_num];
}


function time_date_string($t,$dformat)
{
        global $twentyfourhour_format;
        # This bit's necessary, because it seems %p in strftime format
        # strings doesn't work
        $ampm = date("a",$t);
        if ($twentyfourhour_format)
    {
              return utf8_strftime("%H:%M:%S - ".$dformat,$t);
    }
    else
    {
            return utf8_strftime("%I:%M:%S$ampm - ".$dformat,$t);
    }
}

function time_date_string_jma($t,$dformat)
{
        global $twentyfourhour_format;
        # This bit's necessary, because it seems %p in strftime format
        # strings doesn't work
        $ampm = date("a",$t);
        if ($twentyfourhour_format)
    {
              return utf8_strftime($dformat,$t);
    }
    else
    {
            return utf8_strftime($dformat,$t);
    }
}


// Renvoie une balise span avec un style backgrounf-color correspondant au type de  la réservation
function span_bgground($colclass)
{
    global $tab_couleur;
    static $ecolors;
    $num_couleur = grr_sql_query1("select couleur from agt_type_area where type_letter='".$colclass."'");
    echo "<span style=\"background-color: ".$tab_couleur[$num_couleur]."; background-image: none; background-repeat: repeat; background-attachment: scroll;\">";
}

// Renvoie une balise span avec un style backgrounf-color correspondant au type de  la réservation
function get_color_class($colclass)
{
    global $tab_couleur;
    static $ecolors;
    $num_couleur = grr_sql_query1("select couleur from agt_type_area where type_letter='".$colclass."'");
    return $tab_couleur[$num_couleur];
    
}


# Output a start table cell tag <td> with color class and fallback color.
function tdcell($colclass, $width='')
{
    if ($width!="") $temp = " style=\"width:".$width."%;\" "; else $temp = "";
    global $tab_couleur;
    static $ecolors;
    if (($colclass >= "A") and ($colclass <= "Z")) {
        $num_couleur = grr_sql_query1("select couleur from agt_type_area where type_letter='".$colclass."'");
        echo "<td bgcolor=\"".$tab_couleur[$num_couleur]."\" ".$temp.">";
    } else
        echo "<td class=\"$colclass\" ".$temp.">";
}
// Paul Force
function tdcell_rowspan($colclass , $step)
{
    global $tab_couleur;
    static $ecolors;
    if (($colclass >= "A") and ($colclass <= "Z")) {
        $num_couleur = grr_sql_query1("select couleur from agt_type_area where type_letter='".$colclass."'");
        echo "<td rowspan=\"$step\" bgcolor=\"".$tab_couleur[$num_couleur]."\">";
    } else
        echo "<td rowspan=\"$step\" td class=\"".$colclass."\">";
}

/*if ($row_alert!="")
    {
		$step = $step-$row_alert;
		echo "<td rowspan=$row_alert bgcolor=\"red\">";
	}
	else
	{}
	*/
# Display the entry-type color key. This has up to 2 rows, up to 10 columns.
function show_colour_key($service_id)
{
    echo "<table border=0><tr>\n";
    $nct = 0;
    $sql = "SELECT DISTINCT t.id, t.type_name, t.type_letter FROM agt_type_area t
    LEFT JOIN agt_j_type_area j on j.id_type=t.id
    WHERE (j.id_area  IS NULL or j.id_area != '".$service_id."')
    ORDER BY t.order_display";
    $res = grr_sql_query($sql);
    if ($res) {
    for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
        // La requête sql précédente laisse passer les cas où un type est non valide dans le domaine concerné ET au moins dans un autre domaine, d'où le test suivant
        $test = grr_sql_query1("select id_type from agt_j_type_area where id_type = '".$row[0]."' and id_area='".$service_id."'");
        if ($test == -1) {
            $id_type        = $row[0];
            $type_name        = $row[1];
            $type_letter          = $row[2];
            if (++$nct > 10)
                {
                    $nct = 0;
                    echo "</tr><tr>";
                }
            tdcell($type_letter);
            echo "$type_name</td>\n";
        }
    }
    echo "</tr></table>\n";
    }
}




# Round time down to the nearest resolution
function round_t_down($t, $resolution, $am7)
{
        return (int)$t - (int)abs(((int)$t-(int)$am7)
                  % $resolution);
}

# Round time up to the nearest resolution
function round_t_up($t, $resolution, $am7)
{
    if (($t-$am7) % $resolution != 0)
    {
        return $t + $resolution - abs(((int)$t-(int)
                           $am7) % $resolution);
    }
    else
    {
        return $t;
    }
}

# generates some html that can be used to select which area should be
# displayed.
function make_area_select_html( $link, $current, $year, $month, $day, $user)
{
    global $vocab;
    $out_html .= "<form name=\"area\" action=\"".$_SERVER['PHP_SELF']."\">\n";
    $out_html .= "<b> ".get_vocab("areas")."</b>  ";
    $out_html .= "<select name=\"area\"";
    $out_html .= " onChange=\"area_go()\"";
    $out_html .= ">\n";

    $sql = "select id, service_name from agt_service where etat='n' order by service_name";
       $res = grr_sql_query($sql);
       if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
       {
        $selected = ($row[0] == $current) ? "selected" : "";
        $link2 = "$link?year=$year&amp;month=$month&amp;day=$day&amp;area=$row[0]";
        if (authUserAccesArea($user,$row[0])==1) {
            $out_html .= "<option $selected value=\"$link2\">" . htmlspecialchars($row[1])."</option>\n";
        }
       }
    $out_html .= "</select>
       <SCRIPT type=\"text/javascript\" language=\"JavaScript\">
       <!--
       function area_go()
        {
        box = document.forms[\"area\"].area;
        destination = box.options[box.selectedIndex].value;
        if (destination) location.href = destination;
        }
        // -->
        </SCRIPT>

        <noscript>
        <input type=\"submit\" value=\"Change\" />
        </noscript>";
     $out_html .= "</form>";

    return $out_html;
} # end make_area_select_html

function make_room_select_html( $link, $area, $current, $year, $month, $day )
{
    global $vocab;
    $out_html = "<b><i>".get_vocab('rooms').get_vocab("deux_points")."</i></b><br /><form name=\"room\" action=\"".$_SERVER['PHP_SELF']."\">
                 <select name=\"room\" onChange=\"room_go()\">";

    $out_html .= "<option value=\"".$link."_all.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area\">".get_vocab("all_rooms")."</option>";
    $sql = "select id, room_name, description from agt_room where service_id='".protect_data_sql($area)."' order by order_display,room_name";
       $res = grr_sql_query($sql);
       if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
       {
        if ($row[2]) {$temp = " (".htmlspecialchars($row[2]).")";} else {$temp="";}
        $selected = ($row[0] == $current) ? "selected" : "";
        $link2 = "$link.php?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$row[0]";
        $out_html .= "<option $selected value=\"$link2\">" . htmlspecialchars($row[1].$temp)."</option>";
       }
    $out_html .= "</select>
       <SCRIPT type=\"text/javascript\" language=\"JavaScript\">
       <!--
       function room_go()
        {
        box = document.forms[\"room\"].room;
        destination = box.options[box.selectedIndex].value;
        if (destination) location.href = destination;
        }
        // -->
        </SCRIPT>

        <noscript>
        <input type=\"submit\" value=\"Change\" />
        </noscript>
        </form>";

    return $out_html;
} # end make_room_select_html




function make_area_list_html($link, $current, $year, $month, $day, $user) {
   global $vocab;
   echo "<b><i><span class=\"bground\">".get_vocab("areas")."</span></i></b><br />";
   $sql = "select id, service_name from agt_service where etat='n' order by order_display, service_name";
   $res = grr_sql_query($sql);
   if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
    {
    if (authUserAccesArea($user,$row[0])==1) {
       if ($row[0] == $current)
          {
             echo "<b><span class=\"week\">&gt;&nbsp;<a href=\"".$link."?year=$year&amp;month=$month&amp;day=$day&amp;area=$row[0]\">".htmlspecialchars($row[1])."</a></span></b><br />\n";
          } else {
             echo "<a href=\"".$link."?year=$year&amp;month=$month&amp;day=$day&amp;area=$row[0]\">".htmlspecialchars($row[1])."</a><br />\n";
          }
       }
   }
}
function make_room_list_html($link, $area, $current, $year, $month, $day) {
   global $vocab;
   echo "<b><i><span class=\"bground\">".get_vocab("rooms").get_vocab("deux_points")."</span></i></b><br />";
   $sql = "select id, room_name, description from agt_room where service_id='".protect_data_sql($area)."' order by order_display,room_name";
   $res = grr_sql_query($sql);
	$num_rows= grr_sql_count($res);
	
   if ($res) for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
   {
      if ($row[0] == $current)
      {
        echo "<b><span class=\"week\">&gt;&nbsp;".htmlspecialchars($row[1])."</span></b><br />\n";
      } else {
        echo "<a href=\"".$link."?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$row[0]\">".htmlspecialchars($row[1]). "</a><br />\n";
      }
   }
}

function make_room_list_html_v1($link, $area, $current, $year, $month, $day) {
   global $vocab;
   echo "<b><i><span class=\"bground\">".get_vocab("rooms").get_vocab("deux_points")."</span></i></b><br />";
   $sql = "select id, room_name, description from agt_room where service_id='".protect_data_sql($area)."' order by order_display,room_name";
   $res = grr_sql_query($sql);
	$num_rows= grr_sql_count($res);
	$split=false;
	if ($num_rows > 6){
		$split=true;
		$num_col=$num_rows / 6;
		if(($num_col%6) > 0) $num_col++;
		}
   if ($res){
   	$i=0;
   	while($i<$num_rows){
	    //for ($i = 0; ($row = grr_sql_row($res, $i)); $i++){

	   	for($r=0;$r < $num_col;$r++){
	    		$row = grr_sql_row($res, $i);	   		
		      if ($row[0] == $current){
		        	echo "<b><span class=\"week\">&gt;&nbsp;".htmlspecialchars($row[1])."</span></b>&nbsp;";
		      }else{
		        	echo "<a href=\"".$link."?year=$year&amp;month=$month&amp;day=$day&amp;area=$area&amp;room=$row[0]\">".htmlspecialchars($row[1]). "</a>&nbsp;";
		      }
		      $i++;
		      if($i >$num_rows )break;
	   	}
	   	echo "<br />\n";
	   }
	}
}


function send_mail($id_entry,$action,$dformat,$tab_id_moderes=array())
{ 
    $message_erreur = "";
// $action = 1 -> Création
// $action = 2 -> Modification
// $action = 3 -> Suppression
// $action = 4 -> Suppression automatique
// $action = 5 -> réservation en attente de modération
// $action = 6 -> Résultat d'une décision de modération
// $action = 7 -> Notification d'un retard dans la restitution d'une ressource.
    global $vocab, $grrSettings, $locale, $weekstarts, $enable_periods, $periods_name;
    require_once ("./commun/include/mail.inc.php");
    $m= new my_phpmailer();
    $m->SetLanguage("fr","./phpmailer/language/");
    setlocale(LC_ALL,$locale);
    // Récupération des données concernant la réservation
    $sql = "
    SELECT agt_loc.name,
    agt_loc.description,
    agt_loc.beneficiaire,
    agt_room.room_name,
    agt_service.service_name,
    agt_loc.type,
    agt_loc.room_id,
    agt_loc.repeat_id,
    " . grr_sql_syntax_timestamp_to_unix("agt_loc.timestamp") . ",
    (agt_loc.end_time - agt_loc.start_time),
    agt_loc.start_time,
    agt_loc.end_time,
    agt_room.service_id,
    agt_room.delais_option_reservation,
    agt_loc.option_reservation,
    agt_loc.moderate,
    agt_loc.beneficiaire_ext,
    agt_loc.jours
    FROM agt_loc, agt_room, agt_service
    WHERE agt_loc.room_id = agt_room.id
    AND agt_room.service_id = agt_service.id
    AND agt_loc.id='".protect_data_sql($id_entry)."'
    ";
    $res = grr_sql_query($sql);
    if (! $res) fatal_error(0, grr_sql_error());
    if(grr_sql_count($res) < 1) fatal_error(0, get_vocab('invalid_entry_id'));
    $row = grr_sql_row($res, 0);
    grr_sql_free($res);

    // Récupération des données concernant l'affichage du planning du domaine
/*Renvoie les paramètres d'affichage du domaine
Cas où les créneaux sont basés sur les intitulés :
$enable_periods = y

Dans ce cas chaque créneau correspond à une minute entre 12 h et 12 h 59 (on peut donc définir au plus 59 créneaux !)

$periods_name[] = tableau des intitulés des créneaux
$resolution = 60 : on impose un « pas » de 60 secondes, c'est-à-dire 1 minute
$morningstarts = 12 : début des réservation à 12 h
$eveningends = 12 :heure de fin des réservations : 12 h
$eveningends_minutes : nombre de minutes à ajouter à l'heure $eveningends pour avoir la fin réelle d'une journée. Dans ce cas, il est égal à : (nombre d'intitulé  1)
$weekstarts =
$twentyfourhour_format = $row_[6];

Cas où les créneaux sont basés sur le temps
$enable_periods = n
$resolution
$morningstarts
$eveningends
$eveningends_minutes
$weekstarts
$twentyfourhour_format
*/


    get_planning_area_values($row[12]);

    $breve_description        = removeMailUnicode(htmlspecialchars($row[0]));
    $description  = removeMailUnicode(htmlspecialchars($row[1]));
    $beneficiaire    = htmlspecialchars($row[2]);
    $room_name    = removeMailUnicode(htmlspecialchars($row[3]));
    $service_name    = removeMailUnicode(htmlspecialchars($row[4]));
    $type         = $row[5];
    $room_id      = $row[6];
    $repeat_id    = $row[7];
    $updated      = time_date_string($row[8],$dformat);
    $date_avis    = strftime("%Y/%m/%d",$row[10]);
    $delais_option_reservation = $row[13];
    $option_reservation = $row[14];
    $moderate = $row[15];
    $beneficiaire_ext = htmlspecialchars($row[16]);
    $jours_cycle = htmlspecialchars($row[17]);
    $duration     = $row[9];
    if($enable_periods=='y')
        list( $start_period, $start_date) =  period_date_string($row[10]);
    else
        $start_date = time_date_string($row[10],$dformat);
    if($enable_periods=='y')
        list( , $end_date) =  period_date_string($row[11], -1);
    else
        $end_date = time_date_string($row[11],$dformat);
    $rep_type = 0;

    if($repeat_id != 0)
    {
        $res = grr_sql_query("SELECT rep_type, end_date, rep_opt, rep_num_weeks FROM agt_repeat WHERE id='".protect_data_sql($repeat_id)."'");
        if (! $res) fatal_error(0, grr_sql_error());

        if (grr_sql_count($res) == 1)
        {
            $row2 = grr_sql_row($res, 0);

            $rep_type     = $row2[0];
            $rep_end_date = strftime($dformat,$row2[1]);
            $rep_opt      = $row2[2];
            $rep_num_weeks = $row2[3];
        }
        grr_sql_free($res);
    }
    if ($enable_periods=='y')
        toPeriodString($start_period, $duration, $dur_units);
    else
        toTimeString($duration, $dur_units);
    $weeklist = array("unused","every week","week 1/2","week 1/3","week 1/4","week 1/5");
    if ($rep_type == 2)
        $affiche_period = $vocab[$weeklist[$rep_num_weeks]];
    else
        $affiche_period = $vocab['rep_type_'.$rep_type];

    // Le bénéficiaire
    $beneficiaire_email = affiche_nom_prenom_email($beneficiaire,$beneficiaire_ext,"onlymail");
    if ($beneficiaire != "") {
         $beneficiaire_actif = grr_sql_query1("select etat from agt_utilisateurs where login='$beneficiaire'");
         if ($beneficiaire_actif == -1)
             $beneficiaire_actif = 'actif'; // cas des administrateurs
    } else if (($beneficiaire_ext != "")  and ($beneficiaire_email!="")) {
        $beneficiaire_actif = "actif";
    } else $beneficiaire_actif = "inactif";

    // Utilisateur ayant agit sur la réservation
    $user_login=$_SESSION['login'];
    $user_email = grr_sql_query1("select email from agt_utilisateurs where login='$user_login'");
    //
    // Elaboration du message destiné aux utilisateurs désignés par l'admin dans la partie "Mails automatiques"
    //
    //Nom de l'établissement et mention "mail automatique"
    $message = removeMailUnicode(getSettingValue("company"))." - ".$vocab["title_mail"];
    // Url de GRR
    $message = $message.traite_grr_url("","y")."\n\n";

    $sujet = $vocab["subject_mail1"].$room_name." - ".$date_avis;
    if ($action == 1) {
        // Nouvelle réservation
        $sujet = $sujet.$vocab["subject_mail_creation"];// - Nouvelle réservation
        // L'utilisateur nom prénom (email)
        $message .= $vocab["the_user"].affiche_nom_prenom_email($user_login,"","formail");
        $message = $message.$vocab["creation_booking"]; // a réservé
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") \n";
    } else if ($action == 2) {
        // Modification d'une réservation
        $sujet = $sujet.$vocab["subject_mail_modify"];// - Modification d'une réservation
        if ($moderate == 1) $sujet .= " (".$vocab["en_attente_moderation"].")";// (en attente de modération)
        // L'utilisateur nom prénom (email)
        $message .= $vocab["the_user"].affiche_nom_prenom_email($user_login,"","formail");
        $message = $message.$vocab["modify_booking"];// a modifié la réservation de
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") ";
    } else if ($action == 3) {
        // Suppression d'une réservation
        $sujet = $sujet.$vocab["subject_mail_delete"];//  - Suppression d'une réservation
        if ($moderate == 1) $sujet .= " (".$vocab["en_attente_moderation"].")";// (en attente de modération)
        // L'utilisateur nom prénom (email)
        $message .= $vocab["the_user"].affiche_nom_prenom_email($user_login,"","formail");
        $message = $message.$vocab["delete_booking"]; // a supprimé la réservation de
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") \n";
    } else if ($action == 4) {
        // Suppression automatique
        $sujet = $sujet.$vocab["subject_mail_delete"]; // - Suppression d'une réservation
        // Le délai de confirmation de réservation a été dépassé.\nSuppression automatique de la réservation de
        $message = $message.$vocab["suppression_automatique"];
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") \n";
    } else if ($action == 5) {
        // En attente de modération
        $sujet = $sujet.$vocab["subject_mail_moderation"];// - Réservation en attente de modération
        //La réservation suivante est en attente de modération pour
        $message = $message.$vocab["reservation_en_attente_de_moderation"];
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") \n";
    } else if ($action == 6) {
        // Décision de la modération
        $sujet = $sujet.$vocab["subject_mail_decision_moderation"];// - Traitement d'une réservation en attente de modération
        // On récupère les infos du traitement
        $resmoderate = grr_sql_query("select moderate, motivation_moderation from agt_loc_moderate where id ='".protect_data_sql($id_entry)."'");
        if (! $resmoderate) fatal_error(0, grr_sql_error());
        if (grr_sql_count($resmoderate) < 1) fatal_error(0, get_vocab('invalid_entry_id'));
        $rowModerate = grr_sql_row($resmoderate, 0);
        grr_sql_free($resmoderate);
        $moderate_decision = $rowModerate[0];
        $moderate_description = $rowModerate[1];

        // L'utilisateur nom prénom (email)
        $message .= $vocab["the_user"].affiche_nom_prenom_email($user_login,"","formail");
        $message = $message.$vocab["traite_moderation"]; // a traité la demande de réservation de
        // la ressource "nom de la ressource" ("nom du domaine")
        $message=$message.$vocab["the_room"].$room_name." (".$service_name.") ";
        $message = $message.$vocab["reservee au nom de"];// reservee au nom de
        // L'utilisateur nom prénom (email)
        $message = $message.$vocab["the_user"].affiche_nom_prenom_email($beneficiaire,$beneficiaire_ext,"formail")." \n";

        if ($moderate_decision == 2)
            $message .= "\n".$vocab["moderation_acceptee"]; // Votre demande a été acceptée.
        else if ($moderate_decision == 3)
            $message .= "\n".$vocab["moderation_refusee"]; // Votre demande a été refusée.
        if ($moderate_description != "") {
            $message .= "\n".$vocab["motif"].$vocab["deux_points"]; // Motif :
            $message .= $moderate_description." \n----";
        }
        $message .= "\n".$vocab["voir_details"].$vocab["deux_points"]."\n"; // Voir les détails :
        if (count($tab_id_moderes) == 0 )
            $message .= "\n".traite_grr_url("","y")."view_entry.php?id=".$id_entry;
        else {
            foreach($tab_id_moderes as $id_moderes) {
                $message .= "\n".traite_grr_url("","y")."view_entry.php?id=".$id_moderes;
            }
        }
        $message .= "\n\n".$vocab["rappel_de_la_demande"].$vocab["deux_points"]."\n"; // Rappel de la demande :
    // Notification d'un retard dans la restitution de la ressource
    } else if ($action == 7) {
        $sujet .= $vocab["subject_mail_retard"]; // - Urgent : Retard dans la restitution d'une ressource empruntée"
        // La réservation suivante n'a pas été restituée
        $message .= $vocab["message_mail_retard"].$vocab["deux_points"]." \n";
        // la ressource "nom de la ressource" ("nom du domaine")
        $message .=$room_name." (".$service_name.") \n";
        // Nom de l'emprunteur
        $message .= $vocab["nom emprunteur"].$vocab["deux_points"];
        $message .= affiche_nom_prenom_email($beneficiaire,$beneficiaire_ext,"formail")." \n";
        if ($beneficiaire_email != "") $message .= $vocab["un email envoye"].$beneficiaire_email." \n";
        $message .= "\n".$vocab["changer statut lorsque ressource restituee"].$vocab["deux_points"];
        $message .= "\n".traite_grr_url("","y")."view_entry.php?id=".$id_entry." \n";

    }


    if (($action == 2) or ($action==3)) {
        $message = $message.$vocab["reservee au nom de"];// reservee au nom de
        // L'utilisateur nom prénom (email)
        $message = $message.$vocab["the_user"].affiche_nom_prenom_email($beneficiaire,$beneficiaire_ext,"formail")." \n";
    }

    if (($action == 4) or ($action == 5) or ($action == 7))
        $repondre = getSettingValue("webmaster_email");
    else
        $repondre = $user_email;

    //
    // Infos sur la réservation
    //
    $reservation = '';
    $reservation = $reservation.$vocab["start_of_the_booking"]." ".$start_date."\n";
    $reservation = $reservation.$vocab["duration"]." ".$duration." ".$dur_units."\n";
    if (trim($breve_description) != "")
        $reservation = $reservation.$vocab["namebooker"].ereg_replace("&nbsp;", " ",$vocab["deux_points"])." ".$breve_description."\n";
    else
        $reservation = $reservation.$vocab["entryid"].$room_id."\n";
    if ($description !='') {
        $reservation = $reservation.$vocab["description"]." ".$description."\n";
    }
    // Champ additionnels
    $reservation .= affichage_champ_add_mails($id_entry);

    #Type de réservation
    $temp = grr_sql_query1("select type_name from agt_type_area where type_letter='".$row[5]."'");
    if ($temp == -1) $temp = "?".$row[5]."?"; else $temp = removeMailUnicode($temp);
    $reservation = $reservation.$vocab["type"].ereg_replace("&nbsp;", " ",$vocab["deux_points"])." ".$temp."\n";
    if($rep_type != 0) {
        $reservation = $reservation.$vocab["rep_type"]." ".$affiche_period."\n";
    }

    if($rep_type != 0)
    {
        // cas d'une periodicité "une semaine sur n", on affiche les jours de périodicité
        if ($rep_type == 2)
        {
            $opt = "";
            # Display day names according to language and preferred weekday start.
            for ($i = 0; $i < 7; $i++)
            {
                $daynum = ($i + $weekstarts) % 7;
                if ($rep_opt[$daynum]) $opt .= day_name($daynum) . " ";
            }
            if($opt)
                $reservation = $reservation.$vocab["rep_rep_day"]." ".$opt."\n";
        }
        // cas d'une periodicité "Jour Cycle", on affiche le numéro du jour cycle
        if ($rep_type == 6) {
            if (getSettingValue("jours_cycles_actif") == "Oui")
            $reservation = $reservation.$vocab["rep_type_6"].ereg_replace("&nbsp;", " ",$vocab["deux_points"]).ucfirst(substr($vocab["rep_type_6"],0,1)).$jours_cycle."\n";
        }

        $reservation = $reservation.$vocab["rep_end_date"]." ".$rep_end_date."\n";

    }
    if (($delais_option_reservation > 0) and ($option_reservation != -1))
        $reservation = $reservation."*** ".$vocab["reservation_a_confirmer_au_plus_tard_le"]." ".time_date_string_jma($option_reservation,$dformat)." ***\n";


    $reservation = $reservation."-----\n";

    // message complet du message
    $message = $message.$reservation;
    // Si vous ne souhaitez plus recevoir ces messages automatiques, écrivez en ce sens au gestionnaire de Grr :
    $message = $message.$vocab["msg_no_email"].getSettingValue("webmaster_email");;
    $message = html_entity_decode_all_version($message);
    // Fin de l'élaboration du message destiné aux utilisateurs devant recevoir les mails automatiques
    //
    // maintenant, on envoie le message
    //
    $sql = "SELECT u.email FROM agt_utilisateurs u, agt_j_mailuser_room j WHERE
    (j.id_room='".protect_data_sql($room_id)."' and u.login=j.login and u.etat='actif')  order by u.nom, u.prenom";
    $res = grr_sql_query($sql);
    $nombre = grr_sql_count($res);
    if ($nombre>0) {
        $tab_destinataire = array();
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
          if ($row[0] != "") {
            $tab_destinataire[] = $row[0];
          }
        }
      foreach($tab_destinataire as $value) {
        if (getSettingValue("grr_mail_Bcc") == "y")
            $m->AddBCC( $value );
        else
            $m->AddAddress( $value );
      }
      $m->Subject = $sujet;
      $m->Body = $message;
      $m->AddReplyTo( $repondre );
      if(!$m->Send())
          $message_erreur .= $m->ErrorInfo;
    }
    $m->ClearAddresses();
    $m->ClearBCCs();
    $m->ClearReplyTos();


    // Cas d'une notification de retard : on envoie le *** même message *** aus gestionnaires de la ressources
    // ou aux administrateurs du domaine

    if ($action == 7)  {
        $mail_admin = find_user_room ($room_id);
        if (count($mail_admin) > 0) {
        foreach($mail_admin as $value) {
            if (getSettingValue("grr_mail_Bcc") == "y")
                $m->AddBCC( $value );
            else
                $m->AddAddress( $value );
        }
        $m->Subject = $sujet;
        $m->Body = $message;
        $m->AddReplyTo( $repondre );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        }
        $m->ClearAddresses();
        $m->ClearBCCs();
        $m->ClearReplyTos();
    }
    // Cas d'une notification de retard
    // On envoie un message à l'emprunteur
    if ($action == 7)  {
        $sujet7 = $vocab["subject_mail1"].$room_name." - ".$date_avis;
        $sujet7 .= $vocab["subject_mail_retard"];
        $message7 = removeMailUnicode(getSettingValue("company"))." - ".$vocab["title_mail"];
        $message7 .= traite_grr_url("","y")."\n\n";
        // Sauf erreur, la ressource suivante que vous avez emprunté n'a pas été restituée. S'il s'agit d'une erreur, veuillez ne pas tenir compte de ce courrier.
        $message7 .= $vocab["ressource empruntee non restituée"]."\n";
        $message7 .= $room_name." (".$service_name.")";
        $message7 .= "\n".$reservation;
        $message7 = html_entity_decode_all_version($message7);
        $destinataire7 = $beneficiaire_email;
        $repondre7 = getSettingValue("webmaster_email");
        $m->AddAddress( $destinataire7 );
        $m->Subject = $sujet7;
        $m->Body = $message7;
        $m->AddReplyTo( $repondre7 );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        $m->ClearAddresses();
        $m->ClearReplyTos();
    }
    // Cas d'une moderation
    // On envoie un message au gestionnaires de la ressources ou aux administrateurs du domaine
    // pour prévenir qu'une réservation est en attente de modération
    if ($action == 5)  {
        $mail_admin = find_user_room ($room_id);
        if (count($mail_admin) > 0) {
          foreach($mail_admin as $value) {
            if (getSettingValue("grr_mail_Bcc") == "y")
                $m->AddBCC( $value );
            else
                $m->AddAddress( $value );
          }
        $sujet5 = $vocab["subject_mail1"].$room_name." - ".$date_avis;
        $sujet5 .= $vocab["subject_mail_moderation"];
        $message5 = removeMailUnicode(getSettingValue("company"))." - ".$vocab["title_mail"];
        $message5 .= traite_grr_url("","y")."\n\n";
        $message5 .= $vocab["subject_a_moderer"];
        $message5 .= "\n".traite_grr_url("","y")."view_entry.php?id=".$id_entry;
        $message5 = html_entity_decode_all_version($message5);
        $repondre5 = getSettingValue("webmaster_email");
        $m->Subject = $sujet5;
        $m->Body = $message5;
        $m->AddReplyTo( $repondre5 );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        }
        $m->ClearAddresses();
        $m->ClearBCCs();
        $m->ClearReplyTos();
    }

    // Cas d'une moderation
    // On envoie un message au bénéficiaire de la réservation pour l'avertir que sa demande est en attente de modération
    //
    if (($action == 5) and ($beneficiaire_email!='') and ($beneficiaire_actif=='actif')) {
        $sujet5 = $vocab["subject_mail1"].$room_name." - ".$date_avis;
        $sujet5 .= $vocab["subject_mail_moderation"];
        $message5 = removeMailUnicode(getSettingValue("company"))." - ".$vocab["title_mail"];
        $message5 .= traite_grr_url("","y")."\n\n";
        $message5 .= $vocab["texte_en_attente_de_moderation"];
        $message5 .= "\n".$vocab["rappel_de_la_demande"].$vocab["deux_points"];
        $message5 .= "\n".$vocab["the_room"].$room_name." (".$service_name.")";
        $message5 .= "\n".$reservation;
        $message5 = html_entity_decode_all_version($message5);
        $destinataire5 = $beneficiaire_email;
        $repondre5 = getSettingValue("webmaster_email");
        $m->AddAddress( $destinataire5 );
        $m->Subject = $sujet5;
        $m->Body = $message5;
        $m->AddReplyTo( $repondre5 );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        $m->ClearAddresses();
        $m->ClearReplyTos();
    }

    // Cas d'une modération
    // On envoie un message au bénéficiaire de la réservation pour l'avertir de la désision d'une modération
    //
    if (($action == 6) and ($beneficiaire_email!='') and ($beneficiaire_actif=='actif')) {
        // Décision de la modération
        $sujet6 = $vocab["subject_mail1"].$room_name." - ".$date_avis;
        $sujet6 .= $vocab["subject_mail_decision_moderation"];// - Traitement d'une réservation en attente de modération
        // Pour le message : on reprend le même que celui constitué pour le préposés aux mails automatiques
        $message6 = $message;
        $destinataire6 = $beneficiaire_email;
        $repondre6 = $user_email;
        $m->AddAddress( $destinataire6 );
        $m->Subject = $sujet6;
        $m->Body = $message6;
        $m->AddReplyTo( $repondre6 );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        $m->ClearAddresses();
        $m->ClearReplyTos();
    }

    // Cas d'une création, modification ou suppression d'un message par un utilisateur différent du bénéficiaire :
    // On envoie un message au bénéficiaire de la réservation pour l'avertir d'une modif ou d'une suppression
    //
    if ((($action == 1) or ($action == 2) or ($action==3))  and   (($user_login != $beneficiaire) or (getSettingValue('send_always_mail_to_creator')=='1')) and ($beneficiaire_email!='') and ($beneficiaire_actif=='actif')) {
        $sujet2 = $vocab["subject_mail1"].$room_name." - ".$date_avis;
        $message2 = removeMailUnicode(getSettingValue("company"))." - ".$vocab["title_mail"];
        $message2 = $message2.traite_grr_url("","y")."\n\n";
        $message2 = $message2.$vocab["the_user"].affiche_nom_prenom_email($user_login,"","formail");
        if ($action == 1) {
            $sujet2 = $sujet2.$vocab["subject_mail_creation"];
            $message2 = $message2.$vocab["creation_booking_for_you"];
            $message2=$message2.$vocab["the_room"].$room_name." (".$service_name.").";
        } else if ($action == 2) {
            $sujet2 = $sujet2.$vocab["subject_mail_modify"];
            $message2 = $message2.$vocab["modify_booking"];
            $message2=$message2.$vocab["the_room"].$room_name." (".$service_name.")";
            $message2 = $message2.$vocab["created_by_you"];
        } else {
            $sujet2 = $sujet2.$vocab["subject_mail_delete"];
            $message2 = $message2.$vocab["delete_booking"];
            $message2=$message2.$vocab["the_room"].$room_name." (".$service_name.")";
            $message2 = $message2.$vocab["created_by_you"];
        }
        $message2 = $message2."\n".$reservation;
        $message2 = html_entity_decode_all_version($message2);
        $destinataire2 = $beneficiaire_email;
        $repondre2 = $user_email;
        $m->AddAddress( $destinataire2 );
        $m->Subject = $sujet2;
        $m->Body = $message2;
        $m->AddReplyTo( $repondre2 );
        if(!$m->Send())
            $message_erreur .= $m->ErrorInfo;
        $m->ClearAddresses();
        $m->ClearReplyTos();
    }
    return $message_erreur;
}
function getUserName()
{
    if (isset($_SESSION['login'])) return $_SESSION['login'];
}

/* getWritable($beneficiaire, $user, $id)
 *
 * Determines if a user is able to modify an entry
 *
 * $beneficiaire - The beneficiaire of the entry
 * $user    - Who wants to modify it
 * $id -   Which room are we checking
 *
 * Returns:
 *   0        - The user does not have the required access
 *   non-zero - The user has the required access
 */
function getWritable($beneficiaire, $user, $id)
{
    if ($beneficiaire == "") $beneficiaire = $user;  // Dans le cas d'un bénéficiaire extérieure, $beneficiaire est vide. On fait comme si $user était le bénéficiaire
    $id_room = grr_sql_query1("SELECT room_id FROM agt_loc WHERE id='".protect_data_sql($id)."'");
    $dont_allow_modify = grr_sql_query1("select dont_allow_modify from agt_room where id = '".$id_room."'");
    if ($dont_allow_modify != 'y') {  // si la valeur de dont_allow_modify est "n" ou bien "-1"
        // Always allowed to modify your own stuff
        if($beneficiaire == $user)
        return 1;
    }
    // l'utilisateur $user est-il propriétaire de la resa et a-t-il le droit de réserver la ressource pour $beneficiaire
    $owner = grr_sql_query1("SELECT create_by FROM agt_loc WHERE id='".protect_data_sql($id)."'");
    if ((strtolower($user) == strtolower($owner)) and (verif_qui_peut_reserver_pour($id_room, $owner))) {
        return 1;
        die();
    }

    // allowed to modify stuffs if utilisateur has spécifics rights or statut = admin
    if (getSettingValue("allow_gestionnaire_modify_del") == 0)
        $temp = 3;
    else
        $temp = 2;
    if(authGetUserLevel($user,$id_room) > $temp)
        return 1;
     // ajout par mohan, sauf   le visiteur et le medecin 

     
	 if (get_user_status($user)!="visiteur") 
	 	return 0;
    // Unathorised access
    return 0;
}
/* Get_user_status($user)
 *
 * Determine le status de user
 *
 * $user - l'identifiant de l'utilisateur
 * $id_room -   l'identifiant de la ressource
 *
 * Retourne le status de utilisateur
 */
function get_user_status($user)
{
    if (!isset($user)) return 0;
    $res = grr_sql_query("SELECT statut FROM agt_utilisateurs WHERE login ='".protect_data_sql($user)."'");
    if (!$res || grr_sql_count($res) == 0) return 0;
    $status = mysql_fetch_row($res);
    return strtolower($status[0]);

}

/* auth_visiteur($user,$id_room)
 *
 * Determine si un visiteur peut réserver une ressource
 *
 * $user - l'identifiant de l'utilisateur
 * $id_room -   l'identifiant de la ressource
 *
 * Retourne le niveau d'accès de l'utilisateur
 */
function auth_visiteur($user,$id_room)
{
    global $id_room_autorise;
    $level = "";
    // User not logged in, user level '0'
    if ((!isset($user)) or (!isset($id_room))) return 0;
    $res = grr_sql_query("SELECT statut FROM agt_utilisateurs WHERE login ='".protect_data_sql($user)."'");
    if (!$res || grr_sql_count($res) == 0) return 0;
    $status = mysql_fetch_row($res);
    if (strtolower($status[0]) == 'visiteur') {
        if (($id_room == $id_room_autorise) and ($id_room_autorise != ""))
            return 1;
        else
            return 0;
    } else return 0;

}

/* authGetUserLevel($user,$id,$type)
 *
 * Determine le niveau d'accès de l'utilisateur
 *
 * $user - l'identifiant de l'utilisateur
 * $id -   l'identifiant de la ressource ou du domaine
 * $type - argument optionnel : 'room' (par défaut) si $id désigne une ressource et 'area' si $id désigne un domaine.
 *
 * Retourne le niveau d'accès de l'utilisateur
 */
function authGetUserLevel($user,$id,$type='room')
{
    $level = "";
    // User not logged in, user level '0'
    if(!isset($user)) return 0;
    $res = grr_sql_query("SELECT statut FROM agt_utilisateurs WHERE login ='".protect_data_sql($user)."'");
    if (!$res || grr_sql_count($res) == 0) return 0;
    $status = mysql_fetch_row($res);

    // S'agit-il d'un gestionnaire d'utilisateurs ?
    if ($type == 'user')
        if (strtolower($status[0]) == 'gestionnaire_utilisateur')
            return 1;
        else
            return 0;

    if (strtolower($status[0]) == 'visiteur') return 1;
    if (strtolower($status[0]) == 'administrateur') return 5;


    if ((strtolower($status[0]) == 'utilisateur') or  (strtolower($status[0]) == 'gestionnaire_utilisateur')) {
        if ($type == 'room') {
        // On regarde si l'utilisateur est administrateur du domaine auquel la ressource $id appartient
            $id_area = grr_sql_query1("select service_id from agt_room where id='".protect_data_sql($id)."'");
            $res3 = grr_sql_query("SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j
            WHERE (u.login=j.login and j.id_area='".protect_data_sql($id_area)."' and u.login='".protect_data_sql($user)."')");
            //$sql= "SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j            WHERE (u.login=j.login and j.id_area='".protect_data_sql($id_area)."' and u.login='".protect_data_sql($user)."')";
            //echo $sql;
            
            if (grr_sql_count($res3) > 0)
                return 4;
        // On regarde si l'utilisateur est gestionnaire des réservations pour une ressource
            $str_res2 = "SELECT * FROM agt_utilisateurs u, agt_j_user_room j ";
            $str_res2.= "WHERE u.login=j.login and u.login = '".protect_data_sql($user)."' ";
            if ($id!=-1) $str_res2.="and j.id_room='".protect_data_sql($id)."'";
            $res2 = grr_sql_query($str_res2);
            if (grr_sql_count($res2) > 0)
                return 3;
            if (grr_sql_count($res2) > 0)
                return 3;
        // Sinon il s'agit d'un simple utilisateur
        return 2;

        }
        // On regarde si l'utilisateur est administrateur d'un domaine
        if ($type == 'area') {
            if ($id == '-1') {
                //On regarde si l'utilisateur est administrateur d'un domaine quelconque
                $res2 = grr_sql_query("SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j
                WHERE (u.login=j.login and u.login='".protect_data_sql($user)."')");
                if (grr_sql_count($res2) > 0)
                    return 4;
            } else {

                //On regarde si l'utilisateur est administrateur du domaine dont l'id est $id
                $res3 = grr_sql_query("SELECT u.login FROM agt_utilisateurs u, agt_j_useradmin_area j
                WHERE (u.login=j.login and j.id_area='".protect_data_sql($id)."' and u.login='".protect_data_sql($user)."')");
                if (grr_sql_count($res3) > 0)
                    return 4;
            }
        // Sinon il s'agit d'un simple utilisateur
            return 2;
        }

    }
    
    
  

    
}

/* authUserAccesArea($user,$id)
 *
 * Determines if the user access area
 *
 * $user - The user name
 * $id -   Which area are we checking
 *
 */
function authUserAccesArea($user,$id)
{
    if ($id=='') {
        return 0;
        die();
    }
    $sql = "SELECT * FROM agt_utilisateurs WHERE (login = '".protect_data_sql($user)."' and statut='administrateur')";
    $res = grr_sql_query($sql);
    if (grr_sql_count($res) != "0") return 1;

    $sql = "SELECT * FROM agt_service WHERE (id = '".protect_data_sql($id)."' and access='r')";
    $res = grr_sql_query($sql);
    $test = grr_sql_count($res);
    if ($test == "0") {
        return 1;
    } else {
        $sql2 = "SELECT * FROM agt_j_user_area WHERE (login = '".protect_data_sql($user)."' and id_area = '".protect_data_sql($id)."')";
        $res2 = grr_sql_query($sql2);
        $test2 = grr_sql_count($res2);
        if ($test2 != "0") {
            return 1;
        } else {
            return 0;
        }
    }
}



/* authUserReserveRoom($user,$id)
 *
 * Determines if the user droit reserveé dans cette room 
 *
 * $user - The user name
 * $id -   Which room are we checking
 *
 */
function authUserReserveRoom($user,$id)
{
    if ($id=='') {
        return 0;
        die();
    }
    $sql = "SELECT * FROM agt_utilisateurs WHERE (login = '".protect_data_sql($user)."' and statut='administrateur')";
    $res = grr_sql_query($sql);

    if (grr_sql_count($res) != "0") return 1;


    $sql2 = "SELECT * FROM agt_j_user_room WHERE (login = '".protect_data_sql($user)."' and id_room = '".protect_data_sql($id)."')";
    $res2 = grr_sql_query($sql2);
    $test2 = grr_sql_count($res2);
    if ($test2 != "0") {
        return 1;
    } else {
        return 0;
    }
    
}





// function UserRoomMaxBooking
// Cette fonction teste si l'utilisateur a la possibilité d'effectuer une réservation, compte tenu
// des limitations éventuelles de la ressources et du nombre de réservations déjà effectuées.
//
function UserRoomMaxBooking($user, $id_room, $number) {
  global $enable_periods;
  if ($id_room == '') return 0;
  // On regarde si le nombre de réservation de la ressource est limité
  $sql = "SELECT max_booking FROM agt_room WHERE id = '".protect_data_sql($id_room)."'";
  $result = grr_sql_query1($sql);
  if ($result > 0) {
     if(authGetUserLevel($user,$id_room) < 2 ) {
       return 0;
     } else if(authGetUserLevel($user,$id_room) == 2) {
       $day   = date("d");
        $month = date("m");
        $year  = date("Y");
        $hour  = date("H");
        $minute = date("i");
        if ($enable_periods == 'y')
            $now = mktime(0, 0, 0, $month, $day, $year);
        else
            $now = mktime($hour, $minute, 0, $month, $day, $year);
        $max_booking = grr_sql_query1("SELECT max_booking FROM agt_room WHERE id='".protect_data_sql($id_room)."'");
        $sql2 = "SELECT * FROM agt_loc WHERE (room_id = '".protect_data_sql($id_room)."' and beneficiaire = '".protect_data_sql($user)."' and end_time > '$now')";
        $res = grr_sql_query($sql2);
        $nb_bookings = grr_sql_count($res) + $number;
        if ($nb_bookings > $max_booking) {
          return 0;
        } else {
          return 1;
        }
      } else {
        // l'utilisateur est soit admin, soit administrateur de la ressource.
        return 1;
      }
    } else if ($result == 0) {
     if(authGetUserLevel($user,$id_room) >= 3) {
        return 1;
     } else {
        return 0;
     }
  } else {
     return 1;
  }
}

// function verif_booking_date($user, $id, $date_booking, $date_now)
// $user : le login de l'utilisateur
// $id : l'id de la résa. Si -1, il s'agit d'une nouvelle réservation
// $id_room : id de la ressource
// $date_booking : la date de la réservation (n'est utile que si $id=-1)
// $date_now : la date actuelle
//
function verif_booking_date($user, $id, $id_room, $date_booking, $date_now, $enable_periods, $endtime='') {
  global $correct_diff_time_local_serveur, $can_delete_or_create;
  $can_delete_or_create="y";
  // On teste si l'utilisateur est administrateur
  $sql = "select statut from agt_utilisateurs WHERE login = '".protect_data_sql($user)."'";
  $statut_user = grr_sql_query1($sql);
  if ($statut_user == 'administrateur') {
    return true;
    die();
  }
  // A-t-on le droit d'agir dans le passé ?
  $allow_action_in_past = grr_sql_query1("select allow_action_in_past from agt_room where id = '".protect_data_sql($id_room)."'");
  if ($allow_action_in_past == 'y') {
    return true;
    die();
  }
  // Correction de l'avance en nombre d'heure du serveur sur les postes clients
  if ((isset($correct_diff_time_local_serveur)) and ($correct_diff_time_local_serveur!=0))
      $date_now -= 3600*$correct_diff_time_local_serveur;

  // Créneaux basés sur les intitulés
  // Dans ce cas, on prend comme temps présent le jour même à minuit.
  // Cela signifie qu'il est possible de modifier/réserver/supprimer tout au long d'une journée
  // même si l'heure est passée.
  // Cela demande donc à être amélioré en introduisant pour chaque créneau une heure limite de réservation.
  if ($enable_periods == "y") {
      $month =  date("m",$date_now);
      $day =  date("d",$date_now);
      $year = date("Y",$date_now);
      $date_now = mktime(0, 0, 0, $month, $day, $year);
  }
  if ($id != -1) {
    // il s'agit de l'edition d'une réservation existante
    if (($endtime != '') and ($endtime < $date_now)) {
      return false;
      die();
    }
    if ((getSettingValue("allow_user_delete_after_begin") == 1) or (getSettingValue("allow_user_delete_after_begin") == 2))
        $sql = "SELECT end_time FROM agt_loc WHERE id = '".protect_data_sql($id)."'";
    else
        $sql = "SELECT start_time FROM agt_loc WHERE id = '".protect_data_sql($id)."'";
    $date_booking = grr_sql_query1($sql);
    if ($date_booking < $date_now) {
      return false;
      die();
    } else {
      // dans le cas où le créneau est entamé, on teste si l'utilisateur a le droit de supprimer la réservation
      // Si oui, on transmet la variable $only_modify = TRUE avant que la fonction de retourne true.
      if (getSettingValue("allow_user_delete_after_begin") == 2) {
          $date_debut = grr_sql_query1("SELECT start_time FROM agt_loc WHERE id = '".protect_data_sql($id)."'");
          if ($date_debut < $date_now) $can_delete_or_create = "n"; else $can_delete_or_create = "y";
      }
      return true;
    }

  } else {
    if (getSettingValue("allow_user_delete_after_begin")==1) {
        $id_area = grr_sql_query1("select service_id from agt_room where id = '".protect_data_sql($id_room)."'");
        $resolution_area = grr_sql_query1("select resolution_area from agt_service where id = '".$id_area."'");
        if ($date_booking>$date_now-$resolution_area) {
          return true;
        } else {
         return false;
        }
    } else {
        if ($date_booking>$date_now) {
          return true;
        } else {
         return false;
        }
    }

  }
}

// function verif_duree_max_resa_area($user, $id_room, $starttime, $endtime)
// $user : le login de l'utilisateur
// $id_room : l'id de la ressource. Si -1, il s'agit d'une nouvelle ressource.
// $starttime : début de la réservation
// $endtime : fin de la réservation
//
function verif_duree_max_resa_area($user, $id_room, $starttime, $endtime) {
  if(authGetUserLevel($user,$id_room) >= 3) {
  // On teste si l'utilisateur est gestionnaire de la ressource
    return true;
    die();
  }
  $id_area = grr_sql_query1("select service_id from agt_room where id='".protect_data_sql($id_room)."'");
  $duree_max_resa_area = grr_sql_query1("select duree_max_resa_area from agt_service where id='".$id_area."'");
  $enable_periods =  grr_sql_query1("select enable_periods from agt_service where id='".$id_area."'");
  if ($enable_periods == 'y') $duree_max_resa_area = $duree_max_resa_area*24*60;
  if ($duree_max_resa_area < 0) {
    return true;
    die();
  } else if ($endtime - $starttime > $duree_max_resa_area*60) {
    return false;
    die();
  } else {
    return true;
    die();
  }
}

// function verif_delais_max_resa_room($user, $id_room, $date_booking)
// $user : le login de l'utilisateur
// $id_room : l'id de la ressource. Si -1, il s'agit d'une nouvelle ressoure
// $date_booking : la date de la réservation (n'est utile que si $id=-1)
// $date_now : la date actuelle
//
function verif_delais_max_resa_room($user, $id_room, $date_booking) {
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    $datenow = mktime(0,0,0,$month,$day,$year);

  if(authGetUserLevel($user,$id_room) >= 3) {
  // On teste si l'utilisateur est administrateur
    return true;
    die();
  }
  $delais_max_resa_room = grr_sql_query1("select delais_max_resa_room from agt_room where id='".protect_data_sql($id_room)."'");
  if ($delais_max_resa_room == -1) {
    return true;
    die();
  } else if ($datenow + $delais_max_resa_room*24*3600 +1 < $date_booking) {
    return false;
    die();
  } else {
    return true;
    die();
  }
}

// function verif_access_search : vérifier l'accès à l'outil de recherche
// $user : le login de l'utilisateur
// $id_room : l'id de la ressource.
//
function verif_access_search($user) {
    if(authGetUserLevel($user,-1) >= getSettingValue("allow_search_level"))
        return TRUE;
    else
        return FALSE;
}

// function verif_display_fiche_ressource : vérifier l'accès à la visualisation de la fiche d'une ressource
// $user : le login de l'utilisateur
// $id_room : l'id de la ressource.
//
function verif_display_fiche_ressource($user, $id_room) {
    $show_fic_room = grr_sql_query1("select show_fic_room from agt_room where id='".$id_room."'");
    if ($show_fic_room == "y") {
        if(authGetUserLevel($user,$id_room) >= getSettingValue("visu_fiche_description"))
            return TRUE;
        else
            return FALSE;
    } else {
        return FALSE;
    }
}

// function verif_delais_min_resa_room($user, $id_room, $date_booking)
// $user : le login de l'utilisateur
// $id_room : l'id de la ressource. Si -1, il s'agit d'une nouvelle ressoure
// $date_booking : la date de la réservation (n'est utile que si $id=-1)
// $date_now : la date actuelle
//
function verif_delais_min_resa_room($user, $id_room, $date_booking) {
  if(authGetUserLevel($user,$id_room) >= 3) {
  // On teste si l'utilisateur est administrateur
    return true;
    die();
  }
  $delais_min_resa_room = grr_sql_query1("select delais_min_resa_room from agt_room where id='".protect_data_sql($id_room)."'");
  if ($delais_min_resa_room == 0) {
    return true;
    die();
  } else {
    $hour = date("H");
    $minute  = date("i")+$delais_min_resa_room;
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    $date_limite = mktime($hour,$minute,0,$month,$day,$year);
    if ($date_limite > $date_booking) {
        return false;
        die();
    } else {
        return true;
        die();
    }
  }
}

// Vérifie que la date de confirmation est inférieur à la date de début de réservation
function verif_date_option_reservation($option_reservation, $starttime) {
    if ($option_reservation == -1)
        return true;
    else {
        $day   = date("d",$starttime);
        $month = date("m",$starttime);
        $year  = date("Y",$starttime);
        $date_starttime = mktime(0,0,0,$month,$day,$year);
        if ($option_reservation < $date_starttime)
            return true;
        else
            return false;
    }
}

// Vérifie que $_create_by peut réserver la ressource $_room_id pour $_beneficiaire
function verif_qui_peut_reserver_pour($_room_id, $_create_by) {
    /*if ($_beneficiaire == "") {
    // cas où il s'agit d'un bénéficiaire extérieure : c'est normal que $_beneficiaire soit vide
        return TRUE;
        die();
    }
    if (strtolower($_create_by) == strtolower($_beneficiaire)) {
        return TRUE;
        die();
    }*/
    $qui_peut_reserver_pour  = grr_sql_query1("select qui_peut_reserver_pour from agt_room where id='".$_room_id."'");
   // echo "UserLevel".authGetUserLevel($_create_by, $_room_id);
    if(authGetUserLevel($_create_by, $_room_id) >= $qui_peut_reserver_pour) {
        return TRUE;
        die();
    } else {
        return FALSE;
        die();
    }
}
/* VerifyModeDemo()
 *
 * Affiche une page "opération non autorisée" pour certaines opérations dans le cas le mode demo est activé.
 *
 * Returns: Nothing
 */
function VerifyModeDemo() {
    if (getSettingValue("ActiveModeDemo")=='y') {
      print_header("","","","","");
      ?>
      <H1>Op&eacute;ration non autoris&eacute;e</H1>
      <P>Vous êtes dans une <b>version de démonstration de GRR</b>.
      <br />Certaines fonctions ont été volontairement bridées. C'est le cas pour l'opération que vous avez tenté d'effectuer.</P>
      </BODY></HTML>
      <?php
      die();
    }
}

/* MajMysqlModeDemo()
 * dans le cas le mode demo est activé :
 * Met à jour la base mysql une fois par jour, lors de la première connexion
 *
 */
function MajMysqlModeDemo() {
    // Nom du fichier sql à exécuter
    $fic_sql = "grr_maj_quotidienne.sql";
    if ((getSettingValue("ActiveModeDemo")=='y') and (file_exists($fic_sql))) {
        $date_now = mktime(0,0,0,date("m"),date("d"),date("Y"));
        if ((getSettingValue("date_verify_demo") == "") or (getSettingValue("date_verify_demo") < $date_now )) {
            $fd = fopen($fic_sql, "r");
            $result_ok = 'yes';
            while (!feof($fd)) {
                $query = fgets($fd, 5000);
                $query = trim($query);
                if ($query != '') {
                   $reg = mysql_query($query);
                }
            }
            fclose($fd);
            if (!saveSetting("date_verify_demo", $date_now)) {
                echo "Erreur lors de l'enregistrement de date_verify_demo !<br />";
                die();
            }
        }
    }
}

/* showAccessDenied()
 *
 * Displays an appropate message when access has been denied
 *
 * Returns: Nothing
 */
function showAccessDenied($day, $month, $year, $area, $back)
{
    global $vocab;
    if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
        $type_session = "no_session";
    } else {
        $type_session = "with_session";
    }
    print_header($day, $month, $year, $area,$type_session);
    ?>
   <H1><?php echo get_vocab("accessdenied")?></H1>
   <P>
   <?php echo get_vocab("norights")?>
   </P>
   <P><a href="<?php echo $back; ?>"><?php echo get_vocab("returnprev"); ?></a><A HREF="<?php echo $back; ?>"></A></P>
</BODY>
</HTML>
<?php
}

/* showNoReservation()
 *
 * Displays an appropate message when access has been denied
 *
 * Returns: Nothing
 */
function showNoReservation($day, $month, $year, $area, $back)
{
    global $vocab;
    if ((getSettingValue("authentification_obli")==0) and (!isset($_SESSION['login']))) {
        $type_session = "no_session";
    } else {
        $type_session = "with_session";
    }
    print_header($day, $month, $year, $area,$type_session);
    ?>
   <H1><?php echo get_vocab("accessdenied")?></H1>
   <P>
   <?php echo get_vocab("noreservation")?>
   </P>
   <P>
   <A HREF="<?php echo $back; ?>"><?php echo get_vocab("returnprev"); ?></A>
   </P>
</BODY>
</HTML>
<?php
}


/* showAccessDeniedMaxBookings()
 *
 * Displays an appropate message when access has been denied
 *
 * Returns: Nothing
 */
function showAccessDeniedMaxBookings($day, $month, $year, $area, $id_room,$back)
{
    global $vocab;

    print_header($day, $month, $year, $area);
    ?>
    <H1><?php echo get_vocab("accessdenied")?></H1>
    <P>
    <?php
    $max_booking = grr_sql_query1("SELECT max_booking FROM agt_room WHERE id='".protect_data_sql($id_room)."'");
    echo get_vocab("msg_max_booking").$max_booking."<br /><br />".get_vocab("accessdeniedtoomanybooking");
    ?>
    </P>
    <P>
    <A HREF="<?php echo $back; ?>"><?php echo get_vocab("returnprev"); ?></A>
    </P>
    </BODY>
    </HTML>
    <?php
}


function check_begin_end_bookings($day, $month, $year) {
    $date = mktime(0,0,0,$month,$day,$year);
	/*echo "Date".$date;
	echo "Begin booking".getSettingValue("begin_bookings");
	echo "End booking".getSettingValue("end_bookings");*/
    if (($date < getSettingValue("begin_bookings")) or ($date > getSettingValue("end_bookings")))
    return -1;
}
function showNoBookings($day, $month, $year, $area, $back, $type_session)
{
 global $vocab;
 print_header($day, $month, $year, $area,$type_session);
 $date = mktime(0,0,0,$month,$day,$year);
 echo '<h2>'.get_vocab("nobookings").' '.affiche_date($date).'</h2>';
 echo '<p>'.get_vocab("begin_bookings").'<b>'.affiche_date(getSettingValue("begin_bookings")).'</b></p>';
 echo '<p>'.get_vocab("end_bookings").'<b>'.affiche_date(getSettingValue("end_bookings")).'</b></p>';
 ?>
 <p>
  <?php if ($back != "") { ?>
   <a href="<?php echo $back; ?>"><?php echo get_vocab("returnprev"); ?></a>
   <?php }
  ?>
  </p>
 </BODY>
</HTML>
<?php
}

function date_time_string($t,$dformat)
{
    global $twentyfourhour_format;
    if ($twentyfourhour_format)
                $timeformat = "%T";
    else
    {
                $ampm = date("a",$t);
                $timeformat = "%I:%M:%S$ampm";
    }
    return utf8_strftime($dformat.$timeformat, $t);
}

# Convert a start period and end period to a plain language description.
# This is similar but different from the way it is done in view_entry.
function describe_period_span($starts, $ends)
{
    global $enable_periods, $periods_name, $vocab, $duration;
    list( $start_period, $start_date) =  period_date_string($starts);
    list( , $end_date) =  period_date_string($ends, -1);
    $duration = $ends - $starts;
    toPeriodString($start_period, $duration, $dur_units);
    if ($duration > 1) {
        list( , $start_date) =  period_date_string($starts);
        list( , $end_date) =  period_date_string($ends, -1);
        $temp = $start_date . " ==> " . $end_date;
    } else {
        $temp = $start_date . " - " . $duration . " " . $dur_units;
    }
    return $temp;
}



#Convertit l'heure de début et de fin en période.
function describe_span($starts, $ends, $dformat)
{
    global $vocab, $twentyfourhour_format;
    $start_date = utf8_strftime($dformat, $starts);
        if ($twentyfourhour_format)
    {
                $timeformat = "%T";
    }
    else
    {
                $ampm = date("a",$starts);
                $timeformat = "%I:%M:%S$ampm";
    }
    $start_time = strftime($timeformat, $starts);
    $duration = $ends - $starts;
    if ($start_time == "00:00:00" && $duration == 60*60*24)
        return $start_date . " - " . get_vocab("all_day");
    toTimeString($duration, $dur_units);
    return $start_date . " " . $start_time . " - " . $duration . " " . $dur_units;
}

function get_planning_area_values($id_area) {
    global $resolution, $morningstarts, $eveningends, $eveningends_minutes, $weekstarts, $twentyfourhour_format, $enable_periods, $periods_name, $display_day, $nb_display_day;
    $sql = "SELECT calendar_default_values, 
    resolution_area, morningstarts_area, eveningends_area,
     eveningends_minutes_area, weekstarts_area,
      twentyfourhour_format_area, enable_periods, display_days
    FROM agt_service
    WHERE id = '".protect_data_sql($id_area)."'";
    $res = grr_sql_query($sql);
    if (! $res) {
    //    fatal_error(0, grr_sql_error());
        include "trailer.inc.php";
        exit;
    }
    $row_ = grr_sql_row($res, 0);

    $nb_display_day = 0;
    for ($i = 0; $i < 7; $i++)
    {
      if (substr($row_[8],$i,1) == 'y') {
          $display_day[$i] = 1;
          $nb_display_day++;
      } else
          $display_day[$i] = 0;
    }


    // Créneaux basés sur les intitulés
    if ($row_[7] == 'y') {
        $resolution = 60;
        $morningstarts = 12;
        $eveningends = 12;
        $sql_periode = grr_sql_query("SELECT nom_periode FROM agt_service_periodes where service_id='".$id_area."'");
        $eveningends_minutes = grr_sql_count($sql_periode)-1;
        $i = 0;
        while ($i < grr_sql_count($sql_periode)) {
            $periods_name[$i] = grr_sql_query1("select nom_periode FROM agt_service_periodes where service_id='".$id_area."' and num_periode= '".$i."'");
            $i++;
        }
        $enable_periods = "y";
        $weekstarts = $row_[5];
        $twentyfourhour_format = $row_[6];
    // Créneaux basés sur le temps
    } else {
        if ($row_[0] != 'y') {
            $resolution = $row_[1];
            $morningstarts = $row_[2];
            $eveningends = $row_[3];
            $eveningends_minutes = $row_[4];
            $enable_periods = "n";
            $weekstarts = $row_[5];
            $twentyfourhour_format = $row_[6];
        }
    }
}

// Dans le cas ou $unicode_encoding = 1 (UTF-8) cette fonction encode les chaînes présentes dans
// le code "en dur", en UTF-8 avant affichage
function encode_message_utf8($tag)
{
  global $charset_html, $unicode_encoding;

  if ($unicode_encoding)
  {
    return iconv($charset_html,"utf-8",$tag);
  }
  else
  {
    return $tag;
  }
}

function removeMailUnicode($string)
{
    global $unicode_encoding, $charset_html;
    //
    if ($unicode_encoding)
    {
        return @iconv("utf-8", $charset_html, $string);
    }
    else
    {
        return $string;
    }
}

// Cette fonction vérifie une fois par jour si le délai de confirmation des réservations est dépassé
// Si oui, les réservations concernées sont supprimées et un mail automatique est envoyé.
function verify_confirm_reservation() {
    global $dformat;
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    $date_now = mktime(0,0,0,$month,$day,$year);
    if ((getSettingValue("date_verify_reservation") == "") or (getSettingValue("date_verify_reservation") < $date_now )) {
        $res = grr_sql_query("select id from agt_room where delais_option_reservation > 0");
        if (! $res) {
            //    fatal_error(0, grr_sql_error());
            include "trailer.inc.php";
            exit;
        } else {
            for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
                $res2 = grr_sql_query("select id from agt_loc where option_reservation < '".$date_now."' and option_reservation != '-1' and room_id='".$row[0]."'");
                if (! $res2) {
                    //    fatal_error(0, grr_sql_error());
                    include "trailer.inc.php";
                    exit;
                } else {
                    for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++) {
                        if (getSettingValue("automatic_mail") == 'yes') $_SESSION['session_message_error'] = send_mail($row2[0],4,$dformat);
                        // On efface la réservation
                        grr_sql_command("DELETE FROM agt_loc WHERE id=" . $row2[0]);
                        // On efface le cas écheant également  dans agt_loc_moderate
                        grr_sql_command("DELETE FROM agt_loc_moderate WHERE id=" . $row2[0]);

                     }
                }
            }
        }
        if (!saveSetting("date_verify_reservation", $date_now)) {
            echo "Erreur lors de l'enregistrement de date_verify_reservation !<br />";
            die();
        }
    }
}
// Cette fonction vérifie une fois par jour si les réservations devant être rendus ne sont pas
// en retard
// Si oui, les utilisateurs concernées recoivent un mail automatique pour leur notifier.
function verify_retard_reservation() {
    global $dformat;
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    $date_now = mktime(0,0,0,$month,$day,$year);
    if (((getSettingValue("date_verify_reservation2") == "") or (getSettingValue("date_verify_reservation2") < $date_now )) and (getSettingValue("automatic_mail") == 'yes')) {
        //$res = grr_sql_query("SELECT r.id FROM agt_room r, agt_service a WHERE a.retour_resa_obli = 1 AND r.service_id = a.id");
        $res = grr_sql_query("SELECT id FROM agt_room");
        if (! $res) {
                // fatal_error(0, grr_sql_error());
            include "trailer.inc.php";
            exit;
        } else {
             for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
                $res2 = grr_sql_query("select id from agt_loc where statut_entry='e' and end_time < '".$date_now."' and room_id='".$row[0]."'");
                if (! $res2) {
                       // fatal_error(0, grr_sql_error());
                    include "trailer.inc.php";
                    exit;
                } else {
                    for ($j = 0; ($row2 = grr_sql_row($res2, $j)); $j++) {
                         $_SESSION['session_message_error'] = send_mail($row2[0],7,$dformat);
                    }
                }
            }
        }
        if (!saveSetting("date_verify_reservation2", $date_now)) {
            echo "Erreur lors de l'enregistrement de date_verify_reservation2 !<br />";
            die();
        }
    }
}


function est_fermee($date,$area) {
    $test = grr_sql_query1("select date from  agt_loc_parametres where date = '".$date."' AND service_id='".$area."'AND service_fermee='1'");
    if ($test != -1)
        return TRUE;
    else
        return FALSE;

}


function est_hors_reservation($time) {
    $test = grr_sql_query1("select DAY from grr_calendar where DAY = '".$time."'");
    if ($test != -1)
        return TRUE;
    else
        return FALSE;

}

function resa_est_hors_reservation($start_time,$end_time) {
    $test = grr_sql_query1("select DAY from grr_calendar where DAY = '".$start_time."' or DAY = '".$end_time."'");
    if ($test != -1)
        return TRUE;
    else
        return FALSE;

}

// trouve les utilisateurs gestionnaires de ressource
function find_user_room ($id_room)
{
    $emails = array ();
    $sql = "select email from agt_utilisateurs, agt_j_user_room
    where agt_utilisateurs.login = agt_j_user_room.login and id_room='".$id_room."'";
    $res = grr_sql_query($sql);
    if ($res) {
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++) {
            if (validate_email($row[0])) $emails[] = $row[0];
        }
    }
    // Si la table des emails des gestionnaires de la ressource est vide, on avertit les administrateurs du domaine
    if (count($emails) == 0) {
        $id_area = mrbsGetAreaIdByRoomIdFromRoomId($id_room);
        $sql_admin = grr_sql_query("select email from agt_utilisateurs, agt_j_useradmin_area
        where agt_utilisateurs.login = agt_j_useradmin_area.login and agt_j_useradmin_area.id_area='".$id_area."'");
        if ($sql_admin) {
            for ($i = 0; ($row = grr_sql_row($sql_admin, $i)); $i++) {
                if (validate_email($row[0])) $emails[] = $row[0];
            }
        }
    }
    // Si la table des emails des administrateurs du domaines est vide, on avertit les administrateurs générauxd
    if (count($emails) == 0) {
        $sql_admin = grr_sql_query("select email from agt_utilisateurs where statut = 'administrateur'");
        if ($sql_admin) {
            for ($i = 0; ($row = grr_sql_row($sql_admin, $i)); $i++) {
                if (validate_email($row[0])) $emails[] = $row[0];
            }
        }
    }
    return $emails;
}

function validate_email ($email)
{
    $atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)

    $regex = '/^' . $atom . '+' .   // Une ou plusieurs fois les caractères autorisés avant l'arobase
    '(\.' . $atom . '+)*' .         // Suivis par zéro point ou plus
                                    // séparés par des caractères autorisés avant l'arobase
    '@' .                           // Suivis d'un arobase
    '(' . $domain . '{1,63}\.)+' .  // Suivis par 1 à 63 caractères autorisés pour le nom de domaine
                                    // séparés par des points
    $domain . '{2,63}$/i';          // Suivi de 2 à 63 caractères autorisés pour le nom de domaine

    if (preg_match($regex, $email)) {
            return true;
    } else {
            return false;
    }

}

 function FieldName ($id_field) {
	$sql = "SELECT fieldname FROM agt_overload WHERE id='".$id_field."'";
	$res = grr_sql_query($sql);
	$row = grr_sql_row($res,0);
	return $row[0];
} 
/** grrDelOverloadFromEntries()
 * Supprime les données du champ $id_field de toutes les réservations
 * Modification effectué le 12/07/2013
 */
function grrDelOverloadFromEntries($id_field)
{
  /*$begin_string = "<".$id_field.">";
  $end_string = "</".$id_field.">";*/
  // On cherche à quel domaine est rattaché le champ additionnel
  $id_area = grr_sql_query1("select id_area from agt_overload where id='".$id_field."'");
  if ($id_area == -1) fatal_error(0, get_vocab('error_area') . $id_area . get_vocab('not_found'));
  // On cherche toutes les ressources du domaine
  $call_rooms = grr_sql_query("select id from agt_room where service_id = '".$id_area."'");
  if (! $call_rooms) fatal_error(0, get_vocab('error_room') . $id_room . get_vocab('not_found'));
  for ($i = 0; ($row = grr_sql_row($call_rooms, $i)); $i++) {
      // On cherche toutes les resas de cette resources
      $call_resa = grr_sql_query("select id from agt_loc where room_id ='".$row[0]."'");
      if (! $call_resa) fatal_error(0, get_vocab('invalid_entry_id'));
      for ($j=0;($row2 = grr_sql_row($call_resa, $j)); $j++) 
      {
		  $entry_id = $row2[0];
		  $field_name = FieldName($id_field);
		  $sql = "DELETE FROM agt_overload_data where entry_id='".$entry_id."' and field_name='".$field_name."'";
		  if (grr_sql_command($sql) < 0)
          fatal_error(0, "$sql \n\n" . grr_sql_error());
      }
  }
}
      //if (! $call_resa) fatal_error(0, get_vocab('invalid_entry_id'));
      /*for ($j = 0; ($row2 = grr_sql_row($call_resa, $j)); $j++) {
          $overload_desc = $row2[1];
          $begin_pos = strpos($overload_desc,$begin_string);
          $end_pos = strpos($overload_desc,$end_string);
          if ( $begin_pos !== false && $end_pos !== false ) {
              $endpos = $end_pos + 1 + strlen($begin_string);
              $debut_new_chaine = substr($overload_desc,0,$begin_pos);
              $fin_new_chaine = substr($overload_desc,$endpos);
              $new_chaine = $debut_new_chaine.$fin_new_chaine;
              grr_sql_command("update agt_loc set overload_desc = '".$new_chaine."' where id = '".$row2[0]."'");
          }

       }
      // On cherche toutes les resas de cette resources
      $call_resa = grr_sql_query("select id, overload_desc from agt_repeat where room_id ='".$row[0]."'");
      if (! $call_resa) fatal_error(0, get_vocab('invalid_entry_id'));
      for ($j = 0; ($row2 = grr_sql_row($call_resa, $j)); $j++) {
          $overload_desc = $row2[1];
          $begin_pos = strpos($overload_desc,$begin_string);
          $end_pos = strpos($overload_desc,$end_string);
          if ( $begin_pos !== false && $end_pos !== false ) {
              $endpos = $end_pos + 1 + strlen($begin_string);
              $debut_new_chaine = substr($overload_desc,0,$begin_pos);
              $fin_new_chaine = substr($overload_desc,$endpos);
              $new_chaine = $debut_new_chaine.$fin_new_chaine;
              grr_sql_command("update agt_repeat set overload_desc = '".$new_chaine."' where id = '".$row2[0]."'");
          }

       }
    }
}*/

function traite_grr_url($grr_script_name="",$force_use_grr_url="n") {
    // Dans certaines configuration (reverse proxy, ...) les variables $_SERVER["SCRIPT_NAME"] ou $_SERVER['PHP_SELF']
    // sont mal interprétées entraînant des liens erronés sur certaines pages.
    if (((getSettingValue("use_grr_url")=="y") and (getSettingValue("grr_url")!="")) or ($force_use_grr_url=="y")) {
        if (substr(getSettingValue("grr_url"), -1) != "/") $ad_signe = "/"; else $ad_signe = "";
        return getSettingValue("grr_url").$ad_signe.$grr_script_name;
    } else {
        return $_SERVER['PHP_SELF'];
    }
}
// Pour les Jours/Cycles
//Crée le calendrier Jours/Cycles
function cree_calendrier_date_valide($n,$i) {
        if ($i <= getSettingValue("nombre_jours_Jours/Cycles")) {
            $sql = "INSERT INTO agt_calendrier_jours_cycle set DAY='".$n."', Jours = $i";
            if (grr_sql_command($sql) < 0) {
                fatal_error(1, "<p>" . grr_sql_error());
            }
            $i++;
        }
        else {
            $i = 1;
            $sql = "INSERT INTO agt_calendrier_jours_cycle set DAY='".$n."', Jours = $i";
            if (grr_sql_command($sql) < 0) {
                fatal_error(1, "<p>" . grr_sql_error());
            }
            $i++;
        }
        return $i;
    }

function numero_semaine($date)
{
    /*
     * Norme ISO-8601:
     * - La semaine 1 de toute année est celle qui contient le 4 janvier ou que la semaine 1 de toute année est celle qui contient le 1er jeudi de janvier.
     * - La majorité des années ont 52 semaines mais les années qui commence un jeudi et les années bissextiles commençant un mercredi en possède 53.
     * - Le 1er jour de la semaine est le Lundi
     */

    // Définition du Jeudi de la semaine
    if (date("w",$date)==0) // Dimanche
        $jeudiSemaine = $date-3*24*60*60;
    else if (date("w",$date)<4) // du Lundi au Mercredi
        $jeudiSemaine = $date+(4-date("w",$date))*24*60*60;
    else if (date("w",$date)>4) // du Vendredi au Samedi
        $jeudiSemaine = $date-(date("w",$date)-4)*24*60*60;
    else // Jeudi
        $jeudiSemaine = $date;

    // Définition du premier Jeudi de l'année
    if (date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))==0) // Dimanche
    {
        $premierJeudiAnnee = mktime(12,0,0,1,1,date("Y",$jeudiSemaine))+4*24*60*60;
    }
    else if (date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))<4) // du Lundi au Mercredi
    {
        $premierJeudiAnnee = mktime(12,0,0,1,1,date("Y",$jeudiSemaine))+(4-date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine))))*24*60*60;
    }
    else if (date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))>4) // du Vendredi au Samedi
    {
        $premierJeudiAnnee = mktime(12,0,0,1,1,date("Y",$jeudiSemaine))+(7-(date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))-4))*24*60*60;
    }
    else // Jeudi
    {
        $premierJeudiAnnee = mktime(12,0,0,1,1,date("Y",$jeudiSemaine));
    }

    // Définition du numéro de semaine: nb de jours entre "premier Jeudi de l'année" et "Jeudi de la semaine";
    $numeroSemaine =     (
                    (
                        date("z",mktime(12,0,0,date("m",$jeudiSemaine),date("d",$jeudiSemaine),date("Y",$jeudiSemaine)))
                        -
                        date("z",mktime(12,0,0,date("m",$premierJeudiAnnee),date("d",$premierJeudiAnnee),date("Y",$premierJeudiAnnee)))
                    ) / 7
                ) + 1;

    // Cas particulier de la semaine 53
    if ($numeroSemaine==53)
    {
        // Les années qui commence un Jeudi et les années bissextiles commençant un Mercredi en possède 53
        if (date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))==4 || (date("w",mktime(12,0,0,1,1,date("Y",$jeudiSemaine)))==3 && date("z",mktime(12,0,0,12,31,date("Y",$jeudiSemaine)))==365))
        {
            $numeroSemaine = 53;
        }
        else
        {
            $numeroSemaine = 1;
        }
    }

    return sprintf("%02d",$numeroSemaine);
}

# Calcule le nombre de jours dans un mois en tenant compte des années bissextiles.
function getDaysInMonth($month, $year)
    {
        if ($month < 1 || $month > 12)
            return 0;
        $days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $d = $days[$month - 1];
        if ($month == 2)
        {
                #Vérification de l'année bissextile.
            if ($year%4 == 0)
            {
                if ($year%100 == 0)
                {
                    if ($year%400 == 0)
                        $d = 29;
                    }
                else
                    $d = 29;
                }
            }
        return $d;
}
function getFirstDays()
    {
      global $weekstarts, $display_day;
      $basetime = mktime(12,0,0,6,11+$weekstarts,2000);
      for ($i = 0, $s = ""; $i < 7; $i++)
      {
         $j = ($i + 7 + $weekstarts) % 7;
         $show = $basetime + ($i * 24 * 60 * 60);
         $fl = strftime('%a',$show);
         if ($display_day[$j] == 1)
             $s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\">$fl</td>\n";
         else
             $s .= "<td align=\"center\" valign=\"top\" class=\"calendarHeader\"></td>\n";
      }
      return $s;
}
/*
Construit les informations à afficher sur les plannings
*/
function affichage_lien_resa_planning($breve_description, $id_resa) {
    $affichage = "";
    if ((getSettingValue("display_short_description")==1) and ($breve_description!=""))
        $affichage = $breve_description;
    else {
        $affichage = get_vocab("entryid").$id_resa;
    }
    return htmlspecialchars($affichage,ENT_NOQUOTES);
}

/*
Construit les informations à afficher sur les plannings
*/
function affichage_resa_planning($_description, $id_resa) {
    $affichage = "";
    if (getSettingValue("display_full_description")==1)
        $affichage = htmlspecialchars($_description,ENT_NOQUOTES);
    // Les champs add :
    /*
     * $overload_data = mrbsEntryGetOverloadDesc($id_resa);
    foreach ($overload_data as $fieldname=>$field) {
        if (($field["affichage"] == 'y') and ($field["valeur"]!="")) {
            if ($affichage != "") $affichage .= "<br />";
            $affichage .= htmlspecialchars($fieldname,ENT_NOQUOTES).get_vocab("deux_points").htmlspecialchars($field["valeur"],ENT_NOQUOTES);
        }
    }
    */
    return $affichage;
}

function get_protocole_old($desc_complet){
	$ret_val=urldecode($desc_complet);	
	$ret_val = htmlspecialchars($ret_val,ENT_NOQUOTES);
	return $ret_val;
	
	}
/*
Construit les informations à afficher sur les plannings
*/
function affichage_resa_planning_n($_description, $id_resa) {
    $affichage = "";
    if (getSettingValue("display_full_description")==1)
        $affichage = htmlspecialchars($_description,ENT_NOQUOTES);
    // Les champs add :
    $overload_data = mrbsEntryGetOverloadDesc_n($id_resa);
    foreach ($overload_data as $fieldname=>$field) {
        if (($field["affichage"] == 'y') and ($field["valeur"]!="")) {
            if ($affichage != "") $affichage .= "<br />";
            $affichage .= htmlspecialchars($fieldname,ENT_NOQUOTES).get_vocab("deux_points").htmlspecialchars($field["valeur"],ENT_NOQUOTES);
        }
    }
    return $affichage;
}


/*
Construit les informations à afficher sur les plannings
*/
function affichage_champ_add_mails($id_resa) {
    $affichage = "";
    // Les champs add :
    $overload_data = mrbsEntryGetOverloadDesc($id_resa);
    foreach ($overload_data as $fieldname=>$field) {
        if (($field["overload_mail"] == 'y') and ($field["valeur"]!="")) {
            $affichage .= htmlspecialchars($fieldname).get_vocab("deux_points").htmlspecialchars($field["valeur"])."\n";;
        }
    }
    return $affichage;
}





/*
Affiche un message pop-up
$type_affichage = "user" -> Affichage des "pop-up" de confirmation après la création/modification/suppression d'une réservation
Dans ce cas, l'affichage n'a lieu que si $_SESSION['displ_msg']='yes'
$type_affichage = "admin" -> Affichage des "pop-up" de confirmation dans les menus d'administration
$type_affichage = "force" -> On force l'affichage du pop-up même si javascript_info_admin_disabled est TRUE
*/
function affiche_pop_up($msg="",$type_affichage="user"){
    // Si $_SESSION["msg_a_afficher"] est défini, on l'affiche, sinon, on affiche $msg passé en variable
    if ((isset($_SESSION["msg_a_afficher"])) and ($_SESSION["msg_a_afficher"] != "")) {
        $msg = $_SESSION["msg_a_afficher"];
    }
    if ($msg != "") {
      if ($type_affichage == "user") {
        if (!(getSettingValue("javascript_info_disabled"))) {
          echo "<script type=\"text/javascript\" language=\"javascript\">";
          if ((isset($_SESSION['displ_msg'])) and ($_SESSION['displ_msg']=='yes'))  echo " alert(\"".$msg."\")";
          echo "</script>";
       }
      } else if ($type_affichage == "admin") {
        if (!(getSettingValue("javascript_info_admin_disabled")))  {
            echo "<script type=\"text/javascript\" language=\"javascript\">";
            echo "<!--\n";
            echo " alert(\"".$msg."\")";
            echo "//-->";
            echo "</script>";
        }
     } else {
            echo "<script type=\"text/javascript\" language=\"javascript\">";
            echo "<!--\n";
            echo " alert(\"".$msg."\")";
            echo "//-->";
            echo "</script>";
     }
    }
    $_SESSION['displ_msg']="";
    $_SESSION["msg_a_afficher"] = "";

}

/*
Retourne un tableau contenant les nom et prénom et l'email de $_beneficiaire
*/
function donne_nom_email($_beneficiaire){
    $tab_benef = array();
    $tab_benef["nom"] = "";
    $tab_benef["email"] = "";
    if ($_beneficiaire == "") {
        return $tab_benef;
        die();
    }
    $temp = explode("|",$_beneficiaire);
    if (isset($temp[0])) $tab_benef["nom"] = $temp[0];
    if (isset($temp[1])) $tab_benef["email"] = $temp[1];
    return $tab_benef;
}

/*
Retourne une chaine concaténée des nom et prénom et l'email
*/
function concat_nom_email($_nom, $_email){
    // On supprime les caractères | de $_nom
    $_nom = trim(str_replace("|","",$_nom));
    if ($_nom == "") {
        return "-1";
        die();
    }
    $_email = trim($_email);
    if ($_email != "") {
        if (strstr($_email,"|")) {
            // l'adresse email contient le catactère | ce qui n'est pas normal et peut compromettre la suite du traitement.
            return "-2";
            die();
        }
    }
    $chaine = $_nom."|".$_email;
    return $chaine;
}
/*
Formate les noms, prénom et email du bénéficiaire ou du bénéficiaire extérieur
$type = nomail -> on affiche pas le mail
$type = withmail -> on affiche le mail
$type = formail -> on formate en utf8 pour l'envoi par mail
$type = nolymail -> on affiche uniquement le mail
*/

function affiche_nom_prenom_email($_beneficiaire,$_beneficiaire_ext,$type="nomail"){
    if ($_beneficiaire !="") {
        $sql_beneficiaire = "SELECT prenom, nom, email FROM agt_utilisateurs WHERE login = '".$_beneficiaire."'";
        $res_beneficiaire = grr_sql_query($sql_beneficiaire);
        if ($res_beneficiaire) {
            $row_user = grr_sql_row($res_beneficiaire, 0);
            if ($type == "formail")  {
                $chaine = removeMailUnicode($row_user[0])." ".removeMailUnicode($row_user[1]);
                if ($row_user[2] != "") {
                    $chaine .= " (".$row_user[2].")";
                }
            } else if ($type == "onlymail") {
            // Cas où en envoie uniquement le mail
                $chaine = grr_sql_query1("select email from agt_utilisateurs where login='$_beneficiaire'");
            } else if (($type == "withmail") and ($row_user[2] != "")) {
            // Cas où en envoie les noms, prénoms et mail
                $email = explode('@',$row_user[2]);
                $person = $email[0];
                if (isset($email[1])) {
                    $domain = $email[1];
                    $chaine = "<script type=\"text/javascript\">encode_adresse('".$person."','".$domain."','".AddSlashes($row_user[1]." ".$row_user[0])."',1);</script>";
                } else {
                    $chaine = $row_user[0]." ".$row_user[1];
                }
            } else {
                // Cas où en envoie les noms, prénoms sans le mail
                $chaine = $row_user[0]." ".$row_user[1];
            }
            return $chaine;
            die();
        } else {
            return "";
            die();
        }
    } else {
        // cas d'un bénéficiaire extérieur
        // On récupère le tableau des nom et emails
        $tab_benef = donne_nom_email($_beneficiaire_ext);
        // Cas où en envoie uniquement le mail
        if ($type == "onlymail") {
           $chaine = $tab_benef["email"];
        // Cas où en envoie les noms, prénoms et mail
        } else if (($type == "withmail") and ($tab_benef["email"] != "")) {
            $email = explode('@',$tab_benef["email"]);
            $person = $email[0];
            if (isset($email[1])) {
                $domain = $email[1];
                $chaine = "<script type=\"text/javascript\">encode_adresse('".$person."','".$domain."','".AddSlashes($tab_benef["nom"])."',1);</script>";
            } else {
                $chaine = $tab_benef["nom"];
            }
        } else {
            // Cas où en envoie les noms, prénoms sans le mail
            $chaine = $tab_benef["nom"];
        }
        return $chaine;
        die();
    }
}

// Les lignes suivantes permettent la compatibilité de GRR avec la variables register_global à off
unset($day);
if (isset($_GET["day"])) {
    $day = $_GET["day"];
    settype($day,"integer");
    if ($day < 1) $day = 1;
    if ($day > 31) $day = 31;
}
unset($month);
if (isset($_GET["month"])) {
    $month = $_GET["month"];
    settype($month,"integer");
    if ($month < 1) $month = 1;
    if ($month > 12) $month = 12;
}
unset($year);
if (isset($_GET["year"])) {
    $year = $_GET["year"];
    settype($year,"integer");
    if ($year < 1900) $year = 1900;
    if ($year > 2100) $year = 2100;
}

unset($room);
$room = isset($_GET["room"]) ? $_GET["room"] : NULL;
settype($room,"integer");

unset($area);
$area = isset($_GET["area"]) ? $_GET["area"] : NULL;
settype($area,"integer");


   function date_2Mysql($StrDate)
   {
   	if (empty($StrDate)) return "0000-00-00";
		list($dt,$time)=explode(" ",$StrDate);
     	list($day,$month,$year)=explode("/", $dt);
		return "$year-$month-$day $time";
   }
  
   // function by mohanraju
   // returns des areas allowed pour un utilisateur donnee   
   function get_areas_allowed($user,$statut){
   	$list_areas="";
		if ($statut=="administrateur"){
			$sql="SELECT id  from agt_service ";
		}else{
			$sql="SELECT id_area FROM agt_j_user_area WHERE (login = '".protect_data_sql($user)."')";
		}
	   $res = grr_sql_query($sql);
	   if (! $res) fatal_error(0, grr_sql_error());
		$list_areas="";
		for ($i = 0; ($row = grr_sql_row($res, $i)); $i++){ 
			if (strlen($list_areas)>2){
				$list_areas.=",'".$row[0]."'";			
			}else{	
				$list_areas.="'".$row[0]."'";
			}   
		}
		return $list_areas;
	}
	
?>
