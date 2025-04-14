<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Only allow Admins to view analytics
if ($_SESSION['role'] !== 'admin') {
    echo "<h3>Access Denied: Admins Only</h3>";
    exit();
}

$totalItems = 0;
$totalQuantity = 0;
$lowStock = [];

$result = $conn->query("SELECT * FROM inventory");
$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {
    $totalItems++;
    $totalQuantity += $row['quantity'];

    $labels[] = $row['item_name'];
    $data[] = $row['quantity'];

    if ($row['quantity'] < 5) {  // Low stock threshold
        $lowStock[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Boxicons CDN for icons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            display: flex;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #ffc107;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }
        .sidebar .img-text img {
            width: 60px;
            margin-bottom: 10px;
        }
        .sidebar .name {
            font-size: 20px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
        .sidebar .profession {
            font-size: 12px;
            color: #333;
        }
        .menu-links {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .menu-links li {
            width: 100%;
        }
        .menu-links a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #000;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .menu-links a:hover {
            background: #ffd54f;
        }
        .menu-links i {
            font-size: 20px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 270px;
            padding: 30px;
            width: calc(100% - 270px);
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        a {
            text-decoration: none;
            padding: 6px 12px;
            background: #ffcc00;
            border-radius: 4px;
        }
        canvas {
            max-width: 600px;
            margin: 20px auto;
            display: block;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <header>
        <div class="img-text">
            <img src="logo.png" alt="logo">
            <div class="text header-text">
                <span class="name">LELELEMON</span>
                <span class="profession">INVENTORY</span>
            </div>
        </div>
    </header>

    <ul class="menu-links">
        <li class="nav-link">
            <a href="#">
                <i class='bx bx-search'></i>
                <input type="search" placeholder="Search" style="border:none;outline:none;background:none;">
            </a>
        </li>
        <li class="nav-link">
            <a href="profile.php">
                <i class='bx bx-user'></i>
                <span class="text nav-text">Profile</span>
            </a>
        </li>
        <li class="nav-link">
            <a href="dashboard.php">
                <i class='bx bxs-food-menu'></i>
                <span class="text nav-text">Inventory</span>
            </a>
        </li>
        <li class="nav-link">
            <a href="analytics.php">
                <i class='bx bx-bar-chart-alt'></i>
                <span class="text nav-text">Analytics</span>
            </a>
        </li>
        <li class="nav-link">
            <a href="logout.php">
                <i class='bx bx-log-out'></i>
                <span class="text nav-text">Logout</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <h2>📊 Inventory Analytics</h2>
    <a href="dashboard.php">⬅️ Back to Inventory</a>

    <div class="card">
        <h3>Total Products: <?= $totalItems ?></h3>
    </div>

    <div class="card">
        <h3>Total Quantity in Stock: <?= $totalQuantity ?></h3>
    </div>

    <div class="card">
        <h3>📈 Stock Overview</h3>
        <canvas id="stockChart"></canvas>
    </div>

    <div class="card">
        <h3>⚠️ Low Stock Items (Less than 5)</h3>
        <?php if (count($lowStock) > 0): ?>
            <table>
                <tr><th>Item</th><th>Quantity</th></tr>
                <?php foreach ($lowStock as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>All items are sufficiently stocked! ✅</p>
        <?php endif; ?>
    </div>

    <script>
        const ctx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Quantity',
                    data: <?= json_encode($data); ?>,
                    backgroundColor: '#ffcc00',
                    borderColor: '#ffaa00',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
