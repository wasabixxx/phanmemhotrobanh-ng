<?php
// Lấy slug từ URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : 'login'; // Mặc định là 'login'

// Xử lý routing dựa trên slug
switch ($slug) {
    case 'login':
        require_once 'backend/views/login.php';
        break;
    case 'admin':
        require_once 'backend/views/admin.php';
        break;
    case 'staff':
        require_once 'backend/views/staff.php';
        break;
    case 'logout':
        require_once 'backend/views/logout.php';
        break;
    case 'user':
        require_once 'backend/views/manage_users.php';
        break;
    case 'product':
        require_once 'backend/views/manage_products.php';
        break;
    default:
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>Trang bạn tìm không tồn tại.</p>";
        break;
}
?>
