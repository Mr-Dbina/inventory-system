<?php 
// Initialize variables
$fullname = "";
$date_of_birth = "";
$email = "";
$phone = "";
$address = "";

$fullname_error = "";
$date_of_birth_error = "";
$email_error = "";
$phone_error = "";
$address_error = "";
$password_error = "";
$confirm_password_error = "";

$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $fullname = trim($_POST['fullname']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($fullname)) {
        $fullname_error = "Full Name is required";
        $error = true;
    }

    if (empty($date_of_birth)) {
        $date_of_birth_error = "Date of Birth is required";
        $error = true;
    } else {
        $dob_timestamp = strtotime($date_of_birth);
        if (!$dob_timestamp || $dob_timestamp > time()) {
            $date_of_birth_error = "Please enter a valid date of birth";
            $error = true;
        }
    }

    if (empty($email)) {
        $email_error = "Email is required";
        $error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Email is not valid";
        $error = true;
    }

    if (empty($phone)) {
        $phone_error = "Phone number is required";
        $error = true;
    } elseif (!preg_match("/^(\+|00)?[0-9]{1,4}[0-9]{7,12}$/", str_replace(['-', ' '], '', $phone))) {
        $phone_error = "Phone number is not valid. Use format: +XXX XXXXXXXXX";
        $error = true;
    }

    if (empty($address)) {
        $address_error = "Address is required";
        $error = true;
    }

    if (empty($password)) {
        $password_error = "Password is required";
        $error = true;
      } elseif (strlen($password) < 8) {
          $password_error = "Password must be at least 8 characters";
          $error = true;
      } elseif (!preg_match('/[A-Z]/', $password)) {
          $password_error = "Password must contain at least one uppercase letter";
          $error = true;
      } elseif (!preg_match('/[a-z]/', $password)) {
          $password_error = "Password must contain at least one lowercase letter";
          $error = true;
      } elseif (!preg_match('/[0-9]/', $password)) {
          $password_error = "Password must contain at least one number";
          $error = true;
      } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
          $password_error = "Password must contain at least one special character";
          $error = true;
    }

    if ($password != $confirm_password) {
        $confirm_password_error = "Passwords do not match";
        $error = true;
    }

    // Include DB connection
    include_once "../database/db.php";
    $dbConnection = getDatabaseConnection();

    // Check if email already exists
    $statement = $dbConnection->prepare("SELECT id FROM users WHERE email = ?");
    $statement->bind_param("s", $email);
    $statement->execute();
    $statement->store_result();
    if ($statement->num_rows > 0) {
        $email_error = "Email is already used";
        $error = true;
    }
    $statement->close();

    if (!$error) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');

        $stmt = $dbConnection->prepare("INSERT INTO users (fullname, date_of_birth, email, phone, address, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $fullname, $date_of_birth, $email, $phone, $address, $password_hash, $created_at);

        if ($stmt->execute()) {
            $insert_id = $dbConnection->insert_id;
            session_start();
            $_SESSION["id"] = $insert_id;
            $_SESSION["fullname"] = $fullname;
            $_SESSION["date_of_birth"] = $date_of_birth;
            $_SESSION["email"] = $email;
            $_SESSION["phone"] = $phone;
            $_SESSION["address"] = $address;
            $_SESSION["created_at"] = $created_at;

            header("Location: ../access/login.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $dbConnection->close();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LELELEMON Register</title>
  <link rel="icon" href="/image/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
  body {
    font-family: 'Poppins', sans-serif;
    background-color: #EEEEEE;
    background-image: ('../views/images/logo.png');
    height: 100vh;
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

  .text-danger {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
  }
  </style>
</head>

<body>
  <div class="container-fluid bg-cover" style="background-image: url('../views/images/logo.png'); height: 100vh;">
    <div class="d-flex justify-content-center align-items-center h-100">
      <div class="border shadow p-4" style="width: 600px; background-color: rgba(0, 0, 0, 0.7); border-radius: 12px;">
        <h2 class="text-center mb-4 text-white">Register</h2>
        <hr style="border-color: white;" />

        <!-- Error/Success Display -->
        <?php if (!empty($error) && $error === true) { ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Please fix the errors below.</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php } ?>

        <form method="post">
          <!-- Full Name -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="fullname">Full Name</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-user"></i></span>
                <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($fullname); ?>"
                  required>
              </div>
              <span class="text-danger"><?= $fullname_error; ?></span>
            </div>
          </div>

          <!-- Date of Birth -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="date_of_birth">Date of Birth</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-calendar"></i></span>
                <input type="date" class="form-control" name="date_of_birth"
                  value="<?= htmlspecialchars($date_of_birth); ?>" required>
              </div>
              <span class="text-danger"><?= $date_of_birth_error; ?></span>
            </div>
          </div>

          <!-- Email -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="email">Email</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-envelope"></i></span>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email); ?>" required>
              </div>
              <span class="text-danger"><?= $email_error; ?></span>
            </div>
          </div>

          <!-- Phone -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="phone">Phone</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-phone"></i></span>
                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($phone); ?>" required>
              </div>
              <span class="text-danger"><?= $phone_error; ?></span>
            </div>
          </div>

          <!-- Address -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="address">Address</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-home"></i></span>
                <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address); ?>"
                  required>
              </div>
              <span class="text-danger"><?= $address_error; ?></span>
            </div>
          </div>

          <!-- Password -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="password">Password</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-lock-alt"></i></span>
                <input type="password" class="form-control" name="password" id="password" required>
                <span class="input-group-text" style="cursor: pointer;" id="password-toggle">
                  <i class="bx bxs-lock-open-alt"></i>
                </span>
              </div>
              <small class="form-text text-white-50">Password must be at least 8 characters and include uppercase,
                lowercase, number, and special character.</small>
              <span class="text-danger"><?= $password_error; ?></span>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3 row">
            <label class="col-sm-4 col-form-label text-white" for="confirm_password">Confirm Password</label>
            <div class="col-sm-8">
              <div class="input-group">
                <span class="input-group-text"><i class="bx bxs-lock"></i></span>
                <input type="password" class="form-control" name="confirm_password" id="confirm-password" required>
                <span class="input-group-text" style="cursor: pointer;" id="confirm-password-toggle">
                  <i class="bx bxs-lock-open-alt"></i>
                </span>
              </div>
              <span class="text-danger"><?= $confirm_password_error; ?></span>
            </div>
          </div>

          <!-- Submit buttons -->
          <div class="row mb-3">
            <div class="col d-grid">
              <button type="submit" class="btn btn-outline-primary">Register</button>
            </div>
            <div class="col d-grid">
              <a href="../access/login.php" class="btn btn-outline-light"> Cancel
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
  document.addEventListener('DOMContentLoaded', function() {
    // Password toggle visibility
    const passwordInput = document.getElementById("password");
    const toggleIcon = document.getElementById("password-toggle");

    toggleIcon.addEventListener("click", function() {
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      const icon = this.querySelector('i');
      icon.classList.toggle("bxs-lock");
      icon.classList.toggle("bxs-lock-open-alt");
    });

    // Confirm password toggle visibility
    const confirmPasswordInput = document.getElementById("confirm-password");
    const confirmToggleIcon = document.getElementById("confirm-password-toggle");

    confirmToggleIcon.addEventListener("click", function() {
      const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password";
      confirmPasswordInput.setAttribute("type", type);
      const icon = this.querySelector('i');
      icon.classList.toggle("bxs-lock");
      icon.classList.toggle("bxs-lock-open-alt");
    });

    // Password validation
    confirmPasswordInput.addEventListener('input', function() {
      if (passwordInput.value !== confirmPasswordInput.value) {
        confirmPasswordInput.setCustomValidity("Passwords do not match");
      } else {
        confirmPasswordInput.setCustomValidity("");
      }
    });

    passwordInput.addEventListener('input', function() {
      if (confirmPasswordInput.value !== '') {
        if (passwordInput.value !== confirmPasswordInput.value) {
          confirmPasswordInput.setCustomValidity("Passwords do not match");
        } else {
          confirmPasswordInput.setCustomValidity("");
        }
      }
    });
  });
  </script>
</body>

</html>