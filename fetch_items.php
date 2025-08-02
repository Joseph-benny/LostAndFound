<?php
// fetch_items.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table = $_GET['table'];

if (!in_array($table, ['lost_items', 'found_items'])) {
    exit("Invalid table name.");
}

$sql = "SELECT item_name, item_image, " . ($table === 'lost_items' ? 'lost_id' : 'found_id') . " as item_id FROM $table";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $itemName = htmlspecialchars($row['item_name']);
        $itemImage = htmlspecialchars($row['item_image']);
        $itemId = htmlspecialchars($row['item_id']);
        echo "
        <div class='item-box' onclick=\"window.location.href='connect.php?id=$itemId&table=$table'\">
            <img src='$itemImage' alt='$itemName'>
            <div class='item-name'>$itemName</div>
        </div>";
    }
} else {
    echo "<p>No items found.</p>";
}

$conn->close();
?>
