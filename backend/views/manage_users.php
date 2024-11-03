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

// Lấy danh sách người dùng
$sql = "SELECT * FROM users";
$users = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lí Người Dùng</title>
</head>
<body>
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
</body>
</html>
