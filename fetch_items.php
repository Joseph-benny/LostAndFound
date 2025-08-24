<?php
// DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // Return a 500 status code for server errors
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

// Get and sanitize inputs
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$html = '';

// Use prepared statements for security against SQL injection
function fetch_items($conn, $table, $type, $search) {
    $sql = "SELECT item_name, item_image, lost_id, found_id FROM ";
    
    if ($table == 'found_items') {
        $sql = "SELECT item_name, item_image, found_id as item_id FROM found_items";
    } elseif ($table == 'lost_items') {
        $sql = "SELECT item_name, item_image, lost_id as item_id FROM lost_items";
    }

    if (!empty($search)) {
        $sql .= " WHERE item_name LIKE ? ORDER BY created_at DESC";
    }
    
    $stmt = $conn->prepare($sql);

    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param("s", $searchTerm);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $itemName = htmlspecialchars($row['item_name']);
            $itemImage = !empty($row['item_image']) ? htmlspecialchars($row['item_image']) : 'images/David Wojnarowicz.jpeg'; // Use a generic placeholder
            $itemId = htmlspecialchars($row['item_id']);
            $badgeColor = ($type === 'lost') ? 'bg-danger' : 'bg-success';
            $badgeText = ($type === 'lost') ? 'Lost' : 'Found';
            $tableParam = ($type === 'lost') ? 'lost_items' : 'found_items';

            $output .= '
            <div class="col-md-4">
                <div class="card h-100 shadow-lg border-0" onclick="window.location.href=\'connect.php?id='.$itemId.'&table='.$tableParam.'\'" style="cursor:pointer;">
                    '.(!empty($itemImage) ? '<img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:180px;object-fit:cover;">' : '<div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px;"><i class="fa fa-image fa-2x"></i><br>No Image</div>').'
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">'.$itemName.'</h5>
                        <span class="badge '.$badgeColor.'">'.$badgeText.'</span>
                    </div>
                </div>
            </div>';
        }
    }
    return $output;
}

// Logic to determine which items to fetch
if ($category === 'all' || $category === 'found') {
    $html .= '<h2 class="section-title text-center mt-5">Found Items</h2>';
    $html .= fetch_items($conn, 'found_items', 'found', $search);
    if (empty($html) || strpos($html, 'col-md-4') === false) {
        $html .= '<div class="alert alert-info text-center mt-3">No found items found.</div>';
    }
}

if ($category === 'all' || $category === 'lost') {
    $html .= '<h2 class="section-title text-center">Lost Items</h2>';
    $html .= fetch_items($conn, 'lost_items', 'lost', $search);
    if (empty($html) || strpos($html, 'col-md-4') === false) {
        $html .= '<div class="alert alert-info text-center mt-3">No lost items found.</div>';
    }
}


$conn->close();
echo $html;
?>