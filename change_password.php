<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current = md5($_POST['current_password']);
    $newpass = md5($_POST['new_password']);
    $username = $_SESSION['username'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username' AND password = '$current'");

    if ($result->num_rows == 1) {
        $conn->query("UPDATE users SET password = '$newpass' WHERE username = '$username'");
        $message = "✅ Password updated successfully!";
    } else {
        $message = "❌ Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; text-align: center; }
    form { display: inline-block; margin-top: 20px; }
    input { display:block; padding:8px; margin:8px auto; width:250px; }
    button { padding:8px 16px; background:#ffc107; border:none; border-radius:4px; cursor:pointer; }
    .message { margin-top: 10px; }
  </style>
</head>
<body>

<h2>🔒 Change Password</h2>

<?php if ($message) echo "<p class='message'>$message</p>"; ?>

<form method="POST">
    <input type="password" name="current_password" placeholder="Current Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Update Password</button>
</form>

<a href="profile.php" style="display:block;margin-top:20px;">⬅️ Back to Profile</a>

</body>
</html>
