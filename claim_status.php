<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "lostfound");

$query = "SELECT c.claim_id, c.status, c.claimed_at, l.item_name
          FROM claims c
          JOIN lost_items l ON c.lost_id = l.lost_id
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Claim Status</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background: #007BFF; color: white; }
  </style>
</head>
<body>
  <h2>Your Claims</h2>
  <table>
    <tr>
      <th>Claim ID</th>
      <th>Item</th>
      <th>Status</th>
      <th>Submitted At</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
      <td><?= $row['claim_id'] ?></td>
      <td><?= $row['item_name'] ?></td>
      <td><?= ucfirst($row['status']) ?></td>
      <td><?= $row['claimed_at'] ?></td>
    </tr>
    <?php } ?>
  </table>
</body>
</html>
