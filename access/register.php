<?php
// Initialize variables
$first_name = "";
$last_name = "";
$email = "";
$phone = "";
$address = "";
$role = "staff"; // default role

$first_name_error = "";
$last_name_error = "";
$email_error = "";
$phone_error = "";
$address_error = "";
$password_error = "";
$confirm_password_error = "";

$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'staff';

    // Validate input
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

    // If no errors, insert into DB
    if (!$error) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');

        $stmt = $dbConnection->prepare("INSERT INTO users (first_name, last_name, email, phone, address, password, created_at, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $first_name, $last_name, $email, $phone, $address, $password_hash, $created_at, $role);

        if ($stmt->execute()) {
            $insert_id = $dbConnection->insert_id;
            session_start();
            $_SESSION["id"] = $insert_id;
            $_SESSION["first_name"] = $first_name;
            $_SESSION["last_name"] = $last_name;
            $_SESSION["email"] = $email;
            $_SESSION["phone"] = $phone;
            $_SESSION["address"] = $address;
            $_SESSION["created_at"] = $created_at;
            $_SESSION["role"] = $role;

            header("Location: login.php");
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
  <title>FINAL PROJECT - Register</title>
  <link rel="icon" href="/image/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <?php
            function inputRow($label, $name, $value, $error, $type = 'text') {
              echo '
              <div class="row mb-3">
                <label class="col-sm-4 col-form-label text-dark fw-bold">' . $label . '</label>
                <div class="col-sm-8">
                  <input class="form-control" type="' . $type . '" name="' . $name . '" value="' . htmlspecialchars($value) . '">
                  <span class="text-danger">' . $error . '</span>
                </div>
              </div>';
            }

            inputRow("First Name", "first_name", $first_name, $first_name_error);
            inputRow("Last Name", "last_name", $last_name, $last_name_error);
            inputRow("Email", "email", $email, $email_error);
            inputRow("Phone", "phone", $phone, $phone_error);
            inputRow("Address", "address", $address, $address_error);
            ?>
            <div class="row mb-3">
              <label class="col-sm-4 col-form-label text-dark fw-bold">Role</label>
              <div class="col-sm-8">
                <select class="form-control" name="role">
                  <option value="staff" <?= $role == 'staff' ? 'selected' : '' ?>>Staff</option>
                  <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
              </div>
            </div>
            <?php
            inputRow("Password", "password", "", $password_error, "password");
            inputRow("Confirm Password", "confirm_password", "", $confirm_password_error, "password");
            ?>
            <div class="row mb-3">
              <div class="col d-grid">
                <button type="submit" class="btn btn-outline-primary fw-bold">Register</button>
              </div>
              <div class="col d-grid">
                <a href="/login.php" class="btn btn-outline-secondary fw-bold">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>