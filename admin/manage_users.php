<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

$sql = "SELECT * FROM user ORDER BY user_id DESC";
$result = $conn->query($sql);
$users = [];
while($row = $result->fetch_assoc()){
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola User - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
  </style>
</head>
<body>
  <!-- Navbar Admin -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Admin Kuburan Pacitan</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item active"><a class="nav-link" href="manage_users.php">Kelola User</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text mr-3">Halo, <?php echo $_SESSION['admin_username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h2>Kelola Data User</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Tanggal Lahir</th>
          <th>No KTP</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($users as $user): ?>
        <tr>
          <td><?php echo $user['user_id']; ?></td>
          <td><?php echo $user['username']; ?></td>
          <td><?php echo $user['nama']; ?></td>
          <td><?php echo $user['tgl_lahir']; ?></td>
          <td><?php echo $user['no_ktp']; ?></td>
          <td>
            <a href="manage_users_edit.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
            <a href="manage_users_delete.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
