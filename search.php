<?php
// DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search query from URL
$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

function searchItems($conn, $table, $searchTerm) {
    $stmt = $conn->prepare("SELECT item_name, item_image FROM $table WHERE item_name LIKE ?");
    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Results from $table</h2>";
    if ($result->num_rows > 0) {
        echo "<div class='item-container'>";
        while ($row = $result->fetch_assoc()) {
            $itemName = htmlspecialchars($row['item_name']);
            $itemImage = htmlspecialchars($row['item_image']);
            echo "
                <div class='item-box'>
                    <img src='$itemImage' alt='$itemName' width='150'><br>
                    <strong>$itemName</strong>
                </div>
            ";
        }
        echo "</div>";
    } else {
        echo "<p>No results found in $table.</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results</title>
  <link rel="stylesheet" href="home.css"> <!-- Use same styling -->
</head>
<body>
  <h1>Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h1>
  <?php
  if (!empty($searchTerm)) {
      searchItems($conn, 'lost_items', $searchTerm);
      searchItems($conn, 'found_items', $searchTerm);
  } else {
      echo "<p>Please enter a search term.</p>";
  }
  $conn->close();
  ?>
</body>
</html>
