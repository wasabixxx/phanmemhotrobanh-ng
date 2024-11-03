<?php
// Bắt đầu phiên
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['user_id'])) {
    // Xóa tất cả các biến phiên
    $_SESSION = [];

    // Nếu cần, hủy phiên
    session_destroy();

    // Chuyển hướng đến trang đăng nhập hoặc trang chủ
    header("Location: login");
    exit; // Dừng thực thi script sau khi chuyển hướng
} else {
    // Nếu người dùng chưa đăng nhập, chuyển hướng đến trang chủ hoặc đăng nhập
    header("Location: login");
    exit;
}
?>
