<?php
session_start();
require 'send_mail.php'; // Make sure this file has sendMail() function

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

    // ---------- Multiple-image upload + insert + notifications ----------
    $allowed_types = ['jpg','jpeg','png','gif'];
    $upload_dir = "uploads/";
    $uploaded_images = [];

    // ensure upload directory
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // at least one image
    if (empty($_FILES['item_images']['name'][0])) {
        echo "<script>alert('Please upload at least one image.'); window.history.back();</script>";
        exit;
    }

    // maximum 4 images
    if (count($_FILES['item_images']['name']) > 4) {
        echo "<script>alert('You can upload a maximum of 4 images.'); window.history.back();</script>";
        exit;
    }

    // process each uploaded file
    foreach ($_FILES['item_images']['name'] as $key => $filename) {
        $file_tmp = $_FILES['item_images']['tmp_name'][$key];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.'); window.history.back();</script>";
            exit;
        }

        $new_name = time() . "_" . uniqid() . "." . $file_ext;
        $file_path = $upload_dir . $new_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $uploaded_images[] = $file_path;
        } else {
            echo "<script>alert('Failed to upload one of the images.'); window.history.back();</script>";
            exit;
        }
    }

    // Insert first image into found_items table
    $first_image = $uploaded_images[0];
    $stmt = $conn->prepare("INSERT INTO found_items (user_id, phone, item_name, description, date_found, location, status, item_image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo "<script>alert('Server error.'); window.history.back();</script>";
        exit;
    }

    $stmt->bind_param("isssssss", $user_id, $phone, $item_name, $description, $date_found, $location, $status, $first_image);

    if ($stmt->execute()) {
        $found_id = $conn->insert_id;

        // Insert all images into item_images table
        $img_stmt = $conn->prepare("INSERT INTO item_images (item_id, item_type, image_path) VALUES (?, 'found', ?)");
        if ($img_stmt) {
            foreach ($uploaded_images as $img_path) {
                $img_stmt->bind_param("is", $found_id, $img_path);
                $img_stmt->execute();
            }
            $img_stmt->close();
        }

        // Personal notification for uploader
        $personal_msg = "Your found item '{$item_name}' (Item ID: {$found_id}, User ID: {$user_id}) has been successfully reported.";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())");
        if ($notif_stmt) {
            $notif_stmt->bind_param("is", $user_id, $personal_msg);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        // Global notification for all users
        $global_msg = "New items are added, check whether it is yours.";
        $global_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) SELECT user_id, ?, 'unread', NOW() FROM users");
        if ($global_stmt) {
            $global_stmt->bind_param("s", $global_msg);
            $global_stmt->execute();
            $global_stmt->close();
        }

        // Send mail to uploader if email exists
        $email_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
        if ($email_stmt) {
            $email_stmt->bind_param("i", $user_id);
            $email_stmt->execute();
            $email_stmt->bind_result($email);
            if ($email_stmt->fetch() && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $subject = "Lost & Found - Item Submitted";
                $body = "Your reported found item '{$item_name}' has been submitted successfully.\nItem ID: {$found_id}\nUser ID: {$user_id}";
                sendMail($email, $subject, $body);
            }
            $email_stmt->close();
        }

        // Success redirect
        echo "<script>alert('Found item reported successfully!'); window.location.href='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error reporting item. Try again.'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>
