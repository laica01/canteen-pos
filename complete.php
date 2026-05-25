<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

$id = (int)($_GET['id'] ?? 0);
$conn->query("UPDATE orders SET status='Completed' WHERE id=$id");
header("Location: admin.php");
?>
