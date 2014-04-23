<?php
global $G_db_conn;
// connextion string Saint louis
$SIMPA_SLS="(DESCRIPTION =
					   (ADDRESS_LIST =
							(ADDRESS =
						   (COMMUNITY = sls.ap-hop-paris.fr)
						   (PROTOCOL = TCP)
						   (HOST = o-simpa-b1)
						   (PORT = 10505)
							)
						  )
						  (CONNECT_DATA =
							(SID = SIP1SLS)
						   )
						)";   
    		
    		
$ConnSimpa = ocilogon("consult","consult",$SIMPA_SLS);
if (!$ConnSimpa)
	{
  	echo 'Erreur connecxion SIMPA!!!.' ;
    exit ;
	}
	/*
  else{
  	echo "connextion success SIMPA ";
	}
  */   
  
	/*
	==============================================================================
	Function OraSelect(ConnextionOracleDejaOvert , Requeete) 
	retourn les resulata sous form de tableau
	==============================================================================
	*/
	function OraSelect($sql,$ConnOra){
		if ($sql=="")
		{
			return false;
		}

	
		// vérify  connexion SIMPA
		if (!isset($ConnOra))
		{
			echo  "<br />SIMPA::Erreur connexion Oracle !!!";
			return false;
		}
		// executte qry
		$result = oci_parse($ConnOra, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br />SIMPA::SQL Fetch Erreur :".$oerr["message"];
   		return false;
		}
		
		if (oci_execute($result)){
			$row=0;	
			$data=array();
			while(oci_fetch($result))
			{
				$ncols = oci_num_fields($result);
				$c_line=array();
				for ($i = 1; $i <= $ncols; $i++) 
				{
					$column_name  = oci_field_name ($result, $i);
		      $column_value = oci_result($result, $i);
	       	$c_line[$column_name]=$column_value;
	   		}
				$data[$row]=$c_line;
				$row++;
			}
		

			return $data;	

		}
		else
		{
			echo  "<br />SIMPA::Erreur Executing qry :".$sql;
		}
	
	}		
	  
?>
