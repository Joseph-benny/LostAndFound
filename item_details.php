<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lostfound");

// Validate request
if (!isset($_GET['table']) || (!isset($_GET['lost_id']) && !isset($_GET['found_id']))) {
    echo "Invalid request.";
    exit;
}

$table = $_GET['table']; // 'lost_items' or 'found_items'

// Identify item type and key column
if ($table === 'lost_items' && isset($_GET['lost_id'])) {
    $item_id = intval($_GET['lost_id']);
    $id_column = 'lost_id';
} elseif ($table === 'found_items' && isset($_GET['found_id'])) {
    $item_id = intval($_GET['found_id']);
    $id_column = 'found_id';
} else {
    echo "Invalid request.";
    exit;
}

// Ensure table is valid
if (!in_array($table, ['lost_items', 'found_items'])) {
    echo "Invalid table.";
    exit;
}

// Fetch item details
$item_sql = "SELECT * FROM $table WHERE $id_column = ?";
$stmt = $conn->prepare($item_sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item_result = $stmt->get_result();

if ($item_result->num_rows == 0) {
    echo "Item not found.";
    exit;
}
$item = $item_result->fetch_assoc();

// Fetch uploader details
$uploader_sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($uploader_sql);
$stmt->bind_param("i", $item['user_id']);
$stmt->execute();
$uploader = $stmt->get_result()->fetch_assoc();

// Fetch latest claim (if any)
if ($table === 'lost_items') {
    $claim_sql = "SELECT c.*, u.first_name, u.last_name, u.phone, u.email
                  FROM claims c
                  JOIN users u ON c.user_id = u.user_id
                  WHERE c.lost_id = ?
                  ORDER BY c.claim_id DESC LIMIT 1";
    $stmt = $conn->prepare($claim_sql);
    $stmt->bind_param("i", $item_id);
} else { // found_items
    $claim_sql = "SELECT c.*, u.first_name, u.last_name, u.phone, u.email
                  FROM claims c
                  JOIN users u ON c.user_id = u.user_id
                  WHERE c.found_id = ?
                  ORDER BY c.claim_id DESC LIMIT 1";
    $stmt = $conn->prepare($claim_sql);
    $stmt->bind_param("i", $item_id);
}
$stmt->execute();
$claim_result = $stmt->get_result();
$claimer = $claim_result->num_rows > 0 ? $claim_result->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%);
            min-height: 100vh;
            color: #e3eafc;
        }
        .navbar-custom {
            background: linear-gradient(90deg, #0d1a2f 80%, #1976d2 100%);
        }
        .navbar-brand, .nav-link {
            letter-spacing: 2px;
            font-size: 1.1rem;
            color: #e3eafc !important;
        }
        .nav-link {
            margin-left: 1.5rem !important;
            margin-right: 1.5rem !important;
        }
        .navbar-brand i {
            color: #90caf9;
        }
        .card {
            background: #162447;
            color: #e3eafc;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(25, 118, 210, 0.2);
        }
        .section-title {
            color: #90caf9;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
        }
        .badge {
            font-size: 1rem;
            padding: 0.5em 1em;
        }
        .info-box {
            background: #12203a;
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 10px rgba(25, 118, 210, 0.08);
        }
        .info-box h5 {
            color: #90caf9;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .btn-primary, .btn-success {
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #1976d2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1565c0;
        }
        .btn-success {
            background-color: #43a047;
            border: none;
        }
        .btn-success:hover {
            background-color: #388e3c;
        }
        .text-muted, .text-danger {
            font-size: 1.05rem;
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

<div class="container my-5">
    <h2 class="section-title"><i class="fa fa-box-open me-2"></i>Item Details</h2>
    <div class="card mb-4">
        <img src="<?php echo !empty($item['item_image']) ? htmlspecialchars($item['item_image']) : 'images/placeholder.png'; ?>" 
             class="card-img-top" style="max-height:300px;object-fit:cover;">
        <div class="card-body">
            <h4 class="mb-3"><?php echo htmlspecialchars($item['item_name']); ?></h4>
            <div class="mb-2"><b>Description:</b> <?php echo htmlspecialchars($item['description']); ?></div>
            <div class="mb-2"><b>Date:</b> 
                <?php echo ($table == 'lost_items') ? htmlspecialchars($item['date_lost']) : htmlspecialchars($item['date_found']); ?>
            </div>
            <div class="mb-2"><b>Location:</b> <?php echo htmlspecialchars($item['location']); ?></div>

            <?php if ($claimer): ?>
                <div class="mb-2"><b>Claim Status:</b> 
                    <span class="badge bg-<?php 
                        echo ($claimer['claim_status'] === 'approved') ? 'success' : 
                             (($claimer['claim_status'] === 'pending') ? 'warning' : 'danger'); ?>">
                        <?php echo htmlspecialchars($claimer['claim_status']); ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="mb-2"><b>Claim Status:</b> <span class="badge bg-secondary">No claim yet</span></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="info-box mb-4">
                <h5>Uploaded By</h5>
                <div><?php echo htmlspecialchars($uploader['first_name'] . " " . $uploader['last_name']); ?></div>
                <div>Email: <?php echo htmlspecialchars($uploader['email']); ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if ($claimer): ?>
                <?php if ($claimer['claim_status'] === 'approved'): ?>
                    <div class="info-box mb-4">
                        <h5>Claimed By</h5>
                        <div><?php echo htmlspecialchars($claimer['first_name'] . " " . $claimer['last_name']); ?></div>
                        <div>Email: <?php echo htmlspecialchars($claimer['email']); ?></div>
                        <div>Phone: <?php echo htmlspecialchars($claimer['phone']); ?></div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="tel:<?php echo $uploader['phone']; ?>" class="btn btn-primary">
                                <i class="fa fa-phone me-1"></i>Contact
                            </a>
                            <a href="https://wa.me/91<?php echo $uploader['phone']; ?>?text=Hello, I am contacting regarding the item '<?php echo urlencode($item['item_name']); ?>'." 
                               target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                <?php elseif ($claimer['claim_status'] === 'pending'): ?>
                    <div class="info-box mb-4">
                        <span class="text-muted"><em>This item is currently under review. Claimer details will be visible once approved.</em></span>
                    </div>
                <?php elseif ($claimer['claim_status'] === 'rejected'): ?>
                    <div class="info-box mb-4">
                        <span class="text-danger"><em>The latest claim was rejected.</em></span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="info-box mb-4">
                    <span class="text-muted"><em>No one has claimed this item yet.</em></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>