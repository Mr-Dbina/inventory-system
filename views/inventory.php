<?php

include '../database/db.php';
$conn=getDatabaseConnection();

$query = "SELECT * FROM inventory ORDER BY updated_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Item Management</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="style.css" rel="stylesheet">
</head>

<body>
  <div style="display: flex; width: 100%;">
    <?php include("../partials/sidebar.php"); ?>

    <div class=" mt-4" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
      <h2 class="mb-4">Inventory Management System</h2>

      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Items List</h5>
        </div>

        <div class="card-body">
          <table id="itemsTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
              <tr>
                <th>ID</th>
                <th>ITEM NAME</th>
                <th>QUANTITY</th>
                <th>PRICE</th>
                <th>IMAGE</th>
                <th>UPDATED AT</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= number_format($row['price'], 2)  ?></td>
                <td>
                  <img width="10" height="10" src="<?= htmlspecialchars($row['image'])  ?>" alt="">
                </td>
                <td><?= date('M d, Y H:i', strtotime($row['updated_at']))  ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
  $(document).ready(function() {
    $('#itemsTable').DataTable({
      "order": [
        [5, 'desc']
      ],
      "columnDefs": [{
          "targets": 4,
          "orderable": false,
          "render": function(data, type, row) {
            if (type === 'display') {
              return '<img src="' + data + '" class="item-image">';
            }
            return data;
          }
        },
        {
          "targets": 3,
          "type": "currency"
        },
        {
          "targets": [0, 2, 3],
          "className": "text-center"
        }
      ],
      "responsive": true,
      "language": {
        "search": "_INPUT_",
        "searchPlaceholder": "Search items..."
      }
    });
  });
  </script>
</body>

</html>