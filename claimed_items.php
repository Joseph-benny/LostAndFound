<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lostfound");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all claimed items for this user
$sql = "
    SELECT c.claim_id, c.claim_status, c.claimed_at, c.claim_description,
           COALESCE(l.item_name, f.item_name) AS item_name,
           COALESCE(l.item_image, f.item_image) AS item_image,
           COALESCE(l.location, f.location) AS location,
           COALESCE(l.date_lost, f.date_found) AS item_date,
           u.first_name, u.last_name, u.phone
    FROM claims c
    LEFT JOIN lost_items l ON c.lost_id = l.lost_id
    LEFT JOIN found_items f ON c.found_id = f.found_id
    LEFT JOIN users u ON COALESCE(l.user_id, f.user_id) = u.user_id
    WHERE c.user_id = ?
    ORDER BY c.claimed_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Claimed Items</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <h2 class="text-center mb-4">My Claimed Items</h2>
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $itemName = htmlspecialchars($row['item_name']);
                $itemImage = !empty($row['item_image']) ? htmlspecialchars($row['item_image']) : 'images/placeholder.png';
                $uploaderName = htmlspecialchars($row['first_name'] . " " . $row['last_name']);
                $uploaderPhone = htmlspecialchars($row['phone']);
                $claimStatus = htmlspecialchars($row['claim_status']);
                $claimDate = htmlspecialchars($row['claimed_at']);
                $claimDesc = htmlspecialchars($row['claim_description']);
                $badgeColor = ($claimStatus === 'approved') ? 'success' : (($claimStatus === 'pending') ? 'warning' : 'danger');

                echo '
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-lg border-0">
                        <img src="'.$itemImage.'" class="card-img-top" alt="'.$itemName.'" style="height:200px;object-fit:cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">'.$itemName.'</h5>
                            <p><b>Uploader:</b> '.$uploaderName.'</p>
                            <p><b>Description:</b> '.$claimDesc.'</p>
                            <p><b>Date:</b> '.$claimDate.'</p>
                            <span class="badge bg-'.$badgeColor.'">'.ucfirst($claimStatus).'</span>';

                // Show Contact button only if claim is approved
                if ($claimStatus === 'approved') {
                    // WhatsApp link with country code
                    $whatsappNumber = "91" . preg_replace('/\D/', '', $uploaderPhone);
                    echo '<div class="mt-3">
                            <a href="https://wa.me/'.$whatsappNumber.'" target="_blank" class="btn btn-success btn-sm">
                                <i class="fa fa-whatsapp me-1"></i>Contact Uploader
                            </a>
                          </div>';
                }

                echo '      </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="alert alert-info text-center">No claimed items found.</div>';
        }
        ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
