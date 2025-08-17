<?php
session_start();

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
if ($email === '' || $password === '') {
    echo "<script>
        alert('Please enter both email and password.');
        window.location.href = 'login.html';
    </script>";
    exit;
}

// Prepare SQL query to fetch user by email
$sql = "SELECT user_id, first_name, email, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// If a user is found
if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify the hashed password
    if (password_verify($password, $user['password'])) {
        // Store session details
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] == 0) {
            header("Location: dashboard.php");
            exit;
        } else if ($user['role'] == 1) {
            header("Location: admin.php");
            exit;
        } 
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
