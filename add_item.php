<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['item_name'];
    $qty = $_POST['quantity'];
    $sql = "INSERT INTO inventory (item_name, quantity) VALUES ('$name', $qty)";
    $conn->query($sql);
}

header("Location: dashboard.php");
?>