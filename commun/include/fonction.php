<?php

function WriteJson($array,$default=true)
{
	
	$nbElement = count($array);
	$compteur = 0;
	$json='{';

	foreach($array as $cle => $valeur)
	{
		if(is_array($valeur))
			$json.='"'.$cle.'":'.WriteJson($valeur,false);
		else
			$json.='"'.$cle.'":"'.$valeur.'"';
			
		if(++$compteur != $nbElement)
			$json.=',';
	}
	$json.='}';
	
	if($default)
		$json="[".$json."]";
	
	
	return $json;
	
	
}

function ReadJson($json)
{
	$json=str_replace('"','',$json);
	if($json[0] == "[")
		$json=substr($json,2,-2);
	else
		$json=substr($json,1,-1);
		
	$json=explode(",",$json);
	
	for($i=0;$i<count($json);$i++)
	{
		$tmp=explode(":",$json[$i]);
		
		for($j=0;$j<(count($tmp))/2;$j++)
		{	

			if(($tmp[$j+1][0] != "{") && ($tmp[$j+1][0] != "[") )
			{
				
				$array[$tmp[$j]]=$tmp[$j+1];
			}
			else
			{
				$compteurOuvert=0;
				$compteurFermee=0;
				$first=false;
				$newJson="";
				$last=$tmp[$j];
				$k=$j+1;
				$l=$i;

				do
				{	
					if($k == count($tmp))
					{
						$l++;
						$k=0;
						$tmp=explode(":",$json[$l]);
					}
					
					if($tmp[$k][0] == "{")
					{
						$compteurOuvert++;
						$newJson.=$tmp[$k];
							$newJson.=":";
					}
					elseif($tmp[$k][strlen($tmp[$k])-1] == "}")
					{
						$nbOccu=substr_count($tmp[$k],"}");
						$compteurFermee+=$nbOccu;
						$newJson.=$tmp[$k];
						if($compteurFermee != $compteurOuvert)
							$newJson.=",";
					}
					else
					{
						if($newJson[strlen($newJson)-1] == ",")
							$newJson.=$tmp[$k].":";
						elseif($newJson[strlen($newJson)-1] == ":" && ($compteurFermee != $compteurOuvert))
							$newJson.=$tmp[$k].",";
					}
					$k++;
				}while($compteurFermee != $compteurOuvert);
				echo $last."<br>".$newJson;
				$array[$last]=ReadJson($newJson);
				$j=$k;
				$i=$l;
			}
		}
	}
	
	return $array;
	
}


?>
