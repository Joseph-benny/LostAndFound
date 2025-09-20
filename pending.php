<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending claims
$sql = "
    SELECT 
        c.claim_id, c.user_date_lost, c.claim_description, c.proof_image, c.claim_status,
        f.found_id, f.item_name, f.description AS found_description, f.date_found, f.item_image, f.user_id AS finder_id,
        u1.first_name AS finder_fname, u1.last_name AS finder_lname, u1.phone AS finder_phone,
        u2.user_id AS claimer_id, u2.first_name AS claimer_fname, u2.last_name AS claimer_lname, u2.phone AS claimer_phone
    FROM claims c
    JOIN found_items f ON c.found_id = f.found_id
    JOIN users u1 ON f.user_id = u1.user_id
    JOIN users u2 ON c.user_id = u2.user_id
    WHERE c.claim_status = 'pending'
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Claims</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
      min-height: 100vh;
      color: #e3eafc;
    }
    .navbar-custom {
      background: linear-gradient(90deg, #0d1a2f 80%, #1976d2 100%);
    }
    .navbar-brand, .nav-link {
      letter-spacing: 2px;
      font-size: 1.1rem;
      color: #e3eafc !important;
    }
    .navbar-brand i {
      color: #90caf9;
    }
    .section-title {
      color: #90caf9;
      letter-spacing: 2px;
      margin-bottom: 2rem;
      text-align: center;
      font-weight: bold;
    }
    .card {
      background: #162447;
      color: #e3eafc;
      border: none;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(25, 118, 210, 0.2);
    }
    .card-title {
      color: #90caf9;
      font-weight: bold;
    }
    .thumb-img {
      max-height: 120px;
      margin: 5px;
      cursor: pointer;
      border-radius: 5px;
      transition: transform 0.2s;
      border: 2px solid #1976d2;
      background: #12203a;
    }
    .thumb-img:hover {
      transform: scale(1.1);
      border-color: #90caf9;
    }
    .btn-warning, .btn-success, .btn-danger {
      border-radius: 8px;
      font-weight: 500;
      letter-spacing: 1px;
    }
    .alert-info {
      background: #12203a;
      color: #90caf9;
      border: none;
      border-radius: 0.7rem;
      font-size: 1.1rem;
    }
    .modal-content.bg-dark {
      background: #162447 !important;
      color: #e3eafc;
      border-radius: 1rem;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="home.php">
      <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link" href="home.php">Home</a>
        <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-circle-user"></i></a>
        <a class="nav-link" href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container my-4">
  <h2 class="section-title"><i class="fa fa-hourglass-half me-2"></i>Pending Claims</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php
      // Fetch all extra images for this found_id
      $extra_images = [];
      $img_res = $conn->query("SELECT image_path FROM item_images WHERE item_id = {$row['found_id']} AND item_type = 'found'");
      while ($img = $img_res->fetch_assoc()) {
          $extra_images[] = $img['image_path'];
      }
      ?>
      <div class="card shadow-lg mb-4">
        <div class="card-body">
          <h5 class="card-title"><i class="fa fa-box-open me-2"></i>Item: <?= htmlspecialchars($row['item_name']) ?></h5>
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-primary">Finder (Uploader)</h6>
              <p><b>Name:</b> <?= htmlspecialchars($row['finder_fname']." ".$row['finder_lname']) ?><br>
                 <b>Phone:</b> <?= htmlspecialchars($row['finder_phone']) ?><br>
                 <b>User ID:</b> <?= htmlspecialchars($row['finder_id']) ?></p>

              <h6 class="text-success">Claimer (Receiver)</h6>
              <p><b>Name:</b> <?= htmlspecialchars($row['claimer_fname']." ".$row['claimer_lname']) ?><br>
                 <b>Phone:</b> <?= htmlspecialchars($row['claimer_phone']) ?><br>
                 <b>User ID:</b> <?= htmlspecialchars($row['claimer_id']) ?></p>
            </div>

            <div class="col-md-6">
              <h6 class="text-danger">Claimer's Claim</h6>
              <p><b>Date Lost:</b> <?= htmlspecialchars($row['user_date_lost']) ?><br>
                 <b>Description:</b> <?= htmlspecialchars($row['claim_description']) ?></p>
              <?php if (!empty($row['proof_image'])): ?>
                <img src="<?= htmlspecialchars($row['proof_image']) ?>" class="thumb-img" onclick="showImage(this.src)">
              <?php endif; ?>

              <h6 class="text-info mt-3">All Uploaded Item Images</h6>
              <?php if (!empty($row['item_image'])): ?>
                <img src="<?= htmlspecialchars($row['item_image']) ?>" class="thumb-img" onclick="showImage(this.src)">
              <?php endif; ?>

              <?php foreach ($extra_images as $img_path): ?>
                <img src="<?= htmlspecialchars($img_path) ?>" class="thumb-img" onclick="showImage(this.src)">
              <?php endforeach; ?>
            </div>
          </div>

          <button class="btn btn-warning mt-3 toggle-status" data-claim-id="<?= $row['claim_id'] ?>" data-status="<?= $row['claim_status'] ?>">
              <?= ucfirst(htmlspecialchars($row['claim_status'])) ?>
          </button>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert alert-info text-center">No pending claims found.</div>
  <?php endif; ?>

</div>

<!-- Modal for Image Zoom -->
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark">
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>

<script>
function showImage(src) {
    document.getElementById("modalImage").src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Toggle status (unchanged)
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".toggle-status").forEach(function(button) {
    button.addEventListener("click", function() {
      var claimId = button.getAttribute("data-claim-id");
      var currentStatus = button.getAttribute("data-status");

      var newStatus = (currentStatus === "pending") ? "approved" :
                      (currentStatus === "approved") ? "rejected" : "pending";

      fetch("update_claim_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "claim_id=" + claimId + "&claim_status=" + newStatus
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          button.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
          button.setAttribute("data-status", newStatus);
          button.className = "btn mt-3 toggle-status btn-" +
            (newStatus === "approved" ? "success" : newStatus === "rejected" ? "danger" : "warning");
        } else {
          alert("Failed to update status!");
        }
      });
    });
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>