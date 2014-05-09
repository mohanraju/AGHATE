<?php
header('Content-Type: text/xml; charset=UTF-8');
$value= array(array("id" => 1, "name" => "Web Demo"),
array("id" => 2, "name" => "Audio Countdown"),
array("id" => 3, "name" => "The Tab Key"), 
array("id" => 4, "name" => "Music Sleep Timer")); 



exit(json_encode($value));

?>
