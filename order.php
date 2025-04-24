<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'config.php';

// Pastikan ada parameter paket
if (!isset($_GET['paket'])) {
    header("Location: dashboard.php");
    exit();
}
$paket_id = intval($_GET['paket']);

// Ambil detail paket + lokasi
$stmt = $conn->prepare("
    SELECT p.*, l.name AS lokasi_name, l.lat, l.lng 
    FROM paket p 
    LEFT JOIN locations l ON p.location_id = l.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $paket_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("Paket tidak ditemukan.");
}
$paket = $res->fetch_assoc();
$stmt->close();

// Ambil daftar ahli waris milik user
$stmt2 = $conn->prepare("SELECT * FROM ahli_waris WHERE user_id = ?");
$stmt2->bind_param("i", $_SESSION['user_id']);
$stmt2->execute();
$res2 = $stmt2->get_result();
$ahli_waris = $res2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// Fungsi generate nomor kapling unik
function generateKaplingNumbers($conn, $qty) {
    $used = [];
    $res = $conn->query("SELECT kapling_numbers FROM orders WHERE kapling_numbers <> ''");
    while ($row = $res->fetch_assoc()) {
        foreach (explode(',', $row['kapling_numbers']) as $n) {
            if (is_numeric(trim($n))) $used[] = intval($n);
        }
    }
    sort($used);
    $new = []; $i = 1;
    while (count($new) < $qty) {
        if (!in_array($i, $used)) $new[] = $i;
        $i++;
    }
    return implode(',', $new);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $atas_nama = trim($_POST['atas_nama']);
    $alamat_user = trim($_POST['alamat_user']);
    $qty_kapling = intval($_POST['qty_kapling']);

    // Opsi B: explode CSV string menjadi array, lalu implode kembali
    $ahli_waris_ids = !empty($_POST['ahli_waris_ids'])
        ? explode(',', $_POST['ahli_waris_ids'])
        : [];
    $selected_ahli_waris = implode(',', $ahli_waris_ids);

    // Validasi
    if ($atas_nama === "" || $alamat_user === "" || $qty_kapling <= 0) {
        $message = "Isi semua field (Atas Nama, Alamat, Jumlah Kapling) dengan benar.";
    } elseif ($paket['stok_kapling'] < $qty_kapling) {
        $message = "Maaf, stok tidak mencukupi atau sudah habis.";
    } else {
        $total_harga = $paket['harga'] * $qty_kapling;
        $kapling_numbers = generateKaplingNumbers($conn, $qty_kapling);

        // Insert ke orders
        $stmt3 = $conn->prepare("
            INSERT INTO orders 
              (user_id, total_harga, alamat_user, kapling_numbers, selected_ahli_waris) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt3->bind_param(
            "idsss",
            $_SESSION['user_id'],
            $total_harga,
            $alamat_user,
            $kapling_numbers,
            $selected_ahli_waris
        );
        if ($stmt3->execute()) {
            $order_id = $stmt3->insert_id;
            $stmt3->close();

            // Insert ke order_details
            $stmt4 = $conn->prepare("
                INSERT INTO order_details 
                  (order_id, paket_id, harga, atas_nama, qty_kapling) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt4->bind_param(
                "iidsi",
                $order_id,
                $paket_id,
                $paket['harga'],
                $atas_nama,
                $qty_kapling
            );
            if ($stmt4->execute()) {
                $stmt4->close();
                // Update stok paket
                $stmt5 = $conn->prepare("UPDATE paket SET stok_kapling = stok_kapling - ? WHERE id = ?");
                $stmt5->bind_param("ii", $qty_kapling, $paket_id);
                $stmt5->execute();
                $stmt5->close();
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Gagal menyimpan detail order.";
            }
        } else {
            $message = "Gagal membuat order.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesan Paket Kuburan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background: #f8f9fa; }
    .order-container { margin-top: 30px; }
    .map-frame { width: 100%; height: 300px; border:0; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navUser">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navUser">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container order-container">
    <h2>Pesan Paket: <?php echo $paket['nama']; ?></h2>
    <?php if($message): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="card mb-3">
      <div class="card-body">
        <p><strong>Deskripsi:</strong> <?php echo $paket['deskripsi']; ?></p>
        <p><strong>Harga:</strong> Rp <?php echo number_format($paket['harga'],0,',','.'); ?></p>
        <p><strong>Stok Kapling:</strong> <?php echo $paket['stok_kapling']; ?></p>
        <p><strong>Lokasi:</strong> <?php echo $paket['lokasi_name']; ?></p>
      </div>
    </div>

    <!-- Embed Google Maps tanpa API -->
    <iframe
      class="map-frame"
      src="https://maps.google.com/maps?q=<?php echo $paket['lat']; ?>,<?php echo $paket['lng']; ?>&z=18&output=embed"
      allowfullscreen
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>

    <form method="POST" action="order.php?paket=<?php echo $paket_id; ?>" class="mt-4">
      <div class="form-group">
        <label>Atas Nama:</label>
        <input type="text" name="atas_nama" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Alamat Lengkap:</label>
        <input type="text" name="alamat_user" class="form-control" required>
      </div>
      <!-- Pilih Ahli Waris -->
      <div class="form-group">
        <label>Ahli Waris (Opsional):</label>
        <div class="input-group">
          <input type="text" id="awDisplay" class="form-control" placeholder="-- pilih ahli waris --" readonly>
          <div class="input-group-append">
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#awModal">Pilih</button>
          </div>
        </div>
        <input type="hidden" name="ahli_waris_ids" id="awIds">
      </div>
      <!-- Modal Ahli Waris -->
      <div class="modal fade" id="awModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Pilih Ahli Waris</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <?php if($ahli_waris): ?>
                <?php foreach($ahli_waris as $aw): ?>
                  <div class="form-check">
                    <input class="form-check-input aw-checkbox" type="checkbox" value="<?php echo $aw['id']; ?>" id="aw<?php echo $aw['id']; ?>">
                    <label class="form-check-label" for="aw<?php echo $aw['id']; ?>">
                      <?php echo htmlspecialchars($aw['nama'].' ('.$aw['hubungan'].')'); ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>Tidak ada data ahli waris.</p>
              <?php endif; ?>
            </div>
            <div class="modal-footer">
              <button type="button" id="saveAw" class="btn btn-primary">Simpan</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Jumlah Kapling:</label>
        <input type="number" name="qty_kapling" class="form-control" min="1" required>
      </div>
      <button type="submit" class="btn btn-success">Pesan Sekarang</button>
    </form>
  </div>

  <!-- JS dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
    // Modal Ahli Waris
    $('#saveAw').click(function(){
      let ids = [], names = [];
      $('.aw-checkbox:checked').each(function(){
        ids.push(this.value);
        names.push($(this).next('label').text());
      });
      $('#awIds').val(ids.join(','));
      $('#awDisplay').val(ids.length ? '-- data direkam --' : '-- pilih ahli waris --');
      $('#awModal').modal('hide');
    });
  </script>
</body>
</html>
