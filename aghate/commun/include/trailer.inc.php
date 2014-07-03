<?php
#########################################################################
#                        trailer.inc.php                                #
#                                                                       #
#                 script de bas de page html                            #
#                                                                       #
#            Dernière modification : 21/04/2005                         #
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

// Affichage d'un lien pour format imprimable
if ( ( !isset($_GET['pview'])  or ($_GET['pview'] != 1)) and (isset($affiche_pview))) {
    echo '<p align="center"><a href="'. traite_grr_url($grr_script_name) .'?';
    if (isset($_SERVER['QUERY_STRING']) and ($_SERVER['QUERY_STRING'] != ''))
        echo htmlspecialchars($_SERVER['QUERY_STRING']) . '&amp;';
    echo 'pview=1" ';
    if (getSettingValue("pview_new_windows")==1) echo ' target="_blank"';
    echo '>' . get_vocab("ppreview") . '</a></p>';
}

?>
<hr>
<table width="100%"  border="0" align="center" cellspacing=0 >
  <tr><td align="center"   >
    <p style="font-size: x-small">Assistance en cas de dysfonctionnement <span style="font-weight: bold; font-style: italic">
	 <br><a mail="" >KANDIAH Divan</a></span> mail:divan.kandiah@sls.aphp.fr, tél:01.42.49.97.41 <span style="font-weight: bold; font-style: italic">
	 <br> SOUPRYANE Mohanraju </span>mail:mohanraju.souprayane@sls.aphp.fr tél:01.42.49.49.05<br />
</tr>
	
  <tr><td align="center"   >
    <p style="font-size: x-small">Num&eacute;ro de d&eacute;claration<span style="font-weight: bold; font-style: italic"> CNIL : 1414025</span><br />
    AGHATE : Agenda de Gestion des hospitalisations ambulatoires et ThErapeutiques,
      Il s'agit d'une adaptation d'une application <a href="http://grr.mutualibre.org/">GRR</a> sous licence GPL <br />
  &copy; 2010 DBIM, H&ocirc;pital Saint Louis, Paris 10. 
      Cr&eacute;ation et adaptation par MOHANRAJU Sp sous la direction de Pr. Sylvie CHEVRET. <br />
      en coop&eacute;ration avec l'&eacute;quipe du Pr LEMANN, service H&eacute;pato Gastro Ent&eacute;rologie H&ocirc;pital Saint louis, AP-HP Paris. </p>
    <p style="font-size: x-small">&nbsp;</p></td>
</tr>
</table>
</BODY>
</HTML>
