<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lost & Found Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome for icons -->
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
    }
    .nav-link {
      margin-left: 1.5rem !important;
      margin-right: 1.5rem !important;
    }
    .navbar-brand i {
      color: #90caf9;
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
    }
    .card-title {
      color: #90caf9;
    }
    .badge.bg-danger, .badge.bg-success {
      font-size: 1rem;
      padding: 0.5em 1em;
    }
    .lost-names-row {
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="home.php">
      <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <div class="navbar-nav">
        <a class="nav-link text-white mx-3" href="signup.html"><i class="fa fa-user-plus me-1"></i>Sign&nbsp;Up</a>
        <a class="nav-link text-white mx-3" href="login.html"><i class="fa fa-sign-in-alt me-1"></i>Login</a>
        <a class="nav-link text-white mx-3" href="about.html"><i class="fa fa-info-circle me-1"></i>About&nbsp;Us</a>
        <a class="nav-link text-white mx-3" href="contact.html"><i class="fa fa-envelope me-1"></i>Contact&nbsp;Us</a>
        <a class="nav-link text-white mx-3" href="dashboard.php"><i class="fa-solid fa-circle-user"></i></a>
      </div>
    </div>
  </div>
</nav>

<div class="container">
  <div class="text-center mb-4">
    <img src="images/download (8).jpeg" class="rounded-circle border border-3 border-primary mb-2" width="90" height="90" alt="Logo">
    <h1 class="fw-bold section-title">Lost And Found</h1>
    <p class="lead text-secondary">Find your lost items or report what you've found!</p>
  </div>

  <!-- Search Section -->
  <div class="row justify-content-center mb-4">
    <div class="col-md-8">
      <form method="GET" action="home.php" class="input-group shadow-sm">
        <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
      </form>
    </div>
  </div>

  <!-- Report & Category Dropdowns -->
  <div class="row mb-4">
    <div class="col-md-6 mb-2">
      <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle w-100 shadow-sm" type="button" data-bs-toggle="dropdown">
          <i class="fa fa-plus-circle me-1"></i>Report an Item
        </button>
        <ul class="dropdown-menu w-100">
          <li><a class="dropdown-item" href="lost.html"><i class="fa fa-exclamation-circle me-1 text-danger"></i>Report Lost Item</a></li>
          <li><a class="dropdown-item" href="found.html"><i class="fa fa-check-circle me-1 text-success"></i>Report Found Item</a></li>
        </ul>
      </div>
    </div>
    <div class="col-md-6 mb-2">
<!-- ✅ Select Category Dropdown -->
<div class="dropdown">
  <button class="btn btn-outline-secondary dropdown-toggle w-100 shadow-sm" type="button" data-bs-toggle="dropdown">
    <i class="fa fa-list me-1"></i>Select Category
  </button>
  <ul class="dropdown-menu w-100">
    <li>
      <a class="dropdown-item" href="#" onclick="loadItems('all')">
        <i class="fa fa-th-large text-primary me-1"></i>All Items
      </a>
    </li>
    <li>
      <a class="dropdown-item" href="#" onclick="loadItems('lost_items')">
        <i class="fa fa-exclamation-circle text-danger me-1"></i>Lost Items
      </a>
    </li>
    <li>
      <a class="dropdown-item" href="#" onclick="loadItems('found_items')">
        <i class="fa fa-check-circle text-success me-1"></i>Found Items
      </a>
    </li>
  </ul>
</div>

<!-- ✅ Container to show results -->
<div id="items-container" class="mt-3"></div>

<!-- ✅ Script for Dropdown Logic -->
<script>
  function loadItems(category) {
    let container = document.getElementById("items-container");
    container.innerHTML = ""; // Clear old content

    if (category === "all") {
      container.innerHTML = `
        <div class="alert alert-primary shadow-sm">Showing <b>All Items</b>...</div>
      `;
    } else if (category === "lost_items") {
      container.innerHTML = `
        <div class="alert alert-danger shadow-sm">Showing <b>Lost Items</b>...</div>
      `;
    } else if (category === "found_items") {
      container.innerHTML = `
        <div class="alert alert-success shadow-sm">Showing <b>Found Items</b>...</div>
      `;
    }
  }
</script>

</div>

<!-- Where items will be displayed -->
<div id="items-container" class="row mt-4"></div>

<script>
// Function to load items dynamically
function loadItems(category) {
  // Create AJAX request
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "fetch_items.php?category=" + category, true);
  xhr.onload = function() {
    if (this.status === 200) {
      document.getElementById("items-container").innerHTML = this.responseText;
    }
  };
  xhr.send();
}

// By default load all items
window.onload = function() {
  loadItems('all');
};
</script>

  </div>

  <!-- Items Display Section -->
  <div class="items-section">
    <?php
    // DB Connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lostfound";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

   


    // SPACING BETWEEN LOST AND FOUND
    echo '<div class="my-5"></div>';

    // FOUND ITEMS SECTION
    echo '<h2 class="section-title text-center">Found Items</h2>';
    $sqlFound = "SELECT item_name, item_image, found_id FROM found_items";
    if (!empty($search)) {
        $sqlFound .= " WHERE item_name LIKE '$search%'";
    }
    $resultFound = $conn->query($sqlFound);

    if ($resultFound && $resultFound->num_rows > 0) {
        echo '<div class="row g-4 justify-content-center">';
        while ($row = $resultFound->fetch_assoc()) {
            $itemName = htmlspecialchars($row['item_name']);
            $itemImage = htmlspecialchars($row['item_image']);
            $itemId = htmlspecialchars($row['found_id']);
            echo '
            <div class="col-md-3">
              <div class="card h-100 shadow-lg border-0" onclick="window.location.href=\'connect.php?id='.$itemId.'&table=found_items\'" style="cursor:pointer;">
                '.(!empty($itemImage) ? '<img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:180px;object-fit:cover;">' : '<div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px;"><i class="fa fa-image fa-2x"></i><br>No Image</div>').'
                <div class="card-body text-center">
                  <h5 class="card-title text-primary">'.$itemName.'</h5>
                  <span class="badge bg-success">Found</span>
                </div>
              </div>
            </div>
            ';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-info text-center">No found items found.</div>';
    }

     // LOST ITEMS SECTION
    echo '<h2 class="section-title text-center">Lost Items</h2>';
    $sqlLost = "SELECT item_name, item_image, lost_id FROM lost_items";
    if (!empty($search)) {
        $sqlLost .= " WHERE item_name LIKE '$search%'";
    }
    $resultLost = $conn->query($sqlLost);

if ($resultLost && $resultLost->num_rows > 0) {
    echo '<div class="row lost-names-row justify-content-center">';
    while ($row = $resultLost->fetch_assoc()) {
        $itemName = htmlspecialchars($row['item_name']);
        $itemImage = !empty($row['item_image']) ? htmlspecialchars($row['item_image']) : 'images/David Wojnarowicz.jpeg'; 
        $itemId = htmlspecialchars($row['lost_id']);

        echo '
        <div class="col-md-3 mb-4">
          <div class="card h-100 shadow-lg border-0" 
               onclick="window.location.href=\'connect.php?id='.$itemId.'&table=lost_items\'" 
               style="cursor:pointer;">
            <img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:180px;object-fit:cover;">
            <div class="card-body text-center">
              <h5 class="card-title text-primary">'.$itemName.'</h5>
              <span class="badge bg-danger">Lost</span>
            </div>
          </div>
        </div>
        ';
    }
    echo '</div>';
}

    $conn->close();
    ?>
  </div>
</div>

<!-- Bootstrap JS Bundle (for dropdowns etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadItems(table) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "fetch_items.php?table=" + table, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
      document.getElementById('item-container').innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}
</script>
</body>
</html>