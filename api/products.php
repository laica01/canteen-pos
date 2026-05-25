<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include '../db.php';

// Railway URL — change this after you get your Railway domain
define('BASE_URL', 'https://your-app.up.railway.app');

$products = $conn->query("SELECT * FROM products ORDER BY status='Available' DESC, product_name ASC");

$list = [];
while($p = $products->fetch_assoc()){
    $imgUrl = null;
    if(!empty($p['image'])){
        $imgUrl = BASE_URL . "/images/" . $p['image'];
    }
    $list[] = [
        "id"           => (int)$p['id'],
        "product_name" => $p['product_name'],
        "price"        => (float)$p['price'],
        "stock"        => (int)$p['stock'],
        "status"       => $p['status'],
        "image_url"    => $imgUrl,
    ];
}

echo json_encode(["success" => true, "products" => $list]);
?>