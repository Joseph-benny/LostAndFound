<?php
session_start();
// **Admin check goes here**
// In a real application, check for an admin role or a specific user ID.
/*
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: login.html");
    exit;
}
*/

// DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and validate claim ID from URL
$claim_id = isset($_GET['claim_id']) ? intval($_GET['claim_id']) : 0;
if ($claim_id <= 0) {
    die("Invalid claim ID.");
}

// Fetch the claim details
$sqlClaim = "SELECT * FROM claims WHERE claim_id = ?";
$stmtClaim = $conn->prepare($sqlClaim);
$stmtClaim->bind_param("i", $claim_id);
$stmtClaim->execute();
$resultClaim = $stmtClaim->get_result();
if ($resultClaim->num_rows === 0) {
    die("Claim not found.");
}
$claim = $resultClaim->fetch_assoc();
$stmtClaim->close();

// Determine item table and ID
$item_table = $claim['item_table'];
$item_id_field = ($item_table === 'lost_items') ? 'lost_id' : 'found_id';
$item_id = $claim['item_id'];

// Fetch the item details and the original uploader's ID
$sqlItem = "SELECT user_id, item_name, description, location, item_image FROM $item_table WHERE $item_id_field = ?";
$stmtItem = $conn->prepare($sqlItem);
$stmtItem->bind_param("i", $item_id);
$stmtItem->execute();
$resultItem = $stmtItem->get_result();
$item = $resultItem->fetch_assoc();
$stmtItem->close();

$uploader_id = $item['user_id'];
$claimer_id = $claim['claimer_id'];

// Fetch claimer's details
$sqlClaimer = "SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?";
$stmtClaimer = $conn->prepare($sqlClaimer);
$stmtClaimer->bind_param("i", $claimer_id);
$stmtClaimer->execute();
$resultClaimer = $stmtClaimer->get_result();
$claimer = $resultClaimer->fetch_assoc();
$stmtClaimer->close();

// Fetch uploader's details
$sqlUploader = "SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?";
$stmtUploader = $conn->prepare($sqlUploader);
$stmtUploader->bind_param("i", $uploader_id);
$stmtUploader->execute();
$resultUploader = $stmtUploader->get_result();
$uploader = $resultUploader->fetch_assoc();
$stmtUploader->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Claim Details for Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d1a2f, #1976d2); min-height: 100vh; color: #fff; }
        .container { margin-top: 40px; background: rgba(13, 26, 47, 0.95); padding: 30px; border-radius: 12px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        .list-group-item { background: transparent; color: #fff; border-color: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center text-info mb-4">Claim Details</h2>
    <hr>
    
    <h4 class="text-warning">Item Information</h4>
    <div class="row">
        <div class="col-md-4 mb-3">
            <img src="<?= htmlspecialchars($item['item_image']) ?>" class="img-fluid rounded border border-info" alt="Item Image">
        </div>
        <div class="col-md-8">
            <ul class="list-group">
                <li class="list-group-item"><strong>Item Name:</strong> <?= htmlspecialchars($item['item_name']) ?></li>
                <li class="list-group-item"><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></li>
                <li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></li>
                <li class="list-group-item">
                    <strong>Status:</strong>
                    <span class="badge <?= ($item_table === 'lost_items') ? 'bg-danger' : 'bg-success' ?>">
                        <?= ($item_table === 'lost_items') ? 'Lost' : 'Found' ?>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-md-6 mb-4">
            <h4 class="text-success">Claimer Information</h4>
            <ul class="list-group">
                <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($claimer['first_name'] . ' ' . $claimer['last_name']) ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($claimer['email']) ?></li>
                <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($claimer['phone']) ?></li>
            </ul>
        </div>
        
        <div class="col-md-6 mb-4">
            <h4 class="text-primary">Uploader Information</h4>
            <ul class="list-group">
                <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($uploader['first_name'] . ' ' . $uploader['last_name']) ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($uploader['email']) ?></li>
                <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($uploader['phone']) ?></li>
            </ul>
        </div>
    </div>
    
    <hr class="my-4">

    <h4 class="text-info">Claimer's Answers</h4>
    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Date Claimed:</strong> <?= htmlspecialchars($claim['user_date']) ?></li>
        <li class="list-group-item"><strong>Claim Description:</strong> <?= nl2br(htmlspecialchars($claim['claim_description'])) ?></li>
        <li class="list-group-item">
            <strong>Proof Image:</strong> 
            <?php if (!empty($claim['proof_image'])): ?>
                <a href="<?= htmlspecialchars($claim['proof_image']) ?>" target="_blank" class="btn btn-sm btn-info ms-2">View Proof</a>
            <?php else: ?>
                No image provided.
            <?php endif; ?>
        </li>
    </ul>

    <div class="text-center">
        <h4 class="text-light">Manage Status:</h4>
        <a href="update_claim.php?claim_id=<?= $claim_id ?>&status=approved" class="btn btn-success btn-lg mx-2">Approve</a>
        <a href="update_claim.php?claim_id=<?= $claim_id ?>&status=rejected" class="btn btn-danger btn-lg mx-2">Reject</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>