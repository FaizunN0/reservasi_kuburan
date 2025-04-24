<?php
// index.php - Front Controller untuk Reservasi Kuburan V2

// Daftar rute yang tersedia. 
// Sesuaikan dengan file-file yang ada pada proyek Anda.
$routes = [
    "/" => "home.php",
    "/index.php" => "home.php",
    "/dashboard.php" => "dashboard.php",
    "/login.php" => "login.php",
    "/signup.php" => "signup.php",
    "/order.php" => "order.php",
    "/orders.php" => "orders.php",
    // Rute halaman detail order untuk user
    "/order_detail.php" => "order_detail.php",
    // Rute file untuk user lainnya, misalnya logout.php
    "/logout.php" => "logout.php",
    // Rute untuk admin (sesuaikan dengan struktur folder admin)
    "/admin/dashboard.php" => "admin/dashboard.php",
    "/admin/login.php" => "admin/login.php",
    "/admin/logout.php" => "admin/logout.php",
    "/admin/manage_orders.php" => "admin/manage_orders.php",
    "/admin/manage_users.php" => "admin/manage_users.php",
    "/admin/manage_locations.php" => "admin/manage_locations.php",
    "/admin/manage_paket.php" => "admin/manage_paket.php",
    "/admin/respon_user.php" => "admin/respon_user.php",
    "/admin/invoice.php" => "admin/invoice.php",
    // Rute update status (AJAX) untuk admin
    "/admin/update_order_status.php" => "admin/update_order_status.php",
    // Rute lain dapat ditambahkan di sini...
];

// Ambil path dari URL yang diminta
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Jika aplikasi berada di subfolder, hapus prefix subfolder dari URI
// Misalnya, jika URL: http://localhost/reservasi_kuburan/v2/dashboard.php
// dan proyek Anda berada di subfolder /reservasi_kuburan/v2, maka:
 $basePath = '/reservasi_kuburan/v2';
 if(strpos($requestUri, $basePath) === 0){
    $requestUri = substr($requestUri, strlen($basePath));
 }
// Jika proyek Anda diletakkan langsung di root (misalnya, http://localhost/), maka kode di atas tidak diperlukan.

// Cek apakah URI yang diminta ada di daftar rute
if(array_key_exists($requestUri, $routes)) {
    include $routes[$requestUri];
} else {
    // Jika tidak ada, kirim header 404 dan tampilkan halaman error 404
    http_response_code(404);
    include '404.php';
}
?>