<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require '../config.php';

if (!isset($_GET['order_id'])) {
    die("Order ID tidak tersedia. Pastikan URL mengandung parameter order_id.");
}
$order_id = intval($_GET['order_id']);

// Ambil data order lengkap dengan detail order, paket, dan lokasi paket
$sql = "SELECT 
          o.id AS order_id, 
          o.total_harga, 
          o.tanggal, 
          o.status, 
          o.alamat_user, 
          o.kapling_numbers, 
          o.selected_ahli_waris,
          od.atas_nama, 
          od.qty_kapling, 
          p.nama AS paket_nama, 
          p.deskripsi AS paket_deskripsi,
          l.name AS lokasi_name, 
          l.lat, 
          l.lng
        FROM orders o
        JOIN order_details od ON o.id = od.order_id
        JOIN paket p ON od.paket_id = p.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Order tidak ditemukan.");
}
$order = $result->fetch_assoc();
$stmt->close();

// Ambil nama ahli waris jika ada
$ahliWarisNames = "Tidak ada";
if (!empty($order['selected_ahli_waris'])) {
    $ids = $order['selected_ahli_waris']; // Format: "3,5,7"
    $query = "SELECT GROUP_CONCAT(nama SEPARATOR ', ') AS names FROM ahli_waris WHERE id IN ($ids)";
    $res = $conn->query($query);
    if ($res) {
        $data = $res->fetch_assoc();
        if (!empty($data['names'])) {
            $ahliWarisNames = $data['names'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Order #<?php echo $order['order_id']; ?> - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none; }
    }
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
    .map-container { width: 100%; height: 300px; margin-top: 15px; }
  </style>
</head>
<body>
  <!-- Navbar User -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="userNavbar">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item"><a class="nav-link" href="orders.php">Detail Pesanan</a></li>
         <li class="nav-item"><a class="nav-link" href="ahli_waris.php">Ahli Waris</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  
  <div class="container">
    <h2>Detail Order #<?php echo $order['order_id']; ?></h2>
    <div class="card mb-3">
      <div class="card-header bg-secondary text-white">Informasi Order</div>
      <div class="card-body">
        <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
        <p><strong>Total Harga:</strong> Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
        <p><strong>Tanggal:</strong> <?php echo $order['tanggal']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        <p><strong>Atas Nama:</strong> <?php echo $order['atas_nama']; ?></p>
        <p><strong>Paket:</strong> <?php echo $order['paket_nama']; ?></p>
        <p><strong>Deskripsi Paket:</strong> <?php echo $order['paket_deskripsi']; ?></p>
        <p><strong>Qty Kapling:</strong> <?php echo $order['qty_kapling']; ?></p>
        <p><strong>Alamat:</strong> <?php echo $order['alamat_user']; ?></p>
        <p><strong>Nomor Kapling:</strong> <?php echo $order['kapling_numbers']; ?></p>
        <p><strong>Lokasi Paket:</strong> <?php echo $order['lokasi_name']; ?> (<?php echo $order['lat'] . ", " . $order['lng']; ?>)</p>
        <p><strong>Ahli Waris Terpilih:</strong> <?php echo $ahliWarisNames; ?></p>
        <hr>
        <h4>Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></h4>
      </div>
    </div>
    <div class="mb-3 no-print">
      <button class="btn btn-primary" onclick="routeToMe()">Tampilkan Rute</button>
      <button class="btn btn-secondary" onclick="openLocation()">Tampilkan Lokasi Paket</button>
      <button class="btn btn-success" onclick="window.print()">Cetak Invoice</button>
    </div>
    <!-- Google Maps Embed menggunakan iframe tanpa API -->
    <div class="map-container mb-4" style="height:300px;">
  <iframe
    width="100%" height="100%" frameborder="0" style="border:0"
    src="https://maps.google.com/maps?q=<?php echo $order['lat']; ?>,<?php echo $order['lng']; ?>&z=18&output=embed"
    allowfullscreen
    loading="lazy"
    referrerpolicy="no-referrer-when-downgrade">
  </iframe>
</div>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
    function routeToMe() {
      const lat = <?php echo $order['lat']; ?>;
      const lng = <?php echo $order['lng']; ?>;
      const url = "https://www.google.com/maps/dir/?api=1&destination=" + lat + "," + lng;
      window.open(url, '_blank');
    }
    function openLocation() {
      const lat = <?php echo $order['lat']; ?>;
      const lng = <?php echo $order['lng']; ?>;
      const url = "https://www.google.com/maps/search/?api=1&query=" + lat + "," + lng;
      window.open(url, '_blank');
    }
  </script>
</body>
</html>
