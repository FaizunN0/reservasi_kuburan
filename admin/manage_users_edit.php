<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

if(!isset($_GET['user_id'])){
  die("User ID tidak tersedia.");
}
$user_id = intval($_GET['user_id']);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nama = trim($_POST['nama']);
    $tgl_lahir = trim($_POST['tgl_lahir']);
    $no_ktp = trim($_POST['no_ktp']);
    if(empty($nama) || empty($tgl_lahir) || empty($no_ktp)){
        $message = "Semua data harus diisi.";
    } else {
        $stmt = $conn->prepare("UPDATE user SET nama = ?, tgl_lahir = ?, no_ktp = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $nama, $tgl_lahir, $no_ktp, $user_id);
        if($stmt->execute()){
            header("Location: manage_users.php?msg=User updated successfully");
            exit();
        } else {
            $message = "Gagal mengupdate user.";
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    die("User tidak ditemukan.");
}
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit User - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-4">
    <h2>Edit Data User</h2>
    <?php if(isset($message)): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="manage_users_edit.php?user_id=<?php echo $user_id; ?>">
      <div class="form-group">
         <label>Nama Lengkap:</label>
         <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
      </div>
      <div class="form-group">
         <label>Tanggal Lahir:</label>
         <input type="date" name="tgl_lahir" class="form-control" value="<?php echo $user['tgl_lahir']; ?>" required>
      </div>
      <div class="form-group">
         <label>No KTP:</label>
         <input type="text" name="no_ktp" class="form-control" value="<?php echo htmlspecialchars($user['no_ktp']); ?>" required>
      </div>
      <button type="submit" class="btn btn-success">Update User</button>
      <a href="manage_users.php" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</body>
</html>
