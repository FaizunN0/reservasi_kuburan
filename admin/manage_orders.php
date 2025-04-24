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
  <title>Kelola Order - Admin Kuburan Pacitan</title>
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
         <li class="nav-item active"><a class="nav-link" href="manage_orders.php">Kelola Order</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_users.php">Kelola User</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_locations.php">Kelola Lokasi</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_paket.php">Kelola Paket</a></li>
         <li class="nav-item"><a class="nav-link" href="respon_user.php">Respon User</a></li>
         <li class="nav-item"><a class="nav-link" href="invoice.php">Invoice</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text mr-3">Halo, <?php echo $_SESSION['admin_username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  
  <div class="container">
    <h2>Kelola Order</h2>
    <table id="manageOrdersTable" class="table table-bordered">
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
              <button class="btn btn-sm btn-primary btn-update-status" data-order-id="<?php echo $order['order_id']; ?>">Ubah Status</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Modal Ubah Status -->
  <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="updateStatusModalLabel">Ubah Status Order</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <input type="hidden" id="modalOrderId" value="">
            <div class="form-group">
              <label>Pilih Status:</label>
              <select id="modalStatusSelect" class="form-control">
                <option value="belum dikerjakan">Belum Dikerjakan</option>
                <option value="dikerjakan">Dikerjakan</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="saveStatusBtn">Simpan</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
         </div>
      </div>
    </div>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script>
    $(document).ready(function(){
      $('#manageOrdersTable').DataTable();
      
      $('.btn-update-status').on('click', function(){
         const orderId = $(this).data('order-id');
         $('#modalOrderId').val(orderId);
         $('#updateStatusModal').modal('show');
      });
      
      $('#saveStatusBtn').on('click', function(){
         const orderId = $('#modalOrderId').val();
         const newStatus = $('#modalStatusSelect').val();
         $.ajax({
            url: 'update_order_status.php',
            method: 'POST',
            data: { order_id: orderId, status: newStatus },
            success: function(response){
              if(response.trim() === 'success'){
                Swal.fire({
                  icon: 'success',
                  title: 'Berhasil',
                  text: 'Status order telah diperbarui.'
                }).then(() => { location.reload(); });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal',
                  text: 'Gagal mengubah status order.'
                });
              }
            },
            error: function(){
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan pada server.'
              });
            }
         });
      });
    });
  </script>
</body>
</html>
