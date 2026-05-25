<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){ http_response_code(200); exit; }

include '../db.php';

$data     = json_decode(file_get_contents("php://input"), true);
$username = $conn->real_escape_string(trim($data['username'] ?? ''));
$password = trim($data['password'] ?? '');

if(empty($username) || empty($password)){
    echo json_encode(["success"=>false,"message"=>"Username and password required."]);
    exit;
}

$user = $conn->query("SELECT * FROM students WHERE username='$username'")->fetch_assoc();

$valid = false;
if($user){
    if(password_verify($password, $user['password'])) $valid = true;
    elseif($user['password'] === md5($password))      $valid = true;
}

if(!$valid){
    echo json_encode(["success"=>false,"message"=>"Invalid username or password."]);
    exit;
}

echo json_encode([
    "success" => true,
    "user"    => [
        "id"       => (int)$user['id'],
        "fullname" => $user['fullname'],
        "username" => $user['username'],
        "role"     => $user['role'],
        "balance"  => (float)$user['balance'],
    ]
]);
?>