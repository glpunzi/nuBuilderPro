<?php

class nuinstall {

        var $debug           = true;
        var $DB              = array();
	var $summary1	     = array();
	var $summary2	     = array();
	var $display         = "";
	var $lastSQLerror    = "";
	var $sqlErrors	     = array();
	var $warnings        = array();
	var $removeColumns   = false;
	var $removeIndexes   = false;

        function setDB($DBHost, $DBName, $DBUserID, $DBPassWord) {
                $this->DB['DBHost']       = $DBHost;
                $this->DB['DBName']       = $DBName;
                $this->DB['DBUserID']     = $DBUserID;
                $this->DB['DBPassWord']   = $DBPassWord;
        }

	function importTemplate() {

                $this->addDisplay("<b>Importing template tables: </b>$restore <hr>");

		$file   = realpath(dirname(__FILE__))."/nu_template.sql";
		$handle = fopen($file, "r");
		$temp 	= "";
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				if ($line[0] != "-" AND $line[0] != "/"  AND $line[0] != "\n") {
					$line = trim($line);
					$temp .= $line;
					if ( substr($line, -1) == ";" ) {
                        			$temp = rtrim($temp,';');
						$this->runQuery($temp, $this->DB);
                        			$temp = "";
                			}
        			}
    			}
		} else {
			echo "error opening the file.";
			die();
		}
        }

	function addDisplay($content) {
		$this->display .= $content;
	}

	function run() {
		$this->importTemplate();
		$this->compareTables();
	}

	function showContent() {
		echo $this->display;
	}
	
	function showChangeSummary() {

		if ( (count($this->summary1) + count($this->summary2)) > 0 ) {
			echo "<b>Schema Changes:</b><br>";

			if ( count($this->summary1) > 0 ) {
				echo "Column(s) Added<br>";
				echo "<pre>";
				print_r($this->summary1);
				echo "</pre>";
			}
	
			if ( count($this->summary2) > 0 ) {
				echo "Column(s) Changed<br>";
				echo "<pre>";
				print_r($this->summary2);
				echo "</pre>";
			}

		} else {
			echo "<b>No Schema Changes!</b><br>";
		}
	}

	function showSQLerrors() {
	
		 if ( count($this->sqlErrors) > 0 ) {
                        echo "<b>SQL Errors:</b><br>";
                        echo "<pre>";
                        print_r($this->sqlErrors);
                        echo "</pre>";
                } else {
                        echo "<b>No SQL Errors!</b><br>";
                }

	}

	function showWarnings() {

                 if ( count($this->warnings) > 0 ) {
                        echo "<b>Warnings:</b><br>";
                        echo "<pre>";
                        print_r($this->warnings);
                        echo "</pre>";
                } else {
                        echo "<b>No Warnings!</b><br>";
                }

        }

	function makeTable($template_table, $real_table) {

		// make sure that the real table exists
                $this->addDisplay("<b>Working On: </b> $real_table <br>");
                $this->addDisplay("<b>Creating Table if it does not exists </b><br>");
                $create_sql     = "SHOW CREATE TABLE $template_table";
                $create_rs      = $this->runQuery($create_sql, $this->DB);
                $create_arry    = mysql_fetch_array($create_rs);
                $replace        = "CREATE TABLE `$template_table`";
                $with           = "CREATE TABLE IF NOT EXISTS `$real_table`";
                $create_sql     = str_replace($replace,  $with, $create_arry[1]);
                $this->addDisplay("<pre>");
                $this->addDisplay($create_sql);
                $this->addDisplay("</pre>");
                $this->runQuery($create_sql, $this->DB);
	}

	function deleteNubuilderInfo($template_table, $real_table) {

		// delete existing nuBuilder info in zzsys tables
                $this->addDisplay("<b>Deleting nuBuilder info from table</b><br>");
                $id        = $real_table."_id";
                $clean_sql = "DELETE FROM $real_table WHERE $id IN (SELECT $id FROM $template_table) ";
                $this->addDisplay($clean_sql."<br>");
                $this->runQuery($clean_sql, $this->DB);	
	}

	function loopColumns($template_table, $real_table) {

		$table_schema           = $this->DB['DBName'];
                $dbInfo                 = $this->DB;
                $dbInfo['DBName']       = "information_schema";

		// loop thru all columns in the template table
                $template_sql  = "SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_TYPE, COLUMN_KEY ";
                $template_sql .= "FROM `COLUMNS` ";
                $template_sql .= "WHERE `TABLE_SCHEMA` = '$table_schema' ";
                $template_sql .= "AND `TABLE_NAME` = '$template_table' ";
                $template_rs   = $this->runQuery($template_sql, $dbInfo);

                while ( $template_obj = mysql_fetch_object($template_rs) ) {

                	$column_name      = $template_obj->COLUMN_NAME;
                        $column_type      = $template_obj->COLUMN_TYPE;
                        if ( $template_obj->IS_NULLABLE == "YES" ) {
                        	$is_null = '';
                        } else {
                        	$is_null = 'NOT ';
                        }

                        // get column from real table matching the template column
                        $real_column_sql  = "SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, COLUMN_TYPE, COLUMN_KEY ";
                        $real_column_sql .= "FROM `COLUMNS` ";
                        $real_column_sql .= "WHERE `TABLE_SCHEMA` = '$table_schema' ";
                        $real_column_sql .= "AND `TABLE_NAME` = '$real_table' ";
                        $real_column_sql .= "AND `COLUMN_NAME` = '$column_name' ";
                        $real_column_rs   = $this->runQuery($real_column_sql, $dbInfo);
                        $real_column_obj  = mysql_fetch_object($real_column_rs);

                        // check that the column exits
                        $num = mysql_num_rows($real_column_rs);
                        if ($num != 1 ) {
                        	// ADD
                                $this->addColumn($real_table, $column_name, $column_type, $is_null);
                        } else {

                        	// Compare
				$compare = $this->compareColumns($template_obj, $real_column_obj);
                                if ( false == $compare[0] ) {
                                	// CHANGE
                                        $this->changeColumn($real_table, $column_name, $column_type, $is_null, $compare);
                                }
                         }
		}
	}

	function addColumn($real_table, $column_name, $column_type, $is_null) {

		// ADD
                $this->addDisplay("<b>Adding new/missing column</b><br>");
                $alter_sql = "ALTER TABLE `$real_table` ADD `$column_name` $column_type $is_null NULL";
                $this->addDisplay("$alter_sql <br>");
                array_push($this->summary1,$alter_sql);
                $this->runQuery($alter_sql, $this->DB);
	}

	function changeColumn($real_table, $column_name, $column_type, $is_null, $compare) {

		// CHANGE
		$this->addDisplay("<b>Changing column</b><br>");
                $alter_sql  = "ALTER TABLE `$real_table` CHANGE `$column_name` `$column_name` $column_type ";
                $alter_sql .= " $is_null NULL ".$compare[1];
                $this->addDisplay("$alter_sql <br>");
                array_push($this->summary2,$alter_sql);
                $this->runQuery($alter_sql, $this->DB);

		if ( $compare['2'] == "ADD INDEX" ) {
			$alter_sql  = "ALTER TABLE `$real_table` ADD INDEX (`$column_name`) ";
			$this->addDisplay("$alter_sql <br>");
			array_push($this->summary2,$alter_sql);
			$this->runQuery($alter_sql, $this->DB);
		}
	}

	function dropColumn($real_table, $column_name) {

                // DROP
                $this->addDisplay("<b>Drop column</b><br>");
                $alter_sql  = "ALTER TABLE `$real_table` DROP `$column_name` ";
                $this->addDisplay("$alter_sql <br>");
                array_push($this->summary2,$alter_sql);
                $this->runQuery($alter_sql, $this->DB);
        }

	function dropIndex($real_table, $type) {

                // DROP INDEX
                $this->addDisplay("<b>Drop Index</b><br>");
                $alter_sql  = "ALTER TABLE `$real_table` DROP $type ";
                $this->addDisplay("$alter_sql <br>");
                array_push($this->summary2,$alter_sql);
                $this->runQuery($alter_sql, $this->DB);
        }

	function _getColumns($_table) {

		$table_schema           = $this->DB['DBName'];
                $dbInfo                 = $this->DB;
                $dbInfo['DBName']       = "information_schema";
		$columns		= array();

		$_column_sql  = "SELECT COLUMN_NAME ";
                $_column_sql .= "FROM `COLUMNS` ";
                $_column_sql .= "WHERE `TABLE_SCHEMA` = '$table_schema' ";
                $_column_sql .= "AND `TABLE_NAME` = '$_table' ";
                $_column_sql .= "ORDER BY `ORDINAL_POSITION` ";
                $_column_rs   = $this->runQuery($_column_sql, $dbInfo);
                while ( $_column_obj = mysql_fetch_object($_column_rs) ) {
                        array_push($columns, $_column_obj->COLUMN_NAME);
                }

		return $columns;
	}

	function insertNubuilderInfo($real_table, $template_table) {

		$table_schema           = $this->DB['DBName'];
                $dbInfo                 = $this->DB;
                $dbInfo['DBName']       = "information_schema";
		$colum_order		= array();

		// get columns in correct order so the insert will work
		$real_table_columns 	= $this->_getColumns($real_table);
		$template_table_columns = $this->_getColumns($template_table);

		// remove columns not in template table, so that insert will work
		for ($x = 0; $x < count($real_table_columns); $x++) {
			if ( in_array($real_table_columns[$x], $template_table_columns) ) {	
				array_push($colum_order, $real_table_columns[$x]); 		
			} else {
				
				if ($this->removeColumns == true) {
					$this->dropColumn($real_table, $real_table_columns[$x]);
			
				} else {

					array_push($colum_order, " '' AS ".$real_table_columns[$x]);
					$col  = $real_table_columns[$x];
					$warn = "Found $col in $real_table that is not used by nuBuilder";
					array_push($this->warnings, $warn);
				}
				
			}
		}
                $colum_order2 = implode(", ", $colum_order);

		// do insert
                $this->addDisplay("<b>Inserting nuBuilder info into table</b><br>");
                $insert = "INSERT INTO `$real_table` SELECT $colum_order2 FROM `$template_table` ";
                $this->runQuery($insert, $this->DB);
		$this->addDisplay($insert."<br>");

		//debug errors
		if ( $this->lastSQLerror != "" ) {
			$this->addDisplay("<h3>".$this->lastSQLerror."</h3>");
			$this->addDisplay($insert."<br>");
			$this->addDisplay("<pre>");
	                $this->addDisplay($real_table."<br>");
        	        $this->addDisplay(count($real_table_columns)."<br>");
                	$this->addDisplay(print_r($real_table_columns, true));
	                $this->addDisplay($template_table."<br>");
        	        $this->addDisplay(count($template_table_columns)."<br>");
                	$this->addDisplay(print_r($template_table_columns,true));
	                $this->addDisplay(count($colum_order)."<br>");
        	        $this->addDisplay(print_r($colum_order,true));
                	$this->addDisplay("</pre>");
			$this->addDisplay("<hr>");
		}
		
	}

	function dropTemplateTable($template_table) {

		//Drop template table
                $drop_sql = "DROP TABLE `$template_table`";
                $this->addDisplay("<b>Drop template table</b><br>");
                $this->addDisplay($drop_sql."<br>");
                $this->runQuery($drop_sql, $this->DB);
	}

	function compareTables() {
	
		$table_schema	        = $this->DB['DBName'];
		$dbInfo                 = $this->DB;
                $dbInfo['DBName']       = "information_schema";
                $sql                    = "SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = '".$this->DB['DBName']."'";
                $rs                     = $this->runQuery($sql, $dbInfo);

		// loop thru all tables in database
		while ( $obj = mysql_fetch_object($rs) ) {

			$thisTablePrefix                = substr($obj->TABLE_NAME, 0, 15);
                        $this_table_name                = $obj->TABLE_NAME;
			
			// only look at tables with the template_zzzsys_ prefix
			if ($thisTablePrefix == "template_zzzsys") {

				// get both template and real table names
				$template_table = $this_table_name;
				$real_table	= str_replace("template_", "", $this_table_name); 

				// make sure that the real table exists
				$this->makeTable($template_table, $real_table);

				// delete existing nuBuilder info in zzsys tables
				$this->deleteNubuilderInfo($template_table, $real_table);

				// loop thru all columns in the template table
				$this->loopColumns($template_table, $real_table);

				// insert nubuilder data
				$this->insertNubuilderInfo($real_table, $template_table);

				//Drop template table
				$this->dropTemplateTable($template_table);

				$this->addDisplay("<hr>");
				
			}
		}
	}

	function compareColumns($t_obj, $r_obj) {
		$compare1    = $t_obj->COLUMN_DEFAULT.$t_obj->IS_NULLABLE.$t_obj->COLUMN_TYPE.$t_obj->COLUMN_KEY;
                $compare2    = $r_obj->COLUMN_DEFAULT.$r_obj->IS_NULLABLE.$r_obj->COLUMN_TYPE.$r_obj->COLUMN_KEY;

		$compare1a    = $t_obj->COLUMN_DEFAULT.$t_obj->IS_NULLABLE.$t_obj->COLUMN_TYPE;
                $compare2b    = $r_obj->COLUMN_DEFAULT.$r_obj->IS_NULLABLE.$r_obj->COLUMN_TYPE;

		$result      = array();
		$result[0]   = true;
                $result[1]   = "";
		$result[2]   = "";	
	
		if ($compare1 != $compare2) {
			$this->addDisplay($compare1."<br>");
			$this->addDisplay($compare2."<br>");
		        if ( $t_obj->COLUMN_KEY == 'PRI' AND $r_obj->COLUMN_KEY != 'PRI'  ) {
				$result[0] = false;
				$result[1] = "PRIMARY KEY"; 
			}
			if ( $t_obj->COLUMN_KEY == 'UNI' AND $r_obj->COLUMN_KEY != 'UNI'  ) {
				$result[0] = false;
                                $result[1] = "UNIQUE";
                        }
			if ( $t_obj->COLUMN_KEY == 'MUL' AND $r_obj->COLUMN_KEY != 'MUL'  ) {
				$result[0] = false;		
                                $result[2] = "ADD INDEX";
                        }
			if ($compare1a != $compare2b) {
				$result[0] = false;
			}
			$this->compareColumnsIndexes($t_obj, $r_obj);
		}
		return $result;
	}

	function compareColumnsIndexes($t_obj, $r_obj) {

		$col = $r_obj->COLUMN_NAME;
		$real_table = $r_obj->TABLE_NAME;

		if ( $this->removeIndexes == true ) {

			if ( $r_obj->COLUMN_KEY == 'PRI' AND $t_obj->COLUMN_KEY != 'PRI'  ) {
				$type = " PRIMARY KEY ";
				$this->dropIndex($real_table, $type);
                        }
                        if ( $r_obj->COLUMN_KEY == 'UNI' AND $t_obj->COLUMN_KEY != 'UNI'  ) {
				$type = " INDEX $col ";
				$this->dropIndex($real_table, $type);
                        }
                        if ( $r_obj->COLUMN_KEY == 'MUL' AND $t_obj->COLUMN_KEY != 'MUL'  ) {
				$type = " INDEX $col ";
				$this->dropIndex($real_table, $type);
                        }

		} else {

			if ( $r_obj->COLUMN_KEY == 'PRI' AND $t_obj->COLUMN_KEY != 'PRI'  ) {
				$warn = "Found $col has Primary Key that is not used by nuBuilder";
				array_push($this->warnings, $warn);
                	}
                	if ( $r_obj->COLUMN_KEY == 'UNI' AND $t_obj->COLUMN_KEY != 'UNI'  ) {
				$warn = "Found $col has Unique Index that is not used by nuBuilder";
                        	array_push($this->warnings, $warn);
                	}
                	if ( $r_obj->COLUMN_KEY == 'MUL' AND $t_obj->COLUMN_KEY != 'MUL'  ) {
				$warn = "Found $col has Unique Index that is not used by nuBuilder";
                        	array_push($this->warnings, $warn);
                	}
		}
	}

	function runQuery($pSQL, $dbInfo) {

		$DBHost 	= $dbInfo['DBHost'];
		$DBUserID	= $dbInfo['DBUserID'];
		$DBPassWord     = $dbInfo['DBPassWord'];
		$DBName		= $dbInfo['DBName'];

                $con = mysql_connect($DBHost,$DBUserID,$DBPassWord) or die ("Could not connect to database\n");
                mysql_select_db($DBName,$con) or die ("Could not select database\n");
                $t = mysql_query($pSQL);
	
		$this->lastSQLerror = mysql_error($con);
		if ( "" !=  mysql_error($con) ) {
			$errors[0] = mysql_errno($con);
			$errors[1] = mysql_error($con);
			$errors[2] = $pSQL;
			array_push($this->sqlErrors, $errors);
		}
                return $t;
        }
}
?>
