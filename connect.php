<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize input
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$table = isset($_GET['table']) ? $_GET['table'] : '';

if (!in_array($table, ['lost_items', 'found_items']) || $id <= 0) {
    die("Invalid request.");
}

// Query item
$sqlItem = "SELECT * FROM $table WHERE " . ($table === 'lost_items' ? 'lost_id' : 'found_id') . " = ?";
$stmtItem = $conn->prepare($sqlItem);
$stmtItem->bind_param("i", $id);
$stmtItem->execute();
$resultItem = $stmtItem->get_result();

if ($resultItem->num_rows === 0) {
    die("Item not found.");
}

$item = $resultItem->fetch_assoc();

// Fetch user info
$userId = $item['user_id'];
$sqlUser = "SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    die("Uploader not found.");
}

$user = $resultUser->fetch_assoc();

// Close connections
$stmtItem->close();
$stmtUser->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Item Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0d1a2f, #1976d2);
      min-height: 100vh;
      color: #fff;
    }
    .navbar {
      background: linear-gradient(to right, #0d1a2f, #1976d2);
    }
    .navbar-nav{
      gap:40px;
    }
    .navbar .nav-link {
      color: #fff !important;
      font-weight: 500;
      transition: 0.3s;
    }
    .navbar .nav-link:hover {
      color: #ffcc00 !important;
    }
    .container {
      max-width: 850px;
      margin-top: 40px;
      background: rgba(13, 26, 47, 0.95);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    img {
      max-height: 300px;
      object-fit: contain;
      border: 2px solid #1976d2;
      border-radius: 8px;
      background: #fff;
    }
    .badge {
      font-size: 1rem;
      padding: 0.5em 0.8em;
    }
    .list-group-item {
      background: transparent;
      color: #fff;
      border-color: rgba(255,255,255,0.2);
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="home.php"><i class="fa fa-search me-2"></i>Lost & Found</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="signup.html"><i class="fa fa-user-plus me-1"></i>Sign Up</a></li>
        <li class="nav-item"><a class="nav-link" href="login.html"><i class="fa fa-sign-in-alt me-1"></i>Login</a></li>
        <li class="nav-item"><a class="nav-link" href="about.html"><i class="fa fa-info-circle me-1"></i>About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.html"><i class="fa fa-envelope me-1"></i>Contact Us</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa-solid fa-circle-user"></i></a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container">
  <h2 class="mb-4 text-center text-info">Item Details</h2>
  
  <?php if (!empty($item['item_image'])): ?>
    <div class="text-center mb-4">
      <img src="<?php echo htmlspecialchars($item['item_image']); ?>" class="img-fluid">
    </div>
  <?php endif; ?>

  <ul class="list-group mb-4">
    <li class="list-group-item"><strong>Item Name:</strong> <?php echo htmlspecialchars($item['item_name']); ?></li>
    <li class="list-group-item"><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></li>
    <li class="list-group-item"><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></li>
    <!--<li class="list-group-item"><strong>Date <?php echo $table === 'lost_items' ? 'Lost' : 'Found'; ?>:</strong> 
      <?php echo htmlspecialchars($table === 'lost_items' ? $item['date_lost'] : $item['date_found']); ?>
    </li>-->
    <li class="list-group-item">
      <strong>Status:</strong>
      <span class="badge <?php echo $item['status'] === 'Lost' ? 'bg-danger' : 'bg-success'; ?>">
        <?php echo htmlspecialchars($item['status']); ?>
      </span>
    </li>
      </ul>

  <h4 class="text-info">Uploader Information</h4>
  <ul class="list-group">
    <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></li>
    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></li>
    <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></li>
  </ul>

  <div class="text-end mt-4">
    <a href="claim_item.php?id=<?php echo $id; ?>&table=<?php echo $table; ?>" class="btn btn-warning btn-lg">
      Claim Item
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
