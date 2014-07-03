<?php
session_name('GRR');
$cache_expire = session_cache_expire();
session_start();
echo "<pre>";
print_r($_SESSION);
echo "time :".date("d/m/Y H:i:s");



echo "La session en cache va expirer après $cache_expire minutes";
 
?>
