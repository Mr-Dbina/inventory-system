<?php
include "l.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email = "";
$error = "";

// Process the form submission
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check for empty fields
    if (empty($email) || empty($password)) {
        $error = "Email and Password are required!";
    } else {
        include "db.php";
        $dbConnection = getDatabaseConnection();

        if ($dbConnection) {
            $statement = $dbConnection->prepare(
                "SELECT id, first_name, last_name, phone, address, password, created_at FROM users WHERE email = ?"
            );

            if ($statement) {
                $statement->bind_param('s', $email);
                $statement->execute();
                $statement->bind_result($id, $first_name, $last_name, $phone, $address, $stored_password, $created_at);

                if ($statement->fetch()) {
                    // Verify the password
                    if (password_verify($password, $stored_password)) {
                        // Store user data in session
                        $_SESSION["id"] = $id;
                        $_SESSION["first_name"] = $first_name;
                        $_SESSION["last_name"] = $last_name;
                        $_SESSION["email"] = $email;
                        $_SESSION["phone"] = $phone;
                        $_SESSION["address"] = $address;
                        $_SESSION["created_at"] = $created_at;

                        header("Location: /home.php");
                        exit;
                    } else {
                        $error = "Invalid email or password!";
                    }
                } else {
                    $error = "Invalid email or password!";
                }

                $statement->close();
            } else {
                $error = "Failed to prepare the SQL statement!";
            }
        } else {
            $error = "Database connection error!";
        }
    }
}
?>

<!-- Login Form -->
<div class="container-fluid bg-cover" style="background-image: url('/image/gym.jpg'); height: 100vh;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="border shadow p-4" style="width: 400px; background-color: rgba(0, 0, 0, 0.6); border-radius: 8px;">
            <div class="text-center mb-3">
                <img src="logo.png" width="50" height="50" alt="Logo"> 
            </div>
            <h2 class="text-center mb-4 text-white">LOGIN TO INVENTORY</h2>
            <hr style="border-color: white;" />

            <!-- Error Display -->
            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><?= htmlspecialchars($error) ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php } ?>

            <!-- Login Form -->
            <form method="post">
                <div class="mb-3">
                    <label class="form-label text-white">Email</label>
                    <input 
                        class="form-control <?= !empty($error) ? 'is-invalid' : '' ?>" 
                        name="email" 
                        value="<?= htmlspecialchars($email) ?>" 
                        required 
                    />
                </div>
                <div class="mb-3">
                    <label class="form-label text-white">Password</label>
                    <input 
                        class="form-control <?= !empty($error) ? 'is-invalid' : '' ?>" 
                        type="password" 
                        name="password" 
                        required 
                    />
                </div>
                <div class="row mb-3">
                    <div class="col text-center">
                        <a href="forgot.php" class="link-primary">Forgot Password?</a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col d-grid">
                        <button type="submit" class="btn btn-outline-primary">Login</button>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col d-grid">
                        <p class="text-white text-center">Don't Have an account? <a href="/register.php" class="link-primary">Sign Up</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
