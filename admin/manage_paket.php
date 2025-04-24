<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require '../config.php';

// Folder upload relatif ke file ini
$uploadDir = __DIR__ . '/../uploads/';
// Pastikan folder ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$message = '';

// 1) Hapus paket + file gambarnya
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // ambil nama file di DB
    $row = $conn->query("SELECT image FROM paket WHERE id = $id")->fetch_assoc();
    if ($row && $row['image']) {
        @unlink($uploadDir . $row['image']);
    }
    $stmt = $conn->prepare("DELETE FROM paket WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = $stmt->affected_rows
        ? "Paket berhasil dihapus."
        : "Gagal menghapus paket.";
    $stmt->close();
}

// 2) Jika edit mode, ambil data lama
$edit = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM paket WHERE id = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// 3) Ambil list lokasi
$locations = [];
$res = $conn->query("SELECT * FROM locations ORDER BY name ASC");
while ($loc = $res->fetch_assoc()) {
    $locations[] = $loc;
}
$res->free();

// 4) Proses form tambah / edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = trim($_POST['nama']);
    $deskripsi    = trim($_POST['deskripsi']);
    $stok         = intval($_POST['stok_kapling']);
    $harga        = floatval($_POST['harga']);
    $loc_id       = intval($_POST['location_id']);

    // default image = gambar lama (jika edit) atau kosong
    $imageName = $edit['image'] ?? '';

    // jika ada upload baru
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // hapus gambar lama
        if ($edit && $edit['image']) {
            @unlink($uploadDir . $edit['image']);
        }
        // simpan file baru
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('pkg_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }

    // validasi sederhana
    if ($nama === '' || $deskripsi === '' || $stok < 1 || $harga <= 0 || !$loc_id || $imageName === '') {
        $message = "Semua field (termasuk gambar) harus diisi.";
    } else {
        if ($edit) {
            // UPDATE: tipe ssidisi (s,s,i,d,i,s,i)
            $stmt = $conn->prepare("
                UPDATE paket
                   SET nama=?, deskripsi=?, stok_kapling=?, harga=?, location_id=?, image=?
                 WHERE id=?
            ");
            $stmt->bind_param(
                "ssidisi",
                $nama, $deskripsi, $stok, $harga, $loc_id, $imageName, $edit['id']
            );
            $stmt->execute();
            $message = $stmt->affected_rows
                ? "Paket berhasil diupdate."
                : "Tidak ada perubahan.";
            $stmt->close();
            header("Location: manage_paket.php");
            exit;
        } else {
            // INSERT: tipe ssidis (s,s,i,d,i,s)
            $stmt = $conn->prepare("
                INSERT INTO paket (nama, deskripsi, stok_kapling, harga, location_id, image)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssidis",
                $nama, $deskripsi, $stok, $harga, $loc_id, $imageName
            );
            $stmt->execute();
            $message = $stmt->affected_rows
                ? "Paket berhasil ditambahkan."
                : "Gagal menambahkan paket.";
            $stmt->close();
        }
    }
}

// 5) Fetch semua paket
$sql = "
  SELECT p.*, l.name AS lokasi_name
  FROM paket p
  LEFT JOIN locations l ON p.location_id = l.id
  ORDER BY p.id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Paket</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .container { margin-top: 30px; }
    img.thumb { max-width: 150px; object-fit: cover; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="dashboard.php">Admin Kuburan</a>
</nav>
<div class="container">
  <h2>Kelola Paket</h2>
  <?php if($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <!-- Form Tambah/Edit -->
  <form method="post" action="manage_paket.php<?= $edit ? '?edit='.$edit['id'] : '' ?>"
        enctype="multipart/form-data">
    <?php if($edit): ?>
      <input type="hidden" name="edit_id" value="<?= $edit['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
      <label>Nama Paket</label>
      <input type="text" name="nama" class="form-control" required
             value="<?= htmlspecialchars($edit['nama'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Deskripsi</label>
      <textarea name="deskripsi" class="form-control" required><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label>Stok Kapling</label>
      <input type="number" name="stok_kapling" class="form-control" required
             value="<?= htmlspecialchars($edit['stok_kapling'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Harga</label>
      <input type="number" step="0.01" name="harga" class="form-control" required
             value="<?= htmlspecialchars($edit['harga'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Lokasi Paket</label>
      <select name="location_id" class="form-control" required>
        <option value="">-- Pilih Lokasi --</option>
        <?php foreach($locations as $loc): ?>
          <option value="<?= $loc['id'] ?>"
            <?= (isset($edit['location_id']) && $edit['location_id']==$loc['id'])?'selected':''?>>
            <?= htmlspecialchars($loc['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label><?= $edit ? 'Ganti' : 'Upload' ?> Gambar Paket</label>
      <?php if($edit && $edit['image']): ?>
        <div class="mb-2">
          <img src="../uploads/<?= htmlspecialchars($edit['image']) ?>" class="thumb">
        </div>
      <?php endif; ?>
      <input type="file" name="image" class="form-control-file"
             <?= $edit ? '' : 'required' ?> accept="image/*">
    </div>
    <button class="btn btn-<?= $edit?'warning':'primary' ?>">
      <?= $edit?'Update':'Tambah' ?> Paket
    </button>
    <?php if($edit): ?>
      <a href="manage_paket.php" class="btn btn-secondary">Batal</a>
    <?php endif; ?>
  </form>

  <hr>
  <h3>Daftar Paket</h3>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th><th>Gambar</th><th>Nama</th><th>Deskripsi</th>
        <th>Stok</th><th>Harga</th><th>Lokasi</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td>
            <?php if($row['image']): ?>
              <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="thumb">
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['deskripsi']) ?></td>
          <td><?= $row['stok_kapling'] ?></td>
          <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
          <td><?= htmlspecialchars($row['lokasi_name']) ?></td>
          <td>
            <a href="manage_paket.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="manage_paket.php?delete=<?= $row['id'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Yakin hapus?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
