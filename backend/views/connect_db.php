<?php
// Kết nối đến MySQL bằng MySQLi
$host = 'localhost:3307';
$dbname = 'pmhtbanhang';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}