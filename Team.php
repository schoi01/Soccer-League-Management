<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>Soccer Team</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>

        <h2>Insert Soccer Team:</h2>
        <form method="POST" action="Team.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Team ID: <input type="number" name="teamID"> <br /><br />
            Team Name: <input type="text" name="teamName"> <br /><br />
            Home Stadium ID: <input type="number" name="stadiumID"> <br /><br />
            Ranking: <input type="number" name="ranking"> <br /><br />
            City: <input type="text" name="city"> <br /><br />

            <input type="submit" value="Insert Team" name="insertTeam"></p>
        </form>

        <form method= "GET" action = 'Team.php'>
            <input type = "hidden" value = "displayTuplesRequest" name = "displayTuplesRequest">
            <input type = "submit" value = "Display" name = "display"></p>
        </form>

        <hr />

        <h2>Count number of Players per Team:</h2>
        <form method="GET" action="Team.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countTupleRequest" name="countTupleRequest">
            <input type="submit" name="countTuples"></p>
        </form>

        <h2>Teams that have scored more than 15 goals:</h2>
        <form method="GET" action="Team.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countGoalRequest" name="countGoalRequest">
            <input type="submit" name="countGoals"></p>
        </form>


        <h2>Teams with an average salary higher than the average salary of the league:</h2>
        <form method="GET" action="Team.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countNestedRequest" name="countNestedRequest">
            <input type="submit" name="countNested"></p>
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

        function printRankingResult($result) { //prints results from a select statement
            echo "<br>Insert happening in two tables at once (after normalization)</br>";
            echo "<br>Retrieved data from table Ranking:<br>";
            echo "<table>";
            echo "<tr><th>ranking</th><th>teamName</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printTeamResult($result) {
            echo "<br>Retrieved data from table Team:<br>";
            echo "<table>";
            echo "<tr><th>teamID</th><th>stadiumID</th><th>ranking</th><th>city</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

	function printCountResult($result) {
            echo "<br>Retrieved data from table PlayerInfo:<br>";
            echo "<table>";
            echo "<tr><th>Team ID</th><th>Number of Players</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[1] . "</td><td>" . $row[0] . "</td></tr>";
            }

            echo "</table>";
        }

        function printGoalResult($result) {
            echo "<br>Retrieved data from table PlayerInfo:<br>";
            echo "<table>";
            echo "<tr><th>Team ID</th><th># Goals</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
            }

            echo "</table>";
        }

        function printNestedResult($result, $result2) {
            echo "<table>";
            
            if ($row = OCI_Fetch_Array($result2, OCI_BOTH)) {
                echo "<br>Average Salary in the League is $" . round($row[0]) . ".<br>";
            }

            echo "<br>Retrieved data from table PlayerInfo:</br>";
            echo "<tr><th>Team ID</th><th>Average Salary ($)</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . round($row[1]) . "</td></tr>";
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
            $rankingresult = executePlainSQL("SELECT * FROM Ranking ORDER BY ranking");
            $teamresult = executePlainSQL("SELECT * FROM Team ORDER BY teamID");
            printRankingResult($rankingresult);
            printTeamResult($teamresult);
        }

        function handleInsertRequest() {
            global $db_conn;
            
            try {
                $teamID = $_POST['teamID'];
                $stadID = $_POST['stadiumID'];
                $ranking = $_POST['ranking'];

                $teamIDResult = executePlainSQL("SELECT COUNT(*) FROM Team WHERE teamID = $teamID");
                $stadResult = executePlainSQL("SELECT COUNT(*) FROM Team WHERE stadiumID = $stadID");
                $rankingResult = executePlainSQL("SELECT COUNT(*) FROM Ranking WHERE ranking = $ranking");

                $teamIDrow = oci_fetch_row($teamIDResult);
                $stadrow = oci_fetch_row($stadResult);
                $rankrow = oci_fetch_row($rankingResult);

                if ($teamIDrow[0] > 0) {
                    throw new Exception("ERROR: constraints violated - teamID = " . $teamID . " already exists in table Team. Please try again.");
                } else if ($stadrow[0] > 0) {
                    throw new Exception("ERROR: constraints violated - stadiumID = " . $stadID . " already exists in table Team. Please try again.");
                } else if ($rankrow[0] > 0) {
                    throw new Exception("ERROR: constraints violated - ranking = " . $ranking . " already exists in table Ranking. Please try again.");
                }

                //Getting the values from user and insert data into the table
                $rankingtuple = array (
                    ":bind1" => $_POST['ranking'],
                    ":bind2" => $_POST['teamName']
                );

                $allrankingtuples = array (
                    $rankingtuple
                );
                
                executeBoundSQL("insert into Ranking values (:bind1, :bind2)", $allrankingtuples);
                echo "<br>Successfully inserted into table Ranking!</br>";
                OCICommit($db_conn);

                $teamtuple = array (
                    ":bind3" => $_POST['teamID'],
                    ":bind4" => $_POST['stadiumID'],
                    ":bind5" => $_POST['ranking'],
                    ":bind6" => $_POST['city']
                );

                $allteamtuples = array (
                    $teamtuple
                );

                executeBoundSQL("insert into Team values (:bind3, :bind4, :bind5, :bind6)", $allteamtuples);
                echo "<br>Successfully inserted into table Team!</br>";
                OCICommit($db_conn);

            } catch (Exception $e) {
                echo "" . $e->getMessage();
            }
        }

        //Input: GETRequest from a Table
        //Output: Check if table is empty, if not send request to print result to print tuples in that table
        //tableName is passed from the HTML side to know what the current table is
        function handleCountRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT COUNT(*), teamID FROM PlayerInfo GROUP BY teamID");
            printCountResult($result);
        }


        function handleGoalRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT teamID, SUM(goalNum) FROM PlayerInfo GROUP BY teamID HAVING SUM(goalNum) >15");
            
            echo printGoalResult($result);
        }

        function handleNestedRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT teamID, AVG(P1.playerSalary) FROM PlayerInfo P1 GROUP BY teamID HAVING AVG(P1.playerSalary) > (SELECT AVG(P2.playerSalary) FROM PlayerInfo P2)");
            $result2 = executePlainSQL("SELECT AVG(playerSalary) FROM PlayerInfo");

            echo printNestedResult($result, $result2);
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
                } else if (array_key_exists('countTupleRequest', $_GET)) {
                    handleCountRequest();
                } else if (array_key_exists('countGoalRequest', $_GET)) {
                    handleGoalRequest();
                } else if (array_key_exists('countNestedRequest', $_GET)) {
                    handleNestedRequest();
                }

                disconnectFromDB();
            }
        }

		if (isset($_POST['insertTeam'])) {
            handlePOSTRequest();
        } else if (isset($_GET['display']) || isset($_GET['countTuples']) || isset($_GET['countGoals']) || isset($_GET['countNested'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>
