<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Live Chat - Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS & jQuery -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .chat-window { height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background: #fff; }
    .chat-input { margin-top: 10px; }
    .chat-msg { margin-bottom: 8px; }
    .chat-msg strong { color: #007bff; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="dashboard.php">Reservasi Kuburan Pacitan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav" aria-controls="nav"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item active"><a class="nav-link" href="live_chat.php">Live Chat</a></li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><span class="navbar-text">Halo, <?php echo $_SESSION['username']; ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4">
    <h2>Live Chat</h2>
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="chatTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="global-tab" data-toggle="tab" href="#global" role="tab">Global Chat</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="admin-tab" data-toggle="tab" href="#admin" role="tab">Chat Admin</a>
      </li>
    </ul>
    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Global Chat -->
      <div class="tab-pane fade show active" id="global" role="tabpanel">
        <div id="globalWindow" class="chat-window"></div>
        <div class="input-group chat-input">
          <input type="text" id="globalInput" class="form-control" placeholder="Tulis pesan...">
          <div class="input-group-append">
            <button id="globalSend" class="btn btn-primary">Kirim</button>
          </div>
        </div>
      </div>
      <!-- Chat Admin -->
      <div class="tab-pane fade" id="admin" role="tabpanel">
        <div id="adminWindow" class="chat-window"></div>
        <div class="input-group chat-input">
          <input type="text" id="adminInput" class="form-control" placeholder="Tulis pesan untuk admin...">
          <div class="input-group-append">
            <button id="adminSend" class="btn btn-primary">Kirim</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery & Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
    // Fungsi fetch pesan
    function fetchMessages(mode) {
      $.getJSON('chat_fetch.php', { mode: mode }, function(data) {
        var win = (mode === 'global') ? $('#globalWindow') : $('#adminWindow');
        win.empty();
        data.forEach(function(msg) {
          var sender = (msg.sender === 'admin') ? '<strong>Admin</strong>' : '<strong>' + msg.username + '</strong>';
          win.append('<div class="chat-msg">' + sender + ': ' + $('<div>').text(msg.message).html() + '</div>');
        });
        win.scrollTop(win[0].scrollHeight);
      });
    }

    // Kirim pesan
    function sendMessage(mode) {
      var input = (mode === 'global') ? $('#globalInput') : $('#adminInput');
      var text = input.val().trim();
      if (!text) return;
      $.post('chat_send.php', { mode: mode, message: text }, function() {
        input.val('');
        fetchMessages(mode);
      });
    }

    $(function(){
      // Inisialisasi fetch
      fetchMessages('global');
      fetchMessages('admin');
      // Interval polling tiap 2 detik
      setInterval(function(){
        fetchMessages('global');
        fetchMessages('admin');
      }, 2000);

      // Tombol kirim
      $('#globalSend').click(function(){ sendMessage('global'); });
      $('#adminSend').click(function(){ sendMessage('admin'); });
      // Enter untuk kirim
      $('#globalInput').keypress(function(e){ if(e.which===13) sendMessage('global'); });
      $('#adminInput').keypress(function(e){ if(e.which===13) sendMessage('admin'); });
    });
  </script>
</body>
</html>
