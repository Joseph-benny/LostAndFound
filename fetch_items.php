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

// Helper function to escape output
function esc($str) {
    return htmlspecialchars($str);
}

// Fetch lost items
function fetch_lost_items($conn, $search = '') {
    $sql = "SELECT l.lost_id, l.item_name, l.item_image, l.user_id AS uploader_id, l.claim_state
            FROM lost_items l";

    if (!empty($search)) {
        $sql .= " WHERE l.item_name LIKE ?";
    }

    $sql .= " ORDER BY l.date_lost DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param("s", $searchTerm);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $itemId = $row['lost_id'];
            $itemName = esc($row['item_name']);
            $itemImage = !empty($row['item_image']) ? esc($row['item_image']) : 'images/David Wojnarowicz.jpeg';
            $claimStatus = $row['claim_state'];
            $uploaderId = $row['uploader_id'];

            // Badge and clickable logic
            if ($claimStatus === 'claimed') {
                // Fetch uploader name
                $uploaderName = "Unknown";
                if (!empty($uploaderId)) {
                    $res = $conn->query("SELECT CONCAT(first_name,' ',last_name) AS fullname FROM users WHERE user_id=".intval($uploaderId));
                    if ($res && $res->num_rows > 0) {
                        $uploaderName = $res->fetch_assoc()['fullname'];
                    }
                }

                $output .= '
                <div class="col-md-4">
                    <div class="card h-100 shadow-lg border-0">
                        <img src="'.$itemImage.'" class="card-img-top" style="height:180px; object-fit:cover;" alt="'.$itemName.'">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">'.$itemName.'</h5>
                            <span class="badge bg-secondary">Item Claimed</span>
                            <p class="mt-2 mb-0"><strong>Uploader:</strong> '.$uploaderName.'</p>
                        </div>
                    </div>
                </div>';
            } else {
                // Not claimed → clickable
                $output .= '
                <div class="col-md-4">
                    <div class="card h-100 shadow-lg border-0" onclick="window.location.href=\'connect.php?id='.$itemId.'&table=lost_items\'" style="cursor:pointer;">
                        <img src="'.$itemImage.'" class="card-img-top" style="height:180px; object-fit:cover;" alt="'.$itemName.'">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">'.$itemName.'</h5>
                            <span class="badge bg-danger">Lost</span>
                        </div>
                    </div>
                </div>';
            }
        }
    } else {
        $output .= '<div class="alert alert-info text-center mt-3">No lost items found.</div>';
    }
    return $output;
}

// Fetch found items
function fetch_found_items($conn, $search = '') {
    $sql = "SELECT f.found_id, f.item_name, f.item_image, f.user_id AS uploader_id, f.status
            FROM found_items f";

    if (!empty($search)) {
        $sql .= " WHERE f.item_name LIKE ?";
    }

    $sql .= " ORDER BY f.date_found DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param("s", $searchTerm);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $itemId = $row['found_id'];
            $itemName = esc($row['item_name']);
            $itemImage = !empty($row['item_image']) ? esc($row['item_image']) : 'images/David Wojnarowicz.jpeg';
            $status = $row['status'];

            // Badge color
            $badgeColor = 'bg-secondary';
            if ($status === 'pending') $badgeColor = 'bg-warning';
            elseif ($status === 'approved') $badgeColor = 'bg-success';
            elseif ($status === 'rejected') $badgeColor = 'bg-danger';

            $output .= '
            <div class="col-md-4">
                <div class="card h-100 shadow-lg border-0" onclick="window.location.href=\'connect.php?id='.$itemId.'&table=found_items\'" style="cursor:pointer;">
                    <img src="'.$itemImage.'" class="card-img-top" style="height:180px; object-fit:cover;" alt="'.$itemName.'">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">'.$itemName.'</h5>
                        <span class="badge '.$badgeColor.'">'.esc(ucfirst($status)).'</span>
                    </div>
                </div>
            </div>';
        }
    } else {
        $output .= '<div class="alert alert-info text-center mt-3">No found items found.</div>';
    }
    return $output;
}

// Main output logic
$html = '';
if ($category === 'all' || $category === 'found') {
    $html .= '<h2 class="section-title text-center mt-5">Found Items</h2>';
    $html .= fetch_found_items($conn, $search);
}

if ($category === 'all' || $category === 'lost') {
    $html .= '<h2 class="section-title text-center">Lost Items</h2>';
    $html .= fetch_lost_items($conn, $search);
}

$conn->close();
echo $html;
?>
