<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: ../access/login.php");
    exit;
}

// Function to get database connection
function getDatabaseConnection() {
    $servername = "localhost";
    $username = "root"; // Update with your DB username
    $password = ""; // Update with your DB password
    $dbname = "lelelemon";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Handle Edit Profile Form Submission
if (isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $error = false;
    $error_message = "";
    
    // Validate inputs
    if (empty($fullname)) {
        $error = true;
        $error_message = "Full Name is required";
    }
    
    if (empty($email)) {
        $error = true;
        $error_message = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $error_message = "Invalid email format";
    }
    
    if (empty($phone)) {
        $error = true;
        $error_message = "Phone number is required";
    }
    
    if (empty($address)) {
        $error = true;
        $error_message = "Address is required";
    }
    
    if (!$error) {
        $conn = getDatabaseConnection();
        
        // Check if email is already used by another user
        if ($email !== $_SESSION['email']) {
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $_SESSION['id']);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $error = true;
                $error_message = "Email is already in use by another account";
            }
            $check_email->close();
        }
        
        if (!$error) {
            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, date_of_birth = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("sssssi",  $email, $phone, $address, $_SESSION['id']);
            
            if ($stmt->execute()) {
                // Update session variables
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $_SESSION['address'] = $address;
                
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
            
            $stmt->close();
        }
        
        $conn->close();
    }
}

// Handle Change Password Form Submission
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $error = false;
    $password_error = "";
    
    // Validate password requirements
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = true;
        $password_error = "All password fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error = true;
        $password_error = "New passwords do not match";
    } elseif (strlen($new_password) < 8) {
        $error = true;
        $password_error = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = true;
        $password_error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $error = true;
        $password_error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = true;
        $password_error = "Password must contain at least one number";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error = true;
        $password_error = "Password must contain at least one special character";
    }
    
    if (!$error) {
        $conn = getDatabaseConnection();
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        $stmt->close();
        
        if (!password_verify($current_password, $stored_password)) {
            $error = true;
            $password_error = "Current password is incorrect";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['id']);
            
            if ($update_stmt->execute()) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Error changing password: " . $conn->error;
            }
            
            $update_stmt->close();
        }
        
        $conn->close();
    }
}
?>
<?php include '../partials/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LELELEMON - User Profile</title>
  <link rel="stylesheet" href="../style/inventory.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
  .profile-sidebar {
    background-color: #58b0e0;
    border-radius: 12px;
    overflow: hidden;
  }

  .profile-card {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .avatar-container {
    text-align: center;
    padding: 20px 0;
    position: relative;
  }

  .avatar {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0 auto;
  }

  .headings {
    text-align: center;
    padding: 10px 0;
  }

  .contact-info {
    padding: 0 20px 20px;
  }

  .contact-info ul {
    list-style: none;
    padding: 0;
  }

  .contact-info li {
    padding: 8px 0;
    border-bottom: 1px dotted #434955;
    display: flex;
    align-items: center;
  }

  .contact-info li:last-child {
    border-bottom: none;
  }

  .contact-info i {
    margin-right: 10px;
    color: #58b0e0;
  }

  .footer-bar {
    height: 20px;
    background-color: #58b0e0;
  }

  .nav-pills .nav-link.active {
    background-color: #58b0e0;
  }

  .tab-pane {
    padding: 20px;
  }
  </style>
</head>

<body>
  <div class="container py-5">
    <div class="row">
      <!-- Profile Sidebar -->
      <div class="col-md-4 mb-4">
        <div class="profile-card">
          <div class="avatar-container">
            <div class="avatar">
              <svg id="avatar" viewBox="0 0 61.8 61.8" xmlns="http://www.w3.org/2000/svg">
                <g data-name="Layer 2">
                  <g data-name="—ÎÓÈ 1">
                    <path d="M31.129 8.432c21.281 0 12.987 35.266 0 35.266-12.266 0-21.281-35.266 0-35.266z"
                      fill-rule="evenodd" fill="#ffe8be"></path>
                    <circle fill="#58b0e0" r="30.9" cy="30.9" cx="30.9"></circle>
                    <path
                      d="M45.487 19.987l-29.173.175s1.048 16.148-2.619 21.21h35.701c-.92-1.35-3.353-1.785-3.909-21.385z"
                      fill-rule="evenodd" fill="#60350a"></path>
                    <path
                      d="M18.135 45.599l7.206-3.187 11.55-.3 7.42 3.897-5.357 11.215-7.613 4.088-7.875-4.35-5.331-11.363z"
                      fill-rule="evenodd" fill="#d5e1ed"></path>
                    <path d="M24.744 38.68l12.931.084v8.949l-12.931-.085V38.68z" fill-rule="evenodd" fill="#f9dca4">
                    </path>
                    <path opacity=".11"
                      d="M37.677 38.778v3.58a9.168 9.168 0 0 1-.04 1.226 6.898 6.898 0 0 1-.313 1.327c-4.37 4.165-11.379.78-12.49-6.333z"
                      fill-rule="evenodd"></path>
                    <path
                      d="M52.797 52.701a30.896 30.896 0 0 1-44.08-.293l1.221-3.098 9.103-4.122c3.262 5.98 6.81 11.524 12.317 15.455A45.397 45.397 0 0 0 43.2 45.483l8.144 3.853z"
                      fill-rule="evenodd" fill="#434955"></path>
                    <path
                      d="M19.11 24.183c-2.958 1.29-.442 7.41 1.42 7.383a30.842 30.842 0 01-1.42-7.383zM43.507 24.182c2.96 1.292.443 7.411-1.419 7.384a30.832 30.832 0 001.419-7.384z"
                      fill-rule="evenodd" fill="#f9dca4"></path>
                    <path
                      d="M31.114 8.666c8.722 0 12.377 6.2 12.601 13.367.307 9.81-5.675 21.43-12.6 21.43-6.56 0-12.706-12.018-12.333-21.928.26-6.953 3.814-12.869 12.332-12.869z"
                      fill-rule="evenodd" fill="#ffe8be"></path>
                    <path
                      d="M33.399 24.983a7.536 7.536 0 0 1 5.223-.993h.005c5.154.63 5.234 2.232 4.733 2.601a2.885 2.885 0 0 0-.785 1.022 6.566 6.566 0 0 1-1.052 2.922 5.175 5.175 0 0 1-3.464 2.312c-.168.027-.34.048-.516.058a4.345 4.345 0 0 1-3.65-1.554 8.33 8.33 0 0 1-1.478-2.53v.003s-.797-1.636-2.072-.114a8.446 8.446 0 0 1-1.52 2.64 4.347 4.347 0 0 1-3.651 1.555 5.242 5.242 0 0 1-.516-.058 5.176 5.176 0 0 1-3.464-2.312 6.568 6.568 0 0 1-1.052-2.921 2.75 2.75 0 0 0-.77-1.023c-.5-.37-.425-1.973 4.729-2.603h.002a7.545 7.545 0 0 1 5.24 1.01l-.001-.001.003.002.215.131a3.93 3.93 0 0 0 3.842-.148l-.001.001zm-4.672.638a6.638 6.638 0 0 0-6.157-.253c-1.511.686-1.972 1.17-1.386 3.163a5.617 5.617 0 0 0 .712 1.532 4.204 4.204 0 0 0 3.326 1.995 3.536 3.536 0 0 0 2.966-1.272 7.597 7.597 0 0 0 1.36-2.37c.679-1.78.862-1.863-.82-2.795zm10.947-.45a6.727 6.727 0 0 0-5.886.565c-1.538.911-1.258 1.063-.578 2.79a7.476 7.476 0 0 0 1.316 2.26 3.536 3.536 0 0 0 2.967 1.272 4.228 4.228 0 0 0 .43-.048 4.34 4.34 0 0 0 2.896-1.947 5.593 5.593 0 0 0 .684-1.44c.702-2.25.076-2.751-1.828-3.451z"
                      fill-rule="evenodd" fill="#464449"></path>
                    <path
                      d="M17.89 25.608c0-.638.984-.886 1.598 2.943a22.164 22.164 0 0 0 .956-4.813c1.162.225 2.278 2.848 1.927 5.148 3.166-.777 11.303-5.687 13.949-12.324 6.772 3.901 6.735 12.094 6.735 12.094s.358-1.9.558-3.516c.066-.538.293-.733.798-.213C48.073 17.343 42.3 5.75 31.297 5.57c-15.108-.246-17.03 16.114-13.406 20.039z"
                      fill-rule="evenodd" fill="#8a5c42"></path>
                    <path d="M24.765 42.431a14.125 14.125 0 0 0 6.463 5.236l-4.208 6.144-5.917-9.78z"
                      fill-rule="evenodd" fill="#fff"></path>
                    <path d="M37.682 42.431a14.126 14.126 0 0 1-6.463 5.236l4.209 6.144 5.953-9.668z"
                      fill-rule="evenodd" fill="#fff"></path>
                    <circle fill="#434955" r=".839" cy="52.562" cx="31.223"></circle>
                    <circle fill="#434955" r=".839" cy="56.291" cx="31.223"></circle>
                    <path
                      d="M41.997 24.737c1.784.712 1.719 1.581 1.367 1.841a2.886 2.886 0 0 0-.785 1.022 6.618 6.618 0 0 1-.582 2.086v-4.949zm-21.469 4.479a6.619 6.619 0 0 1-.384-1.615 2.748 2.748 0 0 0-.77-1.023c-.337-.249-.413-1.06 1.154-1.754z"
                      fill-rule="evenodd" fill="#464449"></path>
                  </g>
                </g>
              </svg>
            </div>
          </div>
          <div class="headings">
            <p class="fs-4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></p>
            <p class="text-muted">User</p>
          </div>
          <div class="contact-info">
            <ul>
              <li>
                <i class='bx bxs-phone'></i>
                <span><?php echo htmlspecialchars($_SESSION["phone"]); ?></span>
              </li>
              <li>
                <i class='bx bxs-envelope'></i>
                <span><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
              </li>
              <li>
                <i class='bx bxs-calendar'></i>
                <span><?php echo htmlspecialchars($_SESSION["date_of_birth"]); ?></span>
              </li>
              <li>
                <i class='bx bxs-map'></i>
                <span><?php echo htmlspecialchars($_SESSION["address"]); ?></span>
              </li>
            </ul>
          </div>
          <div class="footer-bar"></div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <ul class="nav nav-pills" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                  type="button" role="tab" aria-controls="profile" aria-selected="true">Edit Profile</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button"
                  role="tab" aria-controls="password" aria-selected="false">Change Password</button>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="myTabContent">
              <!-- Edit Profile Tab -->
              <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <?php if (isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="post">

                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                      value="<?php echo htmlspecialchars($_SESSION["email"]); ?>" required>
                  </div>

                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                      value="<?php echo htmlspecialchars($_SESSION["phone"]); ?>" required>
                  </div>

                  <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"
                      required><?php echo htmlspecialchars($_SESSION["address"]); ?></textarea>
                  </div>

                  <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
              </div>

              <!-- Change Password Tab -->
              <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <?php if (isset($password_error) && !empty($password_error)): ?>
                <div class="alert alert-danger"><?php echo $password_error; ?></div>
                <?php endif; ?>

                <?php if (isset($password_success)): ?>
                <div class="alert alert-success"><?php echo $password_success; ?></div>
                <?php endif; ?>

                <form method="post">
                  <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="current_password" name="current_password"
                        required>
                      <button class="btn btn-outline-secondary toggle-password" type="button"
                        data-target="current_password">
                        <i class='bx bxs-hide'></i>
                      </button>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="new_password" name="new_password" required>
                      <button class="btn btn-outline-secondary toggle-password" type="button"
                        data-target="new_password">
                        <i class='bx bxs-hide'></i>
                      </button>
                    </div>
                    <div class="form-text">
                      Password must be at least 8 characters and include uppercase, lowercase, number, and special
                      character.
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        required>
                      <button class="btn btn-outline-secondary toggle-password" type="button"
                        data-target="confirm_password">
                        <i class='bx bxs-hide'></i>
                      </button>
                    </div>
                  </div>

                  <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Toggle password visibility
  document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const passwordInput = document.getElementById(targetId);
      const icon = this.querySelector('i');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('bxs-hide', 'bxs-show');
      } else {
        passwordInput.type = 'password';
        icon.classList.replace('bxs-show', 'bxs-hide');
      }
    });
  });
  </script>
</body>

</html>