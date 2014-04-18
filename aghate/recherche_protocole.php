<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<script type="text/JavaScript">
		//retuen selected value
		function CheckSelection(retval){
		  	if (retval==""){
		   	alert("Aucune valeur selectionnée!!!");
		   	return false;
		   }
			retval=retval.split("|");
			window.opener.document.forms["main"].protocole.value=retval[1];
			window.opener.document.main.duration.value=retval[2];
			
			//window.close();
		}
		
</script>
<link rel="stylesheet" href="./style_scroll.css" type="text/css">
 <style type="text/css">
	  html>body tbody.scrollContent {
	
	 height: 262px;
	 overflow: auto;
	 width: 650px%
	 }
	
	 /* make TD elements pretty. Provide alternating classes for striping the table */
	 tbody.scrollContent td, tbody.scrollContent tr.normalRow td {
	 background: #FFF;
	 border-bottom: none;
	 border-left: none;
	 border-right: 1px solid #CCC;
	 border-top: 1px solid #DDD;
	 padding: 2px 3px 3px 4px
	 }
	
	 tbody.scrollContent tr.alternateRow td {
	 background: #EEE;
	 border-bottom: none;
	 border-left: none;
	 border-right: 1px solid #CCC;
	 border-top: 1px solid #DDD;
	 padding: 2px 3px 3px 4px
	 }

	  /* define height and width of scrollable area. Add 16px to width for scrollbar          */
	 div.tableContainer {
	 clear: both;
	 border: 1px solid #963;
	 height: 220px;
	 overflow: auto;
	 width: 600px
	 }

	  /* make the A elements pretty. makes for nice clickable headers                */
	 thead.fixedHeader a, thead.fixedHeader a:link, thead.fixedHeader a:visited {
	 color: #FFF;
	 display: block;
	 text-decoration: none;
	 width: 100%
	 }
 
 </style>
<title>Protocoles</title>
</head>
<body > 

		<?Php  
			include ("./commun/include/CustomSql.inc.php");
			$db = new CustomSQL($DBName);
			$showtable = true;
			$sql="SELECT * FROM agt_protocole order by protocole";
			$results=$db->select($sql);			
		
		?>
 <div id="tableContainer" class="tableContainer">
			 <table border="0" cellpadding="0" cellspacing="0" width="100%" class="scrollTable">
			 <thead class="fixedHeader">
         	<tr  id="idHeader" bgcolor=#A8BBCA  height=30>
                 <th>Protocole</th>
                 <th >durée</th>
                 <th >Date debut de validité</th>
                 <th >Date debut de validité</th>
              </tr>              
          </thead>             
         <tbody class="scrollContent">
				   <?Php   
					for($i=0; $i < count($results);$i++) {
						if($class_name=="ligne1"){
							$class_name="ligne2";
						}else{
							$class_name="ligne1";
						}
						$id_prt=$results[$i]['id_protocole'];	
						$protocole=$results[$i]['protocole'];
						$duree=$results[$i]['duree'];						
						$date_deb=$results[$i]['date_deb'];
						$date_fin=$results[$i]['date_fin'];
						$retval="'".$id_prt."|".$protocole."|".$duree."|".$date_deb."|".$date_fin."'";
						$href_protocole="<a href='#' onClick=\"CheckSelection(".$retval.")\"> $protocole</a>";
						if (($i % 2)==0)
							$cl="normalRow";
						else
							$cl="alternateRow";											 
				 ?>                        
                  <tr height=22 class="normalRow"  >
                      <td><?Php   print $href_protocole;?></td>
				      <td ><?Php  print $duree;?> </td>
				      <td ><?Php   print IsEmpty($results[$i]['date_deb']);?></td>
				      <td ><?Php   print IsEmpty($results[$i]['date_fin']);?></td>
			    </tr>
				   <?Php     
				 }
				if ($i==0){
					Print "<tr><td colspan='6'>Aucun patient trouvé </td></tr>";
				}
			  ?> 
           </table>
        </div>
            
</body>
</html>




