<?php
#########################################################################
#                        admin_user_modify.php                          #
#                                                                       #
#            Interface de modification/création d'un utilisateur        #
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


include "./commun/include/admin.inc.php";
$grr_script_name = "admin_user_modify.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
$day   = date("d");
$month = date("m");
$year  = date("Y");

if ((authGetUserLevel(getUserName(),-1) < 5) and (authGetUserLevel(getUserName(),-1,'user') !=  1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
// un gestionnaire d'utilisateurs ne peut pas modifier un administrateur général ou un gestionnaire d'utilisateurs
if (isset($_GET["user_login"]) and (authGetUserLevel(getUserName(),-1,'user') ==  1)) {
    $test_statut = grr_sql_query1("select statut from agt_utilisateurs where login='".$_GET["user_login"]."'");
    if (($test_statut == "administrateur") or ($test_statut == "gestionnaire_utilisateur")) {
        showAccessDenied($day, $month, $year, $area,$back);
        exit();
    }
}


#If we dont know the right date then make it up
unset($user_login);
$user_login = isset($_GET["user_login"]) ? $_GET["user_login"] : NULL;
$valid = isset($_GET["valid"]) ? $_GET["valid"] : NULL;
$msg='';
$user_nom='';
$user_prenom='';
$user_mail='';
$user_statut='';
$user_source='local';
$user_etat='';
$medecin='0';        
$display="";
$retry='';



if ($valid == "yes") {
    // Restriction dans le cas d'une démo
    VerifyModeDemo();
    $reg_nom = isset($_GET["reg_nom"]) ? $_GET["reg_nom"] : NULL;
    $reg_prenom = isset($_GET["reg_prenom"]) ? $_GET["reg_prenom"] : NULL;
    $new_login = isset($_GET["new_login"]) ? $_GET["new_login"] : NULL;
    $reg_password = isset($_GET["reg_password"]) ? $_GET["reg_password"] : NULL;
    $reg_password2 = isset($_GET["reg_password2"]) ? $_GET["reg_password2"] : NULL;
    $reg_statut = isset($_GET["reg_statut"]) ? $_GET["reg_statut"] : NULL;
    $reg_email = isset($_GET["reg_email"]) ? $_GET["reg_email"] : NULL;
    $reg_etat = isset($_GET["reg_etat"]) ? $_GET["reg_etat"] : NULL;
    $medecin = isset($_GET["medecin"]) ? $_GET["medecin"] : NULL;    
    $reg_source = isset($_GET["reg_source"]) ? $_GET["reg_source"] : NULL;


    if (($reg_nom == '') or ($reg_prenom == '')) {
        $msg = get_vocab("please_enter_name");
        $retry = 'yes';
    } else {
        //
        // actions si un nouvel utilisateur a été défini
        //
        if ((isset($new_login)) and ($new_login!='') and (ereg ("^[a-zA-Z0-9_.]{1,20}$", $new_login)) ) {
            // un gestionnaire d'utilisateurs ne peut pas créer un administrateur général ou un gestionnaire d'utilisateurs
            $test_statut = TRUE;
            if  (authGetUserLevel(getUserName(),-1) < 5) {
                if (($reg_statut == "administrateur") or ($reg_statut == "gestionnaire_utilisateur"))
                    $test_statut = FALSE;
            }
            $new_login = strtoupper($new_login);
            $reg_password_c = md5($reg_password);
            if (!($test_statut)) {
                $msg = get_vocab("erreur_choix_statut");
                $retry = 'yes';
            } else if (($reg_password != $reg_password2) or (strlen($reg_password) < $pass_leng)) {
                $msg = get_vocab("passwd_error");
                $retry = 'yes';
            } else {
                $sql = "SELECT * FROM agt_utilisateurs WHERE login = '".$new_login."'";
                $res = grr_sql_query($sql);
                $nombreligne = grr_sql_count ($res);
                if ($nombreligne != 0) {
                    $msg = get_vocab("error_exist_login");
                    $retry = 'yes';
                } else {
                	$msg = "Vous ne pouvez pas créer des nouveaux utilisateurs, le nouveau utlisateur devrais utiliser login et mot de pas de Windows pour acceder Aghate !!!";
                	/*
                    //add user desactivated by mohan , the users can connect only from LDAP
                    $sql = "INSERT INTO agt_utilisateurs SET
                    nom='".protect_data_sql($reg_nom)."',
                    prenom='".protect_data_sql($reg_prenom)."',
                    login='".protect_data_sql($new_login)."',
                    password='".protect_data_sql($reg_password_c)."',
                    statut='".protect_data_sql($reg_statut)."',
                    email='".protect_data_sql($reg_email)."',
                    etat='".protect_data_sql($reg_etat)."',
                    medecin='".protect_data_sql($medecin)."',                    
                    default_area = '0',
                    default_room = '0',
                    default_style = '',
                    default_list_type = '',
                    default_language = 'fr',
                    source='local'";
                    if (grr_sql_command($sql) < 0)
                        {fatal_error(0, get_vocab("msg_login_created_error") . grr_sql_error());
                    } else {
                        $msg = get_vocab("msg_login_created");
                    }
                    $user_login = $new_login;
                    */
                }
            }
//
//action s'il s'agit d'une modification
//
        } else if ((isset($user_login)) and ($user_login!='')) {
            // un gestionnaire d'utilisateurs ne peut pas modifier un administrateur général ou un gestionnaire d'utilisateurs
            $test_statut = TRUE;
            if  (authGetUserLevel(getUserName(),-1) < 5) {
                $old_statut = grr_sql_query1("select statut from agt_utilisateurs where login='".protect_data_sql($user_login)."'");
                if (((($old_statut == "administrateur") or ($old_statut == "gestionnaire_utilisateur")) and ($old_statut !=$reg_statut))
                or ((($old_statut == "utilisateur") or ($old_statut == "visiteur")or ($old_statut == "medecin")) and (($reg_statut == "administrateur") or ($reg_statut == "gestionnaire_utilisateur"))))
                    $test_statut = FALSE;
            }
            if (!($test_statut)) {
                $msg = get_vocab("erreur_choix_statut");
                $retry = 'yes';
            } else if (isset($reg_source)) {
                // On demande un changement de la source ext->local
                $reg_password_c = md5($reg_password);
                if (($reg_password != $reg_password2) or (strlen($reg_password) < $pass_leng)) {
                    $msg = get_vocab("passwd_error");
                    $retry = 'yes';
                }
                $source = "source='local',password='".protect_data_sql($reg_password_c)."',";
            } else {
                $source = "";
            }
        if ($retry != 'yes') {
            $sql = "UPDATE agt_utilisateurs SET nom='".protect_data_sql($reg_nom)."',
            prenom='".protect_data_sql($reg_prenom)."',
            statut='".protect_data_sql($reg_statut)."',
            email='".protect_data_sql($reg_email)."',".$source."
            etat='".protect_data_sql($reg_etat)."',
            medecin='".protect_data_sql($medecin)."'            
            WHERE login='".protect_data_sql($user_login)."'";

            if (grr_sql_command($sql) < 0)
                {fatal_error(0, get_vocab("message_records_error") . grr_sql_error());
            } else {
                $msg = get_vocab("message_records");
            }

            // Cas où on a déclaré un utilisateur inactif, on le supprime dans les tables agt_j_user_area,  agt_j_mailuser_room
            if ($reg_etat != 'actif') {
                $sql = "DELETE FROM agt_j_user_area WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0) fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
                $sql = "DELETE FROM agt_j_mailuser_room WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0) fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
            }

            // Cas où on a déclaré un utilisateur visiteur, on le supprime dans les tables agt_j_user_area, agt_j_mailuser_room et agt_j_user_room

            if ($reg_statut=='visiteur') {
                $sql = "DELETE FROM agt_j_user_room WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0)
                    fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
                $sql = "DELETE FROM agt_j_mailuser_room WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0)
                    fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
                $sql = "DELETE FROM agt_j_user_area WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0)
                    fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
            }
            if ($reg_statut=='administrateur') {
                $sql = "DELETE FROM agt_j_user_room WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0)
                    fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
                $sql = "DELETE FROM agt_j_user_area WHERE login='$user_login'";
                if (grr_sql_command($sql) < 0)
                    fatal_error(0, get_vocab('message_records_error') . grr_sql_error());
            }
        }

        } else {
            $msg = get_vocab("only_letters_and_numbers");
            $retry = 'yes';
        }
    }
    if ($retry == 'yes') {
        $user_nom = $reg_nom;
        $user_prenom = $reg_prenom;
        $user_statut = $reg_statut;
        $user_mail = $reg_email;
        $user_etat = $reg_etat;
		  $medecin  =$medecin ;             
    }
}

// On appelle les informations de l'utilisateur pour les afficher :
if (isset($user_login) and ($user_login!='')) {
    $sql = "SELECT nom, prenom, statut, etat, email, source,medecin FROM agt_utilisateurs WHERE login='$user_login'";
    $res = grr_sql_query($sql);
    if ($res) {
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $user_nom = $row[0];
        $user_prenom = $row[1];
        $user_statut = $row[2];
        $user_etat = $row[3];
        $user_mail = $row[4];
        $user_source = $row[5];
		  $medecin   = $row[6];     
        }
    }
}
if((authGetUserLevel(getUserName(),-1) < 1) and (getSettingValue("authentification_obli")==1))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}


# print the page header
print_header("","","","",$type="with_session", $page="admin");

// Affichage d'un pop-up
affiche_pop_up($msg,"admin");

if (isset($user_login) and ($user_login!='')) {
    echo "<h2>".get_vocab('admin_user_modify_modify.php')."</h2>";
} else {
   // echo "<h2>".get_vocab('admin_user_modify_create.php')."</h2>";
   
   echo "<h2>Vous ne pouvez pas créer des nouveaux utilisateurs, <br />le nouveau utlisateur devrais utiliser login et mot de pas de Windows pour acceder Aghate !!!</h2>";
    echo "| <a href=\"admin_user.php?display=".$display."\">". get_vocab("back")."</a> |   ";
   exit;
}


?>
<p class=bold>
| <a href="admin_user.php?display=<?php echo $display; ?>"><?php echo get_vocab("back"); ?></a> |
<?php
if (isset($user_login) and ($user_login!='')) {
    //echo "<a href=\"admin_user_modify.php?display=$display\">".get_vocab("display_add_user")."</a> | ";
    if (($user_source=='local') or ($user_source==''))
    echo "<a href=\"admin_change_pwd.php?user_login=$user_login\">".get_vocab("change_pwd")."</a> |";
} ?>
<br /><?php echo get_vocab("required"); ?>
</p>
<form action="admin_user_modify.php?display=<?php echo $display; ?>" method='get'>

<?php
echo get_vocab("login")." *".get_vocab("deux_points");
if (isset($user_login) and ($user_login!='')) {
    echo $user_login;
    echo "<input type=\"hidden\" name=\"reg_login\" value=\"$user_login\" />\n";
} else {
    echo "<input type=\"text\" name=\"new_login\" size=\"20\" value=\"".htmlentities($user_login)."\" />\n";
}
echo "<table border=\"0\" cellpadding=\"5\"><tr>";


echo "<td>".get_vocab("last_name")." *".get_vocab("deux_points")."</td><td><input type=\"text\" name=\"reg_nom\" size=\"20\" value=\"";
if ($user_nom) echo htmlspecialchars($user_nom);
echo "\" /></td>\n";
echo "<td>".get_vocab("first_name")." *".get_vocab("deux_points")."</td><td><input type=\"text\" name=\"reg_prenom\" size=\"20\" value=\"";
if ($user_nom) echo htmlspecialchars($user_prenom);
echo "\" /></td>\n";
echo "<td>Statut Medecin:</td>

<td><input type=\"radio\" name=\"medecin\" value=\"1\" "; 
			if ($medecin == "1") { echo "checked";}
			echo " >OUI
    <input type=\"radio\" name=\"medecin\" value=\"0\" "; if ($medecin == "0") { echo "checked";} echo ">NON
 </td>

</tr>\n";

echo "<tr><td>".get_vocab("mail_user").get_vocab("deux_points")."</td><td><input type=\"text\" name=\"reg_email\" size=\"30\" value=\"";
if ($user_mail) echo htmlspecialchars($user_mail);
echo "\" /></td>\n";

echo "<td>".get_vocab("statut").get_vocab("deux_points")."</td>\n";
echo "<td><SELECT name=\"reg_statut\" size=\"1\">\n";
echo "<option value=\"visiteur\" "; if ($user_statut == "visiteur") { echo "SELECTED";}; echo ">".get_vocab("statut_visitor")."</option>\n";
echo "<option value=\"utilisateur\" "; if ($user_statut == "utilisateur") { echo "SELECTED";}; echo ">Gestionaire Reservation</option>\n";
echo "<option value=\"gestionnaire_utilisateur\" "; if ($user_statut == "gestionnaire_utilisateur") { echo "SELECTED";}; echo ">Gestion Reservation/Users</option>\n";
//if ((getSettingValue("ldap_statut") == '') or (getSettingValue("sso_statut") == '')) {
   echo "<option value=\"administrateur\" "; if ($user_statut == "administrateur") { echo "SELECTED";}; echo ">".get_vocab("statut_administrator")."</option>\n";

//}
echo "</select></td>\n";

if (strtolower($_SESSION['login']) != strtolower($user_login)) {
  echo "<td>".get_vocab("activ_no_activ").get_vocab("deux_points")."</td>";
  echo "<td><select name=\"reg_etat\" size=\"1\">\n";
  echo "<option value=\"actif\" ";
  if ($user_etat == "actif")  echo "SELECTED";
  echo ">".get_vocab("activ_user")."</option>\n";
  echo "<option value=\"inactif\" ";
  if ($user_etat == "inactif")  echo "SELECTED";
  echo ">".get_vocab("no_activ_user")."</option>\n";
  echo "</select></td>";
} else {
    echo "<input type=\"hidden\" name=\"reg_etat\" value=\"$user_etat\" />\n";
}
echo "</tr>\n";
echo "</table>";

if (!(isset($user_login)) or ($user_login=='')) {
    echo "<br />".get_vocab("pwd_toot_short")." *".get_vocab("deux_points")."<input type=\"password\" name=\"reg_password\" size=\"20\" />\n";
    echo "<br />".get_vocab("confirm_pwd")." *".get_vocab("deux_points")."<input type=\"password\" name=\"reg_password2\" size=\"20\" />\n";
}

echo "<br />";
if ($user_source == 'ext') {
    echo "<br /><br /><table border=\"1\" cellpadding=\"5\" cellspacing=\"1\" width=\"100%\"><tr><td>\n";
    echo get_vocab("authentification")." <b>".get_vocab("Externe")."</b><br />\n";
    echo "<input type=\"checkbox\" name=\"reg_source\" />".get_vocab("Changer_source_utilisateur_local")."<br />\n";
    echo "<br />".get_vocab("pwd_toot_short")." *".get_vocab("deux_points")."<input type=password name=reg_password size=20 />\n";
    echo "<br />".get_vocab("confirm_pwd")." *".get_vocab("deux_points")."<input type=password name=reg_password2 size=20 />\n";
    echo "</td></tr></table>\n";
}
echo "<input type=\"hidden\" name=\"valid\" value=\"yes\" />\n";
if (isset($user_login)) echo "<input type=\"hidden\" name=\"user_login\" value=\"".$user_login."\" />\n";
echo "<br /><center><input type=\"submit\" value=\"".get_vocab("save")."\" /></center>\n";
echo "</form>\n";

// On affiche la liste des privilèges de cet utilisateurs
if ((isset($user_login)) and ($user_login!='')) {
  echo "<h2>".get_vocab('liste_privileges').$user_prenom." ".$user_nom." :</h2>";
  $a_privileges = 'n';
  $req_area = "select id, service_name, access from agt_service order by order_display";
  $res_area = grr_sql_query($req_area);
  if ($res_area) {
    for ($i = 0; ($row_area = grr_sql_row($res_area, $i)); $i++) {
        // On teste si l'utilisateur administre le domaine
        $test_admin = grr_sql_query1("select count(id_area) from agt_j_useradmin_area j where j.login = '".$user_login."' and j.id_area='".$row_area[0]."'");
        if ($test_admin >= 1) $is_admin = 'y'; else $is_admin = 'n';
        // On teste si l'utilisateur gère des ressources dans ce domaine
        $nb_room = grr_sql_query1("select count(r.room_name) from agt_room r
        left join agt_service a on r.service_id=a.id
        where a.id='".$row_area[0]."'");

        $req_room = "select r.room_name from agt_room r
        left join agt_j_user_room j on r.id=j.id_room
        left join agt_service a on r.service_id=a.id
        where j.login = '".$user_login."' and a.id='".$row_area[0]."'";
        $res_room = grr_sql_query($req_room);
        $is_gestionnaire = '';
        if ($res_room) {
            if ((grr_sql_count($res_room) == $nb_room) and ($nb_room!=0))
                $is_gestionnaire = $vocab["all_rooms"];
            else
            for ($j = 0; ($row_room = grr_sql_row($res_room, $j)); $j++) {
                $is_gestionnaire .= $row_room[0]."<br />";
            }
        }
        // On teste si l'utilisateur reçoit des mails automatiques
        $req_mail = "select r.room_name from agt_room r
        left join agt_j_mailuser_room j on r.id=j.id_room
        left join agt_service a on r.service_id=a.id
        where j.login = '".$user_login."' and a.id='".$row_area[0]."'";
        $res_mail = grr_sql_query($req_mail);
        $is_mail = '';
        if ($res_mail) {
            for ($j = 0; ($row_mail = grr_sql_row($res_mail, $j)); $j++) {
                $is_mail .= $row_mail[0]."<br />";
            }
        }
        // Si le domaine est restreint, on teste si l'utilateur a accès
        if ($row_area[2] == 'r') {
            $test_restreint = grr_sql_query1("select count(id_area) from agt_j_user_area j where j.login = '".$user_login."' and j.id_area='".$row_area[0]."'");
            if ($test_restreint >= 1) $is_restreint = 'y'; else $is_restreint = 'n';
        } else $is_restreint = 'n';

        if (($is_admin == 'y') or ($is_restreint == 'y') or ($is_gestionnaire != '') or ($is_mail != '')) {
            $a_privileges = 'y';
            echo "<H3>".get_vocab("match_area").get_vocab("deux_points").$row_area[1];
            if ($row_area[2] == 'r') echo " (".$vocab["restricted"].")";
            echo "</H3>";
            echo "<ul>";
            if ($is_admin == 'y') echo "<li><b>".get_vocab("administrateur du domaine")."</b></li>";
            if ($is_restreint == 'y') echo "<li><b>".get_vocab("a acces au domaine")."</b></li>";
            if ($is_gestionnaire != '') {
                echo "<li><b>".get_vocab("gestionnaire des resources suivantes")."</b><br />";
                echo $is_gestionnaire;
                echo "</li>";
            }
            if ($is_mail != '') {
                echo "<li><b>".get_vocab("est prevenu par mail")."</b><br />";
                echo $is_mail;
                echo "</li>";
            }
            echo "</ul>";
        }
    }
  }
  if ($a_privileges == 'n') {
      if ($user_statut == 'administrateur')
          echo get_vocab("administrateur general").".";
      else
          echo get_vocab("pas de privileges").".";
  }
}

echo "</body></html>";
?>
