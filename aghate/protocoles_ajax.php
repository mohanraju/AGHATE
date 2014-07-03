<?php include "./commun/include/admin.inc.php";
echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");
?>
	<script type="text/JavaScript">
		function CheckSelection(retval){
		  	if (retval==""){
		   	alert("Aucune valeur selectionnée!!!");
		   	return false;
		   }

			retval=retval.split("|");
			window.opener.document.main.addon_Protocole.value=retval[1];
			window.opener.document.main.duration.value=retval[2];
			window.opener.document.main.duration.focus();
			window.close();
		}
		
		
</script>
<title>Protocoles</title>
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
</head>
<body > 

		<?Php  
			include ("./commun/include/CustomSql.inc.php");
			$db = new CustomSQL($DBName);
			$sql_main="SELECT * FROM agt_pat WHERE nom != 'null'";
			$sql="select * from agt_protocole order by protocole ";
			$results=$db->select($sql);
	
		?>
 
 <table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
			 <thead class="fixedHeader">
         	<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
                 <th width='180'>Protocole</th>
                 <th width='50'>dur&eacute;e( en minutes)</th>
                 <th width='75'>debut de validit&eacute;</th>
                 <th width='95'>fin de validit&eacute;</th>

              </tr>              
          </thead>             
</table>          
<div style="overflow:auto;width:600px; height:150px; ">
<table width="580" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">

				   <?Php   
					for($i=0; $i < count($results);$i++) {
						$id_prt=$results[$i]['id_protocole'];	
						$protocole=$results[$i]['protocole'];
						$duree=$results[$i]['duree'];						
						$date_deb=Date_Mysql2Normal($results[$i]['date_deb']);
						$date_fin=Date_Mysql2Normal($results[$i]['date_fin']);
						$retval="'".$id_prt."|".$protocole."|".$duree."|".$date_deb."|".$date_fin."'";
									
						Print "<tr class=\"initial\" 
									onMouseOver=\"this.className='highlight'\" 
									onMouseOut=\"this.className='normal'\" 
									onClick=\"CheckSelection(".$retval.")\">";
					
						Print "<td width='200'>".$results[$i]['protocole']. " </td>";
						Print "<td width='50'>".$results[$i]['duree']. " </td>";
						Print "<td width='75'>".$date_deb. " </td>";
						Print "<td width='75'>".$date_fin. " </td>";
						Print "</tr>";

				 }
				if ($i==0){
					Print "<tr><td colspan='6'>Aucune patient trouvée </td></tr>";
				}
			  ?> 
</table>
</div>
<div name="test" align="center" style="width:600px;"><a href="#" onClick="window.close()">Fermer</a></div>
</body>




