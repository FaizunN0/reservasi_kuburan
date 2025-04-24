<?php
session_start();
if(!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit();
}
require 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
  $nama = trim($_POST['nama']);
  $hubungan = trim($_POST['hubungan']);
  if(empty($nama) || empty($hubungan)){
    header("Location: ahli_waris.php?msg=Data%20tidak%20lengkap");
    exit();
  }
  $stmt = $conn->prepare("INSERT INTO ahli_waris (user_id, nama, hubungan) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $_SESSION['user_id'], $nama, $hubungan);
  if($stmt->execute()){
    header("Location: ahli_waris.php?msg=Berhasil%20menambah%20data");
    exit();
  } else {
    header("Location: ahli_waris.php?msg=Gagal%20menambah%20data");
    exit();
  }
}
?>
