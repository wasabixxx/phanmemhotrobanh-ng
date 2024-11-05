<?php
session_start(); // Khởi động session
require_once 'connect_db.php'; // Kết nối cơ sở dữ liệu

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
            $_SESSION['username'] = $user['username']; // Sửa đổi ở đây
            
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


<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="https://icons.veryicon.com/png/System/Small%20%26%20Flat/shop.png" alt="" style="height:50px ; width: auto;">
                </a>
                <p class="text-center">Phần mềm hỗ trợ bán hàng</p>
                <form  action="" method="POST">
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="username" name="username" placeholder="Username" class="form-control" required>
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" name="password" placeholder="Password" class="form-control" required>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Remeber this Device
                      </label>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4">ĐĂNG NHẬP</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>