<?php
session_start();
require 'config.php';

$message = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $stmt = $conn->prepare("SELECT user_id, password FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  if($stmt->num_rows > 0){
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();
    if(password_verify($password, $hashed_password)){
      $_SESSION['user_id'] = $user_id;
      $_SESSION['username'] = $username;
      header("Location: dashboard.php");
      exit();
    } else {
      $message = "Password salah.";
    }
  } else {
    $message = "Username tidak ditemukan.";
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .login-container { margin-top: 100px; }
  </style>
</head>
<body>
  <div class="container login-container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h2 class="text-center">Login</h2>
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
          <button type="submit" class="btn btn-primary btn-block">Login</button>
          <p class="text-center mt-3">Belum punya akun? <a href="signup.php">Daftar</a></p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
