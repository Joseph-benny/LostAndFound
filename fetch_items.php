<?php
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category = $_GET['category'] ?? 'all';

if ($category === "lost_items") {
    $sql = "SELECT item_name, description, status, item_image, 'Lost' AS type FROM lost_items";
} elseif ($category === "found_items") {
    $sql = "SELECT item_name, description, status, item_image, 'Found' AS type FROM found_items";
} else {
    // Fetch both with same column structure
    $sql = "(SELECT item_name, description, status, item_image, 'Lost' AS type FROM lost_items)
            UNION ALL
            (SELECT item_name, description, status, item_image, 'Found' AS type FROM found_items)";
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-md-4 mb-3">
                <div class="card shadow-sm">
                  <img src="uploads/' . htmlspecialchars($row['item_image']) . '" 
                       class="card-img-top" 
                       alt="Item" 
                       onerror="this.src=\'default.png\'">
                  <div class="card-body">
                    <h5 class="card-title">' . htmlspecialchars($row['item_name']) . '</h5>
                    <p class="card-text">' . htmlspecialchars($row['description']) . '</p>
                    <span class="badge bg-' . ($row['status']=="claimed" ? "secondary" : "success") . '">' . htmlspecialchars($row['status']) . '</span>
                    <span class="badge bg-info ms-1">' . htmlspecialchars($row['type']) . '</span>
                  </div>
                </div>
              </div>';
    }
} else {
    echo "<p class='text-center text-muted'>No items found.</p>";
}

$conn->close();
?>
