<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound"; // Make sure this matches your actual DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $item_name   = $_POST['item_name'] ?? '';
  $location    = $_POST['location'] ?? '';
  $description = $_POST['description'] ?? '';
  $date_found  = $_POST['date_found'] ?? '';
  $status      = "Found Item";

  // Handle image upload
  $target_dir = "uploads/";
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
  }

  $image_name = basename($_FILES["item_image"]["name"]);
  $target_file = $target_dir . time() . "_" . $image_name;
  $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
  if (!in_array($image_type, $allowed_types)) {
    die("Only JPG, JPEG, PNG & GIF files are allowed.");
  }

  if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
    // Insert into database
    $sql = "INSERT INTO found_items (item_name, location, description, date_found, status, item_image)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $item_name, $location, $description, $date_found, $status, $target_file);

    if ($stmt->execute()) {
      echo "Found item report submitted successfully!";
    } else {
      echo "Database Error: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "Image upload failed.";
  }
}

$conn->close();
?>
