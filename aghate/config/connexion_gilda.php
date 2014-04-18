<?php
global $G_db_conn;
// connextion string Saint louis
$GILDA_SLS="(DESCRIPTION =
       		(ADDRESS_LIST =
        			(ADDRESS =
           			(COMMUNITY = sls.ap-hop-paris.fr)
           			(PROTOCOL = TCP)
           			(HOST = o-gilda-b1)
           			(PORT = 10501)
        			)
       		)
       		(CONNECT_DATA =
        			(SID = GIP1SLS)
       		)
    		)";   
    		
    		
$ConnGilda = ocilogon("consult","consult",$GILDA_SLS);
if (!$ConnGilda)
	{
  	echo 'Erreur connecxion GILDA!!!.' ;
    exit ;
	}
	/*
  else{
  	echo "connextion success gilda ";
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

	
		// vérify  connexion Gilda
		if (!isset($ConnOra))
		{
			echo  "<br />GILDA::Erreur connexion Oracle !!!";
			return false;
		}
		// executte qry
		$result = oci_parse($ConnOra, $sql);
		
		// vérify les resulat d'exec
		if (!$result){
   		$oerr = oci_error($result);
   		echo  "<br />Gilda::SQL Fetch Erreur :".$oerr["message"];
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
			echo  "<br />Gilda::Erreur Executing qry :".$sql;
		}
	
	}		
	  
?>
