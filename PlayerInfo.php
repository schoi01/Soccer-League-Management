<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>PlayerInfo</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>

        <h2>Insert Player Info:</h2>
        <form method="POST" action="PlayerInfo.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Name: <input type="text" name="insName"> <br /><br />
            Jersey Number: <input type="number" name="insJNo"> <br /><br />
            TeamID: <input type="text" name="insTID"> <br /><br />
            Salary: <input type="number" name="insSalary"> <br /><br />
            Position: <input type="text" name="insPos"> <br /><br />
            # Goals: <input type="number" name="insGoals"> <br /><br />
            <input type="submit" value="Insert Player" name="insertPlayer"></p>
        </form>

        <form method= "GET" action = 'PlayerInfo.php'>
            <input type = "hidden" value = "displayTuplesRequest" name = "displayTuplesRequest">
            <input type = "submit" value = "Display" name = "display"></p>
        </form>

        <h2>Count number of Players per Team:</h2>
        <form method="GET" action="PlayerInfo.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countTupleRequest" name="countTupleRequest">
            <input type="submit" name="countTuples"></p>
        </form>

        <h2>Teams that have scored at least 15 goals:</h2>
        <form method="GET" action="PlayerInfo.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countGoalRequest" name="countGoalRequest">
            <input type="submit" name="countGoals"></p>
        </form>


        <h2>Teams with an average salary higher than the average salary of the league:</h2>
        <form method="GET" action="PlayerInfo.php"> <!--refresh page when submitted-->
            <input type="hidden" name="tableName" value = "PlayerInfo">
            <input type="hidden" id="countNestedRequest" name="countNestedRequest">
            <input type="submit" name="countNested"></p>
        </form>

        <hr />

        <?php
        //ini_set('display_errors', 1);
        //ini_set('display_startup_errors', 1);
        //error_reporting(E_ALL);
		
        
        
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
            echo "<br>Retrieved data from table Ranking:<br>";
            echo "<table>";
            echo "<tr><th>ranking</th><th>teamName</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
    

        function printResult($result) {
            echo "<br>Retrieved data from table Player:<br>";
            echo "<table>";
            echo "<tr><th>Name</th><th>Jersey #</th><th>Team ID</th><th>Salary</th><th>Position</th><th># Goals</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td><td>" . $row[5] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printCountResult($result) {
            //echo "<br>Retrieved data from table Player:<br>";
            echo "<table>";
            echo "<tr><th>Team ID</th><th>Number of Players</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[1] . "</td><td>" . $row[0] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printGoalResult($result) {
            //echo "<br>Retrieved data from table Player:<br>";
            echo "<table>";
            echo "<tr><th>Team ID</th><th># Goals</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printNestedResult($result, $result2) {
            //echo "<br>Retrieved data from table Player:<br>";
            echo "<table>";
            
            if ($row = OCI_Fetch_Array($result2, OCI_BOTH)) {
                echo "Average Salary in the League is $" . round($row[0]) . ""; //or just use "echo $row[0]"

            }


            echo "<tr><th>Team ID</th><th>Average Salary ($)</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . round($row[1]) . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }


        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_ryohqua", "a26071787", "dbhost.students.cs.ubc.ca:1522/stu");

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
            $playerresult = executePlainSQL("SELECT * FROM PlayerInfo");
            printResult($playerresult);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table

            $tuple = array (
                ":bind1" => $_POST['insName'],
                ":bind2" => $_POST['insJNo'],
                ":bind3" => $_POST['insTID'],
                ":bind4" => $_POST['insSalary'],
                ":bind5" => $_POST['insPos'],
                ":bind6" => $_POST['insGoals']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into PlayerInfo values (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6)", $alltuples);
            OCICommit($db_conn);
            echo "I have inserted to Player!";
        }

        //Input: GETRequest from a Table
        //Output: Check if table is empty, if not send request to print result to print tuples in that table
        //tableName is passed from the HTML side to know what the current table is
        function handleCountRequest() {
            global $db_conn;
            //printResult(executePlainSQL("SELECT * FROM PlayerInfo"));
            $result = executePlainSQL("SELECT COUNT(*), teamID FROM PlayerInfo GROUP BY teamID");
            printCountResult($result);
        }


        function handleGoalRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT teamID, SUM(goalNum) FROM PlayerInfo GROUP BY teamID HAVING SUM(goalNum) >15");
            
            //if (($row = oci_fetch_row($result)) != false) {
            #echo "<br> The number of tuples in PlayerInfo: " . $row[0] . "<br>";
            echo printGoalResult($result);
            //}
        }

        function handleNestedRequest() {
            global $db_conn;
            $result = executePlainSQL("SELECT teamID, AVG(P1.playerSalary) FROM PlayerInfo P1 GROUP BY teamID HAVING AVG(P1.playerSalary) > (SELECT AVG(P2.playerSalary) FROM PlayerInfo P2)");
            $result2 = executePlainSQL("SELECT AVG(playerSalary) FROM PlayerInfo");
            //if (($row = oci_fetch_row($result)) != false) {
            #echo "<br> The number of tuples in PlayerInfo: " . $row[0] . "<br>";
            echo printNestedResult($result, $result2);
            //}
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
        
		if (isset($_POST['insertPlayer'])) {
            handlePOSTRequest();
        } else if (isset($_GET['display']) || isset($_GET['countTuples']) || isset($_GET['countGoals']) || isset($_GET['countNested'])) {
            handleGETRequest();
        }
		?>
	</body>
</html> 
