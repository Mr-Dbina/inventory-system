<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    $updateQuery = "UPDATE products SET 
                    quantity = $quantity,
                    price = $price,
                    updated_at = NOW()
                    WHERE id = $id";

    mysqli_query($conn, $updateQuery);
    header("Location: inventory_staff.php");
    exit();
}

// Filtering conditions
$conditions = [];

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(product_name LIKE '%$search%' OR category LIKE '%$search%' OR sku LIKE '%$search%')";
}

if (!empty($_GET['category']) && $_GET['category'] != 'All Categories') {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $conditions[] = "category = '$category'";
}

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

if (!empty($_GET['price_min']) || !empty($_GET['price_max'])) {
    if (!empty($_GET['price_min']) && !empty($_GET['price_max'])) {
        $min = (float)$_GET['price_min'];
        $max = (float)$_GET['price_max'];
        $conditions[] = "price BETWEEN $min AND $max";
    } elseif (!empty($_GET['price_min'])) {
        $min = (float)$_GET['price_min'];
        $conditions[] = "price >= $min";
    } elseif (!empty($_GET['price_max'])) {
        $max = (float)$_GET['price_max'];
        $conditions[] = "price <= $max";
    }
}

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'updated_at';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'ASC';

$allowed_sort_fields = ['id', 'product_name', 'category', 'quantity', 'price', 'updated_at'];
if (!in_array($sort, $allowed_sort_fields)) {
    $sort = 'updated_at';
}
if ($direction != 'ASC' && $direction != 'DESC') {
    $direction = 'ASC';
}

$query = "SELECT * FROM products $where ORDER BY $sort $direction";
$result = mysqli_query($conn, $query);

$categoryQuery = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$categoryResult = mysqli_query($conn, $categoryQuery);
?>

<?php include '../partials/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Inventory Management</title>
  <link rel="stylesheet" href="../style/inventory.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text">INVENTORY</h1>
      </div>
      <!-- Enhanced filter form with more options -->
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

        <div class="filter-group">
          <label>Price Range (₱)</label>
          <div class="price-range">
            <input type="number" name="price_min" placeholder="Min" min="0" step="0.01"
              value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>">
            <span>to</span>
            <input type="number" name="price_max" placeholder="Max" min="0" step="0.01"
              value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>">
          </div>
        </div>

        <div class="filter-group">
          <label for="sort">Sort By</label>
          <select name="sort" id="sort">
            <option value="updated_at" <?= ($_GET['sort'] ?? 'updated_at') == 'updated_at' ? 'selected' : '' ?>>Last
              Updated</option>
            <option value="product_name" <?= ($_GET['sort'] ?? '') == 'product_name' ? 'selected' : '' ?>>Product Name
            </option>
            <option value="price" <?= ($_GET['sort'] ?? '') == 'price' ? 'selected' : '' ?>>Price</option>
            <option value="quantity" <?= ($_GET['sort'] ?? '') == 'quantity' ? 'selected' : '' ?>>Quantity</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="direction">Order</label>
          <select name="direction" id="direction">
            <option value="ASC" <?= ($_GET['direction'] ?? 'ASC') == 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="DESC" <?= ($_GET['direction'] ?? '') == 'DESC' ? 'selected' : '' ?>>Descending</option>
          </select>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn-filter">Apply Filters</button>
          <button type="button" class="btn-reset" onclick="resetFilters()">Reset</button>
        </div>
      </form>

      <!-- Results indicator -->
      <div style="margin-bottom: 15px; font-size: 14px; color: #555;">
        <?php 
        $count = mysqli_num_rows($result);
        echo "Showing {$count} product" . ($count != 1 ? "s" : "");
        
        // Show applied filters
        $appliedFilters = [];
        if (!empty($_GET['search'])) $appliedFilters[] = "Search: " . htmlspecialchars($_GET['search']);
        if (!empty($_GET['category']) && $_GET['category'] != 'All Categories') $appliedFilters[] = "Category: " . htmlspecialchars($_GET['category']);
        if (!empty($_GET['stock']) && $_GET['stock'] != 'All Stock Levels') $appliedFilters[] = "Stock: " . htmlspecialchars($_GET['stock']);
        if (!empty($_GET['price_min'])) $appliedFilters[] = "Min Price: ₱" . htmlspecialchars($_GET['price_min']);
        if (!empty($_GET['price_max'])) $appliedFilters[] = "Max Price: ₱" . htmlspecialchars($_GET['price_max']);
        
        if (!empty($appliedFilters)) {
          echo " (Filtered by: " . implode(", ", $appliedFilters) . ")";
        }
        ?>
      </div>
      <!-- Filter form code remains the same -->

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Current Stock</th>
            <th>Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><img src="<?= $row['image_path']; ?>" width="40"> <?= htmlspecialchars($row['product_name']); ?></td>
            <td><span class="badge"><?= htmlspecialchars($row['category']); ?></span></td>
            <td><?= htmlspecialchars($row['sku']); ?></td>
            <td>
              <?php
              $stock = $row['quantity'];
              $level = $stock < 30 ? 'low' : ($stock < 70 ? 'normal' : 'high');
              echo "<span class='$level'>{$stock} units - " . ucfirst($level) . "</span>";
              ?>
              <div class="stock-bar">
                <span class="progress-<?= $level ?>" style="width: <?= min(100, ($stock / 100) * 100) ?>%"></span>
              </div>
            </td>
            <td>₱<?= number_format($row['price'], 2); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn-edit"
                  onclick="openEditForm(<?= $row['id']; ?>, <?= $row['quantity']; ?>, <?= $row['price']; ?>)">
                  <i class='bx bxs-edit'></i> Edit
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Edit Product Modal -->
  <div class="modal-overlay" id="editFormModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Edit Product</h2>
        <button class="close-btn" onclick="closeEditForm()">&times;</button>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-group">
          <label for="quantity">Quantity</label>
          <input type="number" id="edit_quantity" name="quantity" min="0" required>
        </div>
        <div class="form-group">
          <label for="price">Price (₱)</label>
          <input type="number" id="edit_price" name="price" min="0" step="0.01" required>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeEditForm()">Cancel</button>
          <button type="submit" class="btn-save" name="update_product">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openEditForm(id, quantity, price) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_price').value = price;
    document.getElementById('editFormModal').style.display = 'flex';
  }

  function closeEditForm() {
    document.getElementById('editFormModal').style.display = 'none';
  }

  // Functions for Add Modal
  function openAddForm() {
    document.getElementById('addFormModal').style.display = 'flex';
  }

  function closeAddForm() {
    document.getElementById('addFormModal').style.display = 'none';
  }

  function resetFilters() {
    console.log("Reset clicked - redirecting to inventory_staff.php");
    window.location.href = 'inventory_staff.php';
  }
  // Function to reset filters
  function resetFilters() {
    window.location.href = 'inventory_staff.php';
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
    if (event.target == document.getElementById('addFormModal')) {
      closeAddForm();
    }
  }
  </script>
</body>

</html>