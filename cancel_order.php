<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'student'){
    header("Location: index.php"); exit();
}

$sid = (int)$_SESSION['student']['id'];
$oid = (int)($_GET['id'] ?? 0);

$o = $conn->query("SELECT * FROM orders WHERE id=$oid AND student_id=$sid")->fetch_assoc();

if(!$o){
    $_SESSION['flash'] = "⚠️ Order not found.";
    header("Location: dashboard.php"); exit();
}

if($o['is_cancelled'] == 1){
    $_SESSION['flash'] = "⚠️ Order already cancelled.";
    header("Location: dashboard.php"); exit();
}

$now = date("Y-m-d H:i:s");
if($now > $o['cancel_until']){
    $_SESSION['flash'] = "⚠️ Cancel window expired. Order is locked.";
    header("Location: dashboard.php"); exit();
}

/* Cancel & refund */
$total    = (float)$o['total'];
$qty      = (int)$o['quantity'];
$pname    = $conn->real_escape_string($o['product_name']);

$conn->query("UPDATE orders SET is_cancelled=1, status='Cancelled' WHERE id=$oid");
$conn->query("UPDATE students SET balance=balance+$total WHERE id=$sid");
$conn->query("UPDATE products SET stock=stock+$qty, status='Available' WHERE product_name='$pname'");

/* Update session balance */
$fresh = $conn->query("SELECT balance FROM students WHERE id=$sid")->fetch_assoc();
$_SESSION['student']['balance'] = $fresh['balance'];

$_SESSION['flash'] = "✅ Order cancelled. ₱".number_format($total,2)." refunded to your wallet.";
header("Location: dashboard.php");
?>
