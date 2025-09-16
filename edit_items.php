<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login_process.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID & type from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

$user_id = $_SESSION['user_id'];

if ($type === 'lost') {
    $sql = "SELECT * FROM lost_items WHERE lost_id = ? AND user_id = ?";
} elseif ($type === 'found') {
    $sql = "SELECT * FROM found_items WHERE found_id = ? AND user_id = ?";
} else {
    echo "<script>alert('Invalid item type!'); window.location.href='dashboard.php';</script>";
    exit;
}


$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "<script>alert('Item not found or no permission to edit!'); window.location.href='dashboard.php';</script>";
    exit;
}


// Handle form submission separately for lost and found
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);

    if ($type === 'lost') {
        $date_lost = $_POST['date_lost'];
        $update_sql = "UPDATE lost_items SET item_name=?, description=?, date_lost=?, location=? WHERE lost_id=? AND user_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssii", $item_name, $description, $date_lost, $location, $id, $user_id);
    } elseif ($type === 'found') {
        $date_found = $_POST['date_found'];
        $update_sql = "UPDATE found_items SET item_name=?, description=?, date_found=?, location=? WHERE found_id=? AND user_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssii", $item_name, $description, $date_found, $location, $id, $user_id);
    }

    if ($update_stmt->execute()) {
        echo "<script>alert('Item updated successfully!'); window.location.href = 'dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating item.');</script>";
    }
}
?>
