<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>Search Injury Reports</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>

        <h2>Display Entire Table:</h2>
        <form method= "GET" action = 'InjuryReport.php'>
            <input type="hidden" value="displayTuplesRequest" name="displayTuplesRequest">
            <input type="submit" value="Display" name="display"></p>
        </form>

        <hr />

        <h2>Filter by Injury Type:</h2>
        <p><b>NOTE: </b>The values are case sensitive and white-space sensitive.</p>

        <form method="GET" action='InjuryReport.php'>
            Injury Type: <input type="text" name="filtertype"> <br /> <br />

        <h2>Include columns:</h2>
            Injury ID: <input type="checkbox" name="checkinjID" value="injID"> <br /><br />
            Member ID: <input type="checkbox" name="checkmemID" value="memberID"> <br /><br />
            Injury Type: <input type="checkbox" name="checktype" value="injType"> <br /><br />
            Injury Date: <input type="checkbox" name="checkdate" value="injDate"> <br /><br />

            <input type="hidden" id="filterTupleRequest" name="filterTupleRequest">
            <input type="submit" value="Find Reports" name="displayFilterTuples" />
        </form>

        <hr />

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr);
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result, $columns) { //prints results from a select statement
            echo "<br>Retrieved data from table InjuryReport:<br>";
            echo "<table>";
            echo "<tr>";
            foreach ($columns as $column) {
                if ($column != NULL) {
                    echo "<th>" . $column . "</th>";
                }
            }
            echo "</tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printDisplayResult($result) {
            echo "<br>Retrieved data from table InjuryReport:<br>";
            echo "<table>";
            echo "<tr><th>injID</th><th>memberID</th><th>injType</th><th>injDate</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
            }

            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_schoi727", "a33154717", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleDisplayRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT injID, memberID, injType, TO_CHAR(injDate, 'YYYY-MM-DD') FROM InjuryReport");
            printDisplayResult($result);
        }

        function handleDisplayFilterRequest() {
            global $db_conn;

            $result = "";

            $filterType = $_GET['filtertype'];

            $columns = array (
                $injID = $_GET['checkinjID'],
                $memID = $_GET['checkmemID'],
                $injType = $_GET['checktype'],
                $injDate = $_GET['checkdate']
            );

            $query = "SELECT ";
            foreach ($columns as $column) {
                if ($column != NULL) {
                    if ($column == $injDate) {
                        $query .= "TO_CHAR($injDate, 'YYYY-MM-DD'), ";
                    } else {
                        $query .= "$column, ";
                    }
                }
            }
            $query = rtrim($query, ", ");
            $query .= " FROM InjuryReport WHERE injType = '". $filterType . "'";
            
            $result = executePlainSQL($query);

            echo "<br>Filtering those with injury type = " . $filterType . ":</br>";
            printResult($result, $columns);
        }



        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('displayTuplesRequest', $_GET)) {
                    handleDisplayRequest();
                } else if (array_key_exists('filterTupleRequest', $_GET)) {
                    handleDisplayFilterRequest();
                }

                disconnectFromDB();
            }
        }

		if (isset($_GET['displayFilterTuples']) || isset($_GET['display'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>
