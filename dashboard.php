<?php
session_start();
if (!isset($_SESSION['user_id'])|| $_SESSION['role'] != 0) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// DB connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// fetch unread notifications for the logged-in user (for initial render)
$notif_query = $conn->query("SELECT notification_id, message, created_at FROM notifications WHERE user_id = $user_id AND status = 'unread' ORDER BY created_at DESC LIMIT 5");
$notif_count = $notif_query ? $notif_query->num_rows : 0;

// Handle account deletion (unchanged)
if (isset($_GET['delete']) && $_GET['delete'] == $user_id) {
    $conn->query("DELETE FROM lost_items WHERE user_id = $user_id");
    $conn->query("DELETE FROM found_items WHERE user_id = $user_id");
    $conn->query("DELETE FROM users WHERE user_id = $user_id");

    session_destroy();
    echo "<script>
            alert('Your account and all reported items have been deleted.');
            window.location.href = 'home.php';
          </script>";
    exit;
}

$user_query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $user_query->fetch_assoc();

if (isset($user['status']) && $user['status'] == 'blocked') {
    session_destroy();
    echo "<script>
        alert('Your account is currently blocked by the admin.');
        window.location.href = 'login.html';
    </script>";
    exit;
}

// Fetch reported lost and found items
$lost_items = $conn->query("SELECT * FROM lost_items WHERE user_id = $user_id");
$found_items = $conn->query("SELECT * FROM found_items WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
      min-height: 100vh;
      color: #e3eafc;
    }
    .navbar-custom { background: linear-gradient(90deg, #0d1a2f 80%, #1976d2 100%); }
    .card { background: #162447; color: #e3eafc; border: none; }
    .card-title { color: #90caf9; }
    .btn-edit { background-color: #1976d2; color: #fff; }
    .btn-edit:hover { background-color: #1565c0; color: #fff; }
    .btn-logout { background-color: #d32f2f; color: #fff; }
    .btn-logout:hover { background-color: #b71c1c; color: #fff; }
    .section-title { color: #90caf9; margin-top: 2rem; margin-bottom: 1rem; letter-spacing: 2px; }
    .text-mutedtext-center{ color:white; }
  </style>
  <script>
    function logout() { window.location.href = "logout.php"; }
  </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="home.php">
      <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found
    </a>
    <div class="collapse navbar-collapse justify-content-end">
      <div class="navbar-nav">
        <a class="nav-link text-white mx-3" href="home.php">Home</a>
        <a class="nav-link text-white mx-3" href="about.html">About Us</a>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-bell"></i> Notifications 
            <span class="badge bg-danger"><?php echo $notif_count; ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown">
            <?php 
            if ($notif_count > 0) {
                while ($notif = $notif_query->fetch_assoc()) {
                    echo "<li><a class='dropdown-item' href='#'>{$notif['message']}</a></li>";
                }
            } else {
                echo "<li><a class='dropdown-item' href='#'>No new notifications</a></li>";
            }
            ?>
          </ul>
        </li>
      </div>
    </div>
    <button class="btn btn-logout ms-3" onclick="logout()"><i class="fa fa-sign-out-alt me-1"></i>Logout</button>
  </div>
</nav>

<div class="container">
  <div class="row mb-4">
    <div class="col-md-3 text-center">
      <img src="images/grey contacts icon.jpeg" class="rounded-circle mb-3" width="120" height="120" alt="Avatar">
    </div>
    <div class="col-md-9">
      <h2 class="fw-bold section-title">Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
      <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
      <a href="editprofile.php" class="btn btn-edit btn-sm mb-2"><i class="fa fa-edit me-1"></i>Edit Profile</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <h3 class="section-title text-center">Reported Lost Items</h3>
      <?php if ($lost_items->num_rows > 0): ?>
        <?php while ($lost = $lost_items->fetch_assoc()): ?>
          <div class="card mb-3 shadow">
            <div class="card-body">
  <?php if (!empty($lost['item_image'])): ?>
    <img src="<?php echo htmlspecialchars($lost['item_image']); ?>" class="img-fluid mb-2" style="max-height:150px; border-radius:8px;">
  <?php endif; ?>
  <h5 class="card-title"><?php echo htmlspecialchars($lost['item_name']); ?></h5>

              <p class="card-text"><?php echo nl2br(htmlspecialchars($lost['description'])); ?></p>
              <p class="card-text"><span class="badge bg-danger"><?php echo htmlspecialchars($lost['status']); ?></span></p>
              <div class="d-flex gap-2">
                <a href="edit_items.php?id=<?php echo $lost['lost_id']; ?>&type=lost" class="btn btn-edit btn-sm">
                  <i class="fa fa-edit me-1"></i>Edit
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-mutedtext-center">No lost items reported.</p>
      <?php endif; ?>
    </div>
    <div class="col-md-6">
      <h3 class="section-title text-center">Reported Found Items</h3>
      <?php if ($found_items->num_rows > 0): ?>
        <?php while ($found = $found_items->fetch_assoc()): ?>
          <div class="card mb-3 shadow">

          <div class="card-body">
  <?php if (!empty($found['item_image'])): ?>
    <img src="<?php echo htmlspecialchars($found['item_image']); ?>" class="img-fluid mb-2" style="max-height:150px; border-radius:8px;">
  <?php endif; ?>
  <h5 class="card-title"><?php echo htmlspecialchars($found['item_name']); ?></h5>


             <p class="card-text"><?php echo nl2br(htmlspecialchars($found['description'])); ?></p>
              <p class="card-text"><span class="badge bg-success"><?php echo htmlspecialchars($found['status']); ?></span></p>
              <div class="d-flex gap-2">
                <a href="edit_items.php?id=<?php echo $found['found_id']; ?>&type=found" class="btn btn-edit btn-sm">
                  <i class="fa fa-edit me-1"></i>Edit
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-mutedtext-center">No found items reported.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col text-center">
      <a href="home.php" class="btn btn-primary me-2"><i class="fa fa-eye me-1"></i>View Items</a>
      <a href="contact.html" class="btn btn-outline-secondary"><i class="fa fa-envelope me-1"></i>Contact Us</a><br><br>
      <a href="dashboard.php?delete=<?= $user['user_id'] ?>" 
         class="btn btn-danger btn-sm"
         onclick="return confirm('Are you sure you want to delete your account and all reported items?');">
         Delete Account
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#notifDropdown').on('click', function() {
    $.get("get_notifications.php", function(data) {
        let notifications = JSON.parse(data);
        let notifList = $("#notifList");
        notifList.empty();

        if (notifications.length > 0) {
            notifications.forEach(n => {
                notifList.append("<li><a class='dropdown-item' href='#'>" + n.message + 
                    " <br><small class='text-muted'>" + n.created_at + "</small></a></li>");
            });
            $("#notifCount").text(notifications.length);
        } else {
            notifList.append("<li><span class='dropdown-item text-muted'>No notifications</span></li>");
            $("#notifCount").text("");
        }
    });
});
</script>
</body>
</html>
