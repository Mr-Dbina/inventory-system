<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
        }

        .main-content h2 {
            font-size: 24px;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        }

        .admin-buttons a {
            display: inline-block;
            margin: 8px 12px 12px 0;
            padding: 10px 16px;
            background-color: #ffc107;
            color: #000;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .admin-buttons a:hover {
            background-color: #ffca28;
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
            padding: 4px 8px;
            background: #ffcc00;
            color: #000;
            border-radius: 4px;
            margin: 0 2px;
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
                <i class='bx bx-search icon'></i>
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
            <a href="inventory.php">
                <i class='bx bxs-food-menu'></i>
                <span class="text nav-text">Inventory</span>
            </a>
        </li>
        <li class="nav-link">
            <a href="analytics.php">
                <i class='bx bx-bar-chart'></i>
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
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)</h2>
    <a href="logout.php">Logout</a>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin_page.php" style="display:inline-block;margin:10px 5px 20px 0;background:#ffcc00;padding:8px 12px;border-radius:4px;">⬅️ Back to Admin Page</a>
        <a href="analytics.php" style="display:inline-block;margin:10px 0;background:#ffcc00;padding:8px 12px;border-radius:4px;">📊 View Analytics</a>
    <?php endif; ?>

    <h3>Inventory List</h3>
    <table>
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') echo "<th>Actions</th>"; ?>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                    <td>
                        <a href="edit_item.php?id=<?= $row['id'] ?>">Edit</a>
                        <a href="delete_item.php?id=<?= $row['id'] ?>">Delete</a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
