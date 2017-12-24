<?php
$connection = new mysqli($hn, $un, $pw, $db);

$authMsg = "";

if ($connection->connect_error) die($connection->connect_error);

if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    // if ($_SERVER['PHP_AUTH_USER'])  and  ($_SERVER['PHP_AUTH_PW']) are set

    // get user name and password from user
    $un_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_USER']);
    $pw_temp = mysql_entities_fix_string($connection, $_SERVER['PHP_AUTH_PW']);

    // get admin information from the database
    $query = "SELECT * FROM users WHERE username='$un_temp'";
    $result = $connection->query($query);
    if (!$result) die($connection->error);
    else if ($result->num_rows) {
        // if the select query returns data, which means that admin table is not empty

        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();

        // tokens use to encrypt the password from user entered
        $salt1 = "qm&h*";
        $salt2 = "pg!@";
        $token = hash("ripemd128", "$salt1$pw_temp$salt2");

        // if the password user entered matches with the password in the admin table, the user is authed as admin
        if ($token == $row[3]) {

            // store user information into session for future usage
            session_start();
            $_SESSION['username'] = $un_temp;
            $_SESSION['password'] = $pw_temp;
            $_SESSION['forename'] = $row[0];
            $_SESSION['surname'] = $row[1];

            $authMsg =  "$row[0] $row[1] : Hi $row[0], you are now logged in as '$row[2]'";
        } else {
            // if the password user entered doesn't matches with the password in the admin table, the user is not authed as admin
            $authMsg = "You are not authenticated as an admin, so you can only upload putative infected file to be checked!";
        }
    } else {
        // if the select query return no data, which means that the admin table is empty

        $authMsg = "You are not authenticated as an admin, so you can only upload putative infected file to be checked!";
    }
} else {    // if ($_SERVER['PHP_AUTH_USER'])  and  ($_SERVER['PHP_AUTH_PW']) are not set
    header('WWW-Authenticate: Basic realm="Restricted Section"');
    header('HTTP/1.0 401 Unauthorized');
    $authMsg = "You are not authenticated as an admin, so you can only upload putative infected file to be checked!";
}
$connection->close();

function mysql_entities_fix_string($connection, $string)
{
    return htmlentities(mysql_fix_string($connection, $string));
}

function mysql_fix_string($connection, $string)
{
    if (get_magic_quotes_gpc()) $string = stripslashes($string);
    return $connection->real_escape_string($string);
}
?>
