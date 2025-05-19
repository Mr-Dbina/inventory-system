<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM products WHERE id = $id";
    mysqli_query($conn, $deleteQuery);
    header("Location: inventory.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    
    $updateQuery = "UPDATE products SET 
                    product_name = '$product_name',
                    category = '$category',
                    quantity = $quantity,
                    price = $price,
                    updated_at = NOW()
                    WHERE id = $id";
                    
    mysqli_query($conn, $updateQuery);
    header("Location: inventory.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    

    $sku = strtoupper(substr(str_replace(' ', '', $product_name), 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999);
    

    $image_path = "default_product.jpg";
    

    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }
    
    $insertQuery = "INSERT INTO products (product_name, category, quantity, price, image_path, sku, created_at, updated_at) 
                    VALUES ('$product_name', '$category', $quantity, $price, '$image_path', '$sku', NOW(), NOW())";
                    
    mysqli_query($conn, $insertQuery);
    header("Location: inventory.php");
    exit();
}


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

<?php include '../partials/sidebar.php'; ?>
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
        <button class="btn-add" onclick="openAddForm()">
          <i class='bx bx-plus'></i> Add New Product
        </button>
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
                <button class="btn-edit" onclick="openEditForm(<?= $row['id']; ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES); ?>',
                   '<?= htmlspecialchars($row['category']); ?>', <?= $row['quantity']; ?>, <?= $row['price']; ?>)">
                  <i class='bx bxs-edit'></i>
                </button>
                <a href="?delete=<?= $row['id']; ?>" class="btn-delete"
                  onclick="return confirm('Are you sure you want to delete this product?');">
                  <i class='bx bxs-trash'></i>
                </a>
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
        <h2>Edit Product</h2>
        <button class="close-btn" onclick="closeEditForm()">&times;</button>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-group">
          <label for="product_name">Product Name</label>
          <input type="text" id="edit_product_name" name="product_name" required>
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="edit_category" name="category">
            <?php 
            // Reset category result pointer
            mysqli_data_seek($categoryResult, 0);
            while($cat = mysqli_fetch_assoc($categoryResult)): 
            ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
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


  <div class="modal-overlay" id="addFormModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Add New Product</h2>
        <button class="close-btn" onclick="closeAddForm()">&times;</button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
          <label for="product_name">Product Name</label>
          <input type="text" id="add_product_name" name="product_name" required>
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="add_category" name="category">
            <?php 
            // Reset category result pointer
            mysqli_data_seek($categoryResult, 0);
            while($cat = mysqli_fetch_assoc($categoryResult)): 
            ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="quantity">Quantity</label>
          <input type="number" id="add_quantity" name="quantity" min="0" value="0" required>
        </div>
        <div class="form-group">
          <label for="price">Price (₱)</label>
          <input type="number" id="add_price" name="price" min="0" step="0" value="0" required>
        </div>
        <div class="form-group">
          <label for="image">Product Image (Optional)</label>
          <input type="file" id="add_image" name="image" accept="image/*">
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeAddForm()">Cancel</button>
          <button type="submit" class="btn-save" name="add_product">Add Product</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  function openEditForm(id, name, category, quantity, price) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_category').value = category;
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
      window.location.href = 'inventory.php';
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
    if (event.target == document.getElementById('addFormModal')) {
      closeAddForm();
    }
  }
  </script>
</body>

</html>