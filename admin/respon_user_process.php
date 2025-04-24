<?php
session_start();
if(!isset($_SESSION['admin_id'])){
  header("Location: login.php");
  exit();
}
require '../config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $message_id = intval($_POST['message_id']);
    $reply_subject = trim($_POST['reply_subject']);
    $reply_message = trim($_POST['reply_message']);
    if(empty($reply_subject) || empty($reply_message)){
        header("Location: respon_user.php?msg=Data tidak lengkap");
        exit();
    }
    $stmt = $conn->prepare("UPDATE messages SET reply = ? WHERE id = ?");
    $stmt->bind_param("si", $reply_message, $message_id);
    if($stmt->execute()){
        header("Location: respon_user.php?msg=Balasan terkirim");
    } else {
        header("Location: respon_user.php?msg=Gagal mengirim balasan");
    }
    $stmt->close();
} else {
    header("Location: respon_user.php");
}
?>
