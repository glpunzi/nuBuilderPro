<?php require_once('nucommon.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>
<title>Upload</title>
<link rel="stylesheet" href="jquery/jquery-ui.css" />
<script src="jquery/jquery-1.8.3.js" type='text/javascript'></script>
<script src="jquery/jquery-ui.js" type='text/javascript'></script>
<?php
	print nuGetHeader();     
?>
</head>
<body>
<div id="uploadContainer">
<br>
<?php
	if( $_FILES['uploadedfile']['name'] == '' ) { 
?>
	<script>
  	     $(document).ready(function() {
             	document.body.style.backgroundColor = '#EDEDED';
             });
	     function uploaderAction() {
             	$("#upload-file-info").html($("#upload-input").val()); 
		$("#upload-start-button").css({"visibility":"visible"});
	    }
	    function uploaderProgress() {	
		$('#upload-start-button').before('<img src="ajax-loader.gif" style="display: none;" alt="loading" id="loading_image">');
		$('#loading_image').show();
		$("#upload-start-button").css({"visibility":"hidden"}); 
		$('#uploader-form').submit();
    		return true;
	   }	
	</script>
	<form id='uploader-form' name='upload' enctype='multipart/form-data' method='POST' action = 'nufileput.php'>
	<input type='hidden' name='MAX_FILE_SIZE' value='45000000' />

	<div id="upload-button-div" style="position:relative;">
		<a class='nuButton'  style=" text-decoration:none; width:120px; height:20px;" href='javascript:;'>
		Choose File <input class="nuFileUpload"" id="upload-input" size="0" type="file" name="uploadedfile" onchange='uploaderAction();'>
		</a>
	</div>
	<span id="upload-file-info"></span>
	<br>
        <input onclick='uploaderProgress();' id="upload-start-button" class="nuNotSaved" style="width:120px; height:20px; visibility:hidden;" type='submit' value='Start Upload' />
	</form>

<?php 
	} else { 
		if ($_FILES['uploadedfile']['error'] == 0) {

 			$_SESSION['nuFileUpload'] = $_FILES; 
			$tempname = $_SESSION['nuFileUpload']['uploadedfile']['tmp_name'];
			$handle   = fopen($tempname, "rb");
			$contents = fread($handle, filesize($tempname));
			$contents = addslashes($contents);
			$_SESSION['nuFileUpload']['fileContent'] = $contents;
			fclose($handle);
?>
			<script>
			$(document).ready(function() {
				document.body.style.backgroundColor = '#EDEDED';
				window.parent.nuFileUploadDone();
			});
			</script>
			<h3>Upload ready, please click 'Save' to commit.</h3>

<?php 		} else { 


			echo "<h3>";
			echo  nuUploadCodesToMessage($_FILES['uploadedfile']['error']);
			echo "</h3>";

		}
?>

<?php   } 
?>
</div>
</body>
</html>
