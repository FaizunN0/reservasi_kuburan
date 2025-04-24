<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
require '../config.php';

$message = '';

// Proses penghapusan lokasi
if(isset($_GET['delete'])){
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    if($stmt->execute()){
        $message = "Lokasi berhasil dihapus.";
    } else {
        $message = "Gagal menghapus lokasi.";
    }
    $stmt->close();
}

// Mode edit
$edit_data = null;
if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM locations WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $edit_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Proses form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST['edit_id']) && !empty($_POST['edit_id'])){
         $edit_id = intval($_POST['edit_id']);
         $name = trim($_POST['name']);
         $lat = floatval(trim($_POST['lat']));
         $lng = floatval(trim($_POST['lng']));
         if(empty($name) || empty($lat) || empty($lng)){
            $message = "Semua data harus diisi.";
         } else {
            $stmt = $conn->prepare("UPDATE locations SET name = ?, lat = ?, lng = ? WHERE id = ?");
            $stmt->bind_param("sddi", $name, $lat, $lng, $edit_id);
            if($stmt->execute()){
                 $message = "Lokasi berhasil diupdate.";
            } else {
                 $message = "Gagal mengupdate lokasi.";
            }
            $stmt->close();
            $edit_data = null;
         }
    } else {
         $name = trim($_POST['name']);
         $lat = floatval(trim($_POST['lat']));
         $lng = floatval(trim($_POST['lng']));
         if(empty($name) || empty($lat) || empty($lng)){
             $message = "Semua data harus diisi.";
         } else {
             $stmt = $conn->prepare("INSERT INTO locations (name, lat, lng) VALUES (?, ?, ?)");
             $stmt->bind_param("sdd", $name, $lat, $lng);
             if($stmt->execute()){
                  $message = "Lokasi berhasil ditambahkan.";
             } else {
                  $message = "Gagal menambahkan lokasi.";
             }
             $stmt->close();
         }
    }
}

$result = $conn->query("SELECT * FROM locations ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Lokasi - Admin Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
    .map-preview { width: 100%; height: 200px; border: none; }
  </style>
</head>
<body>
  <!-- Navbar Admin -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Admin Kuburan Pacitan</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item active"><a class="nav-link" href="manage_locations.php">Kelola Lokasi</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_paket.php">Kelola Paket</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_orders.php">Kelola Order</a></li>
         <li class="nav-item"><a class="nav-link" href="respon_user.php">Respon User</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['admin_username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  
  <div class="container">
    <h2>Kelola Lokasi</h2>
    <?php if($message != ''): ?>
      <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="manage_locations.php<?php echo ($edit_data ? '?edit='.$edit_data['id'] : ''); ?>">
      <?php if($edit_data): ?>
         <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
         <div class="form-group">
            <label>Edit Nama Lokasi:</label>
            <input type="text" name="name" class="form-control" value="<?php echo $edit_data['name']; ?>" required>
         </div>
         <div class="form-group">
            <label>Edit Latitude:</label>
            <input type="number" step="any" name="lat" class="form-control" value="<?php echo $edit_data['lat']; ?>" required>
         </div>
         <div class="form-group">
            <label>Edit Longitude:</label>
            <input type="number" step="any" name="lng" class="form-control" value="<?php echo $edit_data['lng']; ?>" required>
         </div>
         <button type="submit" class="btn btn-warning">Update Lokasi</button>
         <a href="manage_locations.php" class="btn btn-secondary">Batal</a>
      <?php else: ?>
         <div class="form-group">
            <label>Nama Lokasi:</label>
            <input type="text" name="name" class="form-control" placeholder="Masukkan nama lokasi" required>
         </div>
         <div class="form-group">
            <label>Latitude:</label>
            <input type="number" step="any" name="lat" class="form-control" placeholder="Contoh: -8.213017378418332" required>
         </div>
         <div class="form-group">
            <label>Longitude:</label>
            <input type="number" step="any" name="lng" class="form-control" placeholder="Contoh: 111.08048671888292" required>
         </div>
         <button type="submit" class="btn btn-primary">Tambah Lokasi</button>
         <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
      <?php endif; ?>
    </form>
    <hr>
    <h3>Daftar Lokasi</h3>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama Lokasi</th>
          <th>Latitude</th>
          <th>Longitude</th>
          <th>Preview Map</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td><?php echo $row['name']; ?></td>
          <td><?php echo $row['lat']; ?></td>
          <td><?php echo $row['lng']; ?></td>
          <td>
            <iframe class="map-preview" src="https://www.google.com/maps?q=<?php echo $row['lat']; ?>,<?php echo $row['lng']; ?>&output=embed"></iframe>
          </td>
          <td>
            <a href="manage_locations.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="manage_locations.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus lokasi ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
