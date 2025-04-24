<?php
session_start();
require '../config.php';

if(isset($_SESSION['admin_id'])){
  header("Location: dashboard.php");
  exit();
}

$message = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  // Kredensial admin (contoh)
  if($username === 'admin' && $password === 'admin123'){
      $_SESSION['admin_id'] = 1;
      $_SESSION['admin_username'] = 'admin';
      header("Location: dashboard.php");
      exit();
  } else {
      $message = "Admin login gagal.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Reservasi Kuburan Pacitan</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .login-container { margin-top: 50px; }
  </style>
</head>
<body>
  <div class="container login-container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h2>Admin Login</h2>
        <?php if($message != ""): ?>
          <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
          <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Login Admin</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
