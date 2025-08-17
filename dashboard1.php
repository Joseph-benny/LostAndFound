<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// DB connection
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check role of logged-in user
$role_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$role_query->bind_param("i", $user_id);
$role_query->execute();
$role_result = $role_query->get_result();
$role_data = $role_result->fetch_assoc();

if (!$role_data || $role_data['role'] != 1) {
    // Not an admin → redirect
    header("Location: login.html");
    exit;
}

// Fetch admin details
$user_query = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Fetch lost items reported by admin
$lost_items = $conn->prepare("SELECT * FROM lost_items WHERE user_id = ?");
$lost_items->bind_param("i", $user_id);
$lost_items->execute();
$lost_result = $lost_items->get_result();

// Fetch found items reported by admin
$found_items = $conn->prepare("SELECT * FROM found_items WHERE user_id = ?");
$found_items->bind_param("i", $user_id);
$found_items->execute();
$found_result = $found_items->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
        .nav-link {
            margin-left: 1.5rem !important;
            margin-right: 1.5rem !important;
        }
        .navbar-brand i {
            color: #90caf9;
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
        }
        .btn-edit, .btn-toggle {
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .btn-edit {
            background-color: #1976d2;
            color: #fff;
        }
        .btn-edit:hover {
            background-color: #1565c0;
            color: #fff;
        }
        .btn-toggle {
            background-color: #43a047;
            color: #fff;
        }
        .btn-toggle:hover {
            background-color: #388e3c;
            color: #fff;
        }
        .section-title {
            color: #90caf9;
            margin-top: 2rem;
            margin-bottom: 1rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
  <div class="container-fluid">
    <a href="admin.php" class="btn btn-outline-light me-3">
      <i class="fa fa-arrow-left"></i>
    </a>
    <a class="navbar-brand fw-bold" href="home.php">
      <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link" href="home.php">Home</a>
        <a class="nav-link" href="adminpanel.php">Manage Users</a>
        <a class="nav-link" href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="card p-4 mb-4">
        <h2 class="section-title">Welcome, <?php echo htmlspecialchars($user['first_name']); ?> (Admin)</h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?>
            <a href="editprofile.php" class="btn btn-sm btn-edit ms-2"><i class="fa fa-edit me-1"></i>Edit Profile</a>
        </p>
    </div>

    <h3 class="section-title">Lost Items Reported</h3>
    <div class="row">
        <?php while ($item = $lost_result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card mb-3 shadow">
                    <?php if (!empty($item['item_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($item['item_image']); ?>" class="card-img-top" alt="Item Image" style="height:180px;object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <p>
                            <strong>Status:</strong>
                            <span class="badge bg-danger"><?php echo htmlspecialchars($item['status']); ?></span>
                        </p>
                        <div class="d-flex gap-2">
                            <a href="edit_items.php?id=<?php echo $item['lost_id']; ?>" class="btn btn-edit btn-sm"><i class="fa fa-edit me-1"></i>Edit</a>
                            <a href="mark_claimed.php?type=lost&id=<?php echo $item['lost_id']; ?>" class="btn btn-toggle btn-sm"><i class="fa fa-check me-1"></i>Toggle Claimed</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h3 class="section-title">Found Items Reported</h3>
    <div class="row">
        <?php while ($item = $found_result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card mb-3 shadow">
                    <?php if (!empty($item['item_image'])): ?>
                        <img src="/mini_project/uploads/<?php echo htmlspecialchars($item['item_image']); ?>" class="card-img-top" alt="Item Image" style="height:180px;object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <p>
                            <strong>Status:</strong>
                            <span class="badge bg-success"><?php echo htmlspecialchars($item['status']); ?></span>
                        </p>
                        <div class="d-flex gap-2">
                            <a href="edit_items.php?id=<?php echo $item['found_id']; ?>" class="btn btn-edit btn-sm"><i class="fa fa-edit me-1"></i>Edit</a>
                            <a href="mark_claimed.php?type=found&id=<?php echo $item['found_id']; ?>" class="btn btn-toggle btn-sm"><i class="fa fa-check me-1"></i>Toggle Claimed</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php