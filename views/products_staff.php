<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

// Add CSRF protection
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all categories for the dropdown
$categoryQuery = "SELECT DISTINCT category FROM items ORDER BY category";
$categoryResult = mysqli_query($conn, $categoryQuery);
if (!$categoryResult) {
    die("Category query failed: " . mysqli_error($conn));
}

// Build search/filter conditions
$conditions = [];

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(product_name LIKE '%$search%' OR category LIKE '%$search%')";
}

if (!empty($_GET['category']) && $_GET['category'] != 'All Categories') {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $conditions[] = "category = '$category'";
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$query = "SELECT * FROM items $where ORDER BY updated_at DESC"; // Changed to DESC for newest first

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<?php include '../partials/staff.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style/product.css">
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center; width: 95%; margin: 0 auto;">
        <h1 class="text">PRODUCTS</h1>
      </div>

      <form method="GET" class="filters" id="filterForm">
        <div class="filter-group">
          <input type="text" id="search" name="search" placeholder="Search item..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <div class="filter-group">
          <select name="category" id="category" onchange="document.getElementById('filterForm').submit();">
            <option value="All Categories">All Categories</option>
            <?php 
            // Reset the pointer to the beginning of result set
            mysqli_data_seek($categoryResult, 0);
            while($cat = mysqli_fetch_assoc($categoryResult)): ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>"
              <?= ($_GET['category'] ?? '') == $cat['category'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <button type="button" class="btn-reset" onclick="resetFiltersWithAnimation()" title="ResetFilters">
          <i class='bx bx-refresh'></i>
        </button>
      </form>
      <div style="margin-bottom: 15px; font-size: 14px; color: #555;">
        <?php 
        $count = mysqli_num_rows($result);
        echo "Showing {$count} product" . ($count != 1 ? "s" : "");
        
        // Show applied filters
        $appliedFilters = [];
        if (!empty($_GET['search'])) $appliedFilters[] = "Search: " . htmlspecialchars($_GET['search']);
        if (!empty($_GET['category']) && $_GET['category'] != 'All Categories') $appliedFilters[] = "Category: " . htmlspecialchars($_GET['category']);
        if (!empty($_GET['stock']) && $_GET['stock'] != 'All Stock Levels') $appliedFilters[] = "Stock: " . htmlspecialchars($_GET['stock']);
        
        if (!empty($appliedFilters)) {
          echo " (Filtered by: " . implode(", ", $appliedFilters) . ")";
        }
        ?>
      </div>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Category</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td>
              <?php if(!empty($row['image_path'])): ?>
              <img src="<?= htmlspecialchars($row['image_path']); ?>" width="40"
                style="margin-right: 10px; vertical-align: middle;">
              <?php endif; ?>
              <?= htmlspecialchars($row['product_name']); ?>
            </td>
            <td><span class="badge"><?= htmlspecialchars($row['category']); ?></span></td>
            <td>₱<?= number_format($row['price'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="4" style="text-align: center;">No products found</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <script>
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
      window.location.href = 'products_staff.php';
    }, 600); // Wait for animation to complete
  }
  </script>
</body>

</html>