<?php
session_start();
// You can add an admin check here if needed

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['claim_id'], $_POST['status'])) {
    $claim_id = intval($_POST['claim_id']);
    $status = $conn->real_escape_string($_POST['status']);

    // Update claim status
    $conn->query("UPDATE claims SET status='$status' WHERE claim_id=$claim_id");

    // Fetch user_id & lost item name for notification
    $result = $conn->query("SELECT c.user_id, l.item_name 
                            FROM claims c 
                            JOIN lost_items l ON c.lost_id = l.lost_id 
                            WHERE c.claim_id = $claim_id");
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];
    $item_name = $row['item_name'];

    // Notification message
    $message = "Your claim for <b>$item_name</b> has been <b>$status</b>.";

    // Insert notification
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();

    header("Location: admin_claims.php?updated=1");
    exit;
}


$query = "SELECT c.claim_id, c.user_id, c.user_date_lost, c.claim_description, 
                 c.proof_image, c.status, c.claimed_at,
                 l.date_lost AS actual_date, l.item_name
          FROM claims c
          JOIN lost_items l ON c.lost_id = l.lost_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Review Claims</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background: #007BFF; color: white; }
    img { max-width: 100px; }
    .btn { padding: 6px 10px; text-decoration: none; border-radius: 5px; }
    .approve { background: green; color: white; }
    .reject { background: red; color: white; }
  </style>
</head>
<body>
  <h2>Pending Claims</h2>
  <table>
    <tr>
      <th>Claim ID</th>
      <th>User ID</th>
      <th>Item</th>
      <th>Actual Date (DB)</th>
      <th>User Date</th>
      <th>Description</th>
      <th>Proof</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
      <td><?= $row['claim_id'] ?></td>
      <td><?= $row['user_id'] ?></td>
      <td><?= $row['item_name'] ?></td>
      <td><?= $row['actual_date'] ?></td>
      <td><?= $row['user_date_lost'] ?></td>
      <td><?= $row['claim_description'] ?></td>
      <td>
        <?php if ($row['proof_image']) { ?>
          <img src="<?= $row['proof_image'] ?>">
        <?php } else { echo "No proof"; } ?>
      </td>
      <td><?= ucfirst($row['status']) ?></td>
      <td>
        <?php if ($row['status'] == 'pending') { ?>
          <a class="btn approve" href="update_claim.php?id=<?= $row['claim_id'] ?>&status=approved">Approve</a>
          <a class="btn reject" href="update_claim.php?id=<?= $row['claim_id'] ?>&status=rejected">Reject</a>
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </table>
</body>
</html>
