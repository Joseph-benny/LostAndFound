<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_query = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $user_query->fetch_assoc();

// Update profile logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Gmail validation
    if (!preg_match("/@gmail\.com$/", $email)) {
        echo "<script>alert('Email must end with @gmail.com');</script>";
    } else {
        // Verify old password
        if (!password_verify($old_password, $user['password'])) {
            echo "<script>alert('Old password is incorrect!');</script>";
        } else {
            $update_sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?";
            $params = [$first_name, $last_name, $email, $phone];

            // If email changed, new password required
            if ($email !== $user['email'] && empty($new_password)) {
                echo "<script>alert('Changing email requires a new password.');</script>";
            } else {
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql .= ", password=?";
                    $params[] = $hashed_password;
                }
                $update_sql .= " WHERE user_id=?";
                $params[] = $user_id;

                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);
                $stmt->execute();
                echo "<script>alert('Profile updated successfully!'); window.location='editprofile.php';</script>";
            }
        }
    }
}

// Delete account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $delete_password = $_POST['delete_password'];
    if (password_verify($delete_password, $user['password'])) {
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
        session_destroy();
        echo "<script>alert('Account deleted successfully.'); window.location='login.html';</script>";
    } else {
        echo "<script>alert('Incorrect password. Account not deleted.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
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
        .form-label {
            color: #90caf9;
            font-weight: 500;
        }
        .btn-primary, .btn-outline-danger {
            font-weight: 500;
            letter-spacing: 1px;
        }
        .btn-outline-danger {
            border-radius: 8px;
        }
        .modal-content {
            background: #162447;
            color: #e3eafc;
        }
        .modal-header, .modal-footer {
            border-color: #1976d2;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
    <div class="container-fluid">
        <a href="dashboard.php" class="btn btn-outline-light me-3">
            <i class="fa fa-arrow-left"></i>
        </a>
        <a class="navbar-brand fw-bold" href="home.php">
            <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link" href="home.php">Home</a>
                <a class="nav-link" href="about.html">About Us</a>
                <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-circle-user"></i></a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4 class="mt-2"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                         <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Gmail only)</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Old Password</label>
                            <input type="password" name="old_password" class="form-control" placeholder="Enter old password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password (optional)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Enter new password if changing">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                    <hr>
                    <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Enter current password to confirm:</p>
                <input type="password" name="delete_password" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="delete_account" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>