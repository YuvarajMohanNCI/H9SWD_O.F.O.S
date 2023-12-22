<?php
try{
$servername = "localhost"; // Server
$username = "root"; // Username

$password = ""; // Password 
$dbname = "onlinefoodphp";  // Database

// Create connection
$db = new mysqli($servername, $username, $password, $dbname); 

// Check connection
if ($db->connect_error) { 
    header("Location: error_page.php");
    die("Connection failed: " . $db->connect_error);

}}
catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        header("Location: error_page.php");
}

?>