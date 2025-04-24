<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;
require 'config.php';

$user_id = $_SESSION['user_id'];
$mode    = (isset($_POST['mode']) && $_POST['mode']==='global') ? 1 : 0;
$message = trim($_POST['message']);
if ($message==='') exit;

$sender = 'user';
$stmt = $conn->prepare(
  "INSERT INTO chat_messages (user_id, sender, is_global, message) 
   VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isis", $user_id, $sender, $mode, $message);
$stmt->execute();
echo 'success';
