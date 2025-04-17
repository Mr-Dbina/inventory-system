<?php

$first_name = "";
$last_name = "";
$email = "";
$phone = "";
$address = "";

$first_name_error = "";
$last_name_error = "";
$email_error = "";
$phone_error = "";
$address_error = "";
$password_error = "";
$confirm_password_error = "";

$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($first_name)) {
        $first_name_error = "First Name is required";
        $error = true;
    }
    if (empty($last_name)) {
        $last_name_error = "Last Name is required";
        $error = true;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Email is not valid";
        $error = true;
    }

    include "db.php";
    $dbConnection = getDatabaseConnection();

    // Check for duplicate email
    $statement = $dbConnection->prepare("SELECT id FROM users WHERE email=?");
    $statement->bind_param("s", $email);
    $statement->execute();
    $statement->store_result();

    if ($statement->num_rows > 0) {
        $email_error = "Email is already used";
        $error = true;
    }
    $statement->close();

    if (!preg_match("/^(\+|00\d{1,3})?[- ]?\d{7,12}$/", $phone)) {
        $phone_error = "Phone format is not valid";
        $error = true;
    }
    if (strlen($password) < 6) {
        $password_error = "Password must be at least 6 characters";
        $error = true;
    }
    if ($confirm_password != $password) {
        $confirm_password_error = "Passwords do not match";
        $error = true;
    }

    // Only insert if no errors
    if (!$error) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');

        $statement = $dbConnection->prepare("INSERT INTO users (first_name, last_name, email, phone, address, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->bind_param('sssssss', $first_name, $last_name, $email, $phone, $address, $password, $created_at);

        if ($statement->execute()) {
            $insert_id = $dbConnection->insert_id;

            // Start session and store user data
            session_start();
            $_SESSION["id"] = $insert_id;
            $_SESSION["first_name"] = $first_name;
            $_SESSION["last_name"] = $last_name;
            $_SESSION["email"] = $email;
            $_SESSION["phone"] = $phone;
            $_SESSION["address"] = $address;
            $_SESSION["created_at"] = $created_at;

            header("Location: /login.php");
            exit;
        } else {
            echo "Error: " . $statement->error;
        }

        $statement->close();
    }

    $dbConnection->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FINAL PROJECT</title>
    <link rel="icon" href="/image/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .hero {
            background-image: url('/image/gym.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero .content {
            max-width: 600px;
            text-align: center;
        }
        .navbar {
            background-color: white;
        }
    </style>
</head>
<body>
<div class="hero">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 mx-auto border shadow p-4 bg-light rounded">
                <h2 class="text-center mb-4 text-dark">Register</h2>
                <hr />
                <form method="post">
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">First Name</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="first_name" value="<?= $first_name ?>">
                            <span class="text-danger"><?= $first_name_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Last Name</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="last_name" value="<?= $last_name ?>">
                            <span class="text-danger"><?= $last_name_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Email</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="email" value="<?= $email ?>">
                            <span class="text-danger"><?= $email_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Phone</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="phone" value="<?= $phone ?>">
                            <span class="text-danger"><?= $phone_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Address</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="address" value="<?= $address ?>">
                            <span class="text-danger"><?= $address_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Password</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="password" name="password">
                            <span class="text-danger"><?= $password_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-dark fw-bold">Confirm Password</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="password" name="confirm_password">
                            <span class="text-danger"><?= $confirm_password_error ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col d-grid">
                            <button type="submit" class="btn btn-outline-primary fw-bold">Register</button>
                        </div>
                        <div class="col d-grid">
                            <a href="/login.php" class="btn btn-outline-primary fw-bold">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+3oUZUAKe3w2gv9KSf+VVFIhvjDGi" crossorigin="anonymous"></script>
</body>
</html>

