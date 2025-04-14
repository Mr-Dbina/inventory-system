<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM inventory WHERE id=$id");
$item = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $qty = $_POST['quantity'];
    
    if ($_SESSION['role'] === 'admin') {
        $name = $_POST['item_name'];
        $sql = "UPDATE inventory SET item_name='$name', quantity=$qty WHERE id=$id";
    } else {  // Staff can only update quantity
        $sql = "UPDATE inventory SET quantity=$qty WHERE id=$id";
    }

    $conn->query($sql);
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Item</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 50px; }
        form { display: inline-block; }
        input, button { padding: 8px; margin: 5px; }
        input[readonly] { background: #eee; }
    </style>
</head>
<body>
    <h2>Edit Item</h2>
    <form method="post">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
        <?php else: ?>
            <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" readonly>
        <?php endif; ?>
        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
