<?php
session_start();
require 'config.php';

$message = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $nama = trim($_POST['nama']);
  $tgl_lahir = trim($_POST['tgl_lahir']);
  $no_ktp = trim($_POST['no_ktp']);
  
  $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  if($stmt->num_rows > 0){
    $message = "Username sudah ada.";
  } else {
    $stmt->close();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO user (username, password, nama, tgl_lahir, no_ktp) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashed_password, $nama, $tgl_lahir, $no_ktp);
    if($stmt->execute()){
      $_SESSION['user_id'] = $stmt->insert_id;
      $_SESSION['username'] = $username;
      header("Location: dashboard.php");
      exit();
    } else {
      $message = "Gagal mendaftar.";
    }
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .signup-container { margin-top: 100px; }
  </style>
</head>
<body>
  <div class="container signup-container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h2 class="text-center">Daftar</h2>
        <?php if($message != ""): ?>
          <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="signup.php">
          <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Nama Lengkap:</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Tanggal Lahir:</label>
            <input type="date" name="tgl_lahir" class="form-control" required>
          </div>
          <div class="form-group">
            <label>No KTP:</label>
            <input type="text" name="no_ktp" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Daftar</button>
          <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
