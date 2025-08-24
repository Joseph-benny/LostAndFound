<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get item id from URL
if (!isset($_GET['found_id'])) {
    die("Invalid request. No item selected.");
}
$found_id = intval($_GET['found_id']);

// Database connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_date_lost = $_POST['user_date_lost'];
    $claim_description = $_POST['claim_description'];
    $claim_status = "pending"; // default

    // Handle proof image upload
    $proof_image = NULL;
    if (isset($_FILES["proof_image"]) && $_FILES["proof_image"]["error"] == 0) {
        $target_dir = "uploads/claims/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["proof_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
                $proof_image = $target_file;
            }
        }
    }

    // Insert into claims table
    $stmt = $conn->prepare("INSERT INTO claims (found_id, user_id, user_date_lost, claim_description, proof_image, claim_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $found_id, $user_id, $user_date_lost, $claim_description, $proof_image, $claim_status);

    if ($stmt->execute()) {
        echo "<div style='padding:20px; background:#d4edda; color:#155724;'>Claim submitted successfully! Status: Pending</div>";
    } else {
        echo "<div style='padding:20px; background:#f8d7da; color:#721c24;'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claim Item</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
      min-height: 100vh;
      color: #e3eafc;
    }
    .card {
      background: #162447;
      color: #e3eafc;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(25, 118, 210, 0.2);
    }
    .form-label {
      color: #90caf9;
      font-weight: 500;
    }
    .btn-primary {
      background-color: #1976d2;
      color: #fff;
      font-weight: 500;
      letter-spacing: 1px;
      border-radius: 8px;
      border: none;
    }
    .btn-primary:hover {
      background-color: #1565c0;
      color: #fff;
    }
    .section-title {
      color: #90caf9;
      letter-spacing: 2px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: bold;
    }
    .alert-success, .alert-danger {
      border-radius: 0.7rem;
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow p-4">
        <h3 class="section-title"><i class="fa fa-hand-holding me-2"></i>Claim This Item</h3>
        <form action="" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="user_date_lost" class="form-label">When did you lose it?</label>
            <input type="date" class="form-control" id="user_date_lost" name="user_date_lost" required>
          </div>
          <div class="mb-3">
            <label for="claim_description" class="form-label">Description (unique marks, details, etc.)</label>
            <textarea class="form-control" id="claim_description" name="claim_description" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label for="proof_image" class="form-label">Upload Proof (optional)</label>
            <input type="file" class="form-control" id="proof_image" name="proof_image" accept="image/*">
          </div>
          <button type="submit" class="btn btn-primary w-100"><i class="fa fa-paper-plane me-1"></i>Submit Claim</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font