<?php
include 'db.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$id = $_GET['id'];
$conn->query("DELETE FROM inventory WHERE id=$id");
header("Location: dashboard.php");
?>