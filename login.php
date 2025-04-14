<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
    
        if ($row['role'] === 'admin') {
            header("Location: admin_page.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        echo "<p style='color:red'>Invalid login</p>";
    }    
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; text-align: center; margin-top: 100px; }
        form { display: inline-block; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input { margin: 5px; padding: 8px; width: 200px; }
        input[type=submit] { width: 100%; background: #ffcc00; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Lelelemon Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>