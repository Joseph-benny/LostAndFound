<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost & Found Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        .badge.bg-danger, .badge.bg-success {
            font-size: 1rem;
            padding: 0.5em 1em;
        }
        .search-bar {
            background: #12203a;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0 10px rgba(25, 118, 210, 0.08);
        }
        .dropdown-menu {
            background: #162447;
            color: #e3eafc;
            border-radius: 0.7rem;
        }
        .dropdown-item {
            color: #e3eafc;
        }
        .dropdown-item:hover {
            background: #1976d2;
            color: #fff;
        }
        .btn-primary, .btn-outline-primary, .btn-outline-secondary {
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 1px;
        }
        .btn-primary {
            background-color: #1976d2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }
        .btn-outline-primary {
            border-color: #1976d2;
            color: #1976d2;
        }
        .btn-outline-primary:hover {
            background-color: #1976d2;
            color: #fff;
        }
        .btn-outline-secondary {
            border-color: #90caf9;
            color: #90caf9;
        }
        .btn-outline-secondary:hover {
            background-color: #90caf9;
            color: #162447;
        }
    </style>
</head>
<body>

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

    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <form method="GET" action="home.php" class="input-group shadow-sm">
                <input type="text" name="search" class="form-control" placeholder="Search items..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
            </form>
        </div>
    </div>

    <div class="row mb-4 g-2">
        <div class="col-md-4 mb-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle w-100 shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-plus-circle me-1"></i>Report an Item
                </button>
                <ul class="dropdown-menu w-100">
                    <li><a class="dropdown-item" href="lost.php"><i class="fa fa-exclamation-circle me-1 text-danger"></i>Report Lost Item</a></li>
                    <li><a class="dropdown-item" href="foundfront.php"><i class="fa fa-check-circle me-1 text-success"></i>Report Found Item</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100 shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-list me-1"></i>Select Category
                </button>
                <ul class="dropdown-menu w-100">
                    <li><a class="dropdown-item" href="#" onclick="loadItems('all')"><i class="fa fa-th-large text-primary me-1"></i>All Items</a></li>
                    <li><a class="dropdown-item" href="#" onclick="loadItems('lost')"><i class="fa fa-exclamation-circle text-danger me-1"></i>Lost Items</a></li>
                    <li><a class="dropdown-item" href="#" onclick="loadItems('found')"><i class="fa fa-check-circle text-success me-1"></i>Found Items</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <a href="claimed_items.php" class="btn btn-outline-secondary w-100 shadow-sm">
                <i class="fa fa-star me-1"></i>My Claimed Items
            </a>
        </div>
    </div>

    <div id="items-container" class="row mt-4 g-4">
        <!-- Items loaded by JS/AJAX -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script>
function loadItems(category) {
    var searchParam = new URLSearchParams(window.location.search).get('search');
    var url = "fetch_items.php?category=" + category;
    if (searchParam) {
        url += "&search=" + encodeURIComponent(searchParam);
    }
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById("items-container").innerHTML = this.responseText;
        } else {
            document.getElementById("items-container").innerHTML = '<div class="alert alert-danger text-center">Error loading items. Please try again later.</div>';
        }
    };
    xhr.onerror = function() {
        document.getElementById("items-container").innerHTML = '<div class="alert alert-danger text-center">Network error. Please check your connection.</div>';
    };
    xhr.send();
}
window.onload = function() {
    loadItems('all');
};
</script>
</body>
</html>