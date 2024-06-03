<!-- Built based on the provided Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22) -->

  <html>
    <head>
        <title>Display All Relations</title>
    </head>

    <body>
        <form action = 'SoccerLeague.php'>
            <input type = "submit" value = "Back to main menu" />
        </form>

        <h2>Display All Relations:</h2>

        <form method= "GET" action = 'DisplayAllRelation.php'>
            <input type = "hidden" value = "displayTuplesRequest" name = "displayTuplesRequest">
            <input type = "submit" value = "Display" name = "display"></p>
        </form>

        <hr />

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);

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

        function printGameResult($result) {
            echo "<br>Retrieved data from table Game:<br>";
            echo "<table>";
            echo "<tr><th>gameID</th><th>gameDate</th><th>homeScore</th><th>awayScore</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function printRefResult($result) {
            echo "<br>Retrieved data from table Referee:<br>";
            echo "<table>";
            echo "<tr><th>refereeID</th><th>refName</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
            }

            echo "</table>";
        }
        
        function printMstaffResult($result) {
            echo "<br>Retrieved data from table MedicalStaff:<br>";
            echo "<table>";
            echo "<tr><th>mstaffID</th><th>mstaffName</th><th>mstaffRole</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>";
            }

            echo "</table>";
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

        function printHstadResult($result) {
            echo "<br>Retrieved data from table HomeStadium:<br>";
            echo "<table>";
            echo "<tr><th>stadiumID</th><th>stadiumName</th><th>capacity</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; //or just use "echo $row[0]"
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

        function printPinfoResult($result) {
            echo "<br>Retrieved data from table PlayerInfo:<br>";
            echo "<table>";
            echo "<tr><th>Name</th><th>Jersey #</th><th>Team ID</th><th>Salary</th><th>Position</th><th># Goals</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td><td>" . $row[5] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
        function printPmemResult($result) {
            echo "<br>Retrieved data from table PlayerMember:<br>";
            echo "<table>";
            echo "<tr><th>memberID</th><th>playerName</th><th>pnumber</th><th>teamID</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
        function printInjResult($result) {
            echo "<br>Retrieved data from table InjuryReport:<br>";
            echo "<table>";
            echo "<tr><th>injID</th><th>memberID</th><th>injType</th><th>injDate</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>";
            }

            echo "</table>";
        }
        function printStaffResult($result) {
            echo "<br>Retrieved data from table Staff:<br>";
            echo "<table>";
            echo "<tr><th>memberID</th><th>staffName</th><th>staffSalary</th><th>staffRole</th><th>teamID</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>";
            }

            echo "</table>";
        }
        function printSponsorResult($result) {
            echo "<br>Retrieved data from table Sponsor:<br>";
            echo "<table>";
            echo "<tr><th>SponsorID</th><th>SponsorName</th><th>Fee</th><th>TeamID</th></tr>";
            
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td></tr>" ;
            }

            echo "</table>";
        }
        function printPlaysinResult($result) {
            echo "<br>Retrieved data from table PlaysIn:<br>";
            echo "<table>";
            echo "<tr><th>gameID</th><th>stadiumID</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
        function printReceivesResult($result) {
            echo "<br>Retrieved data from table Receives:<br>";
            echo "<table>";
            echo "<tr><th>mstaffID</th><th>memberID</th><th>injID</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
        function printPlaysResult($result) {
            echo "<br>Retrieved data from table Plays:<br>";
            echo "<table>";
            echo "<tr><th>gameID</th><th>teamID1</th><th>teamID2</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }
        function printEmploysResult($result) {
            echo "<br>Retrieved data from table Employs:<br>";
            echo "<table>";
            echo "<tr><th>gameID</th><th>refereeID</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function handleDisplayRequest() {
            global $db_conn;
            $gameresult = executePlainSQL("SELECT gameID, TO_CHAR(gameDate, 'YYYY-MM-DD'), homeScore, awayScore FROM Game ORDER BY gameID");
            $refereeresult = executePlainSQL("SELECT * FROM Referee ORDER BY refereeID");
            $mstaffresult = executePlainSQL("SELECT * FROM MedicalStaff ORDER BY mstaffID");
            $rankingresult = executePlainSQL("SELECT * FROM Ranking ORDER BY ranking");
            $hstadresult = executePlainSQL("SELECT * FROM HomeStadium ORDER BY stadiumID");
            $teamresult = executePlainSQL("SELECT * FROM Team ORDER BY teamID");
            $pinforesult = executePlainSQL("SELECT * FROM PlayerInfo");
            $pmemresult = executePlainSQL("SELECT * FROM PlayerMember");
            $injresult = executePlainSQL("SELECT injID, memberID, injType, TO_CHAR(injDate, 'YYYY-MM-DD') FROM InjuryReport");
            $staffresult = executePlainSQL("SELECT * FROM Staff");
            $sponsorresult = executePlainSQL("SELECT * FROM Sponsor");
            $playsin = executePlainSQL("SELECT * FROM PlaysIn");
            $receives = executePlainSQL("SELECT * FROM Receives");
            $plays = executePlainSQL("SELECT * FROM Plays");
            $employs = executePlainSQL("SELECT * FROM Employs");

            printGameResult($gameresult);
            printRefResult($refereeresult);
            printMstaffResult($mstaffresult);
            printRankingResult($rankingresult);
            printHstadResult($hstadresult);
            printTeamResult($teamresult);
            printPinfoResult($pinforesult);
            printPmemResult($pmemresult);
            printInjResult($injresult);
            printStaffResult($staffresult);
            printSponsorResult($sponsorresult);
            printPlaysinResult($playsin);
            printReceivesResult($receives);
            printPlaysResult($plays);
            printEmploysResult($employs);
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

		if (isset($_GET['display'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>
