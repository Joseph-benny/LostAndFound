<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

if (isset($_POST['claim_id'], $_POST['claim_status'])) {
    $claim_id = intval($_POST['claim_id']);
    $claim_status = $_POST['claim_status'];

    $stmt = $conn->prepare("UPDATE claims SET claim_status = ? WHERE claim_id = ?");
    $stmt->bind_param("si", $claim_status, $claim_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Update failed"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
$conn->close();
?>
