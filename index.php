<?php
// Lấy slug từ URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : 'home'; // Mặc định là 'home'

// Xử lý routing dựa trên slug
switch ($slug) {
    case 'home':
        include 'home.php';
        break;
    case 'about':
        include 'about.php';
        break;
    case 'contact':
        include 'contact.php';
        break;
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>Trang bạn tìm không tồn tại.</p>";
        break;
}
?>
