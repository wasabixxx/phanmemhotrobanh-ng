<?php
session_start();
require_once 'connect_db.php';

// Kiểm tra quyền truy cập (Chỉ cho phép admin - role_id = 1)
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login");
    exit();
}

// Biến để lưu thông báo lỗi và thành công
$error = '';
$success = '';
$edit_mode = false; // Biến để xác định chế độ chỉnh sửa

// Danh sách vai trò và ca làm việc (dùng mảng cố định)
$roles = [
    1 => 'Admin',
    2 => 'Manager',
    3 => 'Staff'
];
$shifts = [
    1 => 'Ca Sáng',
    2 => 'Ca Chiều',
    3 => 'Ca Tối'
];

// Xử lý thêm hoặc cập nhật người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_user'])) {
        // Lấy dữ liệu từ form
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role_id = $_POST['role_id'] ?? '';
        $shift_id = ($_POST['role_id'] == 3) ? $_POST['shift_id'] : null; // Chỉ Staff mới có ca làm việc

        // Kiểm tra mật khẩu
        if ($password !== $confirm_password) {
            $error = 'Mật khẩu không khớp!';
        } else {
            // Thêm người dùng mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role_id, shift_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $username, $hashed_password, $role_id, $shift_id);

            if ($stmt->execute()) {
                $success = 'Người dùng đã được thêm thành công!';
            } else {
                $error = 'Lỗi khi thêm người dùng: ' . $conn->error;
            }
        }
    } elseif (isset($_POST['update_user'])) {
        // Chế độ chỉnh sửa người dùng
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $role_id = $_POST['role_id'];
        $shift_id = ($role_id == 3) ? $_POST['shift_id'] : null; // Chỉ Staff mới có ca làm việc

        $sql = "UPDATE users SET username = ?, role_id = ?, shift_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $username, $role_id, $shift_id, $user_id);

        if ($stmt->execute()) {
            $success = 'Người dùng đã được cập nhật thành công!';
        } else {
            $error = 'Lỗi khi cập nhật người dùng: ' . $conn->error;
        }
    }
}

// Xử lý chế độ chỉnh sửa (nút "Sửa")
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $user_id = $_GET['edit'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
}

// Xử lý xóa người dùng
if (isset($_POST['delete_users'])) {
    if (!empty($_POST['user_ids'])) {
        $user_ids = implode(',', $_POST['user_ids']);
        $sql = "DELETE FROM users WHERE id IN ($user_ids)";
        if ($conn->query($sql)) {
            $success = 'Người dùng đã được xóa thành công!';
        } else {
            $error = 'Lỗi khi xóa người dùng: ' . $conn->error;
        }
    } else {
        $error = 'Chưa chọn người dùng nào để xóa!';
    }
}
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
// Lấy danh sách người dùng
$sql = "SELECT * FROM users";
$users = $conn->query($sql);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ADMIN</title>
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/seodashlogo.png" />
  <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.html" class="text-nowrap logo-img">
            <img src="https://icons.veryicon.com/png/System/Small%20%26%20Flat/shop.png" alt="" style="height:50px ; width: auto;" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="admin" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">HELLO</span>
              </a>
            </li>
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
              <span class="hide-menu">QUẢN LÍ ADMIN</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="product" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Quản lí hàng</span>
              </a>
            </li>
            <?php if ($role_id == 1): ?>
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-6" class="fs-6"></iconify-icon>
                    <span class="hide-menu">User manager</span>
                </li>
                <li class="sidebar-item active">
                    <a class="sidebar-link" href="user" aria-expanded="false">
                        <span>
                        <iconify-icon icon="solar:user-plus-rounded-bold-duotone" class="fs-6"></iconify-icon>
                        </span>
                        <span class="hide-menu">Quản lí tài khoản</span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4" class="fs-6"></iconify-icon>
              <span class="hide-menu">Tài chính</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="history" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:sticker-smile-circle-2-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Check doanh thu</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="staff" aria-expanded="false">
                <span>
                  <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Trang bán hàng</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link nav-icon-hover" href="javascript:void(0)">
                <i class="ti ti-bell-ringing"></i>
                <div class="notification bg-primary rounded-circle"></div>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <img src="assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                  <div class="message-body">
                    <a href="logout" class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
    <div class="container-fluid">
        <h1>Quản Lí Người Dùng</h1>

        <?php if ($error): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color: green;"><?= $success ?></p>
        <?php endif; ?>

        <!-- Form thêm và sửa người dùng -->
        <form method="POST" action="">
            <input type="hidden" name="user_id" value="<?= $edit_mode ? $edit_user['id'] : '' ?>">
            <label for="username">Tên Đăng Nhập:</label>
            <input type="text" name="username" id="username" value="<?= $edit_mode ? $edit_user['username'] : '' ?>" required>

            <?php if (!$edit_mode): ?>
                <label for="password">Mật Khẩu:</label>
                <input type="password" name="password" id="password" required>
                <label for="confirm_password">Xác Nhận Mật Khẩu:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            <?php endif; ?>

            <label for="role_id">Vai Trò:</label>
            <select name="role_id" id="role_id" onchange="toggleShiftSelection()" required>
                <?php foreach ($roles as $id => $role_name): ?>
                    <option value="<?= $id ?>" <?= $edit_mode && $edit_user['role_id'] == $id ? 'selected' : '' ?>><?= $role_name ?></option>
                <?php endforeach; ?>
            </select>

            <div id="shift_selection" style="display: none;">
                <label for="shift_id">Ca Làm Việc:</label>
                <select name="shift_id" id="shift_id">
                    <?php foreach ($shifts as $id => $shift_name): ?>
                        <option value="<?= $id ?>" <?= $edit_mode && $edit_user['shift_id'] == $id ? 'selected' : '' ?>><?= $shift_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="<?= $edit_mode ? 'update_user' : 'save_user' ?>">
                <?= $edit_mode ? 'Lưu' : 'Thêm Người Dùng' ?>
            </button>
        </form>
        <?php if ($edit_mode): ?>
            <button><a href="user">Huỷ</a></button> 
        <?php endif; ?>

        <script>
            function toggleShiftSelection() {
                const roleId = document.getElementById('role_id').value;
                document.getElementById('shift_selection').style.display = (roleId == 3) ? 'block' : 'none';
            }
            toggleShiftSelection(); // Chạy khi trang tải để kiểm tra giá trị đã chọn
        </script>

        <!-- Bảng danh sách người dùng -->
        <form method="POST" action="">
            <table border="1">
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Vai Trò</th>
                        <th>Ca Làm Việc</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>"></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= $roles[$user['role_id']] ?></td>
                            <td><?= ($user['role_id'] == 3) ? $shifts[$user['shift_id']] : 'Không áp dụng' ?></td>
                            <td><a href="?edit=<?= $user['id'] ?>">Sửa</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$edit_mode): ?>
                <button type="submit" name="delete_users">Xóa Người Dùng Đã Chọn</button>
            <?php endif; ?>
        </form>
    </div>
  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>