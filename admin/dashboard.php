<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

$sql = "SELECT o.id AS order_id, o.total_harga, o.tanggal, o.status, u.username 
        FROM orders o 
        JOIN user u ON o.user_id = u.user_id 
        ORDER BY o.tanggal DESC";
$result = $conn->query($sql);
$orders = [];
while($row = $result->fetch_assoc()){
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
  </style>
</head>
<body>
  <!-- Navbar Admin -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Admin Kuburan Pacitan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item active"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_orders.php">Kelola Order</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_users.php">Kelola User</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_locations.php">Kelola Lokasi</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_paket.php">Kelola Paket</a></li>
         <li class="nav-item"><a class="nav-link" href="respon_user.php">Respon User</a></li>
         <li class="nav-item"><a class="nav-link" href="invoice.php">Invoice</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['admin_username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h2>Ringkasan Order</h2>
    <table id="adminDashboardTable" class="table table-bordered">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Total Harga</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orders as $order): 
            $status = strtolower($order['status']);
            if($status == 'belum dikerjakan'){
                $badgeClass = 'badge badge-danger';
            } elseif($status == 'dikerjakan'){
                $badgeClass = 'badge badge-warning';
            } elseif($status == 'selesai'){
                $badgeClass = 'badge badge-success';
            } else {
                $badgeClass = 'badge badge-secondary';
            }
        ?>
          <tr>
            <td><?php echo $order['order_id']; ?></td>
            <td><?php echo $order['username']; ?></td>
            <td>Rp <?php echo number_format($order['total_harga'],0,',','.'); ?></td>
            <td><?php echo $order['tanggal']; ?></td>
            <td><span class="<?php echo $badgeClass; ?>"><?php echo ucfirst($order['status']); ?></span></td>
            <td>
              <a href="order_detail.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">Detail</a>
              <!-- Tombol update status (bisa ditambahkan jika diperlukan) -->
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function(){
      $('#adminDashboardTable').DataTable();
    });
  </script>
</body>
</html>
