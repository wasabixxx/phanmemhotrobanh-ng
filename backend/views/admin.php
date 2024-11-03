<?php
session_start();

// <---in ra thông tin đăng nhập--->
// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không có role hợp lệ
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    // Chuyển hướng về trang đăng nhập
    header("Location: login");
    exit; // Dừng thực thi mã sau khi chuyển hướng
}

// Nếu người dùng có role_id là 1 hoặc 2, cho phép truy cập trang
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
</head>
<body>
    <h1>Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1> <!-- Hiển thị tên người dùng -->
    
    <?php if ($_SESSION['role_id'] == 1): ?>
        <h2>Chào mừng Admin!</h2>
        <!-- Nội dung dành cho Admin -->
        <ul>
            <li><a href="user">Quản lí tài khoản nhân viên</a></li>
            <li><a href="product">Quản lí thêm đồ vào kho</a></li>
            <li>Check doanh thu</li>
            <li>Check lợi nhuận</li>
            <li>Check hàng tồn kho</li>
            <li><a href='logout'>Đăng xuất</a></li>
        </ul>
    <?php elseif ($_SESSION['role_id'] == 2): ?>
        <h2>Chào mừng Manager!</h2>
        <!-- Nội dung dành cho Manager -->
        <ul>
            <li>Quản lí thêm đồ vào kho</li>
            <li>Check doanh thu</li>
            <li>Check hàng tồn kho</li>
            <li><a href='logout'>Đăng xuất</a></li>
        </ul>
    <?php endif; ?>
</body>
</html>
