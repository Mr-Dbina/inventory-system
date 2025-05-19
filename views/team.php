<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

// Add CSRF protection
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['delete']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
  $id = (int)$_GET['delete']; // Cast to integer for security
  $deleteQuery = "DELETE FROM users WHERE id = ?";
  $stmt = mysqli_prepare($conn, $deleteQuery);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
  header("Location: team.php");
  exit();
}
// Build search/filter conditions
$conditions = [];

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(fullname LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%' OR address LIKE '%$search%')";
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$query = "SELECT id, fullname, date_of_birth, email, phone, address, created_at FROM users $where ORDER BY created_at ASC";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<?php include '../partials/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Management</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/product.css">
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: center; align-items: center; width: 95%; margin: 0 auto;">
        <h1 class="text">TEAM MEMBERS</h1>
      </div>

      <form method="GET" class="filters" id="filterForm">
        <div class="filter-group">
          <input type="text" id="search" name="search" placeholder="Search member..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <button type="button" class="btn-reset" onclick="resetFiltersWithAnimation()" title="ResetFilters">
          <i class='bx bx-refresh'></i>
        </button>
      </form>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Date of Birth</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['fullname']); ?></td>
            <td class="dob-column"><?= htmlspecialchars($row['date_of_birth']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= htmlspecialchars($row['phone']); ?></td>
            <td><?= htmlspecialchars($row['address']); ?></td>
            <td class="action-column">
              <a href="?delete=<?= $row['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="btn-delete"
                onclick="return confirm('Are you sure you want to delete this team member?');">
                <i class='bx bxs-trash'></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="7" style="text-align: center;">No team members found</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Add Edit Form Modal -->
  <div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="closeEditForm()">&times;</span>
      <h2>Edit Team Member</h2>
      <form id="editForm" method="POST" action="update_team.php">
        <input type="hidden" id="edit_id" name="id">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <div class="form-group">
          <label for="edit_fullname">Full Name</label>
          <input type="text" id="edit_fullname" name="fullname" required>
        </div>

        <div class="form-group">
          <label for="edit_dob">Date of Birth</label>
          <input type="date" id="edit_dob" name="date_of_birth" required>
        </div>

        <div class="form-group">
          <label for="edit_email">Email</label>
          <input type="email" id="edit_email" name="email" required>
        </div>

        <div class="form-group">
          <label for="edit_phone">Phone</label>
          <input type="text" id="edit_phone" name="phone" required>
        </div>

        <div class="form-group">
          <label for="edit_address">Address</label>
          <textarea id="edit_address" name="address" required></textarea>
        </div>

        <div class="form-group">
          <button type="button" class="btn-cancel" onclick="closeEditForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  // Function to open edit form modal
  function openEditForm(id, fullname, dob, email, phone, address) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_fullname').value = fullname;
    document.getElementById('edit_dob').value = dob;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;

    document.getElementById('editModal').style.display = 'block';
  }

  // Function to close edit form modal
  function closeEditForm() {
    document.getElementById('editModal').style.display = 'none';
  }

  // Close modal when clicking outside of it
  window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
      closeEditForm();
    }
  }
  // Function to reset filters
  function ResetFilters() {
    window.location.href = 'inventory.php';
  }

  // New function with animation for reset button
  function resetFiltersWithAnimation() {
    // Get the icon element
    const refreshIcon = document.querySelector('.btn-reset i');

    // Add rotation animation class
    refreshIcon.classList.add('rotate-animation');

    // Reset filters after animation completes
    setTimeout(function() {
      window.location.href = 'team.php';
    }, 600); // Wait for animation to complete
  }
  </script>
</body>

</html>