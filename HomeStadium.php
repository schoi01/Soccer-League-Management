<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>Home Stadium</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>
        <h2>Insert Stadium:</h2>
        <form method="POST" action="HomeStadium.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Stadium ID: <input type="number" name="stadiumID" min="1"> <br /><br />
            Stadium Name: <input type="text" name="stadiumName"> <br /><br />
            Capacity: <input type="number" name="capacity" value="0" min="0" required> <br /><br />
            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>
        <!-- <h2>Update Stadium:</h2>
        <form method="POST" action="HomeStadium.php"> 
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
            Stadium ID: <input type="number" name="stadiumID_update" min="1"> <br /><br />
            Stadium Name: <input type="text" name="stadiumName_update"> <br /><br />
            <input type="submit" value="Update" name="insertSubmit"></p>
        </form> -->
        <h2>Delete Stadium:</h2>
        <form method="POST" action="HomeStadium.php"> 
            <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
            Stadium ID: <input type="number" name="stadiumID_delete"> <br /><br />
            <input type="submit" value="Delete" name="insertSubmit"></p>
        </form> 
        <!-- Delete logic unsure yet -->
        <form method= "GET" action = 'HomeStadium.php'>
            <input type = "hidden" value = "displayTuplesRequest" name = "displayTuplesRequest">
            <input type = "submit" value = "Display" name = "display"></p>
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
			return [$statement,$r];
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
                    return !$r;
                }
            }
            return False;
        }

        function printResult($result) { //prints results from a select statement
            echo "<br>Retrieved data about Stadiums:<br>";
            echo "<table>";
            echo "<tr><th>stadiumID</th><th>stadiumName</th><th>capacity</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printTeamResult($result) { //prints results from a select statement
            echo "<br>Retrieved data about Team:<br>";
            echo "<table>";
            echo "<tr><th>TeamID</th><th>StadiumID</th><th>Ranking</th><th>City</th></tr>";
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] ."</td><td>".$row[3]."</td></tr>"; 
            }
            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_sunnybak", "a65861007", "dbhost.students.cs.ubc.ca:1522/stu");
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
            [$result, $error] = executePlainSQL("SELECT * FROM HomeStadium");
            printResult($result);
            [$result, $error] = executePlainSQL("SELECT * FROM Team");
            printTeamResult($result);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['stadiumID'],
                ":bind2" => $_POST['stadiumName'],
                ":bind3" => $_POST['capacity']
            );

            $alltuples = array (
                $tuple
            );

            $error = executeBoundSQL("INSERT into HomeStadium values (:bind1, :bind2, :bind3)", $alltuples);
            OCICommit($db_conn);
            if (!$error) {
                echo "<br>Succesffuly Inserted Data<br>";
                [$result, $error] = executePlainSQL("SELECT * FROM HomeStadium");
                printResult($result);
            }
        }

        function handleUpdateRequest() {
            global $db_conn;

            $stadiumID = $_POST['stadiumID_update'];
            $stadiumName = $_POST['stadiumName_update'];

            [$result,$error] = $command = "UPDATE Homestadium SET STADIUMNAME='" . $stadiumName . "' WHERE STADIUMID='" . $stadiumID . "'";
            // you need the wrap the old name and new name values with single quotations
            executePlainSQL($command);
            OCICommit($db_conn);
            [$result,$error] = executePlainSQL("SELECT * FROM Homestadium WHERE STADIUMID='". $stadiumID . "'");
            $row = OCI_Fetch_Array(($result), OCI_BOTH);
            if (!$row) {
                echo "<br>Please input valid StadiumID<br>";
            } else {
                [$result,$error] = executePlainSQL("SELECT * FROM Homestadium");
                echo "<br>Successfuly updated StadiumID: ".$stadiumID." name to '".$stadiumName."'<br>";
                printResult($result);
            }

        }

        // Will change implementation after cascade logic set
        function handleDeleteRequest() {
            global $db_conn;
            $stadiumID = $_POST['stadiumID_delete'];
            $command = "DELETE FROM HomeStadium" . " WHERE STADIUMID = '" . $stadiumID . "'";
            [$result,$error] = executePlainSQL("SELECT * FROM Homestadium WHERE STADIUMID='". $stadiumID . "'");
            $row = OCI_Fetch_Array(($result), OCI_BOTH);
            if (!$row) {
                echo "<br>Please input valid StadiumID<br>";
            } else {
                executePlainSQL($command);
                OCICommit($db_conn);
                [$result,$error] = executePlainSQL("SELECT * FROM Homestadium");
                echo "<br>Successfuly deleted StadiumID: " .$stadiumID."<br>";
                printResult($result);
                echo "<br>Also deleted corresponding Team ID</br>";
                [$result,$error] = executePlainSQL("SELECT * FROM Team");
                printTeamResult($result);
            }
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                } else if (array_key_exists('deleteQueryRequest', $_POST)) {
                    handleDeleteRequest();
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
                }
                disconnectFromDB();
            }
        }

		if (isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['display'])) {
            handleGETRequest();
        }
	?>
	</body>
</html>
