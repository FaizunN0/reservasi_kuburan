<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    echo "unauthorized";
    exit();
}
require '../config.php';

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['response'])){
    $order_id = intval($_POST['order_id']);
    $response = trim($_POST['response']);
    $stmt = $conn->prepare("UPDATE orders SET admin_response = ? WHERE id = ?");
    $stmt->bind_param("si", $response, $order_id);
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
