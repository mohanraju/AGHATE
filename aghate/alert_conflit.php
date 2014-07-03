<?php 
	include "./commun/include/admin.inc.php";
	echo begin_page(get_vocab("mrbs").get_vocab("deux_points").getSettingValue("company"),"with_session");
	$session_user = $_SESSION['login'];
   $session_statut = $_SESSION['statut'];
	include "./commun/include/CustomSql.inc.php";
	$db = new CustomSQL($DBName);	
	$list_areas =$db->get_areas_allowed($session_user,$session_statut);	
	$showtable = false;
	$sql_ora="";
	$sql_local="";
	if (strlen($list_areas) >2)	{
		$sql="SELECT name,start_time,desc_cause,agt_room.room_name 
				from grr_nonvenu,agt_room 
				WHERE grr_nonvenu.room_id = agt_room.id
				AND reprogram is NULL 
				and agt_room.service_id in ($list_areas) 
				ORDER by start_time desc";
		$data=$db->select($sql);	
	}else{
		echo "<script type=\"text/JavaScript\"> CloseMe()</script>";
	
	}

?>

<title>Recherche Patients</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="style/style.css" type="text/css">
<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<style type="text/css">
<!--
.Style1 {
	color: #0000FF;
	font-weight: bold;
	font-style: italic;
	font-size: large;
}
-->
</style>
</head>
<script type="text/JavaScript">
	function upperCase(Idval){
		var x=document.getElementById(Idval).value
		document.getElementById(Idval).value=x.toUpperCase()
	}
	//retuen selected value
	function CloseMe(){
		window.close();
	}
	//retuen selected value
		
</script>
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
<body bgcolor="#FFFFFF" text="#000000">

<div align="center"><span class="Style1">Liste des patients Non venu et non reprogramées</span></div>
<form>
  
		<table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFCC">
			<tr id="idHeader" bgcolor=#A8BBCA  height=30>
				<th width='250'>Patient</th>
            <th width='75'>Salle</th>				
            <th width='75'>Date</th>
            <th width='100'>Salle</th>
            </tr>              
		</table>          
		<div style="overflow:auto;width:700px; height:150px; ">
		<table width="680" border="1" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
		  <?Php  
		  for($i=0;$i < count($data);$i++){  		
		  		$name=$data[$i]['name'];    			    			    			    			    		
				$date=date("d/m/Y à H:i",$data[$i]['start_time']);
				$motif=$data[$i]['desc_cause'];
				$salle=$data[$i]['room_name'];
		  	 ?>
					<tr class="initial"                       
								onMouseOver="this.className='highlight'"
								onMouseOut="this.className='normal'"    	 >
				    <td width='250'> <?Php  Print $name;?> </td>
				    <td width='75'>  <?Php  Print $salle;?></td>
				    <td width='75'>  <?Php  Print $date;?></td>
				    <td width='100'> <?Php  Print $motif;?></td>
				  </tr>
		  <?Php    }?>

		</table>
</div>

<?php if($i==0)
		echo "<script type=\"text/JavaScript\"> CloseMe()</script>";

?>
<div style="overflow:auto;width:700px;  ">
		  		<div  align="center" ><input name="fermer" type="button" class="form_bouton_bleu" value="Fermer" onClick="CloseMe()"></div>

	</div>		
	
</form>
</body>
</html>
