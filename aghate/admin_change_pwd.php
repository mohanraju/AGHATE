<?php
#########################################################################
#                          admin_change_pwd.php                         #
#                                                                       #
#   Interface de changement du mot de passe pour les administrateurs    #
#                                                                       #
#            Dernière modification : 28/03/2008                         #
#                                                                       #
#########################################################################
/*
 * Copyright 2003-2005 Laurent Delineau
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
$grr_script_name = "admin_change_pwd.php";

$back = '';
if (isset($_SERVER['HTTP_REFERER'])) $back = htmlspecialchars($_SERVER['HTTP_REFERER']);
if(authGetUserLevel(getUserName(),-1) < 5)
{
    $day   = date("d");
    $month = date("m");
    $year  = date("Y");
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}
// Restriction dans le cas d'une démo
VerifyModeDemo();

unset($user_login);
$user_login = isset($_POST["user_login"]) ? $_POST["user_login"] : ($user_login = isset($_GET["user_login"]) ? $_GET["user_login"] : NULL);
$valid = isset($_POST["valid"]) ? $_POST["valid"] : NULL;

$msg='';

if ($valid == "yes") {
    unset($reg_password1);
    $reg_password1 = $_POST["reg_password1"];
    unset($reg_password2);
    $reg_password2 = $_POST["reg_password2"];

    $reg_password_c = md5($reg_password1);
    if (($reg_password1 != $reg_password2) or (strlen($reg_password1) < $pass_leng)) {
        $msg = get_vocab("passwd_error");
    } else {
        $sql = "UPDATE agt_utilisateurs SET password='" . protect_data_sql($reg_password_c)."' WHERE login='$user_login'";
        if (grr_sql_command($sql) < 0)
            {fatal_error(0, get_vocab('update_pwd_failed') . grr_sql_error());
        } else {
            $msg = get_vocab('update_pwd_succeed');
        }
    }
}

// On appelle les informations de l'utilisateur
if (isset($user_login) and ($user_login!='')) {
    $sql = "SELECT nom,prenom, source FROM agt_utilisateurs WHERE login='$user_login'";
    $res = grr_sql_query($sql);
    if ($res) {
        for ($i = 0; ($row = grr_sql_row($res, $i)); $i++)
        {
        $user_nom = $row[0];
        $user_prenom = $row[1];
        $user_source = $row[2];
        }
    }
}

if (($user_source != 'local') and ($user_source != ''))
{
    showAccessDenied($day, $month, $year, $area,$back);
    exit();
}


# print the page header
print_header("","","","",$type="with_session", $page="admin");
affiche_pop_up($msg,"admin");

?>

<p>| <a href="admin_user_modify.php?user_login=<?php echo $user_login; ?>"><?php echo get_vocab("back");?></a> |</p>

<?php
echo "<h3>".get_vocab("pwd_change")."</h3>";
if ($user_login != $_SESSION['login']) {
    echo "<form action=\"admin_change_pwd.php\" method='post'>";
    echo get_vocab("login")." : $user_login";
    echo "<br />".get_vocab("last_name").get_vocab("deux_points").$user_nom."&nbsp;&nbsp;&nbsp;".get_vocab("first_name").get_vocab("deux_points").$user_prenom;
    echo "<br />".get_vocab("pwd_msg_warning");
    echo "<br /><br />".get_vocab("new_pwd1").get_vocab("deux_points")."<input type=\"password\" name=\"reg_password1\" size=\"20\" />";
    echo "<br />".get_vocab("new_pwd2").get_vocab("deux_points")."<input type=\"password\" name=\"reg_password2\" size=\"20\" />";
    echo "<input type=\"hidden\" name=\"valid\" value=\"yes\" />";
    echo "<input type=\"hidden\" name=\"user_login\" value=\"$user_login\" />";
    echo "<br /><input type=\"submit\" value=".get_vocab("save")." /></form>";
} else {
    echo "<br />".get_vocab("pwd_msg_warning2");
}

?>
</body>
</html>
