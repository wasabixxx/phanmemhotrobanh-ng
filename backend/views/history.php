<?php
session_start();
require_once 'connect_db.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header("Location: login");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];
$username = "";
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
}

// Xử lý lọc doanh thu
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'today';
$total_revenue = 0;
$total_profit = 0;
$shift_condition = ""; // Khởi tạo điều kiện

// Xác định điều kiện lọc theo vai trò
$current_date = date('Y-m-d');
switch ($filter) {
    case 'today':
        $shift_condition = "sale_time BETWEEN '$current_date 00:00:00' AND '$current_date 23:59:59'";
        break;

    case 'last_7_days':
        $shift_condition = "sale_time BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() + INTERVAL 1 DAY";
        break;

    case 'last_month':
        $shift_condition = "sale_time BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE() + INTERVAL 1 DAY";
        break;
}

// Truy vấn doanh thu
if ($role_id == 3) { // Staff
    // Truy vấn doanh thu cho nhân viên chỉ trong hôm nay
    if ($shift_condition) {
        $sql = "SELECT * FROM sales WHERE user_id = ? AND $shift_condition";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $total_revenue += $row['total'];
        }
    }
} else {
    // Xử lý cho manager và admin
    if ($shift_condition) {
        $sql = "SELECT * FROM sales WHERE $shift_condition";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $total_revenue += $row['total'];
            $total_profit += $row['profit'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Bán Hàng</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Lịch Sử Bán Hàng - <?php echo htmlspecialchars($username); ?></h1>

    <?php if ($role_id == 1): ?>
        <h2>Doanh thu: <?php echo number_format($total_revenue); ?> ₫</h2>
        <h2>Lợi nhuận: <?php echo number_format($total_profit); ?> ₫</h2>
    <?php elseif ($role_id == 2): ?>
        <h2>Doanh thu: <?php echo number_format($total_revenue); ?> ₫</h2>
    <?php elseif ($role_id == 3): ?>
        <h2>Doanh thu hôm nay: <?php echo number_format($total_revenue); ?> ₫</h2>
    <?php endif; ?>

    <form method="POST">
        <label for="filter">Chọn khoảng thời gian:</label>
        <select name="filter" id="filter">
            <?php if ($role_id == 1): ?>
                <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                <option value="last_7_days" <?php echo $filter == 'last_7_days' ? 'selected' : ''; ?>>7 ngày qua</option>
                <option value="last_month" <?php echo $filter == 'last_month' ? 'selected' : ''; ?>>1 tháng qua</option>
            <?php elseif ($role_id == 2): ?>
                <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                <option value="last_7_days" <?php echo $filter == 'last_7_days' ? 'selected' : ''; ?>>7 ngày qua</option>
                <option value="last_month" <?php echo $filter == 'last_month' ? 'selected' : ''; ?>>1 tháng qua</option>
            <?php elseif ($role_id == 3): ?>
                <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
            <?php endif; ?>
        </select>
        <button type="submit">Lọc</button>
    </form>

    <h2>Danh Sách Giao Dịch</h2>
    <table>
        <thead>
            <tr>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Tổng tiền</th>
                <?php if ($role_id != 3): ?>
                    <th>Lợi nhuận</th>
                <?php endif; ?>
                <th>Người bán</th>
                <th>Thời gian</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Lấy danh sách giao dịch từ bảng sales
            if ($role_id == 3) {
                $sql = "SELECT * FROM sales WHERE user_id = ? AND $shift_condition";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
            } else {
                $sql = "SELECT * FROM sales WHERE $shift_condition";
                $stmt = $conn->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Lấy thông tin sản phẩm từ bảng products
                    $product_id = isset($row['product_id']) ? $row['product_id'] : null;
                    $product_name = '';
                    $product_code = '';
                    if ($product_id !== null) {
                        $product_sql = "SELECT product_name, product_code FROM products WHERE id = ?";
                        $product_stmt = $conn->prepare($product_sql);
                        $product_stmt->bind_param("i", $product_id);
                        $product_stmt->execute();
                        $product_result = $product_stmt->get_result();
                        if ($product_result->num_rows > 0) {
                            $product = $product_result->fetch_assoc();
                            $product_name = isset($product['product_name']) ? $product['product_name'] : '';
                            $product_code = isset($product['product_code']) ? $product['product_code'] : '';
                        }
                    }

                    // Lấy thông tin người bán từ bảng users
                    $seller_id = isset($row['user_id']) ? $row['user_id'] : null;
                    $seller_name = '';
                    if ($seller_id !== null) {
                        $seller_sql = "SELECT username FROM users WHERE id = ?";
                        $seller_stmt = $conn->prepare($seller_sql);
                        $seller_stmt->bind_param("i", $seller_id);
                        $seller_stmt->execute();
                        $seller_result = $seller_stmt->get_result();
                        if ($seller_result->num_rows > 0) {
                            $seller = $seller_result->fetch_assoc();
                            $seller_name = isset($seller['username']) ? $seller['username'] : '';
                        }
                    }

                    // Hiển thị thông tin giao dịch
                    echo '<tr>
                            <td>' . htmlspecialchars($product_code) . '</td>
                            <td>' . htmlspecialchars($product_name) . '</td>
                            <td>' . (isset($row['quantity']) ? htmlspecialchars($row['quantity']) : 'N/A') . '</td>
                            <td>' . (isset($row['total']) ? number_format($row['total']) . ' ₫' : 'N/A') . '</td>';
                    if ($role_id != 3) {
                        echo '<td>' . (isset($row['profit']) ? number_format($row['profit']) . ' ₫' : 'N/A') . '</td>';
                    }
                    echo '<td>' . htmlspecialchars($seller_name) . '</td>
                            <td>' . (isset($row['sale_time']) ? date('d-m-Y H:i:s', strtotime($row['sale_time'])) : 'N/A') . '</td>
                        </tr>';
                }
            } else {
                echo '<tr><td colspan="7">Chưa có giao dịch nào.</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3">Tổng cộng:</td>
                <td><?php echo number_format($total_revenue); ?> ₫</td>
                <?php if ($role_id != 3): ?>
                    <td><?php echo number_format($total_profit); ?> ₫</td>
                <?php endif; ?>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
