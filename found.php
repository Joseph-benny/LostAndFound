<?php
session_start();
require 'send_mail.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $item_name = ucfirst(trim($_POST['item_name']));
    $location = ucfirst(trim($_POST['location']));
    $phone = trim($_POST['phone']);
    $date_found = $_POST['date_found'];
    $description = ucfirst(trim($_POST['description']));
    $status = "found_item";

    // ✅ Validate phone number length
    if (!preg_match("/^\d{10}$/", $phone)) {
        echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>";
        exit;
    }

    // ✅ Handle Image Upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = basename($_FILES["item_image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // ✅ Check allowed types
    $allowed_types = ['jpg','jpeg','png','gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.'); window.history.back();</script>";
        exit;
    }

    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        // ✅ Insert into database
        $stmt = $conn->prepare("INSERT INTO found_items (user_id, phone, item_name, description, date_found, location, status, item_image) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $phone, $item_name, $description, $date_found, $location, $status, $target_file);

        if ($stmt->execute()) {
            // ✅ Item added successfully
            $found_id = $conn->insert_id;

         
  // ✅ Get user's email and name
            $user_stmt = $conn->prepare("SELECT email, first_name FROM users WHERE user_id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_stmt->bind_result($email, $first_name);
            $user_stmt->fetch();
            $user_stmt->close();


    $subject = "Lost & Found - Item Submitted";
    $body = "Your reported Found item (Found ID: $found_id) has been submitted successfully.";
   
   $sent = sendMail($email, $subject, $body);
if (!$sent) {
    error_log("Email failed to send to $email");
}



            // 🔹 Add personal notification for user
            $message = "Your found item '$item_name' has been successfully reported.";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())");
            $notif_stmt->bind_param("is", $user_id, $message);
            $notif_stmt->execute();
            $notif_stmt->close();

            // 🔹 Add common notification for all users (global)
            $global_message = "New items are added, check whether it is yours.";
                   
        // Send global notification to all users
        $users = $conn->query("SELECT user_id FROM users");
        while ($row = $users->fetch_assoc()) {
            $uid = $row['user_id'];
            $conn->query("INSERT INTO notifications (user_id, message, status, created_at) VALUES ($uid, '$global_message', 'unread', NOW())");
        }
            echo "<script>alert('Found item reported successfully!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Error reporting item. Try again.'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Failed to upload image.'); window.history.back();</script>";
    }
}

$conn->close();
?>
