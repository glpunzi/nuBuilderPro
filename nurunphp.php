<?php require_once('nucommon.php'); ?>
<?php

//NEEDS AUTHENTICATION

if(isset($_GET['i'])) {
    
	$jsonID         = $_GET['i'];
	$t              = nuRunQuery("SELECT deb_message AS json FROM zzzsys_debug WHERE zzzsys_debug_id = ? ", array($jsonID));
	$r              = db_fetch_object($t);
	$JSON           = json_decode($r->json);
	$DATA           = $JSON->slp_php;
	$TABLE_ID       = nuTT();
	$hashData       = nuBuildHashData($JSON, $TABLE_ID);
	$php            = nuReplaceHashes($DATA, $hashData);
	
	nuRunQuery("DELETE FROM zzzsys_debug WHERE zzzsys_debug_id = ? ", array($jsonID));
	
} else if(isset($_GET['r'])){
    
	$t 		= nuRunQuery("SELECT slp_php AS json FROM zzzsys_php WHERE slp_code = '".$_GET['r']."'");
	$r              = db_fetch_object($t);
	$php 		= $r->json;
        
}

eval($php);                                                                            //-- run php code

?>
