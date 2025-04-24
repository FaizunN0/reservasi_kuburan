<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require '../config.php';

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("<h3>Error: Order ID tidak tersedia. Pastikan URL mengandung parameter order_id.</h3>");
}
$order_id = intval($_GET['order_id']);

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
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("<h3>Error: Order tidak ditemukan.</h3>");
}
$order = $result->fetch_assoc();
$stmt->close();

$ahliWarisNames = "Tidak ada";
if (!empty($order['selected_ahli_waris'])) {
    $ids = $order['selected_ahli_waris'];
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
  <title>Invoice Order #<?php echo $order['order_id']; ?> - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none; }
    }
    body { background-color: #fff; }
    .invoice-container { margin: 20px auto; max-width: 800px; padding: 20px; border: 1px solid #ccc; }
    .invoice-header { text-align: center; margin-bottom: 20px; }
    .invoice-details { margin-bottom: 20px; }
    .invoice-footer { text-align: center; margin-top: 20px; }
  </style>
</head>
<body>
  <div class="invoice-container">
    <div class="invoice-header">
      <h2>Invoice Order</h2>
      <p>Admin - Reservasi Kuburan Pacitan</p>
    </div>
    <div class="invoice-details">
      <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
      <p><strong>Tanggal:</strong> <?php echo $order['tanggal']; ?></p>
      <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
      <p><strong>Atas Nama:</strong> <?php echo $order['atas_nama']; ?></p>
      <p><strong>Paket:</strong> <?php echo $order['paket_nama']; ?></p>
      <p><strong>Deskripsi Paket:</strong> <?php echo $order['paket_deskripsi']; ?></p>
      <p><strong>Qty Kapling:</strong> <?php echo $order['qty_kapling']; ?></p>
      <p><strong>Alamat:</strong> <?php echo $order['alamat_user']; ?></p>
      <p><strong>Nomor Kapling:</strong> <?php echo $order['kapling_numbers']; ?></p>
      <p><strong>Lokasi Paket:</strong> <?php echo $order['lokasi_name']; ?> (<?php echo $order['lat'].", ".$order['lng']; ?>)</p>
      <p><strong>Ahli Waris Terpilih:</strong> <?php echo $ahliWarisNames; ?></p>
      <hr>
      <h4>Total: Rp <?php echo number_format($order['total_harga'],0,',','.'); ?></h4>
    </div>
    <div class="invoice-footer no-print">
      <button class="btn btn-primary" onclick="window.print()">Cetak Invoice</button>
    </div>
  </div>
</body>
</html>
