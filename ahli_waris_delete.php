<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'config.php';

// Pastikan ada parameter id
if (!isset($_GET['id'])) {
    header("Location: ahli_waris.php");
    exit();
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Hapus hanya jika milik user yang sedang login
$stmt = $conn->prepare("DELETE FROM ahli_waris WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$stmt->close();

// Redirect kembali dengan pesan
header("Location: ahli_waris.php?msg=Data ahli waris berhasil dihapus");
exit();
?>
