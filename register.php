<?php
// Database connection
$servername = "localhost"; // or your server IP
$username = "root";        // your DB username
$password = "";            // your DB password
$dbname = "lostfound"; // replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$user_id = $_POST['user_id'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

// Insert into database
$sql = "INSERT INTO users (user_id,first_name, last_name, phone, email, password)
        VALUES ('$user_id','$first_name', '$last_name', '$phone', '$email', '$password')";

if ($conn->query($sql) === TRUE) {
  //echo "Registration successful!";
   header("Location:dashboard.html");
  exit;
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
