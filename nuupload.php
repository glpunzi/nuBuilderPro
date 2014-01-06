<?php

require_once('nucommon.php'); 

$uploaddir = 'tmp/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)){
  $status = "File uploaded ready for saving..\n";
}else{
   $status = "Upload failed";
}

$name     = $_FILES['userfile']['name'];
$type     = $_FILES['userfile']['type'];
$tmp_name = $_FILES['userfile']['tmp_name'];
$size     = $_FILES['userfile']['size'];

$scr = "

<script>

function nuloadstats(){

    parent.document.getElementById('sfi_tmp_name').value = '$tmp_name';
	parent.document.getElementById('sfi_name').value = '$name';
	parent.document.getElementById('sfi_type').value = '$type';
	parent.document.getElementById('sfi_size').value = '$size';
	parent.nuSetEdited();
}

</script>

<html><body onload='nuloadstats()'>$status</body></html>

";

print $scr;
?> 
