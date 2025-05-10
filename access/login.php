<?php
include "l.php";

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
        include "../database/db.php";
        $dbConnection = getDatabaseConnection();

        if ($dbConnection) {
            $statement = $dbConnection->prepare(
                "SELECT id, first_name, last_name, phone, address, role, password, created_at FROM users WHERE email = ?"
            );

            if ($statement) {
                $statement->bind_param('s', $email);
                $statement->execute();
                $statement->bind_result($id, $first_name, $last_name, $phone, $address, $role, $stored_password, $created_at);

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
                        $_SESSION["role"] = $role;
                        $_SESSION["created_at"] = $created_at;

                        header("Location: ../views/profile.php");
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

<!DOCTYPE html>
<html>

<head>
  <title>LELELEMON</title>
  <link rel="stylesheet" type="text/css" href="../style/responsive.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a81368914c.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
  <img class="wave" src="../views/images/wavee.png">
  <div class="container">
    <div class="img">
      <img src="../views/images/remove.png">
    </div>
    <div class="login-content">
      <form method="post">
        <h2 class="title">Welcome To Inventory</h2>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="input-div one">
          <div class="i">
            <i class="bx bxs-user user"></i>
          </div>
          <div class="div">
            <h5>Email</h5>
            <input class="input <?= !empty($error) ? 'is-invalid' : '' ?>" name="email" type="email"
              value="<?= htmlspecialchars($email) ?>" required />
          </div>
        </div>

        <div class="input-div pass">
          <div class="i">
            <i class="bx bxs-lock-alt"></i>
          </div>
          <div class="div input-with-icon" style="position: relative;">
            <h5>Password</h5>
            <input id="password" class="input <?= !empty($error) ? 'is-invalid' : '' ?>" type="password" name="password"
              required />
            <i id="password-toggle" class='bx bxs-lock-open-alt icon-right'
              style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #FEDF05; cursor: pointer;"></i>
          </div>
        </div>

        <div class="form-options">
          <label><input type="checkbox" /> Remember me</label>
          <a href="../access/forgot.php">Forgot Password?</a>
        </div>

        <input type="submit" class="btn" value="Login">
        <p class="signup-text"> Don't have an account? <a href="register.php">Sign Up</a>
        </p>
      </form>
    </div>
  </div>

  <script type="text/javascript" src="../js/responsive.js"></script>
</body>

</html>