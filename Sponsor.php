<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>Sponsors</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>

        
        <h2>Insert Sponsor:</h2>
        <!--refresh page when submitted-->
        <form method="POST" action="Sponsor.php"> 
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            SponsorID: <input type="number" name="sponsorID" min="1" required> <br /><br />
            Sponsor Name: <input type="text" name="sponsorName"> <br /><br />
            Fee: <input type="number" name="fee"value="0" min="0"> <br /><br />
            TeamID: <input type="number" name="teamID" min="1"> <br /><br />
            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>


        <h2>Update Sponsor:</h2>
        <form method="POST" action="Sponsor.php"> 
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
            SponsorID: <input type="number" name="sponsorID_update" min="1" required> <br /><br />
            Sponsor Name: <input type="text" name="sponsorName_update"> <br /><br />
            Fee: <input type="number" name="fee_update"> <br /><br />
            Sponsoring Team ID: <input type="number" name="teamID_update" min="1"> <br /><br />
            <input type="submit" value="Update" name="insertSubmit"></p>
        </form>
        
        <!-- <h2>Delete Sponsor:</h2>
        <form method="POST" action="Sponsor.php"> 
            <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
            SponsorID: <input type="number" name="sponsorID_delete" min="1" required> <br /><br />
            <input type="submit" value="Delete" name="insertSubmit"></p>
        </form>  -->

        <form method= "GET" action = 'Sponsor.php'>
            <input type = "hidden" value = "displayTuplesRequest" name = "displayTuplesRequest">
            <input type = "submit" value = "Display All Values" name = "display"></p>
        </form>

        <h2>Show Sponsor Attributes:</h2>
        <form action="Sponsor.php" method= "GET">
            <input type = "hidden" value = "projectTuplesRequest" name = "projectTuplesRequest">
	    <p><b>NOTE: </b>ctrl + click to select multiple attributes</p>
            <label for="attributes_multiple">Choose an Attribute:</label>
            <select id="attributes_multiple" name="attributes_multiple[]" multiple>
                <option value="SPONSORID">sponsorID</option>
                <option value="SPONSORNAME">sponsorName</option>
                <option value="FEE">fee</option>
                <option value="TEAMID">teamID</option>
            </select>
            <input type="submit" value = "Show Data" name = "display">
        </form>

        <h2>Show Specific Team Attribute from Sponsor:</h2>
        <form action="Sponsor.php" method= "GET">
            <input type = "hidden" value = "joinTuplesRequest" name = "joinTuplesRequest">
            <label for="attributes">Choose an Attribute:</label>
            <select id="attributes" name="attributes">
                <option value="STADIUMID">StadiumID</option>
                <option value="RANKING">Ranking</option>
                <option value="CITY">City</option>
            </select>
            Search: <input type="text" name="request" required> <br /><br />
            <input type="submit" value = "Show Combined Table" name = "display">
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

        function printResult($result) { //prints results from a select statement
            global $db_conn;
            echo "<br>Retrieved data from table Sponsor:<br>";
            echo "<table>";
            echo "<tr><th>SponsorID</th><th>SponsorName</th><th>Fee</th><th>TeamID</th><th>TeamCity</th></tr>";
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                $teamCityRow = OCI_Fetch_Array(executePlainSQL("SELECT CITY FROM Team WHERE TEAMID='".$row[3]."'"),OCI_BOTH);
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $teamCityRow[0] . "</td></tr>" ;
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

        function printProjectionResult($result,$attributes,$num_elements) { //prints results from a select statement
            global $db_conn;
            echo "<br>Retrieved data from table Sponsor, displaying selected column:<br>";
            // if ($column == "SPONSORID") {
            //     $column = "Sponsor ID";
            // } elseif ($column == "SPONSORNAME") {
            //     $column = "Sponsor Name";
            // } elseif ($column == "FEE") {
            //     $column = "Sponsor Fee";
            // } elseif ($column == "TEAMID") {
            //     $column = "Sponsor Team ID";
            // }
            echo "<table>";
            if ($num_elements == 1) {
                echo "<tr><th>".$attributes[0]."</th></tr>";
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>" . $row[0]."</td></tr>" ;
                }
            } elseif ($num_elements == 2) {
                $res = "<tr>";
                for ($i=0;$i<$num_elements;$i++) {
                    $res = $res."<th>".$attributes[$i]."</th>";
                }
                $res = $res."</tr>";
                echo $res;
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td></tr>" ;
                }
            } elseif ($num_elements == 3) {
                $res = "<tr>";
                for ($i=0;$i<$num_elements;$i++) {
                    $res = $res."<th>".$attributes[$i]."</th>";
                }
                $res = $res."</tr>";
                echo $res;
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>" ;
                }
            } elseif ($num_elements == 4) {
                $res = "<tr>";
                for ($i=0;$i<$num_elements;$i++) {
                    $res = $res."<th>".$attributes[$i]."</th>";
                }
                $res = $res."</tr>";
                echo $res;
                while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                    echo "<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td></tr>" ;
                }
            }
            echo "</table>";
        }

        function printJoinResult($result,$column) {
            global $db_conn;
            echo "<br>Combined Sponsor and Team Data to see comprehensive stats:<br>";
            if ($column == "STADIUMID") {
                $column = "Stadium ID";
            } elseif ($column == "RANKING") {
                $column = "Sponsor Team Ranking";
            } elseif ($column == "CITY") {
                $column = "Sponsor Team City";
            } 
            echo "<table>";
            echo "<tr><th>SponsorName</th><th>SponsorFee</th><th>TeamStadiumID</th><th>TeamStadiumName</th><th>TeamRanking</th><th>TeamCity</th></tr>";
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                $teamStadiumRow = OCI_Fetch_Array(executePlainSQL("SELECT STADIUMNAME FROM Homestadium WHERE STADIUMID='".$row[2]."'"),OCI_BOTH);
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] .  "</td><td>" . $teamStadiumRow[0] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>" ;
            }
            echo "</table>";
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        // Handle functions
        function handleDisplayRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT * FROM Sponsor");
            printResult($result);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['sponsorID'],
                ":bind2" => $_POST['sponsorName'],
                ":bind3" => $_POST['fee'],
                ":bind4" => $_POST['teamID']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("INSERT into Sponsor values (:bind1, :bind2, :bind3, :bind4)", $alltuples);
            OCICommit($db_conn);

            $result = executePlainSQL("SELECT * FROM Sponsor");
            printResult($result);
        }

        function handleUpdateRequest() {
            global $db_conn;

            $sponsorID = $_POST['sponsorID_update'];

            $sponsorNameRow = OCI_Fetch_Array(executePlainSQL("SELECT * FROM Sponsor WHERE SPONSORID='" . $sponsorID . "'"),OCI_BOTH);
            if (!$sponsorNameRow) {
                echo "<br>Please input valid SponsorID<br>";
            } else {
                if (empty($_POST['sponsorName_update'])) {
                    $sponsorName = $sponsorNameRow[1];
                } else { $sponsorName = $_POST['sponsorName_update'];}
    
                if (empty($_POST['fee_update'])) {
                    $fee = $sponsorNameRow[2];
                } else { $fee = $_POST['fee_update'];}
    
                if (empty($_POST['teamID_update'])) {
                    $teamID = NULL;
                } else { $teamID = $_POST['teamID_update'];}
                $TeamIDRow = OCI_Fetch_Array(executePlainSQL("SELECT * FROM Team WHERE TEAMID='" . $teamID . "'"),OCI_BOTH);
                if ($TeamIDRow || is_null($teamID)) {
                    echo "<br>Successfully Updated Sponsor: ".$sponsorName."<br>";
                    $command = "UPDATE Sponsor SET SPONSORNAME='" . $sponsorName . "',FEE='" . $fee . "',TEAMID='" . $teamID . "' WHERE SPONSORID='" . $sponsorID . "'";
                    executePlainSQL($command);
                    OCICommit($db_conn);
                    $result = executePlainSQL("SELECT * FROM Sponsor");
                    printResult($result);
                } else{
                    $command = "UPDATE Sponsor SET SPONSORNAME='" . $sponsorName . "',FEE='" . $fee . "',TEAMID='" . $teamID . "' WHERE SPONSORID='" . $sponsorID . "'";
                    executePlainSQL($command);
                    OCICommit($db_conn);
                }
            }
        }

        function handleDeleteRequest() {
            global $db_conn;
            $sponsorID = $_POST['sponsorID_delete'];
            $command = "DELETE FROM Sponsor" . " WHERE SPONSORID = '" . $sponsorID . "'";
            executePlainSQL($command);
            OCICommit($db_conn);

            $result = executePlainSQL("SELECT * FROM Sponsor");
            printResult($result);
        }

        function handleProjectionRequest() {
            global $db_conn;
            $attributes = $_GET['attributes_multiple'];
            $num_elements = count($attributes);
            $command = "SELECT ";
            for ($i=0;$i<$num_elements;$i++) {
                $command = $command.$attributes[$i];
                if ($i != ($num_elements-1)) {
                    $command = $command.", ";
                }
            }
            $command = $command." FROM Sponsor";
            $result = executePlainSQL($command);
            printProjectionResult($result,$attributes,$num_elements);
        }

        function handleJoinRequest() {
            global $db_conn;
            $attribute = $_GET['attributes'];
            $request = $_GET['request'];
            $command = "SELECT Sponsor.SPONSORNAME, Sponsor.FEE, Team.STADIUMID, Team.RANKING, Team.CITY FROM Sponsor JOIN Team ON Sponsor.TEAMID = TEAM.TEAMID WHERE Team.".$attribute." = '".$request."'";
            $result = executePlainSQL($command);
            $row = OCI_Fetch_Array($result,OCI_BOTH);
            if ($row === false) {
                echo "<br>Please input valid search field</br>";
            } else {
                $result = executePlainSQL($command);
                printJoinResult($result,$attribute);
            }
            // printJoinResult($result,$attribute);
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
                if (array_key_exists('projectTuplesRequest', $_GET)) {
                    handleProjectionRequest();
                }
                if (array_key_exists('joinTuplesRequest', $_GET)) {
                    handleJoinRequest();
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
