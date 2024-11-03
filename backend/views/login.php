<?php
session_start(); // Khởi động session
include 'connect_db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (isset($_SESSION['user_id'])) {
    // Nếu đã đăng nhập, chuyển hướng đến trang tương ứng với vai trò
    if ($_SESSION['role_id'] == 1) { // Admin
        header("Location: admin");
    } elseif ($_SESSION['role_id'] == 2) { // Manager
        header("Location: admin"); // Hoặc trang dành riêng cho Manager
    } elseif ($_SESSION['role_id'] == 3) { // Staff
        header("Location: staff");
    }
    exit(); // Dừng thực thi script
}

// Biến để lưu thông báo lỗi
$error = '';

// Xử lý form khi người dùng gửi thông tin đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra tên đăng nhập và mật khẩu
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            
            // Chuyển hướng đến trang tương ứng
            if ($user['role_id'] == 1) {
                header("Location: admin");
            } elseif ($user['role_id'] == 2) {
                header("Location: admin"); // Hoặc trang dành riêng cho Manager
            } elseif ($user['role_id'] == 3) {
                header("Location: staff");
            }
            exit();
        } else {
            $error = 'Mật khẩu không chính xác!';
        }
    } else {
        $error = 'Tên người dùng không tồn tại!';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="backend/views/css/login.css" />
    <title>Neuromorphic Login Page by Rohit Purkait</title>
  </head>
  <body>
    <div class="main-login-form">
        <div class="login-form-label">
            <h2>Login</h2>
        </div>
        <form action="" method="POST" class="login-form">
            <input type="text" name="username" placeholder="Tên người dùng" class="username-input" required>
            <input type="password" name="password" placeholder="Mật khẩu" class="password-input" required>
            <button type="submit" class="login-button">Đăng Nhập</button>
        </form>
    </div>
  </body>
</html>
