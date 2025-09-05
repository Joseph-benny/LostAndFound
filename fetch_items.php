<?php
// DB Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfound";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connection failed: " . $conn->connect_error;
    exit();
}

// Get and sanitize inputs
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$html = '';

// Fetch items with claim check
function fetch_items($conn, $table, $type, $search) {
    if ($table == 'found_items') {
        $sql = "SELECT f.found_id AS item_id, f.item_name, f.item_image, f.user_id AS uploader_id,
                       c.claim_status, c.user_id AS claimer_id
                FROM found_items f
                LEFT JOIN claims c ON f.found_id = c.found_id 
                   AND c.claim_status = 'approved'";
    } elseif ($table == 'lost_items') {
        $sql = "SELECT l.lost_id AS item_id, l.item_name, l.item_image, l.user_id AS uploader_id,
                       c.claim_status, c.user_id AS claimer_id
                FROM lost_items l
                LEFT JOIN claims c ON l.lost_id = c.lost_id 
                   AND c.claim_status = 'approved'";
    }

    // Search condition
    if (!empty($search)) {
        $sql .= ($table == 'lost_items') ? " WHERE l.item_name LIKE ?" : " WHERE f.item_name LIKE ?";
    }

    // Sorting
    $sql .= ($table == 'lost_items') ? " ORDER BY l.date_lost DESC" : " ORDER BY f.date_found DESC";

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
            $itemImage = !empty($row['item_image']) ? htmlspecialchars($row['item_image']) : 'images/David Wojnarowicz.jpeg';
            $itemId = htmlspecialchars($row['item_id']);
            $claimStatus = $row['claim_status'];
            $uploaderId = $row['uploader_id'];
            $claimerId = $row['claimer_id'];

            // Default badge (Lost/Found)
            if ($claimStatus === 'approved') {
                $badgeColor = "bg-secondary";
                $badgeText = "Already Claimed";
            } else {
                $badgeColor = ($type === 'lost') ? 'bg-danger' : 'bg-success';
                $badgeText = ($type === 'lost') ? 'Lost' : 'Found';
            }

            $tableParam = ($type === 'lost') ? 'lost_items' : 'found_items';

           
            // CASE 1: Not claimed → redirect to connect.php
            if($claimStatus !== 'approved') {
                $output .= '
                <div class="col-md-4">
                    <div class="card h-100 shadow-lg border-0" onclick="window.location.href=\'connect.php?id='.$itemId.'&table='.$tableParam.'\'" style="cursor:pointer;">
                        <img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:180px;object-fit:cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">'.$itemName.'</h5>
                            <span class="badge '.$badgeColor.'">'.$badgeText.'</span>
                        </div>
                    </div>
                </div>';
            }

             // CASE 2: Already claimed → no click, show uploader & claimer names
            else  {
                // Fetch uploader and claimer names
                $uploaderName = "Unknown";
                $claimerName = "Unknown";

                if (!empty($uploaderId)) {
                    $uploaderRes = $conn->query("SELECT CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE user_id = " . intval($uploaderId));
                    if ($uploaderRes && $uploaderRes->num_rows > 0) {
                        $uploaderName = $uploaderRes->fetch_assoc()['fullname'];
                    }
                }

                if (!empty($claimerId)) {
                    $claimerRes = $conn->query("SELECT CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE user_id = " . intval($claimerId));
                    if ($claimerRes && $claimerRes->num_rows > 0) {
                        $claimerName = $claimerRes->fetch_assoc()['fullname'];
                    }
                }

                $output .= '
                <div class="col-md-4">
                    <div class="card h-100 shadow-lg border-0">
                        <img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:180px;object-fit:cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">'.$itemName.'</h5>
                            <span class="badge bg-secondary">'.$badgeText.'</span>
                            <p class="mt-2 mb-0"><strong>Uploader:</strong> '.$uploaderName.'</p>
                            <p class="mb-0"><strong>Claimer:</strong> '.$claimerName.'</p>
                        </div>
                    </div>
                </div>';
            } 
        }
    }
    return $output;
}

// Logic to determine which items to fetch
if ($category === 'all' || $category === 'found') {
    $html .= '<h2 class="section-title text-center mt-5">Found Items</h2>';
    $foundHtml = fetch_items($conn, 'found_items', 'found', $search);
    $html .= !empty($foundHtml) ? $foundHtml : '<div class="alert alert-info text-center mt-3">No found items found.</div>';
}

if ($category === 'all' || $category === 'lost') {
    $html .= '<h2 class="section-title text-center">Lost Items</h2>';
    $lostHtml = fetch_items($conn, 'lost_items', 'lost', $search);
    $html .= !empty($lostHtml) ? $lostHtml : '<div class="alert alert-info text-center mt-3">No lost items found.</div>';
}

$conn->close();
echo $html;
?>
