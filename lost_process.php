<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}


// Make sure user is logged in
$phone = $_POST['phone'] ?? '';
$item_name = $_POST['lost_item'] ?? '';
$description = $_POST['description'] ?? '';
$date_lost = $_POST['date_lost'] ?? '';
$location = $_POST['location'] ?? '';
$status = "lost"; // default status
$image_path = null; // default null
$type="lost";

// ✅ Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle image upload (optional)
if (isset($_FILES["item_image"]) && $_FILES["item_image"]["error"] !== 4) {
    if ($_FILES["item_image"]["error"] === 0) {
        $image_name = basename($_FILES["item_image"]["name"]);
        $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $target_dir = "uploads/"; // Make sure this folder exists and is writable
        $new_image_name = time() . "_" . $image_name;
        $target_file = $target_dir . $new_image_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_type, $allowed_types)) {
            echo "<script>
                    alert('Only JPG, JPEG, PNG & GIF files are allowed.');
                    window.location.href = 'lost.html';
                  </script>";
            exit;
        }

        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            echo "<script>
                    alert('Image upload failed.');
                    window.location.href = 'lost.html';
                  </script>";
            exit;
        }
    } else {
        echo "<script>
                alert('File upload error.');
                window.location.href = 'lost.html';
              </script>";
        exit;
    }
}

// ✅ Insert into lost_items table
$sql = "INSERT INTO lost_items (user_id, phone, item_name, description, date_lost, location, status, item_image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $user_id, $phone, $item_name, $description, $date_lost, $location, $status, $image_path);

if ($stmt->execute()) {
    header("Location: home.php");
    exit;
} else {
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
