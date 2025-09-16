<?php
header("Content-Type: application/json");
require 'claim_notification.php';  // Include your notification function

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
        // ✅ Send email notification to the user after status update
        // Fetch user_id from claims table for this claim
        $res = $conn->query("SELECT user_id FROM claims WHERE claim_id = $claim_id");
        if ($res->num_rows > 0) {
            $user_id = $res->fetch_assoc()['user_id'];

            // Call the reusable notification function
            sendClaimNotification($conn, $user_id, $claim_id, $claim_status);
        }

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
