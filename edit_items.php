<?php
session_start();

// Only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID & type from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

$table = ($type === 'lost') ? 'lost_items' : 'found_items';
$id_column = ($type === 'lost') ? 'lost_id' : 'found_id';

// Fetch current item details (only if belongs to current user)
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM $table WHERE $id_column = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found or you don't have permission to edit it.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $date = ($type === 'lost') ? $_POST['date_lost'] : $_POST['date_found'];
    $location = trim($_POST['location']);

    if ($type === 'lost') {
        $update_sql = "UPDATE lost_items SET item_name = ?, description = ?, date_lost = ?, location = ? WHERE lost_id = ? AND user_id = ?";
    } else {
        $update_sql = "UPDATE found_items SET item_name = ?, description = ?, date_found = ?, location = ? WHERE found_id = ? AND user_id = ?";
    }

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssii", $item_name, $description, $date, $location, $id, $user_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Item updated successfully!'); window.location.href = 'dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating item.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        .btn-success, .btn-secondary {
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .section-title {
            color: #90caf9;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
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
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg mt-5 mb-5 p-4">
                <div class="card-body">
                    <h2 class="section-title">Edit <?php echo ucfirst($type); ?> Item</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo ($type === 'lost') ? 'Date Lost' : 'Date Found'; ?></label>
                            <input type="date" name="<?php echo ($type === 'lost') ? 'date_lost' : 'date_found'; ?>" class="form-control" value="<?php echo ($type === 'lost') ? $item['date_lost'] : $item['date_found']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($item['location']); ?>" required>
                        </div>
                      <?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 0; // default to 0 if not set
?>
<button type="submit" class="btn btn-success w-100 mb-2">
    <i class="fa fa-save me-1"></i>Save Changes
</button>
<a href="<?php echo ($role == 1) ? 'dashboard1.php' : 'dashboard.php'; ?>" 
   class="btn btn-secondary w-100">
   <i class="fa fa-arrow-left me-1"></i>Cancel
</a>
<!-- Add this inside your <form> before the Cancel button -->



                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>