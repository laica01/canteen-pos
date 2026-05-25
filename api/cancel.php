<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){ http_response_code(200); exit; }

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$oid  = (int)($data['order_id']  ?? 0);
$sid  = (int)($data['student_id'] ?? 0);

if(!$oid || !$sid){
    echo json_encode(["success"=>false,"message"=>"Missing fields."]);
    exit;
}

$o = $conn->query("SELECT * FROM orders WHERE id=$oid AND student_id=$sid")->fetch_assoc();

if(!$o){ echo json_encode(["success"=>false,"message"=>"Order not found."]); exit; }
if($o['is_cancelled'] == 1){ echo json_encode(["success"=>false,"message"=>"Already cancelled."]); exit; }

$now = date("Y-m-d H:i:s");
if($now > $o['cancel_until']){
    echo json_encode(["success"=>false,"message"=>"Cancel window expired."]);
    exit;
}

$total = (float)$o['total'];
$qty   = (int)$o['quantity'];
$pname = $conn->real_escape_string($o['product_name']);

$conn->query("UPDATE orders SET is_cancelled=1, status='Cancelled' WHERE id=$oid");
$conn->query("UPDATE students SET balance=balance+$total WHERE id=$sid");
$conn->query("UPDATE products SET stock=stock+$qty, status='Available' WHERE product_name='$pname'");

$fresh      = $conn->query("SELECT balance FROM students WHERE id=$sid")->fetch_assoc();
$newBalance = (float)$fresh['balance'];

echo json_encode([
    "success"     => true,
    "message"     => "Cancelled! ₱".number_format($total,2)." refunded.",
    "new_balance" => $newBalance,
]);
?>