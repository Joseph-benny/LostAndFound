<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lostfound");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lost_id'])) {
    $lost_id = intval($_POST['lost_id']);

    $stmt = $conn->prepare("UPDATE lost_items SET claim_state = 'claimed' WHERE lost_id = ?");
    $stmt->bind_param("i", $lost_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to dashboard
    header("Location: dashboard.php");
    exit;
}

$conn->close();
?>
