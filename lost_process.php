<?php
session_start();
require 'send_mail.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; 
$phone = $_POST['phone'] ?? '';
$item_name = ucfirst(trim($_POST['lost_item']));
$description = ucfirst(trim($_POST['description']));
$date_lost = $_POST['date_lost'] ?? '';
$location = ucfirst(trim($_POST['location']));
$status = "lost"; // default status
$image_path = null; // default null

// ✅ Validate phone number length
if (!preg_match("/^\d{10}$/", $phone)) {
    echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>";
    exit;
}

// ✅ Handle image upload (optional)
if (isset($_FILES["item_image"]) && $_FILES["item_image"]["error"] !== 4) {
    if ($_FILES["item_image"]["error"] === 0) {
        $image_name = basename($_FILES["item_image"]["name"]);
        $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $target_dir = "uploads/";
        $new_image_name = time() . "_" . $image_name;
        $target_file = $target_dir . $new_image_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_type, $allowed_types)) {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.'); window.location.href = 'lost.php';</script>";
            exit;
        }

        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            echo "<script>alert('Image upload failed.'); window.location.href = 'lost.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('File upload error.'); window.location.href = 'lost.php';</script>";
        exit;
    }
}

// ✅ Insert into lost_items table
$sql = "INSERT INTO lost_items (user_id, phone, item_name, description, date_lost, location, status, item_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $user_id, $phone, $item_name, $description, $date_lost, $location, $status, $image_path);

if ($stmt->execute()) {

    // ✅ Get user's email for sending mail
    $user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_stmt->bind_result($email);
    $user_stmt->fetch();
    $user_stmt->close();

    // ✅ Send email to the logged-in user
    $subject = "Lost & Found - Item Submitted";
    $body = "Your reported Lost item has been submitted successfully.";
    if (!sendMail($email, $subject, $body)) {
        error_log("Failed to send email to $email");
    }

    // ✅ Add notification for all users
    $message = "New lost items are added check whether you have those..";
    $notif_sql = "INSERT INTO notifications (user_id, message, status, created_at)
                  SELECT user_id, '$message', 'unread', NOW() FROM users";
    $conn->query($notif_sql);

    header("Location: home.php");
    exit;

} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
