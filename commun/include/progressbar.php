<?php
 function InitProgressBar($gauche,$haut,$largeur,$hauteur,$bord_col,$txt_col,$bg_col)
 {
	 $tailletxt=$hauteur-10;
	 echo '<div id="pourcentage" style="position:absolute;top:'.$haut;
	 echo ';left:'.$gauche;
	 echo ';width:'.$largeur.'px';
	 echo ';height:'.$hauteur.'px;border:1px solid #6B7983 ;font-family:Tahoma;font-weight:bold';
	 echo ';font-size:'.$tailletxt.'px;color:#0000FF;z-index:1;text-align:center;">0%</div>';
	
	 echo '<div id="progrbar" style="position:absolute;top:'.($haut+1); //+1
	 echo ';left:'.($gauche+1); //+1
	 echo ';width:0px';
	 echo ';height:'.$hauteur.'px';
	 echo ';background-color:#D4D0C8;z-index:0;"></div>';
 

 }
 function ProgressBar($indice)
 {
 	//decoupe le pourcentage
 	$list=explode(" ",$indice);
 	$pct=$list[count($list)-1];
	 echo "\n<script>";
	 echo "document.getElementById(\"pourcentage\").innerHTML='".$indice."%';";
	 echo "document.getElementById('progrbar').style.width=".($pct*2).";\n";	 	 
	 echo "</script>";
	 flush(); // explication : http://www.manuelphp.com/php/function.flush.php
 }
 function ProgressBarHide()
 {
	 echo "\n<script>";
	 echo "document.getElementById(\"pourcentage\").style.display = 'none';\n";
	 echo "document.getElementById('progrbar').style.display = 'none';\n";	 
	 echo "</script>";
	 flush(); // explication : http://www.manuelphp.com/php/function.flush.php
 }
?>
