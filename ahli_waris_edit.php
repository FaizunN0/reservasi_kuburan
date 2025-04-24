<?php
session_start();
if(!isset($_SESSION['user_id'])){ 
    header("Location: login.php");
    exit();
}
require 'config.php';

if(!isset($_GET['id'])){
    die("ID tidak tersedia.");
}
$id = intval($_GET['id']);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nama = trim($_POST['nama']);
    $hubungan = trim($_POST['hubungan']);
    if(empty($nama) || empty($hubungan)){
        $message = "Semua data harus diisi.";
    } else {
        $stmt = $conn->prepare("UPDATE ahli_waris SET nama = ?, hubungan = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $nama, $hubungan, $id, $_SESSION['user_id']);
        if($stmt->execute()){
            header("Location: ahli_waris.php?msg=Data berhasil diupdate");
            exit();
        } else {
            $message = "Gagal mengupdate data.";
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT * FROM ahli_waris WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    die("Data tidak ditemukan.");
}
$data = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Ahli Waris</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-4">
    <h2>Edit Data Ahli Waris</h2>
    <?php if(isset($message)): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" action="ahli_waris_edit.php?id=<?php echo $id; ?>">
      <div class="form-group">
         <label>Nama:</label>
         <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
      </div>
      <div class="form-group">
         <label>Hubungan:</label>
         <input type="text" name="hubungan" class="form-control" value="<?php echo htmlspecialchars($data['hubungan']); ?>" required>
      </div>
      <button type="submit" class="btn btn-success">Update</button>
      <a href="ahli_waris.php" class="btn btn-secondary">Kembali</a>
    </form>
  </div>
</body>
</html>
