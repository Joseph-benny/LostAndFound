<?php
session_start();
if (!isset($_SESSION['user_id'])|| $_SESSION['role'] != 1) {
    header("Location: login.html");
    exit;
}

// ...you can add logic for role selection here if needed...

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mode Selection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        .logo-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #90caf9;
            margin-right: 10px;
        }
        .card {
            background: #162447;
            color: #e3eafc;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(25, 118, 210, 0.2);
        }
        .section-title {
            color: #90caf9;
            margin-top: 2rem;
            margin-bottom: 1rem;
            letter-spacing: 2px;
        }
        .btn-lg {
            font-size: 1.2rem;
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .list-grouplist-group-flush{
            color:white;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
    <div class="container-fluid">
        <a href="home.php" class="btn btn-outline-light me-3">
            <i class="fa fa-arrow-left"></i>
        </a>
        <a class="navbar-brand fw-bold d-flex align-items-center" href="home.php">
            <img src="images/download (8).jpeg" alt="Logo" class="logo-img">
            <span><i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link" href="home.php">Home</a>
                <a class="nav-link" href="about.html">About Us</a>
                <a class="nav-link" href="dashboard1.php"><i class="fa-solid fa-circle-user"></i></a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg mt-5 mb-5 p-4">
                <div class="card-body text-center">
                    <h1 class="section-title"><i class="fa fa-user-shield me-2"></i>Select Your Current Role</h1>
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <a href="adminpanel.php?role=admin" class="btn btn-danger btn-lg px-4">Admin</a>
                        <a href="dashboard1.php?role=user" class="btn btn-success btn-lg px-4">User</a>
                    
                   
                    </div>
                    <hr class="bg-primary">
                    <h4 class="mb-3"><i class="fa fa-users me-2"></i>User Session Data</h4>
                    <ul class="list-grouplist-group-flush">
                        <li class="list-group-item bg-transparent text-start"><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></li>
                        <?php
                        // Optionally show more user session data if available
                        if (isset($_SESSION['email'])) {
                            echo '<li class="list-group-item bg-transparent text-start"><strong>Email:</strong> ' . htmlspecialchars($_SESSION['email']) . '</li>';
                        }
                        if (isset($_SESSION['role'])) {
                            echo '<li class="list-group-item bg-transparent text-start"><strong>Role: Admin</strong> ' . htmlspecialchars($_SESSION['role']) . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-