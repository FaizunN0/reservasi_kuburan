<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
require 'config.php';

// Pastikan parameter order_id ada
if(!isset($_GET['order_id'])){
    header("Location: orders.php");
    exit();
}
$order_id = intval($_GET['order_id']);

// Ambil data order dan order_details
$stmt = $conn->prepare("SELECT o.id AS order_id, o.total_harga, o.tanggal, o.alamat_user, o.kapling_numbers, o.selected_ahli_waris, od.atas_nama, od.qty_kapling, od.paket_id FROM orders o JOIN order_details od ON o.id = od.order_id WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    die("Order tidak ditemukan.");
}
$order = $result->fetch_assoc();
$stmt->close();

// Cek batas waktu edit: 18 menit sejak order dibuat
$orderTime = new DateTime($order['tanggal']);
$now = new DateTime();
$diffMinutes = ($now->getTimestamp() - $orderTime->getTimestamp())/60;
if($diffMinutes > 18){
    die("Waktu edit order sudah lewat.");
}

// Ambil daftar paket dengan join ke locations untuk dropdown pilihan
$pakets = [];
$sql = "SELECT p.*, l.name as lokasi_name FROM paket p LEFT JOIN locations l ON p.location_id = l.id ORDER BY p.nama ASC";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()){
    $pakets[] = $row;
}
$res->free();

// Simpan nilai paket lama dan jumlah lama untuk pengembalian stok
$old_paket_id = $order['paket_id'];
$old_qty = $order['qty_kapling'];

// Ambil data ahli waris user (untuk opsi multi-select)
$stmt2 = $conn->prepare("SELECT * FROM ahli_waris WHERE user_id = ?");
$stmt2->bind_param("i", $_SESSION['user_id']);
$stmt2->execute();
$result2 = $stmt2->get_result();
$ahli_waris = [];
while($row = $result2->fetch_assoc()){
    $ahli_waris[] = $row;
}
$stmt2->close();

$message = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Data form
    $new_paket_id = intval($_POST['paket_id']);
    $atas_nama = trim($_POST['atas_nama']);
    $qty_kapling = intval($_POST['qty_kapling']);
    $alamat_user = trim($_POST['alamat_user']);
    // Untuk ahli waris, ambil nilai string (bisa kosong)
    $selected_ahli_waris = isset($_POST['ahli_waris_ids']) ? trim($_POST['ahli_waris_ids']) : "";
    
    if(empty($atas_nama) || $qty_kapling <= 0 || empty($alamat_user)){
         $message = "Semua data harus diisi.";
    } else {
         // Ambil data paket baru
         $stmtNew = $conn->prepare("SELECT * FROM paket WHERE id = ?");
         $stmtNew->bind_param("i", $new_paket_id);
         $stmtNew->execute();
         $resultNew = $stmtNew->get_result();
         if($resultNew->num_rows == 0){
             die("Paket baru tidak ditemukan.");
         }
         $new_paket = $resultNew->fetch_assoc();
         $stmtNew->close();
         
         // Kembalikan stok dari paket lama terlebih dahulu
         $stmtRevert = $conn->prepare("UPDATE paket SET stok_kapling = stok_kapling + ? WHERE id = ?");
         $stmtRevert->bind_param("ii", $old_qty, $old_paket_id);
         $stmtRevert->execute();
         $stmtRevert->close();
         
         // Cek stok paket baru
         if($new_paket['stok_kapling'] < $qty_kapling){
             $message = "Maaf, stok tidak mencukupi untuk paket yang dipilih.";
         } else {
             // Update stok paket baru
             $stmtUpdateStock = $conn->prepare("UPDATE paket SET stok_kapling = stok_kapling - ? WHERE id = ?");
             $stmtUpdateStock->bind_param("ii", $qty_kapling, $new_paket_id);
             $stmtUpdateStock->execute();
             $stmtUpdateStock->close();
             
             // Hitung total harga baru
             $harga = $new_paket['harga'];
             $total_harga = $harga * $qty_kapling;
             
             // Update order_details dengan data baru
             $stmtUpdateDetails = $conn->prepare("UPDATE order_details SET atas_nama = ?, qty_kapling = ?, paket_id = ? WHERE order_id = ?");
             $stmtUpdateDetails->bind_param("siii", $atas_nama, $qty_kapling, $new_paket_id, $order_id);
             if($stmtUpdateDetails->execute()){
                 $stmtUpdateDetails->close();
                 // Update orders dengan total_harga, alamat_user, dan selected_ahli_waris
                 $stmtUpdateOrder = $conn->prepare("UPDATE orders SET total_harga = ?, alamat_user = ?, selected_ahli_waris = ? WHERE id = ?");
                 $stmtUpdateOrder->bind_param("dssi", $total_harga, $alamat_user, $selected_ahli_waris, $order_id);
                 if($stmtUpdateOrder->execute()){
                     header("Location: orders.php?msg=Order updated successfully");
                     exit();
                 } else {
                     $message = "Gagal mengupdate order.";
                 }
                 $stmtUpdateOrder->close();
             } else {
                 $message = "Gagal mengupdate detail order.";
             }
         }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Order - Reservasi Kuburan Pacitan</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item"><a class="nav-link" href="orders.php">Detail Pesanan</a></li>
         <li class="nav-item"><a class="nav-link" href="ahli_waris.php">Ahli Waris</a></li>
         <li class="nav-item"><a class="nav-link" href="user_messages.php">Pesan Saya</a></li>
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h2>Edit Order #<?php echo $order['order_id']; ?></h2>
    <?php if($message != ""): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="order_edit.php?order_id=<?php echo $order['order_id']; ?>">
      <div class="form-group">
         <label>Pilih Paket:</label>
         <select class="form-control" name="paket_id" id="paketSelect" required>
           <?php foreach($pakets as $p): ?>
             <option value="<?php echo $p['id']; ?>" <?php if($p['id'] == $order['paket_id']) echo "selected"; ?>>
               <?php echo $p['nama']." - ".$p['lokasi_name']; ?>
             </option>
           <?php endforeach; ?>
         </select>
      </div>
      <div class="form-group">
         <label>Atas Nama:</label>
         <input type="text" name="atas_nama" class="form-control" value="<?php echo htmlspecialchars($order['atas_nama']); ?>" required>
      </div>
      <div class="form-group">
         <label>Jumlah Kapling:</label>
         <input type="number" name="qty_kapling" class="form-control" value="<?php echo $order['qty_kapling']; ?>" required min="1">
      </div>
      <div class="form-group">
         <label>Alamat Lengkap (User):</label>
         <input type="text" name="alamat_user" class="form-control" value="<?php echo htmlspecialchars($order['alamat_user']); ?>" required>
      </div>
      <!-- Bagian Pilih Ahli Waris dengan modal multi-select -->
      <div class="form-group">
        <label>Pilih Ahli Waris (Opsional):</label>
        <div class="input-group">
          <input type="text" id="ahliWarisDisplay" class="form-control" placeholder="-- pilih ahli waris --" readonly>
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#ahliWarisModal">Pilih</button>
          </div>
        </div>
        <input type="hidden" name="ahli_waris_ids" id="ahliWarisIds">
      </div>
      <!-- Modal untuk memilih ahli waris -->
      <div class="modal fade" id="ahliWarisModal" tabindex="-1" role="dialog" aria-labelledby="ahliWarisModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
             <div class="modal-header">
                <h5 class="modal-title" id="ahliWarisModalLabel">Pilih Ahli Waris</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                  <span aria-hidden="true">&times;</span>
                </button>
             </div>
             <div class="modal-body">
                <?php if(count($ahli_waris) > 0): ?>
                  <?php foreach($ahli_waris as $aw): ?>
                    <div class="form-check">
                      <input class="form-check-input ahli-waris-checkbox" type="checkbox" value="<?php echo $aw['id']; ?>" id="aw_<?php echo $aw['id']; ?>">
                      <label class="form-check-label" for="aw_<?php echo $aw['id']; ?>">
                        <?php echo htmlspecialchars($aw['nama']); ?> (<?php echo htmlspecialchars($aw['hubungan']); ?>)
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p>Tidak ada data ahli waris.</p>
                <?php endif; ?>
             </div>
             <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveAhliWarisBtn">Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
             </div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-success">Update Order</button>
      <a href="orders.php" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
  
  <!-- jQuery, Bootstrap JS, SweetAlert2, Leaflet JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Modal pemilihan Ahli Waris
    document.getElementById('saveAhliWarisBtn').addEventListener('click', function(){
      let selected = [];
      document.querySelectorAll('.ahli-waris-checkbox:checked').forEach(function(cb){
        selected.push(cb.value);
      });
      if(selected.length > 0) {
        document.getElementById('ahliWarisDisplay').value = "-- data direkam --";
      } else {
        document.getElementById('ahliWarisDisplay').value = "-- pilih ahli waris --";
      }
      document.getElementById('ahliWarisIds').value = selected.join(',');
      $('#ahliWarisModal').modal('hide');
    });
  </script>
</body>
</html>
