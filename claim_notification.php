<?php
require 'send_mail.php'; // for sending mail

function sendClaimNotification($conn, $user_id, $claim_id, $status) {
    // Step 1: Get user's email
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    if (!$email) {
        return; // no email found
    }

    // Step 2: Prepare status message
    if ($status == "approved") {
        $message = "Your requested claim (ID: $claim_id) has been approved by the admin.";
    } elseif ($status == "rejected") {
        $message = "Your requested claim (ID: $claim_id) has been rejected by the admin.";
    } else {
        $message = "Your requested claim (ID: $claim_id) is pending admin review.";
    }

    // Step 3: Send mail
    $subject = "Lost & Found - Claim Status Update";
    $body = $message;
    sendMail($email, $subject, $body);

    // Step 4: Add in-app notification
    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES (?, ?, 'unread', NOW())");
    $notif_stmt->bind_param("is", $user_id, $message);
    $notif_stmt->execute();
    $notif_stmt->close();
}
?>
