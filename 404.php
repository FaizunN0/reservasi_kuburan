<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>404 - Halaman Tidak Ditemukan | Reservasi Kuburan Pacitan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      color: #333;
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      text-align: center;
      padding: 50px;
    }
    .error-container {
      margin: 0 auto;
      max-width: 600px;
    }
    .error-code {
      font-size: 100px;
      font-weight: bold;
      color: #dc3545;
    }
    .error-message {
      font-size: 24px;
      margin-bottom: 30px;
    }
    .btn-home {
      margin-top: 20px;
    }
    .search-box {
      max-width: 400px;
      margin: 20px auto;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-code">404</div>
    <div class="error-message">Halaman yang Anda cari tidak ditemukan</div>
    <p>Maaf, halaman yang Anda minta tidak tersedia. Mungkin URL salah ketik atau halaman telah dihapus.</p>
    <button onclick="goBack()">Kembali</button>
  </div>

  <script>
    function goBack() {
      window.history.back();
    }
  </script>
  <!-- Optional JavaScript -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
