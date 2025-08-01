<?php
session_start(); // Start session to hold user login info

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and sanitize input values
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Check if email or password is empty
if (empty($email) || empty($password)) {
    echo "<script>
        alert('Please enter both email and password.');
        window.location.href = 'login.html';
    </script>";
    exit;
}

// Prepare SQL query to fetch user by email
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// If a user is found
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify the hashed password
    if (password_verify($password, $user['password'])) {
        // Password matched — start session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];

        // Redirect to dashboard or protected page
        header("Location: dashboard.html"); // You can change to dashboard.php if needed
        exit;
    } else {
        // Password didn't match
        echo "<script>
            alert('Incorrect password. Please try again.');
            window.location.href = 'login.html';
        </script>";
        exit;
    }
} else {
    // No user found with that email
    echo "<script>
        alert('User not found. Please sign up.');
        window.location.href = 'login.html';
    </script>";
    exit;
}

$conn->close();
?>
