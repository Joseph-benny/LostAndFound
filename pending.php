<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending claims with JOINs
$sql = "
    SELECT 
        c.claim_id, c.user_date_lost, c.claim_description, c.proof_image, c.claim_status,
        f.found_id, f.item_name, f.description AS found_description, f.date_found, f.item_image, f.user_id AS finder_id,
        u1.first_name AS finder_fname, u1.last_name AS finder_lname, u1.phone AS finder_phone,
        u2.user_id AS claimer_id, u2.first_name AS claimer_fname, u2.last_name AS claimer_lname, u2.phone AS claimer_phone
    FROM claims c
    JOIN found_items f ON c.found_id = f.found_id
    JOIN users u1 ON f.user_id = u1.user_id  -- Finder details
    JOIN users u2 ON c.user_id = u2.user_id  -- Claimer details
    WHERE c.claim_status = 'pending'
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Claims</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container my-4">
  <h2 class="mb-4 text-center">Pending Claims</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Item: <?= htmlspecialchars($row['item_name']) ?></h5>
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-primary">Finder (Uploader)</h6>
              <p><b>Name:</b> <?= $row['finder_fname']." ".$row['finder_lname'] ?><br>
                 <b>Phone:</b> <?= $row['finder_phone'] ?><br>
                 <b>User ID:</b> <?= $row['finder_id'] ?></p>

              <h6 class="text-success">Claimer (Receiver)</h6>
              <p><b>Name:</b> <?= $row['claimer_fname']." ".$row['claimer_lname'] ?><br>
                 <b>Phone:</b> <?= $row['claimer_phone'] ?><br>
                 <b>User ID:</b> <?= $row['claimer_id'] ?></p>
            </div>

            <div class="col-md-6">
              <h6 class="text-danger">Claimer's Claim</h6>
              <p><b>Date Lost:</b> <?= $row['user_date_lost'] ?><br>
                 <b>Description:</b> <?= $row['claim_description'] ?></p>
              <?php if (!empty($row['proof_image'])): ?>
                <img src="uploads/<?= $row['proof_image'] ?>" class="img-fluid mb-2" style="max-height:150px;">
              <?php endif; ?>

              <h6 class="text-info">Actual Found Item</h6>
              <p><b>Date Found:</b> <?= $row['date_found'] ?><br>
                 <b>Description:</b> <?= $row['found_description'] ?></p>
              <?php if (!empty($row['item_image'])): ?>
                <img src="uploads/<?= $row['item_image'] ?>" class="img-fluid" style="max-height:150px;">
              <?php endif; ?>
            </div>
          </div>

          <!-- Status Toggle Button -->
          <button 
            class="btn btn-warning mt-3 toggle-status" 
            data-claim-id="<?= $row['claim_id'] ?>" 
            data-status="<?= $row['claim_status'] ?>">
              <?= ucfirst($row['claim_status']) ?>
          </button>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert alert-info">No pending claims found.</div>
  <?php endif; ?>

</div>

<script>
$(document).on("click", ".toggle-status", function() {
    var button = $(this);
    var claimId = button.data("claim-id");
    var currentStatus = button.data("status");

    // Cycle status
    var newStatus;
    if (currentStatus === "pending") {
        newStatus = "approved";
    } else if (currentStatus === "approved") {
        newStatus = "rejected";
    } else {
        newStatus = "pending";
    }

    // AJAX call to update status
    $.post("update_claim_status.php", { claim_id: claimId, claim_status: newStatus }, function(response) {
        if (response.success) {
            button.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
            button.data("status", newStatus);
            if (newStatus === "approved") {
                button.removeClass().addClass("btn btn-success mt-3 toggle-status");
            } else if (newStatus === "rejected") {
                button.removeClass().addClass("btn btn-danger mt-3 toggle-status");
            } else {
                button.removeClass().addClass("btn btn-warning mt-3 toggle-status");
            }
        } else {
            alert("Failed to update status!");
        }
    }, "json");
});
</script>
</body>
</html>
