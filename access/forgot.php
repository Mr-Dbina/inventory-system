<?php

include_once('../database/db.php');
// Make sure the session is started before using session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = "";
// Pre-fill email from cookie or session if available
$email = "";
if (isset($_COOKIE['remember_email'])) {
    $email = $_COOKIE['remember_email'];
} elseif (isset($_SESSION["email"])) {
    $email = $_SESSION["email"];
}

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

        // Fixed the bind_param - added 'si' for string and integer types
        $stmt->bind_param('si', $hashed_pw, $user['id']);
        $stmt->execute();

        $msg = "Password updated successfully!";
        return true; // Password successfully updated
    } else {
        $msg = "Email not found.";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? "";
    $new_password = $_POST['new_password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $msg = "All fields are required.";
    } else {
        $success = forgot_password($email, $new_password, $confirm_password);
        if ($success) {
            // Set a success message in session before redirecting
            $_SESSION['password_reset_success'] = true;
            // Redirect to login.php after success
            header("Location: ../access/login.php");
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
  <title>LELELEMON - Reset Password</title>
  <link rel="icon" href="/image/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #EEEEEE;
  }

  .bg-cover {
    background-size: cover;
    background-position: center;
  }

  .alert {
    margin-bottom: 20px;
  }

  .btn-outline-primary {
    color: #FEDF05;
    border-color: #FEDF05;
  }

  .btn-outline-primary:hover {
    background-color: #FEDF05;
    color: #000;
    border-color: #FEDF05;
  }

  .btn-outline-light:hover {
    color: #000;
  }
  </style>
</head>

<body>
  <div class="container-fluid bg-cover" style="background-image: url('../views/images/logo.png'); height: 100vh;">
    <div class="d-flex justify-content-center align-items-center h-100">
      <div class="border shadow p-4" style="width: 400px; background-color: rgba(0, 0, 0, 0.7); border-radius: 12px;">
        <h2 class="text-center mb-4 text-white">Forgot Password</h2>
        <hr style="border-color: white;" />

        <!-- Error/Success Display -->
        <?php if (!empty($msg)) { 
          $alertClass = strpos($msg, 'successfully') !== false ? 'alert-success' : 'alert-danger';
        ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
          <strong><?= htmlspecialchars($msg) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>

        <form action="" method="post">
          <div class="mb-3">
            <label class="form-label text-white" for="email">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bx bxs-envelope"></i></span>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-white" for="new_password">New Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bx bxs-lock-alt"></i></span>
              <input type="password" class="form-control" id="new-password" name="new_password" required />
              <span class="input-group-text" style="cursor: pointer;" id="new-password-toggle">
                <i class="bx bxs-lock-open-alt"></i>
              </span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-white" for="confirm_password">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bx bxs-lock"></i></span>
              <input type="password" class="form-control" id="confirm-password" name="confirm_password" required />
              <span class="input-group-text" style="cursor: pointer;" id="confirm-password-toggle">
                <i class="bx bxs-lock-open-alt"></i>
              </span>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col d-grid">
              <button type="submit" class="btn btn-outline-primary">Reset Password</button>
            </div>
          </div>
          <div class="row">
            <div class="col d-grid">
              <a href="../access/login.php" class="btn btn-outline-light">Cancel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-pphKo1+p3aVjAwcE8PFXRb7RzQh/B1SxXT3aK5wB2P7jsD5zY4f7l5lYZQ3jc6pw" crossorigin="anonymous">
  </script>
  <script>
  // Add password validation and toggle functionality
  document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new-password');
    const confirmPassword = document.getElementById('confirm-password');
    const newPasswordToggle = document.getElementById('new-password-toggle');
    const confirmPasswordToggle = document.getElementById('confirm-password-toggle');

    // Password toggle functionality for new password
    newPasswordToggle.addEventListener("click", function() {
      const type = newPassword.getAttribute("type") === "password" ? "text" : "password";
      newPassword.setAttribute("type", type);
      const icon = this.querySelector('i');
      icon.classList.toggle("bxs-lock");
      icon.classList.toggle("bxs-lock-open-alt");
    });

    // Password toggle functionality for confirm password
    confirmPasswordToggle.addEventListener("click", function() {
      const type = confirmPassword.getAttribute("type") === "password" ? "text" : "password";
      confirmPassword.setAttribute("type", type);
      const icon = this.querySelector('i');
      icon.classList.toggle("bxs-lock");
      icon.classList.toggle("bxs-lock-open-alt");
    });

    // Password validation
    confirmPassword.addEventListener('input', function() {
      if (newPassword.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords do not match");
      } else {
        confirmPassword.setCustomValidity("");
      }
    });

    newPassword.addEventListener('input', function() {
      if (confirmPassword.value !== '') {
        if (newPassword.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity("Passwords do not match");
        } else {
          confirmPassword.setCustomValidity("");
        }
      }
    });
  });
  </script>
</body>

</html>