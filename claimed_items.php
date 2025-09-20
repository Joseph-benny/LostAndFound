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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Claimed Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
            min-height: 100vh;
            color: #e3eafc;
        }
        .section-title {
            color: #90caf9;
            letter-spacing: 2px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: bold;
        }
        .card {
            background: #162447;
            color: #e3eafc;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(25, 118, 210, 0.2);
        }
        .card-title {
            color: #90caf9;
            font-weight: bold;
        }
        .badge {
            font-size: 1rem;
            padding: 0.5em 1em;
            border-radius: 0.7rem;
        }
        .btn-success {
            background-color: #43a047;
            border: none;
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .btn-success:hover {
            background-color: #388e3c;
        }
        .alert-info {
            background: #12203a;
            color: #90caf9;
            border: none;
            border-radius: 0.7rem;
            font-size: 1.1rem;
        }
        .navbar-custom {
            background: linear-gradient(90deg, #0d1a2f 80%, #1976d2 100%);
        }
        .navbar-brand, .nav-link {
            letter-spacing: 2px;
            font-size: 1.1rem;
            color: #e3eafc !important;
        }
        .navbar-brand i {
            color: #90caf9;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow sticky-top mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="home.php">
            <i class="fa-solid fa-magnifying-glass-location me-2"></i>Lost & Found
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link" href="home.php">Home</a>
                <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-circle-user"></i></a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h2 class="section-title"><i class="fa fa-box-open me-2"></i>My Claimed Items</h2>
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
                            <h5 class="card-title">'.$itemName.'</h5>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>