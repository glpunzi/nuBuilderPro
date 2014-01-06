<?php require_once('nucommon.php'); ?>
<?php
$i                    = $_GET['i'];

$s = "SELECT * FROM zzzsys_file WHERE sfi_code = '$i' ";
$t = nuRunQuery($s);
$r = db_fetch_object($t);

$type	     = "'Content-type: $r->sfi_type'";

Header($type); 
print $r->sfi_blob; 

?>
