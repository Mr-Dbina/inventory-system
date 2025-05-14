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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
  .register-box {
    background-color: #FEB20A;
  }
  </style>
</head>

<body>
  <div class="hero">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-6 mx-auto border shadow p-4 rounded register-box">
          <h2 class="text-center mb-4 text-dark">Register</h2>
          <hr />
          <form method="post">
            <!-- Full Name -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Full Name</label>
              <div class="col-sm-8">
                <input class="form-control" type="text" name="fullname" value="<?= htmlspecialchars($fullname); ?>"
                  required>
                <span class="text-danger"><?= $fullname_error; ?></span>
              </div>
            </div>

            <!-- Date of Birth -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Date of Birth</label>
              <div class="col-sm-8">
                <input class="form-control" type="date" name="date_of_birth"
                  value="<?= htmlspecialchars($date_of_birth); ?>" required>
                <span class="text-danger"><?= $date_of_birth_error; ?></span>
              </div>
            </div>

            <!-- Email -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Email</label>
              <div class="col-sm-8">
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
                <span class="text-danger"><?= $email_error; ?></span>
              </div>
            </div>

            <!-- Phone -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Phone</label>
              <div class="col-sm-8">
                <input class="form-control" type="tel" name="phone" value="<?= htmlspecialchars($phone); ?>" required>
                <span class="text-danger"><?= $phone_error; ?></span>
              </div>
            </div>

            <!-- Address -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Address</label>
              <div class="col-sm-8">
                <input class="form-control" type="text" name="address" value="<?= htmlspecialchars($address); ?>"
                  required>
                <span class="text-danger"><?= $address_error; ?></span>
              </div>
            </div>

            <!-- Password -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Password</label>
              <div class="col-sm-8 position-relative">
                <input class="form-control pe-5" type="password" name="password" id="password" required>
                <i id="password-toggle" class='bx bxs-lock-open-alt' style="position: absolute; right: 21px; top: 23%; transform: translateY(-50%);
                color: #000000; cursor: pointer; font-size: 1.2rem;"></i>
                <small class="form-text text-muted">Password must be at least 8 characters and include uppercase,
                  lowercase, number, and special character.</small>
                <span class="text-danger"><?= $password_error; ?></span>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Confirm Password</label>
              <div class="col-sm-8 position-relative">
                <input class="form-control pe-5" type="password" name="confirm_password" required>
                <i id="confirm-password-toggle" class='bx bxs-lock-open-alt' style="position: absolute; right: 21px; top: 50%; transform: translateY(-50%);
                color: #000000; cursor: pointer; font-size: 1.2rem;"></i>
                <span class="text-danger"><?= $confirm_password_error; ?></span>
              </div>
            </div>

            <!-- Submit buttons -->
            <div class="row mb-3">
              <div class="col d-grid">
                <button type="submit" class="btn btn-outline-dark fw-bold">Register</button>
              </div>
              <div class="col d-grid">
                <a href="../access/login.php" class="btn btn-outline-dark fw-bold">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  const passwordInput = document.getElementById("password");
  const toggleIcon = document.getElementById("password-toggle");

  toggleIcon.addEventListener("click", function() {
    const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
    this.classList.toggle("bxs-lock");
    this.classList.toggle("bxs-lock-open-alt");
  });

  const confirmPasswordInput = document.querySelector("input[name='confirm_password']");
  const confirmToggleIcon = document.getElementById("confirm-password-toggle");

  confirmToggleIcon.addEventListener("click", function() {
    const type = confirmPasswordInput.getAttribute("type") === "password" ? "text" : "password";
    confirmPasswordInput.setAttribute("type", type);
    this.classList.toggle("bxs-lock");
    this.classList.toggle("bxs-lock-open-alt");
  });
  </script>
</body>

</html>