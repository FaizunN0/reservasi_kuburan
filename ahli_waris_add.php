<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
require 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nama = trim($_POST['nama']);
    $hubungan = trim($_POST['hubungan']);
    if(empty($nama) || empty($hubungan)){
        $message = "Semua data harus diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO ahli_waris (user_id, nama, hubungan) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $nama, $hubungan);
        if($stmt->execute()){
            header("Location: ahli_waris.php?msg=Data berhasil ditambahkan");
            exit();
        } else {
            $message = "Gagal menambahkan data.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Ahli Waris</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-4">
    <h2>Tambah Data Ahli Waris</h2>
    <?php if(isset($message)): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="ahli_waris_add.php">
      <div class="form-group">
         <label>Nama:</label>
         <input type="text" name="nama" class="form-control" required>
      </div>
      <div class="form-group">
         <label>Hubungan:</label>
         <input type="text" name="hubungan" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Tambah</button>
      <a href="ahli_waris.php" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</body>
</html>
