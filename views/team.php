<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

// Add CSRF protection
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Build search/filter conditions
$conditions = [];

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(fullname LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%' OR address LIKE '%$search%')";
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$query = "SELECT id, fullname, date_of_birth, email, phone, address, created_at FROM users $where ORDER BY created_at DESC";

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
  <style>
  /* Additional styles for team page */
  .badge.user {
    background-color: #4CAF50;
  }

  .dob-column {
    white-space: nowrap;
  }
  </style>
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

        <a href="team.php" class="btn-reset">Reset</a>
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
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center;">No team members found</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

</body>

</html>