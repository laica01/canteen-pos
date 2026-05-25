<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

$id    = (int)($_POST['product_id'] ?? 0);
$stock = max(0, (int)($_POST['stock'] ?? 0));

if($id > 0){
    $status = $stock > 0 ? 'Available' : 'Sold Out';
    $conn->query("UPDATE products SET stock=$stock, status='$status' WHERE id=$id");
}

header("Location: admin.php?updated=1");
?>
