<?php 

	require_once('nucommon.php'); 
	
	$response                     = array();
	$response['DATA']             = '';
	$response['SUCCESS']          = false;
	$response['ERRORS']           = array();
	$GLOBALS['ERRORS']            = array();

	$hashData                     = nuHashData();
	$code                         = $_POST['nuWindow']['phpCode'];
	
	$sql                          = "SELECT * FROM  zzzsys_php WHERE slp_code = ?";
	$t                            = nuRunQuery($sql, array($code));
	$r                            = db_fetch_object($t);
	
	if(nuPHPAccess($r->zzzsys_php_id)){
	
		$e                        = nuReplaceHashes($r->slp_php, $hashData);
		eval($e); 
		$response['DATA']         = $nuParameters;
		
		if($nuError != ''){
			$response['ERRORS'][] = $nuError;
		}
		
	}else{
		$response['ERRORS'][]     = "Access denied to PHP - ($r->slp_code)";
	}

	print json_encode($response);

?>
