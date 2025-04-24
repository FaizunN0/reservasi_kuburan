<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit();
}
require 'config.php';

// Ambil data paket beserta lokasi dan image
$sql = "
  SELECT p.*, l.name AS lokasi_name
  FROM paket p
  LEFT JOIN locations l ON p.location_id = l.id
  ORDER BY p.nama ASC
";
$result = $conn->query($sql);

// Kelompokkan berdasarkan nama paket
$groupedPakets = [];
while($row = $result->fetch_assoc()){
    $groupedPakets[$row['nama']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .card { margin-bottom: 20px; }
    .card-header { font-weight: bold; }
  </style>
</head>
<body>
  <!-- Navbar User -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navUser">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navUser">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">Detail Pesanan</a></li>
        <li class="nav-item"><a class="nav-link" href="ahli_waris.php">Ahli Waris</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4">
    <h2>Pilih Paket Kuburan</h2>
    <div class="row">
      <?php foreach($groupedPakets as $namaPaket => $variants): 
        $default = $variants[0];
        $jsonVariants = htmlspecialchars(json_encode($variants), ENT_QUOTES, 'UTF-8');
      ?>
      <div class="col-md-4">
        <div class="card">
          <!-- Nama Paket -->
          <div class="card-header bg-secondary text-white">
            <?php echo htmlspecialchars($namaPaket); ?>
          </div>
          <div class="card-body">
            <!-- Preview Gambar: di bawah nama paket, di atas dropdown -->
            <?php if(!empty($default['image'])): ?>
              <img 
                src="uploads/<?php echo htmlspecialchars($default['image']); ?>" 
                class="img-fluid mb-3" 
                alt="Gambar <?php echo htmlspecialchars($namaPaket); ?>">
            <?php endif; ?>

            <!-- Pilih Lokasi -->
            <div class="form-group">
              <label>Pilih Lokasi:</label>
              <select 
                class="form-control variant-select" 
                data-variants='<?php echo $jsonVariants; ?>'>
                <?php foreach($variants as $v): ?>
                  <option 
                    value="<?php echo $v['id']; ?>" 
                    <?php if($v['id'] == $default['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($v['lokasi_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- Detail Paket -->
            <div class="card-details">
              <p><strong>Deskripsi:</strong> 
                <span class="deskripsi"><?php echo htmlspecialchars($default['deskripsi']); ?></span>
              </p>
              <p><strong>Harga:</strong> Rp 
                <span class="harga">
                  <?php echo number_format($default['harga'],0,',','.'); ?>
                </span>
              </p>
              <p><strong>Stok Kapling:</strong> 
                <span class="stok"><?php echo $default['stok_kapling']; ?></span>
              </p>
              <p><strong>Lokasi:</strong> 
                <span class="lokasi"><?php echo htmlspecialchars($default['lokasi_name']); ?></span>
              </p>
            </div>
            <!-- Tombol Pesan -->
            <button 
              class="btn btn-primary pesan-btn mt-2" 
              data-variant-id="<?php echo $default['id']; ?>">
              Pesan
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- JS dependencies -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
    // Update detail & gambar saat variant berubah
    document.querySelectorAll('.variant-select').forEach(function(select){
      select.addEventListener('change', function(){
        var variants = JSON.parse(this.getAttribute('data-variants'));
        var selId    = this.value;
        var v        = variants.find(x => x.id == selId);
        var card     = this.closest('.card-body');
        if (!v) return;
        // update gambar preview
        var img = card.querySelector('img.img-fluid');
        if (img) {
          img.src = 'uploads/' + v.image;
        }
        // update teks detail
        card.querySelector('.deskripsi').innerText = v.deskripsi;
        card.querySelector('.harga').innerText     = parseFloat(v.harga)
          .toLocaleString('id-ID', {maximumFractionDigits: 0});
        card.querySelector('.stok').innerText      = v.stok_kapling;
        card.querySelector('.lokasi').innerText    = v.lokasi_name;
        card.querySelector('.pesan-btn')
            .setAttribute('data-variant-id', v.id);
      });
      // trigger awal
      select.dispatchEvent(new Event('change'));
    });

    // Aksi tombol Pesan
    document.querySelectorAll('.pesan-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = this.getAttribute('data-variant-id');
        window.location.href = 'order.php?paket=' + id;
      });
    });
  </script>
</body>
</html>
