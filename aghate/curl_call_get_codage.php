<?php
$ch = curl_init("http://gesthdj.sls.aphp.fr/curl_get_codage.php");
$fp = fopen("trace_curl_gesthdj.htm", "w");
fwrite($fp,"\n-start-");
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fwrite($fp,"\n-fin-");
fclose($fp);
?> 
