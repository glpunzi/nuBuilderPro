<?php require_once('nucommon.php'); ?>
<?php

if ( isset($_GET['p']) ) {

	$values = array($_GET['p']);
        $sql   = "SELECT slp_php AS eval FROM zzzsys_php WHERE slp_code = ? AND slp_nonsecure = '1' ";

	$rs  = nuRunQuery($sql, $values);
	$num = db_num_rows($rs);

	if ( $num == 1 ) {
		$obj = db_fetch_object($rs);
		eval($obj->eval); 
	} else {
		echo "Request is not allowed";
	}

} else {

	echo "Request format is invalid";
}	

?>
