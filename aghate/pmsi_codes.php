<?php
include ("./commun/include/CustomSql.inc.php");
$db = new CustomSQL($DBName);

	$service=$_SESSION["URM"];

	$sql="SELECT  tag FROM grr_top100 group by tag"; // get all all group_liste (DP,DR,DAS...)
	$res=$db->select($sql);

	
	for($i=0;$i<count($res);$i++){
		$c_grp=$res[$i]['tag'];
		$sqltemp="SELECT  code, description FROM grr_top100 where tag='".$c_grp."' and service='$service' order by code";

		$restemp=$db->select($sqltemp);
		$arr_val="";
		for($k=0;$k<count($restemp);$k++){
			$arr_val.=$restemp[$k]['code']."|".$restemp[$k]['code'].":".$restemp[$k]['description']."||";
		}	
		if(strlen($arr_val) > 1)	{
			$arr_val=substr($arr_val,0,strlen($arr_val)-1); // suprimmer le dernière vercule
			$$c_grp= explode("||",$arr_val);
		}

		
	}

function ModelRadio($modelname1,$VariableName,$default,$onchengemodule="")	{
		$modelname=&$modelname1;
		$res="";
		$taile=count($modelname);
	 	for($i=0;$i < $taile ;$i++){
	 		list($val,$lib)=explode("|",$modelname[$i]);
	 		if ($default==$val)$selected=" checked "; else $selected="  ";
	 		if (strlen(trim($onchengemodule))> 0) $onchange="onclick=\"$onchengemodule(this.value)\"";else $onchange="";
	 		$res.="<input type='radio'  name='$VariableName'  value='$lib' $selected   $onchange >$lib <br />";
		}
		return $res;
}

function ModelSelect($modelname1,$VariableName,$default,$onchengemodule="")	{
		$modelname=&$modelname1;
		if (strlen(trim($onchengemodule))> 0)
	 		$res= "<select    class='inputbox' name=".$VariableName." onchange='".$onchengemodule."(this.value)'>";
		else
	 		$res= "<select    class='inputbox' name=".$VariableName.">";
	 		
	 	for($i=0;$i < count($modelname);$i++){
	 		list($val,$lib)=explode("|",$modelname[$i]);			 	
	 		if ($default==$val)
		 		$res.= "<option value='$lib' selected>".$lib."</option>";
		 	else
		 		$res.= "<option value='$lib' >".$lib."</option>";
		 	
		}
		 		$res.= "</select>";
		return $res;
}

function ModelCheckBox($modelname1,$VariableName,$default,$onchengemodule="")	{
		$modelname=&$modelname1;
	 	$res= "";
	 	for($i=0;$i < count($modelname);$i++){
	 		list($val,$lib)=explode("|",$modelname[$i]);			
	 		if ($default==$val)$selected=" checked "; else $selected="  ";
	 		if (strlen(trim($onchengemodule))> 0)
				$res.="<input type='checkbox' name='$VariableName' id='$VariableName' value='$lib' $selected  onchange='".$onchengemodule."(this.value)' >".$lib."<br />";		 		
		 	else
				$res.="<input type='checkbox' name='$VariableName' id='$VariableName' value='$lib'  $selected >".$lib."<br />";		 		
		 	
		}

		return $res;
}
 



function GetReponse($modelname1,$position)	{
	$modelname=&$modelname1;	
	$res= $modelname[$position];
	for ($l=0;$l<count($modelname);$l++)	{
		list($val,$lib)=explode("|", $modelname[$l]);
		if ($val==$position)
			return $lib;
	}	
	return "Inconnu";
}

 	
?>
