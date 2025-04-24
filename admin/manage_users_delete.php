<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

if(!isset($_GET['user_id'])){
  die("User ID tidak tersedia.");
}
$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
if($stmt->execute()){
  header("Location: manage_users.php?msg=User deleted successfully");
} else {
  header("Location: manage_users.php?msg=Failed to delete user");
}
$stmt->close();
?>
