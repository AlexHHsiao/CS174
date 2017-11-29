<html>
<body>
<form action="main.php" method="POST" enctype="multipart/form-data">
    Select a text file to upload:
    <br>
    <input type="file" name="fileToUpload" id="fileToUpload" size="1" accept="text/plain">
    <br> <br>
    <input type="submit" value="Upload File" name="submit">
</form>

</body>
</html>

<?php

require_once "login.php";
// create mysqli
$conn = new mysqli($hn, $un, $pw, $db);

submitInput();

function submitInput()
{
    // after user click submit button, the program start running
    if (isset($_POST["submit"])) {

        // main function for the program
        main();
    }
}

function main()
{
    // Check if the file exist and in right type
    if (checker()) {
        $name = $_FILES['fileToUpload']['name']; // file name
        $path = $_FILES['fileToUpload']['tmp_name']; // the tmp file that we will use to read

        echo "Reading from file \"", $name, "\"", "<br>";

        $content = file_get_contents($path);
        $length = filesize($path);

        $binCon = '';
        for ($i = 0; $i < ($length < 20? $length : 20); $i++) {
            $binCon .= sprintf("%08b", ord($content[$i]));
        }
        echo $binCon, "<br>";
        var_dump($binCon);


//        // We will read each line of the file and store in a array as the content of row
//        // until we see the separator '---'
//        while (($line = fgets($fh)) !== false) {
//            // If separator is not found, then keep pushing each line into array as table data.
//            // once we see the separator '---', pass the array to process table function
//            // to process data and empty array for next part of data.
//
//            if (trim($line) === "---") {
//                processTable($tableArray);
//                $tableArray = array();
//                $counter = 0;
//            } else {
//                $tableArray[$counter] = trim($line);
//                $counter++;
//            }
//        }
//
//        // get the last table
//        processTable($tableArray);
    }
}

function processTable($tableArray)
{
    echo "<pre>";
    print_r($tableArray);
    echo "</pre>";

    global $conn;

    if ($conn->connect_error)
        die($conn->connect_error);

    // create database if it doesn't exist
    $query = "CREATE DATABASE IF NOT EXISTS " . $tableArray[0];
    $result = $conn->query($query);
    if (!$result) die($conn->error);

    //select db
    mysqli_select_db($conn, $tableArray[0]);

    // check if Table Exists
    $exists = $conn->query("select 1 from " . $tableArray[1]);

    // if the table doesn't exist, create one
    if ($exists === FALSE) {
        echo "This table doesn't exist, going to create one", "<br>";

//        $query = "CREATE TABLE classics (
//                      author VARCHAR(128),
//                      title VARCHAR(128),
//                      type VARCHAR(16),
//                      year CHAR(4) ) ENGINE MyISAM;";

        $query = "CREATE TABLE classics (
                      col1 VARCHAR(128)";

        // we don't know how many col will be in the table, loop the array and add into sql command

        for ($i = 3; $i < count($tableArray); $i++) {
            $query = $query . ", col" . (string)($i - 1) . " VARCHAR(128)";
        }

        $query = $query . ") ENGINE MyISAM;";

        $result = $conn->query($query);
        if (!$result) die($conn->error);
    }

    // create insert query that insert values into table
    $query = "INSERT INTO " . $tableArray[1];

    $query = $query . " VALUES ('" . $tableArray[2] . "'";

    for ($i = 3; $i < count($tableArray); $i++) {
        $query = $query . ", '" . $tableArray[$i] . "'";
    }

    $query = $query . ")";

    // insert value into table, print error otherwise
    $result = $conn->query($query);
    if (!$result) die($conn->error);
}

function checker()
{
    // User is able to click upload button without select any file
    // This if will check file size, if the size is 0, which means that
    // there is no file been selected
    if ($_FILES['fileToUpload']['size'] === 0) {
        echo "Please select a file to upload!!";
        return false;
    }

    // The input type is limited to text file, but I will still check here
    // If the type of file is not text, display warnning message
    if ($_FILES['fileToUpload']['type'] !== 'text/plain') {
        echo "Sorry, only txt files are allowed!!!";
        return false;
    }

    // return ture if all cases are passed
    return true;
}

function selectQuery($selectOption, $table, $other) {
    $query = "SELECT " . $selectOption . " FROM " . $table;

    if ($other) {
        $query = $query . " " . $other;
    }

    return $query;
}

function insertQuery($table, $data) {
    $query = "INSERT INTO " . $table;

    $query = $query . " VALUES ('" . $data[0] . "'";

    for ($i = 1; $i < count($table); $i++) {
        $query = $query . ", '" . $table[$i] . "'";
    }

    $query = $query . ")";

    return $query;
}



?>