<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM products WHERE id = $id";
    mysqli_query($conn, $deleteQuery);
    header("Location: inventory.php");
    exit();
}

// Handle updating product
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

// Handle adding new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    
    // Generate a unique SKU
    $sku = strtoupper(substr(str_replace(' ', '', $product_name), 0, 3)) . '-' . date('Ymd') . '-' . rand(100, 999);
    
    // Default image path
    $image_path = "default_product.jpg";
    
    // Handle image upload if present
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

// Build dynamic SQL conditions based on filters
$conditions = [];

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(product_name LIKE '%$search%' OR category LIKE '%$search%')";
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

$where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
$query = "SELECT * FROM products $where ORDER BY updated_at DESC";

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
  <link rel="stylesheet" href="../style/inventory.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text">Inventory Management</h1>
        <button class="btn-add" onclick="openAddForm()">
          <i class='bx bx-plus'></i> Add New Product
        </button>
      </div>

      <!-- Filters Form -->
      <form method="GET" class="filters">
        <input type="text" name="search" placeholder="Search product..."
          value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <select name="category">
          <option>All Categories</option>
          <option <?= ($_GET['category'] ?? '') == 'Solid' ? 'selected' : '' ?>>Solid</option>
          <option <?= ($_GET['category'] ?? '') == 'Liquid' ? 'selected' : '' ?>>Liquid</option>
        </select>

        <select name="stock">
          <option>All Stock Levels</option>
          <option <?= ($_GET['stock'] ?? '') == 'Low' ? 'selected' : '' ?>>Low</option>
          <option <?= ($_GET['stock'] ?? '') == 'Normal' ? 'selected' : '' ?>>Normal</option>
          <option <?= ($_GET['stock'] ?? '') == 'High' ? 'selected' : '' ?>>High</option>
        </select>

        <button type="submit" style="display: none;"></button>
      </form>

      <!-- Inventory Table -->
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
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
            <td><?= htmlspecialchars($row['sku'] ?? 'N/A'); ?></td>
            <td>
              <?php 
                $stock = $row['quantity'];
                $level = $stock < 30 ? 'low' : ($stock < 70 ? 'normal' : 'high');
                echo "<span class='$level'>{$stock} units - " . ucfirst($level) . "</span>";
              ?>
              <div class="stock-bar"><span class="progress-<?= $level ?>"></span></div>
            </td>
            <td>$<?= number_format($row['price'], 2); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn-edit"
                  onclick="openEditForm(<?= $row['id']; ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['category']); ?>', <?= $row['quantity']; ?>, <?= $row['price']; ?>)">
                  <i class='bx bxs-edit'></i> Edit
                </button>
                <a href="?delete=<?= $row['id']; ?>" class="btn-delete"
                  onclick="return confirm('Are you sure you want to delete this product?');">
                  <i class='bx bxs-trash'></i> Delete
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Edit Form Modal -->
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
            <option value="Solid">Solid</option>
            <option value="Liquid">Liquid</option>
          </select>
        </div>
        <div class="form-group">
          <label for="quantity">Quantity</label>
          <input type="number" id="edit_quantity" name="quantity" min="0" required>
        </div>
        <div class="form-group">
          <label for="price">Price ($)</label>
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
            <option value="Solid">Solid</option>
            <option value="Liquid">Liquid</option>
          </select>
        </div>
        <div class="form-group">
          <label for="quantity">Quantity</label>
          <input type="number" id="add_quantity" name="quantity" min="0" value="0" required>
        </div>
        <div class="form-group">
          <label for="price">Price ($)</label>
          <input type="number" id="add_price" name="price" min="0" step="0.01" value="0.00" required>
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
  // Functions for Edit Modal
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

  // Close modals when clicking outside the form container
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