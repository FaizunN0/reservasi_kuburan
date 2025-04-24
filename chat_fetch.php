<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;
require 'config.php';

$mode = (isset($_GET['mode']) && $_GET['mode']==='global') ? 1 : 0;
$user_id = $_SESSION['user_id'];

if ($mode) {
    // Global chat: semua pesan global
    $stmt = $conn->prepare(
      "SELECT cm.*, u.username 
       FROM chat_messages cm 
       JOIN user u ON cm.user_id=u.user_id 
       WHERE cm.is_global=1 
       ORDER BY cm.created_at ASC"
    );
} else {
    // Private chat: hanya antara user ini dan admin
    $stmt = $conn->prepare(
      "SELECT cm.*, u.username 
       FROM chat_messages cm 
       JOIN user u ON cm.user_id=u.user_id 
       WHERE cm.is_global=0 
         AND (cm.user_id=? OR cm.sender='admin')
       ORDER BY cm.created_at ASC"
    );
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while($row = $res->fetch_assoc()){
    $messages[] = $row;
}
header('Content-Type: application/json');
echo json_encode($messages);
