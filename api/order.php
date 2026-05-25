<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){ http_response_code(200); exit; }

include '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$sid  = (int)($data['student_id'] ?? 0);
$pid  = (int)($data['product_id'] ?? 0);
$qty  = max(1, (int)($data['qty'] ?? 1));

if(!$sid || !$pid){
    echo json_encode(["success"=>false,"message"=>"Missing fields."]);
    exit;
}

$student = $conn->query("SELECT * FROM students WHERE id=$sid")->fetch_assoc();
$p       = $conn->query("SELECT * FROM products WHERE id=$pid")->fetch_assoc();

if(!$student){ echo json_encode(["success"=>false,"message"=>"Student not found."]); exit; }
if(!$p)      { echo json_encode(["success"=>false,"message"=>"Product not found."]); exit; }

if((int)$p['stock'] < $qty){
    echo json_encode(["success"=>false,"message"=>"Only {$p['stock']} left in stock."]);
    exit;
}

$total   = (float)$p['price'] * $qty;
$balance = (float)$student['balance'];

if($balance < $total){
    echo json_encode(["success"=>false,"message"=>"Not enough balance."]);
    exit;
}

$newBalance  = $balance - $total;
$newStock    = (int)$p['stock'] - $qty;
$status      = $newStock <= 0 ? 'Sold Out' : 'Available';
$cancelUntil = date("Y-m-d H:i:s", strtotime("+1 minute"));
$pname       = $conn->real_escape_string($p['product_name']);

$conn->query("UPDATE students SET balance=$newBalance WHERE id=$sid");
$conn->query("UPDATE products SET stock=$newStock, status='$status' WHERE id=$pid");
$conn->query("INSERT INTO orders(student_id,product_name,quantity,total,status,cancel_until,is_cancelled,created_at)
              VALUES($sid,'$pname',$qty,$total,'Preparing','$cancelUntil',0,NOW())");

echo json_encode([
    "success"     => true,
    "message"     => "Order placed! {$p['product_name']} x{$qty}",
    "new_balance" => $newBalance,
]);
?>