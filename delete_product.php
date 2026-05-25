<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'admin'){
    header("Location: index.php"); exit();
}

$id = (int)($_GET['id'] ?? 0);

if($id > 0){
    /* Optionally delete the image file too */
    $p = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    if($p && !empty($p['image']) && file_exists("images/".$p['image'])){
        unlink("images/".$p['image']);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
}

header("Location: admin.php?deleted=1");
?>
