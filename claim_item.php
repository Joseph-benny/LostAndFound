<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate GET params
$lost_id  = isset($_GET['lost_id']) ? (int)$_GET['lost_id'] : 0;
$found_id = isset($_GET['found_id']) ? (int)$_GET['found_id'] : 0;

if ($lost_id > 0) {
    $table = "lost_items";
    $id_field = "lost_id";
    $item_id = $lost_id;
} elseif ($found_id > 0) {
    $table = "found_items";
    $id_field = "found_id";
    $item_id = $found_id;
} else {
    die("Invalid request.");
}

// Check if claim already exists
$sql = "SELECT * FROM claims WHERE $id_field = ? AND user_id = ? ORDER BY claim_id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

// If claim is approved → redirect to item_details.php
if ($existing && $existing['claim_status'] === 'approved') {
    header("Location: item_details.php?$id_field=$item_id&table=$table&claim_id=" . $existing['claim_id']);
    exit;
}

// Handle claim submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_date_lost    = $_POST['user_date_lost'] ?? '';
    $claim_description = $_POST['claim_description'] ?? '';
    $claim_status      = "pending"; // default

    // Handle proof image upload
    $proof_image_path = null;
    if (!empty($_FILES["proof_image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . time() . "_" . basename($_FILES["proof_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
                $proof_image_path = $target_file;
            }
        }
    }

    if ($existing && $existing['claim_status'] === 'rejected') {
        // Update old claim (resubmit)
        $sql = "UPDATE claims SET user_date_lost=?, claim_description=?, proof_image=?, claim_status='pending' 
                WHERE claim_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $user_date_lost, $claim_description, $proof_image_path, $existing['claim_id']);
        $stmt->execute();
        $stmt->close();
        echo "<p>Your claim has been resubmitted for approval.</p>";

    } elseif (!$existing) {
        // Insert new claim
        if ($id_field === "lost_id") {
            $sql = "INSERT INTO claims (lost_id, user_id, user_date_lost, claim_description, proof_image, claim_status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "INSERT INTO claims (found_id, user_id, user_date_lost, claim_description, proof_image, claim_status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $item_id, $user_id, $user_date_lost, $claim_description, $proof_image_path, $claim_status);
        $stmt->execute();
        $stmt->close();

        echo "<p>Your claim has been submitted for approval.</p>";

    } else {
        echo "<p>You have already submitted a claim for this item. Current status: " . htmlspecialchars($existing['claim_status']) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Claim Item</h2>

    <?php if (!$existing || $existing['claim_status'] === 'rejected'): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="user_date_lost" class="form-label">When did you lose/find it?</label>
                <input type="date" class="form-control" name="user_date_lost" required>
            </div>
            <div class="mb-3">
                <label for="claim_description" class="form-label">Describe the item (unique details)</label>
                <textarea class="form-control" name="claim_description" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label for="proof_image" class="form-label">Upload Proof (optional)</label>
                <input type="file" class="form-control" name="proof_image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Submit Claim</button>
        </form>
    <?php elseif ($existing['claim_status'] === 'pending'): ?>
        <p class="alert alert-info">You have already submitted a claim. Current status: Pending Approval</p>
    <?php endif; ?>
</body>
</html>
