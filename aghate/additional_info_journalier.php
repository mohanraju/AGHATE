<?php 
include "./commun/include/admin.inc.php";
include "./commun/include/CustomMysql.php";
include "./commun/include/CommonFonctions.php";
$db = New CustomMySQL($DBName);

if(strlen($service_id)>0)
	$area=$service_id;
else
	$area=$area;
$grr_script_name = "additional_info_journalier.php";
if (empty($area)) $area = get_default_area();

if (strlen($area)==0)
{
	echo "<h1>Service invalde </br>Veuillez recommencez SVP !!!</h1>";
	exit;
} 

$update_info='1';	
//===============================================
// controle des resa presents avant fermée une lit
//====================================
if ($fermee=='1'){
	# Define the start and end of the day.
	list($year,$month,$day)=explode("-",$date);
	$am7=mktime(1,0,0,$month,$day,$year);
	$pm7=mktime(23,0,0,$month,$day,$year);
	// count nombre de patient dans le journée
	$sql_nbr_pat = "SELECT agt_room.id, start_time, end_time, name, agt_loc.id, type, statut_entry, agt_loc.description
	   FROM agt_loc, agt_room
	   WHERE agt_loc.room_id = agt_room.id
	   AND service_id = '".protect_data_sql($area)."'
	   AND start_time < ".($pm7)." AND end_time > $am7 ORDER BY start_time";
	$res= $db->select($sql_nbr_pat);
	if ( count($res) > 0) {
		$err_msg= "Vous devez supprimez les convocations avent de fermer  LITs";
	  $update_info='0';
	}	
}
//---------------------------------------------------
// MODIFICATION
//---------------------------------------------------

if(($tmode=="UPDATE") and ($update_info=='1') ){
	$sql="update agt_loc_parametres set medecin_id='$medecin',details='$details', service_fermee='$fermee' 
	where id ='$id'";

	$results=$db->update($sql);
	echo "modification enregisteré";
	echo "<script>	window.opener.document.location.reload();window.close();</script>";
}
if(($tmode=="INSERT") and ($update_info=='1') ){
	$sql="insert into agt_loc_parametres  (date,medecin_id,details,service_id,service_fermee) values ('$date','".protect_data_sql($medecin)."','".protect_data_sql($details)."','$area','$fermee')";
	$results=$db->insert($sql);
	echo "Enregisterment ok";
	echo "<script>	window.opener.document.location.reload();window.close();</script>";			
}


// get defaul informations to affiche

//defalult selected values	
$sql_="select *  FROM agt_loc_parametres
		where date ='$date' and agt_loc_parametres.service_id='".protect_data_sql($area)."'";
$results=$db->select($sql_);

$details=$results[0]['details'];
$id=$results[0]['id'];
$chk_date=$results[0]['date'];
$fermee=$results[0]['service_fermee'];
$medecin=$results[0]['medecin_id'];	
//echo $sql_;		

// medecind liste
$sql_="select *  FROM agt_medecin
		where   agt_medecin.service_id='".protect_data_sql($area)."'";
	
$results=$db->select($sql_);
$med_list="<select name='medecin'> <option value='0'>Choisissez </option> ";
for($i=0;$i<count($results);$i++){
	if ($medecin==$results[$i]['id_medecin'])
		$med_list.="<option  value='".$results[$i]['id_medecin']."' selected>".$results[$i]['titre']." ".$results[$i]['nom']."</option>";		
	else
		$med_list.="<option  value='".$results[$i]['id_medecin']."'>".$results[$i]['titre']." ".$results[$i]['nom']."</option>";
	}
$med_list.="</select>";


//invese date
list($y,$m,$d)=explode("-",$date);
$_date="$d/$m/$y";


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html>
<head>
<link rel="stylesheet" href="./commun/style/themes/forestier/css/style.css" type="text/css" />
<link rel="stylesheet" href="./commun/style/div_top_fix.css" type="text/css" /><link href="./commun/style/admin_grr.css" rel="stylesheet" type="text/css" /><style type="text/css">div#fixe   { position: fixed; bottom: 5%; right: 5%;}</style><link rel="SHORTCUT ICON" href="./commun/images/favicon.ico" />
<title>Additional Information  Journalier</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta NAME="Robots" content="noindex" />
</head>


<link rel="stylesheet" href="./commun/style/tbl_scroll.css" type="text/css">
<link rel="stylesheet" href="./commun/style/bootstrap.css" type="text/css">
<script type="text/javascript" src="commun/ckeditor/ckeditor.js"></script>
<script src="./commun/js/jquery-1.11.0.js"></script>
<script src="./commun/js/jquery-1.11.0.js"></script>
<script type="text/javascript">
	var tmode="<?php print $tmode;?>"	;
	var update_info="<?php print $update_info;?>"	;

</script>
<style>

	/* Style the CKEditor element to look like a textfield */
	.cke_textarea_inline
	{
		padding: 10px;
		height: 100px;
		overflow: auto;

		border: 1px solid gray;
		-webkit-appearance: textfield;
	}

</style>
	
<body bgcolor='grey'> 

<?php
if (strlen($chk_date) < 3){
	$tmode="INSERT";
}else{
	$tmode="UPDATE";
}

 
?>

 <h3 align='center'> Information journalière pour l'équipe</h3>
 <form role="form"  method='GET'>
  <div class="form-group">
    <label for="exampleInputEmail1">Médecin responsable de la journée </label>
     <?php print $med_list;?>
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Informations de la journée :</label>
    <textarea   name="details" ROWS="3" cols="25"  class="form-control"  placeholder="Déscription"><?php echo $details?></textarea>
  </div>

   <input type="submit" class="btn btn btn-success" name="Enregistrer" value="ENREGISTRER">
	<input type="hidden" name="tmode" value="<?php echo $tmode?>">	        
	<input type="hidden" name="id" value="<?php echo $id?>">	  
	<input type="hidden" name="date" value="<?php echo $date?>">	  	
	<input type="hidden" name="area" value="<?php echo $area?>">
</form>

<script>
	CKEDITOR.inline( 'details' );
	
</script> 
</BODY>
</HTML>	
