<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

/*if (!isset($_GET['item_id'])) {
    die("No item selected.");
}*/
$lost_id = $_GET['lost_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claim Item</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .form-container {
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 6px;
      background: #007BFF;
      color: #fff;
      font-size: 15px;
      cursor: pointer;
    }
    button:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Claim This Item</h2>
    <form action="claim_process.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="lost_id" value="<?= $lost_id ?>">

      <label>Date you lost it:</label>
      <input type="date" name="user_date_lost" required>

      <label>Description:</label>
      <textarea name="claim_description" rows="3" placeholder="Describe the item and unique features" required></textarea>

      <label>Upload proof (optional):</label>
      <input type="file" name="proof_image" accept="image/*">

      <button type="submit">Send for approval</button>
    </form>
  </div>
</body>
</html>
