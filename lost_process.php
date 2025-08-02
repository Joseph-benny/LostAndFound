$image_path = null;

if (
    isset($_FILES["item_image"]) &&
    $_FILES["item_image"]["error"] === 0 &&
    !empty($_FILES["item_image"]["name"])
) {
    $image_name = basename($_FILES["item_image"]["name"]);
    $image_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    $target_file = $target_dir . time() . "_" . $image_name;

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
}



// Insert into lost_items table
$sql = "INSERT INTO lost_items (user_id, phone, lost_item, description, date_lost, location, status, item_image)
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
