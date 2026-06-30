<?php
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("DELETE FROM cakes WHERE id = ?");
$stmt->execute([$id]);

header('Location: dashboard.php');
exit();
?>