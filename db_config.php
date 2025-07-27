<?php
error_reporting(0); // Suppress all errors
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound"; // Make sure this matches your DB

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // Do nothing (fail silently)
}
?>

