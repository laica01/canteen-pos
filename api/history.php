<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include '../db.php';

$sid = (int)($_GET['student_id'] ?? 0);

if(!$sid){
    echo json_encode(["success"=>false,"message"=>"student_id is required."]);
    exit;
}

$now    = date("Y-m-d H:i:s");
$orders = $conn->query("SELECT * FROM orders WHERE student_id=$sid ORDER BY id DESC");

$list = [];
while($o = $orders->fetch_assoc()){
    $canCancel = (
        $o['is_cancelled'] == 0 &&
        $now <= $o['cancel_until'] &&
        $o['status'] == 'Preparing'
    );
    $list[] = [
        "id"           => (int)$o['id'],
        "product_name" => $o['product_name'],
        "quantity"     => (int)$o['quantity'],
        "total"        => (float)$o['total'],
        "status"       => $o['status'],
        "can_cancel"   => $canCancel,
        "cancel_until" => $o['cancel_until'],
        "created_at"   => $o['created_at'],
    ];
}

echo json_encode(["success"=>true,"orders"=>$list]);
?>