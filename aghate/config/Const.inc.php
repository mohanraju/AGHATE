<?php

//Database settings MYSQL

include "./config/config.php";  //pour config MYSQL
error_reporting(E_ALL & ~E_NOTICE);
//Database settings GILDA
$ORAHost="gilda";
$ORAUser="consult";
$ORAPassword="consult";

//variables du langue Français
$temp_charset = "utf-8" ;


// constans table
$table_bgcolor2="#F7F4F4";
$table_bgcolor1="#FCFFFB";	

if (count($_POST)) {
while (list($key, $val) = each($_POST)) {
$$key = $val;
}
}

if (count($_GET)) {
while (list($key, $val) = each($_GET)) {
$$key = $val;
}
}

if (count($_SESSION)) {
while (list($key, $val) = each($_SESSION)) {
$$key = $val;
}
}

if (empty($PHP_SELF)) {
$PHP_SELF = $_SERVER['PHP_SELF'];
}

// messages et erreurs declaration 




?>
