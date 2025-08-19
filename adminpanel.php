<?php
session_start();

// ====== Admin Access Restriction ======
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
    header("Location: login.html");
    exit;
}

// ====== Database Connection ======
$conn = new mysqli("localhost", "root", "", "lostfound");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ====== Handle Delete Action ======
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Delete from lost_items
    $stmt = $conn->prepare("DELETE FROM lost_items WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Delete from found_items
    $stmt = $conn->prepare("DELETE FROM found_items WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Delete from users
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: adminpanel.php");
    exit;
}

// ====== Handle Block/Unblock Action ======
if (isset($_GET['toggle_block'])) {
    $toggle_id = intval($_GET['toggle_block']);
    $new_status = ($_GET['status'] === 'active') ? 'blocked' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $toggle_id);
    $stmt->execute();
    $stmt->close();

    header("Location: adminpanel.php");
    exit;
}

// ====== Fetch All Users ======
$result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #0d1a2f 70%, #1976d2 100%) !important;
            min-height: 100vh;
            color: #e3eafc;
        }
        .container {
            margin-top: 50px;
        }
        .card, .table, .table th, .table td {
            background: #162447 !important;
            color: #e3eafc !important;
            border-color: #1976d2 !important;
        }
        .table thead th {
            background: #1976d2 !important;
            color: #fff !important;
            border-color: #162447 !important;
        }
        .badge.bg-success {
            background-color: #43a047 !important;
        }
        .badge.bg-danger {
            background-color: #d32f2f !important;
        }
        .btn-secondary, .btn-danger, .btn-warning {
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 1px;
        }
        .btn-secondary {
            background: #1976d2;
            color: #fff;
            border: none;
        }
        .btn-secondary:hover {
            background: #1565c0;
            color: #fff;
        }
        .btn-danger {
            background: #d32f2f;
            color: #fff;
            border: none;
        }
        .btn-danger:hover {
            background: #b71c1c;
            color: #fff;
        }
        .btn-warning {
            background: #ffb300;
            color: #162447;
            border: none;
        }
        .btn-warning:hover {
            background: #ffa000;
            color: #162447;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(25, 118, 210, 0.07) !important;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Admin Panel - Manage Users</h2>
    <a href="admin.php" class="btn btn-secondary mb-3">Back</a>

    <table class="table table-bordered table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th width="5%">ID</th>
                <th width="20%">Name</th>
                <th width="25%">Email</th>
                <th width="15%">Phone</th>
                <th width="10%">Status</th>
                <th width="25%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td>
                        <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                       <!-- <a href="adminpanel.php?delete=<?= $row['user_id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this user and all their items?');">
                           Delete
                        </a>-->
                        <a href="adminpanel.php?toggle_block=<?= $row['user_id'] ?>&status=<?= $row['status'] ?>" 
                           class="btn btn-warning btn-sm">
                           <?= $row['status'] === 'active' ? 'Block' : 'Unblock' ?>
                        </a>
                        <a href="viewuser.php?user_id=<?= $row['user_id'] ?>" 
                           class="btn btn-secondary btn-sm">View items</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>