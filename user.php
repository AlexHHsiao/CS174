<?php
$connection = new mysqli($hn, $un, $pw, $db);
if ($connection->connect_error) die($connection->connect_error);

// tokens help to encrypt the password
$salt1 = "qm&h*";
$salt2 = "pg!@";

// information for admin
$forename = "Alex";
$surname = "Xiao";
$username = "AX";
$password = "123456";

// encrypt the password
$token = hash("ripemd128", "$salt1$password$salt2");

// check if information of admin exist in the database already (if this program runs on the first time or not)
$exists = $connection->query("SELECT * FROM users WHERE username='$username'");

// if the database doesn't have any data for admin, insert the information into database
if ($exists->num_rows === 0) {
    add_user($connection, $forename, $surname, $username, $token);
}

// helper function for inserting admin information into the database
function add_user($connection, $fn, $sn, $un, $pw)
{
    $query = "INSERT INTO users VALUES('$fn', '$sn', '$un', '$pw')";
    $result = $connection->query($query);
    if (!$result) die($connection->error);
}

?>