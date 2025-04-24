<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

$sql = "SELECT m.*, u.username FROM messages m JOIN user u ON m.user_id = u.user_id ORDER BY m.created_at DESC";
$result = $conn->query($sql);
$messages = [];
while($row = $result->fetch_assoc()){
    $messages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Inbox & Respon - Admin</title>
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
         <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
         <li class="nav-item"><a class="nav-link" href="manage_orders.php">Kelola Order</a></li>
         <li class="nav-item active"><a class="nav-link" href="respon_user.php">Inbox & Respon</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
         <li class="nav-item"><span class="navbar-text mr-3">Halo, <?php echo $_SESSION['admin_username']; ?></span></li>
         <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="container">
    <h2>Inbox Pesan User</h2>
    <?php if(count($messages)==0): ?>
      <div class="alert alert-info">Tidak ada pesan.</div>
    <?php else: ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Subjek</th>
            <th>Pesan</th>
            <th>Tanggal</th>
            <th>Balasan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($messages as $msg): ?>
            <tr>
              <td><?php echo $msg['id']; ?></td>
              <td><?php echo $msg['username']; ?></td>
              <td><?php echo htmlspecialchars($msg['subject']); ?></td>
              <td><?php echo htmlspecialchars($msg['message']); ?></td>
              <td><?php echo $msg['created_at']; ?></td>
              <td><?php echo !empty($msg['reply']) ? htmlspecialchars($msg['reply']) : 'Belum dibalas'; ?></td>
              <td>
                <a href="respon_user.php?reply_id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-primary">Balas</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php
    if(isset($_GET['reply_id'])){
      $reply_id = intval($_GET['reply_id']);
      $stmtReply = $conn->prepare("SELECT * FROM messages WHERE id = ?");
      $stmtReply->bind_param("i", $reply_id);
      $stmtReply->execute();
      $resultReply = $stmtReply->get_result();
      if($resultReply->num_rows > 0){
        $replyMsg = $resultReply->fetch_assoc();
      }
      $stmtReply->close();
    ?>
      <div class="card mt-4">
        <div class="card-header">Balas Pesan - ID <?php echo $reply_id; ?></div>
        <div class="card-body">
          <form method="POST" action="respon_user_process.php">
            <input type="hidden" name="message_id" value="<?php echo $reply_id; ?>">
            <div class="form-group">
              <label>Subjek Balasan:</label>
              <input type="text" name="reply_subject" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Isi Balasan:</label>
              <textarea name="reply_message" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Kirim Balasan</button>
          </form>
        </div>
      </div>
    <?php } ?>
  </div>
</body>
</html>
