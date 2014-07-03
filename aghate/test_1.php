<?php
session_name('GRR');
ini_set('session.gc_maxlifetime', 60); // 3 heurs
session_cache_expire(1);
$cache_expire = session_cache_expire();
session_start();
echo "La session en cache va expirer après $cache_expire minutes";
$_SESSION['TEST']='TEST DELAI';
print_r($_SESSION);

?>
