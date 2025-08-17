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

if ($id <= 0 || !in_array($type, ['lost', 'found'])) {
    die("Invalid item.");
}

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
<html>
<head>
    <title>Edit Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Edit <?php echo ucfirst($type); ?> Item</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Item Name</label>
            <input type="text" name="item_name" class="form-control" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" required><?php echo htmlspecialchars($item['description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label><?php echo ($type === 'lost') ? 'Date Lost' : 'Date Found'; ?></label>
            <input type="date" name="<?php echo ($type === 'lost') ? 'date_lost' : 'date_found'; ?>" class="form-control" value="<?php echo ($type === 'lost') ? $item['date_lost'] : $item['date_found']; ?>" required>
        </div>
        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($item['location']); ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
