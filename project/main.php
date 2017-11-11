<?php

submitInput();

function submitInput() {
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

        // open the file with read option
        $fh = fopen("$path", 'r') or
        die("File does not exist or you lack permission to open it");

        // We will read each line of the file and check for all numbers
        while (($line = fgets($fh)) !== false) {
            checkDig($line); // pass the line to check numbers
        }

        // close file
        fclose($fh);
    }
}

function checkDig($line) {
    // First to store each word separated by space into an array
    $a = explode(" ", $line);

    // The for loop will run through each element in the array and check it
    for ($i = 0; $i < sizeof($a); $i++) {
        // Clean all extra whitespace to prevent case like "1 " or " 3434 "
        $a[$i] = preg_replace('/\s+/', '', $a[$i]);

        // Print the word if it is number or number string
        if (is_numeric($a[$i])) {
            echo $a[$i], " ";
        }
    }

    echo "<br>";
}

echo "<br>";
echo "Here is the test case for checkDig functon: ", "<br>";
checkDig("If Hamsik17 scores another goal, he will reach Maradona10 with 116 total scores in Napoli");
checkDig("0 0 0 0 12");
checkDig("123 alex334243234");
checkDig("");
checkDig("     ");

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

?>