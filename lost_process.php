<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $item_name   = $_POST['lost_item'] ?? '';
   $description = $_POST['description'] ?? '';
   $date_lost  = $_POST['date_lost'] ?? '';
   $location    = $_POST['location'] ?? '';
   $email       = $_POST['email'] ?? '';
   $phone       = $_POST['phone'] ?? '';
   $status      = "Lost Item";
 
  //  Check if user exists by email
  $user_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
  $user_check->bind_param("s", $email);
  $user_check->execute();
  $user_result = $user_check->get_result();

  if ($user_result->num_rows === 0) {
    //  Auto-create user with default data
    $first_name = "Anonymous";
    $last_name = "";
    $phone = "0000000000";
    $password = password_hash("default123", PASSWORD_DEFAULT); // Encrypt dummy password

    $insert_user = $conn->prepare("INSERT INTO users (first_name, last_name, phone, email, password) VALUES (?, ?, ?, ?, ?)");
    $insert_user->bind_param("sssss", $first_name, $last_name, $phone, $email, $password);
    $insert_user->execute();
    $user_id = $insert_user->insert_id;
    $insert_user->close();
  } else {
    $user = $user_result->fetch_assoc();
    $user_id = $user['user_id'];
  }

  $user_check->close();

  // ✅ Handle image upload
  $target_dir = "uploads/";
  if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
  }

  if (isset($_FILES["item_image"]) && $_FILES["item_image"]["error"] === 0) {
    $image_name = basename($_FILES["item_image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name;
    $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($image_type, $allowed_types)) {
      die("Only JPG, JPEG, PNG & GIF files are allowed.");
    }

    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
      // Insert into lost_items table
      $sql = "INSERT INTO lost_items (user_id, phone, lost_item, description, date_lost, location, status, item_image)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

      $stmt = $conn->prepare($sql);
   
$stmt->bind_param("isssssss", $user_id, $phone, $item_name, $description, $date_lost, $location,  $status, $target_file);

      if ($stmt->execute()) {
        header("Location: home.php");
    exit;
      } else {
        echo "Database Error: " . $stmt->error;
      }

      $stmt->close();
    } else {
      echo "Image upload failed.";
    }
  } else {
    echo "No image selected or file upload error.";
  }
}

$conn->close();
?>
