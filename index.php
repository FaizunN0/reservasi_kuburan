<?php
session_start();
// Jika mau ubah navbar Login/Daftar dinamis, bisa cek session di sini
require 'config.php';

// Ambil semua paket untuk ditampilkan di section Paket
$sql = "
  SELECT p.*, l.name AS lokasi_name
  FROM paket p
  LEFT JOIN locations l ON p.location_id = l.id
  ORDER BY p.id ASC
";
$res = $conn->query($sql);
$pakets = $res->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservasi Kuburan Pacitan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background-color: #07170f;
            color: white;
        }

        .navbar {
            background-color: #0f421c;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            position: relative;
            z-index: 2;
           
        }

        .navbar .logo {
            font-size: 20px;
            font-weight: thin;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            z-index: 1;
        }

        .nav-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: white;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 2;
            position: relative;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 14px 1cm;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                background-color: #2f353c;
                padding: 10px 0.5cm;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
            }

            .nav-links.active {
                display: flex;
            }

            .menu-toggle {
                display: block;
            }
            .hero h1 {
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #fff;
            }
        }

        .hero {
            text-align: center;
            padding: 80px 20px;
            background-color: #07170f;
        }

        .hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #fff;
        }

        .hero p {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 2rem;
        }

        .hero .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 28px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .hero .btn:hover {
            background-color: #0056b3;
        }

        .slideshow-container {
            max-width: 100%;
            position: relative;
            margin: auto;
        }

        .mySlides {
            display: none;
        }

        .rounded-img {
            width: calc(100% - 2cm);
            margin-left: 1cm;
            margin-right: 1cm;
            height: auto;
            border-radius: 20px;
            display: block;
        }

        @media (max-width: 600px) {
            .rounded-img {
                width: calc(100% - 1cm);
                margin-left: 0.5cm;
                margin-right: 0.5cm;
            }
            .navbar{
                width:88% ;
            }
            .hero h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #fff;
        }
        }

        .prev,
        .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            user-select: none;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }

        .next {
            right: 10px;
        }

        .prev {
            left: 10px;
        }

        .prev:hover,
        .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .text {
            color: #f2f2f2;
            font-size: 15px;
            padding: 8px 12px;
            position: absolute;
            bottom: 8px;
            width: 100%;
            text-align: center;
        }

        .numbertext {
            color: #f2f2f2;
            font-size: 12px;
            padding: 8px 12px;
            position: absolute;
            top: 0;
        }

        .paket-section {
            padding: 60px 20px;
            background-color: #07170f;
        }

        .paket-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .paket-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        .paket-card {
            background-color: #111;
            border-radius: 16px;
            padding: 20px;
            width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .paket-card:hover {
            transform: scale(1.03);
        }

        .paket-card h3 {
            margin-bottom: 10px;
        }

        .paket-image {
            width: 100%;
            height: 180px;
            background-color: #000;
            border-radius: 10px;
            margin-bottom: 16px;
            background-image: url(...);
            background-size: contain;      /* Menampilkan seluruh gambar, meski ada letterbox 🡒 tidak terpotong */ 
            background-repeat: no-repeat;  /* Mencegah pengulangan background */ 
            background-position: center;
        }

        .paket-card p {
            margin: 6px 0;
        }

        .paket-button {
            display: inline-block;
            margin-top: 16px;
            background-color: #0f421c;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        section#lokasi {
            padding: 60px 20px;
            background-color: #07170f;
        }

        .embed-responsive {
            position: relative;
            display: block;
            width: 100%;
            padding: 56.25% 0 0 0;
            overflow: hidden;
        }

        .embed-responsive-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        @media screen and (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                gap: 10px;
            }

            .paket-container {
                flex-direction: column;
                align-items: center;
            }

            .paket-button {
                margin-bottom: 10px;
            }
        }
        .text-center {
            text-align: center;
        }
         /* Tentang Kami */
         .about-section { padding: 60px 20px; background-color: #0a240f; text-align: center; }
        .about-section h2 { font-size: 2.5rem; margin-bottom: 20px; }
        .about-section p { max-width: 800px; margin: 0 auto; line-height: 1.6; color: #ccc; }
        /* Pilihan Peti */
        .coffin-section { padding: 60px 20px; background-color: #07170f; }
        .coffin-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; }
        .coffin-card { background-color: #111; border-radius: 16px; padding: 20px; width: 280px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5); text-align: center; }
        .coffin-card img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; }
        .coffin-card h4 { margin: 12px 0 8px; }
        .coffin-card p { font-size: 0.9rem; color: #ccc; }
        .coffin-card a { margin-top: 12px; display: inline-block; background: #0f421c; color: #fff;
            padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="logo">Reservasi Kuburan Pacitan</div>
        <button class="menu-toggle" id="menu-toggle">&#9776;</button>
        <div class="nav-links" id="nav-links">
            <a href="#">Beranda</a>
            <a href="#paket">Paket</a>
            <a href="#lokasi">Lokasi</a>
            <a href="login.php">Login</a>
            <a href="signup.php">Daftar</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Selamat Datang di Layanan Reservasi Kuburan Pacitan</h1>
        <p>Kami menyediakan layanan pemesanan makam yang lengkap, profesional, dan dilayani dengan sepenuh hati.</p>
        <a href="#paket" class="btn">Lihat Paket</a>
    </section>

    <div class="slideshow-container">
        <div class="mySlides fade">
            <div class="numbertext">1 / 3</div>
            <img src="img/k1.jpeg" class="rounded-img" />
            <div class="text">Area Pemakaman Lengkap</div>
        </div>
        <div class="mySlides fade">
            <div class="numbertext">2 / 3</div>
            <img src="img/k2.jpeg" class="rounded-img" />
            <div class="text">Fasilitas Modern & Terawat</div>
        </div>
        <div class="mySlides fade">
            <div class="numbertext">3 / 3</div>
            <img src="img/k3.jpeg" class="rounded-img" />
            <div class="text">Lingkungan Asri & Damai</div>
        </div>
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <section id="paket" class="paket-section">
    <h2 class="paket-title">Paket Kuburan</h2>
        <div class="paket-container">
          <?php foreach($pakets as $p): ?>
            <div class="paket-card">
              <h3><?= htmlspecialchars($p['nama']) ?></h3>
              <div class="paket-image"
                   style="background-image:url('uploads/<?= htmlspecialchars($p['image']) ?>')"></div>
              <p><strong>Deskripsi:</strong> <?= htmlspecialchars($p['deskripsi']) ?></p>
              <p><strong>Harga:</strong> Rp <?= number_format($p['harga'],0,',','.') ?></p>
              <p><strong>Lokasi:</strong> <?= htmlspecialchars($p['lokasi_name']) ?></p>
              <a href="order.php?paket=<?= $p['id'] ?>" class="paket-button">Pesan Sekarang</a>
            </div>
          <?php endforeach; ?>
        </div>
    </section>
        <!-- Tentang Kami -->
        <section id="tentang" class="about-section">
        <h2>Tentang Kami</h2>
        <p>
            Reservasi Kuburan Pacitan berdiri dengan komitmen melayani keluarga dalam momen paling penting dengan penuh empati  
            dan profesionalisme. Kami menawarkan berbagai paket pemakaman, mulai dari layanan standar hingga premium,  
            lengkap dengan dokumentasi lokasi, peti, dan doa bersama. Tim kami siap membantu setiap detail,  
            sehingga Anda dapat fokus pada penghormatan terakhir tanpa beban teknis.
        </p>
    </section>

    <!-- Pilihan Peti (statik) -->
    <section id="coffin" class="coffin-section">
        <h2 class="text-center text-white mb-4">Pilihan Peti</h2>
        <div class="coffin-container">
            <!-- Coffin A -->
            <div class="coffin-card">
                <img src="img/p_kayujati.jpg" alt="Peti Kayu Jati">
                <h4>Peti Kayu Jati</h4>
                <p>Kayu jati pilihan, tahan lama, dengan ukiran khas. Harga mulai Rp 2.500.000.</p>
                <a href="#paket">Pilih Paket</a>
            </div>
            <!-- Coffin B -->
            <div class="coffin-card">
                <img src="img/p_mahoni.jpg" alt="Peti Kayu Mahoni">
                <h4>Peti Kayu Mahoni</h4>
                <p>Kayu mahoni solid, desain elegan, finishing halus. Harga mulai Rp 1.800.000.</p>
                <a href="#paket">Pilih Paket</a>
            </div>
            <!-- Coffin C -->
            <div class="coffin-card">
                <img src="img/p_pinus.jpg" alt="Peti Kayu Pinus">
                <h4>Peti Kayu Pinus</h4>
                <p>Ekonomis, ringan, ramah anggaran. Harga mulai Rp 1.200.000.</p>
                <a href="#paket">Pilih Paket</a>
            </div>
        </div>
    </section>


    <section id="lokasi" class="section">
        <div class="container">
            <h2 class="text-center">Lokasi Kami</h2>
            <div class="embed-responsive embed-responsive-16by9">
        <iframe class="embed-responsive-item" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126743.44851546555!2d110.0!3d-8.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a0e0000000001%3A0x123456789abcdef!2sPacitan%2C%20Indonesia!5e0!3m2!1sen!2sus!4v1610000000000" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
        </div>
        </div>
    </section>

    <script>
        let slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            let slides = document.getElementsByClassName("mySlides");
            if (n > slides.length) { slideIndex = 1; }
            if (n < 1) { slideIndex = slides.length; }
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slides[slideIndex - 1].style.display = "block";
        }

        setInterval(() => {
            plusSlides(1);
        }, 5000);

        const toggle = document.getElementById('menu-toggle');
        const nav = document.getElementById('nav-links');
        toggle.addEventListener('click', () => nav.classList.toggle('active'));
    </script>
</body>

</html>
