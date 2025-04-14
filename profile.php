<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['username'];
$role = $_SESSION['role'];

// You can extend this if your users table has emails or more info!
$result = $conn->query("SELECT * FROM users WHERE username = '$user'");
$userData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; text-align: center; }
        .card { display: inline-block; padding: 20px; border: 1px solid #ccc; border-radius: 8px; margin: 20px auto; text-align: left; }
        .card h2 { margin-top: 0; }
        a { display: inline-block; margin-top: 20px; text-decoration: none; background: #ffcc00; color: #000; padding: 10px 15px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="card">
    <h2>👤 User Profile</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($userData['username']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($userData['role']) ?></p>
    <p><strong>Password:</strong> ********</p> <!-- Hide actual password -->

    <a href="change_password.php" style="background:#ffc107; padding:8px 14px; border-radius:5px; text-decoration:none; color:#000;">🔒 Change Password</a>
<a href="admin_page.php" style="margin-left:10px;">⬅️ Back to Dashboard</a>
</div>

</body>
</html>
