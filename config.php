<?php
// config.php
// 1. Tentukan path folder uploads relatif terhadap file ini
$uploadDir = __DIR__ . '/uploads';

// 2. Periksa apakah folder uploads sudah ada
if (!is_dir($uploadDir)) {
    // 3. Jika belum ada, buat folder tersebut
    //    - mode 0755: owner read/write/execute; group+others read/execute
    //    - true: membuat subfolder rekursif jika diperlukan
    if (!mkdir($uploadDir, 0755, true)) {
        // 4. Jika gagal membuat folder, hentikan eksekusi dan tampilkan error
        die("Gagal membuat folder uploads. Periksa permissions server.");
    }
}

// 5. (Opsional) Pastikan folder bisa ditulisi web server
//    Pada sebagian lingkungan, Anda mungkin perlu chmod secara manual.
//    Jika di Linux dan masih perlu write, uncomment baris berikut:
// chmod($uploadDir, 0755);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host     = 'localhost';
$user     = 'root';
$password = ''; // Sesuaikan dengan konfigurasi Anda
$dbname   = 'reservasi_kuburan_v2';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
