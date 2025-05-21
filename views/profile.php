<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if user is not logged in
if (!isset($_SESSION["id"])) {
    header("Location: ../access/login.php");
    exit;
}

// Function to get database connection
function getDatabaseConnection() {
    $servername = "localhost";
    $username = "root"; // Update with your DB username
    $password = "";     // Update with your DB password
    $dbname = "lelelemon_db";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Get user details from DB
$conn = getDatabaseConnection();
$user_id = $_SESSION['id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['date_of_birth'] = $user['date_of_birth'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['address'] = $user['address'];
    $_SESSION['position'] = $user['position'] ?? 'staff';
    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.jpg';
}

$conn->close();

// Handle profile image upload
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/profile/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = $_SESSION['id'] . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $upload_ok = 1;

    // Check file type
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        $image_error = "Only JPG, JPEG & PNG files are allowed.";
        $upload_ok = 0;
    }
    
    // Check file size (limit to 5MB)
    if ($_FILES["profile_image"]["size"] > 5000000) {
        $image_error = "File is too large. Maximum size is 5MB.";
        $upload_ok = 0;
    }
    
    if ($upload_ok == 1) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Update database with new image path
            $conn = getDatabaseConnection();
            $image_path = $new_filename;
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $image_path, $_SESSION['id']);
            
            if ($stmt->execute()) {
                $_SESSION['profile_image'] = $image_path;
                $image_success = "Profile image updated successfully!";
            } else {
                $image_error = "Error updating profile image in database.";
            }
            
            $stmt->close();
            $conn->close();
        } else {
            $image_error = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle Edit Profile Form Submission
if (isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $position = isset($_POST['position']) ? trim($_POST['position']) : 'staff';

    $error = false;
    $error_message = "";

    // Validation
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

    if (!$error) {
        $conn = getDatabaseConnection();

        // Check for duplicate email
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
            $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, address = ?, position = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $email, $phone, $address, $position, $_SESSION['id']);

            if ($stmt->execute()) {
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $_SESSION['address'] = $address;
                $_SESSION['position'] = $position;
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

<?php include '../partials/staff.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LELELEMON - User Profile</title>
  <link rel="stylesheet" href="../style/profile.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
  body {
    background-color: #f6f1f1;
  }
  </style>
</head>

<body>
  <div class="main-content transition-enabled">
    <div class="container ms-0" style="max-width: 1000px;">

      <div class="row g-4">
        <div class="col-lg-4 col-md-12">
          <div class="profile-card">
            <div class="headings">
              <!-- Profile Image with Camera Icon -->
              <div class="profile-image-container" id="profile-image-container">
                <img
                  src="../uploads/profile/<?php echo htmlspecialchars($_SESSION["profile_image"] ?? "default.jpg"); ?>"
                  alt="Profile Image" class="profile-image">
                <div class="camera-icon">
                  <i class='bx bx-camera'></i>
                </div>

                <!-- Profile Options -->
                <div class="profile-options" id="profile-options">
                  <div class="profile-option" onclick="document.getElementById('profile_image_upload').click();">
                    <i class='bx bx-images'></i> Choose Profile
                  </div>
                  <div class="profile-option" onclick="viewProfilePicture()">
                    <i class='bx bx-user-circle'></i> See Profile
                  </div>
                </div>


                <form id="image_upload_form" method="post" enctype="multipart/form-data" style="display:none;">
                  <input type="file" id="profile_image_upload" name="profile_image"
                    onchange="document.getElementById('image_upload_form').submit();">
                  <input type="hidden" name="upload_image" value="1">
                </form>
              </div>

              <div class="user-info-header">
                <p class="fs-4 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION["fullname"] ?? "User"); ?>
                </p>
              </div>
            </div>
            <div class="contact-info">
              <ul>
                <li>
                  <i class='bx bxs-phone'></i>
                  <span><?php echo htmlspecialchars($_SESSION["phone"] ?? "Not provided"); ?></span>
                </li>
                <li>
                  <i class='bx bxs-envelope'></i>
                  <span><?php echo htmlspecialchars($_SESSION["email"] ?? "Not provided"); ?></span>
                </li>
                <li>
                  <i class='bx bxs-calendar'></i>
                  <span><?php echo htmlspecialchars($_SESSION["date_of_birth"] ?? "Not provided"); ?></span>
                </li>
                <li>
                  <i class='bx bxs-map'></i>
                  <span><?php echo htmlspecialchars($_SESSION["address"] ?? "Not provided"); ?></span>
                </li>
              </ul>
            </div>
            <div class="footer-bar"></div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8 col-md-12">
          <div class="card">
            <div class="card-header">
              <ul class="nav nav-pills" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                    type="button" role="tab" aria-controls="profile" aria-selected="true"
                    style="background-color: #FEDF05; color: #000;">Edit Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                    type="button" role="tab" aria-controls="password" aria-selected="false">
                    Change Password
                  </button>
                </li>

              </ul>
            </div>
            <div class="card-body">
              <!-- Show image upload messages -->
              <?php if (isset($image_error) && !empty($image_error)): ?>
              <div class="alert alert-danger"><?php echo $image_error; ?></div>
              <?php endif; ?>

              <?php if (isset($image_success)): ?>
              <div class="alert alert-success" style="background-color: #FFFD95; color: #000" ;>
                <?php echo $image_success; ?></div>
              <?php endif; ?>

              <div class=" tab-content" id="myTabContent">
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <?php if (isset($error_message) && !empty($error_message)): ?>
                  <div class="alert alert-danger"><?php echo $error_message; ?></div>
                  <?php endif; ?>

                  <?php if (isset($success_message)): ?>
                  <div class="alert alert-success" style="background-color: #FFFD95; color: #000;">
                    <?php echo $success_message; ?></div>
                  <?php endif; ?>


                  <form method="post">
                    <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($_SESSION["email"] ?? ""); ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="phone" class="form-label">Phone</label>
                      <input type="tel" class="form-control" id="phone" name="phone"
                        value="<?php echo htmlspecialchars($_SESSION["phone"] ?? ""); ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="address" class="form-label">Address</label>
                      <textarea class="form-control" id="address" name="address" rows="3"
                        required><?php echo htmlspecialchars($_SESSION["address"] ?? ""); ?></textarea>
                    </div>
                    <button type="submit" name="update_profile"
                      style="background-color: #FEDF05; border-color: #FEDF05; color: #000;" class="btn">Update
                      Profile</button>

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

                    <button type="submit" name="change_password"
                      style="background-color: #FEDF05; border-color: #FEDF05; color: #000;" class="btn">Change
                      Password</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Picture Viewer Modal -->
  <div class="profile-picture-modal" id="profile-picture-modal">
    <div class="profile-picture-content">
      <img src="" alt="Full Profile" class="full-profile-image" id="full-profile-image">
      <button class="close-button" onclick="closeProfilePicture()">
        <i class='bx bx-x'></i>
      </button>
    </div>
  </div>


  <script>
  // Toggle profile options on click
  document.getElementById('profile-image-container').addEventListener('click', function(e) {
    const options = document.getElementById('profile-options');
    options.style.display = options.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
  });

  // Hide profile options when clicking elsewhere
  document.addEventListener('click', function() {
    document.getElementById('profile-options').style.display = 'none';
  });

  // Toggle password visibility
  document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
      const icon = this.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bx bxs-show';
      } else {
        input.type = 'password';
        icon.className = 'bx bxs-hide';
      }
    });
  });

  // Function to view profile picture in modal
  function viewProfilePicture() {
    const modal = document.getElementById('profile-picture-modal');
    const fullImage = document.getElementById('full-profile-image');
    const profileImage = document.querySelector('.profile-image');

    // Set the source of the full-size image to the profile image source
    fullImage.src = profileImage.src;

    // Show the modal
    modal.style.display = 'flex';

    // Hide profile options
    document.getElementById('profile-options').style.display = 'none';
  }

  // Function to close profile picture modal
  function closeProfilePicture() {
    document.getElementById('profile-picture-modal').style.display = 'none';
  }

  // Close modal when clicking outside the image
  document.getElementById('profile-picture-modal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeProfilePicture();
    }
  });
  // Add event listeners for profile tabs
  document.addEventListener('DOMContentLoaded', function() {
    const profileTab = document.getElementById('profile-tab');
    const passwordTab = document.getElementById('password-tab');

    // Function to handle tab switching
    function switchTab(tabElement, tabContentId) {
      // Hide all tab panes
      document.querySelectorAll('.tab-pane').forEach(function(pane) {
        pane.classList.remove('show', 'active');
      });

      // Show the selected tab pane
      const tabContent = document.getElementById(tabContentId);
      if (tabContent) {
        tabContent.classList.add('show', 'active');
      }

      // Update active state on tabs
      document.querySelectorAll('.nav-link').forEach(function(tab) {
        tab.classList.remove('active');
        tab.setAttribute('aria-selected', 'false');
      });

      // Set this tab as active
      tabElement.classList.add('active');
      tabElement.setAttribute('aria-selected', 'true');

      // Add the special styling for the Edit Profile tab if it's active
      if (tabElement.id === 'profile-tab') {
        tabElement.style.backgroundColor = '#FEDF05';
        tabElement.style.color = '#000';
      } else {
        profileTab.style.backgroundColor = '';
        profileTab.style.color = '';
      }
    }

    // Add click event listener to the Edit Profile tab button
    if (profileTab) {
      profileTab.addEventListener('click', function(event) {
        event.preventDefault();
        switchTab(this, 'profile');
      });
    }

    // Add click event listener to the Change Password tab button
    if (passwordTab) {
      passwordTab.addEventListener('click', function(event) {
        event.preventDefault();
        switchTab(this, 'password');
      });
    }
  });
  </script>
</body>

</html>