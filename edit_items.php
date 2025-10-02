<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login_process.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID & type from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

$user_id = $_SESSION['user_id'];

if ($type === 'lost') {
    $sql = "SELECT * FROM lost_items WHERE lost_id = ? AND user_id = ?";
} elseif ($type === 'found') {
    $sql = "SELECT * FROM found_items WHERE found_id = ? AND user_id = ?";
} else {
    echo "<script>alert('Invalid item type!'); window.location.href='dashboard.php';</script>";
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "<script>alert('Item not found or no permission to edit!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Handle form submission separately for lost and found
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);

    if ($type === 'lost') {
        $date_lost = $_POST['date_lost'];
        $update_sql = "UPDATE lost_items SET item_name=?, description=?, date_lost=?, location=? WHERE lost_id=? AND user_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssii", $item_name, $description, $date_lost, $location, $id, $user_id);
    } elseif ($type === 'found') {
        $date_found = $_POST['date_found'];
        $update_sql = "UPDATE found_items SET item_name=?, description=?, date_found=?, location=? WHERE found_id=? AND user_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssii", $item_name, $description, $date_found, $location, $id, $user_id);
    }

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #0d1b2a, #1b263b, #415a77);
      color: #fff;
      min-height: 100vh;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      background: #1b263b;
      color: #fff;
    }
    .card-header {
      background: #0d1b2a;
      border-bottom: 2px solid #415a77;
    }
    .form-control, .form-control:focus {
      background-color: #f8f9fa;
      color: #000;
      border-radius: 10px;
      box-shadow: none;
    }
    .btn-primary {
      background: #0d6efd;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      transition: 0.3s;
    }
    .btn-primary:hover {
      background: #084298;
    }
    .btn-secondary {
      border-radius: 10px;
      font-weight: 600;
    }
    h2 {
      font-weight: bold;
      text-align: center;
      color: #e0e1dd;
    }
  </style>
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="col-lg-6 col-md-8">
      <div class="card p-4">
        <div class="card-header text-center">
          <h2><i class="fa-solid fa-pen-to-square me-2"></i>Edit <?php echo ucfirst($type); ?> Item</h2>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Item Name</label>
              <input type="text" name="item_name" class="form-control" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label"><?php echo $type === 'lost' ? 'Date Lost' : 'Date Found'; ?></label>
              <input type="date" name="<?php echo $type === 'lost' ? 'date_lost' : 'date_found'; ?>" 
                     class="form-control" 
                     value="<?php echo $type === 'lost' ? $item['date_lost'] : $item['date_found']; ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Location</label>
              <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($item['location']); ?>" required>
            </div>
            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-2"></i>Update</button>
              <a href="dashboard.php" class="btn btn-secondary px-4"><i class="fa fa-times me-2"></i>Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
