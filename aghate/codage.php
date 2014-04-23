<?php
#########################################################################
#                        Codage.php                                			#
#                                                                       #
#                  Interface de saisi les codages PMSI                  #
#                  modifiée par mohanraju		                        		#
#                  Dernier modification : 29/01/2009                    #
# 						Accessible uniquement par les médecins										#
#########################################################################
?>
<link rel="stylesheet" href="./commun/style/style_autocomplet.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="./commun/js/AutoSuggest.js" charset="utf-8"></script>

<script  type="text/javascript" src="./commun/js/functions.js" language="javascript" charset="utf-8" ></script>
<script  type="text/javascript" >

	//: functionn add item
var ios = 0;
var aos = 0;

	function AppendListe(theSel, newVal ,listName )
	{
		  var theSel=document.getElementById(theSel);
		  var newText=document.getElementById(newVal).value;
		  newValue=newText;
		if (newText.length < 4 ){
			alert(' Minimum 4 carecteur ');
			return false;
			}
		if (theSel.length == 0) {
			var newOpt1 = new Option(newText, newValue);
		    theSel.options[0] = newOpt1;
		    theSel.selectedIndex = 0;
		}else{
			  theSel.focus();
			  theSel.options[0].selected = true ;
		  	
		    var selText = new Array();
		    var selValues = new Array();
		    var selIsSel = new Array();
		    var newCount = -1;
		    var newSelected = -1;
		    var i;
		    for(i=0; i<theSel.length; i++)
		    {
		      newCount++;
		      selText[newCount] = theSel.options[i].text;
		      selValues[newCount] = theSel.options[i].value;
		      selIsSel[newCount] = theSel.options[i].selected;
			      
				if (newCount == theSel.selectedIndex) {
			   	newCount++;
			      selText[newCount] = newText;
			      selValues[newCount] = newValue;
			      selIsSel[newCount] = false;
			      newSelected = newCount - 1;
				}
			}
		    for(i=0; i<=newCount; i++)
		    {
		      var newOpt = new Option(selText[i], selValues[i]);
		      theSel.options[i] = newOpt;
		      theSel.options[i].selected = selIsSel[i];
		    }
		}
		document.getElementById(newVal).value="";
		// put all in hiddn value
		hid_val="";
		for (var i=0; i<theSel.options.length; i++) {
		    hid_val=hid_val + theSel.options[i].value +"@";
		}
		document.getElementById(listName).value=hid_val;

  	}

	
	// funtion remove item
		function RemoveOption(the_string,hidden_val)
		{
			var theSel=document.getElementById(the_string);
			var reste="";
		   selIndex = theSel.selectedIndex;
		  if (selIndex != -1) {
		    for(i=theSel.length-1; i>=0; i--)
		    {
		      if(theSel.options[i].selected)
		      {
		        theSel.options[i] = null;
		      }else{
		      reste=reste + theSel.options[i].value +"@";
		      
		      }
		      
		    }
		    if (theSel.length > 0) {
		      theSel.selectedIndex = selIndex == 0 ? 0 : selIndex - 1;
		    }
		    document.getElementById(hidden_val).value=reste;
    
		  }
		// put all in hiddn value
		hid_val="";
		for (var i=0; i<theSel.options.length; i++) {
		    hid_val=hid_val + theSel.options[i].value +"@";
		}
		document.getElementById(hidden_val).value=hid_val;
		  
		}





	function popup(champ) {
		if (champ=='PMSI'){
			noip=document.getElementById('noip').value;
			fichier="popup_PMSI.php?param="+noip;
	    	mywindow=open(fichier,'myname','resizable=yes,top=50,left=400,width=700,height=470,status=yes,scrollbars=yes');			
		}else{
			fichier="popup_DP.php?champ="+champ 
	    	mywindow=open(fichier,'myname','resizable=yes,top=50,left=400,width=500,height=670,status=yes,scrollbars=yes');			
		}

	    mywindow.location.href = fichier;
	    if (mywindow.opener == null) mywindow.opener = self;
	}
// -->

</script>

<?php
// Reception des donnée concernant l'affichage du planning du domaine


# Now that we know all the data we start drawing it
// if ($was_del) echo "effac  else echo "OK";

// Cas page pointe sur elle-m, on recalcul $back
if (strstr ($back, 'pmsi_entry.php')) {
    $sql = "select start_time, room_id from agt_loc where id=". $id;
    $res = grr_sql_query($sql);
    if (! $res) fatal_error(0, grr_sql_error());
    if(grr_sql_count($res) >= 1) {
        $row = grr_sql_row($res, 0);
        $year = date ('Y', $row['0']); $month = date ('m', $row['0']); $day = date ('d', $row['0']);
        $back = $page.'.php?year='.$year.'&amp;month='.$month.'&amp;day='.$day;
        if (($_GET["page"] == "week") or ($_GET["page"] == "month") or ($_GET["page"] == "week_all") or ($_GET["page"] == "month_all"))
        {
        $back .= "&amp;area=".mrbsGetServiceIdByRoomId($row['1']);
        }
        if (($_GET["page"] == "week") or ($_GET["page"] == "month") )
        {
        $back .= "&amp;room=".$row['1'];
        }

    } else
        $back = "";
}



//====================================================
//Par mohan pour les sasie PMSI par les medecin
//====================================================
 
?>
<table width="500px" border="1" >
<tr><td>
  <fieldset><legend style="font-size:12pt;font-weight:bold">Patient	</legend>
  Nom : <br />
  Nom : <br />
  Date de Nais : <br />
  </fieldset>

 
  <fieldset><legend style="font-size:12pt;font-weight:bold">Sejour</legend>
  Date entree : <br />
  Date sortie : <br />
  Duree sej : <br />
  </fieldset>
 

 
 <fieldset><legend style="font-size:12pt;font-weight:bold">Diagnostiques</legend>

    <table width="95%" border="1" cellspacing="0" cellpadding="0">
      <tr>
        <td><input name="T_das" id="T_das" type="text"  value="<?php print $T_das?>" size="100"  onkeyup="callsuggestionbox('T_das','ajax_liste_das.php');"  /> </td>
      </tr>  
      <tr>
         <td><input type="radio" name="type_diag" value="DP">DP
          		<input type="radio" name="type_diag" value="DR">DR
          		<input type="radio" name="type_diag" value="DAS">DAS
          <img src="./commun/images/down.jpg" alt="Ajouter" width="14" height="14" hspace="0" vspace="0" border="0" onclick="AppendListe('select_das', 'T_das','das');"    />
          <img src="./commun/images/delete.jpg" alt="Liste DP" width="16" height="16" hspace="0" vspace="0" border="0" onclick="RemoveOption('select_das','das')"/>
          </td>
       </tr>
      <tr>
         <td><select name="select_das" size="4" id="select_das"  width="637" style="width: 637px"><?php print $p_das;?> </select>
          <input name="das" id="das" type="hidden" value="<?php print $das?>">          
          </td>
      </tr>
    </table>    

   </fieldset>
    
    
    
    
   <fieldset>
   <legend style="font-size:12pt;font-weight:bold">Actes</legend>
   <table>
   <tr>
    <td> 
     <input name="T_act" id="T_act" type="text"  value="<?php print $T_act?>" size="100"  onkeyup="callsuggestionbox('T_act','ajax_liste_act.php');"  />
     <img src="./commun/images/down.jpg" alt="Ajouter" width="14" height="14" hspace="0" vspace="0" border="0" onclick="AppendListe('select_act', 'T_act','actes');"    /><br />      
		<select name="select_act" size="4" id="select_act" width="637" style="width: 637px" >
			<?php print $p_actes;?>
        </select>
    <img src="./commun/images/delete.jpg" alt="Liste DP" width="16" height="16" hspace="0" vspace="0" border="0" onclick="RemoveOption('select_act','actes')"/>
			<input name="actes" id="actes" type="hidden" value="<?php print $actes?>" >    
    </td>
    
  </tr>
  </table>
  </fieldset>
 <tr> <td align="center"> <input type="submit" name="update" value="Save" /> <input type="submit" name="update" value="Simuler" /> </td></tr>
</td></tr>  
</table>  
<?php

 	echo "<input type=\"hidden\" name=\"noip\" id=\"noip\"  value=\"".$noip." \"> ";
	// si destinateur de mail pmsi no defenit dans l'area	
	if (strlen($mail) > 2){
 		echo "Mail destinateur(s) PMSI :  ".$mail;
		echo "<input type=\"hidden\" name=\"mail\" id=\"mail\"  value=\"".$mail." \"> "; 	 		
 	}
	echo "<input type=\"hidden\" name=\"service_name\" id=\"mail\"  value=\"".$service_name." \"> "; 	 		


?>
 <div id="suggestionbox"  class="prop" onmousemove="this.style.visibility='visible';" onmouseout="this.style.visibility='hidden';"> </div>
