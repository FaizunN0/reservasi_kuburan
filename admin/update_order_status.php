<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    echo "unauthorized";
    exit();
}
require '../config.php';

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])){
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    $allowed = ['belum dikerjakan', 'dikerjakan', 'selesai'];
    if(!in_array($status, $allowed)){
        echo "Invalid status";
        exit();
    }
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if($stmt->execute()){
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
} else {
    echo "error";
}
?>
