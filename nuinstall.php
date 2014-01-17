<h1>nuBuilder Database Installer/Upgrader</h1>

<form method="POST" action="nuinstall.php">
        Globeadmin Password: <input type="password" name="pwd"><br>

	<p>Drop columns not used by nuBuilder	
	<select name="dropColumns">
		<option value="n">No</option>
		<option value="y">Yes</option>
	</select>
	</p>

	<p>Drop indexes not used by nuBuilder
        <select name="dropIndexes">
                <option value="n">No</option>
                <option value="y">Yes</option>
        </select>
        </p>

	<p><i>Show full output</i>
        <select name="showAll">
                <option value="n">No</option>
                <option value="y">Yes</option>
        </select>
        </p>

	
        <input type="submit" value="Submit">
</form>

<?php
	require_once("config.php");
	require_once("nuinstall_lib.php");
	
	session_start();

	$_SESSION['DBHost']                 = $nuConfigDBHost;
	$_SESSION['DBName']                 = $nuConfigDBName;
	$_SESSION['DBUser']                 = $nuConfigDBUser;
	$_SESSION['DBPassword']             = $nuConfigDBPassword;
	$_SESSION['DBGlobeadminPassword']   = $nuConfigDBGlobeadminPassword;
	$_SESSION['title']                  = $nuConfigtitle;	

        $template 	= new nuinstall();
        $template->setDB($_SESSION['DBHost'], $_SESSION['DBName'], $_SESSION['DBUser'], $_SESSION['DBPassword']);

	if ($_POST['pwd'] == $_SESSION['DBGlobeadminPassword'] ) {

		if ( $_POST['dropColumns'] == "y" ) {
			$template->removeColumns = true;
		}

		if ( $_POST['dropIndexes'] == "y" ) {
                        $template->removeIndexes = true;
                }

	        $template->run();
		$template->showChangeSummary();
		$template->showSQLerrors();	
		$template->showWarnings();
		echo "Done! <br>";
		if ( $_POST['showAll'] == "y" ) {
			echo "<h4>Full Output:</h4>";
			$template->showContent();	
		}

	} else {
		echo "Password fail";
	}
?>
