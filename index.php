<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8'>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="apple-mobile-web-app-capable" content="yes" />

<title>nuBuilder</title>

<link rel="apple-touch-icon" href="apple-touch-icon.png"/>

<link rel="stylesheet" href="jquery/jquery-ui.css" />
<script src="jquery/jquery-1.8.3.js" type='text/javascript'></script>
<script src="jquery/jquery-ui.js" type='text/javascript'></script>
<script src="//api.filepicker.io/v1/filepicker.js" type="text/javascript"></script>

<?php
require_once('config.php');
require_once('nucommon.php');

jsinclude('nuformat.js');
jsinclude('nucommon.js');
jsinclude('nueditform.js');
jsinclude('nubrowseform.js');
jsinclude('nubrowseform.js');

print $GLOBALS['nuSetup']->set_css;  //-- html header

$i  = $_GET['i'];
$h  = $_SESSION['home'];
$t  = $_SESSION['title'];
$l  = nuGetLanguage();
$k1 = $GLOBALS['nuSetup']->set_inkfilepicker_key;

print "

<script>

filepicker.setKey('$k1');

$l
    
function nuGetID(){ 
	return '$i';
}

function nuGetHome(){ 
	return '$h';
}

function nuGetTitle(){ 
	return '$t';
}

</script>

";

?>


<script>

window.nuShiftKey    = false;
window.nuControlKey  = false;
window.nuTimeout     = false;
window.nuMoveable    = false;

$(document).ready(function() {

	$('title').html(nuGetTitle());

	var i            = nuGetID();

        window.nuSession = new nuBuilderSession();
	if(i === ''){                                                            //-- Main Desktop
		toggleModalMode();	
	}else{                                                                  //-- iFrame or new window
		var pSession  = nuGetParentSession();
		nuSession.setSessionID(pSession.nuSessionID);
		var w         = document.defaultView.parent.nuSession.getWindowInfo(i,pSession);
		nuBuildForm(w);                                                     //-- Edit or Browse
                
	}

});


  
</script>
</head>
<body onkeydown="nuKeyPressed(event, true);" onkeyup="nuKeyPressed(event, false);">
</body>
</html> 
