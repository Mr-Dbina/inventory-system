<?php

include_once('tool/db.php');

$msg = "";

function forgot_password($email, $new_password, $confirmation) {
    global $msg; // Allow access to $msg variable from outside
    $connection = getDatabaseConnection();

    $query = "SELECT * FROM `users` WHERE `email` = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        if ($confirmation !== $new_password) {
            $msg = "Passwords do not match.";
            return false;
        }

        $user = $res->fetch_assoc();

        $query = "UPDATE `users` SET `password` = ? WHERE `users`.`id` = ?";
        $stmt = $connection->prepare($query);

        $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt->bind_param('si', $hashed_pw, $user['id']);
        $stmt->execute();

        return true; // Password successfully updated
    } else {
        $msg = "Email not found.";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? "";
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $msg = "All fields are required.";
    } else {
        $success = forgot_password($email, $new_password, $confirm_password);
        if ($success) {
            // Redirect to login.php after success
            header("Location: login.php");
            exit(); // Ensure no further code is executed
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FINAL PROJECT</title>
    <link rel="icon" href="/image/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Your existing CSS */
    </style>
</head>
<body>
<div class="container-fluid bg-cover" style="background-image: url('/image/gym.jpg'); height: 100vh;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="border shadow p-4" style="width: 400px; background-color: rgba(0, 0, 0, 0.6); border-radius: 8px;">
            <h2 class="text-center mb-4 text-white">Change Password</h2>
            <hr style="border-color: white;" />

            <!-- Error Display -->
            <?php if (!empty($msg)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><?= htmlspecialchars($msg) ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <form action="" method="post">
                <div class="mb-3">
                    <label class="form-label text-white" for="email">Email</label>
                    <input 
                        type="email"
                        class="form-control"
                        name="email"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        required
                    />
                </div>
                <div class="mb-3">
                    <label class="form-label text-white" for="new_password">New Password</label>
                    <input 
                        type="password"
                        class="form-control"
                        name="new_password"
                        required
                    />
                </div>
                <div class="mb-3">
                    <label class="form-label text-white" for="confirm_password">Confirm Password</label>
                    <input 
                        type="password"
                        class="form-control"
                        name="confirm_password"
                        required
                    />
                </div>
                <div class="row mb-3">
                    <div class="col d-grid">
                        <button type="submit" class="btn btn-outline-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-pphKo1+p3aVjAwcE8PFXRb7RzQh/B1SxXT3aK5wB2P7jsD5zY4f7l5lYZQ3jc6pw" crossorigin="anonymous"></script>
</body>
</html>

