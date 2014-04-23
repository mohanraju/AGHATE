<?php
/*
############################################################
CHANGER HOPITAL 
POPUP qui va afficher dans le page 
A inclure dans le BODY

ce script lie d'autres scripts 
ajax_changer_hopital.js
ajax_changer_hopital.php
############################################################
*/

require_once("../config/config.php");
require_once("../commun/include/CommonFonctions.php");
require_once("../commun/include/ClassMysql.php");
include_once("../commun/include/ClassHtml.php");

// init les objets
$db=new Mysql();
$html= new Html($db);
$site=$_SESSION['site'];
?>



<div id="IdChangerHopital" title="Changer Hopital">
	<form>
		<fieldset>
		<label for="name">hopital</label>
      <tr> 
        <td><?php Print $html->InputSelect($html->__Liste['ListeSites'],'site',$site); ?></td>                
      </tr>
      <tr> 
        <td align='left'>
        <div id="ErreurMessages">
        </div>
        </td>
      </tr>
             		
		</fieldset>
	</form>
</div>
