<?php
require_once "login.php";
require_once "auth.php";
?>
<!DOCTYPE html>
<head>
    <style>
        .login {
            background-color: gray;
            color: whitesmoke;
            font-size: large;
        }
    </style>

    <script>

        // after submit the form, data will be check by each function
        function validate(form) {
            // check the name
            fail = validateName(form.name.value);

            // if there is a error, show it
            if (fail === "") return true;
            else {
                alert(fail);
                return false
            }
        }

        function validateName(field) {
            // if name is empty
            if (field == "") return "No Malware Name was entered.\n"
            else if (field.length < 5) // if name is less than 5 words
                return "Malware Name must be at least 5 characters.\n"
            else if (/[^a-zA-Z0-9_-]/.test(field)) // if name contains special char not allowed
                return "Only a-z, A-Z, 0-9, - and _ allowed in Malware Name.\n"
            return ""
        }
    </script>
</head>
<html>
<body>

<?php
echo <<<_END
<div class="login">
    <p>
        $authMsg
    </p>
</div>
_END;

?>


<table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
    <form action="main.php" method="POST" enctype="multipart/form-data">
        <th colspan="2" align="center">Select a text file to check</th>

        <tr>
            <td>
                <input type="file" name="fileToUpload" id="fileToUpload" size="1" accept="text/plain">
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <input type="submit" value="Upload File" name="submit">
            </td>
        </tr>
    </form>
</table>

<?php

// if user is admin, display html for uploading surely infected file
if (isset($_SESSION['username'])) {
    echo <<<_END
    <br><br>
    <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
    
        <th colspan="2" align="center">Please upload a surely infected file</th> 
    
        <form action="main.php" method="POST" enctype="multipart/form-data" onsubmit="return validate(this)">
            <tr>
            <td><input type="file" name="adminFile" id="adminFile" size="1" accept="text/plain">
            </td>
            </tr>

            <tr>
            <td>Malware Name</td>
            <td>
                <input type="text" maxlength="16" name="name">
            </td>
            </tr>
            
            <tr>
            <td colspan="2" align="center">
                <input type="submit" value="Upload File" name="adminSub">
            </td>
            </tr>
          
        </form>
    </table>
_END;

    destroy_session_and_data();
}

echo "</body></html>";


// create mysqli
$conn = new mysqli($hn, $un, $pw, $db);

submitInput();

function submitInput()
{
    // after user click submit button, the program start checking malicious content
    if (isset($_POST["submit"])) {
        // check malicious content function for the program
        checkMalicious();
    }

    // after admin click submint button, the program start uploading malicious content
    if (isset($_POST["adminSub"])) {
        // upload malicious content function for the program
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

        $content = file_get_contents($path); // get all content of the file
        $length = filesize($path); // get the size of all content

        $binCon = '';
        // convert each word to binary format
        for ($i = 0; $i < $length; $i++) {
            $binCon .= sprintf("%08b", ord($content[$i]));
        }

        // select all malicious content from database
        $query = "SELECT * FROM infected_info";
        $result = $conn->query($query);
        if (!$result) die($conn->error);

        // use for loop to loop all malicious contents from database and check if the file
        // contains any malicious content
        for ($i = 0; $i < $result->num_rows; $i++) {

            $row = $result->fetch_row();

            // if malicious content is found, print the malicious content name and stop program
            if (strpos($binCon, $row[1]) !== false) {
                echo "Found!!! The file " . $name . " contain malicious " . $row[0], "<br>";
                return;
            }
        }

        echo "The file " . $name . " is safe, and doesn't contain any malicious content.";
    }
}

function insertMalicious()
{
    // Check if the file exist and in right type
    if (checkerAdmin()) {
        $malwareName = "";

        // get the malware name
        if (isset($_POST['name'])) {
            $malwareName = fix_string($_POST['name']);
        }

        global $conn;

        $name = $_FILES['adminFile']['name']; // file name
        $path = $_FILES['adminFile']['tmp_name']; // the tmp file that we will use to read

        echo "Reading from file \"", $name, "\"", "<br>"; // get all content of the file

        // first check if this malicious content exist in the database already.
        $exists = $conn->query("SELECT * FROM infected_info WHERE name='$malwareName'");

        // if the select query has length 0, which means that this malicious content doesn't exist in the database
        if ($exists->num_rows === 0) {
            $content = file_get_contents($path); // get all content of the file
            $length = filesize($path); // get the length of all content

            $binCon = '';

            // if the length of all content is less that 20, we select all content. Otherwise we will select
            // the first 20 words in the file
            for ($i = 0; $i < ($length < 20 ? $length : 20); $i++) {
                // convert each word into binary format
                $binCon .= sprintf("%08b", ord($content[$i]));
            }

            // insert the malicious content into database (name, content)
            add_Mali($conn, $malwareName, $binCon);
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

// helper function for inserting malicious content to the database
function add_Mali($connection, $name, $bin)
{
    $query = "INSERT INTO infected_info VALUES('$name', '$bin')";
    $result = $connection->query($query);
    if (!$result) die($connection->error);
}

// helper function for reading variable from html input
function fix_string($string)
{
    if (get_magic_quotes_gpc())
        $string = stripslashes($string);
    return htmlentities($string);
}

function destroy_session_and_data() {
    $_SESSION = array();
    unset($_SERVER['PHP_AUTH_USER']);
    unset($_SERVER['PHP_AUTH_PW']);
    setcookie(session_name(), '', time() - 2592000, '/');
    session_destroy();
}

?>