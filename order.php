<?php
session_start();
include 'db.php';

if(!isset($_SESSION['student']) || $_SESSION['student']['role'] != 'student'){
    header("Location: index.php"); exit();
}

$student = $_SESSION['student'];
$sid = (int)$student['id'];

/* Validate inputs */
if(!isset($_POST['product_id'], $_POST['qty'])){
    header("Location: dashboard.php"); exit();
}

$pid = (int)$_POST['product_id'];
$qty = max(1, (int)$_POST['qty']);

/* Get fresh product */
$p = $conn->query("SELECT * FROM products WHERE id=$pid")->fetch_assoc();
if(!$p){ header("Location: dashboard.php"); exit(); }

/* Get fresh student balance */
$freshStudent = $conn->query("SELECT * FROM students WHERE id=$sid")->fetch_assoc();
$balance = (float)$freshStudent['balance'];

$total = (float)$p['price'] * $qty;

/* Checks */
if($p['stock'] < $qty){
    $_SESSION['flash'] = "⚠️ Sorry, only {$p['stock']} left in stock!";
    header("Location: dashboard.php"); exit();
}

if($balance < $total){
    $_SESSION['flash'] = "😢 Insufficient balance! You need ₱".number_format($total,2)." but only have ₱".number_format($balance,2).".";
    header("Location: dashboard.php"); exit();
}

/* Deduct balance */
$newBalance = $balance - $total;
$conn->query("UPDATE students SET balance=$newBalance WHERE id=$sid");

/* Update stock */
$newStock = (int)$p['stock'] - $qty;
$status   = ($newStock <= 0) ? 'Sold Out' : 'Available';
$conn->query("UPDATE products SET stock=$newStock, status='$status' WHERE id=$pid");

/* Cancel window: 1 minute */
$cancelUntil = date("Y-m-d H:i:s", strtotime("+1 minute"));
$pname = $conn->real_escape_string($p['product_name']);

$conn->query("INSERT INTO orders(student_id, product_name, quantity, total, status, cancel_until, is_cancelled, created_at)
              VALUES($sid, '$pname', $qty, $total, 'Preparing', '$cancelUntil', 0, NOW())");

/* Update session */
$_SESSION['student']['balance'] = $newBalance;
$_SESSION['flash'] = "✅ Order placed! {$p['product_name']} x{$qty} — ₱".number_format($total,2)." deducted.";

header("Location: dashboard.php");
?>
