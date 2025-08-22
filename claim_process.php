<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$lost_id = $_POST['lost_id'];
$user_date_lost = $_POST['user_date_lost'];
$claim_description = $_POST['claim_description'];

// Handle optional proof image
$proof_image = NULL;
if (!empty($_FILES['proof_image']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $proof_image = $target_dir . time() . "_" . basename($_FILES["proof_image"]["name"]);
    move_uploaded_file($_FILES["proof_image"]["tmp_name"], $proof_image);
}

$stmt = $conn->prepare("INSERT INTO claims (lost_id, user_id, user_date_lost, claim_description, proof_image, status) 
                        VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("iisss", $lost_id, $user_id, $user_date_lost, $claim_description, $proof_image);

if ($stmt->execute()) {
    header("Location: claim_status.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
