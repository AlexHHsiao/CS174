<html>
<body>
<form action="main.php" method="POST" enctype="multipart/form-data">
    Select a text file to check:
    <br>
    <input type="file" name="fileToUpload" id="fileToUpload" size="1" accept="text/plain">
    <br> <br>
    <input type="submit" value="Upload File" name="submit">
</form>

<?php

require_once "login.php";
require_once "user.php";
require_once "auth.php";

if ($auth) {
    echo <<<_END
    <br><br>
    <form action="main.php" method="POST" enctype="multipart/form-data">
        Please upload a surely infected file:
        <br>
        <input type="file" name="adminFile" id="adminFile" size="1" accept="text/plain">
        <br> <br>
        <input type="submit" value="Upload File" name="adminSub">
    </form>
_END;
}

echo "</body></html>";


// create mysqli
$conn = new mysqli($hn, $un, $pw, $db);

submitInput();

function submitInput()
{
    // after user click submit button, the program start running
    if (isset($_POST["submit"])) {
        // main function for the program
        checkMalicious();
    }

    if (isset($_POST["adminSub"])) {
        insertMalicious();
    }
}

function checkMalicious()
{
    // Check if the file exist and in right type
    if (checker()) {

        global $conn;
        $name = $_FILES['fileToUpload']['name']; // file name
        $path = $_FILES['fileToUpload']['tmp_name']; // the tmp file that we will use to read

        echo "Checking file \"", $name, "\"", "<br>";

        $content = file_get_contents($path);
        $length = filesize($path);

        $binCon = '';
        for ($i = 0; $i < $length; $i++) {
            $binCon .= sprintf("%08b", ord($content[$i]));
        }

        $query = "SELECT * FROM infected_info";
        $result = $conn->query($query);
        if (!$result) die($conn->error);

        for ($i = 0; $i < $result->num_rows; $i++) {

            $row = $result->fetch_row();

            if (strpos($binCon, $row[1]) !== false) {
                echo "Found!!! The file " . $name . " contain malicious " . $row[0], "<br>";
                return;
            }
        }

        echo "The file " . $name . " is safe, and doesn't contain any malicious.";
    }
}

function insertMalicious() {
    if (checkerAdmin()) {

        global $conn;

        $name = $_FILES['adminFile']['name']; // file name
        $path = $_FILES['adminFile']['tmp_name']; // the tmp file that we will use to read

        echo "Reading from file \"", $name, "\"", "<br>";
        $maliciousName = substr($name, 0, strlen($name) - 4);

        $exists = $conn->query("SELECT * FROM infected_info WHERE name='$maliciousName'");

        if ($exists->num_rows === 0) {
            $content = file_get_contents($path);
            $length = filesize($path);

            $binCon = '';
            for ($i = 0; $i < ($length < 20 ? $length : 20); $i++) {
                $binCon .= sprintf("%08b", ord($content[$i]));
                echo $content[$i];
            }

            add_Mali($conn, $maliciousName, $binCon);
        }
    }
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

function checkerAdmin()
{
    // User is able to click upload button without select any file
    // This if will check file size, if the size is 0, which means that
    // there is no file been selected
    if ($_FILES['adminFile']['size'] === 0) {
        echo "Please select a file to upload!!";
        return false;
    }

    // The input type is limited to text file, but I will still check here
    // If the type of file is not text, display warnning message
    if ($_FILES['adminFile']['type'] !== 'text/plain') {
        echo "Sorry, only txt files are allowed!!!";
        return false;
    }

    // return ture if all cases are passed
    return true;
}

function selectQuery($selectOption, $table, $other)
{
    $query = "SELECT " . $selectOption . " FROM " . $table;

    if ($other) {
        $query = $query . " " . $other;
    }

    return $query;
}

function add_Mali($connection, $name, $bin)
{
    $query = "INSERT INTO infected_info VALUES('$name', '$bin')";
    $result = $connection->query($query);
    if (!$result) die($connection->error);
}

?>