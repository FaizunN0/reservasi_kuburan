<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit();
}
require 'config.php';

$user_id = $_SESSION['user_id'];
if(isset($_GET['delete'])){
    $order_id = intval($_GET['delete']);
    $stmtCheck = $conn->prepare("SELECT o.tanggal, od.qty_kapling, od.paket_id FROM orders o JOIN order_details od ON o.id = od.order_id WHERE o.id = ? AND o.user_id = ?");
    $stmtCheck->bind_param("ii", $order_id, $user_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if($resultCheck->num_rows == 0){
        header("Location: orders.php?msg=Order tidak ditemukan");
        exit();
    }
    $orderData = $resultCheck->fetch_assoc();
    $stmtCheck->close();
    
    $orderTime = new DateTime($orderData['tanggal']);
    $now = new DateTime();
    $diffMinutes = ($now->getTimestamp() - $orderTime->getTimestamp())/60;
    if($diffMinutes > 18){
        header("Location: orders.php?msg=Waktu edit/hapus order sudah lewat");
        exit();
    }
    
    $stmtRestore = $conn->prepare("UPDATE paket SET stok_kapling = stok_kapling + ? WHERE id = ?");
    $stmtRestore->bind_param("ii", $orderData['qty_kapling'], $orderData['paket_id']);
    $stmtRestore->execute();
    $stmtRestore->close();
    
    $stmtDelDetails = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
    $stmtDelDetails->bind_param("i", $order_id);
    $stmtDelDetails->execute();
    $stmtDelDetails->close();
    
    $stmtDelOrder = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmtDelOrder->bind_param("ii", $order_id, $user_id);
    if($stmtDelOrder->execute()){
         header("Location: orders.php?msg=Order berhasil dihapus");
         exit();
    } else {
         header("Location: orders.php?msg=Gagal menghapus order");
         exit();
    }
    $stmtDelOrder->close();
}

$sql = "SELECT o.id AS order_id, o.total_harga, o.tanggal, o.status, o.alamat_user, o.kapling_numbers, 
               od.atas_nama, od.qty_kapling, p.nama AS paket_nama, 
               (SELECT CONCAT(l.lat, ', ', l.lng) FROM locations l WHERE l.id = p.location_id) AS paket_lokasi 
        FROM orders o 
        JOIN order_details od ON o.id = od.order_id 
        JOIN paket p ON od.paket_id = p.id 
        WHERE o.user_id = ? 
        ORDER BY o.tanggal DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while($row = $result->fetch_assoc()){
    $orderTime = new DateTime($row['tanggal']);
    $now = new DateTime();
    $diffMinutes = ($now->getTimestamp() - $orderTime->getTimestamp())/60;
    $row['editable'] = ($diffMinutes <= 18);
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Order Saya - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
  </style>
</head>
<body>
  <!-- Navbar User -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item active"><a class="nav-link" href="orders.php">Detail Pesanan</a></li>
         <li class="nav-item"><a class="nav-link" href="ahli_waris.php">Ahli Waris</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h2>Daftar Order Saya</h2>
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <table id="ordersTable" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Total Harga</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Atas Nama</th>
          <th>Paket</th>
          <th>Qty Kapling</th>
          <th>Alamat</th>
          <th>Nomor Kapling</th>
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
          <td>Rp <?php echo number_format($order['total_harga'],0,',','.'); ?></td>
          <td><?php echo $order['tanggal']; ?></td>
          <td><span class="<?php echo $badgeClass; ?>"><?php echo ucfirst($order['status']); ?></span></td>
          <td><?php echo $order['atas_nama']; ?></td>
          <td><?php echo $order['paket_nama']; ?></td>
          <td><?php echo $order['qty_kapling']; ?></td>
          <td><?php echo $order['alamat_user']; ?></td>
          <td><?php echo $order['kapling_numbers']; ?></td>
          <td>
            <?php if($order['editable']): ?>
              <button class="btn btn-sm btn-primary btn-edit" data-order-id="<?php echo $order['order_id']; ?>">Edit</button>
              <button class="btn btn-sm btn-danger btn-delete" data-order-id="<?php echo $order['order_id']; ?>">Hapus</button>
            <?php else: ?>
              <button class="btn btn-sm btn-secondary" onclick="showTimeOverAlert()">Edit</button>
              <button class="btn btn-sm btn-secondary" onclick="showTimeOverAlert()">Hapus</button>
            <?php endif; ?>
            <a href="order_detail.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">Detail</a>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script>
    $(document).ready(function() {
      $('#ordersTable').DataTable();

      $('.btn-edit').on('click', function(){
         const orderId = $(this).data('order-id');
         window.location.href = "order_edit.php?order_id=" + orderId;
      });

      $('.btn-delete').on('click', function(){
         const orderId = $(this).data('order-id');
         Swal.fire({
           icon: 'warning',
           title: 'Hapus Order',
           text: 'Apakah Anda yakin ingin menghapus order ini? Stok akan dikembalikan.',
           showCancelButton: true,
           confirmButtonText: 'Ya, hapus!',
           cancelButtonText: 'Batal'
         }).then((result) => {
           if(result.isConfirmed){
             window.location.href = "orders.php?delete=" + orderId;
           }
         });
      });
    });

    function showTimeOverAlert() {
      Swal.fire({
         icon: 'error',
         title: 'Waktu Habis',
         text: 'Batas waktu 18 menit untuk edit/hapus order telah lewat!'
      });
    }
  </script>
</body>
</html>
