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
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Tìm kiếm sản phẩm
$products = [];
$search_query = '';

if (isset($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    $sql = "SELECT * FROM products WHERE product_name LIKE ? OR product_code LIKE ?";
    $search_term = '%' . $search_query . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($product = $result->fetch_assoc()) {
        $products[] = $product;
    }

    // Chỉ trả về HTML của bảng kết quả tìm kiếm
    if (count($products) > 0) {
        foreach ($products as $product) {
            echo '<tr>
                    <td>' . htmlspecialchars($product['product_code']) . '</td>
                    <td>' . htmlspecialchars($product['product_name']) . '</td>
                    <td>' . number_format($product['price_sell']) . ' ₫</td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="price_sell" value="' . $product['price_sell'] . '">
                            <input type="number" name="quantity" placeholder="Số lượng" required>
                            <button type="submit" name="calculate">Nhận tiền</button>
                        </form>
                    </td>
                </tr>';
        }
    } else {
        echo '<tr><td colspan="4">Không tìm thấy sản phẩm nào.</td></tr>';
    }
    exit(); // Dừng thực hiện để không hiển thị phần HTML khác
}

// Tính toán thời gian
date_default_timezone_set('Asia/Ho_Chi_Minh');
$current_time = date('H:i:s');
$current_date = date('Y-m-d');

// Xác định ca làm việc
$shift = '';
if ($current_time >= '07:00:00' && $current_time < '12:00:00') {
    $shift = 'Ca sáng';
} elseif ($current_time >= '12:00:00' && $current_time < '17:00:00') {
    $shift = 'Ca chiều';
} elseif ($current_time >= '17:00:00' && $current_time < '22:00:00') {
    $shift = 'Ca tối';
} else {
    $shift = 'Ngoài giờ làm việc';
}

// Xử lý nhận tiền
$received_money = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calculate'])) {
    $quantity = $_POST['quantity'] ?? 0;
    $price_sell = $_POST['price_sell'] ?? 0;
    $received_money = $quantity * $price_sell;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Staff Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function updateClock() {
                const now = new Date();
                const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: 'Asia/Ho_Chi_Minh' };
                document.getElementById('clock').textContent = now.toLocaleTimeString('vi-VN', options);
            }

            setInterval(updateClock, 1000);
            updateClock();
            
            // Live search
            $('#search_query').on('input', function() {
                const query = $(this).val();
                $.post('', { search_query: query }, function(data) {
                    $('#search_results').html(data);
                }).fail(function() {
                    console.log('Có lỗi xảy ra trong yêu cầu AJAX.');
                });
            });
        });
    </script>
</head>
<body>
    <h1>Quản Lý Sản Phẩm - Nhân Viên</h1>
    <p>Tên người dùng: <?php echo htmlspecialchars($user['username']); ?></p>
    <p>Ca làm việc: <?php echo htmlspecialchars($shift); ?></p>
    <p>Ngày: <?php echo htmlspecialchars($current_date); ?></p>
    <p>Thời gian: <span id="clock"></span></p>

    <input type="text" id="search_query" placeholder="Tìm sản phẩm" required>
    <div id="search_results"></div>

    <h2>Kết quả tìm kiếm</h2>
    <table>
        <thead>
            <tr>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Giá bán</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody id="product_table">
            <?php
            // Hiển thị kết quả tìm kiếm ban đầu nếu có
            if (!empty($products)) {
                foreach ($products as $product) {
                    echo '<tr>
                            <td>' . htmlspecialchars($product['product_code']) . '</td>
                            <td>' . htmlspecialchars($product['product_name']) . '</td>
                            <td>' . number_format($product['price_sell']) . ' ₫</td>
                            <td>
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="price_sell" value="' . $product['price_sell'] . '">
                                    <input type="number" name="quantity" placeholder="Số lượng" required>
                                    <button type="submit" name="calculate">Nhận tiền</button>
                                </form>
                            </td>
                        </tr>';
                }
            }
            ?>
        </tbody>
    </table>

    <?php if ($received_money > 0): ?>
        <h3>Số tiền nhận được: <?php echo number_format($received_money); ?> ₫</h3>
    <?php endif; ?>

    <h2>Tất cả sản phẩm đang hoạt động</h2>
    <?php
    // Lấy tất cả sản phẩm đang hoạt động
    $sql = "SELECT * FROM products WHERE quantity > 0";
    $active_products = $conn->query($sql);
    ?>
    <table>
        <thead>
            <tr>
                <th>Mã sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá bán</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $active_products->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td><?php echo number_format($product['price_sell']); ?> ₫</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
