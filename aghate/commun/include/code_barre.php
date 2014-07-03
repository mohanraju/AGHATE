<?php
function GenerateCodeBarre($nip){
//echo "<br />".$nip;
	// recup?tion des variables
	
	//$CODE_TXT = isset($_GET['text']) ? $_GET['text'] : '2536545841';
	//$CODE_HGT = isset($_GET['height']) ? $_GET['height'] : 80;
	//$CODE_TTX = isset($_GET['write']) ? $_GET['write'] : 0;
	//$CODE_LNG = isset($_GET['len']) ? $_GET['len'] : 2;
	
	$CODE_TXT =$nip;
	$CODE_HGT =80;
	$CODE_TTX =0;
	$CODE_LNG =2;
	
	
	
	
	
	if ($CODE_TXT) {

		$tab=array();
		$tab[0]='212222';
		$tab[1]='222122';
		$tab[2]='222221';
		$tab[3]='121223';
		$tab[4]='121322';
		$tab[5]='131222';
		$tab[6]='122213';
		$tab[7]='122312';
		$tab[8]='132212';
		$tab[9]='221213';
		$tab[10]='221312';
		$tab[11]='231212';
		$tab[12]='112232';
		$tab[13]='122132';
		$tab[14]='122231';
		$tab[15]='113222';
		$tab[16]='123122';
		$tab[17]='123221';
		$tab[18]='223211';
		$tab[19]='221132';
		$tab[20]='221231';
		$tab[21]='213212';
		$tab[22]='223112';
		$tab[23]='312131';
		$tab[24]='311222';
		$tab[25]='321122';
		$tab[26]='321221';
		$tab[27]='312212';
		$tab[28]='322112';
		$tab[29]='322211';
		$tab[30]='212123';
		$tab[31]='212321';
		$tab[32]='232121';
		$tab[33]='111323';
		$tab[34]='131123';
		$tab[35]='131321';
		$tab[36]='112313';
		$tab[37]='132113';
		$tab[38]='132311';
		$tab[39]='211313';
		$tab[40]='231113';
		$tab[41]='231311';
		$tab[42]='112133';
		$tab[43]='112331';
		$tab[44]='132131';
		$tab[45]='113123';
		$tab[46]='113321';
		$tab[47]='133121';
		$tab[48]='313121';
		$tab[49]='211331';
		$tab[50]='231131';
		$tab[51]='213113';
		$tab[52]='213311';
		$tab[53]='213131';
		$tab[54]='311123';
		$tab[55]='311321';
		$tab[56]='331121';
		$tab[57]='312113';
		$tab[58]='312311';
		$tab[59]='332111';
		$tab[60]='314111';
		$tab[61]='221411';
		$tab[62]='431111';
		$tab[63]='111224';
		$tab[64]='111422';
		$tab[65]='121124';
		$tab[66]='121421';
		$tab[67]='141122';
		$tab[68]='141221';
		$tab[69]='112214';
		$tab[70]='112412';
		$tab[71]='122114';
		$tab[72]='122411';
		$tab[73]='142112';
		$tab[74]='142211';
		$tab[75]='241211';
		$tab[76]='221114';
		$tab[77]='413111';
		$tab[78]='241112';
		$tab[79]='134111';
		$tab[80]='111242';
		$tab[81]='121142';
		$tab[82]='121241';
		$tab[83]='114212';
		$tab[84]='124112';
		$tab[85]='124211';
		$tab[86]='411212';
		$tab[87]='421112';
		$tab[88]='421211';
		$tab[89]='212141';
		$tab[90]='214121';
		$tab[91]='412121';
		$tab[92]='111143';
		$tab[93]='111341';
		$tab[94]='131141';
		$tab[95]='114113';
		$tab[96]='114311';
		$tab[97]='411113';
		$tab[98]='411311';
		$tab[99]='113141';
		$tab[100]='114131';
		$tab[101]='311141';
		$tab[102]='411131';
		$tab[103]='211412'; // Start A
		$tab[104]='211214'; // Start B
		$tab[105]='211232'; // Start C
		$tab[106]='2331112'; // STOP
		
		// Initialisation du code barre par le caract? "Start B"
		$barcsum = 104;
		$barcode = $tab[104];
		
		// nombre de caract? de la cha?, servira plusieurs fois dans le code
		$size_cara =strlen($CODE_TXT);
		
		// construction du code barre et calcul du cheksum
		$codecar=0;
		for($i=0; $i<$size_cara; $i++) {
		$j++;
		// le code du caract? en code B est son code ASCII moins 32
		$codecar=ord($CODE_TXT{$i})-32;
		$barcode.=$tab[$codecar];
		$barcsum+=$codecar*$j;
		}
		
		// calcule du modulo du checkum
		$barcsum = fmod($barcsum, 103);
		// ajout du checksum au code barre
		$barcode.= $tab[$barcsum];
		// ajout du code de STOP
		$barcode.= $tab[106];
		
		// taille de la police de carat?
		$font_taille = 5;
		
		
		//longueur en pixel du code barre
		//(caract?s de la cha? + le caract? de d?t + celui de cheksum + les espaces de d?t et fin)
		//fois 11 modules
		//+ les 13 modules du STOP,
		//le tout multipli?ar l'?isseur demand?
		
		$barcode_longueur=(($size_cara+4)*11+13)*$CODE_LNG;
		
		// calcul de la hauteur de l'image en fonction du texte ?crire ou non
		$IM_HGT = $CODE_HGT;
		if ($CODE_TTX!=true) {$IM_HGT-=14;}
		
		// cr?ion de la zone image
		$im = imagecreate($barcode_longueur,$IM_HGT);
		
		// initialisation des param?es graphiques pour le dessin et l'?iture
		$COL_White = imagecolorallocate($im, 255,255,255);
		$COL_Black = imagecolorallocate($im, 0,0,0);
		$font_hauteur = imagefontheight($font_taille);
		$font_largeur = imagefontwidth($font_taille);
		$CODE_HGT = $CODE_HGT-$font_hauteur;
		$xpos = 0;
		$COLOR = $COL_Black;
		
		// dessin zone blanche de d?t
		imagefilledrectangle ( $im, $xpos, 0, $xpos+11*$CODE_LNG, $CODE_HGT, $COL_White );
		$xpos+=11*$CODE_LNG;
		
		// dessin du code barre
		$nb=strlen($barcode);
		for($j=0; $j<$nb; $j++)
		{
		$TMP_CODE = $barcode{$j};
		imagefilledrectangle ( $im, $xpos, 0, $xpos+$TMP_CODE*$CODE_LNG, $CODE_HGT, $COLOR );
		$xpos+=$TMP_CODE*$CODE_LNG;
		$COLOR = ($COLOR!=$COL_Black) ? $COL_Black : $COL_White;
		}
		
		// dessin zone blanche de fin
		imagefilledrectangle ( $im, $xpos, 0, $xpos+11*$CODE_LNG, $CODE_HGT, $COL_White );
		
		// ajout du texte si n?ssaire
		if ($CODE_TTX==true) {
		imagestring($im,$font_taille,($barcode_longueur-$font_largeur*$size_cara)/2,$CODE_HGT,$CODE_TXT,$COL_Black);
		}
		// envoi du header et de l'image, suivi de sa destruction pour lib?r la m?ire :)
	 //header('Content-type: image/gif');
		$fic='./commun/images/codes/'.$CODE_TXT.'.gif';
		//imagejpeg($im, $fic);
		 imagegif($im,$fic);
		imagedestroy($im);
	}
}
?>
