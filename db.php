<?php
$conn = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    (int)getenv('MYSQLPORT')
);
if($conn->connect_error){
    die(json_encode(["error" => $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
?>