<?php
$conn = new mysqli(
    "mysql.railway.internal",
    "root",
    "bdrjnEM0lhWrsONVHzhsSfyeAIMbQGkK",
    "railway",
    3306
);
if($conn->connect_error){
    die(json_encode(["error" => $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
?>