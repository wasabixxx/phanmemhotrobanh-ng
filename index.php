<?php
// Lấy URL từ query string
$request = $_GET['url'] ?? '';

// Tách các phần của URL dựa trên dấu "/"
$requestParts = explode('/', trim($request, '/'));

// Kiểm tra xem phần đầu tiên của URL (trang chính) có khớp với các route đã định nghĩa
switch ($requestParts[0]) {
    case '':
        // Nếu URL trống, hiển thị trang mặc định
        require 'backend/views/home.php';
        break;
    
    case 'about':
        require 'backend/views/about.php';
        break;

    case 'contact':
        require 'backend/views/contact.php';
        break;

    case 'products':
        // Nếu có phần thứ hai, truyền cho trang sản phẩm để xử lý thêm
        $productId = $requestParts[1] ?? null;
        require 'backend/views/products.php';
        break;

    default:
        // Nếu URL không khớp với bất kỳ route nào, hiển thị trang 404
        require 'backend/views/404.php';
        break;
}
