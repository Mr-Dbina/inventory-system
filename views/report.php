<?php  
include '../database/db.php'; 
$conn = getDatabaseConnection();


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM items WHERE id = $id";
    mysqli_query($conn, $deleteQuery);
    header("Location: report.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = (float)$_POST['price'];
    
    $updateQuery = "UPDATE items SET 
                    product_name = '$product_name',
                    category = '$category',
                    price = $price,
                    updated_at = NOW()
                    WHERE id = $id";
                    
    mysqli_query($conn, $updateQuery);
    header("Location: report.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = (float)$_POST['price'];
    

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
    
    $insertQuery = "INSERT INTO items (product_name, category, price, image_path, created_at, updated_at) 
                    VALUES ('$product_name', '$category', $price, '$image_path', NOW(), NOW())";
                    
    mysqli_query($conn, $insertQuery);
    header("Location: report.php");
    exit();
}


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
$query = "SELECT * FROM items $where ORDER BY updated_at ASC";

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
  <style>
  /* Larger table styles */
  table {
    width: 95%;
    margin: 0 auto;
    font-size: 1rem;
    border-collapse: collapse;
  }

  table th,
  table td {
    padding: 12px 15px;
    text-align: center;
    vertical-align: middle;
  }

  table th {
    background-color: #f5f5f5;
    font-weight: bold;
    font-size: 1.1rem;
  }

  table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
  }

  .action-buttons button,
  .action-buttons a {
    padding: 6px 12px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .action-buttons i {
    font-size: 1rem;
    margin-right: 5px;
  }

  .badge {
    padding: 5px 10px;
    font-size: 0.9rem;
    border-radius: 5px;
  }

  .filters {
    width: 95%;
    margin: 0 auto 20px;
    display: flex;
    gap: 15px;
  }

  .content {
    padding: 25px;
  }

  .content h1 {
    margin-bottom: 25px;
    font-size: 1.8rem;
  }

  img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    vertical-align: middle;
    margin-right: 10px;
  }

  .btn-add {
    padding: 8px 16px;
    font-size: 1rem;
  }
  </style>
</head>

<body>
  <section class="home">
    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center; width: 95%; margin: 0 auto;">
        <h1 class="text">SALE REPORT</h1>
        <button class="btn-add" onclick="openAddForm()">
          <i class='bx bx-plus'></i> Add New Product
        </button>
      </div>

      <form method="GET" class="filters">
        <input type="text" name="search" placeholder="Search item..."
          value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <select name="category">
          <option>All Categories</option>
          <option <?= ($_GET['category'] ?? '') == 'Solid' ? 'selected' : '' ?>>Solid</option>
          <option <?= ($_GET['category'] ?? '') == 'Liquid' ? 'selected' : '' ?>>Liquid</option>
        </select>

        <button type="submit" style="display: none;"></button>
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
          <?php while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td style="text-align: left;">
              <img src="<?= $row['image_path']; ?>">
              <?= htmlspecialchars($row['product_name']); ?>
            </td>
            <td><span class="badge"><?= htmlspecialchars($row['category']); ?></span></td>
            <td>$<?= number_format($row['price'], 2); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn-edit"
                  onclick="openEditForm(<?= $row['id']; ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['category']); ?>', <?= $row['price']; ?>)">
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
            <option value="Solid">Solid</option>
            <option value="Liquid">Liquid</option>
          </select>
        </div>
        <div class="form-group">
          <label for="price">Price (₱)</label>
          <input type="number" id="add_price" name="price" min="0" step="0.01" value="0.00" required>
        </div>
        <div class="form-group">
          <label for="image">Product Image</label>
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