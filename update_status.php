<?php
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

$allowed_status = ['pending', 'processing', 'completed', 'cancelled'];

if(in_array($status, $allowed_status)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header('Location: dashboard.php');
exit();
?>