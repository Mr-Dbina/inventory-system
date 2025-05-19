<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

// Removed delete functionality

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    // Keeping only quantity update
    $quantity = (int)$_POST['quantity'];
    
    $updateQuery = "UPDATE products SET 
                    quantity = $quantity,
                    updated_at = NOW()
                    WHERE id = $id";
                    
    mysqli_query($conn, $updateQuery);
    header("Location: inventory.php");
    exit();
}

// Removed add_product functionality

// Initialize conditions array for filtering
$conditions = [];

// Add search condition
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(product_name LIKE '%$search%' OR category LIKE '%$search%' OR sku LIKE '%$search%')";
}

// Add category filter condition
if (!empty($_GET['category']) && $_GET['category'] != 'All Categories') {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $conditions[] = "category = '$category'";
}

// Add stock level filter condition
if (!empty($_GET['stock']) && $_GET['stock'] != 'All Stock Levels') {
    $stockLevel = $_GET['stock'];
    if ($stockLevel == 'Low') {
        $conditions[] = "quantity < 30";
    } elseif ($stockLevel == 'Normal') {
        $conditions[] = "quantity >= 30 AND quantity < 70";
    } elseif ($stockLevel == 'High') {
        $conditions[] = "quantity >= 70";
    }
}

// Construct WHERE clause if any conditions exist
$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Default sort by updated_at
$query = "SELECT * FROM products $where ORDER BY updated_at ASC";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Get all unique categories for the filter dropdown
$categoryQuery = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$categoryResult = mysqli_query($conn, $categoryQuery);
?>

<?php include '../partials/staff.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management</title>
  <link rel="stylesheet" href="../style/inventory.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text">INVENTORY</h1>
        <!-- Removed Add New Product button -->
      </div>

      <!-- Simplified filter form with removed price range and sorting options -->
      <form method="GET" class="filters" id="filterForm">
        <div class="filter-group">
          <label for="search">Search</label>
          <input type="text" id="search" name="search" placeholder="Search item..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <div class="filter-group">
          <label for="category">Category</label>
          <select name="category" id="category">
            <option value="All Categories">All Categories</option>
            <?php while($cat = mysqli_fetch_assoc($categoryResult)): ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>"
              <?= ($_GET['category'] ?? '') == $cat['category'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="filter-group">
          <label for="stock">Stock Level</label>
          <select name="stock" id="stock">
            <option value="All Stock Levels">All Stock Levels</option>
            <option value="Low" <?= ($_GET['stock'] ?? '') == 'Low' ? 'selected' : '' ?>>Low </option>
            <option value="Normal" <?= ($_GET['stock'] ?? '') == 'Normal' ? 'selected' : '' ?>>Normal </option>
            <option value="High" <?= ($_GET['stock'] ?? '') == 'High' ? 'selected' : '' ?>>High</option>
          </select>
        </div>

        <div class="filter-actions">
          <button type="button" class="btn-reset" onclick="resetFiltersWithAnimation()" title="ResetFilters">
            <i class='bx bx-refresh'></i>
          </button>
        </div>
      </form>

      <!-- Results indicator -->
      <div style="margin-bottom: 15px; font-size: 14px; color: #555;">
        <?php 
        $count = mysqli_num_rows($result);
        echo "Showing {$count} item" . ($count != 1 ? "s" : "");
        
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
            <th>Current Stock</th>
            <th>Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><img src="<?= $row['image_path']; ?>" width="40" padding="10px">
              <?= htmlspecialchars($row['product_name']); ?></td>
            <td><span class="badge"><?= htmlspecialchars($row['category']); ?></span></td>
            <td>
              <?php 
                  $stock = $row['quantity'];
                  $level = $stock < 30 ? 'low' : ($stock < 70 ? 'normal' : 'high');
                  echo "<span class='$level'>{$stock} units - " . ucfirst($level) . "</span>";
                ?>
              <div class="stock-bar"><span class="progress-<?= $level ?>"
                  style="width: <?= min(100, ($stock / 100) * 100) ?>%"></span></div>
            </td>
            <td>₱<?= number_format($row['price'], 2); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn-edit" onclick="openEditForm(<?= $row['id']; ?>, <?= $row['quantity']; ?>)">
                  <i class='bx bxs-edit'></i>
                </button>
                <!-- Deleted the delete button -->
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; padding: 20px;">No products found matching your criteria</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <div class="modal-overlay" id="editFormModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Update Stock Quantity</h2>
        <button class="close-btn" onclick="closeEditForm()">&times;</button>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="id" id="edit_id">
        <!-- Keeping only quantity field -->
        <div class="form-group">
          <label for="quantity">Quantity</label>
          <input type="number" id="edit_quantity" name="quantity" min="0" required>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeEditForm()">Cancel</button>
          <button type="submit" class="btn-save" name="update_product">Update Stock</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Removed Add Product Form Modal -->

  <script>
  // Modified function to only pass ID and quantity
  function openEditForm(id, quantity) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('editFormModal').style.display = 'flex';
  }

  function closeEditForm() {
    document.getElementById('editFormModal').style.display = 'none';
  }

  // Removed Add Form functions

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
      window.location.href = 'inventory_staff.php';
    }, 600); // Wait for animation to complete
  }

  // Make filters apply on change (optional - enable if you want instant filtering)
  document.querySelectorAll('#filterForm select').forEach(select => {
    select.addEventListener('change', function() {
      document.getElementById('filterForm').submit();
    });
  });

  // Close modal when clicking outside
  window.onclick = function(event) {
    if (event.target == document.getElementById('editFormModal')) {
      closeEditForm();
    }
    // Removed addFormModal check
  }
  </script>
</body>

</html>