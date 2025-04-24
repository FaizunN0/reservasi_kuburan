<?php
session_start();
if(!isset($_SESSION['user_id'])){
   header("Location: login.php");
   exit();
}
require 'config.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM ahli_waris WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ahli_waris = [];
while($row = $result->fetch_assoc()){
   $ahli_waris[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Ahli Waris - Reservasi Kuburan Pacitan</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
  <style>
    /* Desain unik untuk tabel ahli waris */
    body {
      background-color: #f2f2f2;
    }
    .table-wrapper {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .dataTables_wrapper .dataTables_filter input {
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 6px;
    }
    .btn-edit, .btn-delete {
      margin-right: 5px;
    }
  </style>
</head>
<body>
  <!-- Navbar User -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item active"><a class="nav-link" href="ahli_waris.php">Ahli Waris</a></li>
         <li class="nav-item"><a class="nav-link" href="orders.php">Detail Pesanan</a></li>
         <li class="nav-item"><a class="nav-link" href="user_messages.php">Pesan Saya</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  
  <div class="container mt-4">
    <h2>Data Ahli Waris</h2>
    <div class="table-wrapper">
      <table id="ahliWarisTable" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Hubungan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($ahli_waris as $aw): ?>
          <tr>
            <td><?php echo $aw['id']; ?></td>
            <td><?php echo htmlspecialchars($aw['nama']); ?></td>
            <td><?php echo htmlspecialchars($aw['hubungan']); ?></td>
            <td>
              <a href="ahli_waris_edit.php?id=<?php echo $aw['id']; ?>" class="btn btn-sm btn-primary btn-edit">Edit</a>
              <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $aw['id']; ?>">Hapus</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <a href="ahli_waris_add.php" class="btn btn-success mt-3">Tambah Ahli Waris</a>
  </div>

  <!-- jQuery, Bootstrap JS, DataTables, SweetAlert2 -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script>
    $(document).ready(function() {
      $('#ahliWarisTable').DataTable();

      $('.btn-delete').click(function(){
         const id = $(this).data('id');
         Swal.fire({
           title: 'Hapus Ahli Waris?',
           text: "Apakah Anda yakin ingin menghapus data ini?",
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           cancelButtonColor: '#3085d6',
           confirmButtonText: 'Ya, hapus!',
           cancelButtonText: 'Batal'
         }).then((result) => {
           if(result.isConfirmed){
             window.location.href = "ahli_waris_delete.php?id=" + id;
           }
         });
      });
    });
  </script>
</body>
</html>
