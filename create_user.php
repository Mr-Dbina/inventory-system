<?php
include 'db.php';
session_start();

// Allow only logged-in admins
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_user = $_POST['username'];
    $new_pass = md5($_POST['password']);  // MD5 hashed like login.php
    $role = $_POST['role'];

    // Prevent duplicate usernames
    $check = $conn->query("SELECT * FROM users WHERE username = '$new_user'");
    if ($check->num_rows > 0) {
        $message = "⚠️ Username already exists!";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$new_user', '$new_pass', '$role')";
        if ($conn->query($sql) === TRUE) {
            $message = "✅ User created successfully!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New User</title>
    <style>
        body { font-family: Arial; padding: 40px; text-align: center; }
        form { display: inline-block; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        input, select { margin: 5px; padding: 8px; width: 200px; }
        button { width: 100%; padding: 8px; background: #ffcc00; border: none; cursor: pointer; }
        .msg { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Create New User</h2>
    <a href="admin_page.php">⬅️ Back to Admin Page</a>
    <form method="post">
        <input type="text" name="username" placeholder="New Username" required><br>
        <input type="password" name="password" placeholder="New Password" required><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select><br>
        <button type="submit">Create User</button>
    </form>

    <?php if (!empty($message)) echo "<div class='msg'>$message</div>"; ?>
</body>
</html>
