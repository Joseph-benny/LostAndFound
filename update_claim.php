<?php
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$claim_id = $_GET['id'];
$status = $_GET['status'];

if (in_array($status, ['approved', 'rejected'])) {
    $stmt = $conn->prepare("UPDATE claims SET status=? WHERE claim_id=?");
    $stmt->bind_param("si", $status, $claim_id);
    $stmt->execute();
}

header("Location: admin_claims.php");
exit;
?>
