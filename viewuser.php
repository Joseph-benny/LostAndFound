<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /mini_project/login.html");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Check admin role
$role_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$role_query->bind_param("i", $admin_id);
$role_query->execute();
$role_result = $role_query->get_result();
$role_data = $role_result->fetch_assoc();

if (!$role_data || $role_data['role'] != 1) {
    header("Location: /mini_project/login.html");
    exit;
}

// Get user_id from query
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Invalid user ID.");
}
$user_id = intval($_GET['user_id']);

// Fetch the user's details
$user_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Fetch Lost Items
$lost_stmt = $conn->prepare("SELECT * FROM lost_items WHERE user_id = ?");
$lost_stmt->bind_param("i", $user_id);
$lost_stmt->execute();
$lost_items = $lost_stmt->get_result();

// Fetch Found Items
$found_stmt = $conn->prepare("SELECT * FROM found_items WHERE user_id = ?");
$found_stmt->bind_param("i", $user_id);
$found_stmt->execute();
$found_items = $found_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Items - <?php echo htmlspecialchars($user['first_name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
        color: #e3eafc;
    }
    .navbar-custom {
        background: linear-gradient(90deg, #0d1a2f 80%, #1976d2 100%);
    }
    .section-title {
        color: #90caf9;
        margin-top: 2rem;
        margin-bottom: 1rem;
        letter-spacing: 2px;
    }
    .card {
        background: #162447;
        color: #e3eafc;
        border: none;
        border-radius: 1rem;
    }
    .card-title {
        color: #90caf9;
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
  <div class="container-fluid">
    <a href="adminpanel.php" class="btn btn-outline-light me-3">
      <i class="fa fa-arrow-left"></i> Back
    </a>
    <a class="navbar-brand fw-bold" href="home.php">
      <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found Admin
    </a>
  </div>
</nav>

<div class="container">
    <div class="card p-4 mb-4">
        <h2 class="section-title">Items Reported by <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <h3 class="section-title">Lost Items</h3>
    <div class="row">
        <?php if ($lost_items->num_rows > 0): ?>
            <?php while ($item = $lost_items->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow">
                        <?php if (!empty($item['item_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['item_image']); ?>" 
                                 class="card-img-top" alt="Item Image" 
                                 style="height:180px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p>
                                <strong>Status:</strong>
                                <span class="badge bg-danger"><?php echo htmlspecialchars($item['status']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-light">No lost items reported.</p>
        <?php endif; ?>
    </div>

    <h3 class="section-title">Found Items</h3>
    <div class="row">
        <?php if ($found_items->num_rows > 0): ?>
            <?php while ($item = $found_items->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow">
                        <?php if (!empty($item['item_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['item_image']); ?>" 
                                 class="card-img-top" alt="Item Image" 
                                 style="height:180px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p>
                                <strong>Status:</strong>
                                <span class="badge bg-success"><?php echo htmlspecialchars($item['status']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-light">No found items reported.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
