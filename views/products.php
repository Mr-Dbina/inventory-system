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

// Handle product deletion with improved security
if (isset($_GET['delete']) && isset($_GET['csrf_token']) && $_GET['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_GET['delete']; // Cast to integer for security
    $deleteQuery = "DELETE FROM items WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: report.php");
    exit();
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = (float)$_POST['price'];
    
    $updateQuery = "UPDATE items SET 
                    product_name = ?,
                    category = ?,
                    price = ?,
                    updated_at = NOW()
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ssdi", $product_name, $category, $price, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: report.php");
    exit();
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = (float)$_POST['price'];
    
    $image_path = "default_product.jpg";
    
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $new_filename = uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }
    
    $insertQuery = "INSERT INTO items (product_name, category, price, image_path, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
                    
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssds", $product_name, $category, $price, $image_path);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: report.php");
    exit();
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

<?php include '../partials/sidebar.php'; ?>
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
        <button class="btn-add" onclick="openAddForm()">
          <i class='bx bx-plus'></i> Add New Product
        </button>
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

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Category</th>
            <th>Price</th>
            <th>Action</th>
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
            <td>
              <div class="action-buttons">
                <button class="btn-edit"
                  onclick="openEditForm(<?= $row['id']; ?>, '<?= htmlspecialchars(addslashes($row['product_name'])); ?>', '<?= htmlspecialchars($row['category']); ?>', <?= $row['price']; ?>)">
                  <i class='bx bxs-edit'></i>
                </button>
                <a href="?delete=<?= $row['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="btn-delete"
                  onclick="return confirm('Are you sure you want to delete this product?');">
                  <i class='bx bxs-trash'></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php else: ?>
          <tr>
            <td colspan="5" style="text-align: center;">No products found</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Edit Product Modal -->
  <div class="modal-overlay" id="editFormModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Edit Product</h2>
        <button type="button" class="close-btn" onclick="closeEditForm()">&times;</button>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id" id="edit_id">
        <div class="form-group">
          <label for="product_name">Product Name</label>
          <input type="text" id="edit_product_name" name="product_name" required>
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="edit_category" name="category">
            <?php 
            // Reset the pointer to the beginning of result set
            mysqli_data_seek($categoryResult, 0);
            while($cat = mysqli_fetch_assoc($categoryResult)): ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
            <!-- Add option to create a new category if needed -->
            <option value="new_category">Add New Category</option>
          </select>
        </div>
        <div class="form-group" id="new_category_group" style="display: none;">
          <label for="new_category">New Category Name</label>
          <input type="text" id="new_category_input" name="new_category">
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

  <!-- Add Product Modal -->
  <div class="modal-overlay" id="addFormModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Add New Product</h2>
        <button type="button" class="close-btn" onclick="closeAddForm()">&times;</button>
      </div>
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div class="form-group">
          <label for="product_name">Product Name</label>
          <input type="text" id="add_product_name" name="product_name" required>
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="add_category" name="category">
            <?php 
            // Reset the pointer to the beginning of result set
            mysqli_data_seek($categoryResult, 0);
            while($cat = mysqli_fetch_assoc($categoryResult)): ?>
            <option value="<?= htmlspecialchars($cat['category']) ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
            <?php endwhile; ?>
            <!-- Default options if no categories exist yet -->
            <option value="Solid">Solid</option>
            <option value="Liquid">Liquid</option>
            <option value="new_category">Add New Category</option>
          </select>
        </div>
        <div class="form-group" id="add_new_category_group" style="display: none;">
          <label for="add_new_category">New Category Name</label>
          <input type="text" id="add_new_category_input" name="new_category">
        </div>
        <div class="form-group">
          <label for="price">Price (₱)</label>
          <input type="number" id="add_price" name="price" min="0" step="0.01" value="0.00" required>
        </div>
        <div class="form-group">
          <label for="image">Product Image</label>
          <input type="file" id="add_image" name="image" accept="image/*">
          <small>Accepted formats: JPG, PNG, GIF (Max 2MB)</small>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeAddForm()">Cancel</button>
          <button type="submit" class="btn-save" name="add_product">Add Product</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  // Modal functions
  function openEditForm(id, name, category, price) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_price').value = price;
    document.getElementById('editFormModal').style.display = 'flex';
  }

  function closeEditForm() {
    document.getElementById('editFormModal').style.display = 'none';
  }

  function openAddForm() {
    document.getElementById('addFormModal').style.display = 'flex';
  }

  function closeAddForm() {
    document.getElementById('addFormModal').style.display = 'none';
  }

  // Handle modal closing by clicking outside
  window.onclick = function(event) {
    if (event.target == document.getElementById('editFormModal')) {
      closeEditForm();
    }
    if (event.target == document.getElementById('addFormModal')) {
      closeAddForm();
    }
  }

  // Handle new category options
  document.getElementById('edit_category').addEventListener('change', function() {
    if (this.value === 'new_category') {
      document.getElementById('new_category_group').style.display = 'block';
    } else {
      document.getElementById('new_category_group').style.display = 'none';
    }
  });

  document.getElementById('add_category').addEventListener('change', function() {
    if (this.value === 'new_category') {
      document.getElementById('add_new_category_group').style.display = 'block';
    } else {
      document.getElementById('add_new_category_group').style.display = 'none';
    }
  });
  // Function to reset filters
  function ResetFilters() {
    window.location.href = 'products.php';
  }

  // New function with animation for reset button
  function resetFiltersWithAnimation() {
    // Get the icon element
    const refreshIcon = document.querySelector('.btn-reset i');

    // Add rotation animation class
    refreshIcon.classList.add('rotate-animation');

    // Reset filters after animation completes
    setTimeout(function() {
      window.location.href = 'products.php';
    }, 600); // Wait for animation to complete
  }
  </script>
</body>

</html>